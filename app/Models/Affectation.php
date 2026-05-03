<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affectation extends Model
{
    protected $fillable = [
        'quantite',
        'observations',
        'date_recuperation',
        'date_affectation',
        'article_id',
        'bloc_id',
        'salle_id',
        'user_id',
    ];

    protected $casts = [
        'quantite'          => 'integer',
        'date_recuperation' => 'date',
        'date_affectation'  => 'date',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
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

    public function reaffectations(): HasMany
    {
        return $this->hasMany(Reaffectation::class);
    }

    public function recuperations(): HasMany
    {
        return $this->hasMany(Recuperation::class);
    }

    public function getEstActiveAttribute(): bool
    {
        return is_null($this->date_recuperation);
    }
}