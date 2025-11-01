<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class alerteController extends Controller
{
    public function creerAlerte(Resquest $request)
    {
        $regles = [
            'autorité_id'=>'required|integer|exists:autorites,id',
            'incident_id'=>'nullable|integer|exists:incidents,id',
            'message_alerte'=>'required|string',
            'niveau_alerte'=>'required|string|enum:info,avertissement,urgence',
            'staut_alerte'=>'required|string|enum:active,terminee',
            'date_alerte'=>'required|date',
            'date_fin'=>'nullable|date',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',
            'longititude'=>'required|string',
            'latidude'=>'required|string',
        ]; 

        $validation = $request->validate($regles);

        $alerte = alerte::create([
            'autorite_id'=>$validation['autorite_id'],
            'incident_id'=>$validation['incident_id'],
            'message_alerte'=>$validation['message_alerte'],
            'niveau_alerte'=>$validation['niveau_alerte'],
            'statut_alerte'=>$validation['statut_alerte'],
            'date_alerte'=>$validation['date_alerte'],
            'date_fin'=>$validation['date_fin'],
            'ville'=>$validatio['ville'],
            'secteur'=>$validation['secteur'],
            'quartier'=>$validation['quartier'],
            'logitude'=>$validation['longitude'],
            'latitude'=>$validation['latitude'],
        ]);
    }

    public function modifierAlerte(Request $request, $id)
    {
        $alerte = alerte::find($id);

        if(!$alerte)
        {
            return response->json(['message'=>'alerte introuvable'],404);
        }

        $regles = [
            'incident_id'=>'nullable|integer|exists:incidents,id',
            'message_alerte'=>'required|string',
            'niveau_alerte'=>'required|string|enum:info,avertissement,urgence',
            'staut_alerte'=>'required|string|enum:active,terminee',
            'date_alerte'=>'required|date',
            'date_fin'=>'nullable|date',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',
            'longititude'=>'required|string',
            'latidude'=>'required|string',
        ]; 

        $validation = $request->validate($regles);

        $alerte->update([
            'incident_id'=>'nullable|integer|exists:incidents,id',
            'message_alerte'=>'required|string',
            'niveau_alerte'=>'required|string|enum:info,avertissement,urgence',
            'staut_alerte'=>'required|string|enum:active,terminee',
            'date_alerte'=>'required|date',
            'date_fin'=>'nullable|date',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',
            'longititude'=>'required|string',
            'latidude'=>'required|string',
        ]);
    }

    public function listeAlerte(Request $request)
    {
        $alertes = alerte::query();

        if($request->has('autorite_id'))
        {
            $alertes->where('autorite_id',$request->autorite_id);
        }

        if($request->has('statut_alerte'))
        {
            $alertes->where('staut_alerte',$request->statut_alerte);
        }

        if($request->has('niveau_alerte'))
        {
            $alertes->where('niveau_alerte',$request->niveau_alerte);
        }

        if($request->has('ville'))
        {
            $alertes->where('ville',$request->ville);
        }

        if($request->has('secteur'))
        {
            $alertes->where('secteur',$request->secteur);
        }

        if($request->has('quartier'))
        {
            $alertes->where('quartier',$request->quartier);
        }
    }

    public function supprimerAlerte(Request $request, $id)
    {
        $alerte = alerte::find($id);

        if(!$alerte)
        {
            return response()->json(['message'=>'alerte non trouve'],404);
        }

        $alerte->delete();
        return response()->json(['message'=>'alerte supprimer'],200);
    }
}
