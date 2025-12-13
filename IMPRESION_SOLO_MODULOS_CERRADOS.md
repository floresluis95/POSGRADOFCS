# Impresión Solo en Módulos Cerrados

## Resumen
Se ha modificado la funcionalidad para que el botón de imprimir PDF **solo aparezca en módulos cerrados**, no en módulos activos. Esto asegura que solo se puedan imprimir reportes de módulos que ya han sido validados y cerrados.

## Problema Solucionado

### Antes
- ❌ El botón de imprimir aparecía en **todos** los módulos (activos y cerrados)
- ❌ Se podían imprimir reportes de módulos aún en proceso
- ❌ No había diferenciación entre módulos activos y cerrados

### Después
- ✅ El botón de imprimir **solo aparece en módulos cerrados**
- ✅ Módulos activos solo muestran botón "Evaluar/Editar"
- ✅ Clara diferenciación de funcionalidades por estado del módulo

## Cambios Realizados

### 1. JavaScript - calificacion.js (Líneas 230-242)

#### Antes:
```javascript
html += '<td class="text-center">';
html += '<div class="btn-group" role="group">';

// Botón Evaluar
html += '<button class="btn btn-sm btn-brand btn-evaluar">...</button>';

// Botón Imprimir (aparecía en todos los módulos)
html += '<button class="btn btn-sm btn-success btn-imprimir-reporte">...</button>';

html += '</div>';
html += '</td>';
```

#### Después:
```javascript
html += '<td class="text-center">';

// Solo botón de evaluar para módulos activos
html += '<button class="btn btn-sm btn-brand btn-evaluar">...</button>';

html += '</td>';
```

**Cambio:** Se removió completamente el botón de imprimir de los módulos activos.

### 2. Event Listeners Removidos (Líneas 283-294)

#### Antes:
```javascript
// Event listener para botones de imprimir en módulos activos
$('.btn-imprimir-reporte').on('click', function() {
    // ... código para imprimir
});
```

#### Después:
```javascript
// Removido - ya no hay botones de imprimir en módulos activos
```

**Cambio:** Se eliminó el event listener innecesario.

### 3. Modal de Módulos Cerrados (Sin Cambios)

El modal de módulos cerrados **mantiene** el botón de imprimir:

```javascript
// Botón de imprimir (siempre visible en módulos cerrados)
html += `<button class="btn btn-sm btn-success btn-imprimir-cerrado">
    <i class="la la-print"></i> Imprimir
</button>`;

// Botón de reabrir (solo para administradores)
if (esAdministrador) {
    html += `<button class="btn btn-sm btn-warning btn-reabrir-modulo">
        <i class="la la-unlock"></i> Reabrir
    </button>`;
}
```

## Estructura de Botones por Estado

### Módulos ACTIVOS (Tabla Principal)

```
┌─────────────────────────────────────────┐
│ Módulo: Fundamentos de Odontología     │
├─────────────────────────────────────────┤
│ Estado: ACTIVO                          │
│                                         │
│ Botones disponibles:                    │
│ [Evaluar/Editar] ← Solo este botón     │
└─────────────────────────────────────────┘
```

### Módulos CERRADOS (Modal)

```
┌─────────────────────────────────────────┐
│ Módulo: Anatomía Dental                │
├─────────────────────────────────────────┤
│ Estado: CERRADO                         │
│ Cerrado por: Dr. Juan Pérez             │
│ Fecha: 15/05/2024                       │
│                                         │
│ Botones disponibles:                    │
│ [Imprimir]  [Reabrir] ← Admin only     │
└─────────────────────────────────────────┘
```

## Flujo de Usuario

### Escenario 1: Ver Módulos Activos

1. Usuario accede a "rnotasestudiante"
2. Selecciona un docente
3. Ve tabla con módulos activos
4. Cada módulo muestra **solo** botón "Evaluar" o "Editar"
5. **No hay** botón de imprimir visible

### Escenario 2: Ver Módulos Cerrados

1. Usuario accede a "rnotasestudiante"
2. Selecciona un docente
3. Ve botón "Ver Módulos Cerrados (X)"
4. Click en el botón
5. Abre modal con módulos cerrados
6. Cada módulo muestra:
   - ✅ Botón "Imprimir" (verde)
   - ✅ Botón "Reabrir" (amarillo, solo admin)

### Escenario 3: Imprimir Módulo Cerrado

1. En el modal de módulos cerrados
2. Click en botón "Imprimir"
3. Aparece formulario de fechas
4. Usuario ingresa:
   - Fecha de Inicio
   - Fecha de Finalización
5. Click en "Generar PDF"
6. Se abre nueva pestaña con el PDF
7. PDF muestra:
   - Información del módulo
   - Fechas ingresadas
   - Tabla de calificaciones
   - Firmas

## Razones del Cambio

### 1. Coherencia de Datos
- Los módulos activos pueden tener calificaciones incompletas
- Solo módulos cerrados tienen datos finales y verificados
- Previene impresión de reportes preliminares

### 2. Control de Calidad
- Asegura que solo se impriman reportes oficiales
- Los módulos cerrados han sido validados
- Información confiable para auditorías

### 3. Flujo de Trabajo Claro
- Separación clara: Activos = Edición, Cerrados = Consulta/Impresión
- Previene confusión sobre qué módulos están finalizados
- Mejora experiencia de usuario

### 4. Seguridad
- Evita impresión accidental de datos temporales
- Solo usuarios autorizados pueden cerrar módulos
- Reportes impresos = Documentos oficiales

## Comparación de Funcionalidades

