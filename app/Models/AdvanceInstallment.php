<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceInstallment extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'advance_id',
        'installment_number',
        'amount',
        'month',
        'year',
        'status',
        'payroll_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'installment_number' => 'integer',
            'amount' => 'float',
            'paid_at' => 'datetime',
        ];
    }

    public function advance(): BelongsTo
    {
        return $this->belongsTo(Advance::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
