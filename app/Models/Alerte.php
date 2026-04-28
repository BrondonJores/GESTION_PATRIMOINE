<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    protected $fillable = [
        'statut',
        'canal',
        'retour',
        'date_alerte',
        'date_traitement',
        'article_id',
    ];

     public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
