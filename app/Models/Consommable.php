<?php
// app/Models/Consommable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consommable extends Model
{
    protected $fillable = [
        'designation',
        'categorie_id',
        'quantite_stock',
        'quantite_min',
        'statut',
        'observations',
    ];

    protected $casts = [
        'quantite_stock'   => 'integer',
        'quantite_min'     => 'integer',
        'valeur_unitaire'  => 'decimal:2',
        'date_acquisition' => 'date',
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(AffectationConsommable::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function calculerStatut(): string
    {
        if ($this->quantite_stock <= 0) return 'Épuisé';

        if (!is_null($this->quantite_min)
            && $this->quantite_stock <= $this->quantite_min) {
            return 'Sous seuil';
        }

        return 'Disponible';
    }
}
