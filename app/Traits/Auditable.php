<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static array $auditExclude = [];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->logAudit('created', [], $model->getAuditableAttributes());
        });

        static::updated(function (Model $model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            $oldValues = array_intersect_key($original, $changes);
            $newValues = array_intersect_key($model->getAttributes(), $changes);

            unset($oldValues['updated_at'], $newValues['updated_at']);

            if (!empty($oldValues) || !empty($newValues)) {
                $model->logAudit('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            $model->logAudit('deleted', $model->getAuditableAttributes(), []);
        });
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function logAudit(string $event, array $oldValues, array $newValues): void
    {
        $excludedFields = array_merge(
            ['password', 'remember_token'],
            static::$auditExclude
        );

        $oldValues = array_diff_key($oldValues, array_flip($excludedFields));
        $newValues = array_diff_key($newValues, array_flip($excludedFields));

        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    protected function getAuditableAttributes(): array
    {
        $excludedFields = array_merge(
            ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'],
            static::$auditExclude
        );

        return array_diff_key($this->getAttributes(), array_flip($excludedFields));
    }
}
