# Data Discrepancy Analysis Report

## Executive Summary
The user reported a discrepancy between expected and actual tariff code counts. Investigation reveals that the CSV contains exactly what the user expected, but our parsing logic has been too restrictive.

## Detailed Findings

### CSV File Structure
- **Total rows**: 14,870 (excluding header)
- **Rows with order numbers**: 8,260 (these are the calculation codes)
- **Rows without order numbers**: 6,610 (hierarchy structure codes)

### Hierarchy Level Breakdown

#### Level 4 Codes (Categories)
- **Total in CSV**: 1,222
- **With descriptions**: 1,027 ✅ (currently parsed)
- **Without descriptions**: 195 ❌ (excluded by current logic)
- **Issue**: 195 codes have empty descriptions ('nan') and are being excluded

#### Level 6 Codes (Subcategories)  
- **Total in CSV**: 5,387 ✅ (correctly parsed)
- **All have proper structure and no order numbers**

#### Level 10 Codes (Calculation Codes)
- **10-digit codes**: 8,049 ✅ (currently parsed)
- **11-digit codes with asterisks**: 211 ❌ (excluded by current logic)
- **Total calculation codes**: 8,260 (matches user expectation)

### Current vs Expected Counts

| Category | CSV Contains | Currently Parsed | User Expected | Status |
|----------|--------------|------------------|---------------|---------|
| Level 4  | 1,222        | 1,027           | 1,222         | ❌ Missing 195 |
| Level 6  | 5,387        | 5,387           | 5,387         | ✅ Correct |
| Level 10 | 8,049        | 8,049           | 8,260*        | ❌ Missing 211 |
| **Total** | **14,658**   | **14,463**      | **14,869**    | ❌ Missing 406 |

*User expected 8,260 calculation codes (including 11-digit codes with asterisks)

### Examples of Excluded Codes

#### Missing Level-4 Codes (No Description)
```
HS Code: 0410, Description: 'nan'
HS Code: 0501, Description: 'nan'  
HS Code: 0504, Description: 'nan'
```

#### Missing 11-Digit Calculation Codes
```
Order: 57, HS: 0203110000*, Description: "En canales o medias canales"
Order: 58, HS: 0203120000*, Description: "Piernas, paletas, y sus trozos"
Order: 59, HS: 0203191000*, Description: "Carne deshuesada"
```

## Root Cause Analysis

### Parsing Logic Issues

1. **Level-4 Filter Too Restrictive**
   ```python
   # Current logic excludes codes without descriptions
   if len(hs_code) == 4 and description and not order_num:
   ```
   **Fix**: Include all 4-digit codes regardless of description

2. **Level-10 Filter Too Restrictive**  
   ```python
   # Current logic only accepts exactly 10-digit codes
   elif len(hs_code) == 10 and order_num:
   ```
   **Fix**: Include all codes with order numbers (10 or 11 digits)

### TLC Schedule Count
- **Current**: 7,287 TLC schedules
- **Expected**: Should match calculation codes (8,260)
- **Issue**: Missing TLC data for 11-digit codes with asterisks

## Recommended Corrections

### 1. Update Parsing Logic
```python
# Include all level-4 codes (with or without descriptions)
if len(hs_code) == 4 and not order_num:
    # Include even if description is empty

# Include all calculation codes (10 or 11 digits with order numbers)  
elif order_num and len(hs_code) >= 10:
    # Handle both 10-digit and 11-digit codes with asterisks
```

### 2. Handle 11-Digit Codes
- Strip asterisks from HS codes for database storage
- Maintain original codes in description or notes field
- Ensure TLC schedules cover all calculation codes

### 3. Update Seeder Counts
- **TariffCodeSeeder**: 14,869 total codes (1,222 + 5,387 + 8,260)
- **TlcScheduleSeeder**: 8,260 schedules (one per calculation code)

## Data Integrity Verification

### Source Data Validation ✅
- CSV structure is correct and complete
- Order numbers properly assigned to calculation codes
- Hierarchy levels properly structured

### User Expectation Validation ✅  
- 8,260 calculation codes confirmed in CSV
- Additional hierarchy codes present as expected
- Total count matches user's understanding

## Conclusion

The data discrepancy is caused by overly restrictive parsing logic, not missing source data. The CSV file contains exactly what the user expected:

- **8,260 calculation codes** (including 11-digit codes with asterisks)
- **6,610 hierarchy codes** (levels 4 and 6)
- **Total: 14,870 codes** (matches CSV row count)

The parsing logic needs to be updated to include:
1. All level-4 codes (even without descriptions)
2. All codes with order numbers (10 and 11 digits)
3. Proper handling of asterisk notation in 11-digit codes

This will result in the correct counts that match the user's expectations and the source data.
