<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Models\Admin;
use App\Models\Utilisateur;
use App\Models\Autorite;
use App\Models\Incident;
use App\Models\Unite;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
        // $this->middleware('superadmin');
    }

    public function creerOrganisation(Request $request)
    {   
        $regles = [
            'nom_organisation' => 'required|string|unique:organisations,nom_organisation',
            'description' => 'nullable|string',
            'telephone'=>'required|string',
            'mail_organisation'=>'required|string',
            'logo'=>'nullable|image',
            'adresse_siege'=> 'required|string',
        ];

        $validation = $request->validate($regles);
         
        $logopath=null;

        if(request()->hasfile('logo')){
            $logopath = $request->file('logo')->store('logo','public');
        }



        $organisation = Organisation::create([
            'nom_organisation' => $validation['nom_organisation'],
            'description'=> $validation['description'],
            'telephone'=> $validation['telephone'],
            'mail_organisation'=> $validation['mail_organisation'],
            'logo'=> $logopath,
            'adresse_siege'=> $validation['adresse_siege'],

        ]);

        return response()->json([
            'message' => 'Organisation créée avec succès',
            'organisation' => $organisation
        ], 201);
    }

    public function supprimerOrganisation($id)
    {
        $organisation = Organisation::find($id);
        
        if (!$organisation) {
            return response()->json(['message' => 'Organisation non trouvée'], 404);
        }

        // Vérifier si l'organisation a des unités
        if ($organisation->unites()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette organisation car elle contient des unités'
            ], 400);
        }

        $organisation->delete();

        return response()->json(['message' => 'Organisation supprimée avec succès'], 200);
    }

    public function modifierOrganisation(Request $request, $id)
    {
        $organisation = Organisation::find($id);
        
        if (!$organisation) {
            return response()->json(['message' => 'Organisation non trouvée'], 404);
        }

        $regles = [
            'nom_organisation' => 'required|string|unique:organisations,nom_organisation,' . $id,
            'type_organisation' => 'required|in:police,pompier,medical,municipale',
        ];

        $validation = $request->validate($regles);

        $organisation->update([
            'nom_organisation' => $validation['nom_organisation'],
            'type_organisation' => $validation['type_organisation'],
        ]);

        return response()->json([
            'message' => 'Organisation modifiée avec succès',
            'organisation' => $organisation
        ], 200);
    }

    public function listeOrganisation(Request $request)
    {
        $organisations = Organisation::withCount('unites')->get();
        return response()->json(['organisations' => $organisations], 200);
    }

    public function creerUnite(Request $request)
    {  
        $regles = [
            'nom_unite' => 'required|string',
            'organisation_id' => 'required|exists:organisations,id',
            'adresse' => 'required|string',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'capacite_unite' => 'required|numeric',
            'mail_unite' => 'nullable|string',
            'rayon_intervention' => 'required|numeric',
            'telephone_unite' => 'required|string',
        ];

        $validation = $request->validate($regles);

        // Vérifier l'unicité nom/organisation
        if (Unite::where('nom_unite', $validation['nom_unite'])
                ->where('organisation_id', $validation['organisation_id'])->exists()) {
            return response()->json([
                'message' => 'Cette unité existe déjà dans cette organisation'
            ], 400);
        }


        $unite = Unite::create([
            'nom_unite' => $validation['nom_unite'],
            'organisation_id' => $validation['organisation_id'],
            'adresse' => $validation['adresse'],
            'longitude' => null,
            'latitude' => null,
            'capacite_unite' => $validation['capacite_unite'],
            'mail_unite' => $validation['mail_unite'],
            'rayon_intervention' => $validation['rayon_intervention'],
            'telephone_unite' => $validation['telephone_unite'],
            
        ]);

        return response()->json([
            'message' => 'Unité créée avec succès',
            'unite' => $unite
        ], 201);
    }

    public function modifierUnite(Request $request, $id)
    {
        $unite = Unite::find($id);
        
        if (!$unite) {
            return response()->json(['message' => 'Unité non trouvée'], 404);
        }

        $regles = [
            'nom_unite' => 'required|string',
            'adresse' => 'required|string',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'capacite_unite' => 'required|numeric',
            'mail' => 'required|string',
            'rayon_intervention' => 'required|string',
            'telephone' => 'required|string',
        ];

        $validation = $request->validate($regles);

        $unite->update($validation);

        return response()->json([
            'message' => 'Unité modifiée avec succès',
            'unite' => $unite
        ], 200);
    }

    public function supprimerUnite($id)
    {
        $unite = Unite::find($id);
        
        if (!$unite) {
            return response()->json(['message' => 'Unité non trouvée'], 404);
        }

        // Vérifier si l'unité a des autorités
        if ($unite->autorites()->exists() || $unite->admin()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette unité car elle contient du personnel'
            ], 400);
        }

        $unite->delete();

        return response()->json(['message' => 'Unité supprimée avec succès'], 200);
    }

    

    public function promouvoirEnAutorite(Request $request, $id)
    {
        $regles = [
            'organisation_id' => 'required|exists:organisations,id',
            'unite_id' => 'required|exists:unites,id',
            'matricule' => 'required|string|unique:autorites,matricule',
        ];

        $validation = $request->validate($regles);

        $utilisateur = Utilisateur::find($id);
        
        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if (!$utilisateur->estCitoyen()) {
            return response()->json([
                'message' => 'Seul un citoyen peut être promu en autorité'
            ], 400);
        }

        DB::transaction(function () use ($utilisateur, $validation) {
            Autorite::create([
                'utilisateur_id' => $utilisateur->id,
                'organisation_id' => $validation['organisation_id'],
                'unite_id' => $validation['unite_id'],
                'matricule' => $validation['matricule'],
            ]);

            $utilisateur->update([
                'role_utilisateur' => 'autorite'
            ]);
        });

        return response()->json([
            'message' => 'Citoyen promu en autorité avec succès',
            'utilisateur' => $utilisateur->load('autorite')
        ], 200);
    }

    public function promouvoirEnAdmin(Request $request, $id)
    {
        $regles = [
            'unite_id' => 'required|exists:unites,id',
        ];

        $validation = $request->validate($regles);

        $autorite = Autorite::find($id);
        
        if (!$autorite) {
            return response()->json(['message' => 'Autorité non trouvée'], 404);
        }

        $utilisateur = $autorite->utilisateur;

        if (!$utilisateur->estAutorite()) {
            return response()->json([
                'message' => 'Seule une autorité peut être promue en administrateur'
            ], 400);
        }

       
        $adminExistant = Admin::where('unite_id', $validation['unite_id'])->exists();
        if ($adminExistant) {
            return response()->json([
                'message' => 'Cette unité a déjà un administrateur'
            ], 400);
        }

        DB::transaction(function () use ($utilisateur, $autorite, $validation) {
            Admin::create([
                'utilisateur_id' => $utilisateur->id,
                'organisation_id' => $autorite->organisation_id,
                'unite_id' => $validation['unite_id'],
                'matricule' => $autorite->matricule,
            ]);

            $autorite->delete();

            $utilisateur->update([
                'role_utilisateur' => 'administrateur'
            ]);
        });

        return response()->json([
            'message' => 'Autorité promue en administrateur avec succès',
            'utilisateur' => $utilisateur->load('admin')
        ], 200);
    }

    public function retrograderAdmin(Request $request, $id)
    {
        $regles = [
            'unite_id' => 'required|exists:unites,id',
            'matricule' => 'required|string|unique:autorites,matricule',
        ];

        $validation = $request->validate($regles);

        $admin = Admin::find($id);
        
        if (!$admin) {
            return response()->json(['message' => 'Administrateur non trouvé'], 404);
        }

        $utilisateur = $admin->utilisateur;

        if (!$utilisateur->estAdministrateur()) {
            return response()->json([
                'message' => 'Cet utilisateur n\'est pas un administrateur'
            ], 400);
        }

        DB::transaction(function () use ($utilisateur, $admin, $validation) {
            Autorite::create([
                'utilisateur_id' => $utilisateur->id,
                'organisation_id' => $admin->organisation_id,
                'unite_id' => $validation['unite_id'],
                'matricule' => $validation['matricule'],
                'statut' => true,
            ]);

            $admin->delete();

            $utilisateur->update([
                'role_utilisateur' => 'autorite'
            ]);
        });

        return response()->json([
            'message' => 'Administrateur rétrogradé en autorité avec succès',
            'utilisateur' => $utilisateur->load('autorite')
        ], 200);
    }
    
    public function supprimerAutorite($id)
    {
        $autorite = Autorite::find($id);
        
        if (!$autorite) {
            return response()->json(['message' => 'Autorité non trouvée'], 404);
        }

        $utilisateur = $autorite->utilisateur;

        DB::transaction(function () use ($autorite, $utilisateur) {
            $autorite->delete();
            
            $utilisateur->update([
                'role_utilisateur' => 'citoyen'
            ]);
        });

        return response()->json([
            'message' => 'Autorité supprimée avec succès'
        ], 200);
    }

    public function listeAutorite(Request $request)
    {
        $autorites = Autorite::with(['utilisateur', 'unite', 'organisation'])->get();

        return response()->json([
            'success' => true,
            'autorites' => $autorites
        ], 200);
    }

    public function creerSuperAdmin(Request $request)
    {
        // Validation plus robuste
        $validator = Validator::make($request->all(), [
            'nom_utilisateur' => 'required|string|max:255',
            'prenom_utilisateur' => 'required|string|max:255',
            'email_utilisateur' => 'required|email|unique:utilisateurs,email_utilisateur',
            'cnib' => 'required|string|unique:utilisateurs,cnib',
            'mot_de_passe_utilisateur' => 'required|min:6',
            'date_naissance_utilisateur' => 'required|date',
            'telephone_utilisateur' => 'required|string|unique:utilisateurs,telephone_utilisateur',
            'matricule' => 'required|string|unique:super_admins,matricule',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Création de l'utilisateur
            $user = Utilisateur::create([
                'nom_utilisateur' => $request->nom_utilisateur,
                'prenom_utilisateur' => $request->prenom_utilisateur,
                'email_utilisateur' => $request->email_utilisateur,
                'cnib' => $request->cnib,
                'mot_de_passe_utilisateur' => Hash::make($request->mot_de_passe_utilisateur),
                'date_naissance_utilisateur' => $request->date_naissance_utilisateur,
                'telephone_utilisateur' => $request->telephone_utilisateur,
                'role_utilisateur' => 'superadministrateur',
            ]);

            // 2️⃣ Création du super admin lié à l'utilisateur
            $superAdmin = SuperAdmin::create([
                'utilisateur_id' => $user->id,
                'matricule' => $request->matricule,
                'statut' => true,
            ]);

            DB::commit();

            return response()->json([
                "message" => "Super administrateur créé avec succès",
                "super_admin" => $superAdmin->load('utilisateur')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                "message" => "Erreur lors de la création du super administrateur",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    
    public function organisation(Request $request)
    {
        $organisation = Organisation::all();
        return response()->json($organisation);
    }

    public function admin(Request $request)
    {
        $admin = Admin::all();
        return response()->json($admin);
    }

    public function listeUnite(Request $request)
{
    $unites = Unite::with('organisation')->get(); 
    return response()->json(['unites' => $unites], 200); 
}

    

    public function listeUtilisateur(Request $request)
    {
       $utilisateur= Utilisateur::with('incident')->get();
       return response()->json(['utilisateurs'=> $utilisateur],200);
    }
    
    public function listeAdmin(Request $request)
    {
      $admin = Admin::with('unite','organisation','utilisateur')->get();
      return response()->json(['admins' => $admin], 200);
    }

    public function detailUnite(Request $request, $id)
    {
        $unite = Unite::findOrFail($id);
        return response()->json($unite);
    }

    public function uniteCoordonnee(Request $request, $id)
    {   
        $unite = Unite::find($id);

        $regles = [
            'longitude'=> 'required|numeric',
            'latitude'=> 'required|numeric',
        ];
        
        $validation = $request->validate([$regles]);

        $unite->update([
            'longitude'=> $validation['longitude'],
            'latitude'=> $validation['latitude'],
        ]);

    }
    // ... (les autres méthodes restent similaires mais avec corrections)
}

