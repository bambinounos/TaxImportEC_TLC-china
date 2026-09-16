<?php

namespace Database\Seeders;

use App\Models\SenaeLiberation;
use Illuminate\Database\Seeder;

class SenaeLiberationSeeder extends Seeder
{
    public function run(): void
    {
        $liberations = [
            [
                'code' => '0672',
                'type' => 'TPNG',
                'description' => 'Maquinaria, implementos y partes agrícolas',
                'legal_basis' => 'LORTI Art. 55 Num. 5 / Decreto Ejecutivo 1232',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => false,
                'tariff_reduction_percent' => 0.00,
                'exempts_ice' => false,
                'exempts_fodinfa' => false,
                'is_active' => true,
            ],
            [
                'code' => '0411',
                'type' => 'TPNG',
                'description' => 'Grupos electrógenos y generadores eléctricos',
                'legal_basis' => 'Decreto Ejecutivo No. 411 / Tarifa 0% IVA',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => false,
                'tariff_reduction_percent' => 0.00,
                'exempts_ice' => false,
                'exempts_fodinfa' => false,
                'is_active' => true,
            ],
            [
                'code' => '0671',
                'type' => 'TPNG',
                'description' => 'Tractores de uso agrícola y sus repuestos',
                'legal_basis' => 'LORTI Art. 55 Num. 5',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => false,
                'tariff_reduction_percent' => 0.00,
                'exempts_ice' => false,
                'exempts_fodinfa' => false,
                'is_active' => true,
            ],
            [
                'code' => '0673',
                'type' => 'TPNG',
                'description' => 'Sistemas de riego y material agropecuario',
                'legal_basis' => 'LORTI Art. 55 Num. 5',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => false,
                'tariff_reduction_percent' => 0.00,
                'exempts_ice' => false,
                'exempts_fodinfa' => false,
                'is_active' => true,
            ],
            [
                'code' => '0001',
                'type' => 'TPNG',
                'description' => 'Importaciones del Sector Público y empresas públicas',
                'legal_basis' => 'COPCI Art. 125 lit. a',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => true,
                'tariff_reduction_percent' => 100.00,
                'exempts_ice' => false,
                'exempts_fodinfa' => false,
                'is_active' => true,
            ],
            [
                'code' => '0021',
                'type' => 'TPNG',
                'description' => 'Donaciones de socorro y asistencia humanitaria',
                'legal_basis' => 'COPCI Art. 125 lit. b',
                'exempts_iva' => true,
                'iva_reduction_percent' => 100.00,
                'exempts_tariff' => true,
                'tariff_reduction_percent' => 100.00,
                'exempts_ice' => true,
                'exempts_fodinfa' => true,
                'is_active' => true,
            ],
        ];

        foreach ($liberations as $liberation) {
            SenaeLiberation::updateOrCreate(
                ['code' => $liberation['code']],
                $liberation
            );
        }
    }
}
