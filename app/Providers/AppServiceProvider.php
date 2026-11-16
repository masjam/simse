<?php

namespace App\Providers;

use App\Contracts\WhatsAppGatewayInterface;
use App\Models\Bill;
use App\Models\BookLoan;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SystemSetting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Policies\BillPolicy;
use App\Policies\BookLoanPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\WalletPolicy;
use App\Policies\WalletTransactionPolicy;
use App\Services\WhatsApp\FonnteWhatsAppGateway;
use App\Services\WhatsApp\LogWhatsAppGateway;
use App\Services\WhatsApp\WablasWhatsAppGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppGatewayInterface::class, function (): WhatsAppGatewayInterface {
            $driver = (string) SystemSetting::getValue('wa_driver', config('services.whatsapp.driver', 'log'));

            return match ($driver) {
                'fonnte' => new FonnteWhatsAppGateway(
                    token: SystemSetting::getValue('wa_fonnte_token', config('services.whatsapp.fonnte.token')),
                    endpoint: (string) SystemSetting::getValue('wa_fonnte_endpoint', config('services.whatsapp.fonnte.endpoint', 'https://api.fonnte.com/send')),
                ),
                'wablas' => new WablasWhatsAppGateway(
                    token: SystemSetting::getValue('wa_wablas_token', config('services.whatsapp.wablas.token')),
                    endpoint: (string) SystemSetting::getValue('wa_wablas_endpoint', config('services.whatsapp.wablas.endpoint', 'https://solo.wablas.com/api/send-message')),
                ),
                default => new LogWhatsAppGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
        Model::preventLazyLoading(! app()->isProduction());

        Gate::policy(Payroll::class, PayrollPolicy::class);
        Gate::policy(Wallet::class, WalletPolicy::class);
        Gate::policy(WalletTransaction::class, WalletTransactionPolicy::class);
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(BookLoan::class, BookLoanPolicy::class);

        $this->configureRateLimiting();

        Vite::prefetch(concurrency: 3);
    }

    /**
     * Konfigurasi Rate Limiting terpusat untuk keamanan sistem.
     */
    private function configureRateLimiting(): void
    {
        // 1. Login rate limit (5x per menit per email + IP)
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');
            $key = 'login|'.$request->ip().'|'.strtolower($email);

            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'email' => 'Terlalu banyak percobaan masuk. Silakan tunggu 1 menit lagi demi keamanan akun Anda.',
                ])->with('error', 'Terlalu banyak percobaan masuk. Silakan tunggu 1 menit lagi.');
            });
        });

        // 2. Permintaan OTP Orang Tua (3x per 5 menit per nomor HP/IP)
        RateLimiter::for('parent_otp', function (Request $request) {
            $phone = (string) $request->input('phone_number', $request->input('phone', ''));
            $key = 'otp|'.$request->ip().'|'.$phone;

            return Limit::perMinutes(5, 3)->by($key)->response(function () {
                return back()->withErrors([
                    'phone_number' => 'Terlalu banyak permintaan kode OTP. Silakan tunggu 5 menit sebelum mencoba kembali.',
                ]);
            });
        });

        // 3. Verifikasi PIN Dompet & Kasir Kantin (3x per menit per IP/Wallet)
        RateLimiter::for('pin_verify', function (Request $request) {
            $key = 'pin_verify|'.$request->ip().'|'.($request->input('wallet_id', $request->user()?->id ?? 'guest'));

            return Limit::perMinute(3)->by($key)->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan PIN keliru. Akses verifikasi ditangguhkan selama 1 menit.',
                ], 429);
            });
        });

        // 4. Verifikasi Dokumen Publik (30x per menit per IP)
        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())->response(function () {
                abort(429, 'Terlalu banyak permintaan verifikasi dokumen.');
            });
        });

        // 5. Unduhan Berkas Privat (60x per menit per User/IP)
        RateLimiter::for('file_download', function (Request $request) {
            $key = 'download|'.($request->user()?->id ?? $request->ip());

            return Limit::perMinute(60)->by($key)->response(function () {
                abort(429, 'Batas unduhan berkas terlampaui. Silakan tunggu sejenak.');
            });
        });
    }
}
