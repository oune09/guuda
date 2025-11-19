<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class admin extends Model
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
   
   public function secteurs() 
   {
    return $this->belongsToMany(Secteur::class, 'admin_secteur');
   }

   public function alert()
   {
      return $this->hasMany(alert::class);
   }

   public function scopeStatut()
   {
      return $this->where('statut',true);
   }

}
