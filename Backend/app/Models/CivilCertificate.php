<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CivilCertificateType;
use App\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CivilCertificate extends Model
{
    use HasFactory, HasAuditLogs;

    protected $fillable = [
        'civil_registration_center_id',
        'registry_id',
        'uuid',
        'type',
        'center',
        'reference_number',
        'applicant_first_name',
        'applicant_last_name',
        'applicant_cni',
        'data',
        'officer_comments',
        // NOTE: status, validated_by, is_signed, pdf_path, signature_path, rejection_reason,
        // parent_id, version_number, rectification_reason, is_current are managed by the system only.
    ];

    protected function casts(): array
    {
        return [
            'type' => CivilCertificateType::class,
            'data' => 'array',
            'is_signed' => 'boolean',
            'validated_at' => 'datetime',
            'validated_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CivilCertificate $cert): void {
            if (empty($cert->uuid)) {
                $cert->uuid = (string) Str::uuid();
            }
        });

        static::updating(function (CivilCertificate $cert): void {
            if ($cert->getOriginal('status') === 'signe') {
                throw new \Exception("Cet acte est signé et définitivement immuable.");
            }
        });

        static::deleting(function (CivilCertificate $cert): void {
            if ($cert->status === 'signe') {
                throw new \Exception("Impossible de supprimer un acte signé.");
            }
        });
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(CivilRegistrationCenter::class, 'civil_registration_center_id');
    }

    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class);
    }

    /**
     * Get all attachments for this certificate.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('version_number', 'asc');
    }
}
