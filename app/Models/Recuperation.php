<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recuperation extends Model
{
      protected $fillable = [
        'quantite',
        'observations',
        'date_recuperation',
        'affectation_id',
    ];
    
      public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }
}
