<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
     protected $fillable = [
        'code_salle',
        'nom_salle',
        'capacite',
        'actif',
        'bloc_id',
    ];

    public function bloc()
    {
        return $this->belongsTo(Bloc::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }
}
