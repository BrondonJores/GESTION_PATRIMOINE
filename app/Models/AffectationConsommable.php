<?php
// app/Models/AffectationConsommable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationConsommable extends Model
{
    protected $fillable = [
        'consommable_id',
        'bloc_id',
        'salle_id',
        'quantite',
        'date_affectation',
        'observations',
        'user_id',
    ];

    protected $casts = [
        'quantite'         => 'integer',
        'date_affectation' => 'date',
    ];

    public function consommable(): BelongsTo
    {
        return $this->belongsTo(Consommable::class);
    }

    public function bloc(): BelongsTo
    {
        return $this->belongsTo(Bloc::class);
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}