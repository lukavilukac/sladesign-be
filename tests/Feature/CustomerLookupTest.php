<?php

namespace Tests\Feature;

use App\Models\Order\Order;
use App\Models\Shipping\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lookup_returns_only_checkout_fields_and_latest_linked_shipping(): void
    {
        $user = User::factory()->create(['email' => 'Customer@example.com']);
        $old = $this->shippingFor($user, 'Old address');
        $latest = $this->shippingFor($user, 'Latest address');
        $latest->update(['postal_code' => '00123']);
        // Reusing an older shipping record does not make it the newest shipping record.
        $this->orderFor($user, $old);
        $this->shippingFor(User::factory()->create(), 'Other customer address');

        $response = $this->postJson('/api/customers/lookup', ['email' => ' CUSTOMER@example.com '])
            ->assertOk()->assertJsonPath('exists', true)
            ->assertJsonPath('shipping.address', $latest->address)
            ->assertJsonPath('shipping.postal_code', '00123')
            ->assertExactJson([
                'exists' => true,
                'user' => $user->only(['email', 'first_name', 'last_name', 'phone_number', 'country', 'city', 'address', 'apartment']),
                'shipping' => $latest->only(['country', 'city', 'address', 'apartment', 'phone_number', 'postal_code']),
            ]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_unknown_and_admin_emails_return_the_same_empty_result(): void
    {
        config(['mail.admin_address' => 'shop@example.com']);
        User::factory()->create(['email' => 'shop@example.com']);
        User::factory()->admin()->create(['email' => 'admin@example.com']);
        foreach (['missing@example.com', 'SHOP@example.com', 'admin@example.com'] as $email) {
            $this->postJson('/api/customers/lookup', compact('email'))->assertOk()
                ->assertExactJson(['exists' => false, 'user' => null, 'shipping' => null]);
        }
    }

    public function test_customer_without_shipping_is_found(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/customers/lookup', ['email' => $user->email])->assertOk()
            ->assertJsonPath('exists', true)->assertJsonPath('shipping', null);
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        foreach ([[], ['email' => 'invalid'], ['email' => ['nested']]] as $payload) {
            $this->postJson('/api/customers/lookup', $payload)->assertUnprocessable()->assertJsonValidationErrors('email');
        }
    }

    public function test_public_lookup_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/customers/lookup', ['email' => 'missing@example.com'])->assertOk();
        }
        $this->postJson('/api/customers/lookup', ['email' => 'missing@example.com'])->assertStatus(429);
    }

    private function shippingFor(User $user, string $address): Shipping
    {
        $shipping = Shipping::create(['country' => 'Serbia', 'city' => 'Novi Sad', 'address' => $address,
            'apartment' => '2', 'phone_number' => '+381691111111']);
        $this->orderFor($user, $shipping);

        return $shipping;
    }

    private function orderFor(User $user, Shipping $shipping): void
    {
        Order::create(['user_id' => $user->id, 'shipping_id' => $shipping->id,
            'order_date' => now(), 'price' => 100, 'order_status_id' => Order::PENDING]);
    }
}
