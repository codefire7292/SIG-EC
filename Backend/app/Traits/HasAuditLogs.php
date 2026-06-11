<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAuditLogs
{
    /**
     * Boot the trait.
     */
    protected static function bootHasAuditLogs()
    {
        static::created(function ($model) {
            $model->recordAuditLog('creation');
        });

        static::updated(function ($model) {
            $model->recordAuditLog('modification', [
                'changes' => $model->getChanges(),
                'original' => array_intersect_key($model->getOriginal(), $model->getChanges()),
            ]);
        });

        static::deleted(function ($model) {
            $model->recordAuditLog('suppression');
        });
    }

    /**
     * Record an audit log for this model.
     */
    public function recordAuditLog(string $action, ?array $extraMetadata = [])
    {
        $metadata = array_merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $extraMetadata);

        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_id' => $this->id,
            'auditable_type' => get_class($this),
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get the audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}
