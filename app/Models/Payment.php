<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use Auditable, HasUuids;

    protected $fillable = [
        'bill_id',
        'user_id',
        'amount_paid',
        'payment_method',
        'receipt_photo',
        'payment_date',
        'status',
        'canceled_at',
        'canceled_by',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'float',
            'payment_date' => 'date',
            'canceled_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function canceler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }
}
