# TaxImportEC TLC China

TaxImportEC es el primer software en Ecuador diseñado para calcular impuestos de importación con precisión legal absoluta, incorporando la complejidad de los Tratados de Libre Comercio (TLC) como el acuerdo con China. A diferencia de soluciones genéricas, este sistema está específicamente desarrollado para cumplir con la normativa ecuatoriana y optimizar los cálculos fiscales en importaciones.

## Características Principales

### 🎯 Cálculo Automático de Impuestos
- **Detección automática de partidas arancelarias** con base de datos completa de códigos HS vigentes en Ecuador
- **Cálculo preciso de ICE** (Impuesto a los Consumos Especiales) con tarifas específicas y ad-valorem
- **Aplicación automática de IVA** con tasas configurables por partida arancelaria
- **Soporte completo para TLC China** con reducciones graduales de aranceles (5-20 años)

### 📊 Gestión Avanzada de Costos
- **Prorrateo inteligente** de costos por peso unitario o precio unitario
- **Cálculo CIF automático** con flete, seguro y costos adicionales
- **Costos pre y post impuestos** completamente configurables
- **Margen de ganancia configurable** para cálculo de precios de venta

### 📁 Importación y Exportación Masiva
- **Importación CSV** con validación automática de datos
- **Sugerencia inteligente** de partidas arancelarias basada en descripciones
- **Exportación a CSV y Excel** con todos los cálculos detallados
- **Gestión de múltiples contenedores** para cálculos de flete

### 👥 Sistema Multi-Usuario
- **Roles diferenciados**: Administrador y Usuario
- **Gestión de cálculos** con nombre, fecha y creador
- **Historial completo** de cálculos para auditoría y correcciones
- **Configuraciones personalizables** por usuario

### ⚙️ Configuración Flexible
- **Modo TLC China** vs cálculo de impuestos normal
- **Campos adicionales dinámicos** para nuevos gastos
- **Valores por defecto configurables** para agilizar el trabajo
- **Base de datos actualizable** de partidas arancelarias e impuestos

## Tecnologías Utilizadas

- **Backend**: PHP 8.1+ con Laravel 10
- **Base de Datos**: PostgreSQL
- **Frontend**: Blade Templates con Bootstrap
- **Exportación**: PhpSpreadsheet para Excel
- **Autenticación**: Laravel Sanctum

## Instalación

### Requisitos Previos
- PHP 8.1 o superior
- PostgreSQL 12 o superior
- Composer
- Node.js y npm (para assets)

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/bambinounos/TaxImportEC_TLC-china.git
cd TaxImportEC_TLC-china
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar el entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar la base de datos**
Editar el archivo `.env` con los datos de conexión a PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tax_import_ec
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Compilar assets**
```bash
npm run build
```

7. **Iniciar el servidor**
```bash
php artisan serve
```

## Uso del Sistema

### Importación de Productos

1. **Preparar archivo CSV** con las siguientes columnas:
   - `part_number` (opcional): Número de parte del producto
   - `description_en` (requerido): Descripción en inglés
   - `description_es` (opcional): Descripción en español
   - `hs_code` (opcional): Código arancelario (se puede sugerir automáticamente)
   - `unit_weight` (opcional): Peso unitario en kg
   - `quantity` (requerido): Cantidad de unidades
   - `unit_price_fob` (requerido): Precio unitario FOB en USD

2. **Crear nuevo cálculo** desde el dashboard
3. **Configurar parámetros**:
   - Nombre del cálculo
   - Año de cálculo (para TLC)
   - Modo TLC China (activar/desactivar)
   - Método de prorrateo (peso o precio)
   - Costos de flete y seguro
   - Costos adicionales pre y post impuestos

4. **Importar archivo CSV** y revisar sugerencias de partidas arancelarias
5. **Ejecutar cálculo** automático de impuestos
6. **Revisar y ajustar** resultados si es necesario
7. **Exportar resultados** en CSV o Excel

### Configuración de Sistema (Solo Administradores)

- **Gestión de partidas arancelarias**: Agregar, editar o desactivar códigos HS
- **Configuración de ICE**: Actualizar tarifas y exenciones
- **Cronogramas TLC**: Configurar reducciones arancelarias por años
- **Configuraciones globales**: IVA, seguros, márgenes por defecto

## Estructura de la Base de Datos

### Tablas Principales

- `tariff_codes`: Códigos arancelarios con tarifas base e IVA
- `ice_taxes`: Impuestos a consumos especiales con tarifas específicas y ad-valorem
- `tlc_schedules`: Cronogramas de reducción arancelaria para TLC China
- `calculations`: Cálculos guardados con configuraciones
- `calculation_items`: Items individuales de cada cálculo
- `system_settings`: Configuraciones globales del sistema

## Cálculos Implementados

### Fórmula de Cálculo CIF
```
CIF = FOB + Flete Prorrateado + Seguro + Otros Costos Pre-Impuestos
```

### Cálculo de Aranceles
```
Arancel = CIF × (Tasa Arancelaria ÷ 100)
```
- **Modo Normal**: Usa tasa base de la partida arancelaria
- **Modo TLC China**: Aplica reducción gradual según año de cálculo

### Cálculo de ICE
```
ICE Específico = Cantidad × Tarifa Específica USD
ICE Ad-Valorem = (CIF + Arancel) × (Tasa ICE ÷ 100)
```

### Cálculo de IVA
```
IVA = (CIF + Arancel + ICE) × (Tasa IVA ÷ 100)
```

### Costo Total
```
Costo Total = CIF + Arancel + ICE + IVA + Otros Costos Post-Impuestos
```

### Precio de Venta
```
Precio Venta = Costo Total × (1 + Margen Ganancia ÷ 100)
```

## Contribución

1. Fork el proyecto
2. Crear una rama para la nueva funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Commit los cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## Soporte

Para soporte técnico o consultas sobre el sistema, contactar a:
- Email: soporte@taximportec.com
- GitHub Issues: [Reportar un problema](https://github.com/bambinounos/TaxImportEC_TLC-china/issues)

## Changelog

### v1.0.0 (2024-09-14)
- Implementación inicial del sistema
- Soporte completo para TLC China
- Cálculos automáticos de ICE, IVA y aranceles
- Importación/exportación CSV y Excel
- Sistema multi-usuario con roles
- Base de datos completa de partidas arancelarias ecuatorianas

---

**TaxImportEC** - Desarrollado específicamente para importadores ecuatorianos que requieren precisión legal absoluta en sus cálculos fiscales.
