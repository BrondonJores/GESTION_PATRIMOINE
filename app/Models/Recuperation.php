<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recuperation extends Model
{
    protected $fillable = [
        'quantite',
        'observations',
        'date_recuperation',
        'affectation_id',
    ];

    protected $casts = [
        'quantite'          => 'integer',
        'date_recuperation' => 'date',
    ];

    public function affectation(): BelongsTo
    {
        return $this->belongsTo(Affectation::class);
    }
}