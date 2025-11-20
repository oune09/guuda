<?php

namespace App\Http\Controllers;
use App\Models\incident;

use Illuminate\Http\Request;

class autoriteController extends Controller
{
     public function __construct()
    {
        //$this->middleware('auth:sanctum');
        //$this->middleware('autorite'); // Assure que seul une autorité peut accéder
    }

    public function listeIncident(Request $request)
    {
        $autorite = $request->user()->autorite;
        $incident = Incident::where('unite_id', $autorite->unite_id)
                          ->where('organisation_id', $autorite->organisation_id)
                          ->with('citoyen', 'admin')
                          ->get();

        return response()->json($incident,200);
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
