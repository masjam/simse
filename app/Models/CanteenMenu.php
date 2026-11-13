<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenMenu extends Model
{
    use HasUuids;

    protected $fillable = [
        'canteen_stand_id',
        'name',
        'price',
        'stock',
        'description',
        'photo',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(CanteenStand::class, 'canteen_stand_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(CanteenOrderItem::class, 'canteen_menu_id');
    }
}
