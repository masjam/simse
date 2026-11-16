<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use Auditable, HasUuids;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'reference_id',
        'description',
        'status',
        'canceled_at',
        'canceled_by',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'canceled_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function canceler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }
}
