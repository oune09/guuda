<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    // ==================== CRÉATION ====================

    public function creerOrganisation(Request $request)
    {
        $validation = $request->validate([
            'nom_organisation' => 'required|string|unique:organisations,nom_organisation',
            'description' => 'nullable|string',
            'telephone' => 'required|string',
            'mail_organisation' => 'required|email',
            'logo' => 'nullable|image',
            'adresse_siege' => 'required|string',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $organisation = Organisation::create([
            'nom_organisation' => $validation['nom_organisation'],
            'description' => $validation['description'] ?? null,
            'telephone' => $validation['telephone'],
            'mail_organisation' => $validation['mail_organisation'],
            'logo' => $logoPath,
            'adresse_siege' => $validation['adresse_siege'],
            'statut' => true,
        ]);

        return response()->json([
            'message' => 'Organisation créée avec succès',
            'organisation' => $organisation
        ], 201);
    }

    // ==================== LISTE ====================

    public function listeOrganisation()
    {
         $organisations = Organisation::withCount('unites')->get();
        return response()->json(['organisations' => $organisations], 200);

    }

    // ==================== DÉTAIL ====================

    public function detailOrganisation($id)
    {
        $organisation = Organisation::where('id', $id)
            ->with('unites')
            ->firstOrFail();

        return response()->json($organisation);
    }

    // ==================== MODIFICATION ====================

    public function modifierOrganisation(Request $request, $id)
    {
        $organisation = Organisation::findOrFail($id);

        $validation = $request->validate([
            'nom_organisation' => 'required|string|unique:organisations,nom_organisation,' . $id,
            'description' => 'nullable|string',
            'telephone' => 'required|string',
            'mail_organisation' => 'required|email',
            'adresse_siege' => 'required|string',
        ]);

        $organisation->update($validation);

        return response()->json([
            'message' => 'Organisation modifiée',
            'organisation' => $organisation
        ]);
    }

    // ==================== DÉSACTIVATION ====================

    public function desactiverOrganisation($id)
    {
        $organisation = Organisation::findOrFail($id);

        $organisation->update([
            'statut' => false
        ]);

        
        $organisation->unites()->update([
            'statut' => false
        ]);

        return response()->json([
            'message' => 'Organisation désactivée'
        ]);
    }
}
