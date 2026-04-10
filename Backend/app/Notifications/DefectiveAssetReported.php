<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DefectiveAssetReported extends Notification
{
    use Queueable;

    protected Asset $asset;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
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
            'type' => 'critical',
            'title' => 'Matériel défectueux signalé',
            'message' => "Le matériel {$this->asset->nom} ({$this->asset->serie}) a été signalé comme étant : {$this->asset->etat}.",
            'asset_id' => $this->asset->id,
            'action_url' => '/logistics/assets',
        ];
    }
}
