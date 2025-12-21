<?php

namespace App\Http\Controllers;

use App\Models\admin;
use App\Models\Organisation;
use App\Models\SuperAdmin;
use App\Models\unite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function connexion(Request $request)
    {
        // Validation des données
        $validation = $request->validate([
            'email_utilisateur' => 'required|string|email',
            'mot_de_passe_utilisateur' => 'required|string|min:8',
        ]);

        // Recherche de l'utilisateur
        $utilisateur = Utilisateur::where('email_utilisateur', $validation['email_utilisateur'])->first();
        //check Password 
        
        


        
        // Vérification du mot de passe
        if (!$utilisateur || !Hash::check($validation['mot_de_passe_utilisateur'], $utilisateur->mot_de_passe_utilisateur)) {
          return response()->json([
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
}


        // Vérification que l'utilisateur est actif (selon son rôle)
        if (!$this->estUtilisateurActif($utilisateur)) {
            return response()->json([
                'message' => 'Votre compte n\'est pas actif. Contactez l\'administration.'
            ], 403);
        }

        // Suppression des anciens tokens (sécurité)
        $utilisateur->tokens()->delete();

        // Création du nouveau token
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        // Chargement des relations selon le rôle
        $utilisateur->load($this->getRelationsByRole($utilisateur->role_utilisateur));

        // Réponse de succès
        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration'), // Durée de vie du token
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                'email' => $utilisateur->email_utilisateur,
                'telephone' => $utilisateur->telephone_utilisateur,
                'role' => $utilisateur->role_utilisateur,
                'photo' => $utilisateur->photo ? asset('storage/' . $utilisateur->photo) : null,
                'localisation_active' => !is_null($utilisateur->localisation),
            ],
            'permissions' => $this->getPermissionsByRole($utilisateur->role_utilisateur)
        ], 200);
    }

  public function inscription(Request $request)
{
    // Validation des données
    $validation = $request->validate([
        'nom_utilisateur' => 'required|string|max:255',
        'prenom_utilisateur' => 'required|string|max:255',
        'email_utilisateur' => 'required|string|email|unique:utilisateurs,email_utilisateur',
        'mot_de_passe_utilisateur' => 'required|string|min:8|confirmed',
        'cnib' => 'required|string|unique:utilisateurs,cnib',
        'date_naissance_utilisateur' => 'required|date',
        'telephone_utilisateur' => 'required|string|unique:utilisateurs,telephone_utilisateur',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Gestion de l'upload de la photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos_utilisateurs', 'public');
        }

        // Création de l'utilisateur (le mot de passe sera hashé automatiquement par le mutator)
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => $validation['nom_utilisateur'],
            'prenom_utilisateur' => $validation['prenom_utilisateur'],
            'email_utilisateur' => $validation['email_utilisateur'],
            'mot_de_passe_utilisateur' => $validation['mot_de_passe_utilisateur'], // ⚠️ Le mutator va le hasher
            'cnib' => $validation['cnib'],
            'date_naissance_utilisateur' => $validation['date_naissance_utilisateur'],
            'telephone_utilisateur' => $validation['telephone_utilisateur'],
            'photo' => $photoPath,
            'role_utilisateur' => 'citoyen',
        ]);

        // Création du token
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        Log::info("Nouvel utilisateur inscrit: {$utilisateur->email_utilisateur}");

        return response()->json([
            'message' => 'Inscription réussie',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration'),
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                'email' => $utilisateur->email_utilisateur,
                'telephone' => $utilisateur->telephone_utilisateur,
                'role' => $utilisateur->role_utilisateur,
                'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
            ],
            'permissions' => $this->getPermissionsByRole($utilisateur->role_utilisateur)
        ], 201);

    } catch (\Exception $e) {
        Log::error("Erreur inscription: " . $e->getMessage());
        
        return response()->json([
            'message' => 'Erreur lors de l\'inscription',
            'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur interne'
        ], 500);
    }
}    
 public function inscriptionSuperAdmin(Request $request)
    {
        // Validation des données spécifiques super admin
        $validation = $request->validate([
            'nom_utilisateur' => 'required|string|max:255',
            'prenom_utilisateur' => 'required|string|max:255',
            'email_utilisateur' => 'required|string|email|unique:utilisateurs,email_utilisateur',
            'mot_de_passe_utilisateur' => 'required|string|min:8|confirmed',
            'cnib' => 'required|string|unique:utilisateurs,cnib',
            'date_naissance_utilisateur' => 'required|date',
            'telephone_utilisateur' => 'required|string|unique:utilisateurs,telephone_utilisateur',
            'matricule' => 'required|string|unique:super_admins,matricule',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Gestion de l'upload de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos_utilisateurs', 'public');
            }

            // Création de l'utilisateur avec rôle superadministrateur
            $utilisateur = Utilisateur::create([
                'nom_utilisateur' => $validation['nom_utilisateur'],
                'prenom_utilisateur' => $validation['prenom_utilisateur'],
                'email_utilisateur' => $validation['email_utilisateur'],
                'mot_de_passe_utilisateur' => Hash::make($validation['mot_de_passe_utilisateur']),
                'cnib' => $validation['cnib'],
                'date_naissance_utilisateur' => $validation['date_naissance_utilisateur'],
                'telephone_utilisateur' => $validation['telephone_utilisateur'],
                'photo' => $photoPath,
                'role_utilisateur' => 'superadministrateur',
            ]);

            // Création du profil SuperAdmin
            $superAdmin = SuperAdmin::create([
                'utilisateur_id' => $utilisateur->id,
                'matricule' => $validation['matricule'],
                'statut' => true,
            ]);

            // Création du token
            $token = $utilisateur->createToken('auth_token')->plainTextToken;

            Log::info("Nouveau Super Admin inscrit: {$utilisateur->email_utilisateur}");

            return response()->json([
                'message' => 'Super Admin créé avec succès',
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration'),
                'utilisateur' => [
                    'id' => $utilisateur->id,
                    'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                    'email' => $utilisateur->email_utilisateur,
                    'telephone' => $utilisateur->telephone_utilisateur,
                    'role' => $utilisateur->role_utilisateur,
                    'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
                    'matricule' => $superAdmin->matricule,
                    'statut' => $superAdmin->statut,
                ],
                'permissions' => $this->getPermissionsByRole($utilisateur->role_utilisateur)
            ], 201);

        } catch (\Exception $e) {
            Log::error("Erreur inscription Super Admin: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de la création du Super Admin',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    public function modifierSuperAdmin(Request $request, $id)
    {
        try {
            $superAdmin = SuperAdmin::with('utilisateur')->findOrFail($id);
            $utilisateur = $superAdmin->utilisateur;

            // Validation des données
            $validation = $request->validate([
                'nom_utilisateur' => 'sometimes|string|max:255',
                'prenom_utilisateur' => 'sometimes|string|max:255',
                'email_utilisateur' => 'sometimes|string|email|unique:utilisateurs,email_utilisateur,' . $utilisateur->id,
                'cnib' => 'sometimes|string|unique:utilisateurs,cnib,' . $utilisateur->id,
                'telephone_utilisateur' => 'sometimes|string|unique:utilisateurs,telephone_utilisateur,' . $utilisateur->id,
                'date_naissance_utilisateur' => 'sometimes|date',
                'matricule' => 'sometimes|string|unique:super_admins,matricule,' . $superAdmin->id,
                'statut' => 'sometimes|boolean',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Mise à jour de l'utilisateur
            if (isset($validation['nom_utilisateur'])) {
                $utilisateur->nom_utilisateur = $validation['nom_utilisateur'];
            }
            if (isset($validation['prenom_utilisateur'])) {
                $utilisateur->prenom_utilisateur = $validation['prenom_utilisateur'];
            }
            if (isset($validation['email_utilisateur'])) {
                $utilisateur->email_utilisateur = $validation['email_utilisateur'];
            }
            if (isset($validation['cnib'])) {
                $utilisateur->cnib = $validation['cnib'];
            }
            if (isset($validation['telephone_utilisateur'])) {
                $utilisateur->telephone_utilisateur = $validation['telephone_utilisateur'];
            }
            if (isset($validation['date_naissance_utilisateur'])) {
                $utilisateur->date_naissance_utilisateur = $validation['date_naissance_utilisateur'];
            }

            // Gestion de la photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($utilisateur->photo) {
                    Storage::disk('public')->delete($utilisateur->photo);
                }
                $utilisateur->photo = $request->file('photo')->store('photos_utilisateurs', 'public');
            }

            $utilisateur->save();

            // Mise à jour du SuperAdmin
            if (isset($validation['matricule'])) {
                $superAdmin->matricule = $validation['matricule'];
            }
            if (isset($validation['statut'])) {
                $superAdmin->statut = $validation['statut'];
            }

            $superAdmin->save();

            Log::info("Super Admin modifié: {$utilisateur->email_utilisateur}");

            return response()->json([
                'message' => 'Super Admin modifié avec succès',
                'super_admin' => [
                    'id' => $superAdmin->id,
                    'utilisateur_id' => $utilisateur->id,
                    'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                    'email' => $utilisateur->email_utilisateur,
                    'telephone' => $utilisateur->telephone_utilisateur,
                    'matricule' => $superAdmin->matricule,
                    'statut' => $superAdmin->statut,
                    'photo' => $utilisateur->photo ? asset('storage/' . $utilisateur->photo) : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erreur modification Super Admin: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de la modification du Super Admin',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur interne'
            ], 500);
        }
    }

    public function deconnexion(Request $request)
    {
        // Récupération de l'utilisateur
        $utilisateur = $request->user();

        // Suppression du token actuel
        $utilisateur->currentAccessToken()->delete();

        // Journalisation (optionnel)
        Log::info("Utilisateur {$utilisateur->email_utilisateur} déconnecté");

        return response()->json([
            'message' => 'Déconnexion réussie'
        ], 200);
    }

    public function deconnexionTousAppareils(Request $request)
    {
        $utilisateur = $request->user();

        // Suppression de TOUS les tokens de l'utilisateur
        $utilisateur->tokens()->delete();

        Log::info("Utilisateur {$utilisateur->email_utilisateur} déconnecté de tous les appareils");

        return response()->json([
            'message' => 'Déconnexion de tous les appareils réussie'
        ], 200);
    }
    


    
    

    // ==================== MÉTHODES UTILITAIRES ====================

    private function estUtilisateurActif(Utilisateur $utilisateur)
    {
        switch ($utilisateur->role_utilisateur) {
            case 'autorite':
                return $utilisateur->autorite && $utilisateur->autorite->statut;
            
            case 'administrateur':
                return $utilisateur->admin !== null;
            
            case 'superadministrateur':
                return $utilisateur->superAdmin !== null;
            
            case 'citoyen':
            default:
                return true; // Les citoyens sont toujours actifs
        }
    }

    private function getRelationsByRole($role)
    {
        switch ($role) {
            case 'autorite':
                return ['autorite.unite', 'autorite.organisation'];
            
            case 'administrateur':
                return ['admin.unite', 'admin.organisation'];
            
            case 'superadministrateur':
                return ['superAdmin'];
            
            default:
                return [];
        }
    }

    private function getPermissionsByRole($role)
    {
        $permissions = [
            'citoyen' => [
                'signaler_incident',
                'voir_incidents_proches', 
                'recevoir_alertes',
                'modifier_profil'
            ],
            'autorite' => [
                'voir_incidents_unite',
                'traiter_incidents',
                'creer_alertes',
                'voir_statistiques_unite'
            ],
            'administrateur' => [
                'gerer_autorites',
                'valider_incidents', 
                'creer_alertes_unite',
                'voir_statistiques_unite',
                'exporter_donnees'
            ],
            'superadministrateur' => [
                'gerer_organisations',
                'gerer_unites', 
                'gerer_utilisateurs',
                'voir_statistiques_globales',
                'acces_complet'
            ]
        ];

        return $permissions[$role] ?? [];
    }

    // ==================== MÉTHODE POUR VÉRIFIER LE TOKEN ====================

    public function verifierToken(Request $request)
    {
        $utilisateur = $request->user();
        
        if (!$utilisateur) {
            return response()->json([
                'message' => 'Token invalide'
            ], 401);
        }

        $utilisateur->load($this->getRelationsByRole($utilisateur->role_utilisateur));

        return response()->json([
            'message' => 'Token valide',
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                'email' => $utilisateur->email_utilisateur,
                'role' => $utilisateur->role_utilisateur,
                'photo' => $utilisateur->photo ? asset('storage/' . $utilisateur->photo) : null,
            ],
            'permissions' => $this->getPermissionsByRole($utilisateur->role_utilisateur)
        ], 200);
    }


 public function creerAdmin(Request $request)
{
    // Validation des données - Laravel gère automatiquement les erreurs 422
    $validation = $request->validate([
        'nom_utilisateur' => 'required|string|max:255',
        'prenom_utilisateur' => 'required|string|max:255',
        'email_utilisateur' => 'required|string|email|unique:utilisateurs,email_utilisateur',
        'mot_de_passe_utilisateur' => 'required|string|min:8|confirmed',
        'cnib' => 'nullable|string|unique:utilisateurs,cnib',
        'date_naissance_utilisateur' => 'nullable|date',
        'telephone_utilisateur' => 'required|string|unique:utilisateurs,telephone_utilisateur',
        'organisation_id' => 'required|exists:organisations,id',
        'unite_id' => 'required|exists:unites,id',
        'matricule' => 'required|string|unique:admins,matricule',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Vérifier que l'unité appartient à l'organisation
        $unite = unite::find($validation['unite_id']);
        if (!$unite || $unite->organisation_id != $validation['organisation_id']) {
            return response()->json([
                'message' => 'L\'unité sélectionnée n\'appartient pas à l\'organisation choisie'
            ], 400);
        }

        // Vérifier si cette unité a déjà un admin
        $adminExistant = Admin::where('unite_id', $validation['unite_id'])->exists();
        if ($adminExistant) {
            return response()->json([
                'message' => 'Cette unité a déjà un administrateur'
            ], 400);
        }

        // Gestion de l'upload de la photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos_utilisateurs', 'public');
        }

        // Création de l'utilisateur
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => $validation['nom_utilisateur'],
            'prenom_utilisateur' => $validation['prenom_utilisateur'],
            'email_utilisateur' => $validation['email_utilisateur'],
            'mot_de_passe_utilisateur' => Hash::make($validation['mot_de_passe_utilisateur']),
            'cnib' => $validation['cnib'] ?? null,
            'date_naissance_utilisateur' => $validation['date_naissance_utilisateur'] ?? null,
            'telephone_utilisateur' => $validation['telephone_utilisateur'],
            'photo' => $photoPath,
            'role_utilisateur' => 'administrateur',
        ]);

        // Création du profil Admin
        $admin = admin::create([
            'utilisateur_id' => $utilisateur->id,
            'organisation_id' => $validation['organisation_id'],
            'unite_id' => $validation['unite_id'],
            'matricule' => $validation['matricule'],
        ]);

        // Création du token Sanctum
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        // Récupérer l'organisation pour la réponse
        $organisation = Organisation::find($validation['organisation_id']);

        Log::info("Nouvel Admin créé: {$utilisateur->email_utilisateur} - Unité: {$unite->nom_unite}");

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'token' => $token,
            'token_type' => 'Bearer',
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                'email' => $utilisateur->email_utilisateur,
                'telephone' => $utilisateur->telephone_utilisateur,
                'role' => $utilisateur->role_utilisateur,
                'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
                'matricule' => $admin->matricule,
                'unite' => [
                    'id' => $unite->id,
                    'nom' => $unite->nom_unite,
                    'adresse' => $unite->adresse,
                ],
                'organisation' => [
                    'id' => $organisation->id,
                    'nom' => $organisation->nom_organisation,
                ],
            ],
        ], 201);

    } catch (\Exception $e) {
        Log::error("Erreur création Admin: " . $e->getMessage());
        
        return response()->json([
            'message' => 'Erreur lors de la création de l\'administrateur',
            'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur interne'
        ], 500);
    }
}

