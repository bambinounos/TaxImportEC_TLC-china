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
                'category' => 'Cigarrillos',
                'rate' => 150.0,
                'year' => 2024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Cerveza',
                'rate' => 75.0,
                'year' => 2024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Bebidas alcohólicas',
                'rate' => 75.0,
                'year' => 2024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Vehículos',
                'rate' => 35.0,
                'year' => 2024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'Perfumes y aguas de tocador',
                'rate' => 20.0,
                'year' => 2024,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ice_taxes')->insert($iceTaxes);
        
        Log::info('IceTaxSeeder completed successfully with ' . count($iceTaxes) . ' entries');
    }
}
