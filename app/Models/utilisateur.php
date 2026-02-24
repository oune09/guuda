<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;



class Utilisateur extends Authenticatable
{ 
    use HasApiTokens, HasFactory,HasRoles;

    protected $table = 'utilisateurs'; // Spécifier le nom de la table

    protected $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'email_utilisateur',
        'mot_de_passe_utilisateur',
        'telephone_utilisateur',
        'is_active',
        'verified_at',
        'verification_channel',
        'photo',
        
    ];

     protected $hidden = [
         'mot_de_passe_utilisateur',
         'remember_token',
    ];


    protected $casts = [
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
         'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $guard_name = 'sanctum';

    // Relations
    public function autorite()
    {
        return $this->hasOne(Autorite::class);
    }

    public function incident()
    {
        return $this->hasMany(Incident::class);
    }
    

    public function incidentsSignales()
    {
        return $this->hasMany(Incident::class, 'citoyen_id');
    }

    

}