<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapport extends Model
{
    protected $fillable = [
        'type_rapport',
        'chemin_fichier',
        'format',
        'date_generation',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_generation' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
