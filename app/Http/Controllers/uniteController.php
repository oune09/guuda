<?php

namespace App\Http\Controllers;

use App\Models\Unite;
use Illuminate\Http\Request;

class UniteController extends Controller
{
    // ==================== CRÉATION ====================

    public function creerUnite(Request $request)
    {
        $validation = $request->validate([
            'nom_unite' => 'required|string',
            'organisation_id' => 'required|exists:organisations,id',
            'adresse' => 'required|string',
            'mail_unite' => 'nullable|email',
            'telephone_unite' => 'required|string',
        ]);

        // Unicité nom + organisation
        if (
            Unite::where('nom_unite', $validation['nom_unite'])
                ->where('organisation_id', $validation['organisation_id'])
                ->exists()
        ) {
            return response()->json([
                'message' => 'Cette unité existe déjà dans cette organisation'
            ], 400);
        }

        $unite = Unite::create([
            ...$validation,
            'longitude' => null,
            'latitude' => null,
            'statut' => true,
        ]);

        return response()->json([
            'message' => 'Unité créée avec succès',
            'unite' => $unite
        ], 201);
    }

    // ==================== LISTE ====================

     public function listeUnite(Request $request)
    {
          $unites = Unite::with('organisation')->get(); 
          return response()->json(['unites' => $unites], 200); 
   }
    // ==================== DÉTAIL ====================

    public function detailUnite($id)
    {
        $unite = Unite::where('id', $id)
            ->where('statut', true)
            ->with('organisation')
            ->firstOrFail();

        return response()->json($unite);
    }

    // ==================== MODIFICATION ====================

    public function modifierUnite(Request $request, $id)
    {
        $unite = Unite::findOrFail($id);

        $validation = $request->validate([
            'nom_unite' => 'required|string',
            'adresse' => 'required|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'capacite_unite' => 'required|numeric',
            'mail_unite' => 'nullable|email',
            'rayon_intervention' => 'required|numeric',
            'telephone_unite' => 'required|string',
        ]);

        $unite->update($validation);

        return response()->json([
            'message' => 'Unité modifiée',
            'unite' => $unite
        ]);
    }

    // ==================== DÉSACTIVATION ====================

    public function desactiverUnite($id)
    {
        $unite = Unite::findOrFail($id);

        $unite->update([
            'statut' => false
        ]);

        return response()->json([
            'message' => 'Unité désactivée'
        ]);
    }

  // ==================== lOCALISATION ====================
 public function uniteCoordonnee(Request $request)
    {   
        $autorite = $request->user()->autorite;
       $unite = $autorite->unite;
        if (!$unite) {
         return response()->json([
        'message' => "Aucune unité associée à cet autorité."
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


    public function monUnite(Request $request)
    {
        $autorité = $request->user()->autorite;
        $unite= $autorité->unite;
        if (!$unite) {
         return response()->json([
        'message' => "Aucune unité associée à cet autorité."
        ], 404);
        }

        return response()->json($unite,200);
    }
}
