<?php

namespace App\Services\WhatsApp;

class WablasWhatsAppGateway extends AbstractHttpWhatsAppGateway
{
    public function __construct(
        ?string $token,
        string $endpoint = 'https://solo.wablas.com/api/send-message',
    ) {
        parent::__construct($token, $endpoint);
    }

    protected function driverName(): string
    {
        return 'wablas';
    }

    protected function missingTokenMessage(): string
    {
        return 'Token Wablas belum dikonfigurasi.';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(string $normalizedPhone, string $message): array
    {
        return [
            'phone' => $normalizedPhone,
            'message' => $message,
        ];
    }
}
