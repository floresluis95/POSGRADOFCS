# Mantenimiento

Scripts de migración y corrección de datos que se ejecutaron **una sola vez** durante el desarrollo.
Están aquí (y no en la raíz) para que no se puedan abrir desde el navegador: el `.htaccess`
de esta carpeta bloquea todo acceso web.

- **No subir esta carpeta al servidor de producción.**
- Los `.sql` se importan en phpMyAdmin o con `mysql` por consola.
- Los `.php` se escribieron para ejecutarse desde la raíz del proyecto (usan rutas como
  `__DIR__ . '/modelos/...'`); si alguna vez hace falta uno, ajustar esa ruta a `/../modelos/...`
  o copiarlo temporalmente a la raíz y borrarlo después.

## Migraciones pendientes para producción

Ejecutar en este orden, **con respaldo previo de la base de datos**:

1. `ampliar_nombremodulo.sql`: `modulos.nombremodulo` pasa a VARCHAR(255).
2. `crear_pagoclinica.sql`: tabla `pagoclinica` (vouchers de las órdenes de clínica).
3. `eliminar_columna_passwordtexto.php`: elimina `usuario.PasswordTexto` (contraseñas en texto plano). Ya ejecutado en la BD local.
