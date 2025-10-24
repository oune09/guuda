<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class autorite extends Model
{
   protected $fillable = [
    'utilisateur_id ',
    'organisation',
    'matricule',
    'zone_responsabilite',
    'statut',
   ];

   protected $casts = [
    'statut' => 'boolean',
   ];

   public function incident()
   {
      return $this->hasMany(incident::class);
   }

   public function alert()
   {
      return $this->hasMany(alert::class);
   }
}
