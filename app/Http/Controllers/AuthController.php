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
         'ville_id'=>$validation['ville_id'],
         'secteur_id'=>$validation['secteu_idr'],
        ]);

        
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

    public function deconnexion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
    
}