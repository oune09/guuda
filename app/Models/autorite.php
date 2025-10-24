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

   public function scopeStatut()
   {
      return $this->where('statut',true);
   }

   public function scopeZone($query,$zone)
   {
      return $query->where('zone_responsabilite',$zone);
   }
   
}
