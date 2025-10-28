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
}