| Funcionalidad | Módulos Activos | Módulos Cerrados |
|--------------|-----------------|------------------|
| Ver en tabla principal | ✅ Sí | ❌ No (solo en modal) |
| Botón "Evaluar/Editar" | ✅ Sí | ❌ No |
| Botón "Imprimir" | ❌ **No** | ✅ **Sí** |
| Botón "Reabrir" | ❌ No | ✅ Sí (solo admin) |
| Editar calificaciones | ✅ Sí | ❌ No (excepto admin reabre) |
| Generar PDF | ❌ No | ✅ Sí |

## Proceso de Cierre de Módulo

Para que un módulo pase de ACTIVO a CERRADO:

```
1. Módulo ACTIVO
   ↓
2. Docente/Admin registra todas las calificaciones
   ↓
3. Docente/Admin verifica datos
   ↓
4. Click en "Validar y Cerrar" (en notasdocente)
   ↓
5. Confirmación
   ↓
6. Módulo pasa a estado CERRADO
   ↓
7. Ahora aparece en modal "Módulos Cerrados"
   ↓
8. Botón "Imprimir" disponible
```

## Archivo de Test

### test_pdf_calificaciones.php

Este archivo permite verificar:

1. ✅ Existencia del archivo PDF
2. ✅ Módulos con calificaciones en BD
3. ✅ URLs de prueba para generar PDF
4. ✅ Formulario de fechas funcional
5. ✅ Imágenes (logos) disponibles

**Uso:**
```
http://localhost/POSGRADOFCS/test_pdf_calificaciones.php
```

## Verificación de Cambios

### Checklist de Pruebas

- [ ] Acceder a rnotasestudiante
- [ ] Seleccionar docente con módulos activos
- [ ] Verificar que **no** aparece botón "Imprimir" en tabla principal
- [ ] Verificar que **sí** aparece botón "Evaluar/Editar"
- [ ] Click en "Ver Módulos Cerrados"
- [ ] Verificar que aparece modal con módulos cerrados
- [ ] Verificar que **sí** aparece botón "Imprimir" en modal
- [ ] Click en "Imprimir" de un módulo cerrado
- [ ] Verificar que aparece formulario de fechas
- [ ] Ingresar fechas válidas
- [ ] Click en "Generar PDF"
- [ ] Verificar que se abre PDF en nueva pestaña
- [ ] Verificar que PDF contiene fechas ingresadas

## Archivos Modificados

1. ✅ `vistas/recursos/assets/js/scripts/calificacion.js`
   - Líneas 230-242: Removido botón imprimir de módulos activos
   - Líneas 266-284: Removido event listener innecesario
   - Versión: 6.0

2. ✅ `vistas/componentes/rnotasestudiante.php`
   - Línea 222: Actualizado a versión 6.0

3. ✅ `test_pdf_calificaciones.php`
   - Nuevo archivo de diagnóstico

## Solución de Problemas

### Problema: No aparece botón "Ver Módulos Cerrados"

**Solución:**
- No hay módulos cerrados para ese docente
- Cierra un módulo desde "notasdocente" primero

### Problema: PDF no se genera al hacer click

**Diagnóstico:**
1. Abre consola del navegador (F12)
2. Verifica errores en consola
3. Verifica que no haya bloqueador de pop-ups
4. Usa `test_pdf_calificaciones.php` para diagnóstico

**Soluciones:**
- Permitir pop-ups en el navegador
- Verificar que existe `reporte-calificaciones-pdf.php`
- Verificar que hay calificaciones en el módulo

### Problema: PDF se genera pero está vacío

**Diagnóstico:**
- El módulo no tiene calificaciones registradas

**Solución:**
- Registra al menos una calificación en el módulo
- Verifica en `test_pdf_calificaciones.php` la tabla de módulos

### Problema: Fechas no aparecen en PDF

**Diagnóstico:**
- No se completó el formulario de fechas
- Error en el formato de fechas

**Solución:**
- Asegúrate de ingresar ambas fechas
- Verifica que Fecha Inicio <= Fecha Fin
- Revisa la consola del navegador

## Mejoras Futuras Sugeridas

### 1. Vista Previa del PDF
```javascript
// Mostrar preview antes de imprimir
function mostrarPreviewPDF(url) {
    Swal.fire({
        title: 'Vista Previa del PDF',
        html: '<iframe src="' + url + '" style="width:100%;height:500px;"></iframe>',
        width: '80%',
        showCloseButton: true
    });
}
```

### 2. Guardar Fechas en Base de Datos
```sql
ALTER TABLE modulos
ADD COLUMN FechaInicio DATE,
ADD COLUMN FechaFin DATE;
```

### 3. Enviar PDF por Email
```javascript
function enviarPDFEmail(moduloID) {
    // Solicitar email del destinatario
    // Generar PDF
    // Enviar por email
}
```

### 4. Historial de Impresiones
```sql
CREATE TABLE historial_impresiones (
    IdImpresion INT AUTO_INCREMENT PRIMARY KEY,
    ModuloID INT,
    UsuarioID VARCHAR(20),
    FechaImpresion DATETIME,
    FOREIGN KEY (ModuloID) REFERENCES modulos(Idmodulo)
);
```

## Compatibilidad

### Navegadores Soportados
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+

### Versiones Previas
- Esta funcionalidad **reemplaza** el comportamiento anterior
- Los módulos activos ya **no** pueden imprimir reportes
- Mantiene **compatibilidad completa** con módulos cerrados

---

**Versión:** 6.0
**Fecha de Implementación:** 2025-12-12
**Estado:** ✅ Completado
**Impacto:** Alto - Cambia comportamiento de impresión
**Compatibilidad:** Rompe compatibilidad con versión anterior (intencional)
