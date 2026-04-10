<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification
{
    use Queueable;

    protected $certificate;
    protected $module;

    /**
     * Create a new notification instance.
     */
    public function __construct($certificate, $module)
    {
        $this->certificate = $certificate;
        $this->module = $module;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'certificate_issued',
            'title' => '🎓 Félicitations !',
            'message' => "Vous avez obtenu votre attestation pour le module : {$this->module->titre}",
            'certificate_uuid' => $this->certificate->uuid,
            'module_name' => $this->module->titre,
            'action_url' => route('certificates.view', $this->certificate->uuid),
        ];
    }
}
