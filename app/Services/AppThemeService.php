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
        'success' => '#22c55e',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
        'info' => '#3b82f6',
        'dark_mode' => 'enabled',
        'dark_mode_forced' => 'disabled',
        'sidebar_width' => '20rem',
        'collapsed_sidebar_width' => '4.5rem',
    ];

    private const SIDEBAR_WIDTHS = ['16rem', '18rem', '20rem', '22rem', '24rem'];

    private const COLLAPSED_SIDEBAR_WIDTHS = ['4rem', '4.5rem', '5rem'];

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
            'success' => $this->normalizeHexColor($theme['success'] ?? null, self::DEFAULT_THEME['success']),
            'warning' => $this->normalizeHexColor($theme['warning'] ?? null, self::DEFAULT_THEME['warning']),
            'danger' => $this->normalizeHexColor($theme['danger'] ?? null, self::DEFAULT_THEME['danger']),
            'info' => $this->normalizeHexColor($theme['info'] ?? null, self::DEFAULT_THEME['info']),
            'dark_mode' => $this->normalizeChoice($theme['dark_mode'] ?? null, ['enabled', 'disabled'], self::DEFAULT_THEME['dark_mode']),
            'dark_mode_forced' => $this->normalizeChoice($theme['dark_mode_forced'] ?? null, ['enabled', 'disabled'], self::DEFAULT_THEME['dark_mode_forced']),
            'sidebar_width' => $this->normalizeChoice($theme['sidebar_width'] ?? null, self::SIDEBAR_WIDTHS, self::DEFAULT_THEME['sidebar_width']),
            'collapsed_sidebar_width' => $this->normalizeChoice($theme['collapsed_sidebar_width'] ?? null, self::COLLAPSED_SIDEBAR_WIDTHS, self::DEFAULT_THEME['collapsed_sidebar_width']),
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
                    'success' => $this->normalizeHexColor($theme['success'] ?? null, self::DEFAULT_THEME['success']),
                    'warning' => $this->normalizeHexColor($theme['warning'] ?? null, self::DEFAULT_THEME['warning']),
                    'danger' => $this->normalizeHexColor($theme['danger'] ?? null, self::DEFAULT_THEME['danger']),
                    'info' => $this->normalizeHexColor($theme['info'] ?? null, self::DEFAULT_THEME['info']),
                    'dark_mode' => $this->normalizeChoice($theme['dark_mode'] ?? null, ['enabled', 'disabled'], self::DEFAULT_THEME['dark_mode']),
                    'dark_mode_forced' => $this->normalizeChoice($theme['dark_mode_forced'] ?? null, ['enabled', 'disabled'], self::DEFAULT_THEME['dark_mode_forced']),
                    'sidebar_width' => $this->normalizeChoice($theme['sidebar_width'] ?? null, self::SIDEBAR_WIDTHS, self::DEFAULT_THEME['sidebar_width']),
                    'collapsed_sidebar_width' => $this->normalizeChoice($theme['collapsed_sidebar_width'] ?? null, self::COLLAPSED_SIDEBAR_WIDTHS, self::DEFAULT_THEME['collapsed_sidebar_width']),
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
            'success' => Color::generatePalette($theme['success']),
            'warning' => Color::generatePalette($theme['warning']),
            'danger' => Color::generatePalette($theme['danger']),
            'info' => Color::generatePalette($theme['info']),
        ];
    }

    public function hasDarkMode(): bool
    {
        return $this->getTheme()['dark_mode'] === 'enabled';
    }

    public function hasForcedDarkMode(): bool
    {
        $theme = $this->getTheme();

        return $theme['dark_mode'] === 'enabled' && $theme['dark_mode_forced'] === 'enabled';
    }

    public function getSidebarWidth(): string
    {
        return $this->getTheme()['sidebar_width'];
    }

    public function getCollapsedSidebarWidth(): string
    {
        return $this->getTheme()['collapsed_sidebar_width'];
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

    /**
     * @param array<int, string> $allowed
     */
    private function normalizeChoice(mixed $value, array $allowed, string $fallback): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : $fallback;
    }
}
