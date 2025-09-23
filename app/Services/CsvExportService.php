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
            'ICE Exento',
            'Razón Exoneración ICE',
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
                $item->ice_exempt ? 'Sí' : 'No',
                $item->ice_exempt_reason ?: '',
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
            'E1' => 'ICE Exento',
            'F1' => 'Razón Exoneración ICE',
            'G1' => 'Peso Unitario',
            'H1' => 'Cantidad',
            'I1' => 'Precio Unit. FOB',
            'J1' => 'Valor Total FOB',
            'K1' => 'Flete Prorrateado',
            'L1' => 'Seguro Prorrateado',
            'M1' => 'Otros Costos Pre-Impuestos',
            'N1' => 'Valor CIF',
            'O1' => 'Tasa Arancelaria (%)',
            'P1' => 'Arancel',
            'Q1' => 'Tasa FODINFA (%)',
            'R1' => 'FODINFA',
            'S1' => 'Tasa ICE (%)',
            'T1' => 'ICE',
            'U1' => 'Tasa IVA (%)',
            'V1' => 'IVA',
            'W1' => 'Total Impuestos',
            'X1' => 'Otros Costos Post-Impuestos',
            'Y1' => 'Costo Total',
            'Z1' => 'Costo Unitario',
            'AA1' => 'Precio de Venta',
            'AB1' => 'Precio Unit. Venta',
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
        $sheet->getStyle('A1:AB1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($calculation->items as $item) {
            $sheet->setCellValue("A{$row}", $item->part_number ?: '');
            $sheet->setCellValue("B{$row}", $item->description_en);
            $sheet->setCellValue("C{$row}", $item->description_es ?: '');
            $sheet->setCellValue("D{$row}", $item->hs_code ?: '');
            $sheet->setCellValue("E{$row}", $item->ice_exempt ? 'Sí' : 'No');
            $sheet->setCellValue("F{$row}", $item->ice_exempt_reason ?: '');
            $sheet->setCellValue("G{$row}", $item->unit_weight ?: '');
            $sheet->setCellValue("H{$row}", $item->quantity);
            $sheet->setCellValue("I{$row}", $item->unit_price_fob);
            $sheet->setCellValue("J{$row}", $item->total_fob_value);
            $sheet->setCellValue("K{$row}", $item->prorated_freight);
            $sheet->setCellValue("L{$row}", $item->prorated_insurance);
            $sheet->setCellValue("M{$row}", $item->prorated_additional_pre_tax);
            $sheet->setCellValue("N{$row}", $item->cif_value);
            $sheet->setCellValue("O{$row}", $item->tariff_rate);
            $sheet->setCellValue("P{$row}", $item->tariff_amount);
            $sheet->setCellValue("Q{$row}", $item->fodinfa_rate);
            $sheet->setCellValue("R{$row}", $item->fodinfa_amount);
            $sheet->setCellValue("S{$row}", $item->ice_rate);
            $sheet->setCellValue("T{$row}", $item->ice_amount);
            $sheet->setCellValue("U{$row}", $item->iva_rate);
            $sheet->setCellValue("V{$row}", $item->iva_amount);
            $sheet->setCellValue("W{$row}", $item->total_taxes);
            $sheet->setCellValue("X{$row}", $item->prorated_additional_post_tax);
            $sheet->setCellValue("Y{$row}", $item->total_cost);
            $sheet->setCellValue("Z{$row}", $item->unit_cost);
            $sheet->setCellValue("AA{$row}", $item->sale_price);
            $sheet->setCellValue("AB{$row}", $item->unit_sale_price);
            
            $row++;
        }

        foreach (range('A', 'Z') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getColumnDimension('AA')->setAutoSize(true);
        $sheet->getColumnDimension('AB')->setAutoSize(true);

        $filename = storage_path('app/exports/calculation_' . $calculation->id . '_' . date('Y-m-d_H-i-s') . '.xlsx');
        
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        
        return $filename;
    }
}
