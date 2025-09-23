#!/usr/bin/env python3
"""
Comprehensive fix for TlcScheduleSeeder based on Error-3.txt analysis.

The error shows that the seeder is still missing the elimination_years field
in the actual insert statement, even though my previous script claimed to fix it.
This suggests the fix wasn't applied correctly or there's a structural issue.
"""

import re
import os

def fix_tlc_seeder_comprehensive():
    """Comprehensively fix all issues in TlcScheduleSeeder.php"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    if "'elimination_years'" in content:
        print("✓ elimination_years field found in seeder")
    else:
        print("✗ elimination_years field NOT found in seeder")
    
    if "'start_date'" in content:
        print("✓ start_date field found in seeder")
    else:
        print("✗ start_date field NOT found in seeder")
    
    db_insert_pattern = r"DB::table\('tlc_schedules'\)->insert\(\[(.*?)\]\);"
    db_match = re.search(db_insert_pattern, content, re.DOTALL)
    
    if db_match:
        print("Found DB::table insert pattern")
        insert_data = db_match.group(1)
        
        sample_lines = insert_data[:1000]
        print(f"Sample insert data: {sample_lines}...")
        
        
        record_pattern = r"\[\s*'hs_code'\s*=>\s*'([^']+)',\s*'country_code'\s*=>\s*'([^']+)',\s*'base_rate'\s*=>\s*([^,]+),\s*'tlc_category'\s*=>\s*'([^']*)',\s*'yearly_rates'\s*=>\s*([^,]+),\s*'is_active'\s*=>\s*([^,]+),\s*'created_at'\s*=>\s*([^,]+),\s*'updated_at'\s*=>\s*([^,]+),?\s*\]"
        
        records = re.findall(record_pattern, insert_data, re.DOTALL)
        print(f"Found {len(records)} records to fix")
        
        if records:
            new_records = []
            for i, record in enumerate(records):
                hs_code, country_code, base_rate, tlc_category, yearly_rates, is_active, created_at, updated_at = record
                
                new_record = f"""[
                'hs_code' => '{hs_code}',
                'country_code' => '{country_code}',
                'base_rate' => {base_rate},
                'elimination_years' => 20,
                'start_date' => '2020-01-01',
                'reduction_type' => 'linear',
                'tlc_category' => '{tlc_category}',
                'yearly_rates' => {yearly_rates.strip()},
                'is_active' => {is_active},
                'created_at' => {created_at.strip()},
                'updated_at' => {updated_at.strip()},
            ]"""
                new_records.append(new_record)
                
                if i < 3:  # Show first 3 as sample
                    print(f"Sample record {i+1}: {new_record[:200]}...")
            
            new_seeder_content = f"""<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class TlcScheduleSeeder extends Seeder
{{
    public function run(): void
    {{
        DB::table('tlc_schedules')->insert([
{','.join(new_records)}
        ]);
    }}
}}
"""
            
            backup_path = seeder_path + ".comprehensive_backup"
            with open(backup_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Backup saved to: {backup_path}")
            
            with open(seeder_path, 'w', encoding='utf-8') as f:
                f.write(new_seeder_content)
            
            print("✅ TlcScheduleSeeder.php comprehensively fixed!")
            print(f"Rebuilt seeder with {len(records)} records")
            print("All required fields included: hs_code, country_code, base_rate, elimination_years, start_date, reduction_type, tlc_category, yearly_rates, is_active, created_at, updated_at")
            
            return True
    
    print("❌ Could not find or fix the DB::table insert pattern")
    return False

if __name__ == "__main__":
    success = fix_tlc_seeder_comprehensive()
    if success:
        print("\n🎉 TlcScheduleSeeder comprehensively fixed!")
        print("This should resolve all NOT NULL constraint violations.")
    else:
        print("\n❌ Failed to fix TlcScheduleSeeder comprehensively")
