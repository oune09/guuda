<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class unite extends Model
{protected $fillable = [
        'nom_unite',
        'organisation_id',
        'adresse',
        'longitude',
        'latitude',
        'telephone_unite',
        'mail_unite',
        'statut',
    ];

    protected $casts = [
        'longitude' => 'decimal:8',
        'latitude' => 'decimal:8',
    ];

    // Relations
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function autorites()
    {
        return $this->hasMany(Autorite::class);
    }


    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    
    public function responsable()
    {
        return $this->admin ? $this->admin->utilisateur : null;
    }

    public function nombreAgents()
    {
        return $this->autorites()->where('statut', true)->count();
    }

    public function capaciteDisponible()
    {
        return $this->capacite_max - $this->nombreAgents();
    }

    public function estPleine()
    {
        return $this->capacite_max && $this->nombreAgents() >= $this->capacite_max;
    }

    
    public function scopeProchesDe($query, $latitude, $longitude, $rayonKm = 10)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?",
            [$longitude, $latitude, $rayonKm * 1000]
        );
    }
}
