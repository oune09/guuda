<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\Autorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\VerificationToken;
use App\Mail\ActivationAutoriteMail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UtilisateurController extends Controller
{
    public function __construct()
    {
        // Middleware pour protéger certaines routes si nécessaire
        // $this->middleware('auth:sanctum');
    }

    // ==================== CREATION AUTORITE ====================
   public function creerAutorite(Request $request)
{
    $data = $request->validate([
        'nom_utilisateur' => 'required',
        'prenom_utilisateur' => 'required',
        'email_utilisateur' => 'required|email|unique:utilisateurs,email_utilisateur',
        'telephone_utilisateur' => 'nullable|unique:utilisateurs,telephone_utilisateur',
        'organisation_id' => 'required|exists:organisations,id',
        'unite_id' => 'required|exists:unites,id',
        'matricule' => 'required|unique:autorites,matricule',
        'role' => 'required',
    ]);

    DB::transaction(function () use ($data) {

        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => $data['nom_utilisateur'],
            'prenom_utilisateur' => $data['prenom_utilisateur'],
            'email_utilisateur' => $data['email_utilisateur'],
            'telephone_utilisateur' => $data['telephone_utilisateur'] ?? null,  
            'is_active' => false,
            'verification_channel' => null,
        ]);

        $utilisateur->assignRole($data['role']);

        Autorite::create([
            'utilisateur_id' => $utilisateur->id,
            'organisation_id' => $data['organisation_id'],
            'unite_id' => $data['unite_id'],
            'matricule' => $data['matricule'],
        ]);

        $token = Str::random(64);

        VerificationToken::create([
            'utilisateur_id' => $utilisateur->id,
            'token' => hash('sha256', $token),
            'canal' => 'email',
            'type' => 'activation',
            'expires_at' => now()->addHours(24),
        ]);

        // Envoi local via Mail Laravel
        Mail::to($utilisateur->email_utilisateur)
            ->send(new ActivationAutoriteMail($token));
    });

    return response()->json(['message' => 'Autorité créée']);
}



    // ==================== PROFIL ====================
    public function monProfil(Request $request)
    {
        $utilisateur = $request->user()->load('roles', 'permissions', 'autorite', 'admin', 'superAdmin');
        return response()->json($utilisateur, 200);
    }

    public function updateProfil(Request $request)
    {
        $utilisateur = $request->user();

        $validation = $request->validate([
            'telephone_utilisateur' => 'sometimes|string|unique:utilisateurs,telephone_utilisateur,' . $utilisateur->id,
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($utilisateur->photo) Storage::disk('public')->delete($utilisateur->photo);
            $validation['photo'] = $request->file('photo')->store('photos_utilisateurs', 'public');
        }

        $utilisateur->update($validation);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'utilisateur' => $utilisateur->load('roles', 'permissions')
        ], 200);
    }

    public function ListeAutorite()
    {
        $autorites = Autorite::with('utilisateur', 'organisation', 'unite')->get();
        return response()->json(['autorites' => $autorites], 200);
    }

    public function detailAutorite($id)
    {
        $autorite = Autorite::where('id', $id)
            ->with('utilisateur', 'organisation', 'unite')
            ->firstOrFail();

        return response()->json($autorite, 200);
    }

    public function modifierUtilisateur(Request $request, $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $validation = $request->validate([
            'nom_utilisateur' => 'sometimes|string',
            'prenom_utilisateur' => 'sometimes|string',
            'telephone_utilisateur' => 'sometimes|string|unique:utilisateurs,telephone_utilisateur,' . $id,
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($utilisateur->photo) Storage::disk('public')->delete($utilisateur->photo);
            $validation['photo'] = $request->file('photo')->store('photos_utilisateurs', 'public');
        }

        $utilisateur->update($validation);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'utilisateur' => $utilisateur->load('roles', 'permissions')
        ], 200);
    }

    public function listeCitoyens()
    {
        $citoyens = Utilisateur::role('citoyen')->get();
        return response()->json(['citoyens' => $citoyens], 200);
    }

    public function detailCitoyen($id)
    {
        $citoyen = Utilisateur::where('id', $id)
            ->role('citoyen')
            ->firstOrFail();

        return response()->json($citoyen, 200);
    }

    public function listeUtilisateur()
    {
        $utilisateurs = Utilisateur::with('roles')->get();
        return response()->json(['utilisateurs' => $utilisateurs], 200);
    }

    // ==================== ACTIVATION COMPTE ====================

    public function activerCompte(Request $request)
{
    $request->validate([
        'token' => 'required',
        'mot_de_passe' => 'required|min:8|confirmed',
    ]);

    $hashed = hash('sha256', $request->token);

    $verification = VerificationToken::where('token', $hashed)
        ->where('type', 'activation')
        ->where('expires_at', '>', now())
        ->firstOrFail();

    $utilisateur = $verification->utilisateur;

    $utilisateur->update([
        'mot_de_passe_utilisateur' => Hash::make($request->mot_de_passe),
        'is_active' => true,
        'verified_at' => now(),
    ]);

    $verification->delete();

    return response()->json(['message' => 'Compte activé']);
}

}
