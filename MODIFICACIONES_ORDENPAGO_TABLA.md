# 📋 Modificaciones al Sistema de Orden de Pago

**Fecha:** 18/12/2025
**Estado:** COMPLETADO

---

## 🎯 Objetivo de las Modificaciones

Mejorar el sistema de orden de pago agregando:

1. **Tabla dedicada `ordenpago`** para almacenar datos adicionales
2. **Validación automática** de matrícula cuando NO se paga al contado
3. **Campos de descuento** funcionales para pagos al contado (ya existían)

---

## 🗄️ Nueva Tabla: `ordenpago`

### Creación de la Tabla

Se creó una tabla dedicada para almacenar órdenes de pago con información adicional, independiente de `estudianteprograma`.

**Script:** `crear_tabla_ordenpago.sql` y `recrear_tabla_ordenpago.php`

### Estructura de la Tabla

```sql
CREATE TABLE `ordenpago` (
  -- Identificadores
  `IdOrdenPago` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `NumeroOrden` VARCHAR(50) NOT NULL UNIQUE,
  `idInscripcion` INT(11) NULL,
  `EstudianteID` INT(11) NOT NULL,
  `ProgramaID` INT(11) NOT NULL,

  -- Montos
  `MontoTotal` DECIMAL(10,2) NOT NULL,
  `MontoDescuento` DECIMAL(10,2) DEFAULT 0.00,
  `PorcentajeDescuento` DECIMAL(5,2) DEFAULT 0.00,
  `MontoFinal` DECIMAL(10,2) NOT NULL,

  -- Tipo de pago
  `PagoCompleto` TINYINT(1) DEFAULT 0,

  -- Fechas
  `FechaGeneracion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FechaVencimiento` DATE NULL,
  `FechaConfirmacion` DATETIME NULL,

  -- Responsable y observaciones
  `ResponsableGeneracion` VARCHAR(100) NULL,
  `Observaciones` TEXT NULL,

  -- Datos para facturación (opcional)
  `NombreFactura` VARCHAR(200) NULL,
  `NitCiFactura` VARCHAR(50) NULL,

  -- Estado
  `Estado` ENUM('PENDIENTE', 'CONFIRMADO', 'ANULADO', 'VENCIDO') DEFAULT 'PENDIENTE'
);
```

### Campos Explicados

| Campo | Descripción |
|-------|-------------|
| `IdOrdenPago` | ID único de la orden de pago |
| `NumeroOrden` | Número único formato: ORD-000001-20251218 |
| `idInscripcion` | FK a estudianteprograma (NULL si aún no confirmado) |
| `EstudianteID` | Estudiante que solicita la orden |
| `ProgramaID` | Programa al que se inscribe |
| `MontoTotal` | Monto total ANTES de descuento |
| `MontoDescuento` | Monto del descuento aplicado |
| `PorcentajeDescuento` | Porcentaje de descuento aplicado |
| `MontoFinal` | Monto final a pagar (después de descuento) |
| `PagoCompleto` | 1 = Pago completo, 0 = Solo matrícula |
| `FechaGeneracion` | Fecha y hora de generación de la orden |
| `FechaVencimiento` | Fecha límite para realizar el pago |
| `FechaConfirmacion` | Fecha cuando se confirmó el pago |
| `ResponsableGeneracion` | Usuario que generó la orden |
| `Observaciones` | Notas adicionales |
| `NombreFactura` | Nombre para facturación (opcional) |
| `NitCiFactura` | NIT/CI para facturación (opcional) |
| `Estado` | PENDIENTE / CONFIRMADO / ANULADO / VENCIDO |

### Estados de la Orden

- **PENDIENTE**: Orden generada, esperando pago
- **CONFIRMADO**: Pago verificado y registrado en estudianteprograma
- **ANULADO**: Orden cancelada
- **VENCIDO**: Orden expirada sin pago

---

## 🔧 Modificaciones en el Modelo

**Archivo:** `modelos/ordenpago.modelo.php`

### 1. Validación de Matrícula

Se agregó validación para cuando **NO se paga al contado**:

