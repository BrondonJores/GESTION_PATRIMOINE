<?php

namespace App\Services;

use App\Models\Categorie;
use App\Models\Consommable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConsommableImportService
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
            'designation;categorie;quantite_stock;quantite_min;observations',
            'Marqueur bleu;Fournitures;120;20;Exemple de ligne',
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

        $required = ['designation', 'categorie', 'quantite_stock'];
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
        $designation = $row['designation'] ?? null;
        $categoryValue = $row['categorie'] ?? ($row['categorie_id'] ?? null);

        if ($designation === null || $designation === '') {
            throw new InvalidArgumentException('La désignation est obligatoire.');
        }

        $categorie = $this->resolveCategorie($categoryValue);
        $quantiteStock = $this->positiveInteger($row['quantite_stock'] ?? null, 'quantite_stock');
        $quantiteMin = $this->nullablePositiveInteger($row['quantite_min'] ?? null, 'quantite_min');

        $values = [
            'designation' => $designation,
            'categorie_id' => $categorie->id,
            'quantite_stock' => $quantiteStock,
            'quantite_min' => $quantiteMin,
            'observations' => $this->blankToNull($row['observations'] ?? null),
        ];
        $values['statut'] = (new Consommable($values))->calculerStatut();

        $consommable = Consommable::query()->firstOrNew([
            'designation' => $designation,
            'categorie_id' => $categorie->id,
        ]);
        $exists = $consommable->exists;
        $consommable->fill($values);
        $consommable->save();

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

    private function positiveInteger(?string $value, string $column): int
    {
        if ($value === null || $value === '' || ! ctype_digit($value)) {
            throw new InvalidArgumentException("{$column} doit être un entier positif ou nul.");
        }

        return (int) $value;
    }

    private function nullablePositiveInteger(?string $value, string $column): ?int
    {
        $value = $this->blankToNull($value);

        return $value === null ? null : $this->positiveInteger($value, $column);
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($this->removeBom($header));
        $header = str($header)->ascii()->lower()->replace([' ', '-'], '_')->toString();

        return match ($header) {
            'seuil_min', 'stock_min', 'stock_minimal' => 'quantite_min',
            'stock', 'quantite', 'quantite_actuelle' => 'quantite_stock',
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
