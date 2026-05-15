<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'numero_reference',
        'code_ancien',
        'designation',
        'quantite_totale',
        'quantite_min',
        'observations',
        'is_archived',
        'categorie_id',
    ];

      protected $casts = [
        'quantite_totale' => 'integer',
        'quantite_min' => 'integer',
    ];
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
      public function stocks()
    {
        return $this->hasMany(Stock::class);
    }


    
    public function getDisponibleAttribute(): int
    {
        return Stock::quantitePour($this->id, Stock::DISPONIBLE);
    }

    public function getAffecteAttribute(): int
    {
        return Stock::quantitePour($this->id, Stock::AFFECTE);
    }

    public function getEnMaintenanceAttribute(): int
    {
        return Stock::quantitePour($this->id, Stock::MAINTENANCE);
    }

    public function getReformeAttribute(): int
    {
        return Stock::quantitePour($this->id, Stock::REFORME);
    }

    public function getStatutGlobalAttribute(): string
    {
        return $this->is_archived ? 'Archivé' : 'Actif';
    }

    public function peutEtreArchive(): bool
    {
        return $this->disponible     === 0
            && $this->affecte        === 0
            && $this->en_maintenance === 0;
    }
}
