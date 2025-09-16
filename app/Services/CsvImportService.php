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
        'description_en',
        'quantity',
        'unit_price_fob',
    ];

    protected array $optionalColumns = [
        'part_number',
        'description_es',
        'unit_weight',
        'hs_code',
        'ice_exempt',
        'ice_exempt_reason',
    ];

    public function importFromCsv(UploadedFile $file, Calculation $calculation): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $csvData = $this->parseCsvFile($file);
            
            if (empty($csvData)) {
                $results['errors'][] = 'El archivo CSV está vacío o no se pudo leer correctamente.';
                return $results;
            }

            $this->validateCsvHeaders($csvData[0]);

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                if ($index === 0) continue;
                
                try {
                    $item = $this->createCalculationItem($row, $calculation, $index + 1);
                    if ($item) {
                        $results['success']++;
                        
                        if (empty($item->hs_code)) {
                            $suggestion = $this->suggestTariffCode($item);
                            if ($suggestion) {
                                $results['warnings'][] = "Fila " . ($index + 1) . ": Se sugiere el código arancelario {$suggestion['hs_code']} para '{$item->description_en}'";
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = "Error general: {$e->getMessage()}";
        }

        return $results;
    }

    protected function parseCsvFile(UploadedFile $file): array
    {
        $content = file_get_contents($file->getPathname());
        
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

    protected function createCalculationItem(array $row, Calculation $calculation, int $rowNumber): ?CalculationItem
    {
        $headers = array_map('strtolower', array_map('trim', $row));
        $data = array_combine($headers, $row);

        $requiredFields = [
            'description_en' => $data['description_en'] ?? '',
            'quantity' => (int) ($data['quantity'] ?? 0),
            'unit_price_fob' => (float) ($data['unit_price_fob'] ?? 0),
        ];

        if (empty($requiredFields['description_en'])) {
            throw new \Exception("Descripción en inglés es requerida");
        }

        if ($requiredFields['quantity'] <= 0) {
            throw new \Exception("Cantidad debe ser mayor a 0");
        }

        if ($requiredFields['unit_price_fob'] <= 0) {
            throw new \Exception("Precio unitario FOB debe ser mayor a 0");
        }

        $item = new CalculationItem([
            'calculation_id' => $calculation->id,
            'part_number' => $data['part_number'] ?? null,
            'description_en' => $requiredFields['description_en'],
            'description_es' => $data['description_es'] ?? null,
            'hs_code' => $this->cleanHsCode($data['hs_code'] ?? null),
            'ice_exempt' => filter_var($data['ice_exempt'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'ice_exempt_reason' => $data['ice_exempt_reason'] ?? null,
            'unit_weight' => !empty($data['unit_weight']) ? (float) $data['unit_weight'] : null,
            'quantity' => $requiredFields['quantity'],
            'unit_price_fob' => $requiredFields['unit_price_fob'],
            'total_fob_value' => $requiredFields['quantity'] * $requiredFields['unit_price_fob'],
        ]);

        if ($item->ice_exempt && empty($item->ice_exempt_reason)) {
            throw new \Exception("Razón de exoneración de ICE es requerida cuando el producto está exento");
        }

        if ($item->hs_code && !TariffCode::where('hs_code', $item->hs_code)->exists()) {
            throw new \Exception("Código arancelario '{$item->hs_code}' no existe en la base de datos");
        }

        $item->save();
        return $item;
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
