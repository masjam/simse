<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_role_login_screens_can_be_rendered(): void
    {
        foreach (['siswa', 'guru', 'ortu', 'admin', 'staf', 'bendahara'] as $role) {
            $this->get("/{$role}/login")->assertOk();
        }
    }

    public function test_users_can_authenticate_via_matching_role_login_route(): void
    {
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $guru = User::factory()->create();
        $guru->assignRole('Guru Pengajar');

        $this->post('/guru/login', [
            'email' => $guru->email,
            'password' => 'password',
        ])->assertRedirect(route('guru.dashboard', absolute: false));
        $this->assertAuthenticatedAs($guru);
        $this->post('/logout');

        $siswa = User::factory()->create();
        $siswa->assignRole('Siswa');

        $this->post('/siswa/login', [
            'email' => $siswa->email,
            'password' => 'password',
        ])->assertRedirect(route('siswa.dashboard', absolute: false));
        $this->assertAuthenticatedAs($siswa);
        $this->post('/logout');

        $ortu = User::factory()->create();
        $ortu->assignRole('Orang Tua');

        $this->post('/ortu/login', [
            'email' => $ortu->email,
            'password' => 'password',
        ])->assertRedirect(route('ortu.dashboard', absolute: false));
        $this->assertAuthenticatedAs($ortu);
        $this->post('/logout');

        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_users_cannot_authenticate_via_wrong_role_login_route(): void
    {
        Role::firstOrCreate(['name' => 'Siswa', 'guard_name' => 'web']);

        $siswa = User::factory()->create();
        $siswa->assignRole('Siswa');

        $this->post('/admin/login', [
            'email' => $siswa->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->post('/ortu/login', [
            'email' => $siswa->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
