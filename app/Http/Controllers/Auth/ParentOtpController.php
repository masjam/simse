<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\WhatsAppGatewayInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestParentOtpRequest;
use App\Http\Requests\Auth\VerifyParentOtpRequest;
use App\Models\ParentOtp;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ParentOtpController extends Controller
{
    private const MAX_REQUESTS_PER_WINDOW = 3;

    private const REQUEST_WINDOW_SECONDS = 300;

    private const MAX_VERIFY_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    private const OTP_EXPIRY_MINUTES = 5;

    public function __construct(
        private readonly WhatsAppGatewayInterface $whatsAppGateway,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'initialRole' => 'ortu',
            'otpSentPhone' => session('parent_otp_phone'),
        ]);
    }

    public function requestOtp(RequestParentOtpRequest $request): RedirectResponse
    {
        $normalizedPhone = PhoneNumber::normalize($request->validated('phone_number'));

        if ($normalizedPhone === '') {
            throw ValidationException::withMessages([
                'phone_number' => 'Format nomor WhatsApp tidak valid.',
            ]);
        }

        $this->ensureNotLocked($normalizedPhone);

        $rateKey = $this->rateLimitKey($normalizedPhone, (string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_REQUESTS_PER_WINDOW)) {
            $seconds = RateLimiter::availableIn($rateKey);

            throw ValidationException::withMessages([
                'phone_number' => "Terlalu banyak permintaan OTP. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $variants = PhoneNumber::variants($normalizedPhone);
        $birthDate = $request->validated('birth_date');

        $student = Student::query()
            ->whereDate('tanggal_lahir', $birthDate)
            ->where(function ($query) use ($variants): void {
                $query->whereIn('no_telp_ortu', $variants)
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->whereIn('phone', $variants));
            })
            ->first();

        if (! $student) {
            RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

            throw ValidationException::withMessages([
                'phone_number' => 'Nomor WhatsApp orang tua atau tanggal lahir anak tidak cocok dengan data sekolah.',
            ]);
        }

        $parentUser = $this->resolveOrCreateParentUser($student, $normalizedPhone, $variants);

        ParentOtp::query()
            ->where('phone_number', $normalizedPhone)
            ->whereNull('verified_at')
            ->delete();

        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ParentOtp::create([
            'phone_number' => $normalizedPhone,
            'student_id' => $student->id,
            'user_id' => $parentUser->id,
            'code' => Hash::make($plainCode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        $message = "Kode OTP login Portal Orang Tua SIMSE Anda adalah *{$plainCode}*. "
            .'Berlaku 5 menit. Jangan bagikan kode ini kepada siapa pun.';

        $delivery = $this->whatsAppGateway->send(
            phoneNumber: $normalizedPhone,
            message: $message,
            recipientName: $parentUser->name,
        );

        if (! ($delivery['success'] ?? false)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Gagal mengirim OTP ke WhatsApp. Silakan hubungi pihak sekolah.',
            ]);
        }

        RateLimiter::hit($rateKey, self::REQUEST_WINDOW_SECONDS);

        return back()->with([
            'status' => 'Kode OTP 6 digit telah dikirim ke WhatsApp Anda.',
            'parent_otp_phone' => $request->validated('phone_number'),
        ]);
    }

    public function verifyOtp(VerifyParentOtpRequest $request): RedirectResponse
    {
        $normalizedPhone = PhoneNumber::normalize($request->validated('phone_number'));

        if ($normalizedPhone === '') {
            throw ValidationException::withMessages([
                'phone_number' => 'Format nomor WhatsApp tidak valid.',
            ]);
        }

        $otp = ParentOtp::query()
            ->where('phone_number', $normalizedPhone)
            ->whereNull('verified_at')
            ->latest('created_at')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'otp_code' => 'Sesi OTP tidak ditemukan. Silakan minta kode OTP baru.',
            ]);
        }

        if ($otp->isLocked()) {
            $minutes = max(1, (int) ceil(now()->diffInSeconds($otp->locked_until) / 60));

            throw ValidationException::withMessages([
                'otp_code' => "Terlalu banyak percobaan gagal. Akun terkunci sementara selama {$minutes} menit.",
            ]);
        }

        if ($otp->isExpired()) {
            throw ValidationException::withMessages([
                'otp_code' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode OTP baru.',
            ]);
        }

        if (! Hash::check($request->validated('otp_code'), $otp->code)) {
            $attempts = $otp->attempts + 1;

            if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
                $otp->update([
                    'attempts' => $attempts,
                    'locked_until' => now()->addMinutes(self::LOCKOUT_MINUTES),
                ]);

                throw ValidationException::withMessages([
                    'otp_code' => 'Anda salah memasukkan OTP 5 kali. Login OTP dikunci selama 15 menit.',
                ]);
            }

            $otp->update(['attempts' => $attempts]);
            $remaining = self::MAX_VERIFY_ATTEMPTS - $attempts;

            throw ValidationException::withMessages([
                'otp_code' => "Kode OTP tidak valid. Sisa percobaan: {$remaining} kali.",
            ]);
        }

        $otp->update(['verified_at' => now()]);

        $parentUser = $otp->user ?? User::findOrFail($otp->user_id);
        $variants = PhoneNumber::variants($normalizedPhone);

        $this->linkSiblingsByPhone($parentUser, $variants);

        RateLimiter::clear($this->rateLimitKey($normalizedPhone, (string) $request->ip()));

        Auth::login($parentUser, true);
        $request->session()->regenerate();
        $request->session()->forget('parent_otp_phone');

        if ($otp->student_id) {
            $request->session()->put('parent_active_student_id', $otp->student_id);
        }

        return redirect()->intended(route('ortu.dashboard', absolute: false));
    }

    private function ensureNotLocked(string $normalizedPhone): void
    {
        $lockedOtp = ParentOtp::query()
            ->where('phone_number', $normalizedPhone)
            ->whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->latest('locked_until')
            ->first();

        if ($lockedOtp) {
            $minutes = max(1, (int) ceil(now()->diffInSeconds($lockedOtp->locked_until) / 60));

            throw ValidationException::withMessages([
                'phone_number' => "Permintaan OTP dikunci sementara karena salah berulang. Coba lagi dalam {$minutes} menit.",
            ]);
        }
    }

    private function rateLimitKey(string $normalizedPhone, string $ip): string
    {
        return "parent-otp-send:{$normalizedPhone}|{$ip}";
    }

    /**
     * @param  list<string>  $variants
     */
    private function resolveOrCreateParentUser(Student $student, string $normalizedPhone, array $variants): User
    {
        $role = Role::firstOrCreate(['name' => 'Orang Tua', 'guard_name' => 'web']);

        $parentUser = $student->parent;

        if (! $parentUser) {
            $parentUser = User::query()->whereIn('phone', $variants)->first();
        }

        if (! $parentUser) {
            $studentName = $student->user?->name ?? 'Siswa';
            $parentName = $student->nama_ayah ?: ($student->nama_ibu ?: "Wali {$studentName}");
            $baseEmail = "ortu.{$normalizedPhone}@simse.local";
            $email = User::where('email', $baseEmail)->exists()
                ? 'ortu.'.Str::lower(Str::random(6)).".{$normalizedPhone}@simse.local"
                : $baseEmail;

            $parentUser = User::create([
                'name' => $parentName,
                'email' => $email,
                'phone' => $normalizedPhone,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
        } elseif (! $parentUser->phone) {
            $parentUser->update(['phone' => $normalizedPhone]);
        }

        if (! $parentUser->hasRole($role)) {
            $parentUser->assignRole($role);
        }

        $this->linkSiblingsByPhone($parentUser, $variants);

        if ($student->parent_user_id !== $parentUser->id) {
            $student->update(['parent_user_id' => $parentUser->id]);
        }

        return $parentUser;
    }

    /**
     * Link unlinked sibling records sharing the verified parent phone number.
     *
     * @param  list<string>  $variants
     */
    private function linkSiblingsByPhone(User $parentUser, array $variants): void
    {
        Student::query()
            ->whereNull('parent_user_id')
            ->whereIn('no_telp_ortu', $variants)
            ->update(['parent_user_id' => $parentUser->id]);
    }
}
