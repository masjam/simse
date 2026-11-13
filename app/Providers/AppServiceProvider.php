<?php

namespace App\Providers;

use App\Contracts\WhatsAppGatewayInterface;
use App\Models\SystemSetting;
use App\Services\WhatsApp\FonnteWhatsAppGateway;
use App\Services\WhatsApp\LogWhatsAppGateway;
use App\Services\WhatsApp\WablasWhatsAppGateway;
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
        Vite::prefetch(concurrency: 3);
    }
}
