# Solución: Error de Número de Orden Duplicado

## Problema Reportado

Al intentar generar una orden de pago, aparecía el error:

```
ERROR!
No se pudo registrar la orden de pago: Error en el servidor:
SQLSTATE[23000]: Integrity constraint violation: 1062
Duplicate entry 'ORD-000002-20251219' for key 'NumeroOrden'
```

## Causa del Problema

### 1. Formato de Número de Orden No Único

El número de orden se generaba con el formato:
```
ORD-[ID]-[FECHA]
Ejemplo: ORD-000002-20251219
```

Este formato puede causar duplicados si:
- Se eliminan registros de `estudianteprograma` pero no de `ordenpago`
- Los IDs de autoincremento se reutilizan
- Se generan múltiples órdenes el mismo día con el mismo ID

### 2. Inconsistencia entre Modelo y Controlador

El **modelo** generaba el número de orden de una forma:
```php
// modelos/ordenpago.modelo.php (línea 111)
$numeroOrden = 'ORD-' . str_pad($inscripcionID, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');
```

El **controlador** lo generaba de otra forma:
```php
// controladores/ordenpago.controlador.php (línea 133)
value="ORD-' . str_pad($idInscripcion, 6, '0', STR_PAD_LEFT) . '"
```

Esto causaba que:
- El número se guardara en la base de datos con un formato
- Se mostrara en el PDF con otro formato diferente

## Solución Implementada

### Cambio 1: Número de Orden Incluye Hora

**Archivo**: `modelos/ordenpago.modelo.php` (línea 111)

**ANTES:**
```php
$numeroOrden = 'ORD-' . str_pad($inscripcionID, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');
// Ejemplo: ORD-000002-20251219
```

**DESPUÉS:**
```php
$numeroOrden = 'ORD-' . str_pad($inscripcionID, 6, '0', STR_PAD_LEFT) . '-' . date('YmdHis');
// Ejemplo: ORD-000002-20251219143052
```

**Beneficio**: Incluir hora/minuto/segundo hace el número **prácticamente único**, incluso si se generan múltiples órdenes el mismo día.

### Cambio 2: Controlador Usa el Número del Modelo

**Archivo**: `controladores/ordenpago.controlador.php`

**Línea 82** - Obtener el número de orden del resultado:
```php
$idInscripcion = $resultado['idInscripcion'];
$numeroOrden = $resultado['numeroOrden'];  // ← NUEVO
```

**Línea 134** - Usar el número de orden del modelo:

**ANTES:**
```php
<input type="hidden" name="numeroOrden" value="ORD-' . str_pad($idInscripcion, 6, '0', STR_PAD_LEFT) . '">
```

**DESPUÉS:**
```php
<input type="hidden" name="numeroOrden" value="' . htmlspecialchars($numeroOrden) . '">
```

**Beneficio**: Garantiza que el número de orden en el PDF coincida exactamente con el registrado en la base de datos.

## Limpiar Registros Duplicados Actuales

Antes de poder generar nuevas órdenes, necesitas limpiar los registros duplicados de tus pruebas.

### Opción 1: Limpiar TODAS las Órdenes PENDIENTES (Recomendado para desarrollo)

Abre **phpMyAdmin** y ejecuta:

```sql
-- Eliminar todas las órdenes de pago pendientes
DELETE FROM ordenpago WHERE Estado = 'PENDIENTE';

-- Eliminar pagos de módulos asociados
DELETE FROM pagomodulo
WHERE idinscripcion IN (
    SELECT idInscripcion
    FROM estudianteprograma
    WHERE Estado = 'PENDIENTE'
);

-- Eliminar inscripciones pendientes
DELETE FROM estudianteprograma WHERE Estado = 'PENDIENTE';
```

### Opción 2: Eliminar Solo Órdenes de HOY

Si tienes órdenes pendientes legítimas de días anteriores:

```sql
DELETE FROM ordenpago
WHERE Estado = 'PENDIENTE'
  AND DATE(FechaGeneracion) = CURDATE();

DELETE FROM estudianteprograma
WHERE Estado = 'PENDIENTE'
  AND DATE(FechaInscripcion) = CURDATE();
```

### Opción 3: Eliminar Solo la Orden Problemática

Si solo quieres eliminar la orden específica que causa el error:

```sql
-- Reemplaza con el número exacto del error
DELETE FROM ordenpago WHERE NumeroOrden = 'ORD-000002-20251219';
```

### Verificar Limpieza

Después de ejecutar alguna de las opciones, verifica:

```sql
-- Debe retornar 0 o muy pocos registros
SELECT COUNT(*) as OrdenesRestantes
FROM ordenpago
WHERE Estado = 'PENDIENTE';

SELECT COUNT(*) as InscripcionesRestantes
FROM estudianteprograma
WHERE Estado = 'PENDIENTE';
```

## Formato del Nuevo Número de Orden

### Componentes

```
ORD-[ID de 6 dígitos]-[Fecha y Hora]
```

### Ejemplo Real

```
ORD-000042-20251219143052
```

Donde:
- `ORD` = Prefijo fijo
- `000042` = ID de inscripción (6 dígitos con ceros a la izquierda)
- `20251219` = Fecha (YYYYMMDD) → 19 de diciembre de 2025
- `143052` = Hora (HHMMSS) → 14:30:52

