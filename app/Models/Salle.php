<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salle extends Model
{
    protected $fillable = [
        'code_salle',
        'nom_salle',
        'actif',
        'bloc_id',
    ];

    protected $casts = [
        'actif'    => 'boolean',
        
    ];

    public function bloc(): BelongsTo
    {
        return $this->belongsTo(Bloc::class);
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class);
    }
}