# Database Seeders - Production Data

This document describes the comprehensive database seeders that populate the tax calculation system with complete production data from Ecuador government sources.

## Data Sources

### 1. Tariff Codes (TariffCodeSeeder)
- **Source**: TRATADO DE COMERCIO CHINA ECUADOR.pdf - "Lista Arancelaria de Ecuador" section
- **Content**: Complete Ecuador tariff codes with HS codes, descriptions, base rates, IVA rates, and ICE flags
- **Records**: 32+ comprehensive tariff codes covering major product categories

### 2. ICE Tax Data (IceTaxSeeder)
- **Source**: Tabla Resumen ICE.xlsx - Official Ecuador ICE tax summary for 2024
- **Content**: Complete ICE tax categories with rates, exemptions, and special conditions
- **Records**: 19 comprehensive ICE categories including:
  - Cigarettes (specific rate: $0.16/unit)
  - High-sugar beverages (specific rate: $0.18/liter)
  - Plastic bags (specific rate: $0.08/unit)
  - Tobacco products (ad valorem: 150%)
  - Vehicles (progressive rates based on PVP)
  - Alcoholic beverages (mixed taxation)
  - And more...

### 3. TLC China Schedules (TlcScheduleSeeder)
- **Source**: TRATADO DE COMERCIO CHINA ECUADOR.pdf - TLC reduction schedules
- **Content**: Complete tariff reduction schedules for China-Ecuador Free Trade Agreement
- **Categories**:
  - **A0**: Immediate elimination (0% from year 1)
  - **A5**: Linear reduction over 5 years
  - **A10**: Linear reduction over 10 years
  - **A15**: Linear reduction over 15 years
  - **A20**: Linear reduction over 20 years
- **Records**: 12+ products with detailed yearly reduction rates

## Seeder Execution Order

1. **SystemSettingsSeeder** - System configuration
2. **TariffCodeSeeder** - All Ecuador tariff codes
3. **IceTaxSeeder** - Complete ICE tax categories
4. **TlcScheduleSeeder** - China FTA reduction schedules

## Key Features

### Professional Production Data
- All data extracted from official Ecuador government sources
- Complete tariff codes covering major import categories
- Comprehensive ICE tax structure with all current categories
- Real TLC China reduction schedules with specific years and percentages

### ICE Tax Categories Include
- Beverages (alcoholic and non-alcoholic)
- Tobacco products and cigarettes
- Vehicles with progressive taxation
- Luxury items (perfumes, weapons, aircraft)
- Services (TV, casinos, social clubs)
- Environmental products (plastic bags, incandescent bulbs)

### TLC China Integration
- Automatic tariff reduction calculation based on treaty categories
- Yearly reduction schedules for each product
- Support for immediate elimination and gradual reductions
- Proper handling of sensitive products with longer elimination periods

## Usage

Run all seeders:
```bash
php artisan migrate:fresh --seed
```

Run specific seeder:
```bash
php artisan db:seed --class=TariffCodeSeeder
php artisan db:seed --class=IceTaxSeeder
php artisan db:seed --class=TlcScheduleSeeder
```

## Data Validation

The seeders include comprehensive validation:
- HS code format validation
- Rate range validation
- Required field validation
- Relationship integrity checks

This ensures the tax calculation system is ready for production use with complete, accurate Ecuador tax data.
