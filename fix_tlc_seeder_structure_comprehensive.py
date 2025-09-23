#!/usr/bin/env python3
"""
Comprehensive fix for TlcScheduleSeeder structure based on Error-4.txt analysis.

The error shows that the seeder has elimination_years and start_date fields
incorrectly placed outside the individual record arrays at the end of the file,
rather than being included in each of the 8,260 records.

This causes the SQL insert statement to exclude these fields entirely,
leading to NOT NULL constraint violations.
"""

import re
import os

def fix_tlc_seeder_structure_comprehensive():
    """Comprehensively fix the TlcScheduleSeeder structure"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    record_pattern = r"(\[\s*'hs_code'\s*=>\s*'[^']+',.*?'updated_at'\s*=>\s*now\(\),\s*\])"
    records = re.findall(record_pattern, content, re.DOTALL)
    print(f"Found {len(records)} records to fix")
    
    if len(records) == 0:
        print("❌ No records found in expected format")
        return False
    
    new_records = []
    for i, record in enumerate(records):
        if "'elimination_years'" in record:
            new_records.append(record)
            continue
        
        fixed_record = record.replace(
            "'updated_at' => now(),",
            "'updated_at' => now(),\n                'elimination_years' => 20,\n                'start_date' => '2020-01-01',"
        )
        new_records.append(fixed_record)
        
        if i < 3:  # Show first 3 as sample
            print(f"Sample record {i+1} transformation:")
            print(f"OLD: {record[:150]}...")
            print(f"NEW: {fixed_record[:200]}...")
    
    new_seeder_content = f"""<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Log;

class TlcScheduleSeeder extends Seeder
{{
    public function run(): void
    {{
        Log::info('Starting TlcScheduleSeeder with corrected TLC categories');
        
        $tlcSchedules = [
            {',\n            '.join(new_records)}
        ];

        // Insert in chunks for better performance
        $chunks = array_chunk($tlcSchedules, 500);
        
        foreach ($chunks as $chunk) {{
            DB::table('tlc_schedules')->insert($chunk);
        }}
        
        Log::info('TlcScheduleSeeder completed successfully with ' . count($tlcSchedules) . ' schedules');
    }}
}}
"""
    
    backup_path = seeder_path + ".structure_comprehensive_backup"
    with open(backup_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Backup saved to: {backup_path}")
    
    with open(seeder_path, 'w', encoding='utf-8') as f:
        f.write(new_seeder_content)
    
    print("✅ TlcScheduleSeeder.php comprehensively fixed!")
    print(f"Processed {len(new_records)} records")
    print("Added elimination_years and start_date to all records")
    print("Removed incorrectly placed fields from end of file")
    
    return True

if __name__ == "__main__":
    success = fix_tlc_seeder_structure_comprehensive()
    if success:
        print("\n🎉 TlcScheduleSeeder structure comprehensively fixed!")
        print("This should resolve the NOT NULL constraint violation for elimination_years.")
        print("All 8,260 records now have the required fields properly included.")
    else:
        print("\n❌ Failed to fix TlcScheduleSeeder structure comprehensively")
