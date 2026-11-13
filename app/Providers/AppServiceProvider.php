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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
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

        Vite::prefetch(concurrency: 3);
    }
}
