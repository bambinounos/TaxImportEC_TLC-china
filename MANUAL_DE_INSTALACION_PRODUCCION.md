# Manual de Instalación en Producción (Apache2 + PostgreSQL)

Esta guía detalla los pasos para instalar la aplicación en un servidor de producción utilizando Apache2 como servidor web y PostgreSQL como base de datos.

## 1. Requisitos del Servidor

A continuación se detallan los requisitos de software y sistema operativo para un despliegue exitoso.

### Sistema Operativo
Se recomienda utilizar una distribución de Linux estable y con soporte a largo plazo (LTS). Todas las instrucciones de esta guía están basadas en **Ubuntu 22.04 LTS**.

### Software Requerido
-   **Servidor Web:** Apache2 2.4 o superior.
-   **Base de Datos:** PostgreSQL 12 o superior.
-   **PHP:** Versión **8.1** o superior.
-   **Composer:** Versión 2.x para la gestión de dependencias de PHP.
-   **Git:** Para clonar el repositorio.

### Extensiones de PHP
La instalación de PHP debe incluir las siguientes extensiones:
-   `php8.1-cli`
-   `php8.1-pgsql` (para la conexión con PostgreSQL)
-   `php8.1-mbstring`
-   `php8.1-xml`
-   `php8.1-dom`
-   `php8.1-curl`
-   `php8.1-fileinfo`
-   `php8.1-ctype`
-   `php8.1-tokenizer`
-   `php8.1-openssl`
-   `php8.1-pdo`
-   `php8.1-session`
-   `php8.1-bcmath`

## 2. Pasos de Instalación

Siga estos pasos en orden para instalar la aplicación en el servidor.

### Paso 1: Instalar Software Base (Apache, PostgreSQL, PHP y Git)

1.  **Actualizar el sistema:**
    ```bash
    sudo apt update && sudo apt upgrade -y
    ```

2.  **Instalar Apache2:**
    ```bash
    sudo apt install -y apache2
    ```

3.  **Instalar PostgreSQL:**
    ```bash
    sudo apt install -y postgresql postgresql-contrib
    ```

4.  **Añadir el repositorio de PHP (si es necesario) e instalar PHP 8.1 y extensiones:**
    ```bash
    sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
    sudo apt install -y php8.1 libapache2-mod-php8.1 php8.1-cli php8.1-pgsql php8.1-mbstring php8.1-xml php8.1-dom php8.1-curl php8.1-fileinfo php8.1-ctype php8.1-tokenizer php8.1-openssl php8.1-pdo php8.1-bcmath
    ```

5.  **Instalar Git:**
    ```bash
    sudo apt install -y git
    ```

### Paso 2: Instalar Composer

1.  **Descargar el instalador de Composer:**
    ```bash
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ```

2.  **Verificar la firma del instalador (opcional pero recomendado):**
    ```bash
    # Obtener el hash más reciente de la página de Composer y compararlo
    # https://composer.github.io/pubkeys.html
    ```

3.  **Ejecutar el instalador:**
    ```bash
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    ```

4.  **Limpiar:**
    ```bash
    php -r "unlink('composer-setup.php');"
    ```

### Paso 3: Configurar la Base de Datos PostgreSQL

1.  **Acceder a la línea de comandos de PostgreSQL:**
    ```bash
    sudo -u postgres psql
    ```

2.  **Crear un nuevo rol (usuario) para la aplicación.** Reemplace `'su_password_segura'` con una contraseña fuerte.
    ```sql
    CREATE ROLE nombre_usuario WITH LOGIN PASSWORD 'su_password_segura';
    ```

3.  **Crear la base de datos para la aplicación.**
    ```sql
    CREATE DATABASE nombre_basedatos OWNER nombre_usuario;
    ```

4.  **Salir de psql:**
    ```sql
    \q
    ```

### Paso 4: Clonar el Repositorio

Navegue al directorio donde alojará sus sitios web (ej. `/var/www`) y clone el proyecto. Reemplace `URL_DEL_REPOSITORIO` con la URL real del repositorio Git.

```bash
cd /var/www
sudo git clone URL_DEL_REPOSITORIO nombre_del_proyecto
cd nombre_del_proyecto
```

### Paso 5: Instalar Dependencias de PHP

Instale las dependencias del proyecto utilizando Composer. El flag `--no-dev` es importante para excluir las dependencias de desarrollo en un entorno de producción.

```bash
sudo composer install --optimize-autoloader --no-dev
```

### Paso 6: Configurar el Entorno de la Aplicación

1.  **Copiar el archivo de entorno de ejemplo:**
    ```bash
    sudo cp .env.example .env
    ```

2.  **Editar el archivo `.env`:**
    ```bash
    sudo nano .env
    ```

