<?php

namespace App\Services;

use App\Models\Rapport;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class RapportService
{
    /**
     * Exporte un rapport PDF minimal et trace le fichier généré.
     *
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     */
    public function exportPdf(string $typeRapport, iterable $rows, ?User $user = null): Rapport
    {
        $path = $this->buildPath($typeRapport, 'pdf');
        Storage::disk('local')->put($path, $this->makePdf($typeRapport, $this->normalizeRows($rows)));

        return $this->persistRapport($typeRapport, 'PDF', $path, $user);
    }

    /**
     * Exporte un vrai fichier Excel XLSX et trace le fichier généré.
     *
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     */
    public function exportExcel(string $typeRapport, iterable $rows, ?User $user = null): Rapport
    {
        $path = $this->buildPath($typeRapport, 'xlsx');
        Storage::disk('local')->put($path, $this->makeXlsx($this->normalizeRows($rows)));

        return $this->persistRapport($typeRapport, 'Excel', $path, $user);
    }

    /**
     * Génère un fichier XLSX compatible Excel.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    private function makeXlsx(array $rows): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'rapport-excel-');

        if ($temporaryFile === false) {
            throw new \RuntimeException('Impossible de préparer le fichier Excel temporaire.');
        }

        try {
            $writer = new Writer();
            $writer->openToFile($temporaryFile);

            $headerStyle = (new Style())
                ->setFontBold()
                ->setBackgroundColor('FFE5E7EB');

            if ($rows === []) {
                $writer->addRow(Row::fromValues(['Aucune donnée']));
            } else {
                $headers = array_keys($rows[0]);
                $writer->addRow(Row::fromValues($headers, $headerStyle));

                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues(array_map(
                        fn (string $header): mixed => $row[$header] ?? null,
                        $headers,
                    )));
                }
            }

            $writer->close();

            $content = file_get_contents($temporaryFile);
        } finally {
            @unlink($temporaryFile);
        }

        if ($content === false) {
            throw new \RuntimeException('Impossible de lire le fichier Excel généré.');
        }

        return $content;
    }

    /**
     * Génère un PDF minimal sans dépendance externe.
     *
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
            return DB::transaction(function () use ($typeRapport, $format, $path, $user): Rapport {
                $rapport = $this->createRapport($typeRapport, $format, $path, $user);

                app(AuditLogService::class)->export("Rapports - {$typeRapport}", $user);

                return $rapport;
            });
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
     * Normalise les lignes exportées avant génération du fichier.
     *
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(iterable $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $normalized[] = array_map(
                fn (mixed $value): mixed => is_scalar($value) || $value === null
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE),
                $row instanceof Arrayable ? $row->toArray() : $row,
            );
        }

        return $normalized;
    }

    private function escapePdf(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
