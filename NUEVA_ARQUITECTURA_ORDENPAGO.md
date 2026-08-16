# Nueva Arquitectura: Orden de Pago con Tabla Independiente

## Cambio Fundamental

**ANTES**: La orden de pago creaba un registro en `estudianteprograma` con estado PENDIENTE, causando conflictos y duplicados.

**AHORA**: La orden de pago se registra **SOLO** en la tabla `ordenpago`, sin tocar `estudianteprograma` hasta que el estudiante pague.

## Beneficios de la Nueva Arquitectura

✅ **Sin conflictos**: `estudianteprograma` solo tiene inscripciones reales (pagadas)
✅ **Más limpio**: Separación clara entre "orden generada" y "pago confirmado"
✅ **Más flexible**: Puedes generar múltiples órdenes sin problemas
✅ **Mejor trazabilidad**: Todas las órdenes quedan registradas en `ordenpago`
✅ **Más simple**: No hay que anular/eliminar órdenes pendientes en `estudianteprograma`

## Flujo Completo

### 1. Generar Orden de Pago (NUEVO)

Cuando el estudiante genera una orden:

```
Usuario → Formulario → Controlador → Modelo
                                        ↓
                                  INSERT en ordenpago
                                  (estado='PENDIENTE')
                                        ↓
                                  Generar PDF
```

**NO se toca** `estudianteprograma`

### 2. Estudiante Paga (FUTURO - A implementar)

Cuando el estudiante presenta el voucher en caja:

```
Caja → Sistema de Pagos → UPDATE ordenpago
                             (estado='CONFIRMADO')
                                    ↓
                          INSERT en estudianteprograma
                             (estado='ACTIVO')
```

### 3. Ver Órdenes Pendientes

Las órdenes pendientes se consultan directamente desde `ordenpago`:

```sql
SELECT * FROM ordenpago WHERE Estado = 'PENDIENTE'
```

## Cambios Realizados

### 1. Actualizar Tabla `ordenpago`

**Archivo**: `actualizar_tabla_ordenpago.sql`

**Ejecutar en phpMyAdmin**:

```sql
USE posgradofcs;

-- Agregar campo CostoMatricula
ALTER TABLE ordenpago
ADD COLUMN CostoMatricula DECIMAL(10,2) NULL AFTER PagoCompleto;

-- Agregar campo Firma
ALTER TABLE ordenpago
ADD COLUMN Firma VARCHAR(200) NULL AFTER NitCiFactura;
```

**Campos en ordenpago** (completos):

| Campo | Tipo | Descripción |
|-------|------|-------------|
| IdOrdenPago | INT(11) | ID autoincremental |
| NumeroOrden | VARCHAR(50) | Número único (ORD-YmdHis-XXXX) |
| idInscripcion | INT(11) | NULL hasta que se confirme el pago |
| EstudianteID | INT(11) | ID del estudiante |
| ProgramaID | INT(11) | ID del programa |
| MontoTotal | DECIMAL(10,2) | Monto total sin descuento |
| MontoDescuento | DECIMAL(10,2) | Descuento aplicado |
| PorcentajeDescuento | DECIMAL(5,2) | % de descuento |
| MontoFinal | DECIMAL(10,2) | Monto a pagar (con descuento) |
| PagoCompleto | TINYINT(1) | 0=Solo matrícula, 1=Pago completo |
| **CostoMatricula** | DECIMAL(10,2) | **NUEVO** - Costo de matrícula |
| FechaGeneracion | DATETIME | Fecha de creación |
| FechaVencimiento | DATE | Fecha de vencimiento (opcional) |
| FechaConfirmacion | DATETIME | Fecha de confirmación de pago |
| ResponsableGeneracion | VARCHAR(100) | Quien generó la orden |
| Observaciones | TEXT | Observaciones adicionales |
| NombreFactura | VARCHAR(200) | Nombre para factura |
| NitCiFactura | VARCHAR(50) | NIT o CI para factura |
| **Firma** | VARCHAR(200) | **NUEVO** - Firma del responsable |
| Estado | ENUM | PENDIENTE / CONFIRMADO / ANULADO / VENCIDO |

### 2. Modificar Modelo

**Archivo**: `modelos/ordenpago.modelo.php`

**Cambios principales**:

1. **NO inserta en `estudianteprograma`**
2. **NO inserta en `pagomodulo`**
3. **Solo inserta en `ordenpago`**
4. Genera número de orden con timestamp + random: `ORD-YmdHis-XXXX`
5. Captura todos los campos incluyendo `NombreFactura`, `NitCiFactura`, `Firma`

**Código clave**:

