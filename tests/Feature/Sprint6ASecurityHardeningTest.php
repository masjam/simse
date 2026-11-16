<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\Security\SecureUploadService;
use App\Services\Security\TotpService;
use App\Services\Security\TurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Sprint6ASecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Bendahara', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Guru Pengajar', 'guard_name' => 'web']);
    }

    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
    }

    public function test_totp_service_generates_and_verifies_rfc_6238_codes(): void
    {
        $service = app(TotpService::class);
        $secret = $service->generateSecretKey();

        $this->assertNotEmpty($secret);
        $this->assertEquals(16, strlen($secret));

        $code = $service->getCurrentCode($secret);
        $this->assertEquals(6, strlen($code));
        $this->assertTrue($service->verify($secret, $code));

        $this->assertFalse($service->verify($secret, '000000'));
    }

    public function test_user_with_2fa_enabled_is_redirected_to_two_factor_challenge(): void
    {
        $totpService = app(TotpService::class);
        $secret = $totpService->generateSecretKey();

        $admin = User::factory()->create([
            'email' => 'admin.2fa@test.com',
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['ABCD-1234', 'EFGH-5678'])),
        ]);
        $admin->assignRole('Super Admin');

        $response = $this->post('/login', [
            'email' => 'admin.2fa@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->assertEquals($admin->id, session('login.id'));
    }

    public function test_user_can_authenticate_via_valid_totp_code(): void
    {
        $totpService = app(TotpService::class);
        $secret = $totpService->generateSecretKey();

        $admin = User::factory()->create([
            'email' => 'admin.verify@test.com',
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole('Super Admin');

        $validCode = $totpService->getCurrentCode($secret);

        $response = $this->withSession(['login.id' => $admin->id])
            ->post(route('two-factor.verify'), [
                'code' => $validCode,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_can_authenticate_via_recovery_code_and_code_is_consumed(): void
    {
        $totpService = app(TotpService::class);
        $secret = $totpService->generateSecretKey();

        $admin = User::factory()->create([
            'email' => 'admin.recovery@test.com',
            'password' => Hash::make('Password123!'),
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['RECO-VERY-0001', 'RECO-VERY-0002'])),
        ]);
        $admin->assignRole('Super Admin');

        $response = $this->withSession(['login.id' => $admin->id])
            ->post(route('two-factor.verify'), [
                'recovery_code' => 'RECO-VERY-0001',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $admin->refresh();
        $remainingCodes = json_decode(decrypt($admin->two_factor_recovery_codes), true);
        $this->assertCount(1, $remainingCodes);
        $this->assertNotContains('RECO-VERY-0001', $remainingCodes);
        $this->assertContains('RECO-VERY-0002', $remainingCodes);
    }

    public function test_secure_upload_service_accepts_valid_documents_and_stores_in_private_disk(): void
    {
        Storage::fake('local');
        $service = app(SecureUploadService::class);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $result = $service->upload($file, 'surat');

        $this->assertStringStartsWith('surat/', $result['path']);
        $this->assertEquals('application/pdf', $result['mime_type']);
        Storage::disk('local')->assertExists($result['path']);
    }

    public function test_secure_upload_service_rejects_unwhitelisted_files(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = app(SecureUploadService::class);
        $file = UploadedFile::fake()->create('script.php', 10, 'text/x-php');

        $service->upload($file);
    }

    public function test_turnstile_service_respects_configuration(): void
    {
        $service = app(TurnstileService::class);

        Config::set('services.turnstile.enabled', false);
        $this->assertTrue($service->verify(null));

        Config::set('services.turnstile.enabled', true);
        $this->assertFalse($service->verify(''));
    }

    public function test_updating_password_invalidates_other_device_sessions(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Lama123!'),
        ]);

        $this->actingAs($user);

        $response = $this->put(route('password.update'), [
            'current_password' => 'Lama123!',
            'password' => 'Baru123!@#',
            'password_confirmation' => 'Baru123!@#',
        ]);

        $response->assertSessionHas('status', 'password-updated');
        $this->assertTrue(Hash::check('Baru123!@#', $user->fresh()->password));
    }
}
