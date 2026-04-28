<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
 protected $fillable = [
        'quantite',
        'observations',
        'date_recuperation',
        'article_id',
        'salle_id',
    ];

     public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function reaffectations()
    {
        return $this->hasMany(Reaffectation::class);
    }

    public function recuperations()
    {
        return $this->hasMany(Recuperation::class);
    }
}
