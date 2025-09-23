## Problem Summary

This PR resolves the persistent CSV import and CRUD workflow issues where items report as created but don't save to the database due to NOT NULL constraint violations on `cif_value` and other required fields.

Despite previous fixes in PR #32 and PR #33 being deployed, the issue persisted because there was a **third item creation code path** that was missing the same field initialization.

## Root Cause Analysis

The application has **three different item creation code paths**:

1. **CSV Import** (`CsvImportService::mapCsvDataToItem()`) - ✅ Fixed in PR #32
2. **Manual Single Item** (`CalculationItemController::store()`) - ✅ Fixed in PR #33  
3. **Manual Bulk Items** (`CalculationController::storeManual()`) - ⚠️ **Was missing the fix**

The third path handles bulk manual item creation and was missing the initialization of required NOT NULL database fields, causing persistent constraint violations.

## Changes Made

### 1. Fixed Missing Field Initialization in CalculationController.storeManual()

Added initialization for all required NOT NULL fields:
- `cif_value` - Initialized to `total_fob_value`
- `total_cost` - Initialized to `total_fob_value`
- `unit_cost` - Initialized to `unit_price_fob`
- `sale_price` - Initialized to `total_fob_value`
- `unit_sale_price` - Initialized to `unit_price_fob`

### 2. Enhanced CSV Import Debugging

Added comprehensive logging to the CSV import process:
- Logs item attributes before save operation
- Shows exact `cif_value` and `total_fob_value` values
- Helps identify any remaining edge cases

### 3. Comprehensive Deployment Instructions

Created `DEPLOYMENT_INSTRUCTIONS_FINAL.md` with:
- Complete testing checklist for all three creation methods
- Troubleshooting steps for persistent issues
- Verification commands for all fixes

## Database Fields Requiring Initialization

Based on the migration file, these fields are NOT NULL without defaults:
- `total_fob_value` - Calculated from quantity × unit_price_fob
- `cif_value` - Initialized to total_fob_value (updated later by tax calculations)
- `total_cost` - Initialized to total_fob_value (updated later by tax calculations)
- `unit_cost` - Initialized to unit_price_fob (updated later by tax calculations)
- `sale_price` - Initialized to total_fob_value (updated later by tax calculations)
- `unit_sale_price` - Initialized to unit_price_fob (updated later by tax calculations)

## Testing

After deployment, test all three item creation methods:

1. **CSV Import**: Import sample_products.csv - should show "24 productos creados" without errors
2. **Manual Single Item**: Use "Agregar Item" button for individual item creation
3. **Manual Bulk Items**: Use "Crear Cálculo Manual" for bulk item creation

## Expected Results

- ✅ No "null value in column cif_value" errors
- ✅ Items persist correctly to database in ALL scenarios
- ✅ All CRUD operations work smoothly
- ✅ Export files contain actual data, not empty
- ✅ Debug logs show proper field values before save

## Link to Devin run
https://app.devin.ai/sessions/0146bbe7d72c444eb3c15e0d8dd9c651

## Requested by
@bambinounos
