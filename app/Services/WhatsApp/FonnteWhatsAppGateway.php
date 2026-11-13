<?php

namespace App\Services\WhatsApp;

class FonnteWhatsAppGateway extends AbstractHttpWhatsAppGateway
{
    public function __construct(
        ?string $token,
        string $endpoint = 'https://api.fonnte.com/send',
    ) {
        parent::__construct($token, $endpoint);
    }

    protected function driverName(): string
    {
        return 'fonnte';
    }

    protected function missingTokenMessage(): string
    {
        return 'Token Fonnte belum dikonfigurasi.';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(string $normalizedPhone, string $message): array
    {
        return [
            'target' => $normalizedPhone,
            'message' => $message,
        ];
    }
}