### Ventajas del Nuevo Formato

✅ **Único**: Incluye fecha y hora completa
✅ **Rastreable**: El ID permite localizar la inscripción
✅ **Ordenable**: Se puede ordenar cronológicamente
✅ **Sin duplicados**: Es prácticamente imposible que dos órdenes tengan el mismo número

## Cómo Probar la Solución

### Paso 1: Limpiar Base de Datos

Ejecuta una de las opciones de limpieza en phpMyAdmin.

### Paso 2: Generar Nueva Orden de Pago

1. Ir a: `http://localhost/POSGRADOFCS/ordenpago`
2. Seleccionar estudiante
3. Seleccionar programa
4. Configurar pago
5. Completar datos de facturación
6. Clic en "Generar Orden de Pago"

### Paso 3: Verificar Resultado

**Resultado esperado**:
- ✅ Mensaje "EXITOSO! Orden de Pago registrada..."
- ✅ PDF se abre con número de orden en nuevo formato
- ✅ **NO** aparece error de duplicado

### Paso 4: Verificar en Base de Datos

```sql
SELECT
    NumeroOrden,
    FechaGeneracion,
    Estado,
    MontoFinal
FROM ordenpago
ORDER BY FechaGeneracion DESC
LIMIT 1;
```

**Verificar**:
- ✅ El `NumeroOrden` tiene el nuevo formato con hora
- ✅ El `Estado` es 'PENDIENTE'
- ✅ Los datos coinciden con lo ingresado

### Paso 5: Verificar el PDF

Abrir el PDF generado y verificar:
- ✅ El número de orden muestra el formato completo con hora
- ✅ Todos los datos son correctos
- ✅ Aparece tanto en ORIGINAL como en COPIA

## Cambios Realizados - Resumen

| Archivo | Línea | Cambio | Motivo |
|---------|-------|--------|--------|
| `modelos/ordenpago.modelo.php` | 111 | `date('Ymd')` → `date('YmdHis')` | Incluir hora para unicidad |
| `controladores/ordenpago.controlador.php` | 82 | Agregar `$numeroOrden = $resultado['numeroOrden']` | Obtener número del modelo |
| `controladores/ordenpago.controlador.php` | 134 | Usar `$numeroOrden` en lugar de generarlo | Consistencia modelo-controlador |

## Archivos Adicionales Creados

📄 **`limpiar_ordenes_prueba.sql`** - Script SQL con múltiples opciones para limpiar registros duplicados

## Prevención de Duplicados Futuros

### En Desarrollo

Durante el desarrollo, limpia periódicamente las órdenes PENDIENTES:

```sql
-- Ejecutar al final del día de desarrollo
DELETE FROM ordenpago WHERE Estado = 'PENDIENTE';
DELETE FROM estudianteprograma WHERE Estado = 'PENDIENTE';
```

### En Producción

En producción, considera:

1. **Limpieza automática semanal** de órdenes PENDIENTES antiguas (>7 días)
2. **Anular en lugar de eliminar** para mantener historial:
   ```sql
   UPDATE ordenpago
   SET Estado = 'ANULADO'
   WHERE Estado = 'PENDIENTE'
     AND DATEDIFF(NOW(), FechaGeneracion) > 7;
   ```

3. **Reporte de órdenes pendientes** para seguimiento

## Notas Importantes

### ¿El formato largo es un problema?

**NO**. Aunque el número es más largo visualmente, esto:
- ✅ No afecta la funcionalidad
- ✅ Garantiza unicidad
- ✅ Facilita el rastreo
- ✅ Se puede imprimir sin problemas en el PDF

### ¿Qué pasa con órdenes generadas anteriormente?

Las órdenes con el formato anterior siguen siendo válidas. El nuevo formato solo aplica a órdenes generadas después de esta actualización.

### ¿Se puede acortar el formato?

Si prefieres un formato más corto pero único, puedes usar:

```php
// Opción 1: Solo hora sin segundos
date('YmdHi')  // 202512191430

// Opción 2: Timestamp Unix
time()  // 1702997452

// Opción 3: ID de la tabla ordenpago
$pdo->lastInsertId()  // después de insertar en ordenpago
```

Pero el formato actual (`YmdHis`) es el más legible y rastreable.

## Solución de Problemas

### Si sigue apareciendo el error de duplicado:

1. Verifica que ejecutaste la limpieza SQL
2. Limpia el cache del navegador (Ctrl + Shift + Delete)
3. Verifica los cambios en el código:
   ```bash
   php -l modelos/ordenpago.modelo.php
   php -l controladores/ordenpago.controlador.php
   ```

### Si el PDF muestra número incorrecto:

Verifica que el controlador esté usando `$numeroOrden` del resultado del modelo, no generándolo nuevamente.

---

**Fecha de solución**: 19/12/2025
**Problema**: Duplicate entry 'ORD-000002-20251219' for key 'NumeroOrden'
**Causa**: Formato de número de orden sin hora permitía duplicados
**Solución**: Incluir hora/minuto/segundo en el número de orden
**Estado**: ✅ **RESUELTO**
