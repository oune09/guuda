<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Alerte;

class AlerteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $alerte;

    public function __construct(Alerte $alerte)
    {
        $this->alerte = $alerte;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'alerte_id' => $this->alerte->id,
            'titre_alerte' => $this->alerte->titre_alerte,
            'message_alerte' => $this->alerte->message_alerte,
            'niveau_alerte' => $this->alerte->niveau_alerte,
            'unite_nom' => $this->alerte->unite->nom_unite ?? 'Unité inconnue',
            'organisation_nom' => $this->alerte->unite->organisation->nom_organisation ?? 'Organisation inconnue',
            'date_alerte' => $this->alerte->date_alerte->format('d/m/Y H:i'),
            'date_fin' => $this->alerte->date_fin ? $this->alerte->date_fin->format('d/m/Y H:i') : null,
            'rayon_km' => $this->alerte->rayon_km,
            'type' => 'nouvelle_alerte',
            'url' => "/alertes/{$this->alerte->id}",
            'icon' => $this->getIcon(),
            'couleur' => $this->getCouleur(),
            'urgence' => $this->isUrgent(),
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'AlerteNotification',
            'data' => [
                'alerte_id' => $this->alerte->id,
                'titre' => $this->getTitreNotification(),
                'message' => $this->alerte->message_alerte,
                'niveau' => $this->alerte->niveau_alerte,
                'icon' => $this->getIcon(),
                'couleur' => $this->getCouleur(),
                'date' => now()->format('H:i'),
            ],
            'read_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    // Méthodes utilitaires
    private function getTitreNotification(): string
    {
        $niveau = $this->getNiveauText();
        $unite = $this->alerte->unite->nom_unite ?? 'Autorités';
        return "{$this->getIcon()} {$niveau} - {$unite}";
    }

    private function getNiveauText(): string
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => 'URGENCE',
            'avertissement' => 'Avertissement',
            default => 'Information'
        };
    }

    private function getIcon(): string
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => '🚨',
            'avertissement' => '⚠️',
            default => 'ℹ️'
        };
    }

    private function getCouleur(): string
    {
        return match($this->alerte->niveau_alerte) {
            'urgence' => '#dc3545',
            'avertissement' => '#ffc107',
            default => '#17a2b8'
        };
    }

    private function isUrgent(): bool
    {
        return $this->alerte->niveau_alerte === 'urgence';
    }
}