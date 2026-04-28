<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
   protected $fillable = [
        'type_rapport',
        'chemin_fichier',
        'format',
        'date_generation',
        'user_id',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

}
