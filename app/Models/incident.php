<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incident extends Model
{
    protected $fillable = [
        'utilisateur_id',
        'type_incident',
        'description_incident',
        'priorite',
        'statut_incident',
        'quatier',
        'secteur',
        'ville',
    ];

    protected $casts = [
        'date_incident' => 'datetime',
        'longitide' => 'decimal:10',
        'latitude' => 'decimal:10',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(utilisateur::class);
    }

    public function preuves()
    {
        return $this->hasMany(preuve::class);
    }
     
    public function scopeSecteur($query,$secteur)
    {
        return $query->where('secteur',$secteur);
    }

    public function scopeVille($query,$ville)
    {
        return $query->where('ville',$ville);
    }

    public function scopeStatut($query,$statut)
    {
        return $query->where('statut_incident',$statut);
    }

    public function scopePriorite($query,$priorite)
    {
        return $query->where('priorite',$priorite);
    }


}
