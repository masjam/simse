<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(
        private readonly TotpService $totpService,
    ) {}

    /**
     * Inisiasi pengaktifan 2FA TOTP: generate secret key dan kode pemulihan.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $secret = $this->totpService->generateSecretKey();
        $recoveryCodes = $this->totpService->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ])->save();

        $otpAuthUri = $this->totpService->getOtpAuthUri($user, $secret);
        $qrCodeSvg = $this->totpService->getQrCodeSvgDataUrl($otpAuthUri);

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCodeSvg,
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Konfirmasi aktivasi 2FA dengan kode 6-digit pertama dari aplikasi authenticator.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Kode autentikasi 6 digit wajib diisi.',
            'code.size' => 'Kode autentikasi harus terdiri dari 6 angka.',
        ]);

        $user = $request->user();
        if (! $user->two_factor_secret) {
            return back()->with('error', 'Inisiasi 2FA belum dilakukan.');
        }

        $secret = decrypt($user->two_factor_secret);

        if (! $this->totpService->verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Kode autentikasi yang Anda masukkan salah atau sudah kedaluwarsa.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('success', 'Autentikasi dua faktor (2FA) berhasil diaktifkan pada akun Anda.');
    }

    /**
     * Nonaktifkan 2FA dari akun pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Kata sandi saat ini tidak cocok.',
        ]);

        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('success', 'Autentikasi dua faktor (2FA) telah dinonaktifkan.');
    }

    /**
     * Tampilkan halaman tantangan 2FA saat proses masuk.
     */
    public function challenge(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /**
     * Verifikasi kode 2FA atau kode pemulihan darurat saat login.
     */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('login.id');
        $user = User::findOrFail($userId);

        $code = (string) $request->input('code', '');
        $recoveryCode = (string) $request->input('recovery_code', '');

        $isValid = false;

        if ($code !== '') {
            $secret = decrypt($user->two_factor_secret);
            $isValid = $this->totpService->verify($secret, $code);
        } elseif ($recoveryCode !== '') {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
            $upperCode = strtoupper(trim($recoveryCode));

            if (($key = array_search($upperCode, $recoveryCodes, true)) !== false) {
                unset($recoveryCodes[$key]);
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ])->save();
                $isValid = true;
            }
        }

        if (! $isValid) {
            return back()->withErrors([
                'code' => 'Kode autentikasi 2FA atau kode pemulihan tidak valid.',
            ]);
        }

        // Login pengguna
        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');
        $selectedRole = $request->session()->pull('login.selected_role');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($user->hasRole('Super Admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return redirect()->intended(route('guru.dashboard', absolute: false));
        } elseif ($user->hasRole('Bendahara')) {
            return redirect()->intended(route('bendahara.dashboard', absolute: false));
        } elseif ($user->hasRole('Pengelola Stand Kantin')) {
            return redirect()->intended(route('kantin.dashboard', absolute: false));
        } elseif ($user->hasRole('Staf Sekolah')) {
            return redirect()->intended(route('staf.dashboard', absolute: false));
        } elseif ($user->hasRole('Siswa')) {
            return redirect()->intended(route('siswa.dashboard', absolute: false));
        } elseif ($user->hasRole('Orang Tua')) {
            return redirect()->intended(route('ortu.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
