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
use App\Traits\HasAuditLogs;

class MarriageAct extends Model
{
    use HasFactory, SoftDeletes, HasAuditLogs;

    protected $fillable = [
        'registry_id',
        'certificate_path',
        'reference_number',
        'uuid',
        'husband_first_name',
        'husband_last_name',
        'wife_first_name',
        'wife_last_name',
        'marriage_date',
        'marriage_place',
        'marriage_option',
        'matrimonial_regime',
        'witnesses_metadata',
        'officer_comments',
        'is_judgment',
        'judgment_number',
        'judgment_date',
        'doc_cni_husband_path',
        'doc_cni_wife_path',
        'doc_birth_husband_path',
        'doc_birth_wife_path',
        'doc_domicile_path',
        'doc_medical_path',
        'doc_parental_auth_path',
        'doc_jugement_path',
        'doc_autres_path',
        'spouses_metadata',
        'created_by',
        // NOTE: status, validated_by, validated_at, locked_at etc. managed by the system only.
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'judgment_date' => 'date',
        'is_judgment' => 'boolean',
        'witnesses_metadata' => 'array',
        'spouses_metadata' => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function getAuditTargetSummary(): string
    {
        $spouses = trim(($this->husband_first_name ?? '') . ' ' . ($this->husband_last_name ?? '') . ' & ' . ($this->wife_first_name ?? '') . ' ' . ($this->wife_last_name ?? ''));
        return "Acte de Mariage N° {$this->reference_number}" . ($spouses ? " ({$spouses})" : '');
    }
}
