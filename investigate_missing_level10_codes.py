#!/usr/bin/env python3
import pandas as pd

def investigate_missing_level10_codes():
    """Deep investigation to find the missing 211 level-10 codes"""
    print("=== Investigating Missing Level-10 Codes ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    df = pd.read_csv(csv_path, skiprows=1)
    
    print(f"Total rows in CSV: {len(df)}")
    
    hs_codes = df['Código SA\n2021'].astype(str).str.strip()
    order_nums = df['No']
    descriptions = df['DESCRIPCION'].astype(str).str.strip()
    
    has_order = order_nums.notna() & (order_nums.astype(str).str.isdigit())
    print(f"Total rows with order numbers: {has_order.sum()}")
    
    print(f"\n=== HS Code Lengths for Rows WITH Order Numbers ===")
    rows_with_order = df[has_order]
    hs_with_order = rows_with_order['Código SA\n2021'].astype(str).str.strip()
    
    for length in [4, 6, 8, 10, 11, 12]:
        codes_of_length = hs_with_order[hs_with_order.str.len() == length]
        if len(codes_of_length) > 0:
            print(f"Length {length}: {len(codes_of_length)} codes")
            if length != 10:
                print(f"  Examples: {codes_of_length.head(3).tolist()}")
    
    non_10_with_order = has_order & (hs_codes.str.len() != 10)
    if non_10_with_order.sum() > 0:
        print(f"\n=== Non-10-digit codes WITH order numbers ({non_10_with_order.sum()} total) ===")
        examples = df[non_10_with_order][['No', 'Código SA\n2021', 'DESCRIPCION']].head(10)
        for _, row in examples.iterrows():
            hs_len = len(str(row['Código SA\n2021']).strip())
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']} (len={hs_len}), Desc: {row['DESCRIPCION'][:50]}...")
    
    level_10_no_order = (hs_codes.str.len() == 10) & ~has_order
    if level_10_no_order.sum() > 0:
        print(f"\n=== 10-digit codes WITHOUT order numbers ({level_10_no_order.sum()} total) ===")
        examples = df[level_10_no_order][['No', 'Código SA\n2021', 'DESCRIPCION']].head(10)
        for _, row in examples.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']}, Desc: {row['DESCRIPCION'][:50]}...")
    
    level_10_with_order = (hs_codes.str.len() == 10) & has_order
    print(f"\n=== Current Parsing Results ===")
    print(f"10-digit codes WITH order numbers: {level_10_with_order.sum()}")
    print(f"Expected total calculation codes: 8,260")
    print(f"Missing codes: {8260 - level_10_with_order.sum()}")
    
    print(f"\n=== Data Quality Check ===")
    
    empty_hs = (hs_codes == '') | (hs_codes == 'nan') | hs_codes.isna()
    print(f"Empty HS codes: {empty_hs.sum()}")
    
    numeric_hs = hs_codes.str.isdigit()
    non_numeric_hs = ~numeric_hs & ~empty_hs
    if non_numeric_hs.sum() > 0:
        print(f"Non-numeric HS codes: {non_numeric_hs.sum()}")
        examples = df[non_numeric_hs][['No', 'Código SA\n2021', 'DESCRIPCION']].head(5)
        for _, row in examples.iterrows():
            print(f"  Order: {row['No']}, HS: '{row['Código SA\n2021']}', Desc: {row['DESCRIPCION'][:50]}...")
    
    valid_orders = order_nums[has_order]
    duplicates = valid_orders[valid_orders.duplicated()]
    if len(duplicates) > 0:
        print(f"Duplicate order numbers: {len(duplicates)}")
        print(f"Examples: {duplicates.head(5).tolist()}")
    
    if has_order.sum() > 0:
        min_order = valid_orders.min()
        max_order = valid_orders.max()
        print(f"Order number range: {min_order} to {max_order}")
        
        expected_orders = set(range(int(min_order), int(max_order) + 1))
        actual_orders = set(valid_orders.astype(int))
        missing_orders = expected_orders - actual_orders
        if missing_orders:
            print(f"Missing order numbers: {len(missing_orders)} (e.g., {sorted(list(missing_orders))[:10]})")

def check_asterisk_codes():
    """Check if there are any codes with asterisks that might be causing issues"""
    print(f"\n=== Checking for Asterisk Codes ===")
    
    csv_path = "/home/ubuntu/attachments/33983b3c-c71e-48da-8b04-383fdd9f91f0/Anexo+tlc-2.csv"
    df = pd.read_csv(csv_path, skiprows=1)
    
    hs_codes = df['Código SA\n2021'].astype(str)
    
    has_asterisk = hs_codes.str.contains('*', na=False)
    if has_asterisk.sum() > 0:
        print(f"Codes with asterisks: {has_asterisk.sum()}")
        examples = df[has_asterisk][['No', 'Código SA\n2021', 'DESCRIPCION']].head(10)
        for _, row in examples.iterrows():
            print(f"  Order: {row['No']}, HS: {row['Código SA\n2021']}, Desc: {row['DESCRIPCION'][:50]}...")
    else:
        print("No codes with asterisks found")

if __name__ == "__main__":
    investigate_missing_level10_codes()
    check_asterisk_codes()
