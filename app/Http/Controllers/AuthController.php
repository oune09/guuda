<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\utilisateur;

class AuthController extends Controller
{
    public function inscription(Request $request)
    {
      
        $regles = [
            'nom_utilisateur'=>'required|string|max:10',
            'prenom_utilisateur'=>'required|string|max:20',
            'email_utilisateur'=>'required|string|email|max:50|unique:utilisateurs',
            'mot_de_passe'=>'required|string|min:8|confirmed',
            'mot_de_passe_confirmation'=>'required|string|min:8',
            'cnib'=>'required|string|min:8|unique:utilisateurs',
            'date_naissance_utilisateur'=>'required|date',
            'telephone_utilisateur'=>'required|string|min:8|unique:utilisateurs',
            'photo'=>'nullable|image',
            'role_utilisateur'=>'required|in:citoyen,autorite,administrateur',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string|max:50',

        ];
        
        if($request->role_utilisateur == 'autorite')
        {
           $regles=array_merge($regles,[
            'organisation'=>'required|string',
            'matricule'=>'required|string|unique:autorites',
            'zone_responsabilite'=>'required|string',
            'statut'=>'in:actif,inactif',
            'utilisateur_id'=>'required|integer|exists:utilisateurs,id',
            
           ]);

        }
        
        $photo_path = null;
        if($request->hasFile('photo'))
        {
           $photo_path = $request->file('photo')->store('photo_utilisateur','public');
        }
         
        $validation = $request->validate($regles);

        $utilisateur = utilisateur::create([
         'nom_utilisateur'=>$validation['nom_utilisateur'],
         'prenom_utilisateur'=>$validation['prenom_utilisateur'],
         'email_utilisateur'=>$validation['email_utilisateur'],
         'mot_de_passe_utilisateur'=>bcrypt($validation['mot_de_passe']),
         'cnib'=>$validation['cnib'],
         'date_naissance_utilisateur'=>$validation['date_naissance_utilisateur'],
         'telephone_utilisateur'=>$validation['telephone_utilisateur'],
         'photo'=>$photo_path,
         'role_utilisateur'=>$validation['role_utilisateur'],
         'ville'=>$validation['ville'],
         'secteur'=>$validation['secteur'],
         'quartier'=>$validation['quartier'],
        ]);

        
        if($request->role_utilisateur == 'autorite' || $request->role_utilisateur == 'administrateur')
        {
            autorite::create([
                'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$validation['organisation'],
                'matricule'=>$validation['matricule'],
                'zone_responsabilite'=>$validation['zone_responsabilite'],
                'statut'=>$validation['statut'],

            ]);
        }
        
        return response()->json(['message'=>'utilisateur inscrit avec succes'],201);
        
    }

    public function connexion(Request $request)
    {
        $validation = $request->validate([
            'email_utilisateur'=>'required|string|email',
            'mot_de_passe'=>'required|string|min:8',
        ]);
        $utilisateur = utilisateur::where('email_utilisateur',$validation['email_utilisateur'])->first();
        if(!$utilisateur || !Hash::check($validation['mot_de_passe'],$utilisateur->mot_de_passe_utilisateur))
        {
             return response()->json(['message'=>'identifiant ou mot de passe incorrect',400]);

        }
        $token = $utilisateur->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Connexion réussie',
        'token' => $token,
        'role' => $utilisateur->role_utilisateur, // renvoyer le rôle
        'utilisateur' => $utilisateur,
    ]);
    }
    public function listeAutorite(Request $request)
    {
        $autorite = autorite::query();
        if($request->has('staut'))
        {
            $autorite->where('statut',$request->statut);
        }

        if($request->has('organisation'))
        {
            $autorite->where('organisation',$request->organisation);
        }

        if($request->has('zone_reponsabilite'))
        {
            $autorite->where('zone_responsabilite',$request->zone_responbilite);
        }
        $token = $utilisateur->createToken('auth_token')->plainTextToken;
        return response()->json($autorite->get(),200);
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
 
    public function affecterRole(Request $request, $id)
    {
        $validation = $request->validate([
            'role_utilisateur'=>'required|enum:citoyen,autorite,administrateur',
            'organisation'=>'required_if:role_utilisateur,autorite|string',
            'matricule'=>'required_if:role_utilisateur,autorite|string|unique:autorites',
            'zone_responsabilite'=>'required_if:role_utilisateur,autorite|string',
            'statut'=>'required_if:role_utilisateur,autorite|enum:actif,inactif',
        ]);
        
        $utilisateur =utilisateur::find($id);
        
        if(!$utilisateur)
        {
            return response()->json(['message'=>'utilisateur non trouve'],404);
        }

        $utilisateur->role_utilisateur = $validation['role_utilisateur'];
        $utilisateur->save();

        if($validation['role_utilisateur'] == 'autorite')
        {
            autorite::create([
                'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$validation['organiastion'],
                'matricule'=>$validation['matricule'],
                'zone_responsabilite'=>$validation['zone_responsabilite'],
                'statut'=>$validation['statut'],
            ]);
        }
        return response()->json(['message'=>'role affecter avec succes'],200);
    }

    public function  modifierUtilisateur(Request $request,$id)
    {   $validation = $request->validate([
        'nom_utilisateur'=>'required|string',
        'prenom_utilisateur'=>'required|string',
        'email_utilisateur'=>'required|string|email',
        'date_naissance_utilisateur'=>'required|date',
        'telephone_utilisateur'=>'required|string',
        'ville'=>'required|string',
        'secteur'=>'required|string',
        'quartier'=>'required|string',
        'photo'=>'nullable|image',
        'organisation'=>'required_if:role_utilisateur,autorite,administrateur|string',
        'matricule'=>'required_if:role_utilisateur,autorite,administrateur|string|unique:autorites',
        'Zone_responsabilite'=>'required_if:role_utilisateur,autorite,administrateur|string',
        'statut'=>'required_if:role_utilisateur,autorite|enum:actif,inactif',

    ]);
        $utilisateur = utilisateur::find($id);
        if(!$utilisateur)
        {
            return response()->json(['message'=>'utilisateur non trouve']);
        }
        
        $utilisateur->update([
         'nom_utilisateur'=>$validation['nom_utilisateur'],
         'prenom_utilisateur'=>$validation['prenom_utilisateur'],
         'email_utilisateur'=>$validation['email_utilisateur'],
         'mot_de_passe'=>bcrypt($validation['mot_de_passe']),
         'cnib'=>$validation['cnib'],
         'date_naissance_utilisateur'=>$validation['date_naissance_utilisateur'],
         'telephone_utilisateur'=>$validation['telephone_utilisateur'],
         'photo'=>$photo_path,
         'role_utilisateur'=>$validation['role_utilisateur'],
         'ville'=>$validation['ville'],
         'secteur'=>$validation['secteur'],
         'quartier'=>$validation['quartier'],
        ]);

        if($request->hasFile('photo'))
        {
            $photo_path = $request->file('phote');
            $utilisateur->phote = $photo_path;
        }

        if($utilisateur->role_utilisateur == 'autorite' ||$utilisateur->role_utilisateur == 'administrateur')
        {
           $autorite = autorite::find($utilisateur->id);

           $autorite->update([
            'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$validation['organiastion'],
                'matricule'=>$validation['matricule'],
                'zone_responsabilite'=>$validation['zone_responsabilite'],
                'statut'=>$validation['statut'],
           ]);
        }
        
    }

    
    
}