3.  **Ajustar las siguientes variables clave:**
    -   `APP_NAME`: Nombre de su aplicación (ej. "Calculadora de Impuestos").
    -   `APP_ENV`: Cambiar a `production`.
    -   `APP_DEBUG`: Cambiar a `false`.
    -   `APP_URL`: La URL completa de su sitio (ej. `http://sudominio.com`).
    -   `DB_CONNECTION`: Asegurarse de que es `pgsql`.
    -   `DB_HOST`: `127.0.0.1` (o el host de su base de datos).
    -   `DB_PORT`: `5432`.
    -   `DB_DATABASE`: `nombre_basedatos` (el nombre que creó en el Paso 3).
    -   `DB_USERNAME`: `nombre_usuario` (el rol que creó en el Paso 3).
    -   `DB_PASSWORD`: `su_password_segura` (la contraseña que definió).

### Paso 7: Generar la Clave de la Aplicación

Este comando es crucial para la seguridad de la sesión y el cifrado.

```bash
sudo php artisan key:generate
```

### Paso 8: Ejecutar Migraciones y Seeders

1.  **Ejecutar las migraciones** para crear la estructura de tablas en la base de datos:
    ```bash
    sudo php artisan migrate --force
    ```
    *Se usa `--force` porque la aplicación está en producción.*

2.  **Ejecutar los seeders** para poblar la base de datos con datos iniciales:
    ```bash
    sudo php artisan db:seed --force
    ```

### Paso 9: Establecer Permisos de Directorio

El servidor web (Apache) necesita permisos de escritura en ciertos directorios. En sistemas Ubuntu/Debian, el usuario de Apache es `www-data`.

```bash
sudo chown -R www-data:www-data /var/www/nombre_del_proyecto/storage
sudo chown -R www-data:www-data /var/www/nombre_del_proyecto/bootstrap/cache
sudo chmod -R 775 /var/www/nombre_del_proyecto/storage
sudo chmod -R 775 /var/www/nombre_del_proyecto/bootstrap/cache

## 3. Configuración de Apache2

Para que Apache sirva el sitio web correctamente, es necesario crear un archivo de configuración de Virtual Host.

### Paso 1: Crear el Archivo de Virtual Host

1.  **Crear un nuevo archivo de configuración** para su dominio. Reemplace `sudominio.com` con su nombre de dominio real.
    ```bash
    sudo nano /etc/apache2/sites-available/sudominio.com.conf
    ```

2.  **Pegar la siguiente configuración** en el archivo. Asegúrese de cambiar `sudominio.com`, `www.sudominio.com` y la ruta en `DocumentRoot` y `Directory` para que coincidan con su configuración.

    ```apache
    <VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName sudominio.com
        ServerAlias www.sudominio.com
        DocumentRoot /var/www/nombre_del_proyecto/public

        <Directory /var/www/nombre_del_proyecto/public>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>
    ```

### Paso 2: Habilitar el Sitio y Módulos Necesarios

1.  **Habilitar el módulo `rewrite`** de Apache, que es esencial para las URLs amigables de Laravel.
    ```bash
    sudo a2enmod rewrite
    ```

2.  **Habilitar la configuración del nuevo sitio:**
    ```bash
    sudo a2ensite sudominio.com.conf
    ```

3.  **Deshabilitar el sitio por defecto** (opcional, pero recomendado si este es el único sitio en el servidor).
    ```bash
    sudo a2dissite 000-default.conf
    ```

4.  **Verificar la sintaxis de la configuración de Apache:**
    ```bash
    sudo apache2ctl configtest
    ```
    Si todo está correcto, debería ver el mensaje `Syntax OK`.

5.  **Reiniciar Apache para aplicar los cambios:**
    ```bash
    sudo systemctl restart apache2
    ```

En este punto, su aplicación debería estar accesible a través de su nombre de dominio.

## 4. Optimización para Producción

Para mejorar el rendimiento de la aplicación, es fundamental cachear la configuración, las rutas y las vistas de Laravel.

**Importante:** Cada vez que realice cambios en su código o en los archivos de configuración (`.env`, `config/*.php`, `routes/*.php`), deberá volver a ejecutar estos comandos.

```bash
cd /var/www/nombre_del_proyecto

# Cachear el archivo de configuración
sudo php artisan config:cache

# Cachear la tabla de rutas
sudo php artisan route:cache

# Cachear los archivos de las vistas
sudo php artisan view:cache
```

Para borrar la caché (por ejemplo, después de una actualización), puede usar los siguientes comandos:
```bash
sudo php artisan config:clear
sudo php artisan route:clear
sudo php artisan view:clear
sudo php artisan cache:clear
```

Con estos pasos, la instalación y configuración en el servidor de producción están completas.
```
