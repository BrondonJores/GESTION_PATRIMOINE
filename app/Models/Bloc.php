<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bloc extends Model
{
    protected $fillable = [
        'code_bloc',
        'nom_bloc',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function salles(): HasMany
    {
        return $this->hasMany(Salle::class);
    }
}