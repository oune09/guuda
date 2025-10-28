<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function inscription(Request $request)
    {
      
        $validation = $resquest->validate([
            'nom_utilisateur'=>'required/string/max:10',
            'prenom_utilisateur'=>'required/string/max:20',
            'email_utilisateur'=>'required/string/email/max:50/unique:utilisateurs',
            'mot_de_passe'=>'required/string/min:8/confirmed',
            'cnib'=>'required/string/min:8/unique:utilisateurs',
            'date_naissance_utilisateur'=>'required/date',
            'telephone_utilisateur'=>'required/string/min:8/unique:utilisteurs',
            'photo'=>'nullable/image',
            'role_utilisateur'=>'required/enum:citoyen,autorite,administrateur',
            'ville'=>'required/string',
            'secteur'=>'required/string',
            'quartier'=>'required/string',

        ]);
        
        if($role_utilisateur == 'autorite')
        {
           $validation->validate([
            'organisation'=>'required/string',
            'matricule'=>'required/string/unique:autorites',
            'zone_responsabilite'=>'required/string',
            'statut'=>'boolean',
            'utilisateur_id'=>'required/integer/exists:utilisateurs,id',
            
           ]);
        }

        
        
        
    }
}
