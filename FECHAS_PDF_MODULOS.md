# Implementación de Fechas en PDF de Módulos

## Resumen
Se ha implementado la funcionalidad para solicitar las fechas de inicio y finalización del módulo antes de generar el PDF de calificaciones. Las fechas se muestran en el reporte impreso.

## Cambios Realizados

### 1. JavaScript - calificacion.js

#### Líneas 656-732: Función imprimirReporteCalificaciones
```javascript
function imprimirReporteCalificaciones(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, gradoAcademico, docenteNombre) {
    // Solicitar fechas del módulo antes de generar el PDF
    Swal.fire({
        title: '<i class="la la-calendar"></i> Fechas del Módulo',
        html: `
            // Formulario con 2 campos de fecha
            - Fecha de Inicio
            - Fecha de Finalización
        `,
        preConfirm: () => {
            // Validaciones:
            // 1. Ambas fechas deben estar llenas
            // 2. Fecha inicio <= Fecha fin
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Generar PDF con fechas incluidas en la URL
        }
    });
}
```

**Funcionalidades del Formulario:**

1. **Diseño Atractivo**
   - Icono de calendario en el título
   - Muestra información del módulo (nombre, código, programa)
   - Campos de fecha estilizados
   - Nota informativa sobre el uso de las fechas

2. **Validaciones**
   - **Campos requeridos:** Ambas fechas deben ser ingresadas
   - **Lógica de fechas:** La fecha de inicio debe ser anterior o igual a la fecha de finalización
   - **Mensajes de error:** Validación en tiempo real con mensajes claros

3. **Experiencia de Usuario**
   - Ancho de 600px para mejor visualización
   - Botones claros: "Generar PDF" (azul) y "Cancelar" (gris)
   - Iconos descriptivos en cada campo
   - Focus automático deshabilitado para mejor navegación

### 2. PHP - reporte-calificaciones-pdf.php

#### Líneas 23-42: Recepción y Formateo de Fechas
```php
// Obtener parámetros de fechas
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';

// Formatear fechas al formato español (dd/mm/YYYY)
$fechaInicioFormateada = '';
$fechaFinFormateada = '';

if ($fechaInicio) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaInicio);
    if ($fecha) {
        $fechaInicioFormateada = $fecha->format('d/m/Y');
    }
}

if ($fechaFin) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if ($fecha) {
        $fechaFinFormateada = $fecha->format('d/m/Y');
    }
}
```

**Función:**
- Recibe las fechas en formato ISO (YYYY-MM-DD) desde JavaScript
- Convierte a objeto DateTime para manipulación
- Formatea a formato español (DD/MM/YYYY)
- Maneja casos donde las fechas no están disponibles

#### Líneas 485-491: Mostrar Fechas en el PDF
```php
<tr>
    <td style="border: 1px solid black; padding: 8px;">FECHA:</td>
    <td style="border: 1px solid black; padding: 8px;" colspan="2">
        <strong>Inicio:</strong> <?php echo $fechaInicioFormateada ? $fechaInicioFormateada : '_____________'; ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Finalización:</strong> <?php echo $fechaFinFormateada ? $fechaFinFormateada : '_____________'; ?>
    </td>
</tr>
```

**Función:**
- Muestra ambas fechas en la misma fila de la tabla de información del módulo
- Si las fechas no están disponibles, muestra líneas en blanco para completar manualmente
- Formato claro con etiquetas "Inicio" y "Finalización" en negrita

### 3. Vista - rnotasestudiante.php

#### Línea 222: Actualización de Versión
```html
<script src="vistas/recursos/assets/js/scripts/calificacion.js?v=5.0"></script>
```

**Función:** Fuerza la recarga del archivo JavaScript en el navegador.

## Flujo de Usuario

### 1. Acceder a la Vista
- Usuario accede a "rnotasestudiante"
- Selecciona un docente
- Ve las asignaciones

### 2. Iniciar Impresión
Puede imprimir desde dos lugares:
- **Módulo activo:** Click en botón "Imprimir" (verde) en la tabla principal
- **Módulo cerrado:** Click en botón "Imprimir" en el modal de módulos cerrados

### 3. Formulario de Fechas
Al hacer click en cualquier botón de imprimir:

```
┌─────────────────────────────────────────┐
│  📅 Fechas del Módulo                   │
├─────────────────────────────────────────┤
│                                         │
│  📚 Nombre del Módulo                   │
│  Código: XXX                            │
│  Programa: Nombre del Programa          │
│                                         │
│  ✓ Fecha de Inicio:                    │
│  [___________]  (selector de fecha)     │
│                                         │
│  ✓ Fecha de Finalización:              │
│  [___________]  (selector de fecha)     │
│                                         │
│  ℹ️ Estas fechas se mostrarán en el     │
│     reporte PDF de calificaciones.      │
│                                         │
│  [Cancelar]        [🖨️ Generar PDF]    │
└─────────────────────────────────────────┘
```

