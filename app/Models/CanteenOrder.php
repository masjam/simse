<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanteenOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'canteen_stand_id',
        'student_id',
        'total_price',
        'status',
        'payment_method',
        'qr_code_pickup',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'float',
        ];
    }

    public function stand(): BelongsTo
    {
        return $this->belongsTo(CanteenStand::class, 'canteen_stand_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CanteenOrderItem::class, 'canteen_order_id');
    }
}
