#!/usr/bin/env python3
import pandas as pd
import json
import re

def corrected_parse_anexo_tlc_csv():
    """Corrected parsing logic that includes all codes the user expects"""
    print("=== Corrected Parsing Analysis ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    df = pd.read_csv(csv_path, skiprows=1)
    
    level_4_codes = []  # Category descriptions
    level_6_codes = []  # Subcategory codes  
    calculation_codes = [] # All codes with order numbers (10 and 11 digits)
    
    current_level_4 = None
    current_level_6 = None
    
    for _, row in df.iterrows():
        hs_code_raw = str(row['Código SA\n2021']).strip() if pd.notna(row['Código SA\n2021']) else ''
        description = str(row['DESCRIPCION']).strip() if pd.notna(row['DESCRIPCION']) else ''
        order_num = row['No'] if pd.notna(row['No']) and str(row['No']).isdigit() else None
        
        if not hs_code_raw:
            continue
        
        hs_code = hs_code_raw.replace('*', '')
        has_asterisk = '*' in hs_code_raw
            
        if len(hs_code) == 4 and not order_num:
            current_level_4 = {
                'hs_code': hs_code,
                'description': description if description and description != 'nan' else f'Categoría {hs_code}',
                'hierarchy_level': 4,
                'parent_code': None,
                'has_description': description and description != 'nan'
            }
            level_4_codes.append(current_level_4)
            
        elif len(hs_code) == 6 and not order_num:
            current_level_6 = {
                'hs_code': hs_code,
                'description': description or '',
                'hierarchy_level': 6,
                'parent_code': current_level_4['hs_code'] if current_level_4 else None
            }
            level_6_codes.append(current_level_6)
            
        elif order_num and len(hs_code) >= 10:
            base_rate_raw = str(row['Arancel Base (%)']).strip() if pd.notna(row['Arancel Base (%)']) else '0'
            try:
                if '%' in base_rate_raw:
                    match = re.search(r'(\d+(?:\.\d+)?)%', base_rate_raw)
                    base_rate = float(match.group(1)) if match else 0.0
                else:
                    base_rate = float(base_rate_raw)
            except (ValueError, TypeError, AttributeError):
                base_rate = 0.0
            
            category = str(row['Categoría']).strip() if pd.notna(row['Categoría']) else 'E'
            
            yearly_rates = {}
            for year in range(1, 21):
                col_name = f'Año {year} (%)'
                if col_name in row and pd.notna(row[col_name]):
                    try:
                        yearly_rates[f'year_{year}'] = float(row[col_name])
                    except (ValueError, TypeError):
                        continue
            
            calculation_code = {
                'hs_code': hs_code,  # Cleaned code (without asterisk)
                'hs_code_original': hs_code_raw,  # Original with asterisk if present
                'description': description,
                'hierarchy_level': 10,  # Treat all calculation codes as level 10
                'parent_code': current_level_6['hs_code'] if current_level_6 else (current_level_4['hs_code'] if current_level_4 else None),
                'order_number': int(order_num),
                'base_tariff_rate': base_rate,
                'tlc_category': category,
                'yearly_rates': yearly_rates,
                'has_asterisk': has_asterisk,
                'code_length': len(hs_code_raw)
            }
            calculation_codes.append(calculation_code)
    
    print(f"Corrected parsing results:")
    print(f"Level-4 codes: {len(level_4_codes)}")
    print(f"Level-6 codes: {len(level_6_codes)}")
    print(f"Calculation codes (all with order numbers): {len(calculation_codes)}")
    print(f"Total codes: {len(level_4_codes) + len(level_6_codes) + len(calculation_codes)}")
    
    codes_10_digit = [c for c in calculation_codes if c['code_length'] == 10]
    codes_11_digit = [c for c in calculation_codes if c['code_length'] == 11]
    
    print(f"\nCalculation code breakdown:")
    print(f"10-digit codes: {len(codes_10_digit)}")
    print(f"11-digit codes (with asterisks): {len(codes_11_digit)}")
    
    if codes_11_digit:
        print(f"\nExamples of 11-digit codes:")
        for i, code in enumerate(codes_11_digit[:5]):
            print(f"  {code['order_number']}: {code['hs_code_original']} -> {code['hs_code']} | {code['description'][:50]}...")
    
    level_4_with_desc = [c for c in level_4_codes if c['has_description']]
    level_4_without_desc = [c for c in level_4_codes if not c['has_description']]
    
    print(f"\nLevel-4 code breakdown:")
    print(f"With descriptions: {len(level_4_with_desc)}")
    print(f"Without descriptions: {len(level_4_without_desc)}")
    
    if level_4_without_desc:
        print(f"\nExamples of level-4 codes without descriptions:")
        for i, code in enumerate(level_4_without_desc[:5]):
            print(f"  {code['hs_code']}: {code['description']}")
    
    return level_4_codes, level_6_codes, calculation_codes

def compare_with_current_parsing():
    """Compare corrected parsing with current parsing results"""
    print("\n=== Comparison with Current Parsing ===")
    
    try:
        with open('/home/ubuntu/parsed_tariff_hierarchy.json', 'r') as f:
            current_data = json.load(f)
        
        current_level_4 = len(current_data['level_4'])
        current_level_6 = len(current_data['level_6'])
        current_level_10 = len(current_data['level_10'])
        current_total = current_level_4 + current_level_6 + current_level_10
        
        print(f"Current parsing results:")
        print(f"Level-4: {current_level_4}")
        print(f"Level-6: {current_level_6}")
        print(f"Level-10: {current_level_10}")
        print(f"Total: {current_total}")
        
    except Exception as e:
        print(f"Could not load current parsing results: {e}")

def generate_corrected_seeder_counts():
    """Generate what the seeder counts should be with corrected parsing"""
    level_4, level_6, calculation_codes = corrected_parse_anexo_tlc_csv()
    
    print(f"\n=== Corrected Seeder Counts ===")
    print(f"TariffCodeSeeder should contain:")
    print(f"  Level-4 codes: {len(level_4)}")
    print(f"  Level-6 codes: {len(level_6)}")
    print(f"  Calculation codes: {len(calculation_codes)}")
    print(f"  Total: {len(level_4) + len(level_6) + len(calculation_codes)}")
    
    print(f"\nTlcScheduleSeeder should contain:")
    print(f"  TLC schedules: {len(calculation_codes)} (one per calculation code)")
    
    print(f"\nUser expectation verification:")
    print(f"Expected calculation codes: 8,260")
    print(f"Actual calculation codes: {len(calculation_codes)}")
    print(f"Match: {'✅' if len(calculation_codes) == 8260 else '❌'}")

if __name__ == "__main__":
    compare_with_current_parsing()
    generate_corrected_seeder_counts()
