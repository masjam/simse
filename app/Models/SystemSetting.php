<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    use Auditable, HasUuids;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('system_settings')) {
            return $default;
        }

        $record = static::query()->where('key', $key)->first();

        return $record?->value ?? $default;
    }

    public static function setValue(string $key, ?string $value, ?string $description = null): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'description' => $description,
            ], fn (mixed $v): bool => $v !== null || $key === 'value') + ['value' => $value]
        );
    }
}
