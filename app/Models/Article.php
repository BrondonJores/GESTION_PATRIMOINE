<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'numero_reference',
        'code_ancien',
        'designation',
        'quantite',
        'statut',
        'quantite_min',
        'etat',
        'observations',
        'categorie_id',
    ];

      protected $casts = [
        'quantite'     => 'integer',
        'quantite_min' => 'integer',
    ];
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}
