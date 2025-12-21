<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;



class Utilisateur extends Authenticatable
{ 
    use HasApiTokens, HasFactory;

    protected $table = 'utilisateurs'; // Spécifier le nom de la table

    protected $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'email_utilisateur',
        'mot_de_passe_utilisateur',
        'cnib',
        'date_naissance_utilisateur',
        'telephone_utilisateur',
        'photo',
        'role_utilisateur',
        
    ];

     protected $hidden = [
         'mot_de_passe_utilisateur',
         'remember_token',
    ];


    protected $casts = [
         'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Relations
    public function autorite()
    {
        return $this->hasOne(Autorite::class);
    }

    public function incident()
    {
        return $this->hasMany(Incident::class);
    }
    
    

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function superAdmin()
    {
        return $this->hasOne(SuperAdmin::class);
    }

    public function incidentsSignales()
    {
        return $this->hasMany(Incident::class, 'citoyen_id');
    }

    // Méthodes helpers
    public function estCitoyen()
    {
        return $this->role_utilisateur === 'citoyen';
    }

    public function estAutorite()
    {
        return $this->role_utilisateur === 'autorite';
    }

    public function estAdministrateur()
    {
        return $this->role_utilisateur === 'administrateur';
    }

    public function estSuperAdministrateur()
    {
        return $this->role_utilisateur === 'superadministrateur';
    }

    protected function motDePasseUtilisateur(): Attribute
{
    return Attribute::make(
        set: fn ($value) => Hash::make($value)
    );
}


}