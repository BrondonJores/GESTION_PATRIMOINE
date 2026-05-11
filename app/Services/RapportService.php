<?php

namespace App\Services;

use App\Models\Rapport;
use App\Models\User;
use App\Services\Reports\ReportPdfRenderer;
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
     * Exporte un rapport PDF mis en page et trace le fichier généré.
     *
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     * @param array{debut?: mixed, fin?: mixed} $periode
     */
    public function exportPdf(string $typeRapport, iterable $rows, ?User $user = null, array $periode = []): Rapport
    {
        $rows = $this->normalizeRows($rows);
        $path = $this->buildPath($typeRapport, 'pdf');
        Storage::disk('local')->put($path, app(ReportPdfRenderer::class)->render($typeRapport, $rows, $user, $periode));

        return $this->persistRapport($typeRapport, 'PDF', $path, $user, $periode);
    }

    /**
     * Exporte un vrai fichier Excel XLSX et trace le fichier généré.
     *
     * @param iterable<array<string, mixed>|Arrayable<string, mixed>> $rows
     * @param array{debut?: mixed, fin?: mixed} $periode
     */
    public function exportExcel(string $typeRapport, iterable $rows, ?User $user = null, array $periode = []): Rapport
    {
        $path = $this->buildPath($typeRapport, 'xlsx');
        Storage::disk('local')->put($path, $this->makeXlsx($this->normalizeRows($rows)));

        return $this->persistRapport($typeRapport, 'Excel', $path, $user, $periode);
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

    private function persistRapport(string $typeRapport, string $format, string $path, ?User $user, array $periode = []): Rapport
    {
        try {
            return DB::transaction(function () use ($typeRapport, $format, $path, $user, $periode): Rapport {
                $rapport = $this->createRapport($typeRapport, $format, $path, $user, $periode);

                app(AuditLogService::class)->export("Rapports - {$typeRapport}", $user);

                if ($user !== null) {
                    app(NotificationService::class)->notifyUser(
                        $user,
                        "Votre rapport {$typeRapport} au format {$format} est prêt.",
                    );
                }

                return $rapport;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }

    private function createRapport(string $typeRapport, string $format, string $path, ?User $user, array $periode = []): Rapport
    {
        return Rapport::create([
            'type_rapport' => $typeRapport,
            'format' => $format,
            'periode_debut' => $periode['debut'] ?? null,
            'periode_fin' => $periode['fin'] ?? null,
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
}
