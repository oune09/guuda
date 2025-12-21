<?php

namespace App\Models;

use App\Models\secteur;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'utilisateur_id',
        'organisation_id',
        'unite_id',
        'matricule',
    ];

    protected $casts = [
        'statut' => 'boolean',
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function admin()
    {
    return $this->belongsTo(Admin::class, 'admin_id');
    }


    public function unite()
    {
        return $this->belongsTo(Unite::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function incident()
    {
        return $this->hasMany(Incident::class);
    }

    public function alert()
    {
        return $this->hasMany(Alerte::class);
    }

    // Scope
    public function scopeStatut($query)
    {
        return $query->where('statut', true);
    }
}



