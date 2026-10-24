<?php

namespace Tests\Feature;

use App\Mail\ApplicationError;
use App\Mail\OrderConfirmation;
use App\Mail\OrderToAdmin;
use App\Models\Image\ProductImage\ProductImage;
use App\Models\Modifier\Modifier;
use App\Models\ModifierGroup\ModifierGroup;
use App\Models\Order\Order;
use App\Models\Order\OrderAttempt;
use App\Models\Order\OrderService;
use App\Models\OrderLine\OrderLine;
use App\Models\Product\Product;
use App\Models\User;
use App\Services\OrderAudit;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class OrderAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Real commits are required to test afterCommit; no test transaction wrapper.
        $this->artisan('migrate:fresh')->assertExitCode(0);
        config(['error-notifications.enabled' => false]);
        Mail::fake();
    }

    protected function tearDown(): void
    {
        // Other suites use RefreshDatabase; force a fresh schema after our real commits.
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    private function payload(): array
    {
        $image = ProductImage::create(['path' => '/storage/test.jpg']);
        $product = Product::create(['name' => 'Test', 'slug' => 'test-'.Str::uuid(),
            'description' => 'Test', 'price' => 100, 'product_image_id' => $image->id]);

        return [
            'email_address' => 'customer@example.com', 'first_name' => 'Test', 'last_name' => 'Customer',
            'country_name' => 'Srbija', 'country_code' => '+381', 'city' => 'Beograd',
            'address' => 'Test 123', 'phone_number' => '601234567', 'apartment' => null, 'price' => 200,
            'order_lines' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 100, 'modifier_ids' => []]],
        ];
    }

    private function createOrder(): Order
    {
        $this->postJson('/api/orders/create', $this->payload())->assertCreated();

        return Order::latest('id')->firstOrFail();
    }

    public function test_success_records_every_step_and_sends_both_mails_after_commit(): void
    {
        $payload = $this->payload();
        $payload['checkout_id'] = (string) Str::uuid();
        $response = $this->postJson('/api/orders/create', $payload)->assertCreated();
        $attempt = OrderAttempt::firstOrFail();
        $response->assertHeader('X-Order-Attempt-Id', $attempt->id);
        $this->assertSame('created', $attempt->status);
        $this->assertEquals($payload, $attempt->payload);
        $this->assertSame($payload['checkout_id'], $attempt->checkout_id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_lines', 1);
        $this->assertSame(['request_received', 'validation_passed', 'customer_prepared', 'shipping_prepared',
            'order.created', 'orderline.created', 'order_committed', 'mail_sending', 'mail_sent', 'mail_sending', 'mail_sent'],
            Activity::orderBy('id')->pluck('event')->all());
        $this->assertNull(Activity::where('event', 'order.created')->first()->causer_id);
        $this->assertSame('Guest', Activity::first()->properties['actor']['name']);
        Mail::assertSent(OrderToAdmin::class, 1);
        Mail::assertSent(OrderConfirmation::class, 1);
        $this->assertSame(0, DB::transactionLevel());
        $this->assertNull(app(OrderAudit::class)->attempt);
    }

    public function test_missing_admin_recipient_keeps_order_and_sends_customer_confirmation(): void
    {
        config(['mail.admin_address' => null]);

        $this->postJson('/api/orders/create', $this->payload())->assertCreated();

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame('created_with_mail_errors', OrderAttempt::firstOrFail()->status);
        $failure = Activity::where('event', 'mail_failed')->sole();
        $this->assertSame('admin_mail', $failure->properties['phase']);
        Mail::assertNotSent(OrderToAdmin::class);
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_validation_failure_preserves_payload_and_errors_without_creating_an_order(): void
    {
        $payload = $this->payload();
        $payload['email_address'] = 'invalid';
        $this->postJson('/api/orders/create', $payload)->assertUnprocessable()->assertJsonValidationErrors('email_address');
        $attempt = OrderAttempt::firstOrFail();
        $this->assertSame('validation_failed', $attempt->status);
        $this->assertSame('invalid', $attempt->payload['email_address']);
        $this->assertNull($attempt->validated_payload);
        $this->assertNotEmpty(Activity::where('event', 'validation_failed')->first()->properties['errors']);
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_payload_does_not_store_secrets_or_untrusted_actor_fields(): void
    {
        $payload = $this->payload();
        $payload['password'] = 'secret-password';
        $payload['user_id'] = 123;
        $payload['authorization'] = 'secret-token';
        $payload['order_lines'][0]['card_number'] = 'secret-card';
        $payload['address'] = ['password' => 'nested-secret'];
        $this->postJson('/api/orders/create', $payload)->assertUnprocessable();
        $stored = OrderAttempt::firstOrFail();
        foreach (['secret-password', 'secret-token', 'secret-card', 'nested-secret'] as $secret) {
            $this->assertStringNotContainsString($secret, $stored->toJson());
        }
        $this->assertArrayNotHasKey('user_id', $stored->payload);
        $this->assertNull($stored->actor_id);
    }

    public function test_error_rolls_back_customer_shipping_items_and_success_audit_but_keeps_failure(): void
    {
        $payload = $this->payload();
        // A PHP Error, not only an Exception, must roll back the entire transaction.
        OrderLine::creating(function () {
            throw new \Error('Line persistence failed');
        });
        $this->postJson('/api/orders/create', $payload)->assertStatus(500);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_lines', 0);
        $this->assertDatabaseCount('shippings', 0);
        $this->assertDatabaseMissing('users', ['email' => $payload['email_address']]);
        $attempt = OrderAttempt::firstOrFail();
        $this->assertSame('failed', $attempt->status);
        $this->assertNull($attempt->order_id);
        $this->assertSame(['request_received', 'validation_passed', 'creation_failed'], Activity::orderBy('id')->pluck('event')->all());
        $failure = Activity::where('event', 'creation_failed')->first();
        $this->assertSame('order_line.0', $failure->properties['phase']);
        $this->assertSame(['customer', 'shipping', 'order'], $failure->properties['rolled_back_steps']);
        $this->assertSame(0, DB::transactionLevel());
        Mail::assertNothingSent();
    }

    public static function failingRecipients(): array
    {
        return [['admin'], ['customer'], ['both']];
    }

    #[DataProvider('failingRecipients')]
    public function test_mail_failure_keeps_order_returns_created_and_attempts_both_recipients(string $failure): void
    {
        $pending = Mockery::mock(PendingMail::class);
        Mail::shouldReceive('to')->twice()->andReturn($pending);
        $sent = [];
        $pending->shouldReceive('send')->twice()->andReturnUsing(function ($mail) use ($failure, &$sent) {
            $this->assertSame(0, DB::transactionLevel());
            $this->assertDatabaseCount('orders', 1);
            $kind = $mail instanceof OrderToAdmin ? 'admin' : 'customer';
            $sent[] = $kind;
            if ($failure === 'both' || $failure === $kind) {
                throw new \RuntimeException('SMTP failure '.$kind);
            }
        });
        $this->postJson('/api/orders/create', $this->payload())->assertCreated();
        $this->assertSame(['admin', 'customer'], $sent);
        $this->assertSame('created_with_mail_errors', OrderAttempt::first()->status);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame($failure === 'both' ? 2 : 1, Activity::where('event', 'mail_failed')->count());
        $this->assertSame(0, Activity::where('event', 'creation_failed')->count());
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_outer_transaction_defers_mail_and_rollback_discards_it(): void
    {
        $payload = $this->payload();
        DB::beginTransaction();
        app(OrderService::class)->createOrder($payload);
        Mail::assertNothingSent();
        DB::rollBack();
        Mail::assertNothingSent();
        $this->assertDatabaseCount('orders', 0);

        DB::beginTransaction();
        app(OrderService::class)->createOrder($payload);
        Mail::assertNothingSent();
        DB::commit();
        Mail::assertSent(OrderConfirmation::class, 1);
        Mail::assertSent(OrderToAdmin::class, 1);
    }

    public function test_admin_status_change_records_actor_old_and_new_values_and_ignores_noop(): void
    {
        $order = $this->createOrder();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->patch('/admin/orders/update-status/'.$order->id, ['order_status_id' => Order::IN_PROGRESS])
            ->assertRedirect();
        $entry = Activity::where('event', 'order.updated')->firstOrFail();
        $this->assertEquals($admin->id, $entry->causer_id);
        $this->assertSame($admin->email, $entry->properties['actor']['email']);
        $this->assertEquals(Order::PENDING, $entry->attribute_changes['old']['order_status_id']);
        $this->assertEquals(Order::IN_PROGRESS, $entry->attribute_changes['attributes']['order_status_id']);
        $this->assertNotEmpty($entry->properties['request_id']);
        $this->patch('/admin/orders/update-status/'.$order->id, ['order_status_id' => Order::IN_PROGRESS])->assertRedirect();
        $this->assertSame(1, Activity::where('event', 'order.updated')->count());
    }

    public function test_invalid_status_is_audited_without_changing_order(): void
    {
        $order = $this->createOrder();
        $this->actingAs(User::factory()->admin()->create())
            ->patchJson('/admin/orders/update-status/'.$order->id, ['order_status_id' => 999])->assertUnprocessable();
        $this->assertEquals(Order::PENDING, $order->fresh()->order_status_id);
        $this->assertSame(1, Activity::where('event', 'change_failed')->count());
        $this->assertSame(0, Activity::where('event', 'order.updated')->count());
    }

    public function test_items_modifiers_and_shipping_are_in_order_history(): void
    {
        $order = $this->createOrder();
        $this->actingAs(User::factory()->admin()->create());
        $line = $order->order_lines->first();
        $line->update(['quantity' => 3]);
        $group = ModifierGroup::create(['name' => 'Size']);
        $modifier = Modifier::create(['modifier_group_id' => $group->id, 'value' => 'Large', 'price' => 10]);
        $line->modifiers()->attach($modifier->id);
        $line->modifiers()->detach($modifier->id);
        $order->shipping->update(['address' => 'Changed address']);
        foreach (['orderline.updated', 'orderlinemodifier.created', 'orderlinemodifier.deleted', 'shipping.updated'] as $event) {
            $this->assertDatabaseHas('activity_log', ['event' => $event, 'order_id' => $order->id]);
        }
        $this->getJson('/admin/orders/'.$order->id.'/history?type=changes')->assertOk()
            ->assertJsonFragment(['event' => 'orderline.updated']);
    }

    public function test_history_and_payload_routes_are_admin_only_and_scoped_to_the_order(): void
    {
        $order = $this->createOrder();
        $attempt = OrderAttempt::first();
        $other = $this->createOrder();
        $urls = ['/admin/order-attempts', '/admin/order-attempts/'.$attempt->id,
            '/admin/order-attempts/'.$attempt->id.'/history', '/admin/orders/'.$order->id.'/history'];
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->actingAs(User::factory()->create());
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/admin/order-attempts')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Orders/Attempts')->has('attempts.data', 2)->missing('attempts.data.0.payload'));
        $this->get('/admin/order-attempts/'.$attempt->id)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Orders/Attempt')->where('attempt.id', $attempt->id)->has('attempt.payload'));
        $history = $this->getJson('/admin/orders/'.$order->id.'/history')->assertOk();
        $this->assertCount(11, $history->json('data'));
        foreach ($history->json('data') as $entry) {
            $this->assertSame($attempt->id, $entry['attempt_id']);
        }
        $this->getJson('/admin/orders/'.$order->id.'/history?type=errors')->assertJsonPath('total', 0);
        $this->getJson('/admin/orders/999999/history')->assertNotFound();
    }

    public function test_history_paginates_and_checkout_retries_remain_separate(): void
    {
        $payload = $this->payload();
        $payload['checkout_id'] = (string) Str::uuid();
        $invalid = array_merge($payload, ['email_address' => 'invalid']);
        $this->postJson('/api/orders/create', $invalid)->assertUnprocessable();
        $this->postJson('/api/orders/create', $payload)->assertCreated();
        $attempt = OrderAttempt::where('status', 'created')->first();
        $order = Order::first();
        for ($i = 0; $i < 30; $i++) {
            app(OrderAudit::class)->record('test_event', $order);
        }
        $this->actingAs(User::factory()->admin()->create());
        $this->getJson('/admin/orders/'.$order->id.'/history')->assertJsonCount(25, 'data')->assertJsonPath('total', 41);
        $this->getJson('/admin/orders/'.$order->id.'/history?page=2')->assertJsonCount(16, 'data');
        $this->get('/admin/order-attempts?checkout_id='.$payload['checkout_id'])->assertInertia(fn (Assert $page) => $page->has('attempts.data', 2));
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_failure_notification_links_to_exact_attempt(): void
    {
        config(['error-notifications.enabled' => true]);
        $payload = $this->payload();
        OrderLine::creating(function () {
            throw new \RuntimeException('Forced failure');
        });
        $this->postJson('/api/orders/create', $payload)->assertStatus(500);
        $attempt = OrderAttempt::first();
        Mail::assertSent(ApplicationError::class, function ($mail) use ($attempt) {
            parse_str(parse_url($mail->viewerUrl, PHP_URL_QUERY), $query);

            return $query['query'] === preg_quote($attempt->id, '/');
        });
        Mail::assertSentCount(1);
    }

    public function test_second_item_failure_rolls_back_first_item_and_restores_existing_customer(): void
    {
        $payload = $this->payload();
        $customer = User::factory()->create(['email' => $payload['email_address'], 'first_name' => 'Original']);
        $payload['order_lines'][] = $payload['order_lines'][0];
        $calls = 0;
        OrderLine::creating(function () use (&$calls) {
            if (++$calls === 2) {
                throw new \RuntimeException('Second item failed');
            }
        });
        $this->postJson('/api/orders/create', $payload)->assertStatus(500);
        $this->assertSame('Original', $customer->fresh()->first_name);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_lines', 0);
        $this->assertDatabaseCount('modifier_order_line', 0);
        $this->assertSame(0, Activity::where('event', 'orderline.created')->count());
        $this->assertSame('order_line.1', Activity::where('event', 'creation_failed')->first()->properties['phase']);
    }

    public function test_audit_write_failure_rolls_back_status_change(): void
    {
        $order = $this->createOrder();
        Activity::creating(function ($activity) {
            if ($activity->event === 'order.updated') {
                throw new \RuntimeException('Audit write failed');
            }
        });
        $this->actingAs(User::factory()->admin()->create())
            ->patchJson('/admin/orders/update-status/'.$order->id, ['order_status_id' => Order::DONE])
            ->assertStatus(500);
        $this->assertEquals(Order::PENDING, $order->fresh()->order_status_id);
        $this->assertSame(0, Activity::where('event', 'order.updated')->count());
        $this->assertSame(1, Activity::where('event', 'change_failed')->count());
    }

    public function test_customer_changes_are_not_logged_but_shared_shipping_changes_are_recorded_for_each_order(): void
    {
        $first = $this->createOrder();
        $second = $this->createOrder();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $first->user->update(['first_name' => 'Renamed']);
        $first->shipping->update(['city' => 'Novi Sad']);
        foreach ([$first, $second] as $order) {
            $this->assertDatabaseMissing('activity_log', ['order_id' => $order->id, 'event' => 'user.updated']);
            $this->assertDatabaseHas('activity_log', ['order_id' => $order->id, 'event' => 'shipping.updated', 'causer_id' => $admin->id]);
        }
        $first->user->update(['password' => bcrypt('new-password')]);
        $this->assertSame(0, Activity::where('event', 'user.updated')->count());
    }

    public function test_actor_snapshot_and_audit_survive_order_and_actor_deletion(): void
    {
        $order = $this->createOrder();
        $attempt = OrderAttempt::first();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        app(OrderService::class)->updateOrder($order->id, ['order_status_id' => Order::DONE]);
        $order->order_lines->each->delete();
        app(OrderService::class)->deleteOrder($order->id);
        $admin->delete();
        $entry = Activity::where('event', 'order.updated')->firstOrFail();
        $this->assertSame($admin->email, $entry->properties['actor']['email']);
        $this->assertDatabaseHas('activity_log', ['order_id' => $order->id, 'event' => 'order.deleted']);
        $this->assertDatabaseHas('order_attempts', ['id' => $attempt->id, 'order_id' => $order->id]);
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/order-attempts/'.$attempt->id)->assertOk();
    }

    public function test_new_migrations_can_be_rolled_back_and_reapplied(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 2])->assertExitCode(0);
        $this->assertFalse(Schema::hasTable('order_attempts'));
        $this->assertFalse(Schema::hasTable('activity_log'));
        $this->artisan('migrate')->assertExitCode(0);
        $this->assertTrue(Schema::hasColumn('activity_log', 'attempt_id'));
    }

    public function test_validation_is_recorded_for_clients_without_json_accept_header(): void
    {
        $this->post('/api/orders/create', [])->assertRedirect();
        $this->assertSame('validation_failed', OrderAttempt::firstOrFail()->status);
        $this->assertSame(1, Activity::where('event', 'validation_failed')->count());
    }

    public function test_previous_validation_errors_do_not_mark_a_successful_status_change_as_failed(): void
    {
        $order = $this->createOrder();
        $this->actingAs(User::factory()->admin()->create());
        $this->patch('/admin/orders/update-status/'.$order->id, ['order_status_id' => 999])->assertRedirect();
        $this->patch('/admin/orders/update-status/'.$order->id, ['order_status_id' => Order::DONE])->assertRedirect();
        $this->assertSame(1, Activity::where('event', 'change_failed')->count());
        $this->assertEquals(Order::DONE, $order->fresh()->order_status_id);
    }
}