### 4. Validaciones en Tiempo Real

#### Caso 1: Campos Vacíos
```
Usuario: Deja una o ambas fechas vacías
Sistema: "Por favor, ingrese ambas fechas"
Acción: No se genera el PDF, permanece en el formulario
```

#### Caso 2: Fecha Inicio > Fecha Fin
```
Usuario: Fecha Inicio = 15/05/2024, Fecha Fin = 10/05/2024
Sistema: "La fecha de inicio debe ser anterior o igual a la fecha de finalización"
Acción: No se genera el PDF, permanece en el formulario
```

#### Caso 3: Fechas Válidas
```
Usuario: Fecha Inicio = 01/05/2024, Fecha Fin = 30/05/2024
Sistema: Genera PDF con las fechas incluidas
Acción: Abre nueva pestaña con el PDF
```

### 5. Visualización en el PDF

El PDF incluye una sección de información del módulo donde se muestran las fechas:

```
┌─────────────────────────────────────────────────────────┐
│ PROGRAMA:  Nombre del Programa                          │
├─────────────────────────────────────────────────────────┤
│ MODULO:    XXX - Nombre del Módulo                      │
├─────────────────────────────────────────────────────────┤
│ DOCENTE:   Nombre del Docente                           │
├─────────────────────────────────────────────────────────┤
│ FECHA:     Inicio: 01/05/2024    Finalización: 30/05/2024│
└─────────────────────────────────────────────────────────┘
```

## Formatos de Fecha

### En JavaScript (input)
- **Formato:** YYYY-MM-DD
- **Ejemplo:** 2024-05-30
- **Control:** `<input type="date">`

### En URL (transporte)
- **Formato:** URL encoded YYYY-MM-DD
- **Ejemplo:** `fechaInicio=2024-05-01&fechaFin=2024-05-30`
- **Método:** `encodeURIComponent()`

### En PHP (procesamiento)
- **Formato entrada:** YYYY-MM-DD (ISO 8601)
- **Formato salida:** DD/MM/YYYY (español)
- **Conversión:** `DateTime::createFromFormat()` y `format()`

### En PDF (visualización)
- **Formato:** DD/MM/YYYY
- **Ejemplo:** 01/05/2024
- **Presentación:** "Inicio: 01/05/2024    Finalización: 30/05/2024"

## Características de Validación

### Frontend (JavaScript)

1. **Validación de Campos Vacíos**
```javascript
if (!fechaInicio || !fechaFin) {
    Swal.showValidationMessage('Por favor, ingrese ambas fechas');
    return false;
}
```

2. **Validación de Rango**
```javascript
if (fechaInicio > fechaFin) {
    Swal.showValidationMessage('La fecha de inicio debe ser anterior o igual a la fecha de finalización');
    return false;
}
```

3. **Retorno de Datos**
```javascript
return { fechaInicio: fechaInicio, fechaFin: fechaFin };
```

### Backend (PHP)

1. **Validación de Existencia**
```php
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
```

2. **Validación de Formato**
```php
$fecha = DateTime::createFromFormat('Y-m-d', $fechaInicio);
if ($fecha) {
    // Fecha válida
}
```

3. **Fallback para Valores Vacíos**
```php
echo $fechaInicioFormateada ? $fechaInicioFormateada : '_____________';
```

## Casos de Uso

### Caso 1: Módulo Regular
**Escenario:** Un módulo que se desarrolló del 1 de mayo al 30 de mayo de 2024

**Flujo:**
1. Usuario hace click en "Imprimir"
2. Ingresa: Inicio = 01/05/2024, Fin = 30/05/2024
3. Click en "Generar PDF"
4. PDF muestra: "Inicio: 01/05/2024    Finalización: 30/05/2024"

### Caso 2: Módulo Intensivo
**Escenario:** Un módulo intensivo de un solo día

**Flujo:**
1. Usuario hace click en "Imprimir"
2. Ingresa: Inicio = 15/06/2024, Fin = 15/06/2024
3. Click en "Generar PDF"
4. PDF muestra: "Inicio: 15/06/2024    Finalización: 15/06/2024"

### Caso 3: Error de Usuario
**Escenario:** Usuario ingresa fecha de fin antes de la fecha de inicio

**Flujo:**
1. Usuario hace click en "Imprimir"
2. Ingresa: Inicio = 30/05/2024, Fin = 01/05/2024
3. Click en "Generar PDF"
4. Sistema muestra error: "La fecha de inicio debe ser anterior o igual a la fecha de finalización"
5. Usuario corrige las fechas
6. Genera PDF exitosamente

### Caso 4: Cancelación
**Escenario:** Usuario decide no generar el PDF

**Flujo:**
1. Usuario hace click en "Imprimir"
2. Ve el formulario de fechas
3. Click en "Cancelar"
4. Formulario se cierra, no se genera PDF
5. Usuario permanece en la vista actual

