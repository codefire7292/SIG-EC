<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CivilRegistrationCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'commune',
        'region',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the annual registries for this center.
     */
    public function registries(): HasMany
    {
        return $this->hasMany(Registry::class);
    }

    /**
     * Get the certificates issued at this center.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(CivilCertificate::class);
    }
}
