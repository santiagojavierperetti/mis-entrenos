# Mis Entrenos

Aplicación PHP sencilla para subir archivos `.fit` y almacenar su metadata en una base de datos MySQL.

## Estructura del proyecto

- `index.php`: formulario mínimo para seleccionar un archivo `.fit` y enviarlo al servidor.
- `procesar.php`: valida la subida, evita duplicados, mueve el archivo a `uploads/` y guarda la metadata en la tabla `fit_files`.
- `config.example.php`: plantilla para crear `config.php` con la conexión PDO.
- `database/schema.sql`: script SQL para crear la base de datos y la tabla principal.
- `uploads/`: carpeta donde se guardarán los archivos subidos (se crea en tiempo de ejecución si no existe).

## Paso 1: configurar la base de datos

1. Importa el script [`database/schema.sql`](database/schema.sql) en tu servidor MySQL:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Esto creará la base `mis_entrenos` y la tabla `fit_files` con una restricción para evitar duplicados por hash MD5.

2. Copia `config.example.php` a `config.php` y actualiza usuario, contraseña y host según tu entorno.
   ```bash
   cp config.example.php config.php
   ```

3. Verifica la conexión ejecutando el flujo de subida desde `index.php`. Si todo está correcto deberías ver el resumen del archivo guardado y un nuevo registro en la tabla `fit_files`.

### ¿Qué contiene `database/schema.sql`?

El archivo `schema.sql` es simplemente un guion con instrucciones SQL que crean la base de datos y la tabla necesaria para guardar los metadatos de cada archivo `.fit`. No es un requisito descargarlo desde el navegador: basta con ejecutarlo una vez en el servidor MySQL (como se muestra arriba) para tener todo listo.

## Siguientes pasos

- Añadir más validaciones y una mejor experiencia de usuario en el formulario.
- Procesar los archivos `.fit` para extraer métricas deportivas y guardarlas en nuevas tablas.
- Proteger la carpeta `uploads/` y añadir autenticación si la aplicación se usará en producción.