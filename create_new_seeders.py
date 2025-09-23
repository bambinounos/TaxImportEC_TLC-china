#!/usr/bin/env python3
import json
import pandas as pd

def create_tariff_code_seeder():
    """Create new TariffCodeSeeder with hierarchy support"""
    print("=== Creating TariffCodeSeeder ===")
    
    with open('/home/ubuntu/parsed_tariff_hierarchy.json', 'r') as f:
        data = json.load(f)
    
    level_4 = data['level_4']
    level_6 = data['level_6'] 
    level_10 = data['level_10']
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class TariffCodeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Level 4 codes (category descriptions)
        $level4Codes = [
'''
    
    for code in level_4:
        seeder_content += f'''            [
                'hs_code' => '{code['hs_code']}',
                'description_en' => {repr(code['description'])},
                'description_es' => {repr(code['description'])},
                'base_tariff_rate' => null,
                'iva_rate' => 15.0,
                'unit' => null,
                'has_ice' => false,
                'is_active' => true,
                'hierarchy_level' => 4,
                'parent_code' => null,
                'order_number' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
'''
    
    seeder_content += '''        ];
        
        // Level 6 codes (subcategory codes)
        $level6Codes = [
'''
    
    for code in level_6:
        seeder_content += f'''            [
                'hs_code' => '{code['hs_code']}',
                'description_en' => {repr(code['description'])},
                'description_es' => {repr(code['description'])},
                'base_tariff_rate' => null,
                'iva_rate' => 15.0,
                'unit' => null,
                'has_ice' => false,
                'is_active' => true,
                'hierarchy_level' => 6,
                'parent_code' => {repr(code['parent_code'])},
                'order_number' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
'''
    
    seeder_content += '''        ];
        
        // Level 10 codes (actual tariff codes for calculations)
        $level10Codes = [
'''
    
    chunk_size = 500
    for i in range(0, len(level_10), chunk_size):
        chunk = level_10[i:i+chunk_size]
        for code in chunk:
            base_rate = code.get('base_tariff_rate', 0.0)
            seeder_content += f'''            [
                'hs_code' => '{code['hs_code']}',
                'description_en' => {repr(code['description'])},
                'description_es' => {repr(code['description'])},
                'base_tariff_rate' => {base_rate},
                'iva_rate' => 15.0,
                'unit' => null,
                'has_ice' => false,
                'is_active' => true,
                'hierarchy_level' => 10,
                'parent_code' => {repr(code['parent_code'])},
                'order_number' => {code['order_number']},
                'created_at' => $now,
                'updated_at' => $now,
            ],
'''
    
    seeder_content += '''        ];
        
        // Insert in chunks for performance
        DB::table('tariff_codes')->insert($level4Codes);
        DB::table('tariff_codes')->insert($level6Codes);
        
        // Insert level 10 codes in chunks
        $chunks = array_chunk($level10Codes, 500);
        foreach ($chunks as $chunk) {
            DB::table('tariff_codes')->insert($chunk);
        }
        
        $this->command->info('TariffCodeSeeder completed: ' . 
            count($level4Codes) . ' level-4, ' . 
            count($level6Codes) . ' level-6, ' . 
            count($level10Codes) . ' level-10 codes');
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TariffCodeSeeder.php', 'w') as f:
        f.write(seeder_content)
    
    print(f"Created TariffCodeSeeder with {len(level_4)} + {len(level_6)} + {len(level_10)} codes")

def create_tlc_schedule_seeder():
    """Create new TlcScheduleSeeder with all TLC categories"""
    print("=== Creating TlcScheduleSeeder ===")
    
    with open('/home/ubuntu/parsed_tariff_hierarchy.json', 'r') as f:
        data = json.load(f)
    
    level_10 = data['level_10']
    
    tlc_codes = [code for code in level_10 if code.get('tlc_category') and code['tlc_category'] != 'E']
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class TlcScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $startDate = '2020-01-01';
        
        $tlcSchedules = [
'''
    
    for code in tlc_codes:
        category = code['tlc_category']
        base_rate = code.get('base_tariff_rate', 0.0)
        yearly_rates = json.dumps(code.get('yearly_rates', {}))
        
        elimination_years = calculate_elimination_years(category)
        reduction_type = determine_reduction_type(category)
        
        seeder_content += f'''            [
                'hs_code' => '{code['hs_code']}',
                'country_code' => 'CHN',
                'base_rate' => {base_rate},
                'elimination_years' => {elimination_years},
                'start_date' => '$startDate',
                'reduction_type' => '{reduction_type}',
                'tlc_category' => '{category}',
                'yearly_rates' => '{yearly_rates}',
                'notes' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
'''
    
    seeder_content += '''        ];
        
        // Insert in chunks for performance
        $chunks = array_chunk($tlcSchedules, 500);
        foreach ($chunks as $chunk) {
            DB::table('tlc_schedules')->insert($chunk);
        }
        
        $this->command->info('TlcScheduleSeeder completed: ' . count($tlcSchedules) . ' TLC schedules');
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php', 'w') as f:
        f.write(seeder_content)
    
    print(f"Created TlcScheduleSeeder with {len(tlc_codes)} TLC schedules")

def calculate_elimination_years(category):
    """Calculate elimination years based on TLC category"""
    category_map = {
        'A0': 0,    # Immediate
        'A5': 5,    # 5 years
        'A10': 10,  # 10 years
        'A15': 15,  # 15 years
        'A15-3': 15, # 15 years (3 year delay)
        'A15-5': 15, # 15 years (5 year delay)
        'A17': 17,  # 17 years
        'A17-3': 17, # 17 years (3 year delay)
        'A17-5': 17, # 17 years (5 year delay)
        'A20': 20,  # 20 years
        'A20-3': 20, # 20 years (3 year delay)
        'A20-5': 20, # 20 years (5 year delay)
        'E': 0      # No elimination
    }
    return category_map.get(category, 0)

def determine_reduction_type(category):
    """Determine reduction type based on TLC category"""
    if category == 'A0':
        return 'immediate'
    elif category == 'E':
        return 'staged'  # No reduction
    elif '-' in category:
        return 'staged'  # Delayed start
    else:
        return 'linear'

def create_ice_tax_seeder():
    """Create updated IceTaxSeeder with new Excel data"""
    print("=== Creating IceTaxSeeder ===")
    
    with open('/home/ubuntu/parsed_ice_data.json', 'r') as f:
        ice_data = json.load(f)
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class IceTaxSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        $iceTaxes = [
'''
    
    for sheet_name, rows in ice_data.items():
        year = sheet_name
        for i, row in enumerate(rows):
            if len(row) >= 3:  # Ensure we have enough columns
                category = str(row[0]).strip() if pd.notna(row[0]) else f'Category_{i}'
                description = str(row[1]).strip() if pd.notna(row[1]) else 'No description'
                rate_raw = str(row[2]).strip() if pd.notna(row[2]) else '0'
                
                try:
                    import re
                    match = re.search(r'(\d+(?:\.\d+)?)', rate_raw)
                    rate = float(match.group(1)) if match else 0.0
                except:
                    rate = 0.0
                
                seeder_content += f'''            [
                'category' => {repr(category)},
                'description' => {repr(description)},
                'rate' => {rate},
                'year' => {year},
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
'''
    
    seeder_content += '''        ];
        
        DB::table('ice_taxes')->insert($iceTaxes);
        
        $this->command->info('IceTaxSeeder completed: ' . count($iceTaxes) . ' ICE tax entries');
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/IceTaxSeeder.php', 'w') as f:
        f.write(seeder_content)
    
    print("Created IceTaxSeeder with updated Excel data")

if __name__ == "__main__":
    create_tariff_code_seeder()
    create_tlc_schedule_seeder()
    create_ice_tax_seeder()
    print("=== All seeders created successfully ===")
