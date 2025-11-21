<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Preuve;
use App\Models\admin;
use App\Models\Autorite;
use App\Models\utilisateur;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class IncidentController extends Controller
{
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
        $utilisateur = utilisateur::where('role_utilisateur','administrateur')
                             ->where('organisation_id',$incident->organnisation__id)
                             ->where('unite_id',$incident->unite__id)
                             ->wherehas('unite.secteur',function($query) use ($incident){
                                $query->where('secteur.id',$incident->secteur_id);
                             })
                             ->get();

        $notificationsEnvoyees = 0;
        foreach($utilisateur as $utilisateur){
            try {
                $utilisateur->notify(new Notification($incident));
                $notificationsEnvoyees++;
                Log::info("Notification in-app envoyée à l'utilisateur {$utilisateur->id} pour l'alerte {$incident->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour {$utilisateur->email}: " . $e->getMessage());
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Alerte créée avec succès',
            'data' => $incident->load('autorite', 'incident'),
            'statistiques' => [
                'citoyens_notifies' => $notificationsEnvoyees,
                'zone' => "{$incident->ville_id} - {$incident->secteur} - {$incident->quartier}"
            ]
        ], 201);
        
    
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
    public function adminIncident(Request $request,$id)
    {
        $admin = $request->user($id);
        $incident = incident::where('unite_id',$admin->unite_id)
                         ->where('organisation_id',$admin->organisation_id)
                         ->get();
        
        return response()->json($incident);
    }




}
