<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Incident;

class IncidentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $incident;
    public $type;

    public function __construct(Incident $incident, string $type = 'nouveau')
    {
        $this->incident = $incident;
        $this->type = $type;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'titre' => $this->getTitre(),
            'message' => $this->getMessage(),
            'type' => $this->type,
            'statut_incident' => $this->incident->statut_incident,
            'categorie' => $this->incident->categorie,
            'gravite' => $this->incident->gravite,
            'adresse' => $this->incident->adresse,
            'date_incident' => $this->incident->date_incident->format('d/m/Y H:i'),
            'url' => "/incidents/{$this->incident->id}",
            'icon' => $this->getIcon(),
            'couleur' => $this->getCouleur(),
            'urgence' => $this->isUrgent(),
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'IncidentNotification',
            'data' => [
                'incident_id' => $this->incident->id,
                'titre' => $this->getTitre(),
                'message' => $this->getMessage(),
                'type' => $this->type,
                'icon' => $this->getIcon(),
                'couleur' => $this->getCouleur(),
                'date' => now()->format('H:i'),
            ],
            'read_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    // Méthodes utilitaires
    private function getTitre(): string
    {
        return match($this->type) {
            'nouveau' => "🚨 Nouvel incident signalé",
            'assignation' => "📋 Incident assigné",
            'rejet' => "❌ Incident rejeté",
            'resolution' => "✅ Incident résolu",
            default => "ℹ️ Mise à jour d'incident"
        };
    }

    private function getMessage(): string
    {
        $categorie = $this->getCategorieText();
        
        return match($this->type) {
            'nouveau' => "Nouvel incident {$categorie} signalé à {$this->incident->adresse}",
            'assignation' => "Vous avez été assigné à l'incident : {$this->incident->titre_incident}",
            'rejet' => "Votre incident '{$this->incident->titre_incident}' a été rejeté",
            'resolution' => "L'incident '{$this->incident->titre_incident}' a été résolu",
            default => "Mise à jour de l'incident : {$this->incident->titre_incident}"
        };
    }

    private function getCategorieText(): string
    {
        return match($this->incident->categorie) {
            'accident' => 'd\'accident',
            'incendie' => 'd\'incendie',
            'criminalite' => 'de criminalité',
            'medical' => 'médical',
            'danger' => 'de danger',
            default => 'divers'
        };
    }

    private function getIcon(): string
    {
        return match($this->type) {
            'nouveau' => '🚨',
            'assignation' => '📋',
            'rejet' => '❌',
            'resolution' => '✅',
            default => 'ℹ️'
        };
    }

    private function getCouleur(): string
    {
        return match($this->type) {
            'nouveau' => '#dc3545',
            'assignation' => '#007bff',
            'rejet' => '#6c757d',
            'resolution' => '#28a745',
            default => '#17a2b8'
        };
    }

    private function isUrgent(): bool
    {
        return in_array($this->incident->gravite, ['critique', 'elevee']) || 
               $this->type === 'nouveau';
    }
}