<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class affectation extends Model
{
    protected $fillable = [
        'unite_id',
        'autorite_id',
        'statut',
    ];

    public function autorite()
{
    return $this->belongsTo(Autorite::class);
}

public function incidents()
{
    return $this->hasMany(Incident::class);
}

public function unite()
{
    return $this->belongsTo(Unite::class);
}

}