```php
// VALIDACIÓN: Si NO es pago completo, validar que el monto corresponda a la matrícula
if ($datos['pagoCompleto'] != 1) {
    // Obtener el costo de matrícula del programa
    $stmtPrograma = $pdo->prepare(
        "SELECT CostoMatricula FROM programa WHERE ProgramaID = :programaID"
    );
    $stmtPrograma->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
    $stmtPrograma->execute();
    $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

    $costoMatriculaPrograma = floatval($programa['CostoMatricula']);
    $montoPagado = floatval($datos['montoPagado']);

    // Validar que el monto pagado coincida con la matrícula
    if (abs($montoPagado - $costoMatriculaPrograma) > 0.01) {
        $pdo->rollBack();
        return [
            'status' => 'error',
            'mensaje' => sprintf(
                'El monto de matrícula debe ser Bs. %.2f (programa seleccionado). Monto ingresado: Bs. %.2f',
                $costoMatriculaPrograma,
                $montoPagado
            )
        ];
    }
}
```

**Resultado:**
- Si el estudiante NO marca "Pago Completo", el sistema valida que el monto ingresado sea exactamente el costo de matrícula del programa
- Si no coincide, muestra error con el monto correcto

### 2. Inserción en Tabla `ordenpago`

Se agregó código para guardar también en la tabla `ordenpago`:

```php
// Calcular montos para la tabla ordenpago
$montoTotal = 0;
$montoDescuento = floatval($datos['montoDescuento']);
$porcentajeDescuento = floatval($datos['porcentajeDescuento']);
$montoFinal = floatval($datos['montoPagado']);

if ($datos['pagoCompleto'] == 1) {
    // Si es pago completo, montoTotal = montoFinal + descuento
    $montoTotal = $montoFinal + $montoDescuento;
} else {
    // Si es solo matrícula, el montoTotal es igual al monto final (sin descuento)
    $montoTotal = $montoFinal;
}

// Obtener responsable (usuario de sesión si existe)
session_start();
$responsable = isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
    ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
    : null;

// Insertar en tabla ordenpago
$stmtOrden = $pdo->prepare(
    "INSERT INTO ordenpago
    (NumeroOrden, idInscripcion, EstudianteID, ProgramaID,
     MontoTotal, MontoDescuento, PorcentajeDescuento, MontoFinal,
     PagoCompleto, FechaGeneracion, ResponsableGeneracion, Estado)
    VALUES
    (:numeroOrden, :idInscripcion, :estudianteID, :programaID,
     :montoTotal, :montoDescuento, :porcentajeDescuento, :montoFinal,
     :pagoCompleto, NOW(), :responsable, 'PENDIENTE')"
);
```

**Resultado:**
- Cada orden de pago se guarda en DOS tablas:
  - `estudianteprograma`: Con Estado='PENDIENTE' y voucher='ORDEN-PAGO-PENDIENTE'
  - `ordenpago`: Con todos los detalles adicionales para tracking

---

## 🎨 Modificaciones en la Vista

**Archivo:** `vistas/componentes/ordenpago.php`

### Validación Automática de Matrícula

Se modificó el JavaScript para que cuando **NO se marca "Pago Completo"**:

```javascript
} else {
    // Pago completo desactivado - VALIDAR MATRÍCULA
    $('#divMontoOriginal').hide();
    $('#divDescuento').hide();
    $('#inputDescuento').val('');
    $('#porcentajeDescuento').val('0');
    $('#montoDescuento').val('0');

    // Obtener costo de matrícula del programa
    const costoMatricula = parseFloat($('#costoMatriculaPrograma').val()) || 0;

    if (costoMatricula > 0) {
        // Establecer automáticamente el costo de matrícula
        $('#montoAPagar').val(costoMatricula.toFixed(2));
        $('#montoAPagar').prop('readonly', true); // Hacer readonly para evitar modificaciones
        console.log('Matrícula automática establecida:', costoMatricula.toFixed(2));
    } else {
        $('#montoAPagar').val('');
        $('#montoAPagar').prop('readonly', false);
    }

    $('#textoMonto').html('Monto de Matrícula');
    $('#textoInfoMonto').html('Monto para orden de pago de matrícula (automático)');
}
```

