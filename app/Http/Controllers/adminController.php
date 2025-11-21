<?php

namespace App\Http\Controllers;
use App\Notifications;
use App\Models\autorite;
use APp\Models\preuve;
use App\Models\incident;
use App\Models\alerte;
use App\Models\utilisateur;
use App\Notifications\alerteNotification;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use function Pest\Laravel\get;

class adminController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth:sanctum');
       // $this->middleware('admin'); 
    }

    public function listeAutorite(Request $request)
    {
        $admin = $request->user()->admin; 
        $autorites = Autorite::where('unite_id', $admin->unite_id)
                              ->where('organisation_id', $admin->organisation_id)
                              ->with('utilisateur')
                              ->get();

        return response()->json($autorites);
    }

    public function confirmerSignalement(Request $request,$id)
    {
        $admin = $request->user()->admin;
        $incident = Incident::findOrFail($id);

        if($incident->unite_id !== $admin->unite_id || $incident->organisation_id !== $admin->organisation_id)
        {
            return response()->json(['message'=>'incident hors de votre zone']);
        }

        $incident->update([
            'statut_incident'=>'en_cour',
            'confirmed_by_admin_id' => $admin->id,
        ]);
          $autorites = Autorite::where('unite_id', $admin->unite_id)
                              ->where('organisation_id', $admin->organisation_id)
                              ->with('utilisateur')
                              ->get();

        foreach ($autorites as $autorite) {
            $autorite->utilisateur->notify(new \App\Notifications\IncidenttNotification($incident));
        }

        return response()->json(['message' => 'Signalement confirmé et autorité notifiée']);
    }
     public function listeIncident(Request $request)
    {
        
        $admin = $request->user()->admin; 
        $incidents = Incident::where('unite_id', $admin->unite_id)
                              ->where('organisation_id', $admin->organisation_id)
                              ->with('utilisateur')
                              ->get();

        return response()->json($incidents);
    }
    public function creerAlerte(Request $request)
    {
        $regles = [
            'autorite_id'=>'required|integer|exists:autorites,id',
            'incident_id'=>'nullable|integer|exists:incidents,id',
            'message_alerte'=>'required|string',
            'niveau_alerte'=>'required|string|enum:info,avertissement,urgence',
            'statut_alerte'=>'required|string|enum:active,terminee',
            'date_alerte'=>'required|date',
            'date_fin'=>'nullable|date',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',
            'longitude'=>'required|string',
            'latitude'=>'required|string',
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
            'ville'=>$validation['ville'],
            'secteur'=>$validation['secteur'],
            'quartier'=>$validation['quartier'],
            'longitude'=>$validation['longitude'],
            'latitude'=>$validation['latitude'],
        ]);
         if($request->hasFile('preuve'))
        {
            foreach($request->file('preuve') as $fichier)
            {
                $lien_preuve = $fichier->store('preuve_alerte','public');

                preuve::create([
                    'incident_id'=>$alerte->id,
                    'nom_preuve'=>$fichier->getClientOriginalName(),
                    'type_preuve'=>$fichier->getClientMimeType(),
                    'lien_preuve'=>$lien_preuve,
                    'description_preuve'=>$request->description_alerte,
                    'statut_preuve'=>'valide',
                ]);
            }
        }

       $utilisateur = utilisateur::where('role_utilisateur','citoyen')
                             ->where('secteur_id',$alerte->secteur_id)
                             ->wherehas('unite.secteur',function($query) use ($alerte){
                                $query->where('secteur.id',$alerte->secteur_id);
                             })
                             ->get();

        $notificationsEnvoyees = 0;
        foreach($utilisateur as $utilisateur){
            try {
                $utilisateur->notify(new alerteNotification($alerte));
                $notificationsEnvoyees++;
                Log::info("Notification in-app envoyée à l'utilisateur {$utilisateur->id} pour l'alerte {$alerte->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour {$utilisateur->email}: " . $e->getMessage());
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Alerte créée avec succès',
            'data' => $alerte->load('autorite', 'incident'),
            'statistiques' => [
                'citoyens_notifies' => $notificationsEnvoyees,
                'zone' => "{$alerte->ville} - {$alerte->secteur} - {$alerte->quartier}"
            ]
        ], 201);
    }

     public function modifierAlerte(Request $request, $id)
    {
        $alerte = alerte::find($id);

        if(!$alerte)
        {
            return response()->json(['message'=>'alerte introuvable'],404);
        }

        $regles = [
            'incident_id'=>'nullable|integer|exists:incidents,id',
            'message_alerte'=>'required|string',
            'niveau_alerte'=>'required|string|enum:info,avertissement,urgence',
            'statut_alerte'=>'required|string|enum:active,terminee',
            'date_alerte'=>'required|date',
            'date_fin'=>'nullable|date',
            'ville'=>'required|string',
            'secteur'=>'required|string',
            'quartier'=>'required|string',
            'longitude'=>'required|string',
            'latitude'=>'required|string',
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
            'longitude'=>'required|string',
            'latitude'=>'required|string',
        ]);
         $utilisateur = utilisateur::where('role_utilisateur','citoyen')
                             ->where('ville',$alerte->ville)
                             ->where('secteur',$alerte->secteur)
                             ->where('quartier',$alerte->quartier)
                             ->get();
        
                              $notificationsEnvoyees = 0;
        foreach($utilisateur as $utilisateur){
            try {
                $utilisateur->notify(new alerteNotification($alerte));
                $notificationsEnvoyees++;
                Log::info("Notification in-app envoyée à l'utilisateur {$utilisateur->id} pour l'alerte {$alerte->id}");
            } catch (\Exception $e) {
                Log::error("Erreur notification pour {$utilisateur->email}: " . $e->getMessage());
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Alerte créée avec succès',
            'data' => $alerte->load('autorite', 'incident'),
            'statistiques' => [
                'citoyens_notifies' => $notificationsEnvoyees,
                'zone' => "{$alerte->ville} - {$alerte->secteur} - {$alerte->quartier}"
            ]
        ], 201);
    }

    public function rechercheAlerte(Request $request)
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

        if($request->has('ville_id'))
        {
            $alertes->where('ville_id',$request->ville_id);
        }

        if($request->has('secteur_id'))
        {
            $alertes->where('secteur_id',$request->secteur_id);
        }

        return response()->json($alertes);
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

    public function listeAlerte(Request $request)
    {
        $alertes = alerte::all();
        return response()->json($alertes);
    }

    public function alerteDetail(Request $request,$id)
    {
        $alerte = alerte::find($id);

        if(!$alerte)
        {
            return response()->json(['message'=>'alerte non trouve'],404);
        }

        return response()->json($alerte);
    }

    public function adminAlerte(Request $request,$id)
    {
        $admin = $request->user()->admin;
        $alerte = alerte::where('unite_id',$admin->unite_id)
                         ->where('organisation_id',$admin->organisation_id)
                         ->get();
        
        return response()->json($alerte);
    }

    

}
