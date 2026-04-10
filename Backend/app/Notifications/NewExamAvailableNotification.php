<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewExamAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Exam $exam,
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
        return (new MailMessage())
            ->subject('Nouvel examen disponible')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Un nouvel examen intitulé **\"{$this->exam->titre}\"** a été publié pour le module **{$this->exam->module->titre}**.")
            ->line("L'épreuve est prévue pour le " . ($this->exam->scheduled_at ? $this->exam->scheduled_at->format('d/m/Y à H:i') : 'immédiat') . ".")
            ->action('Se connecter pour voir', url('/login'))
            ->line('Préparez-vous bien !')
            ->salutation('Cordialement, L’équipe E-CRE Kolda');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_exam_available',
            'title' => 'Nouvel Examen Disponible',
            'message' => "Un nouvel examen \"{$this->exam->titre}\" est disponible pour le module {$this->exam->module->titre}.",
            'exam_id' => $this->exam->id,
            'exam_title' => $this->exam->titre,
            'module_id' => $this->exam->module_id,
            'module_title' => $this->exam->module->titre,
            'scheduled_at' => $this->exam->scheduled_at ? $this->exam->scheduled_at->toISOString() : null,
        ];
    }
}
