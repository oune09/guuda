<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdmin extends Model
{
    protected $table = 'super_admins'; // Spécifier le nom de la table

    protected $fillable = [
        'utilisateur_id',
        'matricule',
        'statut',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    
    
   

    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'superAdmin_id');
    }
    
    public function scopeActif($query)
    {
        return $query->where('statut', true);
    }
}