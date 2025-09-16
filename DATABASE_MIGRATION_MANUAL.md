# Manual de Migración de Base de Datos - TaxImportEC TLC China

## Resumen de Cambios

Esta migración corrige la estructura de la base de datos para incluir:
- **14,869 códigos arancelarios** con jerarquía correcta (4→6→10)
- **8,260 códigos de cálculo** nivel-10 (incluyendo códigos con asteriscos)
- **Nuevas categorías TLC** con reducciones escalonadas
- **Datos ICE actualizados** de fuentes gubernamentales

## ⚠️ IMPORTANTE: Respaldo Antes de Migrar

```bash
# 1. Respaldar base de datos actual
pg_dump -h localhost -U tu_usuario -d taximportec > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Respaldar archivos de configuración
cp .env .env.backup
cp -r storage/app storage/app.backup
```

## Opción A: Migración Completa (Recomendada)

### Paso 1: Preparar el Entorno
```bash
# Cambiar al directorio del proyecto
cd /var/www/html/taximportec

# Hacer backup de configuraciones
cp .env .env.backup
cp -r storage/app/calculations storage/app/calculations.backup
```

### Paso 2: Actualizar Código
```bash
# Obtener últimos cambios
git fetch origin
git checkout main
git pull origin main

# Verificar que tienes la versión correcta
git log --oneline -5
```

### Paso 3: Limpiar y Recrear Base de Datos
```bash
# Eliminar todas las tablas y recrear
php artisan migrate:fresh --seed --force

# Verificar que los seeders se ejecutaron correctamente
php artisan tinker
>>> \App\Models\TariffCode::count()
# Debe mostrar: 14869

>>> \App\Models\TariffCode::where('hierarchy_level', 10)->count()
# Debe mostrar: 8260

>>> \App\Models\TlcSchedule::count()
# Debe mostrar: 8260

>>> exit
```

### Paso 4: Restaurar Configuraciones de Usuario
```bash
# Restaurar configuraciones personalizadas si las hay
# (Revisar diferencias entre .env.backup y .env actual)

# Restaurar cálculos guardados si es necesario
# cp -r storage/app/calculations.backup/* storage/app/calculations/
```

## Opción B: Migración Incremental (Para Instalaciones con Datos Críticos)

### Paso 1: Exportar Datos Críticos
```bash
# Exportar cálculos existentes
php artisan tinker
>>> $calculations = \App\Models\Calculation::with('items')->get();
>>> file_put_contents('calculations_backup.json', $calculations->toJson());
>>> exit
```

### Paso 2: Actualizar Estructura de Tablas
```bash
# Ejecutar solo las nuevas migraciones
php artisan migrate --force

# Verificar que las nuevas columnas existen
php artisan tinker
>>> \Schema::hasColumn('tariff_codes', 'hierarchy_level')
# Debe mostrar: true

>>> \Schema::hasColumn('tariff_codes', 'parent_code')
# Debe mostrar: true

>>> \Schema::hasColumn('tlc_schedules', 'tlc_category')
# Debe mostrar: true

>>> exit
```

### Paso 3: Limpiar y Recargar Solo Datos de Referencia
```bash
# Limpiar solo tablas de datos de referencia
php artisan tinker
>>> \App\Models\TariffCode::truncate();
>>> \App\Models\TlcSchedule::truncate();
>>> \App\Models\IceTax::truncate();
>>> exit

# Ejecutar solo seeders de datos de referencia
php artisan db:seed --class=TariffCodeSeeder --force
php artisan db:seed --class=TlcScheduleSeeder --force
php artisan db:seed --class=IceTaxSeeder --force
```

### Paso 4: Restaurar Cálculos de Usuario
```bash
# Restaurar cálculos desde backup (si es necesario)
php artisan tinker
>>> $backupData = json_decode(file_get_contents('calculations_backup.json'), true);
>>> // Procesar y restaurar según sea necesario
>>> exit
```

## Verificación Post-Migración

### Verificar Conteos de Datos
```bash
php artisan tinker
>>> echo "Códigos nivel-4: " . \App\Models\TariffCode::where('hierarchy_level', 4)->count();
# Esperado: 1222

>>> echo "Códigos nivel-6: " . \App\Models\TariffCode::where('hierarchy_level', 6)->count();
# Esperado: 5387

>>> echo "Códigos nivel-10: " . \App\Models\TariffCode::where('hierarchy_level', 10)->count();
# Esperado: 8260

>>> echo "Total códigos: " . \App\Models\TariffCode::count();
# Esperado: 14869

>>> echo "Cronogramas TLC: " . \App\Models\TlcSchedule::count();
# Esperado: 8260

>>> exit
```

