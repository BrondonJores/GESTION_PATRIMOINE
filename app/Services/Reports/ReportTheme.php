<?php

namespace App\Services\Reports;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ReportTheme
{
    private const SETTING_KEY = 'reports.identity';

    private const DEFAULT_IDENTITY = [
        'brand_name' => 'Gestion du patrimoine',
        'entity_name' => 'Établissement public',
        'service_name' => 'Support, administration et reporting',
        'classification_label' => 'INTERNE - DIFFUSION LIMITÉE',
        'document_nature' => 'Rapport administratif',
        'table_title' => 'Données consolidées',
        'footer_label' => 'Document généré automatiquement - ne pas diffuser hors service autorisé.',
        'header_image_path' => null,
        'footer_image_path' => null,
    ];

    public function pageWidth(): int
    {
        return 595;
    }

    public function pageHeight(): int
    {
        return 838;
    }

    public function margin(): int
    {
        return 48;
    }

    public function rowHeight(): int
    {
        return 28;
    }

    public function headerHeight(): int
    {
        return 392;
    }

    public function footerHeight(): int
    {
        return 52;
    }

    public function backgroundColor(): string
    {
        return '1 1 1';
    }

    public function primaryColor(): string
    {
        return '0 0 0';
    }

    public function accentColor(): string
    {
        return '1 1 1';
    }

    public function borderColor(): string
    {
        return '0 0 0';
    }

    public function mutedColor(): string
    {
        return '0 0 0';
    }

    public function alternateRowColor(): string
    {
        return '1 1 1';
    }

    public function brandName(): string
    {
        return $this->identity()['brand_name'];
    }

    public function entityName(): string
    {
        return $this->identity()['entity_name'];
    }

    public function classificationLabel(): string
    {
        return $this->identity()['classification_label'];
    }

    public function footerLabel(): string
    {
        return $this->identity()['footer_label'];
    }

    public function serviceName(): string
    {
        return $this->identity()['service_name'];
    }

    public function documentNature(): string
    {
        return $this->identity()['document_nature'];
    }

    public function tableTitle(): string
    {
        return $this->identity()['table_title'];
    }

    /**
     * @return array<string, string>
     */
    public function identity(): array
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return self::DEFAULT_IDENTITY;
            }

            $identity = AppSetting::query()
                ->where('key', self::SETTING_KEY)
                ->value('value');
        } catch (QueryException) {
            return self::DEFAULT_IDENTITY;
        }

        if (! is_array($identity)) {
            return self::DEFAULT_IDENTITY;
        }

        return [
            'brand_name' => $this->normalizeText($identity['brand_name'] ?? null, self::DEFAULT_IDENTITY['brand_name']),
            'entity_name' => $this->normalizeOptionalText($identity['entity_name'] ?? null),
            'service_name' => $this->normalizeOptionalText($identity['service_name'] ?? null),
            'classification_label' => $this->normalizeOptionalText($identity['classification_label'] ?? null),
            'document_nature' => $this->normalizeOptionalText($identity['document_nature'] ?? null),
            'table_title' => $this->normalizeOptionalText($identity['table_title'] ?? null),
            'footer_label' => $this->normalizeOptionalText($identity['footer_label'] ?? null),
            'header_image_path' => $this->normalizeImagePath($identity['header_image_path'] ?? ($identity['logo_path'] ?? null)),
            'footer_image_path' => $this->normalizeImagePath($identity['footer_image_path'] ?? null),
        ];
    }

    /**
     * @param array<string, string|null> $identity
     */
    public function saveIdentity(array $identity): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            ['value' => [
                'brand_name' => $this->normalizeText($identity['brand_name'] ?? null, self::DEFAULT_IDENTITY['brand_name']),
                'entity_name' => $this->normalizeOptionalText($identity['entity_name'] ?? null),
                'service_name' => $this->normalizeOptionalText($identity['service_name'] ?? null),
                'classification_label' => $this->normalizeOptionalText($identity['classification_label'] ?? null),
                'document_nature' => $this->normalizeOptionalText($identity['document_nature'] ?? null),
                'table_title' => $this->normalizeOptionalText($identity['table_title'] ?? null),
                'footer_label' => $this->normalizeOptionalText($identity['footer_label'] ?? null),
                'header_image_path' => $this->normalizeImagePath($identity['header_image_path'] ?? null),
                'footer_image_path' => $this->normalizeImagePath($identity['footer_image_path'] ?? null),
            ]],
        );
    }

    /**
     * @return array{content: string, width: int, height: int}|null
     */
    public function headerImage(): ?array
    {
        return $this->imageFromPath($this->identity()['header_image_path'] ?? null);
    }

    /**
     * @return array{content: string, width: int, height: int}|null
     */
    public function footerImage(): ?array
    {
        return $this->imageFromPath($this->identity()['footer_image_path'] ?? null);
    }

    /**
     * @return array{content: string, width: int, height: int}|null
     */
    private function imageFromPath(mixed $path): ?array
    {

        if (! is_string($path) || $path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);
        $size = @getimagesize($absolutePath);

        if ($size === false || ($size[2] ?? null) !== IMAGETYPE_JPEG) {
            return null;
        }

        $content = file_get_contents($absolutePath);

        if ($content === false) {
            return null;
        }

        return [
            'content' => $content,
            'width' => (int) $size[0],
            'height' => (int) $size[1],
        ];
    }

    private function normalizeText(mixed $value, string $fallback): string
    {
        if (! is_string($value)) {
            return $fallback;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return $value === '' ? $fallback : mb_substr($value, 0, 180);
    }

    private function normalizeOptionalText(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return mb_substr($value, 0, 180);
    }

    private function normalizeImagePath(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
