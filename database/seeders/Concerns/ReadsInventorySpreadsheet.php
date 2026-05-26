<?php

namespace Database\Seeders\Concerns;

use DOMDocument;
use DOMNode;
use DOMXPath;
use ZipArchive;

trait ReadsInventorySpreadsheet
{
    protected const INVENTORY_SOURCE_FILE = 'Canevas INVENTAIRE.xlsx';

    private const INVENTORY_HEADER_MAP = [
        'famille' => 'famille',
        'categorie' => 'categorie',
        'designation' => 'designation',
        'bloc' => 'bloc',
        'lieu_d_affectation' => 'lieu',
        'nouveau_n_d_inventaire' => 'numero_reference',
        'ancien_n_d_inventaire' => 'code_ancien',
    ];

    /**
     * @return array<int, array<string, string|int>>
     */
    protected function inventoryRows(): array
    {
        $path = base_path(self::INVENTORY_SOURCE_FILE);

        if (! is_readable($path)) {
            $this->command?->warn("Inventaire ignoré : fichier [{$path}] introuvable.");

            return [];
        }

        $rawRows = $this->readFirstWorksheet($path);

        if ($rawRows === []) {
            return [];
        }

        $headers = array_map(fn (?string $header): string => $this->normalizeInventoryKey((string) $header), array_shift($rawRows));
        $columns = [];

        foreach ($headers as $index => $header) {
            if (isset(self::INVENTORY_HEADER_MAP[$header])) {
                $columns[$index] = self::INVENTORY_HEADER_MAP[$header];
            }
        }

        $rows = [];

        foreach ($rawRows as $offset => $rawRow) {
            $row = [
                'source_row' => $offset + 2,
                'famille' => '',
                'categorie' => '',
                'designation' => '',
                'bloc' => '',
                'lieu' => '',
                'numero_reference' => '',
                'code_ancien' => '',
            ];

            foreach ($columns as $index => $column) {
                $row[$column] = $this->cleanInventoryText($rawRow[$index] ?? '');
            }

            if ($row['famille'] === '' && $row['categorie'] === '' && $row['designation'] === '') {
                continue;
            }

            if ($row['famille'] === '' || $row['categorie'] === '' || $row['designation'] === '') {
                $this->command?->warn("Ligne {$row['source_row']} ignorée : famille, catégorie ou désignation manquante.");

                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string, string|int> $row
     */
    protected function inventoryReference(array $row): string
    {
        $newReference = $this->blankInventoryValue((string) $row['numero_reference']);

        if ($newReference !== null) {
            return $newReference;
        }

        $oldReference = $this->blankInventoryValue((string) $row['code_ancien']);

        if ($oldReference !== null) {
            return 'ANCIEN-' . $oldReference;
        }

        return 'XLS-L' . $row['source_row'];
    }

    /**
     * @param array<string, string|int> $row
     */
    protected function inventoryArticleObservations(array $row, string $reference): string
    {
        $notes = [
            "Import inventaire Excel, ligne {$row['source_row']}.",
            "Bloc source : " . ($this->blankInventoryValue((string) $row['bloc']) ?? 'Non renseigné') . '.',
            "Lieu source : " . ($this->blankInventoryValue((string) $row['lieu']) ?? 'Non renseigné') . '.',
        ];

        if ($this->blankInventoryValue((string) $row['numero_reference']) === null) {
            $notes[] = "Référence générée : {$reference}.";
        }

        return implode(' ', $notes);
    }

    protected function inventoryFamilyName(string $value): string
    {
        return match ($this->normalizeInventoryKey($value)) {
            'materiel_et_outillage' => 'Matériel et outillage',
            'materiel_informatique' => 'Matériel informatique',
            'materiel_mobilier' => 'Matériel mobilier',
            'materiel_mobilier_de_bureau' => 'Matériel mobilier de bureau',
            'materiel_pedagogique' => 'Matériel pédagogique',
            'materiel_technique' => 'Matériel technique',
            'equipement_technique' => 'Équipement technique',
            default => $this->cleanInventoryText($value),
        };
    }

    protected function inventoryBlocName(string $value): string
    {
        return match ($this->normalizeInventoryKey($value)) {
            '' => 'Non renseigné',
            'hotel' => 'Hôtel',
            default => $this->cleanInventoryText($value),
        };
    }

    protected function inventorySalleName(string $value): string
    {
        return $this->blankInventoryValue($value) ?? 'Non renseigné';
    }

    protected function isInventoryReformed(string $lieu): bool
    {
        return $this->normalizeInventoryKey($lieu) === 'reforme';
    }

    protected function inventoryCode(string $prefix, string $value): string
    {
        $slug = strtoupper($this->normalizeInventoryKey($value));
        $slug = str_replace('_', '-', $slug);
        $code = "{$prefix}-{$slug}";

        return mb_substr($code, 0, 50);
    }

    protected function inventoryLocationCode(string $bloc, string $salle): string
    {
        $slug = strtoupper($this->normalizeInventoryKey("{$bloc}-{$salle}"));
        $slug = str_replace('_', '-', $slug);
        $code = "SALLE-{$slug}";

        if (mb_strlen($code) <= 50) {
            return $code;
        }

        return mb_substr($code, 0, 41) . '-' . substr(hash('crc32b', "{$bloc}|{$salle}"), 0, 8);
    }

    protected function cleanInventoryText(?string $value): string
    {
        $value = str_replace("\xc2\xa0", ' ', (string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    protected function blankInventoryValue(?string $value): ?string
    {
        $value = $this->cleanInventoryText($value);

        return $value === '' ? null : $value;
    }

    protected function normalizeInventoryKey(string $value): string
    {
        $value = $this->cleanInventoryText($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? $value;

        return trim($value, '_');
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readFirstWorksheet(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return [];
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $worksheetPath = $this->firstWorksheetPath($zip);

            if ($worksheetPath === null) {
                return [];
            }

            $xml = $zip->getFromName($worksheetPath);

            if ($xml === false) {
                return [];
            }

            return $this->readWorksheetRows($xml, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = $this->loadXml($xml);
        $xpath = new DOMXPath($document);
        $strings = [];

        foreach ($xpath->query('//*[local-name()="si"]') as $node) {
            $strings[] = $this->cleanInventoryText($node->textContent);
        }

        return $strings;
    }

    private function firstWorksheetPath(ZipArchive $zip): ?string
    {
        $xml = $zip->getFromName('xl/workbook.xml');

        if ($xml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $document = $this->loadXml($xml);
        $xpath = new DOMXPath($document);
        $sheet = $xpath->query('//*[local-name()="sheet"]')->item(0);

        if ($sheet === null) {
            return 'xl/worksheets/sheet1.xml';
        }

        $sheetId = $sheet->attributes?->getNamedItem('sheetId')?->nodeValue ?? '1';

        return "xl/worksheets/sheet{$sheetId}.xml";
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function readWorksheetRows(string $xml, array $sharedStrings): array
    {
        $document = $this->loadXml($xml);
        $xpath = new DOMXPath($document);
        $rows = [];

        foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') as $rowNode) {
            $row = [];

            foreach ($xpath->query('./*[local-name()="c"]', $rowNode) as $cellNode) {
                $reference = $cellNode->attributes?->getNamedItem('r')?->nodeValue ?? '';
                $index = $this->columnIndex($reference);
                $row[$index] = $this->cellValue($cellNode, $xpath, $sharedStrings);
            }

            if ($row !== []) {
                ksort($row);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function cellValue(DOMNode $cellNode, DOMXPath $xpath, array $sharedStrings): string
    {
        $type = $cellNode->attributes?->getNamedItem('t')?->nodeValue;
        $valueNode = $xpath->query('./*[local-name()="v"]', $cellNode)->item(0);

        if ($type === 's' && $valueNode !== null) {
            return $sharedStrings[(int) $valueNode->textContent] ?? '';
        }

        if ($type === 'inlineStr') {
            return $this->cleanInventoryText($cellNode->textContent);
        }

        return $valueNode !== null ? $this->cleanInventoryText($valueNode->textContent) : '';
    }

    private function columnIndex(string $cellReference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellReference)) ?? '';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function loadXml(string $xml): DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $document;
    }
}
