<?php

namespace App\Http\Controllers;

use App\Models\Autorite;
use App\Models\Preuve;
use App\Models\Incident;
use App\Models\Alerte;
use App\Models\Utilisateur;
use App\Models\Unite;
use App\Models\Organisation;
use App\Notifications\AlerteNotification;
use App\Notifications\IncidentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
        // $this->middleware('admin'); 
    }

    // ==================== GESTION DES AUTORITÉS ====================

    public function listeAutorite(Request $request)
    {
        $admin = $request->user()->admin; 
        $autorites = Autorite::where('unite_id', $admin->unite_id)
                            ->where('organisation_id', $admin->organisation_id)
                            ->with(['utilisateur', 'unite'])
                            ->get();

        return response()->json($autorites);
    }

    

    public function ajouterAutorite(Request $request)
    {
        $admin = $request->user()->admin;

        $regles = [
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'matricule' => 'required|string|unique:autorites,matricule',
        ];

        $validation = $request->validate($regles);

        $utilisateur = Utilisateur::find($validation['utilisateur_id']);

        // Vérifier que l'utilisateur est un citoyen
        if (!$utilisateur->estCitoyen()) {
            return response()->json([
                'message' => 'Seul un citoyen peut être promu en autorité'
            ], 400);
        }

        $autorite = Autorite::create([
            'utilisateur_id' => $utilisateur->id,
            'organisation_id' => $admin->organisation_id,
            'unite_id' => $admin->unite_id,
            'matricule' => $validation['matricule'],
            'statut' => true
        ]);

        $utilisateur->update(['role_utilisateur' => 'autorite']);

        return response()->json([
            'message' => 'Autorité ajoutée avec succès',
            'autorite' => $autorite->load('utilisateur')
        ], 201);
    }

    public function modifierAutorite(Request $request, $id)
    {
        $admin = $request->user()->admin;

        $autorite = Autorite::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        $regles = [
            'matricule' => 'sometimes|string|unique:autorites,matricule,' . $id,
            'statut' => 'sometimes|boolean'
        ];

        $validation = $request->validate($regles);

        $autorite->update($validation);

        return response()->json([
            'message' => 'Autorité modifiée avec succès',
            'autorite' => $autorite
        ], 200);
    }

    public function supprimerAutorite($id)
    {
        $admin = request()->user()->admin;

        $autorite = Autorite::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        $utilisateur = $autorite->utilisateur;

        DB::transaction(function () use ($autorite, $utilisateur) {
            $autorite->delete();
            $utilisateur->update(['role_utilisateur' => 'citoyen']);
        });

        return response()->json([
            'message' => 'Autorité supprimée avec succès'
        ], 200);
    }
     public function detailAutorite($id)
    {
        $autorite = autorite::with(['utilisateur', 'unite', 'organisation'])
                          ->findOrFail($id);

        return response()->json($autorite, 200);
    }

    // ==================== GESTION DES INCIDENTS ====================

    public function listeIncident(Request $request)
    {
        $admin = $request->user()->admin; 
        $incidents = Incident::where('unite_id', $admin->unite_id)
                            ->where('organisation_id', $admin->organisation_id)
                            ->with(['utilisateur', 'autoriteAssignee.utilisateur', 'preuves'])
                            ->orderBy('created_at', 'desc')
                            ->get();

        return response()->json($incidents);
    }

    public function detailIncident($id)
    {
        $admin = request()->user()->admin;
        
        $incident = Incident::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->with(['utilisateur', 'autoriteAssignee.utilisateur', 'preuves', 'alertes'])
                          ->firstOrFail();

        return response()->json($incident);
    }

    public function confirmerSignalement(Request $request, $id)
    {
        $admin = $request->user()->admin;
        $incident = Incident::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        // Vérifier si l'incident est déjà confirmé
        if ($incident->statut_incident !== 'ouvert') {
            return response()->json([
                'message' => 'Cet incident a déjà été traité'
            ], 400);
        }

        $incident->update([
            'statut_incident' => 'en_cours',
            'date_charge' => now()
        ]);

        // Notifier les autorités de l'unité
        $autorites = Autorite::where('unite_id', $admin->unite_id)
                            ->where('statut', true)
                            ->with('utilisateur')
                            ->get();

        foreach ($autorites as $autorite) {
            try {
                $autorite->utilisateur->notify(new IncidentNotification($incident));
                Log::info("Notification envoyée à l'autorité {$autorite->id} pour l'incident {$incident->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour l'autorité {$autorite->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Signalement confirmé et autorités notifiées',
            'incident' => $incident,
            'autorites_notifiees' => $autorites->count()
        ], 200);
    }

    public function assignerIncident(Request $request, $id)
    {
        $admin = $request->user()->admin;

        $regles = [
            'autorite_id' => 'required|exists:autorites,id'
        ];

        $validation = $request->validate($regles);

        $incident = Incident::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        $autorite = Autorite::where('id', $validation['autorite_id'])
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        $incident->update([
            'autorite_assignee_id' => $autorite->id,
            'statut_incident' => 'en_cours',
            'date_charge' => now()
        ]);

        // Notifier l'autorité assignée
        try {
            $autorite->utilisateur->notify(new IncidentNotification($incident, 'assignation'));
            Log::info("Incident {$incident->id} assigné à l'autorité {$autorite->id}");
        } catch (\Exception $e) {
            Log::error("Erreur notification d'assignation: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Incident assigné avec succès',
            'incident' => $incident,
            'autorite' => $autorite
        ], 200);
    }

    public function rejeterIncident(Request $request, $id)
    {
        $admin = $request->user()->admin;

        $incident = Incident::where('id', $id)
                          ->where('unite_id', $admin->unite_id)
                          ->firstOrFail();

        $request->validate([
            'raison_rejet' => 'required|string'
        ]);

        $incident->update([
            'statut_incident' => 'annulee',
            'date_resolution' => now()
        ]);

        // Notifier le citoyen
        try {
            $incident->utilisateur->notify(new IncidentNotification($incident, 'rejet'));
            Log::info("Incident {$incident->id} rejeté");
        } catch (\Exception $e) {
            Log::error("Erreur notification de rejet: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Incident rejeté avec succès'
        ], 200);
    }

    // ==================== GESTION DES ALERTES ====================

   public function creerAlerte(Request $request)
{
    $admin = $request->user()->admin;

    // Vérifiez que l'utilisateur a bien un profil admin
    if (!$admin) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes pas un administrateur'
        ], 403);
    }

    // Validation
    $regles = [
        'titre_alerte' => 'required|string|max:255',
        'message_alerte' => 'required|string',
        'niveau_alerte' => 'required|in:info,avertissement,urgence',
        'rayon_km' => 'required|numeric|min:0.1|max:50',
        'longitude' => 'required|numeric',
        'latitude' => 'required|numeric',
        'date_fin' => 'sometimes|nullable|date|after:now',
    ];
    
    $validation = $request->validate($regles);

    // Gestion de date_fin
    $dateFin = isset($validation['date_fin']) ? $validation['date_fin'] : null;

    // Création de l'alerte avec l'admin_id
    $alerte = Alerte::create([
        'admin_id' => $admin->id, // ⬅️ Utilisez l'ID de l'admin connecté
        'titre_alerte' => $validation['titre_alerte'],
        'message_alerte' => $validation['message_alerte'],
        'niveau_alerte' => $validation['niveau_alerte'],
        'rayon_km' => $validation['rayon_km'],
        'longitude' => $validation['longitude'],
        'latitude' => $validation['latitude'],
        'date_alerte' => now(),
        'date_fin' => $dateFin,
        'statut_alerte' => 'active',
        'unite_id'=>$admin->unite_id
    ]);

    // 🔔 NOTIFIER LES CITOYENS DANS LE RAYON
    // Utilise la formule Haversine pour trouver les citoyens proches
    $rayonKm = $validation['rayon_km'];
    $latitude = $validation['latitude'];
    $longitude = $validation['longitude'];
    
    $citoyens = Utilisateur::where('role_utilisateur', 'citoyen')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->select('*')
        ->selectRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
            [$latitude, $longitude, $latitude]
        )
        ->having('distance', '<=', $rayonKm)
        ->get();

    $notificationsEnvoyees = 0;
    
    foreach ($citoyens as $citoyen) {
        try {
            // Envoie la notification
            $citoyen->notify(new \App\Notifications\AlerteNotification($alerte));
            $notificationsEnvoyees++;
            
            Log::info("Notification envoyée au citoyen {$citoyen->id} pour l'alerte {$alerte->id}");
        } catch (\Exception $e) {
            Log::error("Erreur notification pour citoyen {$citoyen->id}: " . $e->getMessage());
        }
    }

    // Réponse avec statistiques
    return response()->json([
        'success' => true,
        'message' => 'Alerte créée avec succès',
        'data' => $alerte,
        'statistiques' => [
            'citoyens_notifies' => $notificationsEnvoyees,
            'rayon_km' => $validation['rayon_km'],
            'citoyens_dans_rayon' => $citoyens->count()
        ]
    ], 201);

    // ... reste du code ...
}
    public function modifierAlerte(Request $request, $id)
    {
        $admin = $request->user()->admin;

        $alerte = Alerte::where('id', $id)
                      ->where('unite_id', $admin->unite_id)
                      ->firstOrFail();

        $regles = [
            'titre_alerte' => 'sometimes|string|max:255',
            'message_alerte' => 'sometimes|string',
            'niveau_alerte' => 'sometimes|in:info,avertissement,urgence',
            'statut_alerte' => 'sometimes|in:active,terminee',
            'date_fin' => 'sometimes|date|after:now',
        ];

        $validation = $request->validate($regles);

        $alerte->update($validation);

        return response()->json([
            'message' => 'Alerte modifiée avec succès',
            'alerte' => $alerte
        ], 200);
    }

    public function supprimerAlerte($id)
    {
        $admin = request()->user()->admin;

        $alerte = Alerte::where('id', $id)
                      ->where('unite_id', $admin->unite_id)
                      ->firstOrFail();

        $alerte->delete();

        return response()->json([
            'message' => 'Alerte supprimée avec succès'
        ], 200);
    }

    public function listeAlerte(Request $request)
    {
        $admin = $request->user()->admin;

        $alertes = Alerte::where('unite_id', $admin->unite_id)
                       ->with(['incident', 'unite','admin'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($alertes);                              
    }

    public function alerteDetail($id)
    {
        $admin = request()->user()->admin;

        $alerte = Alerte::where('id', $id)
                      ->where('unite_id', $admin->unite_id)
                      ->with(['incident', 'autorite.utilisateur', 'preuves'])
                      ->firstOrFail();

        return response()->json($alerte);
    }

    // ==================== STATISTIQUES & DASHBOARD ====================

    public function dashboardStatistiques(Request $request)
    {
        $admin = $request->user()->admin;

        $stats = [
            'incidents_total' => Incident::where('unite_id', $admin->unite_id)->count(),
            'incidents_ouverts' => Incident::where('unite_id', $admin->unite_id)
                                         ->where('statut_incident', 'ouvert')
                                         ->count(),
            'incidents_en_cours' => Incident::where('unite_id', $admin->unite_id)
                                          ->where('statut_incident', 'en_cours')
                                          ->count(),
            'incidents_resolus' => Incident::where('unite_id', $admin->unite_id)
                                         ->where('statut_incident', 'resolu')
                                         ->count(),
            'autorites_actives' => Autorite::where('unite_id', $admin->unite_id)
                                         ->where('statut', true)
                                         ->count(),
            'alertes_actives' => Alerte::where('unite_id', $admin->unite_id)
                                     ->where('statut_alerte', 'active')
                                     ->count(),
        ];

        return response()->json($stats, 200);
    }

    public function statistiquesMensuelles(Request $request)
    {
        $admin = $request->user()->admin;

        $stats = DB::table('incidents')
                  ->select(
                      DB::raw('MONTH(created_at) as mois'),
                      DB::raw('COUNT(*) as total_incidents'),
                      DB::raw('SUM(CASE WHEN statut_incident = "resolu" THEN 1 ELSE 0 END) as incidents_resolus')
                  )
                  ->where('unite_id', $admin->unite_id)
                  ->whereYear('created_at', date('Y'))
                  ->groupBy('mois')
                  ->orderBy('mois')
                  ->get();

        return response()->json($stats, 200);
    }

    // ==================== MÉTHODES UTILITAIRES ====================

    private function getCitoyensDansRayon($latitude, $longitude, $rayonKm)
    {
        return Utilisateur::where('role_utilisateur', 'citoyen')
                         ->whereRaw(
                             "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
                             [$longitude, $latitude, $rayonKm * 1000]
                         )
                         ->get();
    }

    public function monUnite(Request $request)
    {
        $admin = $request->user()->admin;
        $unite= $admin->unite;
        if (!$unite) {
         return response()->json([
        'message' => "Aucune unité associée à cet admin."
        ], 404);
        }

        return response()->json($unite,200);
    }

   public function uniteCoordonnee(Request $request)
    {   
        $admin = $request->user()->admin;
       $unite = $admin->unite;
        if (!$unite) {
         return response()->json([
        'message' => "Aucune unité associée à cet admin."
        ], 404);
        }

       $validation = $request->validate([
        'longitude' => 'required|numeric',
        'latitude' => 'required|numeric',
       ]);

       $unite->update($validation);

       return response()->json([
        'message' => 'Coordonnées mises à jour.',
        'unite' => $unite
    ]);

    }

}