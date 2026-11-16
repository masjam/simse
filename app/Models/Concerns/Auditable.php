<?php

namespace App\Models\Concerns;

use App\Models\Attendance;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            if ($model->shouldAudit('created')) {
                $model->recordAuditLog('created', null, $model->getAuditAttributes($model->getAttributes()));
            }
        });

        static::updated(function (Model $model) {
            if ($model->shouldAudit('updated')) {
                $dirty = $model->getDirty();
                $original = Arr::only($model->getOriginal(), array_keys($dirty));

                $model->recordAuditLog(
                    'updated',
                    $model->getAuditAttributes($original),
                    $model->getAuditAttributes($dirty)
                );
            }
        });

        static::deleted(function (Model $model) {
            if ($model->shouldAudit('deleted')) {
                $model->recordAuditLog('deleted', $model->getAuditAttributes($model->getOriginal()), null);
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                if ($model->shouldAudit('restored')) {
                    $model->recordAuditLog('restored', null, $model->getAuditAttributes($model->getAttributes()));
                }
            });
        }
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Fields excluded from audit logs for security and privacy.
     */
    protected function getAuditBlacklist(): array
    {
        return [
            'password',
            'remember_token',
            'pin',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'token',
            'verification_token',
            'updated_at',
        ];
    }

    protected function getAuditAttributes(array $attributes): array
    {
        return Arr::except($attributes, $this->getAuditBlacklist());
    }

    protected function shouldAudit(string $event): bool
    {
        // Khusus Attendance: hanya audit jika perubahan dilakukan setelah hari H
        if ($this instanceof Attendance && $event === 'updated') {
            $attendanceDate = $this->date ? Carbon::parse($this->date)->startOfDay() : null;
            if ($attendanceDate && $attendanceDate->isToday()) {
                return false;
            }
        }

        return true;
    }

    public function recordAuditLog(string $event, ?array $oldValues, ?array $newValues): ?AuditLog
    {
        if ($event === 'updated' && empty($newValues)) {
            return null;
        }

        return AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => (string) $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
