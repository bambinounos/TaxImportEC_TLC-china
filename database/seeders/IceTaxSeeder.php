<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IceTaxSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        $iceTaxes = [
        ];
        
        DB::table('ice_taxes')->insert($iceTaxes);
        
        $this->command->info('IceTaxSeeder completed: ' . count($iceTaxes) . ' ICE tax entries');
    }
}