public function creerAutorite(Request $request)
{
    // Validation des données spécifiques autorité
    $validation = $request->validate([
        'nom_utilisateur' => 'required|string|max:255',
        'prenom_utilisateur' => 'required|string|max:255',
        'email_utilisateur' => 'required|string|email|unique:utilisateurs,email_utilisateur',
        'mot_de_passe_utilisateur' => 'required|string|min:8|confirmed',
        'cnib' => 'required|string|unique:utilisateurs,cnib',
        'date_naissance_utilisateur' => 'required|date',
        'telephone_utilisateur' => 'required|string|unique:utilisateurs,telephone_utilisateur',
        'organisation_id' => 'required|exists:organisations,id',
        'unite_id' => 'required|exists:unites,id',
        'matricule' => 'required|string|unique:autorites,matricule',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Vérifier que l'unité appartient à l'organisation
        $unite = \App\Models\Unite::find($validation['unite_id']);
        if (!$unite || $unite->organisation_id != $validation['organisation_id']) {
            return response()->json([
                'message' => 'L\'unité sélectionnée n\'appartient pas à l\'organisation choisie'
            ], 400);
        }

        // Gestion de l'upload de la photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos_utilisateurs', 'public');
        }

        // Création de l'utilisateur avec rôle autorité
        $utilisateur = Utilisateur::create([
            'nom_utilisateur' => $validation['nom_utilisateur'],
            'prenom_utilisateur' => $validation['prenom_utilisateur'],
            'email_utilisateur' => $validation['email_utilisateur'],
            'mot_de_passe_utilisateur' => Hash::make($validation['mot_de_passe_utilisateur']),
            'cnib' => $validation['cnib'],
            'date_naissance_utilisateur' => $validation['date_naissance_utilisateur'],
            'telephone_utilisateur' => $validation['telephone_utilisateur'],
            'photo' => $photoPath,
            'role_utilisateur' => 'autorite',
        ]);

        // Création du profil Autorité
        $autorite = \App\Models\Autorite::create([
            'utilisateur_id' => $utilisateur->id,
            'unite_id' => $validation['unite_id'],
            'matricule' => $validation['matricule'],
            'statut' => true,
        ]);

        // Création du token
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        Log::info("Nouvelle Autorité créée: {$utilisateur->email_utilisateur} - Unité: {$unite->nom_unite}");

        return response()->json([
            'message' => 'Autorité créée avec succès',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration'),
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom_complet' => $utilisateur->prenom_utilisateur . ' ' . $utilisateur->nom_utilisateur,
                'email' => $utilisateur->email_utilisateur,
                'telephone' => $utilisateur->telephone_utilisateur,
                'role' => $utilisateur->role_utilisateur,
                'photo' => $photoPath ? asset('storage/' . $photoPath) : null,
                'matricule' => $autorite->matricule,
                'statut' => $autorite->statut,
                'unite' => [
                    'id' => $unite->id,
                    'nom' => $unite->nom_unite,
                    'organisation' => $unite->organisation->nom_organisation,
                ],
            ],
            'permissions' => $this->getPermissionsByRole($utilisateur->role_utilisateur)
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error("Erreur création Autorité: " . $e->getMessage());
        
        return response()->json([
            'message' => 'Erreur lors de la création de l\'autorité',
            'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erreur interne'
        ], 500);
    }
}


}