## Beneficios de la Implementación

### 1. Exactitud de Información
- Las fechas exactas del módulo quedan registradas en el PDF
- Información oficial para auditorías y archivo
- Eliminación de errores de transcripción manual

### 2. Flexibilidad
- Permite ingresar cualquier rango de fechas
- Soporta módulos de diferente duración
- Adaptable a módulos intensivos o extensos

### 3. Validación Robusta
- Previene errores de entrada
- Guía al usuario con mensajes claros
- Garantiza coherencia de datos

### 4. Experiencia de Usuario
- Interfaz intuitiva con SweetAlert2
- Selectores de fecha nativos del navegador
- Feedback inmediato de errores

### 5. Documentación Oficial
- PDFs con información completa
- Formato profesional
- Listo para impresión o archivo digital

## Mejoras Futuras Sugeridas

### 1. Autocompletado de Fechas
```javascript
// Obtener fechas sugeridas desde la base de datos
$.ajax({
    url: 'ajax/modulo.ajax.php',
    data: { accion: 'obtenerFechasModulo', moduloID: moduloID },
    success: function(response) {
        if (response.fechaInicio) {
            $('#fechaInicio').val(response.fechaInicio);
        }
        if (response.fechaFin) {
            $('#fechaFin').val(response.fechaFin);
        }
    }
});
```

### 2. Guardar Fechas en Base de Datos
```sql
ALTER TABLE modulos
ADD COLUMN FechaInicio DATE,
ADD COLUMN FechaFin DATE;
```

### 3. Validación de Fechas Realistas
```javascript
// Validar que las fechas no sean muy antiguas o futuras
const hoy = new Date();
const fechaMax = new Date(hoy.getFullYear() + 1, hoy.getMonth(), hoy.getDate());
const fechaMin = new Date(hoy.getFullYear() - 5, hoy.getMonth(), hoy.getDate());
```

### 4. Formato de Fecha Configurable
```php
// Permitir configurar el formato en settings
$formatoFecha = $_SESSION['FormatoFecha'] ?? 'd/m/Y';
$fechaInicioFormateada = $fecha->format($formatoFecha);
```

### 5. Duración del Módulo
```javascript
// Calcular y mostrar la duración automáticamente
const inicio = new Date(fechaInicio);
const fin = new Date(fechaFin);
const duracionDias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

html += `<p>Duración: ${duracionDias} días</p>`;
```

## Compatibilidad

### Navegadores Soportados
- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Opera 67+

### Características HTML5 Usadas
- `<input type="date">` - Selector de fecha nativo
- SweetAlert2 - Librería externa (CDN)
- JavaScript ES6+ - Funciones flecha, template literals

### PHP Requerido
- PHP 7.0+ (para DateTime::createFromFormat)
- Sesiones PHP habilitadas
- PDO para base de datos

## Pruebas Recomendadas

### 1. Prueba de Validación
```
✓ Dejar ambos campos vacíos → Error
✓ Dejar solo fecha inicio → Error
✓ Dejar solo fecha fin → Error
✓ Fecha inicio > fecha fin → Error
✓ Fecha inicio = fecha fin → Éxito
✓ Fecha inicio < fecha fin → Éxito
```

### 2. Prueba de Formatos
```
✓ Fecha: 01/01/2024 → Formato español correcto
✓ Fecha: 31/12/2024 → Formato español correcto
✓ Fecha: 29/02/2024 → Año bisiesto válido
```

### 3. Prueba de Integración
```
✓ Módulo activo → Imprimir → Formulario → PDF
✓ Módulo cerrado → Modal → Imprimir → Formulario → PDF
✓ Cancelar formulario → No genera PDF
```

### 4. Prueba de PDF
```
✓ PDF contiene fechas formateadas
✓ PDF muestra líneas en blanco si no hay fechas (casos legacy)
✓ PDF imprime correctamente
✓ PDF guarda correctamente como archivo
```

## Archivos Modificados

1. ✅ `vistas/recursos/assets/js/scripts/calificacion.js`
   - Función `imprimirReporteCalificaciones()` completamente reescrita
   - Líneas 656-732

2. ✅ `vistas/componentes/reporte-calificaciones-pdf.php`
   - Recepción de parámetros de fecha (líneas 23-24)
   - Formateo de fechas (líneas 26-42)
   - Visualización en PDF (líneas 485-491)

3. ✅ `vistas/componentes/rnotasestudiante.php`
   - Actualización de versión a 5.0 (línea 222)

## Documentación Relacionada

- `IMPLEMENTACION_MODULOS_CERRADOS.md` - Funcionalidad de módulos cerrados
- `REAPERTURA_MODULOS_ADMIN.md` - Reapertura por administradores

---

**Versión:** 5.0
**Fecha de Implementación:** 2025-12-12
**Estado:** ✅ Completado y Funcional
**Autor:** Sistema de Gestión de Posgrado FCS
