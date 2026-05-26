<?php

namespace App\Services\Reports;

use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Consommable;
use App\Models\Notification;
use App\Models\Reaffectation;
use App\Models\Recuperation;
use App\Models\User;
use App\Support\Alertes\StockAlertType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportRowsBuilder
{
    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    public function build(array $data): array
    {
        return match ($data['type_rapport']) {
            'Inventaire des articles' => Article::query()
                ->with('categorie')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'created_at', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Article $article): array => [
                    'Référence' => $article->numero_reference,
                    'Désignation' => $article->designation,
                    'Statut' => $article->statut,
                    'Catégorie' => $article->categorie?->nom_categorie,
                ])
                ->all(),
            'Inventaire des consommables' => Consommable::query()
                ->with('categorie')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'created_at', $data))
                ->orderBy('designation')
                ->limit(5000)
                ->get()
                ->map(fn (Consommable $consommable): array => [
                    'Désignation' => $consommable->designation,
                    'Catégorie' => $consommable->categorie?->nom_categorie,
                    'Stock actuel' => $consommable->quantite_stock,
                    'Seuil minimal' => $consommable->quantite_min,
                    'Statut' => $consommable->statut,
                ])
                ->all(),
            'Rapport par bloc' => $this->rapportParBloc($data),
            'Rapport par salle' => $this->rapportParSalle($data),
            'Affectations' => Affectation::query()
                ->with(['article', 'bloc', 'salle'])
                ->where('type', 'article')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_affectation', $data))
                ->orderByDesc('date_affectation')
                ->limit(5000)
                ->get()
                ->map(fn (Affectation $affectation): array => [
                    'Référence' => $affectation->article?->numero_reference,
                    'Article' => $affectation->article?->designation,
                    'Bloc' => $affectation->bloc?->nom_bloc,
                    'Salle' => $affectation->salle?->nom_salle,
                    'Quantité' => $affectation->quantite,
                    'Statut affectation' => $affectation->date_recuperation === null ? 'Active' : 'Récupérée',
                    'Date affectation' => $affectation->date_affectation,
                    'Date de récupération' => $affectation->date_recuperation,
                ])
                ->all(),
            'Réaffectations' => Reaffectation::query()
                ->with('affectation.article')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_reaffectation', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Reaffectation $reaffectation): array => [
                    'Article' => $reaffectation->affectation?->article?->designation,
                    'Quantité' => $reaffectation->quantite,
                    'Date de réaffectation' => $reaffectation->date_reaffectation,
                ])
                ->all(),
            'Récupérations' => Recuperation::query()
                ->with('affectation.article')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_recuperation', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Recuperation $recuperation): array => [
                    'Article' => $recuperation->affectation?->article?->designation,
                    'Quantité' => $recuperation->quantite,
                    'Date de récupération' => $recuperation->date_recuperation,
                ])
                ->all(),
            'Alertes' => Alerte::query()
                ->with('consommable')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_alerte', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Alerte $alerte): array => [
                    'Consommable' => $alerte->consommable?->designation,
                    "Type d'alerte" => StockAlertType::label($alerte->type_alerte),
                    'Statut' => $alerte->statut,
                    'Canal' => $alerte->canal,
                    'Date alerte' => $alerte->date_alerte,
                    'Date traitement' => $alerte->date_traitement,
                ])
                ->all(),
            'Notifications' => Notification::query()
                ->with('user')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_envoi', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Notification $notification): array => [
                    'Utilisateur' => $notification->user?->name,
                    'Canal' => $notification->canal,
                    'Lu' => $notification->lu ? 'Oui' : 'Non',
                    'Date envoi' => $notification->date_envoi,
                ])
                ->all(),
            'Utilisateurs' => User::query()
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'created_at', $data))
                ->limit(5000)
                ->get()
                ->map(fn (User $user): array => [
                    'Identifiant' => $user->id,
                    'Nom' => $user->name,
                    'Créé le' => $user->created_at,
                ])
                ->all(),
            'Logs' => AuditLog::query()
                ->with('user')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_action', $data))
                ->limit(5000)
                ->get()
                ->map(fn (AuditLog $log): array => [
                    'Module' => $log->module,
                    'Action' => $log->action,
                    'Utilisateur' => $log->user_id ? "Utilisateur #{$log->user_id}" : 'Système',
                    'Date action' => $log->date_action,
                ])
                ->all(),
            default => [],
        };
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function rapportParBloc(array $data): array
    {
        return Affectation::query()
            ->join('articles', 'articles.id', '=', 'affectations.article_id')
            ->join('categories', 'categories.id', '=', 'articles.categorie_id')
            ->leftJoin('salles', 'salles.id', '=', 'affectations.salle_id')
            ->join('blocs', function ($join): void {
                $join->on('blocs.id', '=', 'affectations.bloc_id')
                    ->orOn('blocs.id', '=', 'salles.bloc_id');
            })
            ->where('affectations.type', 'article')
            ->whereNull('affectations.date_recuperation')
            ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'affectations.created_at', $data))
            ->groupBy('blocs.nom_bloc', 'categories.nom_categorie', 'articles.designation', 'articles.statut')
            ->orderBy('blocs.nom_bloc')
            ->orderBy('categories.nom_categorie')
            ->orderBy('articles.designation')
            ->limit(5000)
            ->get([
                'blocs.nom_bloc as bloc',
                'categories.nom_categorie as categorie',
                'articles.designation as designation',
                'articles.statut as statut',
                DB::raw('COUNT(DISTINCT articles.id) as total_articles'),
            ])
            ->map(fn (object $row): array => [
                'Bloc' => $row->bloc,
                'Catégorie' => $row->categorie,
                'Détail' => $row->designation,
                'Statut' => $row->statut,
                'Total articles' => (int) $row->total_articles,
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function rapportParSalle(array $data): array
    {
        return Affectation::query()
            ->join('articles', 'articles.id', '=', 'affectations.article_id')
            ->join('categories', 'categories.id', '=', 'articles.categorie_id')
            ->join('salles', 'salles.id', '=', 'affectations.salle_id')
            ->join('blocs', 'blocs.id', '=', 'salles.bloc_id')
            ->where('affectations.type', 'article')
            ->whereNull('affectations.date_recuperation')
            ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'affectations.created_at', $data))
            ->groupBy('blocs.nom_bloc', 'salles.nom_salle', 'categories.nom_categorie', 'articles.designation', 'articles.statut')
            ->orderBy('blocs.nom_bloc')
            ->orderBy('salles.nom_salle')
            ->orderBy('categories.nom_categorie')
            ->orderBy('articles.designation')
            ->limit(5000)
            ->get([
                'blocs.nom_bloc as bloc',
                'salles.nom_salle as salle',
                'categories.nom_categorie as categorie',
                'articles.designation as designation',
                'articles.statut as statut',
                DB::raw('COUNT(DISTINCT articles.id) as total_articles'),
            ])
            ->map(fn (object $row): array => [
                'Bloc' => $row->bloc,
                'Salle' => $row->salle,
                'Catégorie' => $row->categorie,
                'Détail' => $row->designation,
                'Statut' => $row->statut,
                'Total articles' => (int) $row->total_articles,
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function filtrerPeriode(Builder $query, string $colonne, array $data): Builder
    {
        return $query->whereBetween($colonne, [
            $data['periode_debut'],
            $data['periode_fin'],
        ]);
    }
}
