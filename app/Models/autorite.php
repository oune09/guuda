<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class autorite extends Model
{
   protected $fillable = [
    'utilisateur_id ',
    'organisation',
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

    public function incidentsAssignes()
    {
        return $this->hasMany(Incident::class, 'autorite_id');
    }
   
}
