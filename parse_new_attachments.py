#!/usr/bin/env python3
import pandas as pd
import json
import os
from docx import Document

def parse_anexo_tlc_csv():
    """Parse the new Anexo TLC-2 CSV with proper hierarchy handling"""
    print("=== Parsing Anexo TLC-2 CSV ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    df = pd.read_csv(csv_path, skiprows=1)
    
    level_4_codes = []  # Category descriptions
    level_6_codes = []  # Subcategory codes  
    level_10_codes = [] # Actual tariff codes for calculations
    
    current_level_4 = None
    current_level_6 = None
    
    for _, row in df.iterrows():
        hs_code = str(row['Código SA\n2021']).strip() if pd.notna(row['Código SA\n2021']) else ''
        description = str(row['DESCRIPCION']).strip() if pd.notna(row['DESCRIPCION']) else ''
        order_num = row['No'] if pd.notna(row['No']) and str(row['No']).isdigit() else None
        
        if not hs_code:
            continue
            
        if len(hs_code) == 4 and description and not order_num:
            current_level_4 = {
                'hs_code': hs_code,
                'description': description,
                'hierarchy_level': 4,
                'parent_code': None
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
                        # Skip non-numeric values like 'E' or other category codes
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
    
    print(f"Parsed {len(level_4_codes)} level-4 codes")
    print(f"Parsed {len(level_6_codes)} level-6 codes") 
    print(f"Parsed {len(level_10_codes)} level-10 codes")
    
    return level_4_codes, level_6_codes, level_10_codes

def parse_ice_excel():
    """Parse the updated ICE Excel file"""
    print("=== Parsing ICE Excel ===")
    
    excel_path = "/home/ubuntu/attachments/ff5e28c8-1971-47d8-b36b-2fa889dad112/Tabla+Resumen+ICE+.xlsx"
    
    ice_data = {}
    xl_file = pd.ExcelFile(excel_path)
    
    for sheet_name in xl_file.sheet_names:
        print(f"Processing sheet: {sheet_name}")
        df = pd.read_excel(excel_path, sheet_name=sheet_name, header=None)
        
        data_rows = []
        for i, row in df.iterrows():
            if i < 5:  # Skip first few header rows
                continue
            if pd.notna(row[0]) and str(row[0]).strip():
                data_rows.append(row)
        
        ice_data[sheet_name] = data_rows
    
    return ice_data

def calculate_tlc_elimination_years(category):
    """Calculate elimination years based on TLC category"""
    category_map = {
        'A0': 0,    # Immediate
        'A5': 5,    # 5 years
        'A10': 10,  # 10 years
        'A15': 15,  # 15 years
        'A15-3': 15, # 15 years (3 year delay)
        'A15-5': 15, # 15 years (5 year delay)
        'A17': 17,  # 17 years
        'A17-3': 17, # 17 years (3 year delay)
        'A17-5': 17, # 17 years (5 year delay)
        'A20': 20,  # 20 years
        'A20-3': 20, # 20 years (3 year delay)
        'A20-5': 20, # 20 years (5 year delay)
        'E': 0      # No elimination
    }
    return category_map.get(category, 0)

def determine_reduction_type(category):
    """Determine reduction type based on TLC category"""
    if category == 'A0':
        return 'immediate'
    elif category == 'E':
        return 'staged'  # No reduction
    elif '-' in category:
        return 'staged'  # Delayed start
    else:
        return 'linear'

if __name__ == "__main__":
    level_4, level_6, level_10 = parse_anexo_tlc_csv()
    ice_data = parse_ice_excel()
    
    with open('/home/ubuntu/parsed_tariff_hierarchy.json', 'w') as f:
        json.dump({
            'level_4': level_4,
            'level_6': level_6, 
            'level_10': level_10
        }, f, indent=2)
    
    with open('/home/ubuntu/parsed_ice_data.json', 'w') as f:
        json.dump(ice_data, f, indent=2, default=str)
    
    print("=== Parsing Complete ===")
    print(f"Data saved to parsed_tariff_hierarchy.json and parsed_ice_data.json")
