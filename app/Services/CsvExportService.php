<?php

namespace App\Services;

use App\Models\Calculation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class CsvExportService
{
    public function exportCalculationToCsv(Calculation $calculation): string
    {
        $calculation->load('items.tariffCode');
        
        $headers = [
            'Número de Parte',
            'Descripción (EN)',
            'Descripción (ES)',
            'Código Arancelario',
            'Peso Unitario',
            'Cantidad',
            'Precio Unit. FOB',
            'Valor Total FOB',
            'Flete Prorrateado',
            'Seguro Prorrateado',
            'Otros Costos Pre-Impuestos',
            'Valor CIF',
            'Tasa Arancelaria (%)',
            'Arancel',
            'Tasa ICE (%)',
            'ICE',
            'Tasa IVA (%)',
            'IVA',
            'Total Impuestos',
            'Otros Costos Post-Impuestos',
            'Costo Total',
            'Costo Unitario',
            'Precio de Venta',
            'Precio Unit. Venta',
        ];

        $filename = storage_path('app/exports/calculation_' . $calculation->id . '_' . date('Y-m-d_H-i-s') . '.csv');
        
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');
        
        fputcsv($file, $headers);

        foreach ($calculation->items as $item) {
            $row = [
                $item->part_number ?: '',
                $item->description_en,
                $item->description_es ?: '',
                $item->hs_code ?: '',
                $item->unit_weight ?: '',
                $item->quantity,
                number_format($item->unit_price_fob, 4),
                number_format($item->total_fob_value, 2),
                number_format($item->prorated_freight, 4),
                number_format($item->prorated_insurance, 4),
                number_format($item->prorated_additional_pre_tax, 4),
                number_format($item->cif_value, 2),
                number_format($item->tariff_rate, 4),
                number_format($item->tariff_amount, 4),
                number_format($item->ice_rate, 4),
                number_format($item->ice_amount, 4),
                number_format($item->iva_rate, 4),
                number_format($item->iva_amount, 4),
                number_format($item->total_taxes, 4),
                number_format($item->prorated_additional_post_tax, 4),
                number_format($item->total_cost, 2),
                number_format($item->unit_cost, 4),
                number_format($item->sale_price, 2),
                number_format($item->unit_sale_price, 4),
            ];
            
            fputcsv($file, $row);
        }

        fclose($file);
        
        return $filename;
    }

    public function exportCalculationToExcel(Calculation $calculation): string
    {
        $calculation->load('items.tariffCode');
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Cálculo de Impuestos');

        $headers = [
            'A1' => 'Número de Parte',
            'B1' => 'Descripción (EN)',
            'C1' => 'Descripción (ES)',
            'D1' => 'Código Arancelario',
            'E1' => 'Peso Unitario',
            'F1' => 'Cantidad',
            'G1' => 'Precio Unit. FOB',
            'H1' => 'Valor Total FOB',
            'I1' => 'Flete Prorrateado',
            'J1' => 'Seguro Prorrateado',
            'K1' => 'Otros Costos Pre-Impuestos',
            'L1' => 'Valor CIF',
            'M1' => 'Tasa Arancelaria (%)',
            'N1' => 'Arancel',
            'O1' => 'Tasa ICE (%)',
            'P1' => 'ICE',
            'Q1' => 'Tasa IVA (%)',
            'R1' => 'IVA',
            'S1' => 'Total Impuestos',
            'T1' => 'Otros Costos Post-Impuestos',
            'U1' => 'Costo Total',
            'V1' => 'Costo Unitario',
            'W1' => 'Precio de Venta',
            'X1' => 'Precio Unit. Venta',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ];
        $sheet->getStyle('A1:X1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($calculation->items as $item) {
            $sheet->setCellValue("A{$row}", $item->part_number ?: '');
            $sheet->setCellValue("B{$row}", $item->description_en);
            $sheet->setCellValue("C{$row}", $item->description_es ?: '');
            $sheet->setCellValue("D{$row}", $item->hs_code ?: '');
            $sheet->setCellValue("E{$row}", $item->unit_weight ?: '');
            $sheet->setCellValue("F{$row}", $item->quantity);
            $sheet->setCellValue("G{$row}", $item->unit_price_fob);
            $sheet->setCellValue("H{$row}", $item->total_fob_value);
            $sheet->setCellValue("I{$row}", $item->prorated_freight);
            $sheet->setCellValue("J{$row}", $item->prorated_insurance);
            $sheet->setCellValue("K{$row}", $item->prorated_additional_pre_tax);
            $sheet->setCellValue("L{$row}", $item->cif_value);
            $sheet->setCellValue("M{$row}", $item->tariff_rate);
            $sheet->setCellValue("N{$row}", $item->tariff_amount);
            $sheet->setCellValue("O{$row}", $item->ice_rate);
            $sheet->setCellValue("P{$row}", $item->ice_amount);
            $sheet->setCellValue("Q{$row}", $item->iva_rate);
            $sheet->setCellValue("R{$row}", $item->iva_amount);
            $sheet->setCellValue("S{$row}", $item->total_taxes);
            $sheet->setCellValue("T{$row}", $item->prorated_additional_post_tax);
            $sheet->setCellValue("U{$row}", $item->total_cost);
            $sheet->setCellValue("V{$row}", $item->unit_cost);
            $sheet->setCellValue("W{$row}", $item->sale_price);
            $sheet->setCellValue("X{$row}", $item->unit_sale_price);
            
            $row++;
        }

        foreach (range('A', 'X') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = storage_path('app/exports/calculation_' . $calculation->id . '_' . date('Y-m-d_H-i-s') . '.xlsx');
        
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        
        return $filename;
    }
}
