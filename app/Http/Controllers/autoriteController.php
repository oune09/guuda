<?php

namespace App\Http\Controllers;

use App\Models\alerte;
use App\Models\autorite;
use App\Models\incident;
use App\Models\preuve;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class autoriteController extends Controller
{
     public function __construct()
    {
        //$this->middleware('auth:sanctum');
        //$this->middleware('autorite'); // Assure que seul une autorité peut accéder
    }

    public function listeIncident(Request $request)
    {
        $autorite = $request->user()->autorite;
        
        $incidents = Incident::where('organisation_id', $autorite->organisation_id)
                          ->when($autorite->unite_id, function($query) use ($autorite) {
                              $query->where('unite_id', $autorite->unite_id);
                          })
                          ->with(['utilisateur', 'autoriteAssignee', 'preuves'])
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json($incidents, 200);
    }

    public function incidentsAssignes(Request $request)
    {
        $autorite = $request->user()->autorite;
        
        $incidents = Incident::where('autorite_assignee_id', $autorite->id)
                          ->with(['utilisateur', 'preuves'])
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json($incidents, 200);
    }

     public function accepterIncident(Request $request, $id)
    {
        $autorite = $request->user()->autorite;
        
        $incident = Incident::where('id', $id)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->firstOrFail();

        // Vérifier si l'incident n'est pas déjà assigné
        if ($incident->autorite_assignee_id) {
            return response()->json([
                'message' => 'Cet incident est déjà assigné à une autorité'
            ], 400);
        }

        $incident->update([
            'autorite_assignee_id' => $autorite->id,
            'statut_incident' => 'en_cours',
            'date_charge' => now()
        ]);

        return response()->json([
            'message' => 'Incident accepté avec succès',
            'incident' => $incident
        ], 200);
    }


  public function traiterIncident(Request $request, $id)
    {
        $autorite = $request->user()->autorite;
        
        $incident = Incident::where('id', $id)
                          ->where('autorite_assignee_id', $autorite->id)
                          ->firstOrFail();

        $request->validate([
            'statut_incident' => 'required|in:en_cours,resolu,annulee',
            'notes' => 'nullable|string'
        ]);

        $incident->update([
            'statut_incident' => $request->statut_incident,
            'date_resolution' => $request->statut_incident === 'resolu' ? now() : null
        ]);

        return response()->json([
            'message' => 'Statut de l\'incident mis à jour',
            'incident' => $incident
        ], 200);
    }

    public function detailIncident($id)
    {
        $autorite = request()->user()->autorite;
        
        $incident = Incident::where('id', $id)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->with([
                              'utilisateur', 
                              'autoriteAssignee.utilisateur',
                              'preuves',
                              'alertes'
                          ])
                          ->firstOrFail();

        return response()->json($incident, 200);
    }

    // ==================== GESTION DES ALERTES ====================

    public function creerAlerte(Request $request)
    {
        $autorite = $request->user()->autorite;

        $regles = [
            'titre_alerte' => 'required|string|max:255',
            'message_alerte' => 'required|string',
            'niveau_alerte' => 'required|in:info,avertissement,urgence',
            'rayon_km' => 'required|numeric|min:0.1|max:50',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'date_fin' => 'nullable|date|after:now',
        ];

        $validation = $request->validate($regles);

        $alerte = Alerte::create([
            'autorite_id' => $autorite->id,
            'unite_id' => $autorite->unite_id,
            'titre_alerte' => $validation['titre_alerte'],
            'message_alerte' => $validation['message_alerte'],
            'niveau_alerte' => $validation['niveau_alerte'],
            'rayon_km' => $validation['rayon_km'],
            'longitude' => $validation['longitude'],
            'latitude' => $validation['latitude'],
            'date_alerte' => now(),
            'date_fin' => $validation['date_fin'],
            'statut_alerte' => 'active'
        ]);

        // Ici, vous ajouterez la logique pour notifier les citoyens dans le rayon
        // dispatch(new NotifierCitoyensProches($alerte));

        return response()->json([
            'message' => 'Alerte créée avec succès',
            'alerte' => $alerte
        ], 201);
    }

    public function mesAlertes(Request $request)
    {
        $autorite = $request->user()->autorite;
        
        $alertes = Alerte::where('autorite_id', $autorite->id)
                       ->with(['incident', 'unite'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($alertes, 200);
    }

    public function modifierAlerte(Request $request, $id)
    {
        $autorite = $request->user()->autorite;
        
        $alerte = Alerte::where('id', $id)
                      ->where('autorite_id', $autorite->id)
                      ->firstOrFail();

        $regles = [
            'titre_alerte' => 'sometimes|string|max:255',
            'message_alerte' => 'sometimes|string',
            'niveau_alerte' => 'sometimes|in:info,avertissement,urgence',
            'statut_alerte' => 'sometimes|in:active,terminee',
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
        $autorite = request()->user()->autorite;
        
        $alerte = Alerte::where('id', $id)
                      ->where('autorite_id', $autorite->id)
                      ->firstOrFail();

        $alerte->delete();

        return response()->json([
            'message' => 'Alerte supprimée avec succès'
        ], 200);
    }

    // ==================== GESTION DES PREUVES ====================

    public function ajouterPreuveIncident(Request $request, $incidentId)
    {
        $autorite = $request->user()->autorite;
        
        $incident = Incident::where('id', $incidentId)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->firstOrFail();

        $request->validate([
            'nom_preuve' => 'required|string',
            'type_preuve' => 'required|in:image,video,audio,document',
            'lien_preuve' => 'required|string',
            'description_preuve' => 'nullable|string'
        ]);

        $preuve = preuve::create([
            'incident_id' => $incident->id,
            'nom_preuve' => $request->nom_preuve,
            'type_preuve' => $request->type_preuve,
            'lien_preuve' => $request->lien_preuve,
            'description_preuve' => $request->description_preuve,
            'statut_preuve' => 'valide' // Les preuves ajoutées par les autorités sont automatiquement validées
        ]);

        return response()->json([
            'message' => 'Preuve ajoutée avec succès',
            'preuve' => $preuve
        ], 201);
    }

    // ==================== STATISTIQUES & DASHBOARD ====================

    public function dashboardStatistiques(Request $request)
    {
        $autorite = $request->user()->autorite;
        
        $stats = [
            'incidents_total' => Incident::where('organisation_id', $autorite->organisation_id)
                                      ->when($autorite->unite_id, function($query) use ($autorite) {
                                          $query->where('unite_id', $autorite->unite_id);
                                      })
                                      ->count(),
            
            'incidents_assignes' => Incident::where('autorite_assignee_id', $autorite->id)
                                         ->count(),
            
            'incidents_en_cours' => Incident::where('autorite_assignee_id', $autorite->id)
                                         ->where('statut_incident', 'en_cours')
                                         ->count(),
            
            'incidents_resolus' => Incident::where('autorite_assignee_id', $autorite->id)
                                        ->where('statut_incident', 'resolu')
                                        ->count(),
            
            'alertes_actives' => Alerte::where('autorite_id', $autorite->id)
                                    ->where('statut_alerte', 'active')
                                    ->count(),
            
            'mes_alertes_total' => Alerte::where('autorite_id', $autorite->id)
                                      ->count()
        ];

        return response()->json($stats, 200);
    }

    public function statistiquesMensuelles(Request $request)
    {
        $autorite = $request->user()->autorite;
        
        $stats = DB::table('incidents')
                  ->select(
                      DB::raw('MONTH(created_at) as mois'),
                      DB::raw('COUNT(*) as total_incidents'),
                      DB::raw('SUM(CASE WHEN statut_incident = "resolu" THEN 1 ELSE 0 END) as incidents_resolus')
                  )
                  ->where('organisation_id', $autorite->organisation_id)
                  ->when($autorite->unite_id, function($query) use ($autorite) {
                      $query->where('unite_id', $autorite->unite_id);
                  })
                  ->whereYear('created_at', date('Y'))
                  ->groupBy('mois')
                  ->orderBy('mois')
                  ->get();

        return response()->json($stats, 200);
    }

    // ==================== PROFIL & INFORMATIONS ====================

    public function monProfil(Request $request)
    {
        $autorite = $request->user()->autorite->load(['unite', 'organisation', 'utilisateur']);
        
        return response()->json($autorite, 200);
    }

    public function updateProfil(Request $request)
    {
        $utilisateur = $request->user();
        $autorite = $utilisateur->autorite;

        $regles = [
            'telephone_utilisateur' => 'sometimes|string|unique:utilisateurs,telephone_utilisateur,' . $utilisateur->id,
            'photo' => 'sometimes|string|nullable',
        ];

        $validation = $request->validate($regles);

        $utilisateur->update($validation);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'utilisateur' => $utilisateur->load('autorite')
        ], 200);
    }

    public function detailAutorite($id)
    {
        $autorite = autorite::with(['utilisateur', 'unite', 'organisation'])
                          ->findOrFail($id);

        return response()->json($autorite, 200);
    }
}
