<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'lien_externe',
        'fichier_path',
        'description',
    ];
}
