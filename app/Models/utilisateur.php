<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\user as  Authenticatable;

class utilisateur extends Model
{
    protected  $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'email_utilisateur',
        'mot_de_passe',
        'cnib',
        'date_naissance_utilisateur',
        'telephone_utilisateur',
        'photo',
        'role_utilisateur',
        'ville',
        'secteur',
        'quatier',

    ];

    protected $hidden = [
        'mot_de_passe_utilisateur',
        'remenber_token',
        
    ];

    protected $casts = [
        'email_verified_at' =>'datetime',
        'date_naissance_utilisateur' => 'date',

    ];


    public function autorite()
    {
        return this->hasOne(Autorite::class);
    }

    public function incident()
    {
    return $this->hasMany(Incident::class);
    }
     
    public function scopeCitoyens($query)
    {
        return $query->where('role_utilisateur','citoyen');
    }

    public function scopeAurotites($query)
     {
        return $query->where('role_utilisateur','autorite');
     }

     public function scopeAdministrateures($query)
     {
        return $query->where('role_utilisateur','administrateur');
     }
    

     public function citoyen()
     {
        return $this->role === 'citoyen';
     }

     public function aurorite()
     {
        return $this->role === 'autorite';
     }

     public function administrateur()
     {
        return $this->role ==='administrateur';
     }

}
