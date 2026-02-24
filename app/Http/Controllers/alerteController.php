<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Alerte;
use App\Models\Utilisateur;
use App\Models\Incident;
use App\Models\Autorite;

class AlerteController extends Controller
{
    public function __construct()
    {
        // Middleware Spatie pour la gestion des permissions
    }

    // ==================== CREATION ====================
    public function creerAlerte(Request $request)
    {
        $autorite = $request->user()->autorite;

        if (!$autorite) {
            return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas un administrateur'], 403);
        }

        $rules = [
            'titre_alerte' => 'required|string|max:255',
            'message_alerte' => 'required|string',
            'niveau_alerte' => 'required|in:info,avertissement,urgence',
            'rayon_km' => 'required|numeric|min:0.1|max:50',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'date_fin' => 'sometimes|nullable|date|after:now',
        ];
        $validated = $request->validate($rules);

        $alerte = Alerte::create([
            'autorite_id' => $autorite->id,
            'titre_alerte' => $validated['titre_alerte'],
            'message_alerte' => $validated['message_alerte'],
            'niveau_alerte' => $validated['niveau_alerte'],
            'rayon_km' => $validated['rayon_km'],
            'longitude' => $validated['longitude'],
            'latitude' => $validated['latitude'],
            'date_alerte' => now(),
            'date_fin' => $validated['date_fin'] ?? null,
            'statut_alerte' => 'active',
            'unite_id' => $autorite->unite_id,
        ]);

        // Notifications aux citoyens dans le rayon
        $citoyens = $this->getCitoyensDansRayon($validated['latitude'], $validated['longitude'], $validated['rayon_km']);
        $notificationsEnvoyees = 0;

        foreach ($citoyens as $citoyen) {
            try {
                $citoyen->notify(new \App\Notifications\AlerteNotification($alerte));
                $notificationsEnvoyees++;
                Log::info("Notification envoyée au citoyen {$citoyen->id} pour l'alerte {$alerte->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour citoyen {$citoyen->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alerte créée avec succès',
            'data' => $alerte,
            'statistiques' => [
                'citoyens_notifies' => $notificationsEnvoyees,
                'rayon_km' => $validated['rayon_km'],
                'citoyens_dans_rayon' => $citoyens->count(),
            ],
        ], 201);
    }

    // ==================== MODIFICATION ====================
    public function modifierAlerte(Request $request, $id)
    {
        $autorite = $request->user()->autorite;

        $alerte = Alerte::where('id', $id)->where('unite_id', $autorite->unite_id)->firstOrFail();

        $rules = [
            'titre_alerte' => 'sometimes|string|max:255',
            'message_alerte' => 'sometimes|string',
            'niveau_alerte' => 'sometimes|in:info,avertissement,urgence',
            'statut_alerte' => 'sometimes|in:active,terminee',
            'date_fin' => 'sometimes|date|after:now',
        ];

        $validated = $request->validate($rules);

        $alerte->update($validated);

        return response()->json(['message' => 'Alerte modifiée avec succès', 'alerte' => $alerte]);
    }

    // ==================== SUPPRESSION ====================
    public function supprimerAlerte($id)
    {
        $autorite = request()->user()->autorite;

        $alerte = Alerte::where('id', $id)->where('unite_id', $autorite->unite_id)->firstOrFail();
        $alerte->delete();

        return response()->json(['message' => 'Alerte supprimée avec succès']);
    }

    // ==================== LISTE & DETAIL ====================
    public function listeAlerte(Request $request)
    {
        $autorite = $request->user()->autorite;

        $alertes = Alerte::where('unite_id', $autorite->unite_id)
            ->with(['incident', 'unite', 'autorite'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($alertes);
    }

    public function alerteDetail($id)
    {
        $autorite = request()->user()->autorite;

        $alerte = Alerte::where('id', $id)
            ->where('unite_id', $autorite->unite_id)
            ->with(['incident', 'autorite.utilisateur', 'preuves'])
            ->firstOrFail();

        return response()->json($alerte);
    }

    // ==================== ALERTES UTILISATEUR ====================
    public function mesAlertes(Request $request)
    {
        $utilisateur = $request->user();

        if (!$utilisateur->localisation) {
            return response()->json(['message' => 'Activez votre géolocalisation pour voir les alertes'], 400);
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

        return response()->json(['alertes' => $alertes, 'rayon_km' => $rayon, 'total' => $alertes->count()]);
    }

    public function alertes(Request $request)
    {
        $Alertes = Alerte::all();
        return response()->json($Alertes);
    }

    public function alertesParUnite(Request $request)
    {
        $autorite = $request->user()->autorite;

        $alertes = Alerte::where('unite_id', $autorite->unite_id)
                       ->with(['incident', 'unite','autorite'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($alertes);                              
    }

    // ==================== MÉTHODES PRIVÉES ====================
    private function getCitoyensDansRayon(float $latitude, float $longitude, float $rayonKm)
    {
        return Utilisateur::where('role_utilisateur', 'citoyen')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('*')
            ->selectRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
            ->having('distance', '<=', $rayonKm)
            ->get();
    }
}
