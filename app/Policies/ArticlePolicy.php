<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class ArticlePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // L'admin peut TOUT faire sur les articles
        if ($user->hasRole('admin')) {
            return true;
        }

        return null; // continuer vers la méthode spécifique
    }
    public function viewAny(User $user): bool
    {
        return $user->can('view articles');
    }

    public function view(User $user, Article $article): bool
    {
        return $user->can('view articles');
    }

    public function create(User $user): bool
    {
        return $user->can('create articles');
    }

    public function update(User $user, Article $article): bool
    {
         // Règle métier : article réformé = intouchable
        if ($article->statut === 'Réformé') {
            return false;
        }
        return $user->can('update articles');
    }

    public function delete(User $user, Article $article): bool
    {
       // Un article déjà réformé ne peut pas être supprimé (déjà hors service)
        if ($article->statut === 'Réformé') {
            return false;
        }
        return $user->can('delete articles');
    }

      public function exporter(User $user): bool
    {
        return $user->can('view articles')
            && ($user->hasRole('admin') || $user->hasRole('gestionnaire'));
    }
}
