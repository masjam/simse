<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollComponent extends Model
{
    protected $fillable = [
        'payroll_id',
        'type',
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
