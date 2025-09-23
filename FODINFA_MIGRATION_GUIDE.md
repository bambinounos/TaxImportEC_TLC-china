# FODINFA Migration Guide

## Problem Summary
The FODINFA tax calculation (0.5% of CIF value) was implemented in the code but the database columns were not added to existing production databases, causing errors when trying to recalculate existing calculations.

## Error Details
```
SQLSTATE[42703]: Undefined column: 7 ERROR: column "fodinfa_rate" of relation "calculation_items" does not exist
Class "App\Http\Controllers\SystemSetting" not found at AdminController.php:244
```

## Root Cause
The original `calculation_items` migration was already executed before FODINFA columns were added. Laravel won't re-run executed migrations, so a new migration is needed to add the missing columns.

## Solution Steps

### 1. Update Code (Already Done)
✅ SystemSetting import added to AdminController.php (PR #39)
✅ FODINFA calculation logic implemented (PR #38)
✅ New migration created to add FODINFA columns

### 2. Run Database Migration
A new migration file has been created specifically for adding FODINFA columns:
- `database/migrations/2024_09_23_000001_add_fodinfa_columns_to_calculation_items.php`
- Adds `fodinfa_rate` DECIMAL(8,4) DEFAULT 0.5
- Adds `fodinfa_amount` DECIMAL(10,4) DEFAULT 0

**Run this command on production:**
```bash
cd /var/www/html/taximportec
git checkout main
git pull origin main
php artisan migrate
php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear
```

### 3. Verify Functionality
After migration, test:
1. Navigate to `/admin/local-expenses-config` - should load without 500 error
2. Recalculate existing calculations - should include FODINFA values
3. Test mass IVA update functionality (12% to 15%)
4. Create new calculations - should show FODINFA column

## FODINFA Calculation Details
- **Rate**: Fixed 0.5% of CIF value
- **Formula**: FODINFA = CIF Value × 0.5%
- **Sequence**: Ad Valorem → FODINFA → ICE → IVA
- **IVA Base**: (CIF + Ad Valorem + FODINFA + ICE) × 15%

## Files Modified
- ✅ `app/Http/Controllers/AdminController.php` - SystemSetting import
- ✅ `app/Services/TaxCalculationService.php` - FODINFA calculation logic
- ✅ `database/migrations/2024_01_01_000006_create_calculation_items_table.php` - FODINFA columns
- ✅ `resources/views/calculations/show.blade.php` - FODINFA display
- ✅ `app/Services/CsvExportService.php` - FODINFA export
- ✅ `app/Models/CalculationItem.php` - FODINFA fields

## Expected Results
After running the migration:
- ✅ `/admin/local-expenses-config` loads without errors
- ✅ Existing calculations can be recalculated with FODINFA
- ✅ New calculations include FODINFA automatically
- ✅ Mass IVA update functionality works
- ✅ CSV import/export includes FODINFA columns