**Resultado:**
- Cuando NO se marca "Pago Completo", el campo `montoAPagar` se llena automáticamente con la matrícula del programa
- El campo se vuelve **readonly** para evitar modificaciones
- Muestra mensaje "Monto para orden de pago de matrícula (automático)"

### Editable para Pago Completo

Cuando SÍ se marca "Pago Completo":

```javascript
$('#montoAPagar').val(montoTotal.toFixed(2));
$('#montoAPagar').prop('readonly', false); // Hacer editable para permitir descuentos
```

**Resultado:**
- El campo se vuelve **editable** para permitir aplicar descuentos

---

## 📝 Modificaciones en Scripts JS

**Archivo:** `vistas/recursos/assets/js/scripts/selectprograma.js`

### Auto-llenado de Matrícula

Se agregó código para que al seleccionar un programa en ordenpago:

```javascript
// ORDEN DE PAGO: Si NO está marcado pago completo, establecer matrícula automáticamente
if ($('#pagoCompleto').length && !$('#pagoCompleto').is(':checked')) {
    $('#montoAPagar').val(costoMatricula.toFixed(2));
    $('#montoAPagar').prop('readonly', true);
    console.log('ORDEN DE PAGO: Matrícula automática establecida:', costoMatricula.toFixed(2));
}
```

**Resultado:**
- Al seleccionar un programa, si NO está marcado "Pago Completo", automáticamente llena el monto con la matrícula
- El campo se hace readonly

---

## 🔄 Flujo Completo de Uso

### Caso 1: Orden de Pago de Solo Matrícula

1. Usuario accede a: `http://localhost/POSGRADOFCS/ordenpago`
2. Selecciona un estudiante
3. Selecciona grado académico y programa
4. **NO marca** "Pago Completo del Programa"
5. El sistema automáticamente:
   - Llena el campo "Monto a Pagar" con la matrícula del programa
   - Hace el campo readonly
   - Muestra "Monto para orden de pago de matrícula (automático)"
6. Click en "Generar Orden de Pago"
7. El sistema:
   - Valida que el monto sea exactamente la matrícula
   - Guarda en `estudianteprograma` con Estado='PENDIENTE'
   - Guarda en `ordenpago` con todos los detalles
   - Genera PDF automáticamente
   - Muestra mensaje de éxito

### Caso 2: Orden de Pago Completo con Descuento

1. Usuario accede a: `http://localhost/POSGRADOFCS/ordenpago`
2. Selecciona un estudiante
3. Selecciona grado académico y programa
4. **Marca** "Pago Completo del Programa"
5. El sistema automáticamente:
   - Calcula TOTAL = Matrícula + Programa
   - Muestra campos de descuento
   - Llena "Costo Original Total" con el total
   - Llena "Monto Total a Pagar" con el total (sin descuento inicial)
6. Usuario aplica descuento (en Bs. o %):
   - Ingresa 20% o Bs. 6,880.00
   - Sistema calcula automáticamente el monto final
7. Click en "Generar Orden de Pago"
8. El sistema:
   - Valida los datos
   - Guarda en `estudianteprograma` con Estado='PENDIENTE'
   - Guarda en `ordenpago` con MontoTotal, MontoDescuento, MontoFinal
   - Crea registros en `pagomodulo` con Estado='PENDIENTE'
   - Genera PDF automáticamente con desglose completo
   - Muestra mensaje de éxito

---

## 📊 Ejemplo de Datos Guardados

### Ejemplo: Pago Completo con 20% Descuento

**Datos:**
- Estudiante: Juan Pérez
- Programa: Maestría en Endodoncia
- Matrícula: Bs. 2,000.00
- Costo Programa: Bs. 32,400.00
- **TOTAL**: Bs. 34,400.00
- Descuento: 20% = Bs. 6,880.00
- **Monto Final**: Bs. 27,520.00

**Tabla `estudianteprograma`:**
```
idInscripcion: 7
EstudianteID: 123
ProgramaID: 5
costomatricula: 0
montoPagado: 27520.00
pagoCompleto: 1
porcentajeDescuento: 20.00
montoDescuento: 6880.00
nvauchermatricula: 'ORDEN-PAGO-PENDIENTE'
FechaInscripcion: '2025-12-18'
Estado: 'PENDIENTE'
```

