<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\CalculationItem;
use App\Models\TariffCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsvImportService
{
    protected array $requiredColumns = [
        'part_number',
        'description_en',
        'quantity',
        'unit_price_fob',
    ];

    protected array $optionalColumns = [
        'description_es',
        'unit_weight',
        'hs_code',
        'ice_exempt',
        'ice_exempt_reason',
    ];

    public function importFromCsv(UploadedFile $file, Calculation $calculation): array
    {
        $results = [
            'toCreate' => [],
            'toUpdate' => [],
            'toDelete' => [],
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $csvData = $this->parseCsvFile($file);
            
            if (count($csvData) < 2) {
                throw new \Exception('El archivo CSV está vacío o solo contiene la cabecera.');
            }

            $headers = array_map('strtolower', array_map('trim', $csvData[0]));
            $this->validateCsvHeaders($headers);

            $existingItems = $calculation->items()->get()->keyBy('part_number');
            $csvPartNumbers = [];

            // Process rows for creation or update
            for ($i = 1; $i < count($csvData); $i++) {
                $row = $csvData[$i];
                $rowNumber = $i + 1;

                try {
                    if (count($row) !== count($headers)) {
                        $results['errors'][] = "Fila {$rowNumber}: El número de columnas no coincide con la cabecera.";
                        continue;
                    }
                    $data = array_combine($headers, $row);
                    $partNumber = $data['part_number'] ?? null;

                    if (empty($partNumber)) {
                        $results['errors'][] = "Fila {$rowNumber}: El 'part_number' es obligatorio.";
                        continue;
                    }

                    $csvPartNumbers[$partNumber] = true;

                    $itemData = $this->mapCsvDataToItem($data);
                    $newItem = null;
                    $item = null;

                    if ($existingItems->has($partNumber)) {
                        $item = $existingItems->get($partNumber);
                        $results['toUpdate'][] = ['item' => $item, 'data' => $itemData];
                    } else {
                        $newItem = new CalculationItem($itemData);
                        $results['toCreate'][] = $newItem;
                    }

                    $currentItem = $item ?? $newItem;
                    if ($currentItem && empty($currentItem->hs_code)) {
                        $suggestion = $this->suggestTariffCode($currentItem);
                        if ($suggestion) {
                            $results['warnings'][] = "Fila {$rowNumber}: Se sugiere el código arancelario {$suggestion['hs_code']} para '{$currentItem->description_en}'";
                        }
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Fila {$rowNumber}: " . $e->getMessage();
                }
            }

            $itemsToDelete = $existingItems->filter(function ($item, $partNumber) use ($csvPartNumbers) {
                return !isset($csvPartNumbers[$partNumber]);
            });

            foreach ($itemsToDelete as $item) {
                $results['toDelete'][] = $item;
            }

        } catch (\Exception $e) {
            $results['errors'][] = "Error general: {$e->getMessage()}";
        }

        return $results;
    }

    protected function parseCsvFile(UploadedFile $file): array
    {
        $content = file_get_contents($file->getPathname());

        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }
        
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $lines = str_getcsv($content, "\n");
        $csvData = [];

        foreach ($lines as $line) {
            if (trim($line)) {
                $csvData[] = str_getcsv($line, ',');
            }
        }

        return $csvData;
    }

    protected function validateCsvHeaders(array $headers): void
    {
        $headers = array_map('strtolower', array_map('trim', $headers));
        
        foreach ($this->requiredColumns as $required) {
            if (!in_array(strtolower($required), $headers)) {
                throw new \Exception("Columna requerida '{$required}' no encontrada en el CSV.");
            }
        }
    }

    protected function mapCsvDataToItem(array $data): array
    {
        if (empty($data['part_number'])) {
            throw new \Exception("'part_number' es requerido.");
        }
        if (empty($data['description_en'])) {
            throw new \Exception("Descripción en inglés es requerida.");
        }

        $quantity = (int) ($data['quantity'] ?? 0);
        $unitPriceFob = (float) ($data['unit_price_fob'] ?? 0);

        if ($quantity <= 0) {
            throw new \Exception("Cantidad debe ser mayor a 0.");
        }
        if ($unitPriceFob <= 0) {
            throw new \Exception("Precio unitario FOB debe ser mayor a 0.");
        }

        $totalFobValue = $quantity * $unitPriceFob;

        $itemData = [
            'part_number' => $data['part_number'],
            'description_en' => $data['description_en'],
            'description_es' => $data['description_es'] ?? null,
            'hs_code' => $this->cleanHsCode($data['hs_code'] ?? null),
            'ice_exempt' => filter_var($data['ice_exempt'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'ice_exempt_reason' => $data['ice_exempt_reason'] ?? null,
            'unit_weight' => !empty($data['unit_weight']) ? (float) $data['unit_weight'] : null,
            'quantity' => $quantity,
            'unit_price_fob' => $unitPriceFob,
            'total_fob_value' => $totalFobValue,
            'cif_value' => $totalFobValue,
            'total_cost' => $totalFobValue,
            'unit_cost' => $unitPriceFob,
            'sale_price' => $totalFobValue,
            'unit_sale_price' => $unitPriceFob,
        ];

        if ($itemData['ice_exempt'] && empty($itemData['ice_exempt_reason'])) {
            throw new \Exception("Razón de exoneración de ICE es requerida cuando el producto está exento.");
        }

        if ($itemData['hs_code'] && !TariffCode::where('hs_code', $itemData['hs_code'])->exists()) {
            throw new \Exception("Código arancelario '{$itemData['hs_code']}' no existe en la base de datos.");
        }

        return $itemData;
    }

    protected function cleanHsCode(?string $hsCode): ?string
    {
        if (empty($hsCode)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $hsCode);
        
        if (strlen($cleaned) < 4) {
            return null;
        }

        return substr($cleaned, 0, 10);
    }

    public function suggestTariffCode(CalculationItem $item): ?array
    {
        $description = strtolower($item->description_en);
        $words = explode(' ', $description);
        
        $suggestions = TariffCode::where('is_active', true)
            ->where('hierarchy_level', 10)
            ->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $query->orWhere('description_en', 'LIKE', "%{$word}%")
                              ->orWhere('description_es', 'LIKE', "%{$word}%");
                    }
                }
            })
            ->limit(5)
            ->get(['hs_code', 'description_en', 'description_es']);

        if ($suggestions->isEmpty()) {
            $previousItems = CalculationItem::whereNotNull('hs_code')
                ->where('description_en', 'LIKE', "%{$words[0]}%")
                ->limit(3)
                ->get(['hs_code', 'description_en']);

            if ($previousItems->isNotEmpty()) {
                $hsCode = $previousItems->first()->hs_code;
                $tariffCode = TariffCode::where('hs_code', $hsCode)->first();
                
                if ($tariffCode) {
                    return [
                        'hs_code' => $tariffCode->hs_code,
                        'description' => $tariffCode->description_es ?: $tariffCode->description_en,
                        'source' => 'previous_calculations'
                    ];
                }
            }
        }

        if ($suggestions->isNotEmpty()) {
            $best = $suggestions->first();
            return [
                'hs_code' => $best->hs_code,
                'description' => $best->description_es ?: $best->description_en,
                'source' => 'database_match'
            ];
        }

        return null;
    }
}
