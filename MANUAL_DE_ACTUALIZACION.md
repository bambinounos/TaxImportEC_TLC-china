# Manual de Actualización en Producción - TaxImportEC

Este documento describe el procedimiento para actualizar la aplicación TaxImportEC a su última versión en un entorno de producción.

## Introducción

Actualizar una aplicación en producción es un proceso delicado. Es crucial seguir estos pasos para asegurar que la aplicación se actualice sin interrupciones significativas para los usuarios y sin pérdida de datos.

Este manual asume que la aplicación ya fue instalada siguiendo el `MANUAL_DE_INSTALACION.md`.

## Antes de Empezar: Copia de Seguridad

**¡IMPORTANTE!** Antes de iniciar cualquier proceso de actualización, es **altamente recomendable** realizar una copia de seguridad completa de su base de datos. Si algo sale mal durante las migraciones, podrá restaurar la base de datos a su estado anterior.

Para una base de datos PostgreSQL, puede usar el comando `pg_dump`:

```bash
pg_dump -U su_usuario_de_db -h localhost su_base_de_datos > backup_fecha.sql
```

## Pasos para la Actualización

Siga estos pasos en el directorio raíz de su aplicación (e.g., `/var/www/TaxImportEC_TLC-china`).

### 1. Activar el Modo de Mantenimiento

Para evitar que los usuarios accedan a la aplicación mientras se actualiza, ponga la aplicación en modo de mantenimiento. Esto mostrará una página de "Servicio no disponible" a todos los visitantes.

```bash
php artisan down
```

### 2. Obtener el Código Más Reciente

Descargue los últimos cambios del código fuente desde el repositorio de Git.

```bash
git pull
```

### 3. Instalar Dependencias de Composer

Instale o actualice las dependencias de PHP. Es importante usar las mismas flags que en la instalación para optimizar para producción.

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Ejecutar Migraciones de la Base de Datos

Si la nueva versión del código incluye cambios en la estructura de la base de datos (nuevas tablas, columnas, etc.), debe aplicar las migraciones.

```bash
php artisan migrate --force
```
El flag `--force` es necesario para ejecutar migraciones en producción. Laravel le pedirá confirmación antes de proceder.

### 5. Actualizar Assets de Frontend

Si hubo cambios en los archivos JavaScript o CSS, debe reinstalar las dependencias de Node.js y compilar los assets nuevamente.

```bash
npm install
npm run build
```

### 6. Limpiar y Cachear la Configuración

Después de actualizar el código y las dependencias, es fundamental limpiar las cachés antiguas y volver a generarlas. Esto asegura que Laravel utilice la nueva configuración y las nuevas rutas.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Desactivar el Modo de Mantenimiento

Una vez que todos los pasos anteriores se hayan completado correctamente, la aplicación estará lista para volver a estar en línea. Desactive el modo de mantenimiento.

```bash
php artisan up
```

La aplicación ahora está actualizada y disponible para los usuarios.

## (Opcional) Reiniciar los Queue Workers

Si su aplicación utiliza colas (queues) para trabajos en segundo plano (e.g., enviar correos electrónicos, procesar trabajos pesados), debe reiniciarlos para que utilicen el nuevo código.

```bash
php artisan queue:restart
```
Este comando instruirá a todos los workers de la cola para que se reinicien de forma gradual sin interrumpir los trabajos que se están ejecutando actualmente.

Con estos pasos, su aplicación TaxImportEC estará actualizada y funcionando con la última versión del código.
