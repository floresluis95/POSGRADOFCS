# Solución: Error "El estudiante ya está inscrito en este programa"

## Problema Reportado

Al intentar generar una orden de pago, el sistema mostraba:

```
ERROR!
El estudiante ya está inscrito en este programa
```

Pero el estudiante **NO** se había inscrito aún.

## Causa del Problema

La validación de duplicados en el modelo estaba verificando contra **todos los estados excepto ANULADO**, incluyendo el estado **PENDIENTE**.

### Código Anterior (líneas 21-38)

```php
// Verificar si el estudiante ya está inscrito en el mismo programa
$stmtCheck = $pdo->prepare(
    "SELECT idInscripcion FROM estudianteprograma
     WHERE EstudianteID = :estudianteID
     AND ProgramaID = :programaID
     AND Estado != 'ANULADO'"  // ← Incluía PENDIENTE
);
```

### ¿Por qué causaba el error?

Cuando se genera una orden de pago, se crea un registro en `estudianteprograma` con:
- `Estado = 'PENDIENTE'`
- `nvauchermatricula = 'ORDEN-PAGO-PENDIENTE'`

Si intentabas generar otra orden de pago (por ejemplo, en una prueba), el sistema detectaba el registro PENDIENTE anterior y lo consideraba como "ya inscrito".

### Escenarios problemáticos

1. **Pruebas múltiples**: Al probar el sistema, cada intento creaba un registro PENDIENTE
2. **Órdenes perdidas**: Si un estudiante perdía una orden de pago, no podía generar otra
3. **Órdenes no pagadas**: Órdenes generadas pero no pagadas bloqueaban nuevas generaciones

## Solución Implementada

### Cambio en el Modelo

**Archivo**: `modelos/ordenpago.modelo.php`
**Líneas**: 21-40

**Nuevo código**:

```php
// Verificar si el estudiante ya está inscrito ACTIVAMENTE en el mismo programa
// NOTA: Solo verificamos contra inscripciones ACTIVAS, no contra órdenes PENDIENTES
// Esto permite generar múltiples órdenes de pago sin pagar (ej: si pierde una orden)
$stmtCheck = $pdo->prepare(
    "SELECT idInscripcion FROM estudianteprograma
     WHERE EstudianteID = :estudianteID
     AND ProgramaID = :programaID
     AND Estado IN ('ACTIVO', 'CONFIRMADO')"  // ← Solo estados confirmados
);
```

### Lógica de la Solución

✅ **PERMITE** generar órdenes de pago cuando:
- No existe ninguna inscripción activa/confirmada
- Existen órdenes PENDIENTES anteriores (no pagadas)
- El estudiante quiere generar una nueva orden

❌ **BLOQUEA** generar órdenes cuando:
- El estudiante ya tiene una inscripción ACTIVA en el programa
- El estudiante ya tiene una inscripción CONFIRMADA en el programa

### Estados en estudianteprograma

| Estado | Significado | Bloqueaería nueva orden? |
|--------|-------------|------------------------|
| `PENDIENTE` | Orden de pago generada pero no pagada | ❌ NO (permite nuevas) |
| `ACTIVO` | Inscripción confirmada y activa | ✅ SÍ (ya está inscrito) |
| `CONFIRMADO` | Inscripción confirmada | ✅ SÍ (ya está inscrito) |
| `ANULADO` | Inscripción anulada | ❌ NO (permite nuevas) |

## Beneficios de la Solución

### 1. Permite Múltiples Órdenes de Pago
Si un estudiante:
- Pierde una orden de pago
- No paga una orden generada
- Quiere generar una nueva orden con datos diferentes

Puede hacerlo sin problemas.

### 2. Facilita las Pruebas
Durante el desarrollo y pruebas, puedes generar múltiples órdenes sin que se bloqueen entre sí.

### 3. Protege Contra Duplicados Reales
Si un estudiante **ya pagó** e inscribió en un programa (Estado = ACTIVO), el sistema correctamente bloqueará nuevas órdenes con el mensaje:

```
ERROR!
El estudiante ya tiene una inscripción activa en este programa
```

## Flujo Completo de Orden de Pago

### 1. Generación de Orden (PENDIENTE)

Cuando el estudiante genera una orden de pago:

```sql
INSERT INTO estudianteprograma
  (EstudianteID, ProgramaID, Estado, nvauchermatricula, ...)
VALUES
  (123, 5, 'PENDIENTE', 'ORDEN-PAGO-PENDIENTE', ...)
```

### 2. El Estudiante Paga

Cuando el estudiante paga en caja y presenta el voucher:

```sql
UPDATE estudianteprograma
SET
  Estado = 'ACTIVO',
  nvauchermatricula = 'VOUCHER-123456',
  ...
WHERE idInscripcion = 123
```

### 3. Protección Contra Duplicados

Ahora, si intenta generar otra orden:
- La consulta busca: `Estado IN ('ACTIVO', 'CONFIRMADO')`
- Encuentra el registro ACTIVO
- Bloquea la creación con mensaje de error

## Limpieza de Órdenes Pendientes (Opcional)

Si deseas limpiar órdenes PENDIENTES antiguas que nunca se pagaron:

### Opción 1: Eliminar órdenes PENDIENTES antiguas (más de 30 días)

```sql
DELETE FROM estudianteprograma
WHERE Estado = 'PENDIENTE'
  AND DATEDIFF(NOW(), FechaInscripcion) > 30;
```

