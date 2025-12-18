# ✅ Reestructuración Completa de Orden de Pago

**Fecha:** 18/12/2025
**Estado:** COMPLETADO

## 🔍 Cambio Realizado

Se ha **reestructurado completamente** el módulo de "Orden de Pago" para que funcione como un **PREREGISTRO** similar al formulario de inscripción, pero **SIN campo de voucher**.

---

## 📋 Funcionamiento Anterior

**ANTES**, "ordenpago" funcionaba para:
- Buscar estudiantes ya inscritos
- Seleccionar módulos pagados
- Generar PDF de orden de pago para módulos existentes

---

## ✅ Funcionamiento Actual

**AHORA**, "ordenpago" funciona como:

### 1. **Preregistro de Estudiantes**
- Formulario similar a "inscripción"
- Selección de estudiante y programa
- Cálculo de matrícula o pago completo con descuentos
- **SIN campo de voucher** (porque es un preregistro)

### 2. **Estado PENDIENTE**
- El registro se guarda con `Estado = 'PENDIENTE'` en la tabla `estudianteprograma`
- Indica que es una orden de pago pendiente de confirmación
- El voucher se guarda como "ORDEN-PAGO-PENDIENTE"

### 3. **Generación Automática de PDF**
- Al registrar la orden de pago, se genera automáticamente el PDF
- El PDF muestra todos los detalles del pago a realizar
- Incluye instrucciones de pago

---

## 📁 Archivos Creados/Modificados

### 1. **Modelo** - `modelos/ordenpago.modelo.php`
**NUEVO ARCHIVO**

Funciones principales:
- `RegistrarPreregistroModelo()` - Registra el preregistro con estado PENDIENTE
- `ListarPreregistrosModelo()` - Lista todos los preregistros pendientes

**Características:**
- Verifica duplicados (estudiante ya inscrito en el programa)
- Guarda con `Estado = 'PENDIENTE'`
- Si es pago completo, crea registros en `pagomodulo` con estado PENDIENTE
- Genera número de orden único
- Retorna el ID de inscripción y número de orden

---

### 2. **Controlador** - `controladores/ordenpago.controlador.php`
**NUEVO ARCHIVO**

Funciones principales:
- `RegistrarOrdenPagoControlador()` - Procesa el formulario y genera el PDF
- `ListarPreregistrosControlador()` - Obtiene preregistros pendientes

**Flujo:**
1. Recibe datos del formulario
2. Calcula correctamente el monto (Matrícula + Programa si es pago completo)
3. Aplica descuentos si los hay
4. Registra en la BD con estado PENDIENTE
5. Abre automáticamente el PDF de orden de pago
6. Redirige de vuelta al formulario

---

### 3. **Vista** - `vistas/componentes/ordenpago.php`
**ARCHIVO COMPLETAMENTE REESCRITO**

**Estructura:**

#### Sección 1: Datos del Estudiante
- Select2 para buscar estudiante
- Botón para registrar nuevo estudiante

#### Sección 2: Información Académica
- Selección de grado académico
- Selección de programa
- Detalles del programa seleccionado
- Checkbox de "Pago Completo"

#### Sección 3: Información de Orden de Pago
- **SIN campo de voucher** (diferencia principal con inscripción)
- Alerta indicando que es un preregistro
- Campos de descuento (igual que inscripción)
- Cálculo automático de montos
- Fecha de generación de la orden

**JavaScript:**
- Manejo de checkbox de pago completo
- Cálculo de TOTAL = Matrícula + Programa
- Cálculo de descuentos (en Bs. o %)
- Actualización dinámica del monto final

---

### 4. **PDF** - `vistas/componentes/orden-pago-pdf.php`
**NUEVO ARCHIVO**

Genera un PDF profesional con:

#### Cabecera:
- Logos de UTO y FCS
- Datos de la universidad
- Título "ORDEN DE PAGO"
- Número de orden y fecha

#### Datos del Estudiante:
- Nombre completo
- C.I.
- Correo y celular
- Programa
- Grado académico
- Código del programa

#### Detalle del Monto a Pagar:

**Si es Pago Completo:**
```
1. MATRÍCULA DEL PROGRAMA:        Bs. 2,000.00
2. COSTO DEL PROGRAMA:             Bs. 32,400.00
─────────────────────────────────────────────────
SUBTOTAL (MATRÍCULA + PROGRAMA):   Bs. 34,400.00
DESCUENTO APLICADO (20%):          - Bs. 6,880.00
─────────────────────────────────────────────────
TOTAL A PAGAR:                     Bs. 27,520.00
```

**Si es Solo Matrícula:**
```
MATRÍCULA DEL PROGRAMA:            Bs. 2,000.00
─────────────────────────────────────────────────
TOTAL A PAGAR:                     Bs. 2,000.00
```

#### Instrucciones de Pago:
- Pasos para realizar el pago
- Información sobre confirmación del registro

---

## 🔄 Flujo Completo de Uso

### Paso 1: Acceder al Formulario
```
http://localhost/POSGRADOFCS/ordenpago
```

### Paso 2: Llenar el Formulario
1. **Seleccionar estudiante** (o crear nuevo)
2. **Seleccionar grado académico** (Diplomado, Maestría, Especialidad)
3. **Seleccionar programa** - Automáticamente carga costos
4. **Marcar "Pago Completo"** (opcional)
   - Si se marca: Calcula TOTAL = Matrícula + Programa
   - Si NO se marca: Solo matrícula
5. **Aplicar descuento** (opcional)
   - En Bs. o en %
   - Calcula automáticamente el monto final
