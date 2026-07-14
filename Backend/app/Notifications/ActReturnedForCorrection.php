<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActReturnedForCorrection extends Notification implements ShouldQueue
{
    use Queueable;

    protected $actType;
    protected $actId;
    protected $referenceNumber;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $actType, $actId, string $referenceNumber)
    {
        $this->actType = $actType;
        $this->actId = $actId;
        $this->referenceNumber = $referenceNumber;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Only use the database channel for internal in-app notifications
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeName = '';
        switch ($this->actType) {
            case 'naissance': $typeName = 'Naissance'; break;
            case 'mariage': $typeName = 'Mariage'; break;
            case 'deces': $typeName = 'Décès'; break;
        }

        return [
            'title' => 'Acte renvoyé à la correction',
            'message' => "L'acte de {$typeName} (Réf: {$this->referenceNumber}) a été renvoyé à la correction par l'officier. Veuillez le revoir.",
            'type' => $this->actType,
            'act_id' => $this->actId,
            'url' => "/acts/{$this->actType}/{$this->actId}/edit",
        ];
    }
}
