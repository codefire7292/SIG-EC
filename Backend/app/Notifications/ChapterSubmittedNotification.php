<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ChapterGroupProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChapterSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ChapterGroupProgress $progress,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $group   = $this->progress->group;
        $chapter = $this->progress->chapter;
        $trainer = $this->progress->submitter;

        return (new MailMessage())
            ->subject('Chapitre soumis pour validation')
            ->greeting('Bonjour,')
            ->line("Le formateur **{$trainer->name}** a soumis le chapitre **\"{$chapter->titre}\"** pour le groupe **{$group->nom_groupe}**.")
            ->line('En tant que Responsable de Groupe, veuillez valider ce chapitre.')
            ->action('Voir le chapitre', url("/chapter-progress/{$this->progress->id}"))
            ->salutation('Cordialement, E-CRE Kolda');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'chapter_submitted',
            'progress_id'   => $this->progress->id,
            'group_id'      => $this->progress->group_id,
            'chapter_id'    => $this->progress->chapter_id,
            'chapter_title' => $this->progress->chapter->titre,
            'trainer_name'  => $this->progress->submitter->name,
            'group_name'    => $this->progress->group->nom_groupe,
        ];
    }
}
