#!/usr/bin/env python3
import pandas as pd
import csv

def analyze_csv_detailed():
    """Detailed analysis of the CSV file to understand filtering issues"""
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    
    print("=== Detailed CSV Analysis ===")
    
    with open(csv_path, 'r', encoding='utf-8') as file:
        reader = csv.reader(file)
        header = next(reader)
        print(f"CSV Headers: {header}")
        
        print("\nFirst 20 data rows:")
        for i, row in enumerate(reader):
            if i >= 20:
                break
            if len(row) >= 3:
                order_num = row[0] if row[0].strip() else 'EMPTY'
                hs_code = row[1] if row[1].strip() else 'EMPTY'
                description = row[2][:50] + '...' if len(row[2]) > 50 else row[2]
                print(f"Row {i+2}: Order={order_num}, HS={hs_code}, Desc={description}")
    
    df = pd.read_csv(csv_path, skiprows=1)
    print(f"\nDataFrame shape: {df.shape}")
    print(f"Columns: {list(df.columns)}")
    
    hs_codes = df['Código SA\n2021'].astype(str).str.strip()
    order_nums = df['No']
    descriptions = df['DESCRIPCION'].astype(str).str.strip()
    
    print("\n=== HS Code Length Analysis ===")
    for length in [4, 6, 8, 10]:
        codes_of_length = hs_codes[hs_codes.str.len() == length]
        print(f"Length {length}: {len(codes_of_length)} codes")
        if len(codes_of_length) > 0:
            print(f"  Examples: {codes_of_length.head(3).tolist()}")
    
    print("\n=== Order Number Analysis ===")
    has_order = order_nums.notna() & (order_nums.astype(str).str.isdigit())
    no_order = order_nums.isna() | ~(order_nums.astype(str).str.isdigit())
    print(f"Rows with valid order numbers: {has_order.sum()}")
    print(f"Rows without valid order numbers: {no_order.sum()}")
    
    print("\n=== Current Parsing Logic Analysis ===")
    
    level_4_current = (hs_codes.str.len() == 4) & (descriptions != '') & no_order
    level_4_excluded_with_order = (hs_codes.str.len() == 4) & (descriptions != '') & has_order
    level_4_excluded_no_desc = (hs_codes.str.len() == 4) & (descriptions == '')
    
    print(f"Level 4 - Current logic includes: {level_4_current.sum()}")
    print(f"Level 4 - Excluded (has order): {level_4_excluded_with_order.sum()}")
    print(f"Level 4 - Excluded (no description): {level_4_excluded_no_desc.sum()}")
    
    level_6_current = (hs_codes.str.len() == 6) & no_order
    level_6_excluded = (hs_codes.str.len() == 6) & has_order
    
    print(f"Level 6 - Current logic includes: {level_6_current.sum()}")
    print(f"Level 6 - Excluded (has order): {level_6_excluded.sum()}")
    
    level_10_current = (hs_codes.str.len() == 10) & has_order
    level_10_excluded = (hs_codes.str.len() == 10) & no_order
    
    print(f"Level 10 - Current logic includes: {level_10_current.sum()}")
    print(f"Level 10 - Excluded (no order): {level_10_excluded.sum()}")
    
    print("\n=== Alternative Logic Analysis ===")
    alt_level_4 = hs_codes.str.len() == 4
    alt_level_6 = hs_codes.str.len() == 6
    alt_level_10 = hs_codes.str.len() == 10
    
    print(f"Alternative - All length 4: {alt_level_4.sum()}")
    print(f"Alternative - All length 6: {alt_level_6.sum()}")
    print(f"Alternative - All length 10: {alt_level_10.sum()}")
    print(f"Alternative - Total: {alt_level_4.sum() + alt_level_6.sum() + alt_level_10.sum()}")
    
    empty_hs = (hs_codes == '') | (hs_codes == 'nan')
    print(f"Empty HS codes: {empty_hs.sum()}")
    
    if level_10_excluded.sum() > 0:
        print(f"\nExamples of excluded level-10 codes (no order number):")
        excluded_examples = df[level_10_excluded][['No', 'Código SA\n2021', 'DESCRIPCION']].head(5)
        for _, row in excluded_examples.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']}, Desc: {row['DESCRIPCION'][:50]}...")

if __name__ == "__main__":
    analyze_csv_detailed()
