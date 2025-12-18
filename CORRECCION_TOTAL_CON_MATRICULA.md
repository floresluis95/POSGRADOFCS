# ✅ Corrección: Descuento sobre TOTAL (Matrícula + Programa)

**Fecha:** 18/12/2025
**Estado:** COMPLETADO

## 🔍 Problema Identificado

El descuento en pagos al contado se estaba aplicando **SOLO sobre el costo del programa**, cuando debería aplicarse sobre el **TOTAL = Matrícula + Programa**.

### Ejemplo del Problema Anterior:
Supongamos:
- Costo de Matrícula: Bs. 2,000.00
- Costo del Programa: Bs. 32,400.00
- **TOTAL real:** Bs. 34,400.00

Si el estudiante paga completo con 20% de descuento:

**❌ ANTES (INCORRECTO):**
- Se aplicaba descuento solo sobre Bs. 32,400.00
- Descuento 20%: Bs. 6,480.00
- Monto a pagar: Bs. 25,920.00 (INCORRECTO)

**✅ AHORA (CORRECTO):**
- Se aplica descuento sobre Bs. 34,400.00 (Matrícula + Programa)
- Descuento 20%: Bs. 6,880.00
- Monto a pagar: Bs. 27,520.00 (CORRECTO)

## ✅ Solución Implementada

### 1. Modificación en `selectprograma.js`

**Archivo:** `vistas/recursos/assets/js/scripts/selectprograma.js`

**Cambio realizado:**
```javascript
// Guardar valores en campos hidden para uso posterior
// IMPORTANTE: costoTotalPrograma = Costo del Programa (SIN matrícula)
// Para pago completo, se sumará: costoMatriculaPrograma + costoTotalPrograma
$('#costoTotalPrograma').val(costoPrograma.toFixed(2));
$('#costoMatriculaPrograma').val(costoMatricula.toFixed(2));
```

**Función:** Ahora guarda correctamente tanto el costo del programa como el costo de matrícula en campos hidden.

---

### 2. Modificación en `inscripcion.php`

**Archivo:** `vistas/componentes/inscripcion.php`

**a) Nuevo campo hidden:**
```html
<input type="hidden" name="costoTotalConMatricula" id="costoTotalConMatricula" value="0">
```

**b) Lógica JavaScript actualizada:**
```javascript
// IMPORTANTE: El monto TOTAL es la suma de Matrícula + Programa
const costoPrograma = parseFloat($('#costoTotalPrograma').val()) || 0;
const costoMatricula = parseFloat($('#costoMatriculaPrograma').val()) || 0;
const montoTotal = costoMatricula + costoPrograma;

// Guardar el monto total original (antes de descuento) en campo hidden
$('#costoTotalConMatricula').val(montoTotal.toFixed(2));

// Mostrar costo original (TOTAL = Matrícula + Programa)
$('#montoOriginalDisplay').val(montoTotal.toFixed(2));

// Establecer monto a pagar inicial (sin descuento)
$('#montoMatricula').val(montoTotal.toFixed(2));
```

**Función:**
- Calcula el TOTAL correcto (Matrícula + Programa)
- Muestra ese TOTAL como base para aplicar el descuento
- Los descuentos ahora se calculan sobre este TOTAL

---

### 3. Actualización en `recibo-pago-modulos.php`

**Archivo:** `vistas/componentes/recibo-pago-modulos.php`

**Cambios realizados:**

**a) Cálculo del costo original:**
```php
$costoMatriculaBD = floatval($estudiante['CostoMatricula']); // Costo de matrícula del programa (BD)
$costoProgramaBD = floatval($estudiante['CostoTotalPrograma']); // Costo del programa (BD)

// Calcular el costo original TOTAL (antes del descuento)
// Cuando es pago completo: TOTAL = Matrícula + Programa
if ($pagoCompleto == 1) {
    // Usar el cálculo más preciso: montoPagado + montoDescuento
    $costoOriginal = $montoPagado + $montoDescuento;
    // Verificar con la suma de matrícula + programa (debe coincidir)
    $totalDesdePrograma = $costoMatriculaBD + $costoProgramaBD;
    // Si hay diferencia significativa, usar el total desde programa
    if (abs($costoOriginal - $totalDesdePrograma) > 0.01) {
        $costoOriginal = $totalDesdePrograma;
    }
}
```

