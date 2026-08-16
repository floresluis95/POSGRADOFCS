# Solución: Conflicto SweetAlert y Generación de PDF

## Problema Identificado

El PDF de la Orden de Pago no se generaba debido a un **conflicto entre SweetAlert v1 y SweetAlert v2**.

### Causa Raíz

El sistema estaba cargando AMBAS versiones de SweetAlert simultáneamente:

1. **plantilla.php** (líneas 169-170):
   ```javascript
   <script src="vistas/recursos/assets/vendors/general/sweetalert2/dist/sweetalert2.min.js"></script>
   ```

2. **ordenpago.php** (línea 546 - ANTES):
   ```javascript
   <script src="vistas/recursos/sweetalert.min.js"></script>
   ```

Esto causaba errores de JavaScript como:
- `Uncaught TypeError: can't access property 'constructor', this is undefined`
- Impedía que se ejecutara el código para abrir el PDF

## Solución Aplicada

### Cambio Realizado

**Archivo**: `vistas/componentes/ordenpago.php`
**Línea**: 546
**Acción**: Eliminada la carga duplicada de SweetAlert v1

**ANTES:**
```html
<!-- SweetAlert v1 (para alertas) -->
<script src="vistas/recursos/sweetalert.min.js" type="text/javascript"></script>
```

**DESPUÉS:**
```html
<!-- SweetAlert ya está cargado por plantilla.php (SweetAlert2 con compatibilidad v1) -->
```

### ¿Por qué funciona?

SweetAlert2 mantiene **compatibilidad con la sintaxis de SweetAlert v1**, por lo que las llamadas `swal()` en el controlador funcionarán correctamente sin necesidad de cambiar código.

## Cómo Probar la Solución

### Paso 1: Verificar que no hay errores de consola

1. Abrir en el navegador: `http://localhost/POSGRADOFCS/ordenpago`
2. Presionar **F12** para abrir Developer Tools
3. Ir a la pestaña **Console**
4. **NO DEBE APARECER**:
   - ❌ `can't access property 'constructor'`
   - ❌ `sweetalert2.min.js` errors

### Paso 2: Probar el flujo completo

1. **Seleccionar un estudiante** del dropdown
   - ✅ Debe aparecer la tabla con los datos del estudiante
   - ✅ Debe mostrarse "PASO 2: SELECCIONAR PROGRAMA"

2. **Seleccionar un programa**
   - ✅ Debe mostrarse "PASO 3: CONFIGURAR PAGO"
   - ✅ Debe mostrarse "PASO 4: DATOS ADICIONALES DE FACTURACIÓN"

3. **Completar datos de pago**:
   - Tipo de pago (Solo matrícula / Pago completo)
   - Descuento (si aplica)
   - Monto a pagar

4. **Completar datos de facturación**:
   - ✅ Nombre para Factura
   - ✅ NIT o CI
   - ✅ Responsable
   - ✅ Firma (opcional)

5. **Hacer clic en "Generar Orden de Pago"**

### Resultado Esperado

1. ✅ Debe aparecer SweetAlert con mensaje: **"EXITOSO! Orden de Pago registrada. Se generará el PDF automáticamente."**

2. ✅ Después de 1-2 segundos, debe **abrirse una nueva pestaña** con el PDF

3. ✅ El PDF debe contener:
   - **ORIGINAL** (parte superior)
   - **COPIA** (parte inferior)
   - Todos los datos del estudiante
   - Datos del programa
   - Monto en números y letras
   - Datos de facturación (Nombre, NIT/CI)
   - Responsable y firma
   - Número de orden (ORD-XXXXXX)

4. ✅ Después de abrir el PDF, debe **redirigir automáticamente** a la página de ordenpago

### Verificar en Base de Datos

Abrir phpMyAdmin y ejecutar:

```sql
-- Ver última inscripción registrada
SELECT
    ep.*,
    e.Nombre, e.Apaterno, e.Amaterno,
    p.NombrePrograma
FROM estudianteprograma ep
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
ORDER BY ep.idInscripcion DESC
LIMIT 1;
```

**Verificar**:
- ✅ `costomatricula` tiene el valor correcto
- ✅ `montoPagado` tiene el valor correcto
- ✅ `pagoCompleto` es 0 (solo matrícula) o 1 (pago completo)
- ✅ `porcentajeDescuento` y `montoDescuento` reflejan el descuento aplicado
- ✅ `FechaInscripcion` está registrada correctamente

## Verificación de Logs

Si el PDF aún no se genera, revisar los logs de Apache:

```
C:\xampp\apache\logs\error.log
```

**Buscar**:
- `RegistrarOrdenPagoControlador ejecutado` - Confirma que el controlador se ejecutó
- `POST registrarOrdenPago detectado` - Confirma que el POST fue recibido
- `Datos de orden de pago preparados` - Muestra los datos antes de insertar
- `Enviando formulario PDF...` - Confirma que el JavaScript se ejecutó

## Archivos Modificados

1. ✅ **vistas/componentes/ordenpago.php** - Eliminada carga duplicada de SweetAlert v1
2. ✅ **controladores/ordenpago.controlador.php** - Ya tenía los wrappers `window.addEventListener("load")`

## Archivos de Diagnóstico Disponibles

Si necesitas más ayuda para diagnosticar:
- 📄 `DIAGNOSTICO_ORDENPAGO_PDF.md` - Guía completa de diagnóstico paso a paso
- 📄 `test_pdf_simple.php` - Verificar que TCPDF funciona correctamente

## Notas Importantes

- ✅ SweetAlert2 está cargado globalmente por `plantilla.php`
- ✅ La sintaxis `swal()` funciona con SweetAlert2 (compatibilidad backward)
- ✅ No es necesario cambiar el código del controlador
- ✅ El PDF se genera en formato single-page con ORIGINAL y COPIA

## Solución de Problemas Adicionales

### Si el PDF aún no se genera:

1. **Verificar que TCPDF está instalado**:
   ```
   http://localhost/POSGRADOFCS/test_pdf_simple.php
   ```

2. **Verificar que la sesión está activa**:
   - Asegurarse de estar logueado en el sistema

3. **Verificar permisos de archivos**:
   - Apache debe poder leer `vistas/componentes/generar-orden-pago-pdf.php`
   - Apache debe poder leer `vendor/tecnickcom/tcpdf/`

4. **Limpiar cache del navegador**:
   - Ctrl + Shift + Delete
   - Borrar cache y cookies

### Si aparece página en blanco al generar PDF:

Habilitar errores PHP temporalmente en `php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```

Reiniciar Apache y volver a intentar.

## Próximos Pasos

Una vez confirmado que el PDF se genera correctamente:

1. ✅ Verificar que todos los campos nuevos aparecen en el PDF
2. ✅ Verificar el formato de la página (ORIGINAL y COPIA)
3. ✅ Probar con diferentes tipos de pago (solo matrícula vs. pago completo)
4. ✅ Probar con descuentos aplicados
5. ✅ Verificar que los montos en letras se generan correctamente

---

**Fecha de solución**: 19/12/2025
**Problema**: Conflicto SweetAlert v1/v2 impidiendo generación de PDF
**Solución**: Eliminada carga duplicada de SweetAlert v1 de ordenpago.php