6. **Verificar fecha** de generación de la orden

### Paso 3: Generar Orden de Pago
1. Click en "Generar Orden de Pago"
2. El sistema:
   - Valida los datos
   - Guarda en BD con estado PENDIENTE
   - Abre automáticamente el PDF de orden de pago
   - Muestra mensaje de éxito

### Paso 4: PDF Generado
- Se abre automáticamente en nueva pestaña
- Contiene todos los detalles del pago
- Se puede imprimir o guardar
- Incluye número de orden único

---

## 💾 Estructura de Datos en BD

### Tabla: `estudianteprograma`

**Campos relevantes:**
```
idInscripcion: Auto-increment
EstudianteID: ID del estudiante
ProgramaID: ID del programa
costomatricula: 0 si es pago completo, monto si es solo matrícula
montoPagado: Monto FINAL a pagar (con descuento aplicado)
pagoCompleto: 1 si es pago completo, 0 si es solo matrícula
porcentajeDescuento: Porcentaje de descuento aplicado
montoDescuento: Monto del descuento en Bs.
nvauchermatricula: "ORDEN-PAGO-PENDIENTE"
FechaInscripcion: Fecha de generación de la orden
Estado: "PENDIENTE"
```

### Tabla: `pagomodulo` (si es pago completo)

**Campos relevantes:**
```
idinscripcion: ID de la inscripción
IdModulo: ID del módulo
costomodulo: Costo distribuido del módulo
fechapago: Fecha de la orden
nvaucher: "PENDIENTE-ORD-XXXXXX"
Estado: "PENDIENTE"
```

---

## 📊 Ejemplo Completo

### Caso: Estudiante solicita orden de pago completo con 20% de descuento

**Datos:**
- Estudiante: Juan Pérez
- Programa: Maestría en Endodoncia
- Matrícula: Bs. 2,000.00
- Costo Programa: Bs. 32,400.00
- Descuento: 20%

**Proceso:**

1. **En el formulario:**
   - Se marca "Pago Completo"
   - Sistema calcula: TOTAL = Bs. 2,000 + Bs. 32,400 = Bs. 34,400.00
   - Se aplica descuento 20%: Bs. 6,880.00
   - **Monto Final:** Bs. 27,520.00

2. **En la BD (`estudianteprograma`):**
   ```
   costomatricula: 0
   montoPagado: 27520.00
   pagoCompleto: 1
   porcentajeDescuento: 20.00
   montoDescuento: 6880.00
   nvauchermatricula: "ORDEN-PAGO-PENDIENTE"
   Estado: "PENDIENTE"
   ```

3. **En la BD (`pagomodulo`):**
   - Se crean registros para TODOS los módulos del programa
   - Cada módulo con Estado: "PENDIENTE"
   - El costo se distribuye entre los módulos: Bs. 27,520 / N módulos

4. **PDF Generado:**
   - Número: ORD-000007-20251218
   - Muestra desglose completo
   - Total a pagar: Bs. 27,520.00

---

## 🔑 Diferencias Clave con Inscripción

| Aspecto | Inscripción | Orden de Pago |
|---------|-------------|---------------|
| **Voucher** | REQUERIDO | **NO SE SOLICITA** |
| **Estado** | ACTIVO | **PENDIENTE** |
| **Voucher en BD** | Número real | "ORDEN-PAGO-PENDIENTE" |
| **Propósito** | Registro con pago | **Preregistro sin pago** |
| **Al guardar** | Redirige a lista | **Abre PDF automáticamente** |
| **Comprobante de imagen** | Se sube | **NO se solicita** |

---

## ✅ Verificación

### 1. Probar el formulario:
```
http://localhost/POSGRADOFCS/ordenpago
```

### 2. Verificar que:
- ✅ Se puede seleccionar estudiante
- ✅ Se puede seleccionar programa
- ✅ El checkbox de pago completo funciona
- ✅ Los descuentos calculan correctamente
- ✅ NO aparece campo de voucher
- ✅ Al guardar, se genera el PDF automáticamente
- ✅ El PDF muestra todos los datos correctamente
- ✅ El registro queda con estado PENDIENTE

### 3. Verificar en la BD:
```sql
SELECT * FROM estudianteprograma WHERE Estado = 'PENDIENTE';
```

Debe mostrar los preregistros con:
- nvauchermatricula = 'ORDEN-PAGO-PENDIENTE'
- Estado = 'PENDIENTE'
- Todos los montos correctos

---

## 📝 Notas Importantes

1. **Los archivos AJAX antiguos** (`ajax/ordenpago.ajax.php`) ya NO se usan
2. **El archivo viejo** (`diagnostico_ordenpago.php`) puede eliminarse
3. **El modelo y controlador** son completamente nuevos
4. **El flujo es completamente diferente** al anterior
5. **Compatible con el cálculo** de descuentos de inscripción (Matrícula + Programa)

---

## 🎯 Resultado Final

El sistema de "Orden de Pago" ahora funciona como un **preregistro completo**:

✅ **Sin voucher** - Es una orden, no un pago confirmado
✅ **Estado PENDIENTE** - Se confirma cuando se verifique el pago
✅ **PDF automático** - Se genera al guardar
✅ **Mismo modelo que inscripción** - Misma estructura, mismos cálculos
✅ **Descuentos correctos** - Sobre TOTAL (Matrícula + Programa)

---

**Desarrollado el:** 18/12/2025
**Estado:** LISTO PARA PRODUCCIÓN ✅
