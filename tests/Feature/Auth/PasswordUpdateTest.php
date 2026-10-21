<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    // These Breeze routes were removed from the CMS before the framework upgrade.
    public function test_disabled_account_routes_remain_unavailable(): void
    {
        $user = User::factory()->create();
        $original = $user->refresh()->getAttributes();

        $this->put('/password')->assertNotFound();

        $this->actingAs($user);

        $this->put('/password')->assertNotFound();

        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 1);
    }
}
