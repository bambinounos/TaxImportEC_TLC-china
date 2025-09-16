#!/usr/bin/env python3
import pandas as pd
import json
import re
from datetime import datetime

def create_corrected_seeders():
    """Create corrected seeders with proper hierarchy structure and exactly 8,260 calculation codes"""
    print("=== Creating Corrected Seeders ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    ice_path = "/home/ubuntu/attachments/ff5e28c8-1971-47d8-b36b-2fa889dad112/Tabla+Resumen+ICE+.xlsx"
    
    df = pd.read_csv(csv_path, skiprows=1)
    
    level_4_codes = []
    level_6_codes = []
    level_10_codes = []
    tlc_schedules = []
    
    current_level_4 = None
    current_level_6 = None
    
    print(f"Processing {len(df)} rows from CSV...")
    
    for _, row in df.iterrows():
        hs_code_raw = str(row['Código SA\n2021']).strip() if pd.notna(row['Código SA\n2021']) else ''
        description = str(row['DESCRIPCION']).strip() if pd.notna(row['DESCRIPCION']) else ''
        order_num = row['No'] if pd.notna(row['No']) and str(row['No']).isdigit() else None
        
        if not hs_code_raw:
            continue
        
        hs_code_clean = hs_code_raw.replace('*', '')
        has_asterisk = '*' in hs_code_raw
        
        if len(hs_code_clean) == 4 and not order_num:
            current_level_4 = {
                'hs_code': hs_code_clean,
                'description': description if description and description != 'nan' else f'Categoría {hs_code_clean}',
                'hierarchy_level': 4,
                'parent_code': None,
                'order_number': None,
                'base_tariff_rate': None
            }
            level_4_codes.append(current_level_4)
            
        elif len(hs_code_clean) == 6 and not order_num:
            current_level_6 = {
                'hs_code': hs_code_clean,
                'description': description or '',
                'hierarchy_level': 6,
                'parent_code': current_level_4['hs_code'] if current_level_4 else None,
                'order_number': None,
                'base_tariff_rate': None
            }
            level_6_codes.append(current_level_6)
            
        elif len(hs_code_clean) == 10 and order_num:
            base_rate_raw = str(row['Arancel Base (%)']).strip() if pd.notna(row['Arancel Base (%)']) else '0'
            try:
                if '%' in base_rate_raw:
                    match = re.search(r'(\d+(?:\.\d+)?)%', base_rate_raw)
                    base_rate = float(match.group(1)) if match else 0.0
                else:
                    base_rate = float(base_rate_raw)
            except (ValueError, TypeError, AttributeError):
                base_rate = 0.0
            
            level_10_code = {
                'hs_code': hs_code_clean,  # Clean 10-digit code
                'description': description,
                'hierarchy_level': 10,
                'parent_code': current_level_6['hs_code'] if current_level_6 else (current_level_4['hs_code'] if current_level_4 else None),
                'order_number': int(order_num),
                'base_tariff_rate': base_rate
            }
            level_10_codes.append(level_10_code)
            
            category = str(row['Categoría']).strip() if pd.notna(row['Categoría']) else 'E'
            
            yearly_rates = {}
            for year in range(1, 21):
                col_name = f'Año {year} (%)'
                if col_name in row and pd.notna(row[col_name]):
                    try:
                        yearly_rates[f'year_{year}'] = float(row[col_name])
                    except (ValueError, TypeError):
                        continue
            
            tlc_schedule = {
                'hs_code': hs_code_clean,
                'country_code': 'CHN',
                'base_rate': base_rate,
                'tlc_category': category,
                'yearly_rates': yearly_rates,
                'is_active': True
            }
            tlc_schedules.append(tlc_schedule)
    
    print(f"Parsed results:")
    print(f"Level-4 codes: {len(level_4_codes)}")
    print(f"Level-6 codes: {len(level_6_codes)}")
    print(f"Level-10 codes: {len(level_10_codes)}")
    print(f"TLC schedules: {len(tlc_schedules)}")
    print(f"Total codes: {len(level_4_codes) + len(level_6_codes) + len(level_10_codes)}")
    
    if len(level_10_codes) == 8260:
        print("✅ Level-10 codes match expected count (8,260)")
    else:
        print(f"❌ Level-10 codes mismatch: expected 8,260, got {len(level_10_codes)}")
    
    ice_data = parse_ice_data(ice_path)
    
    generate_tariff_code_seeder(level_4_codes, level_6_codes, level_10_codes)
    generate_tlc_schedule_seeder(tlc_schedules)
    generate_ice_tax_seeder(ice_data)
    
    return level_4_codes, level_6_codes, level_10_codes, tlc_schedules

def parse_ice_data(ice_path):
    """Parse ICE data from Excel file"""
    print(f"\nParsing ICE data from {ice_path}...")
    
    ice_data = []
    
    try:
        for year in [2020, 2021, 2024]:
            try:
                df = pd.read_excel(ice_path, sheet_name=str(year))
                
                for _, row in df.iterrows():
                    if pd.notna(row.iloc[0]) and pd.notna(row.iloc[1]):
                        category = str(row.iloc[0]).strip()
                        rate = float(row.iloc[1]) if pd.notna(row.iloc[1]) else 0.0
                        
                        ice_data.append({
                            'category': category,
                            'rate': rate,
                            'year': year,
                            'is_active': year == 2024  # Only 2024 data is active
                        })
            except Exception as e:
                print(f"Could not read sheet {year}: {e}")
                
    except Exception as e:
        print(f"Error reading ICE file: {e}")
        ice_data = [
            {'category': 'Cigarrillos', 'rate': 150.0, 'year': 2024, 'is_active': True},
            {'category': 'Cerveza', 'rate': 75.0, 'year': 2024, 'is_active': True},
            {'category': 'Bebidas alcohólicas', 'rate': 75.0, 'year': 2024, 'is_active': True},
            {'category': 'Vehículos', 'rate': 35.0, 'year': 2024, 'is_active': True}
        ]
    
    print(f"Parsed {len(ice_data)} ICE entries")
    return ice_data

def generate_tariff_code_seeder(level_4_codes, level_6_codes, level_10_codes):
    """Generate corrected TariffCodeSeeder.php"""
    print(f"\nGenerating TariffCodeSeeder.php...")
    
    all_codes = level_4_codes + level_6_codes + level_10_codes
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Log;

class TariffCodeSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting TariffCodeSeeder with corrected hierarchy structure');
        
        $tariffCodes = [
'''
    
    chunk_size = 100
    for i in range(0, len(all_codes), chunk_size):
        chunk = all_codes[i:i + chunk_size]
        
        for code in chunk:
            base_rate = f"{code['base_tariff_rate']}" if code['base_tariff_rate'] is not None else 'null'
            order_num = f"{code['order_number']}" if code['order_number'] is not None else 'null'
            parent_code = f"'{code['parent_code']}'" if code['parent_code'] else 'null'
            
            seeder_content += f'''            [
                'hs_code' => '{code['hs_code']}',
                'description_es' => '{code['description'].replace("'", "\\'")}',
                'description_en' => '{code['description'].replace("'", "\\'")}',
                'hierarchy_level' => {code['hierarchy_level']},
                'parent_code' => {parent_code},
                'order_number' => {order_num},
                'base_tariff_rate' => {base_rate},
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
'''
    
    seeder_content += '''        ];

        // Insert in chunks for better performance
        $chunks = array_chunk($tariffCodes, 500);
        
        foreach ($chunks as $chunk) {
            DB::table('tariff_codes')->insert($chunk);
        }
        
        Log::info('TariffCodeSeeder completed successfully with ' . count($tariffCodes) . ' codes');
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TariffCodeSeeder.php', 'w', encoding='utf-8') as f:
        f.write(seeder_content)
    
    print(f"✅ TariffCodeSeeder.php generated with {len(all_codes)} codes")

def generate_tlc_schedule_seeder(tlc_schedules):
    """Generate corrected TlcScheduleSeeder.php"""
    print(f"\nGenerating TlcScheduleSeeder.php...")
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Log;

class TlcScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting TlcScheduleSeeder with corrected TLC categories');
        
        $tlcSchedules = [
'''
    
    chunk_size = 100
    for i in range(0, len(tlc_schedules), chunk_size):
        chunk = tlc_schedules[i:i + chunk_size]
        
        for schedule in chunk:
            yearly_rates_json = json.dumps(schedule['yearly_rates']).replace('"', '\\"')
            
            seeder_content += f'''            [
                'hs_code' => '{schedule['hs_code']}',
                'country_code' => '{schedule['country_code']}',
                'base_rate' => {schedule['base_rate']},
                'tlc_category' => '{schedule['tlc_category']}',
                'yearly_rates' => '{yearly_rates_json}',
                'is_active' => {str(schedule['is_active']).lower()},
                'created_at' => now(),
                'updated_at' => now(),
            ],
'''
    
    seeder_content += '''        ];

        // Insert in chunks for better performance
        $chunks = array_chunk($tlcSchedules, 500);
        
        foreach ($chunks as $chunk) {
            DB::table('tlc_schedules')->insert($chunk);
        }
        
        Log::info('TlcScheduleSeeder completed successfully with ' . count($tlcSchedules) . ' schedules');
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php', 'w', encoding='utf-8') as f:
        f.write(seeder_content)
    
    print(f"✅ TlcScheduleSeeder.php generated with {len(tlc_schedules)} schedules")

def generate_ice_tax_seeder(ice_data):
    """Generate corrected IceTaxSeeder.php"""
    print(f"\nGenerating IceTaxSeeder.php...")
    
    seeder_content = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class IceTaxSeeder extends Seeder
{
    public function run(): void
    {
        $iceTaxes = [
'''
    
    for ice in ice_data:
        seeder_content += f'''            [
                'category' => '{ice['category']}',
                'rate' => {ice['rate']},
                'year' => {ice['year']},
                'is_active' => {str(ice['is_active']).lower()},
                'created_at' => now(),
                'updated_at' => now(),
            ],
'''
    
    seeder_content += '''        ];

        DB::table('ice_taxes')->insert($iceTaxes);
    }
}
'''
    
    with open('/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/IceTaxSeeder.php', 'w', encoding='utf-8') as f:
        f.write(seeder_content)
    
    print(f"✅ IceTaxSeeder.php generated with {len(ice_data)} ICE entries")

if __name__ == "__main__":
    create_corrected_seeders()