```php
// INSERTAR SOLO EN ORDENPAGO - NO tocar estudianteprograma
$stmtOrden = $pdo->prepare(
    "INSERT INTO ordenpago
    (NumeroOrden, idInscripcion, EstudianteID, ProgramaID,
     MontoTotal, MontoDescuento, PorcentajeDescuento, MontoFinal,
     PagoCompleto, CostoMatricula, FechaGeneracion,
     NombreFactura, NitCiFactura, ResponsableGeneracion, Firma, Estado)
    VALUES
    (:numeroOrden, NULL, :estudianteID, :programaID,
     :montoTotal, :montoDescuento, :porcentajeDescuento, :montoFinal,
     :pagoCompleto, :costoMatricula, NOW(),
     :nombreFactura, :nitCiFactura, :responsable, :firma, 'PENDIENTE')"
);
```

**Retorna**:
```php
return [
    'status' => 'exitoso',
    'idOrdenPago' => $idOrdenPago,  // ID en ordenpago
    'numeroOrden' => $numeroOrden,
    'mensaje' => 'Orden de pago creada exitosamente'
];
```

### 3. Modificar Controlador

**Archivo**: `controladores/ordenpago.controlador.php`

**Cambios principales**:

1. **Captura campos de facturación** desde `$_POST`
2. **Pasa campos al modelo** en el array `$datosOrdenPago`
3. **Consulta directa** a `estudiante` y `programa` (sin usar `estudianteprograma`)
4. Usa `idOrdenPago` en lugar de `idInscripcion`

**Código clave**:

```php
// Capturar campos de facturación
$nombreFactura = htmlspecialchars(trim($_POST['nombreFactura']));
$nitCiFactura = htmlspecialchars(trim($_POST['nitCiFactura']));
$responsable = htmlspecialchars(trim($_POST['responsable']));
$firma = isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '';

// Preparar datos
$datosOrdenPago = array(
    "EstudianteID" => (int)$_POST['idcliente'],
    "ProgramaID" => (int)$_POST['programa'],
    "costomatricula" => $montoMatricula,
    "montoPagado" => $montoPagado,
    "pagoCompleto" => $pagoCompleto,
    "porcentajeDescuento" => $porcentajeDescuento,
    "montoDescuento" => $montoDescuentoAplicado,
    "FechaInscripcion" => htmlspecialchars(trim($_POST['fechaInscripcion'])),
    "NombreFactura" => $nombreFactura,
    "NitCiFactura" => $nitCiFactura,
    "Responsable" => $responsable,
    "Firma" => $firma
);

// Registrar orden
$resultado = OrdenPagoModelos::RegistrarPreregistroModelo($datosOrdenPago);

// Obtener datos para PDF (sin usar estudianteprograma)
$stmt = $pdo->prepare("
    SELECT
        e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
        p.NombrePrograma, p.Codigo, p.Version, p.NumeroTramite
    FROM estudiante e
    CROSS JOIN programa p
    WHERE e.EstudianteID = :estudianteID
      AND p.ProgramaID = :programaID
");
```

## Instrucciones de Implementación

### Paso 1: Actualizar Base de Datos

1. Abre **phpMyAdmin**
2. Selecciona la base de datos `posgradofcs`
3. Ve a la pestaña **SQL**
4. Ejecuta el contenido del archivo: `actualizar_tabla_ordenpago.sql`

```sql
ALTER TABLE ordenpago
ADD COLUMN CostoMatricula DECIMAL(10,2) NULL AFTER PagoCompleto;

ALTER TABLE ordenpago
ADD COLUMN Firma VARCHAR(200) NULL AFTER NitCiFactura;
```

5. Verifica que se agregaron correctamente:

```sql
DESCRIBE ordenpago;
```

Debes ver los campos `CostoMatricula` y `Firma`

### Paso 2: Limpiar Registros Antiguos (Opcional)

Si tienes registros de pruebas anteriores en `estudianteprograma`:

```sql
-- Ver órdenes pendientes en estudianteprograma (del sistema anterior)
SELECT * FROM estudianteprograma WHERE Estado = 'PENDIENTE';

-- Eliminarlas (opcional, solo en desarrollo)
DELETE FROM estudianteprograma WHERE Estado = 'PENDIENTE';

-- Ver órdenes en ordenpago
SELECT * FROM ordenpago WHERE Estado = 'PENDIENTE';

-- Eliminarlas si son de prueba
DELETE FROM ordenpago WHERE Estado = 'PENDIENTE';
```

### Paso 3: Probar el Nuevo Flujo

1. **Generar Orden de Pago**:
   - Ir a: `http://localhost/POSGRADOFCS/ordenpago`
   - Seleccionar estudiante
   - Seleccionar programa
   - Configurar pago
   - Completar datos de facturación
   - Generar orden

2. **Verificar en Base de Datos**:

```sql
-- Ver la última orden generada
SELECT * FROM ordenpago ORDER BY FechaGeneracion DESC LIMIT 1;

-- Verificar que NO se creó en estudianteprograma
SELECT * FROM estudianteprograma
WHERE EstudianteID = [ID del estudiante]
  AND ProgramaID = [ID del programa];
-- Debe retornar 0 filas si es la primera orden
```

3. **Verificar PDF**:
   - Debe abrirse automáticamente
   - Debe tener todos los datos
   - Número de orden debe tener formato: `ORD-20251219151030-4567`

