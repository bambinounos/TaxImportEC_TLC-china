# Manual de Actualización de Base de Datos - TaxImportEC TLC China

## Resumen de Cambios

Este manual documenta la actualización completa de la estructura de base de datos para corregir errores críticos identificados en la estructura de códigos arancelarios y implementar soporte completo para las 13 categorías TLC China con reducciones escalonadas.

### Problemas Corregidos

1. **Estructura de códigos arancelarios incorrecta**: Los números de orden (1,2,3...) estaban concatenados incorrectamente con códigos HS
2. **Niveles de jerarquía mezclados**: No había separación clara entre códigos de nivel 4, 6 y 10
3. **Categorías TLC incompletas**: Solo se soportaban categorías básicas, faltaban reducciones escalonadas
4. **Funcionalidad de entrada manual faltante**: Solo se permitía importación CSV

### Nuevas Características

1. **Jerarquía de códigos arancelarios**: Soporte para niveles 4 (categorías), 6 (subcategorías) y 10 (códigos de cálculo)
2. **13 categorías TLC completas**: A0, A5, A10, A15, A17, A20, A15-3, A15-5, A17-3, A17-5, A20-3, A20-5, E
3. **Entrada manual de productos**: Interfaz para ingreso línea por línea
4. **Datos ICE actualizados**: Información de años 2020, 2021, 2024

## Cambios en la Base de Datos

### 1. Migración de Códigos Arancelarios

**Archivo**: `database/migrations/2024_01_01_000002_create_tariff_codes_table.php`

**Nuevos campos agregados**:
```php
$table->integer('hierarchy_level')->default(10);     // 4, 6, o 10
$table->string('parent_code', 20)->nullable();       // Código padre para jerarquía
$table->integer('order_number')->nullable();         // Número de orden original
$table->decimal('base_tariff_rate', 8, 4)->nullable(); // Ahora nullable para niveles 4 y 6
```

**Índices agregados**:
```php
$table->index(['hierarchy_level', 'parent_code']);
$table->index('order_number');
```

### 2. Migración de Cronogramas TLC

**Archivo**: `database/migrations/2024_01_01_000004_create_tlc_schedules_table.php`

**Nuevo campo agregado**:
```php
$table->string('tlc_category', 10)->nullable(); // Categoría TLC (A0, A5, A15-3, etc.)
```

## Nuevos Seeders

### 1. TariffCodeSeeder Actualizado

**Archivo**: `database/seeders/TariffCodeSeeder.php`

- **1,027 códigos nivel 4**: Descripciones de categorías
- **5,387 códigos nivel 6**: Códigos de subcategorías  
- **8,049 códigos nivel 10**: Códigos para cálculos (únicos con base_tariff_rate)
- **Total**: 14,463 códigos arancelarios

**Características**:
- Relaciones padre-hijo correctas
- Solo códigos nivel 10 tienen tarifas base
- Inserción en chunks para rendimiento

### 2. TlcScheduleSeeder Actualizado

**Archivo**: `database/seeders/TlcScheduleSeeder.php`

- **7,287 cronogramas TLC**: Todos los códigos con reducción China
- **13 categorías soportadas**: Incluyendo reducciones escalonadas
- **Lógica de reducción compleja**: Manejo de períodos de gracia

**Categorías TLC implementadas**:
- **A0**: Eliminación inmediata
- **A5**: Reducción lineal 5 años
- **A10**: Reducción lineal 10 años
- **A15**: Reducción lineal 15 años
- **A15-3**: Tarifa base 3 años, luego reducción 12 años
- **A15-5**: Tarifa base 5 años, luego reducción 10 años
- **A17**: Reducción lineal 17 años
- **A17-3**: Tarifa base 3 años, luego reducción 14 años
- **A17-5**: Tarifa base 5 años, luego reducción 12 años
- **A20**: Reducción lineal 20 años
- **A20-3**: Tarifa base 3 años, luego reducción 17 años
- **A20-5**: Tarifa base 5 años, luego reducción 15 años
- **E**: Sin reducción (mantiene tarifa base)

### 3. IceTaxSeeder Actualizado

**Archivo**: `database/seeders/IceTaxSeeder.php`

- **Datos ICE actualizados**: Años 2020, 2021, 2024
- **Categorías completas**: Todas las categorías de productos con ICE
- **Tasas actualizadas**: Según tabla resumen oficial

## Servicios Actualizados

### 1. TaxCalculationService

**Archivo**: `app/Services/TaxCalculationService.php`

**Nuevas características**:
- Soporte para reducciones escalonadas con períodos de gracia
- Cálculo correcto de categorías A15-3, A17-5, A20-3, etc.
- Lógica de reducción lineal después del período de gracia

**Método principal actualizado**: `calculateTlcReduction()`

### 2. CsvImportService

**Archivo**: `app/Services/CsvImportService.php`

