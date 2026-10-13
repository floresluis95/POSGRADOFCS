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
11. `crear_configuracion.sql`: tabla `configuracion` (parámetros editables por el ADM; NOTA_APROBACION = 76). Importar con `--default-character-set=utf8mb4` si es por consola.
12. `usuario_unico.sql`: índice único en `usuario.Usuario` (no puede haber dos cuentas con el mismo nombre de usuario). Antes revisa que no haya repetidos.
13. `profesion_unica.sql`: `profesion.NombreProfesion` pasa a VARCHAR(100) (antes los nombres largos se cortaban y quedaban repetidos) e índice único. Antes revisa que no haya repetidos.
14. `mayusculas_estudiante.sql`: datos del estudiante en MAYÚSCULAS también con tildes ("POTOSí" → "POTOSÍ"). Importar con `--default-character-set=utf8` si es por consola.
15. `estudiante_indices_correo.sql`: quita los índices repetidos `EmailPersonal` y `Correo_2` de `estudiante.Correo` (queda `Correo`, el correo sigue siendo único).
16. `documentoemitido_aprobacion.sql`: columnas `documentoemitido.Datos` (datos con que se generó cada documento, para reimprimirlo y corregirlo desde Registro de documentos), `Estado` (PENDIENTE / APROBADO por el decano), `FechaAprobacion` y `UsuarioAprobadorID`. Ya ejecutado en la BD local.
17. `ordenpago_codigo_grupo.sql`: columna `ordenpago.PlanPropuestoCodigoGrupo` (el Plan Grupal se arma en Pre-Registro: las órdenes de un mismo grupo comparten el código, que pasa a `pagoprograma.CodigoGrupo` al validar el voucher). Ya ejecutado en la BD local.
18. `firmante_mayusculas.sql`: nombres de los responsables de la firma (`firmante.Nombre` y `ordenpago.Firma`) en MAYÚSCULAS. Importar con `--default-character-set=utf8mb4` si es por consola. Ya ejecutado en la BD local.
