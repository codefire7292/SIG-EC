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

class DeathAct extends Model
{
    use HasFactory, SoftDeletes, HasAuditLogs;

    protected $fillable = [
        'registry_id',
        'reference_number',
        'uuid',
        'deceased_first_name',
        'deceased_last_name',
        'gender',
        'date_of_birth',
        'birth_date_type',
        'birth_year',
        'presumed_age',
        'date_of_death',
        'time_of_death',
        'place_of_death',
        'health_facility',
        'act_registration_date',
        'cause_of_death',
        'is_judgment',
        'judgment_number',
        'judgment_date',
        'judgment_court',
        'doc_death_cert_path',
        'doc_deceased_id_path',
        'doc_declarant_id_path',
        'doc_jugement_path',
        'doc_autres_path',
        'death_metadata',
        'witnesses_metadata',
        'officer_comments',
        'created_by',
    ];

    protected $casts = [
        'date_of_death' => 'date',
        'date_of_birth' => 'date',
        'birth_year' => 'integer',
        'presumed_age' => 'integer',
        'act_registration_date' => 'date',
        'judgment_date' => 'date',
        'is_judgment' => 'boolean',
        'death_metadata' => 'array',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DeathAct::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DeathAct::class, 'parent_id');
    }

    public function versions()
    {
        return DeathAct::where('reference_number', $this->reference_number)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function getAuditTargetSummary(): string
    {
        $name = trim(($this->deceased_first_name ?? '') . ' ' . ($this->deceased_last_name ?? ''));
        return "Acte de Décès N° {$this->reference_number}" . ($name ? " ({$name})" : '');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
