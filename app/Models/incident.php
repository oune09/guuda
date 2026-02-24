<?php

namespace App\Models;

use App\Models\secteur;
use App\Models\preuve;


use Illuminate\Database\Eloquent\Model;

class incident extends Model
{
    protected $fillable = [
       'utilisateur_id',
        'organisation_id',
        'autorite_id',
        'unite_id',
        'date_incident',
        'date_charge',
        'date_resolution',
        'statut_incident',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'date_incident' => 'datetime',
        'date_charge' => 'datetime',
        'date_resolution' => 'datetime',
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function autoriteAssignee()
    {
        return $this->belongsTo(Autorite::class, 'autorite_assignee_id');
    }

    public function preuves()
    {
        return $this->hasMany(Preuve::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    // Méthodes utilitaires
    public function estOuvert()
    {
        return $this->statut_incident === 'ouvert';
    }

    public function estEnCours()
    {
        return $this->statut_incident === 'en_cours';
    }

    public function estResolu()
    {
        return $this->statut_incident === 'resolu';
    }


}
