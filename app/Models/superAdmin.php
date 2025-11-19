<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class superAdmin extends Model
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

   public function secteur()
   {
      return $this->hasMany(Secteur::class);
   }
   
   public function ville()
   {
     return $this->hasMany(Ville::class);
   }
   public function organisation()
   {
     return $this->hasMany(Organisation::class);
   }
   
   public function scopeStatut()
   {
      return $this->where('statut',true);
   }

}
