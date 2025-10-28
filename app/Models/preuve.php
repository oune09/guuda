<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class preuve extends Model
{
    protected $fillable = [
        'incident_id',
        'nom_incident',
        'type_preuve',
        'lien_preuve',
        'description_preuve',
        'statut_preuve',
    ];

    protected $casts = [
        ''
    ];

    public function  incident()
    {
        return $this->belongsTo(incident::class);
    }

    public function scopeStatut($query,$statut)
    {
        return $query->where('statut_preuve',$satut);
    }
    
    public function scopeType($query,$type)
    {
        return $query->where('type_preuve',$type);
    }

    

}
