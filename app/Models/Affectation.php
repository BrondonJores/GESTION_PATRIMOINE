<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    protected $fillable = [
        'type',
        'article_id',
        'consommable_id',
         'bloc_id',  
        'salle_id',
        'quantite',
        'date_affectation',
        'date_recuperation',
        'observations',
        'user_id',
    ];

    protected $casts = [
        'quantite'          => 'integer',
        'date_affectation'  => 'date',
        'date_recuperation' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function consommable(): BelongsTo
    {
        return $this->belongsTo(Consommable::class);
    }

    

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }
    public function bloc(): BelongsTo
{
    return $this->belongsTo(Bloc::class);
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function estPourArticle(): bool
    {
        return $this->type === 'article';
    }

    public function estPourConsommable(): bool
    {
        return $this->type === 'consommable';
    }

    public function estActive(): bool
    {
        return is_null($this->date_recuperation);
    }

    // Label affiché dans la liste
    public function getLabelAttribute(): string
    {
        return $this->estPourArticle()
            ? ($this->article?->designation ?? '—')
            : ($this->consommable?->designation ?? '—');
    }

    public function getReferenceAttribute(): string
    {
        return $this->estPourArticle()
            ? ($this->article?->numero_reference ?? '—')
            : ($this->consommable?->reference ?? '—');
    }
}