### Verificar Jerarquía
```bash
php artisan tinker
>>> // Verificar que códigos nivel-10 tienen padres
>>> $orphans = \App\Models\TariffCode::where('hierarchy_level', 10)
>>>     ->whereNull('parent_code')->count();
>>> echo "Códigos nivel-10 sin padre: " . $orphans;
# Esperado: 0 o muy pocos

>>> // Verificar ejemplo de jerarquía
>>> $code = \App\Models\TariffCode::where('hs_code', '0101210000')->first();
>>> echo "Código: " . $code->hs_code . " -> Padre: " . $code->parent_code;
# Esperado: Código: 0101210000 -> Padre: 010121

>>> exit
```

### Verificar Funcionalidad
```bash
# Probar que la aplicación carga correctamente
php artisan serve --host=0.0.0.0 --port=8000 &

# Probar endpoints críticos
curl -s http://localhost:8000/calculations | grep -q "Cálculos" && echo "✅ Página de cálculos OK"
curl -s http://localhost:8000/admin/tariff-codes | grep -q "Códigos" && echo "✅ Admin códigos OK"

# Detener servidor de prueba
pkill -f "php artisan serve"
```

## Nuevas Funcionalidades Disponibles

### 1. Jerarquía de Códigos Arancelarios
- **Nivel 4**: Categorías descriptivas (ej: 0101 - "Caballos, asnos, mulos...")
- **Nivel 6**: Subcategorías (ej: 010121)
- **Nivel 10**: Códigos de cálculo (ej: 0101210000)

### 2. Nuevas Categorías TLC
- **A0**: Eliminación inmediata
- **A5, A10, A15, A17, A20**: Reducción lineal en 5, 10, 15, 17, 20 años
- **A15-3, A17-3, A20-3**: Mantener tasa base por 3 años, luego reducir
- **A15-5, A17-5, A20-5**: Mantener tasa base por 5 años, luego reducir
- **E**: Sin reducción (mantiene tasa base)

### 3. Auto-detección Mejorada
- Búsqueda solo en códigos nivel-10 (códigos de cálculo)
- Mayor precisión en sugerencias automáticas

### 4. Entrada Manual de Cálculos
- Nueva interfaz para entrada línea por línea
- Disponible en `/calculations/create-manual`

## Solución de Problemas

### Error: "Column 'hierarchy_level' doesn't exist"
```bash
# Ejecutar migración específica
php artisan migrate --path=database/migrations/2024_01_01_000002_create_tariff_codes_table.php --force
```

### Error: "Class 'TariffCodeSeeder' not found"
```bash
# Regenerar autoload
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: Memoria insuficiente durante seeding
```bash
# Aumentar límite de memoria temporalmente
php -d memory_limit=512M artisan db:seed --class=TariffCodeSeeder --force
```

### Verificar Integridad de Datos
```bash
# Script de verificación completa
php artisan tinker
>>> $issues = [];
>>> 
>>> // Verificar códigos duplicados
>>> $duplicates = \App\Models\TariffCode::select('hs_code')
>>>     ->groupBy('hs_code')->havingRaw('count(*) > 1')->count();
>>> if ($duplicates > 0) $issues[] = "Códigos duplicados: $duplicates";
>>> 
>>> // Verificar cronogramas TLC huérfanos
>>> $orphanSchedules = \App\Models\TlcSchedule::whereNotIn('hs_code', 
>>>     \App\Models\TariffCode::where('hierarchy_level', 10)->pluck('hs_code'))->count();
>>> if ($orphanSchedules > 0) $issues[] = "Cronogramas TLC huérfanos: $orphanSchedules";
>>> 
>>> if (empty($issues)) {
>>>     echo "✅ Verificación completa: Sin problemas detectados";
>>> } else {
>>>     echo "❌ Problemas encontrados:\n" . implode("\n", $issues);
>>> }
>>> 
>>> exit
```

## Contacto y Soporte

Si encuentras problemas durante la migración:

1. **Revisar logs**: `tail -f storage/logs/laravel.log`
2. **Verificar configuración**: Comparar `.env` con `.env.example`
3. **Restaurar backup**: Si es necesario, restaurar desde el backup inicial

## Notas Importantes

- ⚠️ **Siempre hacer backup antes de migrar**
- ✅ **La migración es irreversible sin backup**
- 🔄 **Probar en entorno de desarrollo primero**
- 📊 **Verificar conteos de datos después de migrar**
- 🧪 **Probar funcionalidad crítica post-migración**

---

**Versión del Manual**: 1.0  
**Fecha**: Septiembre 2024  
**Compatibilidad**: Laravel 10.x, PHP 8.1+, PostgreSQL 12+
