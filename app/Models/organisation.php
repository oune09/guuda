<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
 protected $fillable = [
        'nom_organisation',
        'description',
        'telephone',
        'mail_organisation',
        'adresse_siege',
        'logo',
    ];

    // Relations
    public function unites()
    {
        return $this->hasMany(Unite::class);
    }

    public function autorites()
    {
        return $this->hasMany(Autorite::class);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    // Méthodes utilitaires
    
}
