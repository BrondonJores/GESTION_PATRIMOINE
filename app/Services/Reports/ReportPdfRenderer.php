<?php

namespace App\Services\Reports;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

        return Pdf::loadView('reports.pdf', [
            'title' => $title,
            'columns' => $columns ?: ['Information'],
            'rows' => $this->formattedRows($rows),
            'user' => $user,
            'periode' => $this->formatPeriod($periode),
            'summary' => $summary,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'reference' => 'RAP-' . now()->format('Ymd-His'),
            'identity' => $this->theme->identity(),
            'headerImageSrc' => $this->imageSrc($this->theme->headerImage()),
            'footerImageSrc' => $this->imageSrc($this->theme->footerImage()),
        ])
            ->setPaper('a4', 'portrait')
            ->output();
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

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, string>>
     */
    private function formattedRows(array $rows): array
    {
        return array_map(
            fn (array $row): array => array_map(fn (mixed $value): string => $this->formatValue($value), $row),
            $rows,
        );
    }

    /**
     * @param array{content: string, width: int, height: int}|null $image
     */
    private function imageSrc(?array $image): ?string
    {
        if ($image === null) {
            return null;
        }

        return 'data:image/jpeg;base64,' . base64_encode($image['content']);
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

        $value = trim((string) $value, " \t\n\r\0\x0B\"");

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value) === 1) {
            return Carbon::parse($value)->format('d/m/Y H:i');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) === 1) {
            return Carbon::parse($value)->format('d/m/Y H:i');
        }

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
