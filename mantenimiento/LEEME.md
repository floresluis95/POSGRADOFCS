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
19. `monto_cuota_plan.sql`: columnas `ordenpago.PlanPropuestoMontoCuota` y `pagoprograma.MontoCuota` (monto de cada cuota del programa pactado en Pre-Registro: redondeado hacia arriba a decenas, la última cuota ajusta; se puede cambiar marcando "Modificar cuota") y `ordenpago.PlanPropuestoMontoPrograma` (total del programa escrito a mano en Bs. en vez del % de descuento). Ya ejecutado en la BD local.
20. `ordenpago_voucher_extraviado.sql`: estado `EXTRAVIADO` en `ordenpago.Estado` y columnas `MotivoExtraviado`, `FechaExtraviado` y `ResponsableExtraviado` (orden de cuota o clínica cuyo voucher el estudiante perdió: deja de contar como pendiente para generar la siguiente; el voucher se registra después en esa misma orden). Ya ejecutado en la BD local.
21. `documentoemitido_monto.sql`: columna `documentoemitido.Monto` (monto pagado por cada certificado; historial de pagos de certificados en Registro de documentos). Los precios por tipo y grado académico se guardan en `configuracion` (claves `COSTO_CERT_<TIPO>_<GRADO>`) desde la pantalla Costos de certificados: no hace falta cargarlos por SQL. Ya ejecutado en la BD local.
22. `firma_orden_estudiante.sql`: columna `ordenpago.GeneradaPorEstudiante` (orden de cuota que genera el propio estudiante desde Mis órdenes de pago) y tabla `imagenfirma` (firma del coordinador y sello de Posgrado, en imagen, que se imprimen solo en esas órdenes; se cargan desde Configuración). Ya ejecutado en la BD local.
23. `crear_solicitudprocesada.sql`: tabla `solicitudprocesada` (control de envíos repetidos por corte de internet: si la respuesta no llegó y el usuario vuelve a enviar los mismos datos, se devuelve la respuesta del primer envío en vez de registrar todo otra vez; las filas se borran solas a los 2 días). Mientras no exista, el sistema funciona igual pero sin ese control. Ya ejecutado en la BD local.
24. `crear_calificacionreseteo.sql`: tabla `calificacionreseteo` (copia de las notas de un módulo antes de resetearlas desde Reporte de Notas, con quién lo hizo, cuándo y el motivo). Ya ejecutado en la BD local.
25. `crear_intentoingreso.sql`: tabla `intentoingreso` (límite de intentos de ingreso: con 5 contraseñas incorrectas en 15 minutos ese usuario queda bloqueado 15 minutos). Mientras no exista, el login funciona igual pero sin el límite. Ya ejecutado en la BD local.
26. `crear_certificadosindocumento.sql`: certificados que el sistema no genera (solo se registra el pago). Tabla `certificadosindocumento` (catálogo de nombres con precio sugerido, con PRO BIBLIOTECA; lo mantiene el ADM en Registro de documentos) y columnas `documentoemitido.NombreCertificado` y `documentoemitido.FechaPago` (fecha de pago del voucher). Se registran en `documentoemitido` con `TipoDocumento = 'SIN_DOCUMENTO'`. Importar con `--default-character-set=utf8mb4` si es por consola. Ya ejecutado en la BD local.
27. `crear_certificadotitulacion.sql`: tabla `certificadotitulacion` (qué certificados exige cada grado académico para la titulación; se marca en Costos de certificados) y columna `documentoemitido.IdCertificadoSD` (a qué certificado sin documento del catálogo corresponde cada registro; la migración la completa por nombre en los ya registrados). Ya ejecutado en la BD local.
28. `ordenpago_motivo_anulacion.sql`: columnas `ordenpago.MotivoAnulacion`, `FechaAnulacion` y `ResponsableAnulacion` (al anular una orden de cuota o clínica desde Matriculados se pide el motivo; se ve en Órdenes de pago). Necesaria: sin estas columnas la pantalla Órdenes de pago no carga (anular sí funciona, pero sin guardar el motivo). Ya ejecutado en la BD local.
29. `crear_avisocalificacion.sql`: tabla `avisocalificacion` (avisos del Administrador a los docentes para que registren las calificaciones de módulos ya terminados; se envían desde Registrar Notas y el docente los ve en su panel con "Entendido"). Mientras no exista, la lista de módulos pendientes se ve igual pero no se pueden enviar avisos. Ya ejecutado en la BD local.

## Configuración del servidor de producción

En el `php.ini` del servidor (Ubuntu: `/etc/php/8.3/apache2/php.ini`), además de la zona horaria:

```ini
display_errors = Off
log_errors = On
date.timezone = America/La_Paz
session.cookie_httponly = 1
session.cookie_samesite = Lax
session.use_strict_mode = 1
; Solo cuando el sitio ya funcione con HTTPS (con http el login deja de funcionar):
session.cookie_secure = 1
```

El `.htaccess` de la raíz ya pone `httponly`, `samesite` y `use_strict_mode` cuando PHP corre como
módulo de Apache, pero `cookie_secure` va solo en el servidor porque en XAMPP local no hay HTTPS.
