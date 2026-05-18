<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerte extends Model
{
    protected $fillable = [
        'statut',
        'canal',
        'type_alerte',
        'retour',
        'note_resolution',
        'date_alerte',
        'date_traitement',
        'article_id',
    ];

    protected function casts(): array
    {
        return [
            'date_alerte' => 'datetime',
            'date_traitement' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
