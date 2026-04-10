<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupRoleChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $groupName,
        public string $roleLabel,
        public string $action, // 'attribué' or 'retiré'
        public string $userName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'group_role_changed',
            'title'   => "Mise à jour du leadership",
            'message' => "Le rôle de {$this->roleLabel} a été {$this->action} à {$this->userName} dans le groupe {$this->groupName}.",
            'group_name' => $this->groupName,
        ];
    }
}
