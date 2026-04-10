<?php

declare(strict_types=1);

namespace App\Http\Requests\ChapterProgress;

use App\Models\ChapterGroupProgress;
use Illuminate\Foundation\Http\FormRequest;

class ApproveChapterProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Anti-IDOR: only the approved "Responsable Groupe" of this specific group
     * (with the validate-chapters permission) can approve.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        // Must be the responsable of the group linked to this progress entry OR a student in the group
        /** @var ChapterGroupProgress $progress */
        $progress = $this->route('chapterGroupProgress');

        if (!$progress || !$progress->group) {
            return false;
        }

        if ($user->can('validate-chapters') && $progress->group->responsable_groupe_id === $user->id) {
            return true;
        }

        if ($user->hasRole('Apprenant') && $progress->group->students()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
