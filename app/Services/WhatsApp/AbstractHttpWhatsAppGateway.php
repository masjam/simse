<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppGatewayInterface;
use App\Models\WhatsappLog;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Throwable;

abstract class AbstractHttpWhatsAppGateway implements WhatsAppGatewayInterface
{
    public function __construct(
        protected readonly ?string $token,
        protected readonly string $endpoint,
    ) {}

    abstract protected function driverName(): string;

    abstract protected function missingTokenMessage(): string;

    /**
     * Build provider-specific HTTP payload.
     *
     * @return array<string, mixed>
     */
    abstract protected function payload(string $normalizedPhone, string $message): array;

    /**
     * Send a WhatsApp message via the configured HTTP provider.
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
        $driver = $this->driverName();

        if (blank($this->token)) {
            $error = $this->missingTokenMessage();
            $this->recordLog($normalizedPhone, $message, $recipientName, 'Gagal', $error, $logId);

            return [
                'success' => false,
                'driver' => $driver,
                'status' => 'Gagal',
                'response' => $error,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->timeout(10)->post($this->endpoint, $this->payload($normalizedPhone, $message));

            $body = $response->body();
            $json = $response->json();
            $isSuccess = $response->successful() && (bool) ($json['status'] ?? false);
            $status = $isSuccess ? 'Terkirim' : 'Gagal';

            $this->recordLog($normalizedPhone, $message, $recipientName, $status, $body, $logId);

            return [
                'success' => $isSuccess,
                'driver' => $driver,
                'status' => $status,
                'response' => $body,
            ];
        } catch (Throwable $exception) {
            $this->recordLog($normalizedPhone, $message, $recipientName, 'Gagal', $exception->getMessage(), $logId);

            return [
                'success' => false,
                'driver' => $driver,
                'status' => 'Gagal',
                'response' => $exception->getMessage(),
            ];
        }
    }

    protected function recordLog(
        string $phoneNumber,
        string $message,
        ?string $recipientName,
        string $status,
        ?string $responseLog,
        ?string $logId = null,
    ): void {
        if ($logId) {
            WhatsappLog::query()->where('id', $logId)->update([
                'phone_number' => $phoneNumber,
                'status' => $status,
                'response_log' => $responseLog,
            ]);

            return;
        }

        WhatsappLog::create([
            'recipient_name' => $recipientName,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'status' => $status,
            'response_log' => $responseLog,
        ]);
    }
}
