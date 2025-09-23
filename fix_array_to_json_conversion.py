#!/usr/bin/env python3
"""
Fix TlcScheduleSeeder array to JSON conversion issue.

The current seeder has PHP arrays like:
'yearly_rates' => ['year_1' => 0.0, 'year_2' => 0.0, ...]

But when using DB::table()->insert(), PostgreSQL JSON columns need JSON strings:
'yearly_rates' => json_encode(['year_1' => 0.0, 'year_2' => 0.0, ...])
"""

import re
import os

def fix_array_to_json_conversion():
    """Fix the array to JSON conversion issue in TlcScheduleSeeder.php"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    array_pattern = r"'yearly_rates' => \[([^\]]+)\]"
    matches = re.findall(array_pattern, content)
    print(f"Found {len(matches)} PHP array patterns")
    
    empty_array_pattern = r"'yearly_rates' => \[\]"
    empty_matches = re.findall(empty_array_pattern, content)
    print(f"Found {len(empty_matches)} empty array patterns")
    
    def convert_array_to_json_encode(match):
        """Convert PHP array to json_encode() call"""
        array_content = match.group(1)
        
        if not array_content.strip():
            return "'yearly_rates' => json_encode([])"
        
        return f"'yearly_rates' => json_encode([{array_content}])"
    
    print("Converting PHP arrays to json_encode() calls...")
    new_content = re.sub(array_pattern, convert_array_to_json_encode, content)
    
    new_content = re.sub(empty_array_pattern, "'yearly_rates' => json_encode([])", new_content)
    
    if new_content != content:
        print("Writing corrected seeder...")
        
        backup_path = seeder_path + ".array_backup"
        with open(backup_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Backup saved to: {backup_path}")
        
        with open(seeder_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print("✅ TlcScheduleSeeder.php corrected successfully!")
        
        print("\nSample of corrections made:")
        sample_old = re.search(r"'yearly_rates' => \[[^\]]+\]", content)
        sample_new = re.search(r"'yearly_rates' => json_encode\(\[[^\]]+\]\)", new_content)
        
        if sample_old and sample_new:
            print(f"OLD: {sample_old.group()[:100]}...")
            print(f"NEW: {sample_new.group()[:100]}...")
        
        return True
    else:
        print("No changes needed - file already in correct format")
        return False

if __name__ == "__main__":
    success = fix_array_to_json_conversion()
    if success:
        print("\n🎉 TlcScheduleSeeder array to JSON conversion fixed!")
        print("The seeder now uses json_encode() for PostgreSQL JSON columns.")
        print("This should resolve the 'Array to string conversion' error.")
    else:
        print("\n❌ No changes made to TlcScheduleSeeder")
