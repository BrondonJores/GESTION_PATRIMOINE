<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    
    protected $fillable = [
        'nom_categorie',
        'description',
        'code_categorie',
        'famille_id',
    ];

    
    public function famille()
    {
        return $this->belongsTo(Famille::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
