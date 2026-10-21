<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // These Breeze routes were removed from the CMS before the framework upgrade.
    public function test_disabled_account_routes_remain_unavailable(): void
    {
        $user = User::factory()->create();
        $original = $user->refresh()->getAttributes();

        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password')->assertNotFound();
        $this->get('/reset-password/token')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();

        $this->actingAs($user);

        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password')->assertNotFound();
        $this->get('/reset-password/token')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();

        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 1);
    }
}