**b) Desglose detallado en el recibo:**
Ahora muestra:
1. **Matrícula** - Bs. X,XXX.XX
2. **Costo del Programa** - Bs. XX,XXX.XX
3. **Subtotal (Matrícula + Programa)** - Bs. XX,XXX.XX
4. **Descuento Aplicado (X%)** - Bs. X,XXX.XX
5. **TOTAL PAGADO** - Bs. XX,XXX.XX

---

### 4. Actualización en `pdfpagocompletoprograma.php`

**Archivo:** `extensiones/tcpdf/pdf/pdfpagocompletoprograma.php`

**Cambios realizados:**

**a) Cálculo del costo total original:**
```php
$costoMatriculaBD = floatval($estudiante['CostoMatricula']); // Costo de matrícula del programa
$costoProgramaBD = floatval($estudiante['CostoTotalPrograma']); // Costo del programa

// El costo TOTAL original es: Matrícula + Programa
// Este es el monto ANTES del descuento
$costoTotalOriginal = $costoMatriculaBD + $costoProgramaBD;

// Verificación de coherencia: costoTotalOriginal - montoDescuento debe ser igual a montoPagado
$diferencia = abs(($costoTotalOriginal - $montoDescuento) - $montoPagado);
if ($diferencia > 0.01) {
    // Si hay inconsistencia, recalcular el costo original sumando monto pagado + descuento
    $costoTotalOriginal = $montoPagado + $montoDescuento;
}
```

**b) Desglose detallado en el PDF:**
El PDF ahora muestra:
1. **MATRÍCULA DEL PROGRAMA:** Bs. X,XXX.XX
2. **COSTO DEL PROGRAMA:** Bs. XX,XXX.XX
3. **SUBTOTAL (MATRÍCULA + PROGRAMA):** Bs. XX,XXX.XX
4. **DESCUENTO APLICADO (X%):** - Bs. X,XXX.XX
5. **TOTAL PAGADO:** Bs. XX,XXX.XX

---

## 📊 Ejemplo Completo de Funcionamiento

### Caso: Estudiante paga completo con 20% de descuento

**Datos del Programa:**
- Costo de Matrícula: Bs. 2,000.00
- Costo del Programa: Bs. 32,400.00
- **TOTAL:** Bs. 34,400.00

**Proceso:**

1. **En el formulario de inscripción:**
   - Se marca "Pago Completo"
   - El sistema calcula: TOTAL = Bs. 2,000 + Bs. 32,400 = **Bs. 34,400.00**
   - Se muestra este TOTAL en el campo "Costo Original del Programa"

2. **Aplicación del descuento:**
   - Usuario ingresa: 20% de descuento
   - Sistema calcula: Bs. 34,400 × 20% = **Bs. 6,880.00** de descuento
   - Monto final: Bs. 34,400 - Bs. 6,880 = **Bs. 27,520.00**

3. **Datos guardados en la BD (tabla `estudianteprograma`):**
   ```
   costomatricula: 0 (no se cobra separada cuando es pago completo)
   montoPagado: 27520.00 ✅ (monto FINAL con descuento)
   montoDescuento: 6880.00 ✅
   porcentajeDescuento: 20.00 ✅
   pagoCompleto: 1
   ```

4. **En el recibo de pago:**
   ```
   1. Matrícula                          Bs. 2,000.00
   2. Costo del Programa                 Bs. 32,400.00
   ─────────────────────────────────────────────────────
   Subtotal (Matrícula + Programa)       Bs. 34,400.00
   Descuento Aplicado (20%)              - Bs. 6,880.00
   ─────────────────────────────────────────────────────
   TOTAL PAGADO                          Bs. 27,520.00 ✅
   ```

