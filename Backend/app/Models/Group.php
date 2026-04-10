<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom_groupe',
        'module_id',
        'formateur_id',
        'responsable_groupe_id',
        'adjoint_groupe_id',
        'annee_academique',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_id'             => 'integer',
            'formateur_id'          => 'integer',
            'responsable_groupe_id' => 'integer',
            'adjoint_groupe_id'     => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /**
     * The module this group is trained on.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * The user acting as the trainer (formateur) for this group.
     */
    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /**
     * The user acting as the group supervisor (responsable), optional.
     */
    public function responsableGroupe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_groupe_id');
    }

    /**
     * The user acting as the group deputy (adjoint), optional.
     */
    public function adjointGroupe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjoint_groupe_id');
    }

    /**
     * Nominations for group supervisor within this group.
     */
    public function nominations(): HasMany
    {
        return $this->hasMany(Nomination::class);
    }

    /**
     * Chapter progress entries for this group.
     */
    public function chapterProgress(): HasMany
    {
        return $this->hasMany(ChapterGroupProgress::class);
    }

    /**
     * Students (apprenants) assigned to this group.
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    /**
     * Alias for students() — used by some controllers.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->students();
    }
}
