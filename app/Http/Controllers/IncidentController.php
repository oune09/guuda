<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Preuve;
use App\Models\Admin;
use App\Models\Autorite;
use App\Models\Utilisateur;
use App\Models\Organisation;
use App\Models\Unite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class IncidentController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function creerIncident(Request $request)
    {
        $regles = [
            'titre_incident' => 'required|string|max:255',
            'description_incident' => 'required|string',
            'categorie' => 'required|in:accident,incendie,criminalite,medical,danger,autre',
            'gravite' => 'required|in:faible,moyenne,elevee,critique',
            'adresse' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'preuves' => 'nullable|array',
            'preuves.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,pdf|max:5120',
        ];

        $validation = $request->validate($regles);

        DB::beginTransaction();
        try {
            $utilisateur = $request->user();

            // Trouver l'organisation et l'unité responsables basées sur la localisation
            $organisationUnite = $this->trouverOrganisationUniteResponsable(
                $validation['latitude'], 
                $validation['longitude']
            );

            $incident = Incident::create([
                'utilisateur_id' => $utilisateur->id,
                'organisation_id' => $organisationUnite['organisation_id'],
                'unite_id' => $organisationUnite['unite_id'],
                'titre_incident' => $validation['titre_incident'],
                'description_incident' => $validation['description_incident'],
                'categorie' => $validation['categorie'],
                'gravite' => $validation['gravite'],
                'adresse' => $validation['adresse'],
                'latitude' => $validation['latitude'],
                'longitude' => $validation['longitude'],
                'date_incident' => now(),
                'priorite' => $this->determinerPriorite($validation['gravite']),
                'statut_incident' => 'ouvert',
            ]);

            // Gestion des preuves
            if ($request->hasFile('preuves')) {
                foreach ($request->file('preuves') as $fichier) {
                    $lien_preuve = $fichier->store('preuves_incidents', 'public');

                    Preuve::create([
                        'incident_id' => $incident->id,
                        'nom_preuve' => $fichier->getClientOriginalName(),
                        'type_preuve' => $fichier->getMimeType(),
                        'lien_preuve' => $lien_preuve,
                        'taille_fichier' => $fichier->getSize(),
                        'statut_preuve' => 'en_attente',
                    ]);
                }
            }

            // Notifier les administrateurs de l'unité responsable
            $this->notifierAdministrateursUnite($incident, $organisationUnite['unite_id']);

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

    public function listeIncident(Request $request)
    {
        $incidents = Incident::with(['utilisateur', 'preuves', 'organisation', 'unite']);

        // Filtres
        if ($request->has('utilisateur_id')) {
            $incidents->where('utilisateur_id', $request->utilisateur_id);
        }

        if ($request->has('statut_incident')) {
            $incidents->where('statut_incident', $request->statut_incident);
        }

        if ($request->has('categorie')) {
            $incidents->where('categorie', $request->categorie);
        }

        if ($request->has('gravite')) {
            $incidents->where('gravite', $request->gravite);
        }

        if ($request->has('organisation_id')) {
            $incidents->where('organisation_id', $request->organisation_id);
        }

        if ($request->has('unite_id')) {
            $incidents->where('unite_id', $request->unite_id);
        }

        // Filtre par localisation (rayon)
        if ($request->has(['latitude', 'longitude', 'rayon_km'])) {
            $incidents->whereRaw(
                "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
                [
                    $request->longitude,
                    $request->latitude,
                    $request->rayon_km * 1000
                ]
            );
        }

        $incidents = $incidents->orderBy('created_at', 'desc')->get();

        return response()->json([
            'incidents' => $incidents,
            'total' => $incidents->count()
        ], 200);
    }

    public function incidentsProches(Request $request)
    {
        $validation = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rayon_km' => 'sometimes|numeric|min:0.1|max:50'
        ]);

        $rayon = $request->rayon_km ?? 5;

        $incidents = Incident::where('statut_incident', 'valide')
                           ->whereRaw(
                               "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
                               [
                                   $validation['longitude'],
                                   $validation['latitude'],
                                   $rayon * 1000
                               ]
                           )
                           ->with(['utilisateur', 'preuves', 'organisation'])
                           ->orderBy('created_at', 'desc')
                           ->get();

        return response()->json([
            'incidents' => $incidents,
            'rayon_km' => $rayon,
            'total' => $incidents->count()
        ], 200);
    }

    public function detailIncident($id)
    {
        $incident = Incident::with([
            'utilisateur', 
            'preuves', 
            'organisation', 
            'unite',
            'autoriteAssignee.utilisateur',
            'alertes'
        ])->findOrFail($id);

        return response()->json($incident, 200);
    }

    public function supprimerIncident($id)
    {
        $incident = Incident::find($id);
        
        if (!$incident) {
            return response()->json([
                'message' => 'Incident non trouvé'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Supprimer les preuves associées
            foreach ($incident->preuves as $preuve) {
                Storage::disk('public')->delete($preuve->lien_preuve);
                $preuve->delete();
            }

            $incident->delete();

            DB::commit();

            return response()->json([
                'message' => 'Incident supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'incident',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function incidentsParUnite(Request $request)
{  
    $admin = $request->user()->admin;

    $incidents = Incident::where('unite_id', $admin->unite_id)
                       ->where('organisation_id', $admin->organisation_id)
                       ->with(['utilisateur', 'preuves'])
                       ->orderBy('created_at', 'desc')
                       ->get();

    return response()->json([
        'incidents' => $incidents,
        'total' => $incidents->count()
    ], 200);
}


    // ==================== MÉTHODES UTILITAIRES PRIVÉES ====================

    private function trouverOrganisationUniteResponsable($latitude, $longitude)
    {
        // Trouver l'unité la plus proche pour cet incident
        $unite = Unite::whereRaw(
            "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= rayon_intervention_km * 1000",
            [$longitude, $latitude]
        )
        ->where('statut', true)
        ->orderByRaw(
            "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) ASC",
            [$longitude, $latitude]
        )
        ->first();

        if (!$unite) {
            // Fallback: première unité active
            $unite = Unite::where('statut', true)->first();
        }

        return [
            'organisation_id' => $unite->organisation_id,
            'unite_id' => $unite->id,
            'organisation_nom' => $unite->organisation->nom_organisation,
            'unite_nom' => $unite->nom_unite
        ];
    }

    private function notifierAdministrateursUnite(Incident $incident, $uniteId)
    {
        $admins = Admin::where('unite_id', $uniteId)
                     ->with('utilisateur')
                     ->get();

        $notificationsEnvoyees = 0;
        
        foreach ($admins as $admin) {
            try {
                // $admin->utilisateur->notify(new IncidentNotification($incident));
                $notificationsEnvoyees++;
                Log::info("Notification envoyée à l'admin {$admin->utilisateur->id} pour l'incident {$incident->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour admin {$admin->utilisateur->email}: " . $e->getMessage());
            }
        }

        return $notificationsEnvoyees;
    }

    private function determinerPriorite($gravite)
    {
        return match($gravite) {
            'critique', 'elevee' => 'elevee',
            'moyenne' => 'moyenne',
            'faible' => 'faible',
            default => 'moyenne'
        };
    }
}