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

   protected $casts = [
    'statut' => 'boolean',
   ];

   public function incident()
   {
      return $this->hasMany(incident::class);
   }
   
   public function autorites()
   {
    return $this->belongsToMany(Autorite::class, 'autorite_secteur');
   }

   public function alert()
   {
      return $this->hasMany(alerte::class);
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
