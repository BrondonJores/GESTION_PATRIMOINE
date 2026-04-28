<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Famille extends Model
{
   protected $fillable = [
        'code_famille',
        'nom_famille',
        'description',
    ];


    public function categories()
    {
        return $this->hasMany(Categorie::class);
    }
}
