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
            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['remember_token']);

            if (!empty($changes)) {
                $original = array_intersect_key($model->getOriginal(), $changes);
                $model->recordAuditLog('modification', [
                    'changes' => $changes,
                    'original' => $original,
                ]);
            }
        });

        static::deleted(function ($model) {
            $model->recordAuditLog('suppression');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->recordAuditLog('restauration');
            });
        }
    }

    /**
     * Record an audit log for this model.
     */
    public function recordAuditLog(string $action, ?array $extraMetadata = [])
    {
        $user = Auth::user();
        $ip = request()?->ip() ?? '127.0.0.1';
        $userAgent = request()?->userAgent() ?? 'System';

        $baseMetadata = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->getRoleNames()->first(),
        ];

        if (method_exists($this, 'getAuditTargetSummary')) {
            $baseMetadata['target_summary'] = $this->getAuditTargetSummary();
        } elseif (isset($this->reference_number)) {
            $baseMetadata['target_summary'] = class_basename($this) . ' #' . $this->reference_number;
        } elseif (isset($this->certificate_number)) {
            $baseMetadata['target_summary'] = class_basename($this) . ' #' . $this->certificate_number;
        } elseif (isset($this->name)) {
            $baseMetadata['target_summary'] = class_basename($this) . ' ' . $this->name;
        }

        $metadata = array_merge($baseMetadata, $extraMetadata ?? []);

        return AuditLog::create([
            'user_id' => $user?->id ?? Auth::id(),
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
