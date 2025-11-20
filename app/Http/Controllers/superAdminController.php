<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\admin ;
use App\Models\ville;
use App\Models\secteur ;
use App\Models\utilisateur ;
use App\Models\autorite ;
use App\Models\unite ;
use App\Models\organisation;

class superAdminController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth:sanctum');
       // $this->middleware('superadmin');
    }
    public function creerSecteur(Request $request)
  {
   $regle = [
    'nom_secteur'=>'required|string',
    'ville_id'=>'required|integer|exists:ville,id',
    'superAdmin_id'=>'required|integer|exists:superAdmin,id',
   ];

     $validation = $request->validate($regle);

    if (Secteur::where('nom_secteur', $validation['nom_secteur'])
          ->where('ville_id', $validation['ville_id'])->exists()) {
      return response()->json(['message' => 'Ce secteur existe déjà dans cette ville'], 400);
    }

   $secteur = Secteur::create([
    'nom_secteur'=>$validation['nom_secteur'],
    'ville_id'=>$validation['ville_id'],
    'superAdmin_id'=>$validation['superAdmin_id'],
   ]);

  }
 
  public function supprimerSecteur($id)
  {
    $secteur = Secteur::find($id);
    
    if(!$secteur)
    {
      return response()->json(['message'=>'secteur non trouve'],200);
    }
    $secteur->delete();
    return response()->json(['message'=>'secteur supprimer avec succes'],200);
  }

  public function modifierSecteur(Request $request,$id)
  {
    $secteur = Secteur::find($id);

    if(!$secteur)
    {
      return response()->json(['message'=>'secteur non trouve'],200);
    }
    
    $regle=[
      'nom_secteur'=>'required|string',
      'ville_id'=>'required|integer|exists:ville,id',
      'superadmin_id'=>'required|integer|exists:superadmin,id',
    ];
    $validation = $request->validate($regle);
    $secteur->update([
       'nom_secteur'=>$validation['nom_secteur'],
       'ville_id'=>$validation['ville_id'],
       'superAdmin_id'=>$validation['superAdmin_id'],
    ]);
    return response()->json(['message'=>'secteur modifier avec succes'],200);
  }

   public function listeSecteur(Request $request)
  {
    $secteur = Secteur::all();
    return response()->json(['secteur'=>$secteur],200);
  }

    public function creerOrganisation(Request $request)
  {
   $regle = [
    'nom_organisation'=>'required|string',
    'superAdmin_id'=>'required|integer|exists:superAdmin,id',
   ];

   $validation = $request->validate($regle);
    if (Organisation::where('nom_organisation', $validation['nom_organisation'])->exists())
    {
      return response()->json(['message' => 'Cette organisation existe déjà'], 400);
    }
   $organisation = Organisation::create([
    'nom_organisation'=>$validation['nom_organisation'],
    'superAdmin_id'=>$validation['superAdmin_id'],
   ]);

  }

  public function supprimerOrganisation($id)
  {
    $organisation = Organisation::find($id);
    
    if(!$organisation)
    {
      return response()->json(['message'=>'organisation non trouve'],200);
    }
    $organisation->delete();
    return response()->json(['message'=>'organisation supprimer avec succes'],200);
  }

  public function modifierOrganisation(Request $request,$id)
  {
    $organisation = Organisation::find($id);
    if(!$organisation)
    {
      return response()->json(['message'=>'organisation non trouve'],200);
    }
    $regle=[
      'nom_organisation'=>'required|string',
      'superadmin_id'=>'required|integer|exists:superadmin,id',
    ];
    $validation = $request->validate($regle);
    $organisation->update([
      'nom_organisation'=>$validation['nom_organisation'],
      'superAdmin_id'=>$validation['superAdmin_id'],
    ]);
    return response()->json(['message'=>'organisation modifier avec succes']);
  } 
  
  public function listeOrganisation(Request $request)
  {
    $organisation = Organisation::all();
    return response()->json(['organisation'=>$organisation],200);
  }

  public function creerVille(Request $request)
  {
   $regle = [
    'nom_ville'=>'required|string',
    'superAdmin_id'=>'required|integer|exists:superAdmin,id',
   ];

   $validation = $request->validate($regle);
   
   $ville = Ville::create([
    'nom_ville'=>$validation['nom_ville'],
    'superAdmin_id'=>$validation['superAdmin_id'],
   ]);
    return response()->json(['message'=>'ville creer avec succes'], 200);
  }

  public function supprimerVille($id)
  {
    $ville = Ville::find($id);
    
    if(!$ville)
    {
      return response()->json(['message'=>'ville non trouve'],200);
    }
    $ville->delete();
    return response()->json(['message'=>'ville supprimer avec succes'],200);
  }

  public function modifierVille(Request $request, $id)
  {
    $regle=[
      'nom_ville'=>'required|string',
      'superadmin_id'=>'required|integer|exists:superadmin,id',
    ];
    $validation = $request->validate($regle);

    $ville = Ville::find($id);
    if(!$ville)
    {
      return response()->json(['message'=>'ville non trouve'],200);
    }
    
    $ville->update([
      'nom_ville'=> $validation['nom_ville'],
      'superAdmin_id'=> $validation['superAdmin_id'],
    ]);
    return response()->json(['message'=>'ville modifier avec succes'],200);
  } 
  
  public function listeVille(Request $request)
  {
    $villes = Ville::all();
    return response()->json(['villes'=>$villes],200);
  }

  public function promouvoireAutorite(Request $request,$id)
  {
    $regle = [
        'nouveau_role' => 'required|in:citoyen,autorite,administrateur',
        'unite_id' => 'required_if:nouveau_role,administrateur|exists:unites,id',
        
    ];

    $validation = $request->validate($regle);
    $autorite = Autorite::findOrFail($id);
    $utilisateur = $autorite->utilisateur;

     if($autorite->role_utilisateur !== 'autorite')
     {
       return response()->json(['message'=>'utilisateur n est pas une autorite'],400);
     }
     $organisation = $autorite->organisation_id;
     $matricule = $autorite->matricule;
     
     Admin::create([
                'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$organisation,
                'matricule'=>$matricule,
                'unite_id'=>$validation['unite_id'],
                'statut'=>$validation['statut'],
       
    ]);
    $autorite->delete();
    $utilisateur->update(['role_utilisateur' => 'administrateur']);

    return response()->json([
        'message' => 'Autorité promue en administrateur avec succès',
        'utilisateur' => $utilisateur->load('admin')
    ], 200);
  }

  public function creerAutorite(Request $request,$id)
  {
    $regle = [
        'nouveau_role' => 'required|in:citoyen,autorite,administrateur',
        'unite_id' => 'required_if:nouveau_role,autorite|exists:unites,id',
        'organisation_id' => 'required_if:nouveau_role,autorite|exists:unites,id',
        'matricule' => 'required_if:nouveau_role,autorite|string|unique:autorites,matricule',
    ];

    $validation = $request->validate($regle);
    $citoyen = Utilisateur::findOrFail($id);
    $utilisateur = $citoyen->utilisateur;

     if($citoyen->role_utilisateur !== 'citoyen')
     {
       return response()->json(['message'=>'utilisateur n est pas un citoyen'],400);
     }
     
     Autorite::create([
                'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$validation['organisation_id'],
                'matricule'=>$validation['matricule'],
                'unite_id'=>$validation['unite_id'],
                'statut'=>$validation['statut'],
       
    ]);
    $utilisateur->update(['role_utilisateur' => 'autorite']);

    return response()->json([
        'message' => 'Autorité promue en autorite avec succès',
        'utilisateur' => $utilisateur->load('admin')
    ], 200);
  }

   public function retograderAdmin(Request $request, $id)
   {
    // Validation
    $regle = [
        'nouveau_role' => 'required|in:autorite', // on ne gère que ce cas
        'unite_id' => 'required|exists:unites,id',
        'matricule' => 'required|string|unique:autorites,matricule',
    ];

    $validation = $request->validate($regle);

    // Récupération admin
    $admin = Admin::findOrFail($id);
    $utilisateur = $admin->utilisateur;

    if ($utilisateur->role_utilisateur !== 'administrateur') {
        return response()->json(['message' => 'Cet utilisateur n\'est pas un administrateur'], 400);
    }

    // Récupération des données admin
    $organisation_id = $admin->organisation_id;
    $matricule = $validation['matricule'];
    $statut = $admin->statut ?? 'actif'; // sécurité si statut n'existe pas

    // Création autorité
    Autorite::create([
        'utilisateur_id' => $utilisateur->id,
        'organisation_id' => $organisation_id,
        'matricule' => $matricule,
        'unite_id' => $validation['unite_id'],
        'statut' => $statut,
    ]);

    // Suppression admin
    $admin->delete();

    // Mise à jour utilisateur
    $utilisateur->update(['role_utilisateur' => 'autorite']);

    return response()->json([
        'message' => 'Administrateur rétrogradé en autorité avec succès',
        'utilisateur' => $utilisateur->load('autorite')
    ], 200);
  }

  public function affecterSecteur(Request $request, $uniteId)
  {
    $validation = $request->validate([
        'secteur_ids' => 'required|array',
        'secteur_ids.*' => 'integer|exists:secteurs,id',
    ]);

    $unite = Unite::findOrFail($uniteId);

    // sync() remplace les anciens secteurs par les nouveaux
    $unite->secteurs()->attach($validation['secteur_ids']);

    return response()->json([
        'message' => 'Secteurs affectés à l unité avec succès',
        'secteurs' => $unite->secteurs
    ]);
  }
  public function retierSecteur(Request $request, $uniteId)
{
    $validation = $request->validate([
        'secteur_ids' => 'required|array',
        'secteur_ids.*' => 'integer|exists:secteurs,id',
    ]);

    $unite = Unite::findOrFail($uniteId);

    // sync() remplace les anciens secteurs par les nouveaux
    $unite->secteurs()->detach($validation['secteur_ids']);

    return response()->json([
        'message' => 'Secteurs retirer à l unité avec succès',
        'secteurs' => $unite->secteurs->get(),
    ]);
    }

    public function supprimerAutorite($id)
    {
        $autorite = autorite::find($id);
        if(!$autorite)
        {
          return response()->json(['message'=>'autorite non trouve'],404);
        }
        $autorite->delete();
        return response()->json(['message'=>'autorite supprime avec succes'],200);
        
    }

      public function listeAutorite(Request $request)
    {
        $autorites = Autorite::query()->with(['utilisateur', 'unite', 'organisation', 'ville', 'secteur']);

        if ($request->has('statut'))
        {
        $autorites->where('statut', $request->statut);
        }

        if ($request->has('organisation_id'))
        {
        $autorites->where('organisation_id', $request->organisation_id);
        }

        if ($request->has('secteur_id')) 
        {
        $autorites->where('secteur_id', $request->secteur_id);
        }

        if ($request->has('ville_id')) 
        {
        $autorites->where('ville_id', $request->ville_id);
        }

        if ($request->has('unite_id'))
        {
        $autorites->where('unite_id', $request->unite_id);
        }

        return response()->json([
            'success' => true,
            'autorites' => $autorites->get()
           ], 200);
    }

}