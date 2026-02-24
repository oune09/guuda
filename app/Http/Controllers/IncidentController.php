<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Preuve;
use App\Models\Autorite;
use App\Models\Unite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use App\Notifications\IncidentNotification; // Notification Spatie
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class IncidentController extends Controller
{
    public function __construct()
    {
        // Middleware pour les permissions Spatie
       
    }

    // ==================== CREATION ====================
    public function creerIncident(Request $request)
{
    $rules = [
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'organisation_id' => 'required|integer|exists:organisations,id',
        'preuves' => 'nullable|array',
        'preuves.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,pdf|max:5120',
        'audio' => 'required|file|mimetypes:audio/mpeg,audio/wav,audio/x-wav|max:10240',
    ];

    $validated = $request->validate($rules);
    $latitude = (float) $validated['latitude'];
    $longitude = (float) $validated['longitude'];
    DB::beginTransaction();
    try {
        $utilisateur = $request->user();
        $organisationUnite = $this->trouverOrganisationUniteResponsable(
            $latitude, 
            $longitude,
            $validated['organisation_id']
        );

        $incident = Incident::create([
            'utilisateur_id' => $utilisateur->id,
            'organisation_id' => $validated['organisation_id'],
            'unite_id' => $organisationUnite['unite_id'],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'date_incident' => now(),
            'statut_incident' => 'ouvert',
        ]);

        $audiofichier = $validated['audio'];
        $audioLien = $audiofichier->store('audios_incidents', 'public');
        Preuve::create([
            'incident_id' => $incident->id,
            'nom_preuve' => $audiofichier->getClientOriginalName(),
            'type_preuve' => $audiofichier->getMimeType(),
            'lien_preuve' => $audioLien,
            'taille_fichier' => $audiofichier->getSize(),
            'statut_preuve' => 'en_attente',
        ]);

        if ($request->hasFile('preuves')) {
            foreach ($request->file('preuves') as $fichier) {
                $lien = $fichier->store('preuves_incidents', 'public');
                Preuve::create([
                    'incident_id' => $incident->id,
                    'nom_preuve' => $fichier->getClientOriginalName(),
                    'type_preuve' => $fichier->getMimeType(),
                    'lien_preuve' => $lien,
                    'taille_fichier' => $fichier->getSize(),
                    'statut_preuve' => 'en_attente',
                ]);
            }
        }

        // Notification aux autorités avec le rôle admin
        $this->notifierAutorites($incident, $organisationUnite['unite_id']);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Incident signalé avec succès',
            'data' => $incident->load('preuves'),
            'organisation_responsable' => $organisationUnite['organisation_nom'],
            'unite_responsable' => $organisationUnite['unite_nom']
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de l\'incident',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // ==================== LISTE & DETAIL ====================
    public function listeIncident()
    {
        $incidents = Incident::with(['utilisateur', 'preuves', 'organisation', 'unite', 'autoriteAssignee.utilisateur'])
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($incidents);
    }

    public function mesIncidents(Request $request)
    {
        $utilisateur = $request->user();

        $incidents = Incident::where('utilisateur_id', $utilisateur->id)
                           ->with(['preuves', 'organisation', 'unite', 'autoriteAssignee.utilisateur'])
                           ->orderBy('created_at', 'desc')
                           ->get();

        return response()->json([
            'incidents' => $incidents,
            'total' => $incidents->count()
        ], 200);
    }

    public function citoyenIncidents(Request $request)
    {
        $utilisateur = $request->user();

        $incidents = Incident::where('utilisateur_id', $utilisateur->id)
                           ->with(['preuves', 'organisation', 'unite', 'autoriteAssignee.utilisateur'])
                           ->orderBy('created_at', 'desc')
                           ->get();

        return response()->json([
            'incidents' => $incidents,
            'total' => $incidents->count()
        ], 200);
    }

    public function detailIncident($id)
    {
        $incident = Incident::with(['utilisateur', 'preuves', 'organisation', 'unite', 'autoriteAssignee.utilisateur'])
                            ->findOrFail($id);

        return response()->json($incident);
    }

    // ==================== ACTIONS ADMINISTRATIVES ====================
    public function confirmerSignalement($id)
    {
        $incident = Incident::findOrFail($id);

        if ($incident->statut_incident !== 'ouvert') {
            return response()->json(['message' => 'Cet incident a déjà été traité'], 400);
        }

        $incident->update([
            'statut_incident' => 'en_cours',
            'date_charge' => now()
        ]);

        $this->notifierAutorites($incident, $incident->unite_id);

        return response()->json([
            'message' => 'Signalement confirmé et autorités notifiées',
            'incident' => $incident
        ]);
    }

    public function assignerIncident(Request $request, $id)
    {
        $request->validate(['autorite_id' => 'required|exists:autorites,id']);
        $incident = Incident::findOrFail($id);
        $autorite = Autorite::findOrFail($request->autorite_id);

        $incident->update([
            'autorite_assignee_id' => $autorite->id,
            'statut_incident' => 'en_cours',
            'date_charge' => now()
        ]);

        try {
            $autorite->utilisateur->notify(new \App\Notifications\IncidentNotification($incident, 'assignation'));
        } catch (\Exception $e) {
            Log::error("Erreur notification assignation: ".$e->getMessage());
        }

        return response()->json([
            'message' => 'Incident assigné avec succès',
            'incident' => $incident,
            'autorite' => $autorite
        ]);
    }

    public function rejeterIncident(Request $request, $id)
    {
        $request->validate(['raison_rejet' => 'required|string']);
        $incident = Incident::findOrFail($id);

        $incident->update([
            'statut_incident' => 'annulee',
            'date_resolution' => now()
        ]);

        try {
            $incident->utilisateur->notify(new \App\Notifications\IncidentNotification($incident, 'rejet'));
        } catch (\Exception $e) {
            Log::error("Erreur notification rejet: ".$e->getMessage());
        }

        return response()->json(['message' => 'Incident rejeté avec succès']);
    }
    public function traiterIncident(Request $request, $id)
{
    $user = $request->user();

    
    if (!$user->can('incident.traiter')) {
        abort(403, 'Permission refusée');
    }

    $autorite = $user->autorite;

    if (!$autorite) {
        abort(403, 'Utilisateur non autorisé');
    }

    $incident = Incident::where('id', $id)
        ->where('autorite_assignee_id', $autorite->id)
        ->firstOrFail();

    $validation = $request->validate([
        'statut_incident' => 'required|in:en_cours,resolu,annulee',
        'notes' => 'nullable|string'
    ]);

    $incident->update([
        'statut_incident' => $validation['statut_incident'],
        'date_resolution' => $validation['statut_incident'] === 'resolu'
            ? now()
            : null,
    ]);

    return response()->json([
        'message' => 'Statut de l’incident mis à jour',
        'incident' => $incident
    ]);
}   

 public function incidents(Request $request)
 {
    $icidents = Incident::all();
    return response()->json($icidents);
 }

 public function incidentsParUnite(Request $request)
{  
    try {
        $autorite = $request->user()->autorite;
        $uniteId = $autorite?->unite_id;
        $organisationId = $request->query('organisation_id');

        if (!$uniteId) {
            return response()->json(['message' => 'Aucune unité associée'], 403);
        }

        $incidents = Incident::where('unite_id', $uniteId)
                           ->when($organisationId, function($query) use ($organisationId) {
                               return $query->where('organisation_id', $organisationId);
                           })
                           ->with(['utilisateur', 'preuves']) // Assurez-vous que ces relations existent dans Incident.php
                           ->orderBy('created_at', 'desc')
                           ->get();

        return response()->json([
            'incidents' => $incidents,
            'total' => $incidents->count()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur technique sur le serveur',
            'error' => $e->getMessage() 
        ], 500);
    }
}



    // ==================== MÉTHODES PRIVÉES ====================
    private function determinerPriorite($gravite)
    {
        return match($gravite) {
            'critique', 'elevee' => 'elevee',
            'moyenne' => 'moyenne',
            'faible' => 'faible',
            default => 'moyenne'
        };
    }

 private function trouverOrganisationUniteResponsable($latitude, $longitude, $organisationId = null)
{
    $rayonMetres = 10000; // 10km en mètres

    $query = Unite::whereRaw(
        "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
        [$longitude, $latitude, $rayonMetres]
    )
    ->where('statut', true);

    // Filtrer par organisation_id si fourni et non nul
    if ($organisationId) {
        $query->where('organisation_id', $organisationId);
    }

    $unite = $query->orderByRaw(
        "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) ASC",
        [$longitude, $latitude]
    )->first();

    // Si pas d'unité trouvée dans le rayon ET organisation_id fourni
    // Chercher n'importe quelle unité de cette organisation (même hors rayon)
    if (!$unite && $organisationId) {
        $unite = Unite::where('organisation_id', $organisationId)
                      ->where('statut', true)
                      ->first();
    }

    // Si toujours pas d'unité, prendre la première unité disponible
    if (!$unite) {
        $unite = Unite::where('statut', true)->first();
    }

    return [
        'organisation_id' => $unite->organisation_id,
        'unite_id' => $unite->id,
        'organisation_nom' => $unite->organisation->nom_organisation,
        'unite_nom' => $unite->nom_unite
    ];
}
   private function notifierAutorites($incident, $uniteId)
{
    $autorites = Autorite::where('unite_id', $uniteId)
                         ->where('statut', true)
                         ->with('utilisateur.roles')
                         ->get()
                         ->filter(function($autorite) {
                             return $autorite->utilisateur->hasPermissionTo('incident.view.unite');
                         });

    foreach ($autorites as $autorite) {
        try {
            $autorite->utilisateur->notify(new \App\Notifications\IncidentNotification($incident));
            Log::info("Notification envoyée à l'autorité {$autorite->id} pour l'incident {$incident->id}");
        } catch (\Exception $e) {
            Log::error("Erreur notification autorité {$autorite->id}: " . $e->getMessage());
        }
    }
}

}
