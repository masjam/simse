<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\SystemSetting;
use App\Models\WhatsappLog;
use App\Support\PhoneNumber;

class WhatsAppService
{
    public function isEnabled(): bool
    {
        return (string) SystemSetting::getValue('wa_is_active', '1') !== '0';
    }

    public function queue(string $phoneNumber, string $message, ?string $recipientName = null): ?WhatsappLog
    {
        $normalizedPhone = PhoneNumber::normalize($phoneNumber);

        if ($normalizedPhone === '' || ! $this->isEnabled()) {
            return null;
        }

        $log = WhatsappLog::create([
            'recipient_name' => $recipientName,
            'phone_number' => $normalizedPhone,
            'message' => $message,
            'status' => 'Pending',
            'response_log' => 'Menunggu antrean pengiriman...',
        ]);

        SendWhatsAppNotificationJob::dispatch($log->id);

        return $log;
    }
}
