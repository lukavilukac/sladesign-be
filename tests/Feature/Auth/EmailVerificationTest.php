<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    // These Breeze routes were removed from the CMS before the framework upgrade.
    public function test_disabled_account_routes_remain_unavailable(): void
    {
        $user = User::factory()->create();
        $original = $user->refresh()->getAttributes();

        $this->get('/verify-email')->assertNotFound();
        $this->get('/verify-email/1/hash')->assertNotFound();
        $this->post('/email/verification-notification')->assertNotFound();

        $this->actingAs($user);

        $this->get('/verify-email')->assertNotFound();
        $this->get('/verify-email/1/hash')->assertNotFound();
        $this->post('/email/verification-notification')->assertNotFound();

        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 1);
    }
}
