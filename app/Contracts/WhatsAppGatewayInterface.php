<?php

namespace App\Contracts;

interface WhatsAppGatewayInterface
{
    /**
     * Send a WhatsApp message and return delivery status details.
     *
     * @return array{success: bool, driver: string, status: string, response: ?string}
     */
    public function send(
        string $phoneNumber,
        string $message,
        ?string $recipientName = null,
        ?string $logId = null,
    ): array;
}
