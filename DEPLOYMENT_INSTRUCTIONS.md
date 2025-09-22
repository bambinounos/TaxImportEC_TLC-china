# CSV Import and CRUD Fix Deployment Instructions

## Problem Summary
- CSV import shows "24 productos creados" but items don't persist to database
- Manual item creation fails with NOT NULL constraint violations
- Root cause: Multiple item creation code paths missing required field initialization

## Deployment Steps

### 1. Update Production Server
```bash
cd /var/www/html/taximportec
git checkout main
git pull origin main
```

### 2. Verify All Fixes are Applied
Check these critical files contain the required field initializations:

**CsvImportService.php** line 180:
```php
'cif_value' => $totalFobValue,
```

**CalculationItemController.php** store method:
```php
$data['cif_value'] = $data['total_fob_value'];
```

**CalculationController.php** storeManual method:
```php
'cif_value' => $totalFobValue,
'total_cost' => $totalFobValue,
'unit_cost' => $productData['unit_price_fob'],
'sale_price' => $totalFobValue,
'unit_sale_price' => $productData['unit_price_fob'],
```

### 3. Clear Application Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 4. Check Application Logs
Monitor logs during testing to see debug information:
```bash
tail -f storage/logs/laravel.log
```

## Testing Checklist

### CSV Import Testing
- [ ] Import sample_products.csv
- [ ] Verify "24 productos creados" message appears WITHOUT errors
- [ ] Confirm items are visible in calculation view (not empty)
- [ ] Test export functionality produces files with actual data
- [ ] Check logs for "CSV Import: About to save item" debug messages

### Manual Single Item CRUD Testing
- [ ] Click "Agregar Item" button in calculation view
- [ ] Fill out item creation form and submit
- [ ] Verify item appears in the list immediately
- [ ] Test edit functionality (pencil icon)
- [ ] Test delete functionality (trash icon)

### Manual Bulk Item Creation Testing
- [ ] Use "Crear Cálculo Manual" functionality
- [ ] Add multiple products in the form
- [ ] Submit and verify all items are created
- [ ] Check that calculation shows all items correctly

### Expected Results
- No "null value in column cif_value" errors
- Items persist correctly to database
- All CRUD operations work smoothly
- Export files contain actual data, not empty
- Debug logs show proper field values before save

## Troubleshooting

### If Issues Persist
1. Check that all three item creation code paths are fixed:
   - CSV Import: `CsvImportService::mapCsvDataToItem()`
   - Manual Single Item: `CalculationItemController::store()`
   - Manual Bulk Items: `CalculationController::storeManual()`

2. Verify database migration has NOT NULL constraints:
   ```sql
   \d calculation_items
   ```

3. Check application logs for the new debug messages to identify which code path is failing

4. Clear all caches including OPcache if enabled:
   ```bash
   php artisan optimize:clear
   sudo service php8.1-fpm reload  # or your PHP version
   ```
