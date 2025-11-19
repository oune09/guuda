<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class alerteNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Alerte  $alerte)
    {
        $this->alerte = $alerte;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
       return [
            'alerte_id' => $this->alerte->id,
            'titre' => "🚨 {$this->getNiveauText()} - {$this->alerte->ville}",
            'message' => $this->alerte->message_alerte,
            'niveau' => $this->alerte->niveau_alerte,
            'ville' => $this->alerte->ville,
            'secteur' => $this->alerte->secteur,
            'quartier' => $this->alerte->quartier,
            'date_alerte' => $this->alerte->date_alerte->format('d/m/Y H:i'),
            'type' => 'nouvelle_alerte',
            'url' => "/alertes/{$this->alerte->id}",
            'icon' => $this->getIcon(),
            'couleur' => $this->getCouleur(),
        ];
    }

    private function getNiveauText()
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => 'URGENCE',
            'avertissement' => 'Avertissement',
            default => 'Information'
        };
    }

    private function getIcon()
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => '🚨',
            'avertissement' => '⚠️',
            default => 'ℹ️'
        };
    }

    private function getCouleur()
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => '#d63142ff',
            'avertissement' => '#ffc107',
            default => '#17a2b8'
        };
    }
}
