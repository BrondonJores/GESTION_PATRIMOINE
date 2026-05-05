<?php

namespace App\Services;

use App\Models\AppSetting;
use Filament\Support\Colors\Color;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class AppThemeService
{
    private const SETTING_KEY = 'appearance.theme';

    private const DEFAULT_THEME = [
        'primary' => '#f59e0b',
        'gray' => '#71717a',
    ];

    /**
     * @return array<string, string>
     */
    public function getTheme(): array
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return self::DEFAULT_THEME;
            }

            $theme = AppSetting::query()
                ->where('key', self::SETTING_KEY)
                ->value('value');
        } catch (QueryException) {
            return self::DEFAULT_THEME;
        }

        if (! is_array($theme)) {
            return self::DEFAULT_THEME;
        }

        return [
            'primary' => $this->normalizeHexColor($theme['primary'] ?? null, self::DEFAULT_THEME['primary']),
            'gray' => $this->normalizeHexColor($theme['gray'] ?? null, self::DEFAULT_THEME['gray']),
        ];
    }

    /**
     * @param array<string, string|null> $theme
     */
    public function saveTheme(array $theme): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => [
                    'primary' => $this->normalizeHexColor($theme['primary'] ?? null, self::DEFAULT_THEME['primary']),
                    'gray' => $this->normalizeHexColor($theme['gray'] ?? null, self::DEFAULT_THEME['gray']),
                ],
            ],
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getFilamentColors(): array
    {
        $theme = $this->getTheme();

        return [
            'primary' => Color::generatePalette($theme['primary']),
            'gray' => Color::generatePalette($theme['gray']),
        ];
    }

    private function normalizeHexColor(?string $color, string $fallback): string
    {
        if (! is_string($color)) {
            return $fallback;
        }

        $color = trim($color);

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $fallback;
        }

        return strtolower($color);
    }
}
