<?php

namespace App\Services;

use App\Models\Rapport;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class RapportService
{
    /**
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     */
    public function exportPdf(string $typeRapport, iterable $rows, ?User $user = null): Rapport
    {
        $path = $this->buildPath($typeRapport, 'pdf');
        Storage::disk('local')->put($path, $this->makePdf($typeRapport, $this->normalizeRows($rows)));

        return $this->persistRapport($typeRapport, 'PDF', $path, $user);
    }

    /**
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     */
    public function exportExcel(string $typeRapport, iterable $rows, ?User $user = null): Rapport
    {
        $path = $this->buildPath($typeRapport, 'csv');
        Storage::disk('local')->put($path, $this->makeCsv($this->normalizeRows($rows)));

        return $this->persistRapport($typeRapport, 'Excel', $path, $user);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function makeCsv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $headers = array_keys($rows[0]);
        $lines = [$this->csvLine($headers)];

        foreach ($rows as $row) {
            $lines[] = $this->csvLine(array_map(fn (string $header) => $row[$header] ?? '', $headers));
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function makePdf(string $title, array $rows): string
    {
        $content = "%PDF-1.4\n";
        $objects = [];
        $body = "BT /F1 12 Tf 50 780 Td (" . $this->escapePdf($title) . ") Tj";
        $line = 760;

        foreach (array_slice($rows, 0, 45) as $row) {
            $body .= " 50 {$line} Td (" . $this->escapePdf(implode(' | ', array_map('strval', $row))) . ") Tj";
            $line -= 16;
        }

        $body .= ' ET';
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Length " . strlen($body) . " >>\nstream\n{$body}\nendstream";

        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($content);
            $content .= ($index + 1) . " 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($content);
        $content .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $content .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $content . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function persistRapport(string $typeRapport, string $format, string $path, ?User $user): Rapport
    {
        try {
            return DB::transaction(fn (): Rapport => $this->createRapport($typeRapport, $format, $path, $user));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    private function createRapport(string $typeRapport, string $format, string $path, ?User $user): Rapport
    {
        return Rapport::create([
            'type_rapport' => $typeRapport,
            'format' => $format,
            'chemin_fichier' => $path,
            'date_generation' => Carbon::now(),
            'user_id' => $user?->id,
        ]);
    }

    private function buildPath(string $typeRapport, string $extension): string
    {
        $slug = str($typeRapport)->slug()->value();

        if ($slug === '') {
            throw new InvalidArgumentException('Le type de rapport est obligatoire.');
        }

        return 'rapports/' . $slug . '-' . Carbon::now()->format('Ymd-His') . '.' . $extension;
    }

    /**
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(iterable $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $normalized[] = $row instanceof Arrayable ? $row->toArray() : $row;
        }

        return $normalized;
    }

    /**
     * @param iterable<mixed> $values
     */
    private function csvLine(iterable $values): string
    {
        return collect($values)
            ->map(fn (mixed $value) => '"' . str_replace('"', '""', (string) $value) . '"')
            ->implode(',');
    }

    private function escapePdf(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
