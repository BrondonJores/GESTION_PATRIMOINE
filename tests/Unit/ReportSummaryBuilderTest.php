<?php

namespace Tests\Unit;

use App\Services\Reports\ReportSummaryBuilder;
use PHPUnit\Framework\TestCase;

class ReportSummaryBuilderTest extends TestCase
{
    public function test_inventory_summary_shows_reformed_articles(): void
    {
        $rows = array_merge(
            array_fill(0, 567, ['Statut' => 'Affecté']),
            array_fill(0, 179, ['Statut' => 'Réformé']),
        );

        $summary = (new ReportSummaryBuilder())->build('Inventaire des articles', $rows);

        $this->assertSame([
            'Articles' => 746,
            'Affectés' => 567,
            'Disponibles' => 0,
            'Réformés' => 179,
        ], $summary);
    }

    public function test_affectations_summary_counts_active_and_recovered_rows(): void
    {
        $summary = (new ReportSummaryBuilder())->build('Affectations', [
            ['Référence' => 'A-001', 'Statut affectation' => 'Active'],
            ['Référence' => 'A-002', 'Statut affectation' => 'Active'],
            ['Référence' => 'A-001', 'Statut affectation' => 'Récupérée'],
        ]);

        $this->assertSame([
            'Affectations' => 3,
            'Actives' => 2,
            'Récupérées' => 1,
            'Articles distincts' => 2,
        ], $summary);
    }

    public function test_consumables_inventory_summary_counts_stock_statuses(): void
    {
        $summary = (new ReportSummaryBuilder())->build('Inventaire des consommables', [
            ['Statut' => 'Disponible'],
            ['Statut' => 'Disponible'],
            ['Statut' => 'Sous seuil'],
            ['Statut' => 'Épuisé'],
        ]);

        $this->assertSame([
            'Consommables' => 4,
            'Disponibles' => 2,
            'Sous seuil' => 1,
            'Épuisés' => 1,
        ], $summary);
    }
}
