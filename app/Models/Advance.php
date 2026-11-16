<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    use Auditable, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'installment_count',
        'monthly_amount',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'disbursed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'installment_count' => 'integer',
            'monthly_amount' => 'float',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(AdvanceInstallment::class);
    }
}
