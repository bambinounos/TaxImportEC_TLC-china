# FODINFA Migration Guide

## Problem Summary
The FODINFA tax calculation (0.5% of CIF value) was implemented in the code but the database migration was not run on production, causing errors when trying to recalculate existing calculations.

## Error Details
```
SQLSTATE[42703]: Undefined column: 7 ERROR: column "fodinfa_rate" of relation "calculation_items" does not exist
```

## Solution Steps

### 1. Update Code (Already Done)
✅ SystemSetting import added to AdminController.php (PR #39)
✅ FODINFA calculation logic implemented (PR #38)
✅ FODINFA columns exist in migration file

### 2. Run Database Migration
The FODINFA columns are already defined in the migration file:
- `database/migrations/2024_01_01_000006_create_calculation_items_table.php` (lines 28-29)
- `fodinfa_rate` DECIMAL(8,4) DEFAULT 0.5
- `fodinfa_amount` DECIMAL(10,4) DEFAULT 0

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
