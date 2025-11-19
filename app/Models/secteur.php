<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class secteur extends Model
{
    protected $fillable = [
        'ville_id',
        'nom_secteur',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

   public function admins() 
   {
    return $this->belongsToMany(Admin::class, 'admin_secteur');
   }

    public function autorites() 
    {
    return $this->belongsToMany(Autorite::class, 'autorite_secteur');
    }

    public function incident()
    {
        return $this->hasMany(Incident::class);
    }

    public function alerte()
    {
        return $this->hasMany(alerte::class);
    }

     public function estUtiliseParAdmin($adminId)
    {
        return $this->admins()->where('admin_id', $adminId)->exists();
    }

    public function peutEtreSupprime()
    {
        return $this->autorites()->count() === 0 && $this->signalements()->count() === 0;
    }
}


