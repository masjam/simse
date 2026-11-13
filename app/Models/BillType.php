<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'amount',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
