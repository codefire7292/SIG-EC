<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chapter_id',
        'file_path',
        'student_comment',
        'grade',
        'trainer_feedback',
        'status',
        'answers',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'float',
            'user_id' => 'integer',
            'chapter_id' => 'integer',
            'answers' => 'json',
        ];
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /**
     * The student who made this submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The chapter this submission belongs to.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
