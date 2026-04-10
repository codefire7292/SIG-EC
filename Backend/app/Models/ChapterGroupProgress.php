<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterGroupProgress extends Model
{
    use HasFactory;

    protected $table = 'chapter_group_progress';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group_id',
        'chapter_id',
        'status',
        'submitted_by',
        'validated_by',
        'validated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group_id'     => 'integer',
            'chapter_id'   => 'integer',
            'submitted_by' => 'integer',
            'validated_by' => 'integer',
            'validated_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The trainer who submitted the chapter.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * The group supervisor who validated the chapter.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