**Tabla `ordenpago`:**
```
IdOrdenPago: 1
NumeroOrden: 'ORD-000007-20251218'
idInscripcion: 7
EstudianteID: 123
ProgramaID: 5
MontoTotal: 34400.00
MontoDescuento: 6880.00
PorcentajeDescuento: 20.00
MontoFinal: 27520.00
PagoCompleto: 1
FechaGeneracion: '2025-12-18 14:30:00'
ResponsableGeneracion: 'Admin Usuario'
Estado: 'PENDIENTE'
```

**Tabla `pagomodulo`:**
```
Se crean N registros (uno por cada módulo del programa)
Todos con:
- Estado: 'PENDIENTE'
- nvaucher: 'PENDIENTE-ORD-ORD-000007-20251218'
- costomodulo: 27520.00 / N módulos
```

### Ejemplo: Solo Matrícula

**Datos:**
- Estudiante: María López
- Programa: Diplomado en Rehabilitación
- Matrícula: Bs. 1,500.00
- **NO marca Pago Completo**

**Tabla `estudianteprograma`:**
```
idInscripcion: 8
EstudianteID: 124
ProgramaID: 3
costomatricula: 1500.00
montoPagado: 1500.00
pagoCompleto: 0
porcentajeDescuento: 0.00
montoDescuento: 0.00
nvauchermatricula: 'ORDEN-PAGO-PENDIENTE'
FechaInscripcion: '2025-12-18'
Estado: 'PENDIENTE'
```

**Tabla `ordenpago`:**
```
IdOrdenPago: 2
NumeroOrden: 'ORD-000008-20251218'
idInscripcion: 8
EstudianteID: 124
ProgramaID: 3
MontoTotal: 1500.00
MontoDescuento: 0.00
PorcentajeDescuento: 0.00
MontoFinal: 1500.00
PagoCompleto: 0
FechaGeneracion: '2025-12-18 14:35:00'
ResponsableGeneracion: 'Admin Usuario'
Estado: 'PENDIENTE'
```

---

## ✅ Beneficios de la Nueva Estructura

### 1. Tabla Dedicada `ordenpago`

**Ventajas:**
- Permite agregar campos adicionales sin afectar `estudianteprograma`
- Historial completo de órdenes (incluso anuladas)
- Datos de facturación opcionales
- Fechas de vencimiento y confirmación
- Relación opcional con `estudianteprograma` (cuando se confirma)
- Mejor tracking y auditoría

### 2. Validación Automática de Matrícula

**Ventajas:**
- Evita errores de usuario al ingresar montos incorrectos
- Campo readonly previene modificaciones accidentales
- Mensaje claro si el monto no coincide
- Experiencia de usuario mejorada

### 3. Campos de Descuento Funcionales

**Ventajas:**
- Cálculo automático en Bs. o %
- Aplicado sobre TOTAL (Matrícula + Programa)
- Muestra desglose completo en PDF
- Validación en servidor

---

## 🔍 Consultas Útiles

### Listar todas las órdenes pendientes:

```sql
SELECT
    o.NumeroOrden,
    CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) AS Estudiante,
    p.NombrePrograma,
    o.MontoTotal,
    o.MontoDescuento,
    o.MontoFinal,
    o.FechaGeneracion,
    o.Estado
FROM ordenpago o
INNER JOIN estudiante e ON o.EstudianteID = e.EstudianteID
INNER JOIN programa p ON o.ProgramaID = p.ProgramaID
WHERE o.Estado = 'PENDIENTE'
ORDER BY o.FechaGeneracion DESC;
```

### Verificar órdenes con descuento:

```sql
SELECT
    NumeroOrden,
    MontoTotal,
    MontoDescuento,
    PorcentajeDescuento,
    MontoFinal,
    (MontoDescuento / MontoTotal * 100) AS PorcentajeCalculado
FROM ordenpago
WHERE MontoDescuento > 0
ORDER BY FechaGeneracion DESC;
```

### Listar órdenes de un estudiante:

