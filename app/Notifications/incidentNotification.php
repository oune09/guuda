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
            'type' => $this->type,
            'statut_incident' => $this->incident->statut_incident,
            'date_incident' => $this->incident->date_incident->format('d/m/Y H:i'),
            'url' => "/incidents/{$this->incident->id}",
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'IncidentNotification',
            'data' => [
                'incident_id' => $this->incident->id,
                'type' => $this->type,
                'date' => now()->format('H:i'),
            ],
            'read_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}