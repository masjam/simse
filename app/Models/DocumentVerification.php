<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVerification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'token',
        'document_type',
        'reference_type',
        'reference_id',
        'title',
        'recipient_name',
        'content_hash',
        'metadata',
        'issued_by',
        'issued_at',
        'is_revoked',
        'revoked_at',
        'revoked_by',
        'revoked_reason',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'issued_at' => 'datetime',
            'is_revoked' => 'boolean',
            'revoked_at' => 'datetime',
        ];
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Get masked recipient name (e.g., "A***d S***a") for public verification privacy.
     */
    public function getMaskedRecipientNameAttribute(): string
    {
        $words = explode(' ', trim($this->recipient_name));
        $maskedWords = array_map(function ($word) {
            $length = mb_strlen($word);
            if ($length <= 2) {
                return $word;
            }
            $first = mb_substr($word, 0, 1);
            $last = mb_substr($word, -1);

            return $first.str_repeat('*', max(1, $length - 2)).$last;
        }, $words);

        return implode(' ', $maskedWords);
    }

    public function revoke(User $user, string $reason): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_by' => $user->id,
            'revoked_reason' => $reason,
        ]);
    }
}
