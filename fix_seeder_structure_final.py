#!/usr/bin/env python3
"""
Final fix for TlcScheduleSeeder structure based on Error-4.txt analysis.

The error shows the SQL insert is missing elimination_years and start_date entirely.
The insert columns are: base_rate, country_code, created_at, hs_code, is_active, tlc_category, updated_at, yearly_rates

We need to add elimination_years and start_date to the column list and values.
"""

import re
import os

def fix_seeder_structure_final():
    """Fix the TlcScheduleSeeder structure to include all required fields"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    
    insert_pattern = r"DB::table\('tlc_schedules'\)->insert\(\[(.*?)\]\);"
    match = re.search(insert_pattern, content, re.DOTALL)
    
    if not match:
        print("❌ Could not find DB::table insert pattern")
        return False
    
    print("✓ Found DB::table insert pattern")
    insert_data = match.group(1)
    
    
    record_pattern = r"\[\s*'hs_code'\s*=>\s*'([^']+)'[^}]+\]"
    records = re.findall(record_pattern, insert_data, re.DOTALL)
    print(f"Found {len(records)} records in seeder")
    
    if len(records) == 0:
        print("❌ No records found in expected format")
        return False
    
    full_record_pattern = r"(\[\s*'hs_code'\s*=>\s*'[^']+',.*?\])"
    full_records = re.findall(full_record_pattern, insert_data, re.DOTALL)
    print(f"Found {len(full_records)} full records")
    
    if len(full_records) == 0:
        print("❌ No full records found")
        return False
    
    new_records = []
    for i, record in enumerate(full_records):
        if "'elimination_years'" in record:
            new_records.append(record)
            continue
        
        closing_bracket_pos = record.rfind(']')
        if closing_bracket_pos == -1:
            new_records.append(record)
            continue
        
        before_closing = record[:closing_bracket_pos].rstrip()
        
        if not before_closing.endswith(','):
            before_closing += ','
        
        missing_fields = """
                'elimination_years' => 20,
                'start_date' => '2020-01-01',"""
        
        new_record = before_closing + missing_fields + "\n            ]"
        new_records.append(new_record)
        
        if i < 3:  # Show first 3 as sample
            print(f"Sample record {i+1} transformation:")
            print(f"OLD: {record[:100]}...")
            print(f"NEW: {new_record[:100]}...")
    
    new_insert_data = ',\n            '.join(new_records)
    
    new_seeder_content = f"""<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class TlcScheduleSeeder extends Seeder
{{
    public function run(): void
    {{
        DB::table('tlc_schedules')->insert([
            {new_insert_data}
        ]);
    }}
}}
"""
    
    backup_path = seeder_path + ".structure_final_backup"
    with open(backup_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Backup saved to: {backup_path}")
    
    with open(seeder_path, 'w', encoding='utf-8') as f:
        f.write(new_seeder_content)
    
    print("✅ TlcScheduleSeeder.php structure fixed!")
    print(f"Processed {len(new_records)} records")
    print("Added elimination_years and start_date to all records")
    
    return True

if __name__ == "__main__":
    success = fix_seeder_structure_final()
    if success:
        print("\n🎉 TlcScheduleSeeder structure completely fixed!")
        print("This should resolve the NOT NULL constraint violation for elimination_years.")
    else:
        print("\n❌ Failed to fix TlcScheduleSeeder structure")
