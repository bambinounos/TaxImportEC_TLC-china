<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IceTaxSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting IceTaxSeeder with corrected ICE data');
        
        $iceTaxes = [
            [
                'product_category' => 'Cigarrillos',
                'description' => 'Cigarrillos',
                'taxable_subjects' => 'Sujetos Pasivos: Productor, Importador',
                'taxable_event' => 'Productor: Venta (primera transferencia). Importador: Importación.',
                'base_type' => 'advalorem',
                'specific_base_description' => null,
                'specific_rate_usd' => null,
                'advalorem_rate_percent' => 150.00,
                'exemptions' => null,
                'reductions' => null,
                'benefits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category' => 'Cerveza',
                'description' => 'Cerveza Industrial por litro de alcohol puro',
                'taxable_subjects' => 'Sujetos Pasivos: Productor, Importador',
                'taxable_event' => 'Productor: Venta (primera transferencia). Importador: Importación.',
                'base_type' => 'advalorem',
                'specific_base_description' => null,
                'specific_rate_usd' => null,
                'advalorem_rate_percent' => 75.00,
                'exemptions' => null,
                'reductions' => null,
                'benefits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category' => 'Bebidas alcohólicas',
                'description' => 'Bebidas Alcohólicas',
                'taxable_subjects' => 'Sujetos Pasivos: Productor, Importador',
                'taxable_event' => 'Productor: Venta (primera transferencia). Importador: Importación.',
                'base_type' => 'advalorem',
                'specific_base_description' => null,
                'specific_rate_usd' => null,
                'advalorem_rate_percent' => 75.00,
                'exemptions' => 'Exención: bebidas alcohólicas elaboradas localmente con ingredientes nacionales de al menos el 70%.',
                'reductions' => 'Rebaja: hasta el 50% de la tarifa específica para bebidas con ingredientes nacionales.',
                'benefits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category' => 'Vehículos',
                'description' => 'Vehículos motorizados',
                'taxable_subjects' => 'Sujetos Pasivos: Importador, Ensamblador',
                'taxable_event' => 'Importación o primera venta local',
                'base_type' => 'advalorem',
                'specific_base_description' => null,
                'specific_rate_usd' => null,
                'advalorem_rate_percent' => 35.00,
                'exemptions' => 'Exención: Vehículos para transporte público y carga.',
                'reductions' => null,
                'benefits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category' => 'Perfumes y aguas de tocador',
                'description' => 'Perfumes y aguas de tocador',
                'taxable_subjects' => 'Sujetos Pasivos: Productor, Importador',
                'taxable_event' => 'Productor: Venta (primera transferencia). Importador: Importación.',
                'base_type' => 'advalorem',
                'specific_base_description' => null,
                'specific_rate_usd' => null,
                'advalorem_rate_percent' => 20.00,
                'exemptions' => null,
                'reductions' => null,
                'benefits' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ice_taxes')->insert($iceTaxes);
        
        Log::info('IceTaxSeeder completed successfully with ' . count($iceTaxes) . ' entries');
    }
}
