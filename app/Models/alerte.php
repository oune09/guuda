<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class alerte extends Model
{
    protected $fillable =[
        'admin_id',
        'unite_id',
        'titre_alerte', 
        'message_alerte',
        'statut_alerte',
        'date_alerte',
        'date_fin',
        'niveau_alerte',
        'longitude',
        'latitude',
        'rayon_km',
    ];


    protected $casts = [
        'date_alerte' => 'datetime',
        'date_fin' => 'datetime',
    ];

    // Relations
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function unite()
    {
        return $this->belongsTo(Unite::class);
    }

    public function preuves()
    {
        return $this->hasMany(Preuve::class);
    }

    // Méthodes utilitaires
    public function estActive()
    {
        return $this->statut_alerte === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('statut_alerte', 'active');
    }
}
