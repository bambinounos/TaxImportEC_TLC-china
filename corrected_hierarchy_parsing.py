#!/usr/bin/env python3
import pandas as pd
import json

def corrected_hierarchy_parsing():
    """Corrected parsing based on user clarification about hierarchy structure"""
    print("=== Corrected Hierarchy Parsing Analysis ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    df = pd.read_csv(csv_path, skiprows=1)
    
    level_4_codes = []  # Category descriptions (no order numbers)
    level_6_codes = []  # Subcategory codes (no order numbers)  
    level_10_codes = [] # Calculation codes (with order numbers)
    
    current_level_4 = None
    current_level_6 = None
    
    for _, row in df.iterrows():
        hs_code = str(row['Código SA\n2021']).strip() if pd.notna(row['Código SA\n2021']) else ''
        description = str(row['DESCRIPCION']).strip() if pd.notna(row['DESCRIPCION']) else ''
        order_num = row['No'] if pd.notna(row['No']) and str(row['No']).isdigit() else None
        
        if not hs_code:
            continue
            
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
            
        elif len(hs_code) == 10 and order_num:
            base_rate_raw = str(row['Arancel Base (%)']).strip() if pd.notna(row['Arancel Base (%)']) else '0'
            try:
                if '%' in base_rate_raw:
                    import re
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
            
            level_10_code = {
                'hs_code': hs_code,
                'description': description,
                'hierarchy_level': 10,
                'parent_code': current_level_6['hs_code'] if current_level_6 else (current_level_4['hs_code'] if current_level_4 else None),
                'order_number': int(order_num),
                'base_tariff_rate': base_rate,
                'tlc_category': category,
                'yearly_rates': yearly_rates
            }
            level_10_codes.append(level_10_code)
    
    print(f"Corrected parsing results:")
    print(f"Level-4 codes (categories): {len(level_4_codes)}")
    print(f"Level-6 codes (subcategories): {len(level_6_codes)}")
    print(f"Level-10 codes (calculations): {len(level_10_codes)}")
    print(f"Total codes: {len(level_4_codes) + len(level_6_codes) + len(level_10_codes)}")
    
    print(f"\nUser expectation verification:")
    print(f"Expected level-10 calculation codes: 8,260")
    print(f"Actual level-10 calculation codes: {len(level_10_codes)}")
    print(f"Match: {'✅' if len(level_10_codes) == 8260 else '❌'}")
    
    if len(level_10_codes) != 8260:
        print(f"Difference: {8260 - len(level_10_codes)} codes")
    
    level_4_with_desc = [c for c in level_4_codes if c['has_description']]
    level_4_without_desc = [c for c in level_4_codes if not c['has_description']]
    
    print(f"\nLevel-4 code breakdown:")
    print(f"With descriptions: {len(level_4_with_desc)}")
    print(f"Without descriptions: {len(level_4_without_desc)}")
    
    if level_4_without_desc:
        print(f"\nExamples of level-4 codes without descriptions:")
        for i, code in enumerate(level_4_without_desc[:5]):
            print(f"  {code['hs_code']}: {code['description']}")
    
    print(f"\nData validation:")
    
    level_4_with_order = df[(df['Código SA\n2021'].astype(str).str.len() == 4) & 
                           (df['No'].notna()) & 
                           (df['No'].astype(str).str.isdigit())]
    print(f"4-digit codes with order numbers (unexpected): {len(level_4_with_order)}")
    
    level_6_with_order = df[(df['Código SA\n2021'].astype(str).str.len() == 6) & 
                           (df['No'].notna()) & 
                           (df['No'].astype(str).str.isdigit())]
    print(f"6-digit codes with order numbers (unexpected): {len(level_6_with_order)}")
    
    level_10_no_order = df[(df['Código SA\n2021'].astype(str).str.len() == 10) & 
                          (df['No'].isna() | ~df['No'].astype(str).str.isdigit())]
    print(f"10-digit codes without order numbers (unexpected): {len(level_10_no_order)}")
    
    return level_4_codes, level_6_codes, level_10_codes

def compare_with_current_seeders():
    """Compare corrected parsing with current seeder counts"""
    print(f"\n=== Comparison with Current Seeders ===")
    
    level_4, level_6, level_10 = corrected_hierarchy_parsing()
    
    print(f"Corrected parsing totals:")
    print(f"  Level-4: {len(level_4)}")
    print(f"  Level-6: {len(level_6)}")
    print(f"  Level-10: {len(level_10)}")
    print(f"  Total: {len(level_4) + len(level_6) + len(level_10)}")
    
    print(f"\nCurrent seeder counts (from previous analysis):")
    print(f"  TariffCodeSeeder: 14,463 codes")
    print(f"  TlcScheduleSeeder: 7,287 schedules")
    
    print(f"\nExpected seeder counts with corrected parsing:")
    print(f"  TariffCodeSeeder: {len(level_4) + len(level_6) + len(level_10)} codes")
    print(f"  TlcScheduleSeeder: {len(level_10)} schedules (one per calculation code)")

if __name__ == "__main__":
    compare_with_current_seeders()
