# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TaxImportEC is a Laravel 10 (PHP 8.1+, PostgreSQL) web app that calculates Ecuador import taxes with support for the China–Ecuador Free Trade Agreement (TLC). Users create "calculations", load items (manually or via CSV), and the system computes tariffs, FODINFA, ICE, and IVA per item, plus prorated costs and sale prices. All user-facing text (views, validation messages, errors) is in Spanish — keep it that way.

## Commands

```bash
composer install                 # dependencies (use --no-dev --optimize-autoloader in production)
php artisan serve                # dev server
php artisan migrate              # run migrations (--force needed in production)
php artisan db:seed              # seeds REAL production data (see below)
vendor/bin/pint                  # code style (Laravel Pint)
```

Cache clearing after config/route/view changes (order matters — clear before optionally re-caching):

```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear && php artisan optimize:clear
```

Notable absences:
- **No test suite.** There is no `tests/` directory or `phpunit.xml` (phpunit is in require-dev but unused).
- **No frontend build.** There is no `package.json`/Vite despite README/manuals mentioning `npm run build`. Bootstrap 5.1 and Font Awesome load from CDNs in `resources/views/layouts/`. Do not introduce a build step for asset changes.
- Database must be **PostgreSQL** — migrations use PostgreSQL-specific syntax (e.g. the users role enum migration alters a CHECK constraint, not a MySQL ENUM).

## Frontend Surfaces

Two distinct visual systems coexist:

- **Interior app** (`layouts/app.blade.php` + dashboard/calculations/admin views): Bootstrap 5.1 from CDN. Pagination renders Bootstrap 5 markup because `AppServiceProvider::boot()` calls `Paginator::useBootstrapFive()` — do not remove it, or pagination arrows render as giant unstyled Tailwind SVGs.
- **Public/guest pages** (`welcome.blade.php`, `layouts/guest.blade.php` used by `auth/login` and `auth/register`): fully self-contained "customs-document" identity — inline CSS, Google Fonts CDN (Archivo / IBM Plex Mono / Public Sans), **no Bootstrap**. Tokens: ink `#16233A`, paper `#F4EDDC`, stamp red `#BE3A26` (stamp + primary CTA only), kraft `#C49A55`. Extend this identity from `layouts/guest.blade.php` when restyling more guest pages; the interior app has not adopted it.

The calculations index supports search (`?search=`) matching calculation name/description or contained items (part_number, description_en/es, hs_code) via PostgreSQL `ilike` — see `CalculationController::index()`.

## Seeders Are Production Data

`db:seed` loads official Ecuador government data, not fake data: ~9,566 tariff codes (`TariffCodeSeeder`), ~2,708 China FTA reduction schedules (`TlcScheduleSeeder`), 19 ICE tax categories, and system settings. The `.backup` files in `database/seeders/` and the `fix_*.py` / `create_new_seeders.py` / `parse_new_attachments.py` scripts at repo root are one-off artifacts from generating those seeders — don't run or modify them for normal work.

## Tax Calculation Engine (the core of the app)

`app/Services/TaxCalculationService.php` runs the whole pipeline per item. The tax bases cascade — order is legally significant, do not reorder:

1. **CIF** = total FOB + prorated freight + insurance (FOB × `insurance_rate`%) + prorated pre-tax additional costs
2. **Tariff** = CIF × rate. With `use_tlc_china`, rate comes from `TariffCode::getEffectiveTariffRate('CHN', calculation_year)` → `TlcSchedule::getEffectiveRate()`, which checks `yearly_rates` JSON first, then falls back to immediate/linear reduction over `elimination_years`. Otherwise `base_tariff_rate`.
3. **FODINFA** = 0.5% of CIF (hardcoded)
4. **ICE** = computed on (CIF + tariff), only if `tariff_code.has_ice`, item is not `ice_exempt`, and an active `IceTax` matches the first 4 digits of the HS code. `IceTax` supports specific, ad-valorem, and mixed bases.
5. **IVA** = (CIF + tariff + FODINFA + ICE) × per-tariff-code `iva_rate` (default from `SystemSetting` `default_iva_rate`, 15%)
6. **Total cost** = CIF + taxes + prorated post-tax costs; **sale price** applies `profit_margin_percent` (item-level override falls back to calculation-level).

Proration of shared costs (freight, additional pre/post-tax) is by FOB value or by weight, per `calculation.proration_method`. Post-tax additional costs are stored as JSON arrays of `{amount, iva_applies}` objects (with backward compat for plain numbers) — see `Calculation::getTotalAdditionalCostsPostTax()`.

After changing item data or calculation costs, controllers call `TaxCalculationService::calculateTaxes()` to recompute everything; totals are denormalized onto the `calculations` row.

## Authorization Model

Three roles on `users.role`: `admin`, `user`, `tariff_viewer`.

- **Ownership/sharing** of calculations goes through `app/Policies/CalculationPolicy.php`. Calculations can be shared via `calculation_shares` (pivot with `view`/`edit` permission); only owner or admin can delete/share. Mutations are recorded to `calculation_audit_logs` via `AuditService`.
- **Role gates** are inline closure middleware in controller constructors (`AdminController` requires `isAdmin()`, `TariffCodeViewController` requires `canViewTariffs()` = admin or tariff_viewer). An `admin` middleware alias also exists in `app/Http/Kernel.php`. Follow the existing pattern of the controller you're editing.

## CSV Import

`app/Services/CsvImportService.php` implements **sync semantics** keyed on `part_number`: rows create or update items, and existing items missing from the CSV are flagged for deletion (user confirms). Required columns: `part_number`, `description_en`, `quantity`, `unit_price_fob`. Optional: `description_es`, `unit_weight`, `hs_code`, `ice_exempt`, `ice_exempt_reason`, `profit_margin_percent`. Invalid HS codes are auto-resolved to the nearest valid code with a warning rather than rejected. `sample_products.csv` at repo root shows the expected format.

## Deployment Docs

Production install/update procedures live in Spanish markdown files at repo root (`MANUAL_DE_INSTALACION.md`, `MANUAL_DE_ACTUALIZACION.md`, `INSTRUCCIONES.md`). If you change a deployment-relevant step (migrations, caching, dependencies), update `MANUAL_DE_ACTUALIZACION.md` to match.
