<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registry extends Model
{
    use HasFactory;

    protected $fillable = [
        'civil_registration_center_id',
        'type',
        'year',
        'reference_prefix',
        'last_number',
        'status',
        'opening_date',
        'closing_date',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'closing_date' => 'date',
        'year' => 'integer',
        'last_number' => 'integer',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(CivilRegistrationCenter::class, 'civil_registration_center_id');
    }

    public function birthActs(): HasMany
    {
        return $this->hasMany(BirthAct::class);
    }

    public function marriageActs(): HasMany
    {
        return $this->hasMany(MarriageAct::class);
    }

    public function deathActs(): HasMany
    {
        return $this->hasMany(DeathAct::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(CivilCertificate::class);
    }
}
