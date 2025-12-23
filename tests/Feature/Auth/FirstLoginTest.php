<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FirstLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa first login page dapat diakses oleh user dengan is_first_login = true
     */
    public function test_first_login_page_can_be_accessed_by_first_time_user(): void
    {
        $user = User::factory()->create([
            'is_first_login' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/first-login');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/FirstLogin')
            ->has('user', fn ($user) => $user
                ->has('name')
                ->has('username')
                ->has('email')
            )
        );
    }

    /**
     * Test bahwa user yang bukan first login akan di-redirect ke dashboard
     */
    public function test_non_first_login_user_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'is_first_login' => false,
            'status' => 'active',
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($user)->get('/first-login');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test bahwa guest user tidak dapat mengakses first login page
     */
    public function test_guest_cannot_access_first_login_page(): void
    {
        $response = $this->get('/first-login');

        $response->assertRedirect('/login');
    }

    /**
     * Test bahwa user dapat mengubah password pada first login dengan valid data
     */
    public function test_user_can_change_password_on_first_login(): void
    {
        $user = User::factory()->create([
            'is_first_login' => true,
            'status' => 'active',
            'role' => 'TEACHER',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $newPassword = 'NewSecurePassword123!@#';

        $response = $this->actingAs($user)->post('/first-login', [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect('/teacher/dashboard');
        $response->assertSessionHas('message', 'Password berhasil diubah. Selamat datang!');

        // Refresh user dari database
        $user->refresh();

        // Verify password sudah berubah dan is_first_login sudah false
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertFalse($user->is_first_login);

        // Verify activity log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'first_login_password_change',
            'status' => 'success',
        ]);
    }

    /**
     * Test bahwa password harus memenuhi requirements (min 8 char, mixed case, number, symbol)
     */
    public function test_password_must_meet_requirements(): void
    {
        $user = User::factory()->create([
            'is_first_login' => true,
            'status' => 'active',
        ]);

        // Test password terlalu pendek
        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ]);

        $response->assertSessionHasErrors('password');

        // Test password tanpa uppercase
        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'lowercase123!',
            'password_confirmation' => 'lowercase123!',
        ]);

        $response->assertSessionHasErrors('password');

        // Test password tanpa lowercase
        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'UPPERCASE123!',
            'password_confirmation' => 'UPPERCASE123!',
        ]);

        $response->assertSessionHasErrors('password');

        // Test password tanpa angka
        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'NoNumbers!@#',
            'password_confirmation' => 'NoNumbers!@#',
        ]);

        $response->assertSessionHasErrors('password');

        // Test password tanpa simbol
        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'NoSymbols123',
            'password_confirmation' => 'NoSymbols123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test bahwa password confirmation harus cocok
     */
    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'is_first_login' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test bahwa user yang bukan first login tidak dapat update via first login endpoint
     */
    public function test_non_first_login_user_cannot_update_password_via_first_login_endpoint(): void
    {
        $user = User::factory()->create([
            'is_first_login' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/first-login', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test redirect ke dashboard yang sesuai berdasarkan role user
     * Note: STUDENT role currently disabled
     */
    public function test_redirects_to_correct_dashboard_based_on_role(): void
    {
        $roles = [
            'SUPERADMIN' => '/admin/dashboard',
            'ADMIN' => '/admin/dashboard',
            'PRINCIPAL' => '/principal/dashboard',
            'TEACHER' => '/teacher/dashboard',
            'PARENT' => '/parent/dashboard',
            // 'STUDENT' => '/student/dashboard', // DISABLED - Student portal belum diimplementasi
        ];

        foreach ($roles as $role => $expectedRoute) {
            $user = User::factory()->create([
                'is_first_login' => true,
                'status' => 'active',
                'role' => $role,
            ]);

            // Generate unique password untuk setiap test iteration
            $uniquePassword = 'UniqueTest!Pass@2025_'.uniqid();

            $response = $this->actingAs($user)->post('/first-login', [
                'password' => $uniquePassword,
                'password_confirmation' => $uniquePassword,
            ]);

            $response->assertRedirect($expectedRoute);
        }
    }
}
