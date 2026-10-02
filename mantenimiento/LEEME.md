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
4. `crear_recuperacionpassword.sql`: columna `personal.Correo` y tabla `recuperacionpassword` (códigos de recuperación de contraseña por correo).
5. `crear_requisitos.sql`: tablas `requisito` (catálogo, con los 6 requisitos iniciales) y `requisitoinscripcion` (requisitos entregados por cada matriculado).
6. `crear_autoridades.sql`: tabla `autoridad` (Director y Coordinador de Posgrado que firman el Documento Privado de Compromiso; se editan desde la pantalla Autoridades). Por consola importar con `--default-character-set=utf8mb4`, si no los acentos ("García") se guardan mal.
7. `ampliar_requisitos.sql`: columna `requisito.Automatico`, nuevo requisito "Ficha de inscripción" y marca la ficha y el Documento de compromiso como entregados en los matriculados existentes. Importar con `--default-character-set=utf8mb4` si es por consola.
8. `crear_requisitogrado.sql`: tabla `requisitogrado` (a qué grados académicos se pide cada requisito; sin filas = todos los grados).
9. `ampliar_cronogramadefensa.sql`: columna `cronogramadefensa.EstudianteID` (la defensa muestra el título guardado del trabajo de grado), títulos existentes a MAYÚSCULAS y columna `trabajogrado.HoraEntrega` (hora de entrega del informe final).
10. `crear_ordenpagomodulo.sql`: tabla `ordenpagomodulo` (a qué módulo(s) corresponde cada orden de cuota; solo control, no cambia la orden).
