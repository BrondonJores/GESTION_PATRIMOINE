<?php

namespace App\Support\Alertes;

class StockAlertType
{
    public const SEUIL_PROCHE = 'seuil_minimal_proche';
    public const STOCK_MINIMAL = 'stock_minimal_atteint';
    public const STOCK_EPUISE = 'stock_epuise';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::SEUIL_PROCHE => 'Seuil minimal proche',
            self::STOCK_MINIMAL => 'Stock minimal atteint',
            self::STOCK_EPUISE => 'Stock épuisé',
        ];
    }

    public static function label(?string $type): string
    {
        return self::labels()[$type] ?? 'Alerte de stock';
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::labels());
    }
}
