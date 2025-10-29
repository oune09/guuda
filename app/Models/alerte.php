<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class alerte extends Model
{
    protected $fillable =[
        'autorite-id',
        'incident_id',
        'message_alerte',
        'statut_alerte',
        'date_alerte',
        'date_fin',
        'ville',
        'secteur',
        'quartier',
        'niveau_alerte',
    ];
}
