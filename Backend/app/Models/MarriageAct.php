<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\AuditLog;

class MarriageAct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registry_id',
        'reference_number',
        'uuid',
        'husband_first_name',
        'husband_last_name',
        'wife_first_name',
        'wife_last_name',
        'marriage_date',
        'marriage_place',
        'witnesses_metadata',
        'officer_comments',
        // NOTE: status, validated_by, validated_at, locked_at etc. managed by the system only.
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'witnesses_metadata' => 'array',
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

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MarriageAct::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MarriageAct::class, 'parent_id');
    }

    public function versions()
    {
        return MarriageAct::where('reference_number', $this->reference_number)
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
