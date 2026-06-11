<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'label',
    ];

    /**
     * Get the parent attachable model (BirthAct, MarriageAct, etc.).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
