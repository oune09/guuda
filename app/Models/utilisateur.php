<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\user as  Authenticatable;

class utilisateur extends Model
{
    protected  $fillable = [
        'nom_utilisateur',
        'prenom_utilisateur',
        'email_utilisateru',
        'cnib',
        'date_naissance_utilisateru',
        'telephone_utilisateur',
        'photo',
        'role_utilisateur',
        'vile',
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

}
