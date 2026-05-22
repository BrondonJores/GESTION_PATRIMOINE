<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaffectation extends Model
{
    protected $fillable = [
        'quantite',
        'observations',
        'date_reaffectation',
        'affectation_id',
        'salle_id',
    ];

    protected $casts = [
        'quantite'           => 'integer',
        'date_reaffectation' => 'date',
    ];

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class);
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }
}