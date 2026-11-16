<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ParentOtpController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login');

    // 2FA Challenge saat login
    Route::get('two-factor-challenge', [TwoFactorAuthenticationController::class, 'challenge'])
        ->name('two-factor.login');

    Route::post('two-factor-challenge', [TwoFactorAuthenticationController::class, 'verifyChallenge'])
        ->middleware('throttle:login')
        ->name('two-factor.verify');

    Route::get('{role}/login', [AuthenticatedSessionController::class, 'create'])
        ->whereIn('role', ['siswa', 'guru', 'ortu', 'admin', 'staf', 'bendahara', 'kantin', 'ppdb', 'bk'])
        ->name('role.login');

    Route::post('{role}/login', [AuthenticatedSessionController::class, 'store'])
        ->whereIn('role', ['siswa', 'guru', 'ortu', 'admin', 'staf', 'bendahara', 'kantin', 'ppdb', 'bk'])
        ->middleware('throttle:login')
        ->name('role.login.store');

    Route::get('ortu/otp', [ParentOtpController::class, 'create'])
        ->name('ortu.otp');

    Route::post('ortu/otp/request', [ParentOtpController::class, 'requestOtp'])
        ->middleware('throttle:parent_otp')
        ->name('ortu.otp.request');

    Route::post('ortu/otp/verify', [ParentOtpController::class, 'verifyOtp'])
        ->name('ortu.otp.verify');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Manajemen 2FA (TOTP)
    Route::post('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.enable');

    Route::post('user/confirmed-two-factor-authentication', [TwoFactorAuthenticationController::class, 'confirm'])
        ->name('two-factor.confirm');

    Route::delete('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->name('two-factor.disable');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
