<?php

namespace App\Http\Controllers;

use App\Model\secteur as ModelSecteur;
use App\Models\autorite;
use App\Models\secteur;

use Illuminate\Http\Request;

class autoriteSecteurController extends Controller
{
    public function edition($id)
    {
        $autorite = Autorite::FindOrFail($id);
        $secteur = Secteur::all();
        return view('autorite.edit', compact('autorite','secteur'));
    }

    public function modifier(Request $request,$id)
    {
        $autorite = Autorite::FindOrFail($id);
        $autorite->secteur()->sync($request->secteur);
        return redirect()->route('autorites.show', $id)->with('success', 'Secteurs mis à jour avec succès.');
    }
}