## Consultas Útiles

### Ver todas las órdenes pendientes

```sql
SELECT
    op.NumeroOrden,
    e.Nombre,
    e.Apaterno,
    p.NombrePrograma,
    op.MontoFinal,
    op.FechaGeneracion,
    op.Estado
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE op.Estado = 'PENDIENTE'
ORDER BY op.FechaGeneracion DESC;
```

### Ver órdenes de un estudiante específico

```sql
SELECT
    op.NumeroOrden,
    p.NombrePrograma,
    op.MontoFinal,
    op.PagoCompleto,
    op.FechaGeneracion,
    op.Estado
FROM ordenpago op
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE op.EstudianteID = [ID_ESTUDIANTE]
ORDER BY op.FechaGeneracion DESC;
```

### Verificar que no hay pendientes en estudianteprograma

```sql
-- NO debe retornar ninguna fila con el nuevo sistema
SELECT * FROM estudianteprograma WHERE Estado = 'PENDIENTE';
```

### Simular confirmación de pago (manual)

```sql
-- 1. Actualizar orden a CONFIRMADO
UPDATE ordenpago
SET Estado = 'CONFIRMADO',
    FechaConfirmacion = NOW()
WHERE IdOrdenPago = [ID_ORDEN];

-- 2. Crear registro en estudianteprograma
INSERT INTO estudianteprograma
(EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto,
 porcentajeDescuento, montoDescuento, nvauchermatricula, FechaInscripcion, Estado)
SELECT
    EstudianteID,
    ProgramaID,
    CostoMatricula,
    MontoFinal,
    PagoCompleto,
    PorcentajeDescuento,
    MontoDescuento,
    'VOUCHER-MANUAL-001',
    NOW(),
    'ACTIVO'
FROM ordenpago
WHERE IdOrdenPago = [ID_ORDEN];

-- 3. Actualizar orden con el idInscripcion
UPDATE ordenpago op
INNER JOIN estudianteprograma ep ON ep.EstudianteID = op.EstudianteID AND ep.ProgramaID = op.ProgramaID
SET op.idInscripcion = ep.idInscripcion
WHERE op.IdOrdenPago = [ID_ORDEN];
```

## Comparación: Antes vs Ahora

| Aspecto | ANTES | AHORA |
|---------|-------|-------|
| **Tabla principal** | estudianteprograma | ordenpago |
| **Estado inicial** | PENDIENTE en estudianteprograma | PENDIENTE en ordenpago |
| **Conflictos duplicados** | ✗ Frecuentes | ✓ Eliminados |
| **Limpieza necesaria** | ✗ Sí, en estudianteprograma | ✓ Solo en ordenpago |
| **Integridad de datos** | ✗ Inconsistente | ✓ Consistente |
| **Generación PDF** | Desde estudianteprograma | Desde estudiante + programa |
| **Confirmación de pago** | Actualizar estado PENDIENTE→ACTIVO | INSERT en estudianteprograma |

## Próximos Pasos (Futuro)

### 1. Módulo de Confirmación de Pagos

Crear una vista para que caja confirme pagos:

- Listar órdenes PENDIENTES
- Ingresar número de voucher
- Confirmar pago
- Automáticamente crear registro en `estudianteprograma`
- Actualizar `ordenpago` a CONFIRMADO

### 2. Reporte de Órdenes Pendientes

Vista para ver:
- Órdenes pendientes
- Órdenes vencidas (más de X días)
- Órdenes confirmadas hoy/semana/mes

### 3. Integración con Sistema de Pagos

- API para confirmar pagos desde banco
- Webhook para notificaciones de pago
- QR de pago en el PDF

## Resumen de Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `modelos/ordenpago.modelo.php` | ✅ Solo inserta en ordenpago |
| `controladores/ordenpago.controlador.php` | ✅ Captura campos, consulta directa |
| `actualizar_tabla_ordenpago.sql` | ✅ ALTER TABLE para agregar campos |
| `NUEVA_ARQUITECTURA_ORDENPAGO.md` | ✅ Esta documentación |

## Soporte y Troubleshooting

### Error: "Column 'CostoMatricula' not found"

**Solución**: Ejecutar el script SQL `actualizar_tabla_ordenpago.sql`

### Error: "Column 'Firma' not found"

**Solución**: Ejecutar el script SQL `actualizar_tabla_ordenpago.sql`

### Órdenes se crean en estudianteprograma

**Causa**: Código antiguo aún está ejecutándose
**Solución**: Verificar que los archivos modelo.php y controlador.php están actualizados

### PDF no se genera

1. Verificar consola del navegador (F12)
2. Verificar que SweetAlert está cargado
3. Verificar logs de Apache: `C:\xampp\apache\logs\error.log`

---

**Fecha de implementación**: 19/12/2025
**Versión**: 2.0 - Arquitectura Independiente
**Estado**: ✅ **IMPLEMENTADO - Listo para pruebas**
