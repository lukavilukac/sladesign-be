<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_authenticated_users_cannot_register(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/register')->assertRedirect(RouteServiceProvider::HOME);
        $this->post('/register')->assertRedirect(RouteServiceProvider::HOME);

        $this->assertDatabaseCount('users', 1);
    }
}