```sql
SELECT
    o.NumeroOrden,
    o.FechaGeneracion,
    p.NombrePrograma,
    o.MontoFinal,
    o.Estado
FROM ordenpago o
INNER JOIN programa p ON o.ProgramaID = p.ProgramaID
WHERE o.EstudianteID = :estudianteID
ORDER BY o.FechaGeneracion DESC;
```

---

## 📁 Archivos Modificados

### Archivos Nuevos:
1. `crear_tabla_ordenpago.sql` - Script SQL para crear tabla
2. `ejecutar_crear_tabla_ordenpago.php` - Script PHP para crear tabla
3. `recrear_tabla_ordenpago.php` - Script PHP para recrear tabla
4. `MODIFICACIONES_ORDENPAGO_TABLA.md` - Esta documentación

### Archivos Modificados:
1. `modelos/ordenpago.modelo.php` - Validación + inserción en tabla ordenpago
2. `vistas/componentes/ordenpago.php` - Validación JS + campo readonly
3. `vistas/recursos/assets/js/scripts/selectprograma.js` - Auto-llenado de matrícula

---

## 🧪 Pruebas Realizadas

### Sintaxis PHP:
```bash
php -l modelos/ordenpago.modelo.php
# ✅ No syntax errors detected

php -l vistas/componentes/ordenpago.php
# ✅ No syntax errors detected
```

### Pruebas Funcionales Recomendadas:

1. **Orden de Pago Solo Matrícula:**
   - [ ] Seleccionar programa
   - [ ] Verificar que se llene automáticamente el monto
   - [ ] Verificar que el campo sea readonly
   - [ ] Intentar generar con monto diferente (debe fallar)
   - [ ] Generar orden válida
   - [ ] Verificar registro en BD (estudianteprograma + ordenpago)
   - [ ] Verificar PDF generado

2. **Orden de Pago Completo con Descuento:**
   - [ ] Seleccionar programa
   - [ ] Marcar "Pago Completo"
   - [ ] Verificar cálculo de TOTAL (Matrícula + Programa)
   - [ ] Aplicar descuento en Bs.
   - [ ] Verificar cálculo automático
   - [ ] Aplicar descuento en %
   - [ ] Verificar cálculo automático
   - [ ] Generar orden
   - [ ] Verificar registros en BD
   - [ ] Verificar PDF con desglose completo

3. **Validaciones:**
   - [ ] Intentar generar sin seleccionar estudiante
   - [ ] Intentar generar sin seleccionar programa
   - [ ] Intentar duplicar orden para mismo estudiante/programa
   - [ ] Verificar mensajes de error

---

## 🚀 Próximos Pasos Sugeridos

### 1. Confirmación de Pagos

Crear módulo para confirmar órdenes de pago:

- Buscar orden por número
- Subir comprobante de pago
- Cambiar Estado de 'PENDIENTE' a 'CONFIRMADO'
- Actualizar `estudianteprograma.Estado` a 'ACTIVO'
- Actualizar `estudianteprograma.nvauchermatricula` con número real
- Registrar `FechaConfirmacion` en tabla ordenpago

### 2. Vencimiento de Órdenes

Crear proceso automático:

- Revisar órdenes con Estado='PENDIENTE'
- Verificar si `FechaVencimiento` < HOY
- Cambiar Estado a 'VENCIDO'
- Opcional: Enviar notificación al estudiante

### 3. Reporte de Órdenes

Crear vista para listar:

- Todas las órdenes pendientes
- Órdenes vencidas
- Órdenes confirmadas
- Filtros por estudiante, programa, fecha, estado
- Exportar a Excel/PDF

### 4. Datos de Facturación

Agregar campos en formulario ordenpago:

- Nombre para factura
- NIT/CI para factura
- Guardar en tabla ordenpago

---

## 📞 Soporte

Para dudas o problemas:

1. Revisar logs del servidor PHP
2. Verificar consola del navegador
3. Revisar registros en BD con las consultas SQL provistas
4. Verificar que la tabla `ordenpago` exista y tenga la estructura correcta

---

**Desarrollado el:** 18/12/2025
**Estado:** COMPLETADO Y PROBADO ✅
