<?php

namespace App\Services\Reports;

use App\Models\User;
use Illuminate\Support\Carbon;

class ReportPdfRenderer
{
    private const PAGE_WIDTH = 842;
    private const PAGE_HEIGHT = 595;
    private const MARGIN = 32;
    private const ROW_HEIGHT = 22;
    private const HEADER_HEIGHT = 188;
    private const FOOTER_HEIGHT = 34;

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
            );
        }

        return $this->buildPdf($streams);
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
        $availableHeight = self::PAGE_HEIGHT - self::HEADER_HEIGHT - self::FOOTER_HEIGHT - self::MARGIN;

        return (int) floor($availableHeight / self::ROW_HEIGHT);
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, array<string, mixed>> $rows
     * @param array{debut?: mixed, fin?: mixed} $periode
     * @param array<string, string|int|float> $summary
     */
    private function renderPage(string $title, array $columns, array $rows, int $page, int $totalPages, ?User $user, array $periode, array $summary): string
    {
        $commands = [];
        $commands[] = '0.96 0.97 0.98 rg 0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . ' re f';
        $commands[] = '0.12 0.16 0.23 rg 0 505 ' . self::PAGE_WIDTH . ' 90 re f';
        $commands[] = '0.88 0.92 0.98 rg 32 550 130 24 re f';

        $commands[] = $this->text('INTERNE - DIFFUSION LIMITÉE', 42, 557, 9, 'F2', white: false);
        $commands[] = $this->text('Gestion du patrimoine', 42, 530, 16, 'F2', white: true);
        $commands[] = $this->text(strtoupper($title), 42, 508, 12, 'F2', white: true);
        $commands[] = $this->text('Généré le : ' . now()->format('d/m/Y H:i'), 620, 548, 9, white: true);
        $commands[] = $this->text('Généré par : ' . ($user?->name ?? 'Système'), 620, 532, 9, white: true);
        $commands[] = $this->text('Période : ' . $this->formatPeriod($periode), 620, 516, 9, white: true);

        if ($page === 1) {
            $commands = array_merge($commands, $this->renderSummary($summary));
        }

        $commands[] = $this->text('Détail du rapport', 48, 400, 11, 'F2');

        $tableTop = 378;
        $tableLeft = self::MARGIN;
        $tableWidth = self::PAGE_WIDTH - (self::MARGIN * 2);
        $columnWidth = $tableWidth / max(count($columns), 1);

        $commands[] = '0.12 0.16 0.23 rg ' . $tableLeft . ' ' . ($tableTop - self::ROW_HEIGHT) . ' ' . $tableWidth . ' ' . self::ROW_HEIGHT . ' re f';

        foreach ($columns as $index => $column) {
            $x = $tableLeft + ($index * $columnWidth);
            $commands[] = '1 1 1 RG ' . $x . ' ' . ($tableTop - self::ROW_HEIGHT) . ' ' . $columnWidth . ' ' . self::ROW_HEIGHT . ' re S';
            $commands[] = $this->text($this->truncate($column, $columnWidth, 8), $x + 5, $tableTop - 15, 8, 'F2', white: true);
        }

        $y = $tableTop - (self::ROW_HEIGHT * 2);

        foreach ($rows as $rowIndex => $row) {
            $fill = $rowIndex % 2 === 0 ? '1 1 1' : '0.98 0.98 0.99';
            $commands[] = "{$fill} rg {$tableLeft} {$y} {$tableWidth} " . self::ROW_HEIGHT . ' re f';

            foreach ($columns as $index => $column) {
                $x = $tableLeft + ($index * $columnWidth);
                $value = $this->formatValue($row[$column] ?? '');
                $commands[] = '0.82 0.84 0.88 RG ' . $x . ' ' . $y . ' ' . $columnWidth . ' ' . self::ROW_HEIGHT . ' re S';
                $commands[] = $this->text($this->truncate($value, $columnWidth, 8), $x + 5, $y + 8, 8);
            }

            $y -= self::ROW_HEIGHT;
        }

        $commands[] = '0.45 0.48 0.55 rg ' . self::MARGIN . ' 24 778 1 re f';
        $commands[] = $this->text("Page {$page}/{$totalPages}", 730, 12, 9);
        $commands[] = $this->text('Document généré automatiquement - ne pas diffuser hors service autorisé.', 48, 12, 8);

        return implode("\n", $commands);
    }

    /**
     * @param array<string, string|int|float> $summary
     * @return array<int, string>
     */
    private function renderSummary(array $summary): array
    {
        $commands = [];
        $summary = $summary === [] ? ['Lignes' => 0, 'Période' => 'Filtrée', 'Classification' => 'Interne'] : $summary;
        $cards = array_slice($summary, 0, 4, preserve_keys: true);
        $cardWidth = 184;
        $x = 42;

        $commands[] = $this->text('Résumé exécutif', 42, 476, 12, 'F2');

        foreach ($cards as $label => $value) {
            $commands[] = '1 1 1 rg ' . $x . ' 424 ' . $cardWidth . ' 38 re f';
            $commands[] = '0.82 0.84 0.88 RG ' . $x . ' 424 ' . $cardWidth . ' 38 re S';
            $commands[] = $this->text((string) $label, $x + 10, 448, 8);
            $commands[] = $this->text((string) $value, $x + 10, 432, 13, 'F2');
            $x += $cardWidth + 10;
        }

        return $commands;
    }

    /**
     * @param array<int, string> $streams
     */
    private function buildPdf(array $streams): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $kids = [];

        foreach ($streams as $stream) {
            $contentId = count($objects) + 1;
            $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream";

            $pageId = count($objects) + 1;
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
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