### Opción 2: Anular órdenes PENDIENTES antiguas

```sql
UPDATE estudianteprograma
SET Estado = 'ANULADO'
WHERE Estado = 'PENDIENTE'
  AND DATEDIFF(NOW(), FechaInscripcion) > 30;
```

### Opción 3: Ver órdenes PENDIENTES actuales

```sql
SELECT
    ep.idInscripcion,
    ep.FechaInscripcion,
    e.Nombre, e.Apaterno, e.Amaterno,
    p.NombrePrograma,
    ep.montoPagado,
    DATEDIFF(NOW(), ep.FechaInscripcion) as DiasDesdeCreacion
FROM estudianteprograma ep
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
WHERE ep.Estado = 'PENDIENTE'
ORDER BY ep.FechaInscripcion DESC;
```

## Cómo Probar la Solución

### Paso 1: Limpiar Órdenes Pendientes de Prueba (Opcional)

Si quieres empezar limpio, ejecuta en phpMyAdmin:

```sql
-- Ver órdenes pendientes actuales
SELECT * FROM estudianteprograma WHERE Estado = 'PENDIENTE';

-- Si quieres eliminarlas:
DELETE FROM estudianteprograma WHERE Estado = 'PENDIENTE';

-- También limpiar la tabla ordenpago relacionada:
DELETE FROM ordenpago WHERE Estado = 'PENDIENTE';
```

### Paso 2: Generar Orden de Pago

1. Ir a: `http://localhost/POSGRADOFCS/ordenpago`
2. Seleccionar estudiante
3. Seleccionar programa
4. Configurar pago
5. Completar datos de facturación
6. Clic en "Generar Orden de Pago"

**Resultado esperado**:
- ✅ Mensaje "EXITOSO! Orden de Pago registrada..."
- ✅ PDF se abre automáticamente
- ✅ **NO** aparece error de "ya está inscrito"

### Paso 3: Intentar Generar Otra Orden (Mismo Estudiante/Programa)

1. Volver a ordenpago
2. Seleccionar el **mismo estudiante**
3. Seleccionar el **mismo programa**
4. Completar formulario
5. Generar orden de pago

**Resultado esperado**:
- ✅ Se permite generar la nueva orden
- ✅ Se crea otro registro PENDIENTE
- ✅ Otro PDF se genera

### Paso 4: Simular Pago y Activación

```sql
-- Activar una de las órdenes (simular que el estudiante pagó)
UPDATE estudianteprograma
SET Estado = 'ACTIVO', nvauchermatricula = 'VOUCHER-TEST-001'
WHERE idInscripcion = (
    SELECT MAX(idInscripcion)
    FROM estudianteprograma
    WHERE Estado = 'PENDIENTE'
);
```

### Paso 5: Intentar Generar Orden con Inscripción ACTIVA

1. Volver a ordenpago
2. Seleccionar el mismo estudiante y programa (que ahora tiene Estado ACTIVO)
3. Completar formulario
4. Generar orden de pago

**Resultado esperado**:
- ❌ Error: "El estudiante ya tiene una inscripción activa en este programa"
- ✅ NO permite crear orden duplicada

## Verificaciones de Base de Datos

### Ver todas las inscripciones de un estudiante

```sql
SELECT
    ep.idInscripcion,
    ep.Estado,
    ep.FechaInscripcion,
    p.NombrePrograma,
    ep.nvauchermatricula,
    ep.montoPagado
FROM estudianteprograma ep
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
WHERE ep.EstudianteID = 123  -- Cambia 123 por el ID del estudiante
ORDER BY ep.FechaInscripcion DESC;
```

### Ver órdenes de pago generadas

```sql
SELECT
    op.NumeroOrden,
    op.FechaGeneracion,
    op.Estado,
    e.Nombre, e.Apaterno,
    p.NombrePrograma,
    op.MontoFinal
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
ORDER BY op.FechaGeneracion DESC
LIMIT 10;
```

## Notas Importantes

### ¿Qué pasa con las órdenes PENDIENTES antiguas?

Las órdenes PENDIENTES se mantienen en la base de datos indefinidamente a menos que:
1. Se implemente una tarea automática de limpieza
2. Se eliminen manualmente
3. Se anulen manualmente

**Recomendación**: Implementar una limpieza mensual de órdenes PENDIENTES con más de 30-60 días.

### ¿Se puede pagar una orden PENDIENTE antigua?

Sí, técnicamente se puede actualizar una orden PENDIENTE antigua a ACTIVA si el estudiante presenta el pago.

### ¿Qué pasa si hay múltiples órdenes PENDIENTES?

- Todas se mantienen en la base de datos
- Solo una se convertirá en ACTIVA cuando el estudiante pague
- Las demás pueden eliminarse o anularse manualmente

## Resumen

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Validación** | Estado != 'ANULADO' | Estado IN ('ACTIVO', 'CONFIRMADO') |
| **Órdenes múltiples PENDIENTES** | ❌ Bloqueadas | ✅ Permitidas |
| **Protección contra duplicados** | ✅ Funcionaba | ✅ Funciona mejor |
| **Flexibilidad** | ❌ Muy restrictivo | ✅ Balanceado |

---

**Fecha de solución**: 19/12/2025
**Problema**: Error "El estudiante ya está inscrito" cuando no debería aparecer
**Causa**: Validación incluía estados PENDIENTES
**Solución**: Validar solo contra estados ACTIVO y CONFIRMADO
**Estado**: ✅ **RESUELTO**
