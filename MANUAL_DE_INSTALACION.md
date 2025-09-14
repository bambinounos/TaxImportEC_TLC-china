# Manual de Instalación en Producción - TaxImportEC

Este documento detalla los pasos necesarios para instalar y configurar la aplicación TaxImportEC en un servidor de producción utilizando Apache2 como servidor web.

## 1. Requisitos del Servidor

Antes de comenzar, asegúrese de que su servidor de producción cumpla con los siguientes requisitos:

-   **Sistema Operativo**: Distribución de Linux moderna (e.g., Ubuntu 22.04, CentOS 7+).
-   **Servidor Web**: Apache 2.4 o superior.
-   **PHP**: Versión 8.1 o superior, con las siguientes extensiones:
    -   `pdo_pgsql` (para la conexión con PostgreSQL)
    -   `mbstring`
    -   `xml`
    -   `json`
    -   `curl`
    -   `tokenizer`
    -   `bcmath`
    -   `ctype`
    -   `fileinfo`
-   **Base de Datos**: PostgreSQL 12 o superior.
-   **Software Adicional**:
    -   `composer` (versión 2.x) para gestionar las dependencias de PHP.
    -   `node.js` (versión 16+) y `npm` para compilar los assets de frontend.
    -   `git` para clonar el repositorio.

## 2. Pasos de Instalación

Siga estos pasos para instalar la aplicación en su servidor.

### a. Clonar el Repositorio

Clone el código fuente desde el repositorio de GitHub en el directorio de su elección (por ejemplo, `/var/www`).

```bash
cd /var/www
git clone https://github.com/bambinounos/TaxImportEC_TLC-china.git
cd TaxImportEC_TLC-china
```

### b. Instalar Dependencias

Instale las dependencias de PHP con Composer. Use las flags `--no-dev` y `--optimize-autoloader` para un entorno de producción.

```bash
composer install --no-dev --optimize-autoloader
```

### c. Configurar el Entorno

Copie el archivo de ejemplo `.env.example` a `.env` y configure las variables de entorno para producción.

```bash
cp .env.example .env
```

Ahora, edite el archivo `.env` con su editor de preferencia (e.g., `nano .env`). Asegúrese de configurar lo siguiente:

-   **Modo de la Aplicación**:
    ```env
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://su-dominio.com
    ```
-   **Conexión a la Base de Datos**:
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=tax_import_ec
    DB_USERNAME=su_usuario_de_db
    DB_PASSWORD=su_contraseña_de_db
    ```

Una vez guardado el archivo `.env`, genere la clave de la aplicación:

```bash
php artisan key:generate
```

### d. Ejecutar Migraciones y Seeders

Aplique las migraciones para crear la estructura de la base de datos y ejecute los seeders para poblar los datos iniciales.

```bash
php artisan migrate --force
php artisan db:seed --force
```
**Nota**: El flag `--force` es necesario para ejecutar las migraciones en un entorno de producción.

### e. Compilar Assets de Frontend

Instale las dependencias de Node.js y compile los archivos CSS y JavaScript para producción.

```bash
npm install
npm run build
```

### f. Establecer Permisos

Asegúrese de que el servidor web (e.g., `www-data`) tenga permisos de escritura sobre los directorios `storage` y `bootstrap/cache`.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 3. Configuración de Apache2

Para que la aplicación sea accesible a través de un dominio, debe configurar un Virtual Host en Apache.

### a. Crear Archivo de Configuración del Virtual Host

Cree un nuevo archivo de configuración en el directorio `sites-available` de Apache.

```bash
sudo nano /etc/apache2/sites-available/taximportec.conf
```

### b. Añadir Contenido al Virtual Host

Pegue la siguiente configuración en el archivo, reemplazando `su-dominio.com` con su dominio real y `/var/www/TaxImportEC_TLC-china` con la ruta a su proyecto.

```apache
<VirtualHost *:80>
    ServerName su-dominio.com
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/TaxImportEC_TLC-china/public

    <Directory /var/www/TaxImportEC_TLC-china/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
