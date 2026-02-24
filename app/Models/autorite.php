<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class autorite extends Model
{
   protected $fillable = [
    'utilisateur_id',
    'organisation_id',
    'unite_id',
    'matricule',
    'statut',
   ];
public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function unite()
    {
        return $this->belongsTo(Unite::class);
    }

   public function affectations()
{
    return $this->hasMany(Affectation::class);
}

public function affectationActive()
{
    return $this->hasOne(Affectation::class)->where('statut', true);
}

   
}
