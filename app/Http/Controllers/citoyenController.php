<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Preuve;
use App\Models\Autorite;
use App\Models\utilisateur;

class citoyenController extends Controller
{
     public function  modifierUtilisateur(Request $request,$id)
    {   $validation = $request->validate([
        'nom_utilisateur'=>'required|string',
        'prenom_utilisateur'=>'required|string',
        'email_utilisateur'=>'required|string|email',
        'date_naissance_utilisateur'=>'required|date',
        'telephone_utilisateur'=>'required|string',
        'ville_id'=>'required|string',
        'secteur_id'=>'required|string',
        'photo'=>'nullable|image',
        'matricule'=>'required_if:role_utilisateur,autorite,administrateur|string|unique:autorites',
        'statut'=>'required_if:role_utilisateur,autorite|enum:actif,inactif',

    ]);
        $utilisateur = utilisateur::find($id);
        if(!$utilisateur)
        {
            return response()->json(['message'=>'utilisateur non trouve']);
        }
        if($request->hasFile('photo'))
        {
            $photo_path = $request->file('phote');
            $utilisateur->phote = $photo_path;
        }
        $utilisateur->update([
         'nom_utilisateur'=>$validation['nom_utilisateur'],
         'prenom_utilisateur'=>$validation['prenom_utilisateur'],
         'email_utilisateur'=>$validation['email_utilisateur'],
         'mot_de_passe'=>bcrypt($validation['mot_de_passe']),
         'cnib'=>$validation['cnib'],
         'date_naissance_utilisateur'=>$validation['date_naissance_utilisateur'],
         'telephone_utilisateur'=>$validation['telephone_utilisateur'],
         'photo'=> $photo_path,
         'ville_id'=>$validation['ville_id'],
         'secteur_id'=>$validation['secteur_id'],
        ]);

        return response()->json(['message'=>'utilisateur modifie avec succes'],200);
        
    }
    public function creeIncident(Request $request)
    {
        $regles = [
            'utilisateur_id'=>'required|integer|exists:utilisateurs,id',
            'type_incident'=>'required|string',
            'description_incident'=>'required|string',
            'priorite'=>'required|string|enum:faible,moyenne,elevee',
            'statut_incident'=>'required|string|enum:ouvert,ferme,en_cours',
            'quartier'=>'required|string',
            'secteur'=>'required|string',
            'ville'=>'required|string',
        ];

        $validation = $request->validate($regles);

        $incident = incident::create([
            'utilisateur_id'=>$validation['utilisateur_id'],
            'type_incident'=>$validation['type_incident'],
            'description_incident'=>$validation['description_incident'],
            'priorite'=>$validation['priorite'],
            'statut_incident'=>$validation['statut_incident'],
            'quartier'=>$validation['quartier'],
            'secteur'=>$validation['secteur'],
            'ville'=>$validation['ville'],
        ]);

        if($request->hasFile('preuve'))
        {
            foreach($request->file('preuve') as $fichier)
            {
                $lien_preuve = $fichier->store('preuve_incident','public');

                preuve::create([
                    'incident_id'=>$incident->id,
                    'nom_preuve'=>$fichier->getClientOriginalName(),
                    'type_preuve'=>$fichier->getClientMimeType(),
                    'lien_preuve'=>$lien_preuve,
                    'description_preuve'=>$request->description_preuve,
                    'statut_preuve'=>'valide',
                ]);
            }
        }
        
    
    }

    public function listeIncident( REquest $request)
    {
       $incidents = incident::query();
       
       if($request->has('utilisateur_id'))
        {
            $incidents->where('utilisateur_id',$request->utilisateur_id);
        }

        if($request->has('statut_incident'))
        {
            $incidents->where('statut_incident',$request->statut_incident);
        }
        
        if($request->has('secteur'))
        {
            $incidents->where('secteur',$request->secteur);
        }

        if($request->has('ville'))
        {
            $incidents->where('ville',$request->ville);
        }

        if($request->has('quartier'))
        {
            $incidents->where('quartier',$request->quartier);
        }
        
        return response()->json($incidents->get(),200);
    }

    public function supprimerIncident($id)
    {
        $incident = incident::find($id);
        if(!$incident)
        {
            return response()->json(['message'=>'incident non trouve'],404);
        }

        $incident->delete();
        return response()->json(['message'=>'incident supprimer avec succes'],200);
    }

}
