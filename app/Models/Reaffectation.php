<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reaffectation extends Model
{
    protected $fillable = [
        'quantite',
        'observations',
        'date_reaffectation',
        'affectation_id',
    ];

     public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }

}
