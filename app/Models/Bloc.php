<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloc extends Model
{
    protected $fillable = [
        'code_bloc',
        'nom_bloc',
        'description',
        'actif',
    ];

   
    public function salles()
    {
        return $this->hasMany(Salle::class);
    }
}
