#!/usr/bin/env python3
"""
Fix TlcScheduleSeeder JSON escaping issue.

The seeder is currently generating escaped JSON strings like:
'yearly_rates' => '{\"year_1\": 0.0, \"year_2\": 0.0, ...}'

But Laravel expects PHP arrays for JSON casting:
'yearly_rates' => ['year_1' => 0.0, 'year_2' => 0.0, ...]
"""

import re
import os

def fix_tlc_seeder_json():
    """Fix the JSON escaping issue in TlcScheduleSeeder.php"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    escaped_json_pattern = r"'yearly_rates' => '\{\\\"year_\d+\\\""
    matches = re.findall(escaped_json_pattern, content)
    print(f"Found {len(matches)} escaped JSON patterns")
    
    json_pattern = r"'yearly_rates' => '\{([^}]+)\}'"
    
    def convert_json_to_array(match):
        """Convert escaped JSON string to PHP array format"""
        json_content = match.group(1)
        
        if not json_content.strip():
            return "'yearly_rates' => []"
        
        
        clean_content = json_content.replace('\\"', '"')
        
        php_pairs = []
        pair_pattern = r'"([^"]+)":\s*([^,}]+)'
        
        for pair_match in re.finditer(pair_pattern, clean_content):
            key = pair_match.group(1)
            value = pair_match.group(2).strip()
            php_pairs.append(f"'{key}' => {value}")
        
        if php_pairs:
            php_array = '[' + ', '.join(php_pairs) + ']'
            return f"'yearly_rates' => {php_array}"
        else:
            return "'yearly_rates' => []"
    
    print("Converting escaped JSON to PHP arrays...")
    new_content = re.sub(json_pattern, convert_json_to_array, content)
    
    new_matches = re.findall(escaped_json_pattern, new_content)
    print(f"Remaining escaped JSON patterns: {len(new_matches)}")
    
    empty_pattern = r"'yearly_rates' => '\{\}'"
    empty_matches = re.findall(empty_pattern, content)
    print(f"Found {len(empty_matches)} empty yearly_rates patterns")
    
    new_content = re.sub(empty_pattern, "'yearly_rates' => []", new_content)
    
    if new_content != content:
        print("Writing corrected seeder...")
        
        backup_path = seeder_path + ".backup"
        with open(backup_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Backup saved to: {backup_path}")
        
        with open(seeder_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print("✅ TlcScheduleSeeder.php corrected successfully!")
        
        print("\nSample of corrections made:")
        sample_old = re.search(r"'yearly_rates' => '\{[^}]+\}'", content)
        sample_new = re.search(r"'yearly_rates' => \[[^\]]+\]", new_content)
        
        if sample_old and sample_new:
            print(f"OLD: {sample_old.group()[:100]}...")
            print(f"NEW: {sample_new.group()[:100]}...")
        
        return True
    else:
        print("No changes needed - file already in correct format")
        return False

if __name__ == "__main__":
    success = fix_tlc_seeder_json()
    if success:
        print("\n🎉 TlcScheduleSeeder JSON issue fixed!")
        print("The seeder now uses proper PHP arrays instead of escaped JSON strings.")
        print("This should resolve the PostgreSQL JSON parsing error.")
    else:
        print("\n❌ No changes made to TlcScheduleSeeder")
