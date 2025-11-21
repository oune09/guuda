<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class unite extends Model
{
    protected $fillable =[
        'nom',
        'organisation_id',
        'ville_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(organisation::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function secteur()
    {
        return $this->belongsToMany(secteur::class, 'unite_secteur','unite_id','secteur_id');
    }

    public function autorite()
    {
        return $this->hasMAny(Autorite::class);
    }

     public function couvreSecteur(int $secteurId): bool
    {
        return $this->secteurs()->where('secteurs.id', $secteurId)->exists();
    }

    
    public function scopeForOrganisationAndSecteur($query, $organisationId, $secteurId)
    {
        return $query->where('organisation_id', $organisationId)
                     ->whereHas('secteurs', fn($q) => $q->where('secteurs.id', $secteurId));
    }
}
