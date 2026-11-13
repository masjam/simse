<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KioskDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'mac_address',
        'location',
        'is_active',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public static function normalizeMac(string $mac): string
    {
        $cleaned = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $mac) ?? '');
        if (strlen($cleaned) === 12) {
            return implode(':', str_split($cleaned, 2));
        }

        return strtoupper(trim($mac));
    }
}
