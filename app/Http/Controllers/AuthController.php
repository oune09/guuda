<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function inscription(Request $request)
    {
      
        $regles = [
            'nom_utilisateur'=>'required|string|max:10',
            'prenom_utilisateur'=>'required|string|max:20',
            'email_utilisateur'=>'required|string|email|max:50|unique:utilisateurs',
            'mot_de_passe'=>'required|string|min:8|confirmed',
            'cnib'=>'required|string|min:8|unique:utilisateurs',
            'date_naissance_utilisateur'=>'required|date',
            'telephone_utilisateur'=>'required|string|min:8|unique:utilisteurs',
            'photo'=>'nullable|image',
            'role_utilisateur'=>'required|enum:citoyen,autorite,administrateur',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',

        ];
        
        if($request->role_utilisateur == 'autorite')
        {
           $regles->array_merge($regles,[
            'organisation'=>'required|string',
            'matricule'=>'required|string|unique:autorites',
            'zone_responsabilite'=>'required|string',
            'statut'=>'boolean',
            'utilisateur_id'=>'required/integer|exists:utilisateurs,id',
            
           ]);

        }

        return response()->json(['message'=>'utilisateur inscrit avec succes'],201);
        
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

        
        if($request->role_utilisateur == 'autorite')
        {
            autorite::create([
                'utilisateur_id'=>$utilisateur->id,
                'organisation'=>$validation['organisation'],
                'matricule'=>$validation['matricule'],
                'zone_responsabilite'=>$validation['zone_responsabilite'],
                'statut'=>$validation['statut'],

            ]);
        }
        
        
    }

    public function connexion(request $request)
    {
        $validation = $request->validate([
            'email_utilisateur'=>'requeried|string|email',
            'mot_de_passe'=>'requeried|string|min:8',
        ]);

        return response()->json(['message'=>'utilisateur connecte avec succes'],200);
    }

    
    
    
}