5. **En el PDF de pago completo:**
   - Muestra el mismo desglose detallado
   - Claramente indica que el descuento se aplicó sobre el TOTAL
   - Todos los montos son coherentes

---

## 📁 Archivos Modificados

### JavaScript:
1. ✅ `vistas/recursos/assets/js/scripts/selectprograma.js`
   - Guarda costos de matrícula y programa en campos hidden

### PHP - Vista:
2. ✅ `vistas/componentes/inscripcion.php`
   - Agrega campo hidden `costoTotalConMatricula`
   - Calcula TOTAL = Matrícula + Programa cuando se marca pago completo
   - Aplica descuento sobre ese TOTAL

### PHP - Recibos y PDFs:
3. ✅ `vistas/componentes/recibo-pago-modulos.php`
   - Calcula costo original correcto (Matrícula + Programa)
   - Muestra desglose detallado

4. ✅ `extensiones/tcpdf/pdf/pdfpagocompletoprograma.php`
   - Calcula costo original correcto (Matrícula + Programa)
   - PDF muestra desglose completo

---

## ✅ Verificación

### Para Nuevos Registros:

1. **Ir al formulario de inscripción:**
   ```
   http://localhost/POSGRADOFCS/inscripcion
   ```

2. **Pasos de prueba:**
   - Seleccionar un estudiante
   - Seleccionar un programa
   - Marcar "Pago Completo del Programa"
   - Verificar que el "Costo Original" muestre la suma de Matrícula + Programa
   - Aplicar un descuento (ej: 20%)
   - Verificar que el cálculo sea correcto
   - Registrar la inscripción

3. **Verificar en el recibo:**
   ```
   http://localhost/POSGRADOFCS/vistas/componentes/recibo-pago-modulos.php?idinscripcion=X
   ```
   - Debe mostrar el desglose completo
   - El TOTAL debe ser Matrícula + Programa
   - El descuento debe estar correcto
   - El monto pagado debe ser correcto

4. **Verificar en el PDF:**
   ```
   http://localhost/POSGRADOFCS/extensiones/tcpdf/pdf/pdfpagocompletoprograma.php?idinscripcion=X
   ```
   - El PDF debe mostrar el mismo desglose
   - Todos los montos deben coincidir

### Para Registros Existentes:

Los registros existentes que fueron creados antes de esta corrección tienen datos incorrectos. Para corregirlos:

1. **Ejecutar el script de diagnóstico:**
   ```
   http://localhost/POSGRADOFCS/diagnostico_monto_pagado.php
   ```

2. **Ejecutar el script de corrección:**
   ```
   http://localhost/POSGRADOFCS/corregir_monto_pagado.php
   ```

**⚠️ NOTA:** Los registros antiguos fueron creados con el cálculo incorrecto (solo sobre el programa), por lo que sus montos NO coincidirán con el nuevo cálculo. Si es necesario recalcular, se deberá hacer manualmente caso por caso.

---

## 🎯 Diferencias Clave

| Aspecto | ANTES (Incorrecto) | AHORA (Correcto) |
|---------|-------------------|------------------|
| Base del descuento | Solo Costo del Programa | **Matrícula + Programa** |
| Ejemplo con 20% desc. | Bs. 32,400 × 20% = Bs. 6,480 | **Bs. 34,400 × 20% = Bs. 6,880** |
| Monto final (ejemplo) | Bs. 25,920 | **Bs. 27,520** |
| Visualización recibo | Mostraba solo programa | **Muestra desglose completo** |
| PDF pago completo | Mostraba solo total programa | **Muestra Matrícula + Programa** |

---

## 📝 Importante

1. **Los nuevos registros** usarán el cálculo correcto automáticamente
2. **Los registros antiguos** tienen el cálculo incorrecto y pueden necesitar ajuste manual
3. **El sistema ahora es coherente** en todos los puntos: formulario, recibo, PDF
4. **El descuento siempre se aplica sobre el TOTAL** (Matrícula + Programa)

---

**Desarrollado el:** 18/12/2025
**Estado:** LISTO PARA PRODUCCIÓN ✅
