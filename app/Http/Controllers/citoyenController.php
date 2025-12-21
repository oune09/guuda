<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Preuve;
use App\Models\Utilisateur;
use App\Models\Alerte;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CitoyenController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
        // $this->middleware('role:citoyen');
    }

    // ==================== GESTION DU PROFIL ====================

    public function modifierProfil(Request $request)
    {
        $utilisateur = $request->user();

        $regles = [
            'nom_utilisateur' => 'sometimes|string|max:50',
            'prenom_utilisateur' => 'sometimes|string|max:50',
            'telephone_utilisateur' => 'sometimes|string|min:8|max:20|unique:utilisateurs,telephone_utilisateur,' . $utilisateur->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $validation = $request->validate($regles);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photo')) {
                if ($utilisateur->photo) {
                    Storage::disk('public')->delete($utilisateur->photo);
                }
                $validation['photo'] = $request->file('photo')->store('photos_utilisateurs', 'public');
            }

            $utilisateur->update($validation);

            DB::commit();

            return response()->json([
                'message' => 'Profil modifié avec succès',
                'utilisateur' => $utilisateur->fresh()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la modification du profil'
            ], 500);
        }
    }

    // ==================== GESTION DES INCIDENTS ====================

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

    public function detailIncident($id)
    {
        $utilisateur = request()->user();

        $incident = Incident::where('id', $id)
                          ->where('utilisateur_id', $utilisateur->id)
                          ->with(['preuves', 'organisation', 'unite', 'autoriteAssignee.utilisateur', 'alertes'])
                          ->firstOrFail();

        return response()->json($incident, 200);
    }

    public function supprimerIncident($id)
    {
        $utilisateur = request()->user();

        $incident = Incident::where('id', $id)
                          ->where('utilisateur_id', $utilisateur->id)
                          ->where('statut_incident', 'ouvert')
                          ->firstOrFail();

        DB::beginTransaction();
        try {
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
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    // ==================== ALERTES ET NOTIFICATIONS ====================

    public function mesAlertes(Request $request)
    {
        $utilisateur = $request->user();

        if (!$utilisateur->localisation) {
            return response()->json([
                'message' => 'Activez votre géolocalisation pour voir les alertes'
            ], 400);
        }

        $rayon = $request->rayon_km ?? 5;

        $alertes = Alerte::where('statut_alerte', 'active')
                       ->whereRaw(
                           "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
                           [
                               $utilisateur->localisation->getLng(),
                               $utilisateur->localisation->getLat(),
                               $rayon * 1000
                           ]
                       )
                       ->with(['unite.organisation'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json([
            'alertes' => $alertes,
            'rayon_km' => $rayon,
            'total' => $alertes->count()
        ], 200);
    }

    // ==================== STATISTIQUES ====================

    public function mesStatistiques(Request $request)
    {
        $utilisateur = $request->user();

        $stats = [
            'incidents_signales' => Incident::where('utilisateur_id', $utilisateur->id)->count(),
            'incidents_ouverts' => Incident::where('utilisateur_id', $utilisateur->id)
                                         ->where('statut_incident', 'ouvert')
                                         ->count(),
            'incidents_en_cours' => Incident::where('utilisateur_id', $utilisateur->id)
                                          ->where('statut_incident', 'en_cours')
                                          ->count(),
            'incidents_resolus' => Incident::where('utilisateur_id', $utilisateur->id)
                                         ->where('statut_incident', 'resolu')
                                         ->count(),
        ];

        return response()->json($stats, 200);
    }

    public function alerte(Request $request)
    {
        $alerte = Alerte::all();
        return response()->json($alerte, 200);
    }

    public function incident(Request $request)
    {
        $incident = Incident::all();
        return response()->json($incident, 200);
    }
}