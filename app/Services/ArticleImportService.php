<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ArticleImportService
{
    /**
     * @return array{created: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function importCsv(string $path): array
    {
        if (! is_readable($path)) {
            throw new InvalidArgumentException("Le fichier d'import est introuvable ou illisible.");
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new InvalidArgumentException("Impossible d'ouvrir le fichier d'import.");
        }

        try {
            $headers = $this->readHeaders($handle);
            $delimiter = $headers['delimiter'];
            $columns = $headers['columns'];

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            $line = 1;

            DB::transaction(function () use ($handle, $delimiter, $columns, &$created, &$updated, &$skipped, &$errors, &$line): void {
                while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $line++;

                    if ($this->isEmptyRow($values)) {
                        continue;
                    }

                    $row = $this->combineRow($columns, $values);

                    try {
                        $result = $this->importRow($row);
                        $created += $result === 'created' ? 1 : 0;
                        $updated += $result === 'updated' ? 1 : 0;
                    } catch (\Throwable $exception) {
                        $skipped++;
                        $errors[] = "Ligne {$line}: {$exception->getMessage()}";
                    }
                }
            });

            return compact('created', 'updated', 'skipped', 'errors');
        } finally {
            fclose($handle);
        }
    }

    public function csvTemplate(): string
    {
        return implode("\n", [
            'numero_reference;designation;categorie;statut;code_ancien;observations',
            'EQ-2026-001;Ordinateur portable;Informatique;Disponible;OLD-001;Exemple de ligne',
            '',
        ]);
    }

    /**
     * @return array{delimiter: string, columns: array<int, string>}
     */
    private function readHeaders(mixed $handle): array
    {
        $firstLine = fgets($handle);

        if ($firstLine === false) {
            throw new InvalidArgumentException("Le fichier d'import est vide.");
        }

        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';
        $columns = str_getcsv($this->removeBom($firstLine), $delimiter);
        $columns = array_map(fn (string $column): string => $this->normalizeHeader($column), $columns);

        $required = ['numero_reference', 'designation', 'categorie'];
        $missing = array_diff($required, $columns);

        if ($missing !== []) {
            throw new InvalidArgumentException('Colonnes obligatoires manquantes: ' . implode(', ', $missing));
        }

        return ['delimiter' => $delimiter, 'columns' => $columns];
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string|null> $values
     * @return array<string, string|null>
     */
    private function combineRow(array $columns, array $values): array
    {
        $row = [];

        foreach ($columns as $index => $column) {
            $value = $values[$index] ?? null;
            $row[$column] = is_string($value) ? trim($value) : $value;
        }

        return $row;
    }

    /**
     * @param array<string, string|null> $row
     */
    private function importRow(array $row): string
    {
        $numeroReference = $this->blankToNull($row['numero_reference'] ?? null);
        $designation = $this->blankToNull($row['designation'] ?? null);
        $categoryValue = $row['categorie'] ?? ($row['categorie_id'] ?? null);

        if ($numeroReference === null) {
            throw new InvalidArgumentException('Le numéro de référence est obligatoire.');
        }

        if ($designation === null) {
            throw new InvalidArgumentException('La désignation est obligatoire.');
        }

        $categorie = $this->resolveCategorie($categoryValue);

        $values = [
            'numero_reference' => $numeroReference,
            'designation' => $designation,
            'code_ancien' => $this->blankToNull($row['code_ancien'] ?? null),
            'statut' => $this->resolveStatut($row['statut'] ?? null),
            'observations' => $this->blankToNull($row['observations'] ?? null),
            'categorie_id' => $categorie->id,
        ];

        $article = Article::query()->firstOrNew(['numero_reference' => $numeroReference]);
        $exists = $article->exists;
        $article->fill($values);
        $article->save();

        return $exists ? 'updated' : 'created';
    }

    private function resolveCategorie(?string $value): Categorie
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            throw new InvalidArgumentException('La catégorie est obligatoire.');
        }

        $query = Categorie::query()
            ->where('nom_categorie', $value)
            ->orWhere('code_categorie', $value);

        if (ctype_digit($value)) {
            $query->orWhereKey((int) $value);
        }

        $categorie = $query->first();

        if ($categorie === null) {
            throw new InvalidArgumentException("Catégorie introuvable: {$value}.");
        }

        return $categorie;
    }

    private function resolveStatut(?string $value): string
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            return Article::DISPONIBLE;
        }

        $normalized = str($value)->ascii()->lower()->replace([' ', '-'], '_')->toString();

        return match ($normalized) {
            'disponible' => Article::DISPONIBLE,
            'affecte' => Article::AFFECTE,
            'en_maintenance', 'maintenance' => Article::MAINTENANCE,
            'reforme' => Article::REFORME,
            default => throw new InvalidArgumentException("Statut invalide: {$value}."),
        };
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($this->removeBom($header));
        $header = str($header)->ascii()->lower()->replace([' ', '-'], '_')->toString();

        return match ($header) {
            'reference', 'ref', 'numero', 'n_reference', 'n_ref', 'no_reference' => 'numero_reference',
            'ancien_code' => 'code_ancien',
            'category', 'categorie_id' => 'categorie',
            default => $header,
        };
    }

    /**
     * @param array<int, string|null> $values
     */
    private function isEmptyRow(array $values): bool
    {
        return collect($values)
            ->every(fn (?string $value): bool => trim((string) $value) === '');
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function removeBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }
}
