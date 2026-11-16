<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    /**
     * URL verifikasi Cloudflare Turnstile.
     */
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Periksa apakah verifikasi Turnstile aktif di lingkungan saat ini.
     */
    public function isEnabled(): bool
    {
        return (bool) config('services.turnstile.enabled', false);
    }

    /**
     * Dapatkan site key untuk widget frontend.
     */
    public function getSiteKey(): string
    {
        return (string) config('services.turnstile.site_key', '');
    }

    /**
     * Verifikasi token Turnstile dari request pengguna.
     */
    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $secretKey = config('services.turnstile.secret_key');
        if (empty($secretKey)) {
            Log::warning('Turnstile diaktifkan tetapi TURNSTILE_SECRET_KEY belum dikonfigurasi.');

            return true;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return (bool) ($data['success'] ?? false);
            }

            Log::error('Permintaan verifikasi Turnstile gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Exception saat memverifikasi Turnstile: '.$e->getMessage());

            return false;
        }
    }
}
