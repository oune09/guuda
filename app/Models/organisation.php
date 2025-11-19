<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class organisation extends Model
{
   protected $fillable =[
    'nom',
   ];

   public function superAdmin()
   {
    return $this->hasone(SuperAdmin::class);
   }

   public function autorite()
   {
    return $this->hasMany(Autorite::class);
   }

   public function admin()
   {
    return $this->hasMany(Admin::class);
   }
   
}
