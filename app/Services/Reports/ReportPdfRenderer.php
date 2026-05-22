<?php

namespace App\Services\Reports;

use App\Models\User;
use Illuminate\Support\Carbon;

class ReportPdfRenderer
{
    public function __construct(
        private readonly ReportTheme $theme,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array{debut?: mixed, fin?: mixed} $periode
     * @param array<string, string|int|float> $summary
     */
    public function render(string $title, array $rows, ?User $user = null, array $periode = [], array $summary = []): string
    {
        $columns = $this->columns($rows);
        $rows = $rows === [] ? [['Information' => 'Aucune donnée pour la période sélectionnée.']] : $rows;
        $rowsPerPage = $this->rowsPerPage();
        $chunks = array_chunk($rows, $rowsPerPage);
        $chunks = $chunks === [] ? [[]] : $chunks;
        $headerImage = $this->theme->headerImage();
        $footerImage = $this->theme->footerImage();

        $streams = [];

        foreach ($chunks as $index => $chunk) {
            $streams[] = $this->renderPage(
                title: $title,
                columns: $columns ?: ['Information'],
                rows: $chunk,
                page: $index + 1,
                totalPages: count($chunks),
                user: $user,
                periode: $periode,
                summary: $summary,
                hasHeaderImage: $headerImage !== null,
                hasFooterImage: $footerImage !== null,
            );
        }

        return $this->buildPdf($streams, [
            'header' => $headerImage,
            'footer' => $footerImage,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, string>
     */
    private function columns(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        return array_keys($rows[0]);
    }

    private function rowsPerPage(): int
    {
        $availableHeight = $this->theme->pageHeight()
            - $this->theme->headerHeight()
            - $this->theme->footerHeight()
            - $this->theme->margin();

        return (int) floor($availableHeight / $this->theme->rowHeight());
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, array<string, mixed>> $rows
     * @param array{debut?: mixed, fin?: mixed} $periode
     * @param array<string, string|int|float> $summary
     */
    private function renderPage(string $title, array $columns, array $rows, int $page, int $totalPages, ?User $user, array $periode, array $summary, bool $hasHeaderImage, bool $hasFooterImage): string
    {
        $commands = [];
        $commands[] = $this->theme->backgroundColor() . ' rg 0 0 ' . $this->theme->pageWidth() . ' ' . $this->theme->pageHeight() . ' re f';

        if ($hasHeaderImage) {
            $commands[] = 'q 499 0 0 88 48 742 cm /ImHeader Do Q';
        } else {
            $headerTextX = 48;
            $headerTextY = 792;

            $commands[] = $this->textIfNotBlank($this->theme->brandName(), $headerTextX, $headerTextY, 12, 'F2');
            $commands[] = $this->textIfNotBlank($this->theme->entityName(), $headerTextX, $headerTextY - 14, 9, 'F2');
            $commands[] = $this->textIfNotBlank($this->theme->serviceName(), $headerTextX, $headerTextY - 28, 8);
            $commands[] = $this->textIfNotBlank($this->theme->classificationLabel(), 374, 792, 9, 'F2');
            $commands[] = $this->textIfNotBlank($this->theme->documentNature(), 374, 774, 9);
        }

        if (! $hasHeaderImage) {
            $commands[] = $this->theme->primaryColor() . ' RG 48 750 499 1 re S';
        }

        $commands[] = $this->textIfNotBlank(strtoupper($this->theme->documentNature()), 218, 714, 13, 'F2');
        $commands[] = $this->text($this->truncate(strtoupper($title), 499, 11), 48, 682, 11, 'F2');

        if ($page === 1) {
            $commands = array_merge($commands, $this->renderAdministrativeNotice($title, $user, $periode, $summary));
        }

        $commands[] = $this->textIfNotBlank($this->theme->tableTitle(), 48, 408, 10, 'F2');

        $tableTop = 386;
        $tableLeft = $this->theme->margin();
        $tableWidth = $this->theme->pageWidth() - ($this->theme->margin() * 2);
        $columnWidth = $tableWidth / max(count($columns), 1);

        $commands[] = $this->theme->primaryColor() . ' rg ' . $tableLeft . ' ' . ($tableTop - $this->theme->rowHeight()) . ' ' . $tableWidth . ' ' . $this->theme->rowHeight() . ' re f';

        foreach ($columns as $index => $column) {
            $x = $tableLeft + ($index * $columnWidth);
            $commands[] = '1 1 1 RG ' . $x . ' ' . ($tableTop - $this->theme->rowHeight()) . ' ' . $columnWidth . ' ' . $this->theme->rowHeight() . ' re S';
            $commands[] = $this->text($this->truncate($column, $columnWidth, 8), $x + 5, $tableTop - 15, 8, 'F2', white: true);
        }

        $y = $tableTop - ($this->theme->rowHeight() * 2);

        foreach ($rows as $rowIndex => $row) {
            $fill = $rowIndex % 2 === 0 ? '1 1 1' : $this->theme->alternateRowColor();
            $commands[] = "{$fill} rg {$tableLeft} {$y} {$tableWidth} " . $this->theme->rowHeight() . ' re f';

            foreach ($columns as $index => $column) {
                $x = $tableLeft + ($index * $columnWidth);
                $value = $this->formatValue($row[$column] ?? '');
                $commands[] = $this->theme->borderColor() . ' RG ' . $x . ' ' . $y . ' ' . $columnWidth . ' ' . $this->theme->rowHeight() . ' re S';
                $commands[] = $this->text($this->truncate($value, $columnWidth, 8), $x + 5, $y + 8, 8);
            }

            $y -= $this->theme->rowHeight();
        }

        if ($hasFooterImage) {
            $commands[] = 'q 499 0 0 46 48 10 cm /ImFooter Do Q';
            $commands[] = $this->text("Page {$page}/{$totalPages}", 482, 20, 8);
        } else {
            $commands[] = $this->theme->mutedColor() . ' rg ' . $this->theme->margin() . ' 24 ' . $tableWidth . ' 1 re f';
            $commands[] = $this->text("Page {$page}/{$totalPages}", 482, 20, 9);
            $commands[] = $this->text($this->theme->footerLabel(), 48, 20, 8);
        }

        return implode("\n", $commands);
    }

    /**
     * @param array<string, string|int|float> $summary
     * @return array<int, string>
     */
    private function renderAdministrativeNotice(string $title, ?User $user, array $periode, array $summary): array
    {
        $commands = [];
        $reference = 'RAP-' . now()->format('Ymd-His');

        $commands[] = $this->theme->accentColor() . ' rg 48 576 499 64 re f';
        $commands[] = $this->theme->borderColor() . ' RG 48 576 499 64 re S';
        $commands[] = $this->theme->borderColor() . ' RG 48 608 499 1 re S';
        $commands[] = $this->theme->borderColor() . ' RG 297 576 1 64 re S';

        $commands[] = $this->text('Référence', 58, 620, 8, 'F2');
        $commands[] = $this->text($reference, 134, 620, 8);
        $commands[] = $this->text('Objet', 58, 600, 8, 'F2');
        $commands[] = $this->text($this->truncate($title, 150, 8), 134, 600, 8);

        $commands[] = $this->text('Période', 308, 620, 8, 'F2');
        $commands[] = $this->text($this->truncate($this->formatPeriod($periode), 150, 8), 386, 620, 8);
        $commands[] = $this->text('Établi par', 308, 600, 8, 'F2');
        $commands[] = $this->text($this->truncate($user?->name ?? 'Système', 150, 8), 386, 600, 8);

        $commands[] = $this->text('Date de génération', 58, 588, 8, 'F2');
        $commands[] = $this->text(now()->format('d/m/Y H:i'), 174, 588, 8);

        if ($summary !== []) {
            $commands[] = $this->text('Synthèse', 48, 548, 10, 'F2');

            $x = 48;
            $y = 520;
            $width = 116;

            foreach (array_slice($summary, 0, 4, preserve_keys: true) as $label => $value) {
                $commands[] = $this->theme->accentColor() . " rg {$x} {$y} {$width} 42 re f";
                $commands[] = $this->theme->borderColor() . " RG {$x} {$y} {$width} 42 re S";
                $commands[] = $this->text($this->truncate((string) $label, $width - 12, 8), $x + 6, $y + 27, 8, 'F2');
                $commands[] = $this->text($this->truncate((string) $value, $width - 12, 11), $x + 6, $y + 10, 11);
                $x += $width + 10;
            }
        }

        return $commands;
    }

    /**
     * @param array<int, string> $streams
     */
    /**
     * @param array<int, string> $streams
     * @param array{header?: array{content: string, width: int, height: int}|null, footer?: array{content: string, width: int, height: int}|null} $images
     */
    private function buildPdf(array $streams, array $images = []): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $imageIds = [];

        foreach (['header' => 'ImHeader', 'footer' => 'ImFooter'] as $key => $name) {
            $image = $images[$key] ?? null;

            if ($image === null) {
                continue;
            }

            $imageId = count($objects) + 1;
            $objects[$imageId] = "<< /Type /XObject /Subtype /Image /Width {$image['width']} /Height {$image['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($image['content']) . " >>\nstream\n{$image['content']}\nendstream";
            $imageIds[$name] = $imageId;
        }

        $kids = [];

        foreach ($streams as $stream) {
            $contentId = count($objects) + 1;
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream";

            $pageId = count($objects) + 1;
            $xObject = '';

            if ($imageIds !== []) {
                $xObjectItems = collect($imageIds)
                    ->map(fn (int $id, string $name): string => "/{$name} {$id} 0 R")
                    ->implode(' ');
                $xObject = " /XObject << {$xObjectItems} >>";
            }

            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->theme->pageWidth() . ' ' . $this->theme->pageHeight() . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >>' . $xObject . ' >> /Contents ' . $contentId . ' 0 R >>';
            $kids[] = "{$pageId} 0 R";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= str_pad((string) $offsets[$id], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function text(string $text, float $x, float $y, int $size = 10, string $font = 'F1', bool $white = false): string
    {
        $color = $white ? '1 1 1 rg' : '0 0 0 rg';

        return "{$color} BT /{$font} {$size} Tf {$x} {$y} Td (" . $this->escape($text) . ') Tj ET';
    }

    private function textIfNotBlank(string $text, float $x, float $y, int $size = 10, string $font = 'F1', bool $white = false): string
    {
        return trim($text) === '' ? '' : $this->text($text, $x, $y, $size, $font, $white);
    }

    private function truncate(string $value, float $width, int $fontSize): string
    {
        $maxCharacters = max(8, (int) floor($width / ($fontSize * 0.48)));

        return mb_strlen($value) > $maxCharacters
            ? mb_substr($value, 0, $maxCharacters - 1) . '…'
            : $value;
    }

    private function formatPeriod(array $periode): string
    {
        $debut = $this->formatValue($periode['debut'] ?? null);
        $fin = $this->formatValue($periode['fin'] ?? null);

        if ($debut === '' && $fin === '') {
            return 'Non renseignée';
        }

        return "{$debut} au {$fin}";
    }

    private function formatValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('d/m/Y H:i');
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if ($value === null) {
            return '';
        }

        return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
    }

    private function escape(string $value): string
    {
        $value = str_replace("\n", ' ', $value);
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded === false ? $value : $encoded);
    }
}
