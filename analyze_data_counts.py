#!/usr/bin/env python3
import json
import csv
import re

def analyze_csv_file():
    """Analyze the original CSV file to count entries by hierarchy level"""
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    
    level_4_count = 0
    level_6_count = 0
    level_10_count = 0
    total_rows = 0
    
    try:
        with open(csv_path, 'r', encoding='utf-8') as file:
            reader = csv.reader(file)
            next(reader)  # Skip header
            
            for row in reader:
                if len(row) < 2:
                    continue
                    
                total_rows += 1
                hs_code = row[1].strip()
                
                if len(hs_code) == 4:
                    level_4_count += 1
                elif len(hs_code) == 6:
                    level_6_count += 1
                elif len(hs_code) == 10:
                    level_10_count += 1
                    
        print(f"CSV File Analysis:")
        print(f"Total rows (excluding header): {total_rows}")
        print(f"Level 4 codes: {level_4_count}")
        print(f"Level 6 codes: {level_6_count}")
        print(f"Level 10 codes: {level_10_count}")
        print(f"Sum of all levels: {level_4_count + level_6_count + level_10_count}")
        
    except Exception as e:
        print(f"Error analyzing CSV: {e}")

def analyze_parsed_json():
    """Analyze the parsed JSON data"""
    json_path = "/home/ubuntu/parsed_tariff_hierarchy.json"
    
    try:
        with open(json_path, 'r') as f:
            data = json.load(f)
            
        print(f"\nParsed JSON Analysis:")
        print(f"Level 4 codes: {len(data['level_4'])}")
        print(f"Level 6 codes: {len(data['level_6'])}")
        print(f"Level 10 codes: {len(data['level_10'])}")
        print(f"Total codes: {len(data['level_4']) + len(data['level_6']) + len(data['level_10'])}")
        
    except Exception as e:
        print(f"Error analyzing JSON: {e}")

def analyze_seeder_files():
    """Count entries in the seeder files"""
    
    tariff_seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TariffCodeSeeder.php"
    tariff_count = 0
    
    try:
        with open(tariff_seeder_path, 'r') as f:
            content = f.read()
            tariff_count = content.count("'hs_code' =>")
            
        print(f"\nSeeder Files Analysis:")
        print(f"TariffCodeSeeder entries: {tariff_count}")
        
    except Exception as e:
        print(f"Error analyzing TariffCodeSeeder: {e}")
    
    tlc_seeder_path = "/home/ubuntu/TaxImportEC_TLC-china-main/database/seeders/TlcScheduleSeeder.php"
    tlc_count = 0
    
    try:
        with open(tlc_seeder_path, 'r') as f:
            content = f.read()
            tlc_count = content.count("'hs_code' =>")
            
        print(f"TlcScheduleSeeder entries: {tlc_count}")
        
    except Exception as e:
        print(f"Error analyzing TlcScheduleSeeder: {e}")

if __name__ == "__main__":
    print("=== Data Count Analysis ===")
    analyze_csv_file()
    analyze_parsed_json()
    analyze_seeder_files()
