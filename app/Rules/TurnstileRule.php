<?php

namespace App\Rules;

use App\Services\Security\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurnstileRule implements ValidationRule
{
    public function __construct(
        private readonly ?string $remoteIp = null,
        private readonly ?TurnstileService $turnstileService = null,
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = $this->turnstileService ?? app(TurnstileService::class);

        if (! $service->isEnabled()) {
            return;
        }

        $token = is_string($value) ? $value : '';

        if (! $service->verify($token, $this->remoteIp)) {
            $fail('Verifikasi keamanan bot (Cloudflare Turnstile) tidak valid atau telah kedaluwarsa. Silakan ulangi.');
        }
    }
}
