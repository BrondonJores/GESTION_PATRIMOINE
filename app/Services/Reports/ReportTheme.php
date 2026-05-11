<?php

namespace App\Services\Reports;

class ReportTheme
{
    public function pageWidth(): int
    {
        return 842;
    }

    public function pageHeight(): int
    {
        return 595;
    }

    public function margin(): int
    {
        return 32;
    }

    public function rowHeight(): int
    {
        return 22;
    }

    public function headerHeight(): int
    {
        return 292;
    }

    public function footerHeight(): int
    {
        return 34;
    }

    public function backgroundColor(): string
    {
        return '1 1 1';
    }

    public function primaryColor(): string
    {
        return '0.05 0.05 0.05';
    }

    public function accentColor(): string
    {
        return '0.93 0.93 0.93';
    }

    public function borderColor(): string
    {
        return '0.70 0.70 0.70';
    }

    public function mutedColor(): string
    {
        return '0.35 0.35 0.35';
    }

    public function alternateRowColor(): string
    {
        return '0.97 0.97 0.97';
    }

    public function brandName(): string
    {
        return 'Gestion du patrimoine';
    }

    public function classificationLabel(): string
    {
        return 'INTERNE - DIFFUSION LIMITÉE';
    }

    public function footerLabel(): string
    {
        return 'Document généré automatiquement - ne pas diffuser hors service autorisé.';
    }

    public function serviceName(): string
    {
        return 'Support, administration et reporting';
    }

    public function documentNature(): string
    {
        return 'Rapport administratif';
    }

    public function tableTitle(): string
    {
        return 'Données consolidées';
    }
}
