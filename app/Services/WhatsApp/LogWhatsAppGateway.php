<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppGatewayInterface;
use App\Models\WhatsappLog;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;

class LogWhatsAppGateway implements WhatsAppGatewayInterface
{
    /**
     * Log the WhatsApp message locally for development and testing.
     *
     * @return array{success: bool, driver: string, status: string, response: ?string}
     */
    public function send(
        string $phoneNumber,
        string $message,
        ?string $recipientName = null,
        ?string $logId = null,
    ): array {
        $normalizedPhone = PhoneNumber::normalize($phoneNumber);
        $responseNote = 'Driver log: pesan simulasi tersimpan di log aplikasi.';

        Log::info('[WhatsApp Log Gateway]', [
            'recipient_name' => $recipientName,
            'phone_number' => $normalizedPhone,
            'message' => $message,
        ]);

        if ($logId) {
            WhatsappLog::query()->where('id', $logId)->update([
                'phone_number' => $normalizedPhone,
                'status' => 'Terkirim',
                'response_log' => $responseNote,
            ]);
        } else {
            WhatsappLog::create([
                'recipient_name' => $recipientName,
                'phone_number' => $normalizedPhone,
                'message' => $message,
                'status' => 'Terkirim',
                'response_log' => $responseNote,
            ]);
        }

        return [
            'success' => true,
            'driver' => 'log',
            'status' => 'Terkirim',
            'response' => $responseNote,
        ];
    }
}
