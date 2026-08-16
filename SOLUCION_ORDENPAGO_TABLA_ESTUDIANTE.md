# Solución: Tabla de Estudiante no se muestra en Orden de Pago

## Diagnóstico del Problema

El problema reportado es que al seleccionar un estudiante en la orden de pago, no se muestra la tabla con sus datos.

### Pasos para Diagnosticar:

1. **Abrir la consola del navegador** (F12 en Chrome/Firefox)
2. **Ir a ordenpago** en el sistema
3. **Seleccionar un estudiante** del dropdown
4. **Verificar en la consola** qué mensajes aparecen

### Mensajes esperados en la consola:

```
=== INICIANDO ORDEN DE PAGO ===
jQuery version: X.X.X
Select2 disponible: function
Select2 inicializado en elementos: 1
ID del select de estudiante: selectEstudiante
Tiene clase kt-select2-general: true
=== EVENTO DISPARADO ===
Tipo de evento: change
Estudiante ID seleccionado: XX
Valor del select: XX
Respuesta del servidor: {objeto con datos}
```

## Posibles Causas y Soluciones:

### 1. El evento no se dispara
**Síntoma**: No aparece "=== EVENTO DISPARADO ===" en la consola
**Solución**: El problema está en Select2

### 2. El AJAX falla
**Síntoma**: Aparece "Error AJAX" en la consola
**Solución**: Verificar ajax/estudiantes.ajax.php

### 3. La respuesta tiene error
**Síntoma**: Aparece response.error en la consola
**Solución**: Verificar que el estudiante existe en la base de datos

### 4. La tabla no se muestra
**Síntoma**: Los datos se reciben pero la tabla no aparece
**Solución**: Verificar CSS display:none

## Archivos de Prueba Creados:

He creado dos archivos de prueba para diagnosticar el problema:

1. **test_ordenpago_estudiante.php** - Test completo con consola visual
2. **test_ajax_estudiante_simple.php** - Test simple del AJAX

### Cómo usar los archivos de prueba:

1. Abrir en el navegador: `http://localhost/POSGRADOFCS/test_ajax_estudiante_simple.php`
2. Seleccionar un estudiante
3. Hacer clic en "Probar AJAX Directo"
4. Ver el resultado en pantalla

## Solución Rápida:

Si el AJAX funciona en los archivos de prueba pero no en ordenpago.php, el problema podría ser:

### Opción A: Inicialización de Select2

El Select2 podría estar interfiriendo con el evento change. Solución:

```javascript
// Cambiar esto (línea 440):
$('#selectEstudiante').on('select2:select change', function(e) {

// Por esto:
$('#selectEstudiante').on('select2:select', function(e) {
```

### Opción B: Orden de inicialización

El evento se registra ANTES de inicializar Select2. Solución:

Mover la inicialización de Select2 DESPUÉS del registro del evento.

### Opción C: Usar evento select2:selecting

```javascript
$('#selectEstudiante').on('select2:selecting', function(e) {
    const estudianteID = e.params.args.data.id;
    // resto del código...
});
```

## Código Mejorado:

Ver archivo: `ordenpago_mejorado.js` (próximo a crear)

## Verificación Final:

1. ✓ PHP sin errores de sintaxis
2. ✓ IDs del HTML coinciden con JavaScript
3. ✓ AJAX configurado correctamente
4. ✓ Event listeners registrados

El problema más probable es la inicialización de Select2 o el evento change.
