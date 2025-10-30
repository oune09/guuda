<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function creeIncident(Request $request)
    {
        $regles = [
            'utilisateur_id'=>'required|integer|exists:utilisateurs,id',
            'type_incident'=>'required|string',
            'description_incident'=>'required|string',
            'priorite'=>'requiredè|string|enum:faible,moyenne,elevee',
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
            'quatier'=>$validation['quartier'],
            'secteur'=>$validation['secteur'],
            'ville'=>$validation['ville'],
        ]);

        if($request->hasFile('preuve'))
        {
            foreach($request->file('peuve') as $fichier)
            {
                $lien_preuve = $fichier->store('preuve_incident','public');

                preuve::create([
                    'incident_id'=>$incident->id,
                    'nom_preuve'=>$fichier->getClientOriginalName(),
                    'type_preuve'=>$fichier->getClientMineType(),
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

        if($request->has('ville'));
        {
            $incidents->where('ville',$resquet->ville);
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

    public function detailIncident($id)
    {
        $incident = incident::find($id);
        if(!$incident)
        {
            return response()->json(['message'=>'incident non trouve'],404);
        }
        

        
    }
}
