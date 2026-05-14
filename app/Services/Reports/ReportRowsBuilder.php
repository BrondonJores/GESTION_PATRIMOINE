<?php

namespace App\Services\Reports;

use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Reaffectation;
use App\Models\Recuperation;
use App\Models\User;
use App\Support\Alertes\StockAlertType;
use Illuminate\Database\Eloquent\Builder;

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
                    'Quantité' => $article->quantite,
                    'Seuil minimum' => $article->quantite_min,
                    'Statut' => $article->statut,
                    'État' => $article->etat,
                    'Catégorie' => $article->categorie?->nom_categorie,
                ])
                ->all(),
            'Affectations' => Affectation::query()
                ->with(['article', 'salle'])
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'created_at', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Affectation $affectation): array => [
                    'Article' => $affectation->article?->designation,
                    'Salle' => $affectation->salle?->nom_salle,
                    'Quantité' => $affectation->quantite,
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
                ->with('article')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_alerte', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Alerte $alerte): array => [
                    'Article' => $alerte->article?->designation,
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
     */
    private function filtrerPeriode(Builder $query, string $colonne, array $data): Builder
    {
        return $query->whereBetween($colonne, [
            $data['periode_debut'],
            $data['periode_fin'],
        ]);
    }
}
