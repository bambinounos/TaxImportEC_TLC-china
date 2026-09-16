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
            'Código Liberatorio (TPNG)',
            'ICE Exento',
            'Razón Exoneración ICE',
            'IVA Exento',
            'Razón Exoneración IVA',
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
            'Tasa FODINFA (%)',
            'FODINFA',
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
            'Margen Ganancia Individual (%)',
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
                $item->liberation_code ?: '',
                $item->ice_exempt ? 'Sí' : 'No',
                $item->ice_exempt_reason ?: '',
                $item->iva_exempt ? 'Sí' : 'No',
                $item->iva_exempt_reason ?: '',
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
                number_format($item->fodinfa_rate, 4),
                number_format($item->fodinfa_amount, 4),
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
                $item->profit_margin_percent !== null ? number_format($item->profit_margin_percent, 4) : '',
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
            'E1' => 'Código Liberatorio (TPNG)',
            'F1' => 'ICE Exento',
            'G1' => 'Razón Exoneración ICE',
            'H1' => 'IVA Exento',
            'I1' => 'Razón Exoneración IVA',
            'J1' => 'Peso Unitario',
            'K1' => 'Cantidad',
            'L1' => 'Precio Unit. FOB',
            'M1' => 'Valor Total FOB',
            'N1' => 'Flete Prorrateado',
            'O1' => 'Seguro Prorrateado',
            'P1' => 'Otros Costos Pre-Impuestos',
            'Q1' => 'Valor CIF',
            'R1' => 'Tasa Arancelaria (%)',
            'S1' => 'Arancel',
            'T1' => 'Tasa FODINFA (%)',
            'U1' => 'FODINFA',
            'V1' => 'Tasa ICE (%)',
            'W1' => 'ICE',
            'X1' => 'Tasa IVA (%)',
            'Y1' => 'IVA',
            'Z1' => 'Total Impuestos',
            'AA1' => 'Otros Costos Post-Impuestos',
            'AB1' => 'Costo Total',
            'AC1' => 'Costo Unitario',
            'AD1' => 'Precio de Venta',
            'AE1' => 'Precio Unit. Venta',
            'AF1' => 'Margen Ganancia Individual (%)',
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
        $sheet->getStyle('A1:AF1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($calculation->items as $item) {
            $sheet->setCellValue("A{$row}", $item->part_number ?: '');
            $sheet->setCellValue("B{$row}", $item->description_en);
            $sheet->setCellValue("C{$row}", $item->description_es ?: '');
            $sheet->setCellValue("D{$row}", $item->hs_code ?: '');
            $sheet->setCellValue("E{$row}", $item->liberation_code ?: '');
            $sheet->setCellValue("F{$row}", $item->ice_exempt ? 'Sí' : 'No');
            $sheet->setCellValue("G{$row}", $item->ice_exempt_reason ?: '');
            $sheet->setCellValue("H{$row}", $item->iva_exempt ? 'Sí' : 'No');
            $sheet->setCellValue("I{$row}", $item->iva_exempt_reason ?: '');
            $sheet->setCellValue("J{$row}", $item->unit_weight ?: '');
            $sheet->setCellValue("K{$row}", $item->quantity);
            $sheet->setCellValue("L{$row}", $item->unit_price_fob);
            $sheet->setCellValue("M{$row}", $item->total_fob_value);
            $sheet->setCellValue("N{$row}", $item->prorated_freight);
            $sheet->setCellValue("O{$row}", $item->prorated_insurance);
            $sheet->setCellValue("P{$row}", $item->prorated_additional_pre_tax);
            $sheet->setCellValue("Q{$row}", $item->cif_value);
            $sheet->setCellValue("R{$row}", $item->tariff_rate);
            $sheet->setCellValue("S{$row}", $item->tariff_amount);
            $sheet->setCellValue("T{$row}", $item->fodinfa_rate);
            $sheet->setCellValue("U{$row}", $item->fodinfa_amount);
            $sheet->setCellValue("V{$row}", $item->ice_rate);
            $sheet->setCellValue("W{$row}", $item->ice_amount);
            $sheet->setCellValue("X{$row}", $item->iva_rate);
            $sheet->setCellValue("Y{$row}", $item->iva_amount);
            $sheet->setCellValue("Z{$row}", $item->total_taxes);
            $sheet->setCellValue("AA{$row}", $item->prorated_additional_post_tax);
            $sheet->setCellValue("AB{$row}", $item->total_cost);
            $sheet->setCellValue("AC{$row}", $item->unit_cost);
            $sheet->setCellValue("AD{$row}", $item->sale_price);
            $sheet->setCellValue("AE{$row}", $item->unit_sale_price);
            $sheet->setCellValue("AF{$row}", $item->profit_margin_percent);

            $row++;
        }

        $columns = array_merge(range('A', 'Z'), ['AA', 'AB', 'AC', 'AD', 'AE', 'AF']);
        foreach ($columns as $column) {
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
