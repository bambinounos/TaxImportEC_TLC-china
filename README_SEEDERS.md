# Database Seeders - Complete Production Data

This document describes the comprehensive database seeders that populate the tax calculation system with complete production data from Ecuador government sources.


- **Source**: Anexo tlc.csv - Complete Ecuador tariff schedule from China-Ecuador FTA
- **Content**: Complete Ecuador tariff codes with HS codes, descriptions, base rates, IVA rates, and ICE flags
- **Records**: 9566 complete tariff codes covering all product categories

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

- **Source**: Anexo tlc.csv - Complete TLC reduction schedules from China-Ecuador FTA
- **Content**: Complete tariff reduction schedules for China-Ecuador Free Trade Agreement
- **Records**: 2708 TLC schedules with detailed yearly reduction rates
- **Categories**:
  - **A0**: Immediate elimination (0% from year 1) - 850 products
  - **A5**: Linear reduction over 5 years - 982 products
  - **A10**: Linear reduction over 10 years - 743 products
  - **A15**: Linear reduction over 15 years - 85 products
  - **A20**: Linear reduction over 20 years - 48 products


1. **SystemSettingsSeeder** - System configuration
2. **TariffCodeSeeder** - All Ecuador tariff codes (9566 codes)
3. **IceTaxSeeder** - Complete ICE tax categories (19 categories)
4. **TlcScheduleSeeder** - China FTA reduction schedules (2708 schedules)


- All data extracted from official Ecuador government sources
- Complete Ecuador tariff schedule from Anexo tlc.csv (9566 tariff codes)
- Comprehensive ICE tax structure with all current categories (19 categories)
- Real TLC China reduction schedules with specific years and percentages (2708 schedules)
- Automatic ICE flag detection based on product categories
- Enhanced descriptions for major product categories

- Beverages (alcoholic and non-alcoholic)
- Tobacco products and cigarettes
- Vehicles with progressive taxation
- Luxury items (perfumes, weapons, aircraft)
- Services (TV, casinos, social clubs)
- Environmental products (plastic bags, incandescent bulbs)

- Automatic tariff reduction calculation based on treaty categories
- Yearly reduction schedules for each product
- Support for immediate elimination and gradual reductions
- Proper handling of sensitive products with longer elimination periods


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


The seeders include comprehensive validation:
- HS code format validation
- Rate range validation
- Required field validation
- Relationship integrity checks

This ensures the tax calculation system is ready for production use with complete, accurate Ecuador tax data including:
- **9566 complete tariff codes** from the official Ecuador tariff schedule
- **2708 TLC China reduction schedules** with yearly elimination rates
- **19 comprehensive ICE tax categories** with specific and ad valorem rates
- **Automatic product categorization** with ICE flags and enhanced descriptions

The database will be populated with 9566 real tariff codes instead of sample data, making the system professional and production-ready with the complete Ecuador tariff schedule.


- **Total Tariff Codes**: 9566 (exceeds expected 8,580)
- **Total TLC Schedules**: 2708
- **TLC Category Distribution**:
  - A0 (Immediate): 850 products
  - A5 (5 years): 982 products
  - A10 (10 years): 743 products
  - A15 (15 years): 85 products
  - A20 (20 years): 48 products
- **ICE Categories**: 19 complete categories
- **Data Source**: Official Anexo tlc.csv from Ecuador government

The system now contains the complete Ecuador tariff schedule with all China FTA reductions, making it ready for professional production use.
