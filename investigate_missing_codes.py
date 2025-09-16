#!/usr/bin/env python3
import pandas as pd
import csv

def investigate_missing_level_4():
    """Investigate why 195 level-4 codes are missing during parsing"""
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    
    print("=== Investigating Missing Level-4 Codes ===")
    
    df = pd.read_csv(csv_path, skiprows=1)
    
    hs_codes = df['Código SA\n2021'].astype(str).str.strip()
    descriptions = df['DESCRIPCION'].astype(str).str.strip()
    order_nums = df['No']
    
    level_4_all = hs_codes.str.len() == 4
    level_4_with_desc = level_4_all & (descriptions != '') & (descriptions != 'nan')
    level_4_no_desc = level_4_all & ((descriptions == '') | (descriptions == 'nan'))
    level_4_with_order = level_4_all & order_nums.notna() & (order_nums.astype(str).str.isdigit())
    level_4_no_order = level_4_all & (order_nums.isna() | ~(order_nums.astype(str).str.isdigit()))
    
    print(f"Total level-4 codes in CSV: {level_4_all.sum()}")
    print(f"Level-4 with description: {level_4_with_desc.sum()}")
    print(f"Level-4 without description: {level_4_no_desc.sum()}")
    print(f"Level-4 with order number: {level_4_with_order.sum()}")
    print(f"Level-4 without order number: {level_4_no_order.sum()}")
    
    current_logic = level_4_all & (descriptions != '') & (descriptions != 'nan') & (order_nums.isna() | ~(order_nums.astype(str).str.isdigit()))
    print(f"Current parsing logic captures: {current_logic.sum()}")
    
    excluded = level_4_all & ~current_logic
    if excluded.sum() > 0:
        print(f"\nExamples of excluded level-4 codes ({excluded.sum()} total):")
        excluded_df = df[excluded][['No', 'Código SA\n2021', 'DESCRIPCION']].head(10)
        for _, row in excluded_df.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']}, Desc: '{row['DESCRIPCION']}'")

def investigate_level_10_count():
    """Investigate the level-10 code count discrepancy"""
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    
    print("\n=== Investigating Level-10 Code Count ===")
    
    df = pd.read_csv(csv_path, skiprows=1)
    hs_codes = df['Código SA\n2021'].astype(str).str.strip()
    order_nums = df['No']
    
    level_10_all = hs_codes.str.len() == 10
    print(f"Total level-10 codes in CSV: {level_10_all.sum()}")
    
    has_order = order_nums.notna() & (order_nums.astype(str).str.isdigit())
    level_10_with_order = level_10_all & has_order
    print(f"Level-10 with order numbers: {level_10_with_order.sum()}")
    
    level_10_no_order = level_10_all & ~has_order
    print(f"Level-10 without order numbers: {level_10_no_order.sum()}")
    
    if level_10_no_order.sum() > 0:
        print(f"\nExamples of level-10 codes without order numbers:")
        no_order_df = df[level_10_no_order][['No', 'Código SA\n2021', 'DESCRIPCION']].head(5)
        for _, row in no_order_df.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']}, Desc: {row['DESCRIPCION'][:50]}...")
    
    level_10_codes = hs_codes[level_10_all]
    duplicates = level_10_codes[level_10_codes.duplicated()]
    if len(duplicates) > 0:
        print(f"\nDuplicate level-10 codes found: {len(duplicates)}")
        print(f"Examples: {duplicates.head(5).tolist()}")
    else:
        print(f"\nNo duplicate level-10 codes found")

def check_user_expectation():
    """Check if user's expectation of 8,260 codes is based on a different source"""
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    
    print("\n=== Checking User Expectation ===")
    
    df = pd.read_csv(csv_path, skiprows=1)
    order_nums = df['No']
    has_order = order_nums.notna() & (order_nums.astype(str).str.isdigit())
    
    print(f"Total rows with order numbers: {has_order.sum()}")
    print(f"User expected: 8,260 level-10 codes")
    print(f"Difference: {8260 - has_order.sum()}")
    
    hs_codes = df['Código SA\n2021'].astype(str).str.strip()
    non_10_with_order = has_order & (hs_codes.str.len() != 10)
    
    if non_10_with_order.sum() > 0:
        print(f"\nNon-10-digit codes with order numbers: {non_10_with_order.sum()}")
        examples = df[non_10_with_order][['No', 'Código SA\n2021', 'DESCRIPCION']].head(5)
        for _, row in examples.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']} (len={len(str(row['Código SA\n2021']))}), Desc: {row['DESCRIPCION'][:50]}...")

if __name__ == "__main__":
    investigate_missing_level_4()
    investigate_level_10_count()
    check_user_expectation()
