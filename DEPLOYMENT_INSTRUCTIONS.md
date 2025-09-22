# CSV Import and CRUD Fix Deployment Instructions

## Problem Summary
- CSV import shows "24 productos creados" but items don't persist to database
- Manual item creation fails with NOT NULL constraint violations
- Root cause: Required database fields not initialized during item creation

## Deployment Steps

### 1. Update Production Server
```bash
cd /var/www/html/taximportec
git checkout main
git pull origin main
```

### 2. Verify Fix is Applied
Check that `app/Services/CsvImportService.php` line 180 contains:
```php
'cif_value' => $totalFobValue,
```

### 3. Clear Application Cache (if applicable)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Testing Checklist

### CSV Import Testing
- [ ] Import sample_products.csv
- [ ] Verify "24 productos creados" message appears WITHOUT errors
- [ ] Confirm items are visible in calculation view (not empty)
- [ ] Test export functionality produces files with actual data

### Manual CRUD Testing
- [ ] Click "Agregar Item" button in calculation view
- [ ] Fill out item creation form and submit
- [ ] Verify item appears in the list immediately
- [ ] Test edit functionality (pencil icon)
- [ ] Test delete functionality (trash icon)

### Expected Results
- No "null value in column cif_value" errors
- Items persist correctly to database
- All CRUD operations work smoothly
- Export files contain actual data, not empty
