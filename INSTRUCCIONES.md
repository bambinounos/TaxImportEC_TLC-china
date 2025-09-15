# Instrucciones de Despliegue y Mantenimiento

Este documento proporciona instrucciones sobre cómo desplegar, actualizar y mantener la aplicación.

## Requisitos

- PHP >= 8.1
- Composer 2.x
- Acceso a la base de datos configurada en el archivo `.env`

## Instalación por Primera Vez

Si está desplegando la aplicación en un servidor nuevo, siga estos pasos:

1.  **Clonar el repositorio:**
    ```bash
    git clone <URL_DEL_REPOSITORIO> .
    ```

2.  **Instalar dependencias:**
    Descargue Composer si no lo tiene y luego instale las dependencias del proyecto.
    ```bash
    # Descargar composer.phar si no existe
    if [ ! -f "composer.phar" ]; then
        curl -sS https://getcomposer.org/installer | php
    fi

    # Instalar dependencias
    php composer.phar install --no-dev --optimize-autoloader
    ```

3.  **Configurar el entorno:**
    Copie el archivo de ejemplo `.env.example` a `.env` y configure las variables, especialmente las de la base de datos (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).
    ```bash
    cp .env.example .env
    ```

4.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

5.  **Ejecutar las migraciones de la base de datos:**
    ```bash
    php artisan migrate --force
    ```

## Actualización de una Instalación Existente

Para actualizar la aplicación a la última versión, siga estos pasos:

1.  **Obtener los últimos cambios:**
    ```bash
    git pull
    ```

2.  **Instalar/actualizar dependencias:**
    Es una buena práctica volver a ejecutar `composer install` para asegurarse de que todas las dependencias estén actualizadas según el archivo `composer.lock`.
    ```bash
    php composer.phar install --no-dev --optimize-autoloader
    ```

3.  **Ejecutar migraciones:**
    Si hay nuevos cambios en la base de datos, ejecute las migraciones.
    ```bash
    php artisan migrate --force
    ```

4.  **Limpiar y generar cachés:**
    Para asegurar que los cambios se apliquen correctamente en producción, es importante limpiar las cachés antiguas y generar las nuevas.
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

## Verificación del Arreglo Actual

Para verificar que el problema original de `view:cache` se ha resuelto, por favor siga estos pasos después de hacer `git pull` para obtener los nuevos archivos (`config/view.php` y este `INSTRUCCIONES.md`):

1.  Asegúrese de que las dependencias estén instaladas:
    ```bash
    php composer.phar install --no-dev --optimize-autoloader
    ```
2.  Limpie todas las cachés para estar seguros:
    ```bash
    php artisan view:clear
    php artisan config:clear
    php artisan route:clear
    ```
3.  Intente cachear las vistas de nuevo:
    ```bash
    php artisan view:cache
    ```
    Este comando ya no debería arrojar el error "View path not found".

## Nota sobre el Entorno de Depuración

Durante la depuración, encontramos un comportamiento muy inusual en el entorno de la línea de comandos (CLI) que impedía que las funciones auxiliares de Laravel se cargaran correctamente. A pesar de que todos los requisitos y configuraciones parecían correctos, el problema persistió. La solución implementada (agregar `config/view.php`) resuelve el problema de despliegue original. Si los problemas de CLI persisten, podría ser necesario investigar más a fondo la configuración específica de PHP en el servidor.
