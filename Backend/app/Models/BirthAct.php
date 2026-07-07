<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\AuditLog;

class BirthAct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registry_id',
        'certificate_path',
        'reference_number',
        'uuid',
        'first_name',
        'last_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'is_judgment',
        'judgment_number',
        'judgment_date',
        'judgment_court',
        'father_name',
        'mother_name',
        'parents_metadata',
        'officer_comments',
        // NOTE: status, validated_by, validated_at, locked_at, is_locked, parent_id, version_number,
        // rectification_reason, is_current are managed by the system only — never from user HTTP input.
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_judgment' => 'boolean',
        'judgment_date' => 'date',
        'parents_metadata' => 'array',
        'validated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BirthAct::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BirthAct::class, 'parent_id');
    }

    public function versions()
    {
        return BirthAct::where('reference_number', $this->reference_number)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function recordAuditLog(string $action): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => self::class,
            'auditable_id' => $this->id,
            'metadata' => [
                'status' => $this->status,
                'reference' => $this->reference_number,
                'version' => $this->version_number,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
