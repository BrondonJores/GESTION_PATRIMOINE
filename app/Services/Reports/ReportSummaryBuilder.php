<?php

namespace App\Services\Reports;

class ReportSummaryBuilder
{
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, string|int>
     */
    public function build(string $typeRapport, array $rows): array
    {
        return match ($typeRapport) {
            'Inventaire des articles' => [
                'Articles' => count($rows),
                'Disponibles' => $this->countWhere($rows, 'Statut', 'Disponible'),
                'Affectés' => $this->countWhere($rows, 'Statut', 'Affecté'),
            ],
            'Rapport par bloc', 'Rapport par salle' => [
                'Articles' => $this->sum($rows, 'Total articles'),
                'Catégories' => collect($rows)->pluck('Catégorie')->filter()->unique()->count(),
                'Groupes' => count($rows),
            ],
            'Alertes' => [
                'Alertes' => count($rows),
                'Résolues' => $this->countWhere($rows, 'Statut', 'Résolu'),
                'À traiter' => count($rows) - $this->countWhere($rows, 'Statut', 'Résolu'),
            ],
            'Notifications' => [
                'Notifications' => count($rows),
                'Lues' => $this->countWhere($rows, 'Lu', 'Oui'),
                'Non lues' => $this->countWhere($rows, 'Lu', 'Non'),
            ],
            'Logs' => [
                'Actions' => count($rows),
                'Modules' => collect($rows)->pluck('Module')->filter()->unique()->count(),
                'Période' => 'Filtrée',
            ],
            default => [
                'Lignes' => count($rows),
                'Colonnes' => count($rows[0] ?? []),
                'Période' => 'Filtrée',
            ],
        };
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function sum(array $rows, string $column): int|float
    {
        return collect($rows)
            ->sum(fn (array $row): int|float => is_numeric($row[$column] ?? null) ? (float) $row[$column] : 0);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function countWhere(array $rows, string $column, string $value): int
    {
        return collect($rows)
            ->filter(fn (array $row): bool => ($row[$column] ?? null) === $value)
            ->count();
    }

}