**Cambio crítico**:
- Auto-detección ahora busca solo en códigos nivel 10
- Mejora la precisión de sugerencias automáticas

### 3. AdminController

**Archivo**: `app/Http/Controllers/AdminController.php`

**Nuevas características**:
- CRUD completo con soporte de jerarquía
- Validación de niveles de jerarquía
- Ordenamiento por nivel y código

## Nuevas Funcionalidades

### 1. Entrada Manual de Productos

**Archivos**:
- `app/Http/Controllers/CalculationController.php` - Métodos `createManual()` y `storeManual()`
- `resources/views/calculations/create-manual.blade.php` - Interfaz de entrada manual
- `routes/web.php` - Rutas para entrada manual

**Características**:
- Interfaz JavaScript dinámica
- Agregar/eliminar productos línea por línea
- Validación de códigos arancelarios
- Soporte para exención ICE por producto

### 2. Interfaz de Creación Mejorada

**Archivo**: `resources/views/calculations/create.blade.php`

**Mejoras**:
- Opción dual: CSV vs Manual
- Modal para importación CSV
- Interfaz más intuitiva

## Procedimiento de Actualización

### Paso 1: Backup de Base de Datos

```bash
# PostgreSQL
pg_dump -h localhost -U username -d database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# MySQL
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Paso 2: Actualizar Código

```bash
git checkout main
git pull origin main
git checkout devin/1758053020-database-restructure
```

### Paso 3: Ejecutar Migraciones y Seeders

```bash
# Limpiar y recrear base de datos
php artisan migrate:fresh --seed

# O ejecutar solo seeders actualizados
php artisan db:seed --class=TariffCodeSeeder
php artisan db:seed --class=TlcScheduleSeeder  
php artisan db:seed --class=IceTaxSeeder
```

### Paso 4: Verificar Instalación

```bash
# Verificar conteos de registros
php artisan tinker
>>> App\Models\TariffCode::count()
=> 14463
>>> App\Models\TlcSchedule::count() 
=> 7287
>>> App\Models\TariffCode::where('hierarchy_level', 10)->count()
=> 8049
```

### Paso 5: Probar Funcionalidades

1. **Admin Interface**: Acceder a `/admin/tariff-codes`
2. **Entrada Manual**: Crear cálculo manual en `/calculations/create-manual`
3. **Importación CSV**: Probar importación desde `/calculations/create`
4. **Cálculos TLC**: Verificar reducciones con códigos que tengan categorías escalonadas

## Verificación de Integridad

### Consultas de Verificación

```sql
-- Verificar jerarquía de códigos
SELECT hierarchy_level, COUNT(*) FROM tariff_codes GROUP BY hierarchy_level;

-- Verificar códigos con TLC
SELECT tlc_category, COUNT(*) FROM tlc_schedules GROUP BY tlc_category;

-- Verificar relaciones padre-hijo
SELECT 
    COUNT(*) as total_with_parents
FROM tariff_codes 
WHERE parent_code IS NOT NULL;

-- Verificar códigos nivel 10 con tarifas
SELECT COUNT(*) FROM tariff_codes 
WHERE hierarchy_level = 10 AND base_tariff_rate IS NOT NULL;
```

### Resultados Esperados

- **Códigos nivel 4**: 1,027
- **Códigos nivel 6**: 5,387  
- **Códigos nivel 10**: 8,049
- **Total códigos**: 14,463
- **Cronogramas TLC**: 7,287
- **Categorías TLC**: 13 diferentes

## Rollback (Si es Necesario)

### Paso 1: Restaurar Backup

```bash
# PostgreSQL
psql -h localhost -U username -d database_name < backup_file.sql

# MySQL  
mysql -u username -p database_name < backup_file.sql
```

### Paso 2: Revertir Código

```bash
git checkout main
git reset --hard HEAD~1  # Solo si es necesario
```

## Notas Importantes

1. **Rendimiento**: Los seeders usan inserción en chunks para manejar grandes volúmenes de datos
2. **Integridad**: Todas las relaciones padre-hijo están correctamente establecidas
3. **Compatibilidad**: La funcionalidad existente se mantiene intacta
4. **Escalabilidad**: La estructura soporta futuras expansiones de categorías TLC

## Soporte y Troubleshooting

### Errores Comunes

1. **Memory limit exceeded**: Aumentar `memory_limit` en PHP
2. **Foreign key constraints**: Verificar que códigos padre existan antes que hijos
3. **Timeout en seeders**: Ejecutar seeders individualmente

### Logs de Verificación

```bash
# Verificar logs de Laravel
tail -f storage/logs/laravel.log

# Verificar logs de base de datos
# PostgreSQL: /var/log/postgresql/
# MySQL: /var/log/mysql/
```

---

**Fecha de creación**: Septiembre 2025  
**Versión**: 1.0  
**Autor**: Sistema de actualización automática TaxImportEC
