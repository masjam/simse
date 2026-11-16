<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentNotificationPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'channel_wa',
        'channel_in_app',
        'notify_attendance',
        'notify_finance',
        'notify_grades',
        'notify_discipline',
    ];

    protected function casts(): array
    {
        return [
            'channel_wa' => 'boolean',
            'channel_in_app' => 'boolean',
            'notify_attendance' => 'boolean',
            'notify_finance' => 'boolean',
            'notify_grades' => 'boolean',
            'notify_discipline' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
