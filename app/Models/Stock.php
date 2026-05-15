<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['article_id', 'statut', 'quantite'];
    protected $casts    = ['quantite' => 'integer'];

    const DISPONIBLE  = 'Disponible';
    const AFFECTE     = 'Affecté';
    const MAINTENANCE = 'En_maintenance';
    const REFORME     = 'Réformé';

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Lire la quantité d'un statut donné pour un article.
     * Retourne 0 si la ligne n'existe pas.
     */
    public static function quantitePour(int $articleId, string $statut): int
    {
        return (int) static::where('article_id', $articleId)
                           ->where('statut', $statut)
                           ->value('quantite');
    }

    /**
     * Déplacer une quantité d'un statut vers un autre.
     *
     *(crée la ligne si absente avec quantite=0)
     * puis update séparément — jamais de INSERT si la ligne existe déjà.
     */
    public static function deplacer(
        int    $articleId,
        string $statutSource,
        string $statutDest,
        int    $quantite
    ): void {
        if ($quantite <= 0) {
            throw new \Exception("La quantité doit être supérieure à zéro.");
        }

        //  Source 
        $ligneSource = static::firstOrCreate(
            ['article_id' => $articleId, 'statut' => $statutSource],
            ['quantite'   => 0]
        );

        if ($ligneSource->quantite < $quantite) {
            throw new \Exception(
                "Stock '{$statutSource}' insuffisant. " .
                "Disponible : {$ligneSource->quantite}, demandé : {$quantite}."
            );
        }

        // UPDATE direct sur la ligne existante — pas de doublon possible
        $ligneSource->update(['quantite' => $ligneSource->quantite - $quantite]);

        // Destination
        $ligneDest = static::firstOrCreate(
            ['article_id' => $articleId, 'statut' => $statutDest],
            ['quantite'   => 0]
        );

        $ligneDest->update(['quantite' => $ligneDest->quantite + $quantite]);
    }
}