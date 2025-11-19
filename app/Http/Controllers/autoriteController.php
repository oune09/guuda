<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class autoriteController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('autorite'); // Assure que seul une autorité peut accéder
    }

    public function listeIncident(Request $request,$incident)
    {
        $autorite = $request->user()->autorite;
        $incident = Incident::where('unite_id', $autorite->unite_id)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->with('citoyen', 'admin')
                          ->get();

        return response()->json($alertes);
    }

    public function traiteIncident(Request $request,$incident)
    {
        $autorite = $request->user()->autorite;
        $incident=Incident::where('unite_id', $autorite->unite_id)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->with('citoyen', 'admin')
                          ->firstOrFail();

        $incident->update(['statut_incident'=>'terminee']);
    }
}