**Nota**: La directiva `AllowOverride All` es crucial para que el archivo `.htaccess` de Laravel sea procesado por Apache, permitiendo las URL amigables.

### c. Habilitar el Sitio y Módulos de Apache

Habilite el nuevo sitio, el módulo `rewrite` de Apache y reinicie el servidor para aplicar los cambios.

```bash
sudo a2ensite taximportec.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### d. (Opcional) Configurar SSL con Let's Encrypt

Para un sitio en producción, es altamente recomendable usar HTTPS. Puede obtener un certificado SSL gratuito usando Certbot (Let's Encrypt).

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d su-dominio.com
```
Certbot modificará automáticamente su archivo de configuración de Apache para redirigir el tráfico HTTP a HTTPS.

## 4. Paso Crítico: Poblar la Base de Datos de Partidas Arancelarias

**¡ATENCIÓN!** Esta es la parte más importante de la instalación.

El archivo `README.md` del proyecto menciona que se incluye una "base de datos completa de partidas arancelarias ecuatorianas", sin embargo, **esta base de datos no se encuentra en los archivos de seeding del repositorio**. La aplicación **no funcionará correctamente** sin estos datos, ya que el sistema valida los códigos arancelarios (HS Codes) de los productos importados contra la tabla `tariff_codes`.

Debe poblar esta tabla manualmente.

### a. Estructura de la Tabla `tariff_codes`

Necesitará obtener los datos de partidas arancelarias de una fuente oficial (e.g., el servicio de aduanas de Ecuador) y cargarlos en la tabla `tariff_codes`. La estructura de la tabla es la siguiente:

-   `id` (serial, autoincremental)
-   `hs_code` (varchar, 10 caracteres, único): El código arancelario. Ej: `8471.30.00`.
-   `description_en` (varchar): Descripción en inglés.
-   `description_es` (varchar): Descripción en español.
-   `base_tariff_rate` (decimal, 8,4): Tasa de arancel base. Ej: `15.0000` para 15%.
-   `iva_rate` (decimal, 8,4): Tasa de IVA. Por defecto `15.0000`.
-   `has_ice` (boolean): `true` si la partida tiene un impuesto ICE asociado, `false` si no.
-   `is_active` (boolean): `true` si la partida está activa.
-   `created_at`, `updated_at` (timestamps)

### b. ¿Cómo Poblar los Datos?

Tiene varias opciones para cargar los datos:

1.  **Mediante SQL**: Crear un script `.sql` con sentencias `INSERT` para cada partida arancelaria y ejecutarlo en su base de datos PostgreSQL.
2.  **Herramienta de Base de Datos**: Usar una herramienta como DBeaver, pgAdmin o la línea de comandos (`psql`) para importar los datos desde un archivo CSV.
3.  **Crear un Seeder Personalizado**: Si es un desarrollador, puede crear su propio `TariffCodeSeeder.php` en Laravel, leer los datos de un CSV o JSON, y usar `php artisan db:seed --class=TariffCodeSeeder` para poblar la tabla.

**Ejemplo de sentencia SQL INSERT:**

```sql
INSERT INTO tariff_codes (hs_code, description_en, description_es, base_tariff_rate, iva_rate, has_ice, is_active, created_at, updated_at) VALUES
('8471.30.00', 'Portable digital automatic data processing machines', 'Máquinas automáticas para tratamiento o procesamiento de datos, portátiles', 0.0000, 15.0000, false, true, NOW(), NOW()),
('8703.23.00', 'Passenger car 1200cc gasoline', 'Automóvil de pasajeros 1200cc gasolina', 35.0000, 15.0000, true, true, NOW(), NOW());
```

Es crucial que esta tabla esté completa y correctamente poblada para que los cálculos de impuestos de la aplicación sean precisos.

## 5. Optimización para Producción

Después de completar todos los pasos anteriores, ejecute los siguientes comandos para optimizar el rendimiento de la aplicación en producción. Estos comandos cachean la configuración, las rutas y las vistas, lo que reduce la cantidad de trabajo que Laravel necesita hacer en cada solicitud.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

¡Felicidades! Su instancia de TaxImportEC está instalada, configurada y optimizada para producción.
