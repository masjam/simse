<?php

namespace App\Jobs;

use App\Contracts\WhatsAppGatewayInterface;
use App\Models\WhatsappLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 60, 180];

    public function __construct(
        public readonly string $whatsappLogId,
    ) {}

    public function handle(WhatsAppGatewayInterface $gateway): void
    {
        $log = WhatsappLog::find($this->whatsappLogId);

        if (! $log) {
            return;
        }

        $result = $gateway->send(
            phoneNumber: $log->phone_number,
            message: $log->message,
            recipientName: $log->recipient_name,
            logId: $log->id,
        );

        if (! ($result['success'] ?? false)) {
            throw new RuntimeException($result['response'] ?? 'Pengiriman WhatsApp gagal.');
        }
    }

    public function failed(?Throwable $exception): void
    {
        WhatsappLog::query()
            ->where('id', $this->whatsappLogId)
            ->update([
                'status' => 'Gagal',
                'response_log' => $exception?->getMessage() ?? 'Gagal setelah batas percobaan ulang.',
            ]);
    }
}
