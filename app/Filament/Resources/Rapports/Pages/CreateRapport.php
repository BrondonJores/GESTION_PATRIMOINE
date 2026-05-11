<?php

namespace App\Filament\Resources\Rapports\Pages;

use App\Filament\Resources\Rapports\RapportResource;
use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Reaffectation;
use App\Models\Recuperation;
use App\Models\User;
use App\Services\RapportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;

class CreateRapport extends CreateRecord
{
    protected static string $resource = RapportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['date_generation'] = now();
        $data['chemin_fichier'] = null;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(RapportService::class);
        $lignes = $this->lignesDuRapport($data);
        $periode = [
            'debut' => $data['periode_debut'],
            'fin' => $data['periode_fin'],
        ];

        return $data['format'] === 'Excel'
            ? $service->exportExcel($data['type_rapport'], $lignes, auth()->user(), $periode)
            : $service->exportPdf($data['type_rapport'], $lignes, auth()->user(), $periode);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function lignesDuRapport(array $data): array
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
                    'Observations' => $affectation->observations,
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
                    'Observations' => $reaffectation->observations,
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
                    'Observations' => $recuperation->observations,
                ])
                ->all(),
            'Alertes' => Alerte::query()
                ->with('article')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'date_alerte', $data))
                ->limit(5000)
                ->get()
                ->map(fn (Alerte $alerte): array => [
                    'Article' => $alerte->article?->designation,
                    'Statut' => $alerte->statut,
                    'Canal' => $alerte->canal,
                    'Date alerte' => $alerte->date_alerte,
                    'Date traitement' => $alerte->date_traitement,
                    'Retour' => $alerte->retour,
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
                    'Contenu' => $notification->contenu,
                ])
                ->all(),
            'Utilisateurs' => User::query()
                ->with('roles')
                ->tap(fn (Builder $query): Builder => $this->filtrerPeriode($query, 'created_at', $data))
                ->limit(5000)
                ->get()
                ->map(fn (User $user): array => [
                    'Nom' => $user->name,
                    'E-mail' => $user->email,
                    'Rôles' => $user->roles->pluck('name')->implode(', '),
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
                    'Utilisateur' => $log->user?->name,
                    'Adresse IP' => $log->adresse_ip,
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
