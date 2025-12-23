<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa login page bisa diakses
     */
    public function test_login_page_can_be_accessed(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    /**
     * Test bahwa user bisa login dengan credentials yang valid
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'status' => 'ACTIVE',
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'testuser',
            'password' => 'password123',
            'remember' => false,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect();
    }

    /**
     * Test bahwa login gagal dengan credentials yang salah
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'testuser',
            'password' => 'wrongpassword',
            'remember' => false,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('identifier');
    }

    /**
     * Test bahwa inactive user tidak bisa login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'username' => 'inactiveuser',
            'password' => bcrypt('password123'),
            'status' => 'INACTIVE',
        ]);

        $response = $this->post(route('login'), [
            'identifier' => 'inactiveuser',
            'password' => 'password123',
            'remember' => false,
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('identifier');
    }

    /**
     * Test bahwa authenticated user bisa logout
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
