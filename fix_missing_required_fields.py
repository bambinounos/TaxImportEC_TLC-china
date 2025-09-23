#!/usr/bin/env python3
"""
Fix TlcScheduleSeeder missing required fields issue.

The seeder is missing required fields:
- elimination_years (integer, NOT NULL)
- start_date (date, NOT NULL)

These need to be added to all insert statements.
"""

import re
import os
from datetime import datetime

def fix_missing_required_fields():
    """Fix the missing required fields in TlcScheduleSeeder.php"""
    
    seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    
    if not os.path.exists(seeder_path):
        print(f"Error: {seeder_path} does not exist")
        return False
    
    print("Reading TlcScheduleSeeder.php...")
    
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters")
    
    insert_pattern = r"(\[\s*'hs_code' => [^}]+\],)"
    matches = re.findall(insert_pattern, content, re.DOTALL)
    print(f"Found {len(matches)} insert array patterns")
    
    if matches:
        print("\nSample insert structure:")
        sample = matches[0][:200] + "..." if len(matches[0]) > 200 else matches[0]
        print(sample)
    
    def add_missing_fields(match):
        """Add missing required fields to insert array"""
        array_content = match.group(1)
        
        if "'elimination_years'" in array_content:
            return array_content  # Already has the field
        
        closing_bracket_pos = array_content.rfind('],')
        if closing_bracket_pos == -1:
            closing_bracket_pos = array_content.rfind(']')
        
        if closing_bracket_pos != -1:
            before_closing = array_content[:closing_bracket_pos]
            after_closing = array_content[closing_bracket_pos:]
            
            missing_fields = """
                'elimination_years' => 20,
                'start_date' => '2020-01-01',"""
            
            new_array = before_closing + missing_fields + after_closing
            return new_array
        
        return array_content
    
    print("Adding missing required fields...")
    new_content = re.sub(insert_pattern, add_missing_fields, content, flags=re.DOTALL)
    
    if new_content != content:
        print("Writing corrected seeder...")
        
        backup_path = seeder_path + ".missing_fields_backup"
        with open(backup_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Backup saved to: {backup_path}")
        
        with open(seeder_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print("✅ TlcScheduleSeeder.php corrected successfully!")
        
        print("\nSample of corrections made:")
        new_matches = re.findall(insert_pattern, new_content, re.DOTALL)
        if new_matches:
            sample = new_matches[0][:300] + "..." if len(new_matches[0]) > 300 else new_matches[0]
            print(sample)
        
        return True
    else:
        print("No changes needed - fields already present or pattern not found")
        return False

if __name__ == "__main__":
    success = fix_missing_required_fields()
    if success:
        print("\n🎉 TlcScheduleSeeder missing fields issue fixed!")
        print("Added elimination_years and start_date to all insert arrays.")
        print("This should resolve the NOT NULL constraint violation.")
    else:
        print("\n❌ No changes made to TlcScheduleSeeder")
