<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'type_activite',
        'date',
        'audience_estimee',
        'image_path',
        'description',
        'status',
        'lieu',
        'heure_debut',
        'heure_fin',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'audience_estimee' => 'integer',
        ];
    }
}
