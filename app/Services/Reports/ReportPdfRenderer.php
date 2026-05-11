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
    private function renderPage(string $title, array $columns, array $rows, int $page, int $totalPages, ?User $user, array $periode, array $summary): string
    {
        $commands = [];
        $commands[] = $this->theme->backgroundColor() . ' rg 0 0 ' . $this->theme->pageWidth() . ' ' . $this->theme->pageHeight() . ' re f';
        $commands[] = $this->theme->borderColor() . ' RG 32 32 ' . ($this->theme->pageWidth() - 64) . ' ' . ($this->theme->pageHeight() - 64) . ' re S';

        $commands[] = $this->text($this->theme->brandName(), 42, 558, 12, 'F2');
        $commands[] = $this->text($this->theme->serviceName(), 42, 542, 9);
        $commands[] = $this->text($this->theme->classificationLabel(), 636, 558, 9, 'F2');
        $commands[] = $this->text($this->theme->documentNature(), 636, 542, 9);
        $commands[] = $this->theme->primaryColor() . ' RG 42 526 758 1 re S';

        $commands[] = $this->text(strtoupper($this->theme->documentNature()), 344, 502, 13, 'F2');
        $commands[] = $this->text($this->truncate(strtoupper($title), 740, 11), 42, 478, 11, 'F2');

        if ($page === 1) {
            $commands = array_merge($commands, $this->renderAdministrativeNotice($title, $user, $periode, $summary));
        }

        $commands[] = $this->text($this->theme->tableTitle(), 42, 326, 10, 'F2');

        $tableTop = 306;
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

        $commands[] = $this->theme->mutedColor() . ' rg ' . $this->theme->margin() . ' 24 ' . $tableWidth . ' 1 re f';
        $commands[] = $this->text("Page {$page}/{$totalPages}", 730, 12, 9);
        $commands[] = $this->text($this->theme->footerLabel(), 48, 12, 8);

        return implode("\n", $commands);
    }

    /**
     * @param array<string, string|int|float> $summary
     * @return array<int, string>
     */
    private function renderAdministrativeNotice(string $title, ?User $user, array $periode, array $summary): array
    {
        $commands = [];
        $summary = $summary === [] ? ['Lignes' => 0, 'Période' => 'Filtrée', 'Classification' => 'Interne'] : $summary;
        $reference = 'RAP-' . now()->format('Ymd-His');
        $summaryText = $this->formatSummaryText($summary);

        $commands[] = $this->theme->accentColor() . ' rg 42 386 758 70 re f';
        $commands[] = $this->theme->borderColor() . ' RG 42 386 758 70 re S';
        $commands[] = $this->theme->borderColor() . ' RG 42 421 758 1 re S';
        $commands[] = $this->theme->borderColor() . ' RG 421 386 1 70 re S';

        $commands[] = $this->text('Référence', 52, 440, 8, 'F2');
        $commands[] = $this->text($reference, 136, 440, 8);
        $commands[] = $this->text('Objet', 52, 424, 8, 'F2');
        $commands[] = $this->text($this->truncate($title, 260, 8), 136, 424, 8);

        $commands[] = $this->text('Période', 432, 440, 8, 'F2');
        $commands[] = $this->text($this->formatPeriod($periode), 516, 440, 8);
        $commands[] = $this->text('Établi par', 432, 424, 8, 'F2');
        $commands[] = $this->text($this->truncate($user?->name ?? 'Système', 240, 8), 516, 424, 8);

        $commands[] = $this->text('Date de génération', 52, 404, 8, 'F2');
        $commands[] = $this->text(now()->format('d/m/Y H:i'), 166, 404, 8);
        $commands[] = $this->text('Niveau de diffusion', 432, 404, 8, 'F2');
        $commands[] = $this->text($this->theme->classificationLabel(), 558, 404, 8);

        $commands[] = $this->text('Synthèse administrative', 42, 362, 10, 'F2');

        foreach ($this->wrap($summaryText, 142) as $index => $line) {
            $commands[] = $this->text($line, 42, 346 - ($index * 12), 8);
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
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->theme->pageWidth() . ' ' . $this->theme->pageHeight() . '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
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

    /**
     * @param array<string, string|int|float> $summary
     */
    private function formatSummaryText(array $summary): string
    {
        $items = [];

        foreach (array_slice($summary, 0, 5, preserve_keys: true) as $label => $value) {
            $items[] = mb_strtolower((string) $label) . ' : ' . $this->formatValue($value);
        }

        return 'Le présent rapport consolide les informations disponibles sur la période indiquée. '
            . 'Les constats principaux sont les suivants : ' . implode('; ', $items) . '. '
            . 'Les données ci-dessous doivent être exploitées dans le respect des habilitations internes.';
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, int $maxCharacters): array
    {
        $words = preg_split('/\s+/', $text) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);

            if ($line !== '' && mb_strlen($candidate) > $maxCharacters) {
                $lines[] = $line;
                $line = $word;
                continue;
            }

            $line = $candidate;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return array_slice($lines, 0, 3);
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
