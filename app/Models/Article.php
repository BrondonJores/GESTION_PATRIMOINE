<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'numero_reference',
        'code_ancien',
        'designation',
        'statut', 
        'observations',
        'categorie_id',
    ];

        // Constantes statut — évite les strings magiques partout dans le code
    const DISPONIBLE    = 'Disponible';
    const AFFECTE       = 'Affecté';
    const MAINTENANCE   = 'En_maintenance';
    const REFORME       = 'Réformé';

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    

    
    // ── Helpers ────────────────────────────────────────────────────

    public function estDisponible(): bool
    {
        return $this->statut === self::DISPONIBLE;
    }

    public function estAffecte(): bool
    {
        return $this->statut === self::AFFECTE;
    }

    public function estEnMaintenance(): bool
    {
        return $this->statut === self::MAINTENANCE;
    }

    public function estReforme(): bool
    {
        return $this->statut === self::REFORME;
    }

    // Un article réformé est définitivement hors service
    public function estArchive(): bool
    {
        return $this->statut === self::REFORME;
    }
}
