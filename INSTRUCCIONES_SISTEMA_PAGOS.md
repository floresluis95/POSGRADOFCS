# 📋 SISTEMA DE INSCRIPCIÓN CON PLAN DE PAGOS POR MÓDULO

## 🎯 Descripción del Sistema

Sistema completo de gestión de inscripciones para programas académicos (Diplomados, Maestrías, Especialidades) con:
- ✅ Plan de pagos automático por módulo
- ✅ Control de vouchers de pago
- ✅ Seguimiento de cuotas vencidas/pagadas
- ✅ Compatible con PHP 8
- ✅ Arquitectura MVC
- ✅ Seguridad contra inyecciones SQL

---

## 📁 Archivos Creados/Modificados

### 1. Base de Datos
- **`bd/sistema_pagos.sql`** - Script SQL con nuevas tablas:
  - `plan_pagos`: Plan de pagos de cada inscripción
  - `cuota`: Cuotas individuales (una por módulo)
  - `voucher`: Registro de vouchers de pago
  - `vista_estado_pagos`: Vista consolidada del estado de pagos

### 2. Modelo (MVC)
- **`modelos/inscripcion.modelo.php`** - Modelo completo con:
  - `ObtenerDetalleProgramaModelo()` - Obtener detalles de programa
  - `ListarProgramasPorGradoModelo()` - Listar programas por grado
  - `RegistrarInscripcionModelo()` - Registrar inscripción
  - `CrearPlanPagosModelo()` - Crear plan con cuotas automáticas
  - `RegistrarVoucherModelo()` - Registrar voucher de pago
  - `ObtenerCuotasPlanModelo()` - Obtener cuotas de un plan
  - `GenerarCodigoVoucherModelo()` - Generar código único de voucher

### 3. Controlador (MVC)
- **`controladores/inscripcion.controlador.php`** - Controlador AJAX con:
  - `ObtenerProgramasPorGradoControlador()` - Cargar programas por grado
  - `ObtenerDetalleProgramaControlador()` - Cargar detalles del programa
  - `RegistrarInscripcionControlador()` - Procesar inscripción completa
  - `RegistrarVoucherControlador()` - Procesar voucher de pago
  - `ObtenerCuotasPlanControlador()` - Obtener cuotas de un plan

### 4. Vista (MVC)
- **`vistas/componentes/inscripcion.php`** - Vista actualizada con:
  - Sección de detalles del programa
  - Calculadora de costo por módulo
  - Preview del plan de pagos
  - Formulario completo de inscripción

### 5. JavaScript/AJAX
- **`vistas/recursos/assets/js/scripts/inscripcion.js`** - Lógica AJAX:
  - Carga dinámica de programas según grado académico
  - Muestra detalles del programa seleccionado
  - Cálculo automático de costo por módulo
  - Generación de preview del plan de pagos
  - Envío de formulario con validación

### 6. Otros
- **`ajax/inscripcion.ajax.php`** - Handler AJAX alternativo

---

## 🚀 Instalación

### Paso 1: Ejecutar Script SQL

```sql
-- Ejecutar en phpMyAdmin o MySQL Workbench
SOURCE C:/xampp/htdocs/POSGRADOFCS/bd/sistema_pagos.sql
```

O copiar y pegar el contenido del archivo en phpMyAdmin.

### Paso 2: Verificar Carpeta de Vouchers

El sistema creará automáticamente la carpeta `vistas/recursos/vouchers/` cuando se suba el primer voucher.

### Paso 3: Probar el Sistema

1. Ir a: `http://localhost/POSGRADOFCS/inscripcion`
2. Seleccionar estudiante
3. Elegir grado académico
4. Seleccionar programa
5. Ingresar pago de matrícula
6. Ver preview del plan de pagos
7. Guardar inscripción

---

## 📊 Estructura de Tablas

### Tabla: `plan_pagos`
```
PlanPagoID (PK)
idInscripcion (FK → estudianteprograma)
CostoTotal
MontoPagoInicial
MontoModulos
CantidadModulos
CostoPorModulo
FechaCreacion
Estado (ACTIVO/COMPLETADO/SUSPENDIDO)
```

### Tabla: `cuota`
```
CuotaID (PK)
PlanPagoID (FK → plan_pagos)
NumeroModulo
NombreModulo
MontoCuota
FechaVencimiento
EstadoPago (PENDIENTE/PAGADO/VENCIDO/PARCIAL)
MontoPagado
FechaPago
Observaciones
```

### Tabla: `voucher`
```
VoucherID (PK)
CuotaID (FK → cuota)
CodigoVoucher (ÚNICO)
MontoPago
FechaPago
MetodoPago (EFECTIVO/TRANSFERENCIA/DEPOSITO/QR/TARJETA)
NumeroTransaccion
ArchivoVoucher (ruta imagen)
RegistradoPor (FK → personal)
Validado (0/1)
FechaValidacion
ValidadoPor (FK → personal)
Observaciones
```

---

## 🔧 Funcionalidades Implementadas

### 1. Inscripción Automática
- ✅ Selección de estudiante
- ✅ Selección de programa por grado académico
- ✅ Visualización de detalles del programa
- ✅ Cálculo automático de cuotas
- ✅ Generación automática del plan de pagos

### 2. Plan de Pagos
- ✅ Crea automáticamente una cuota por cada módulo
- ✅ Distribuye el costo equitativamente
- ✅ Ajusta la última cuota por redondeos
- ✅ Genera fechas de vencimiento mensuales
- ✅ Nombres de módulos en números romanos (MÓDULO I, II, III...)

### 3. Control de Vouchers
- ✅ Código único generado automáticamente (VOU-YYYYMMDD-HHMMSS-RAND)
- ✅ Subida de imagen del voucher
- ✅ Múltiples métodos de pago
- ✅ Registro de usuario que registra
- ✅ Sistema de validación (pendiente/validado)
- ✅ Actualización automática del estado de la cuota

### 4. Seguridad
- ✅ Parámetros preparados (PDO)
- ✅ Validación de datos
- ✅ Transacciones PDO para integridad
- ✅ Manejo de excepciones
- ✅ Logs de errores

---

## 📝 Flujo de Trabajo

### A. Inscripción de Estudiante

```
1. Usuario selecciona GRADO ACADÉMICO
   ↓
2. AJAX carga programas disponibles
   ↓
3. Usuario selecciona PROGRAMA
   ↓
4. Sistema muestra detalles (módulos, costo, sede, etc.)
   ↓
5. Usuario ingresa PAGO DE MATRÍCULA
   ↓
6. Sistema calcula automáticamente:
   - Monto de módulos = Costo Total - Matrícula
   - Costo por módulo = Monto Módulos / Cantidad Módulos
   ↓
7. Sistema muestra preview del plan de pagos
   ↓
8. Usuario confirma y guarda
   ↓
9. Sistema crea:
   - Registro en estudianteprograma
   - Plan de pagos en plan_pagos
   - N cuotas en tabla cuota (una por módulo)
```

### B. Registro de Pago (Voucher)

```
1. Usuario selecciona cuota a pagar
   ↓
2. Ingresa datos del pago:
   - Monto
   - Método (efectivo, transferencia, etc.)
   - Número de transacción
   - Sube imagen del voucher
   ↓
3. Sistema genera código único
   ↓
4. Guarda voucher y actualiza cuota:
   - MontoPagado += MontoPago
   - EstadoPago = PAGADO (si monto >= monto cuota)
   - EstadoPago = PARCIAL (si monto < monto cuota)
```

---

## 🔍 Consultas Útiles

### Ver estado de pagos de un estudiante
```sql
SELECT * FROM vista_estado_pagos
WHERE EstudianteID = 1;
```

### Ver cuotas pendientes
```sql
SELECT * FROM cuota
WHERE EstadoPago IN ('PENDIENTE', 'PARCIAL')
ORDER BY FechaVencimiento ASC;
```

### Ver vouchers de una cuota
```sql
SELECT v.*, CONCAT(p.Nombres, ' ', p.ApellidoPaterno) AS RegistradoPor
FROM voucher v
LEFT JOIN personal p ON v.RegistradoPor = p.IdPersonal
WHERE v.CuotaID = 1
ORDER BY v.FechaPago DESC;
```

### Vouchers pendientes de validación
```sql
SELECT * FROM voucher
WHERE Validado = 0
ORDER BY FechaPago DESC;
```

---

## 🎨 Personalización

### Cambiar formato de código de voucher
Editar en `modelos/inscripcion.modelo.php`:
```php
public static function GenerarCodigoVoucherModelo()
{
    // Formato personalizado
    return 'PAGO-' . date('Y') . '-' . rand(1000, 9999);
}
```

### Cambiar nombres de módulos
Editar función `romano()` en `modelos/inscripcion.modelo.php`:
```php
// Ejemplo: MÓDULO 1, MÓDULO 2...
$nombreModulo = "MÓDULO " . $i;
```

### Ajustar fechas de vencimiento
Editar en `CrearPlanPagosModelo()`:
```php
// Cambiar "+{$i} month" por "+{$i} week" para semanal
$fechaVencimiento = date('Y-m-d', strtotime("+{$i} month", ...));
```

---

## ⚠️ Notas Importantes

1. **Backup de BD**: Hacer backup antes de ejecutar el script SQL
2. **Permisos de carpeta**: La carpeta `vistas/recursos/vouchers/` necesita permisos de escritura (777)
3. **Validación de vouchers**: Implementar módulo de validación de vouchers según necesidad
4. **Reportes**: Crear reportes de pagos pendientes/vencidos
5. **Notificaciones**: Implementar notificaciones de vencimiento de cuotas

---

## 📞 Soporte

Para dudas o problemas:
1. Revisar logs de PHP: `error_log()`
2. Revisar consola del navegador (F12)
3. Verificar que las tablas se crearon correctamente
4. Verificar permisos de carpetas

---

## 🔄 Próximas Mejoras Sugeridas

- [ ] Módulo de validación de vouchers
- [ ] Reportes de pagos (PDF)
- [ ] Notificaciones automáticas por email/SMS
- [ ] Dashboard de estado de pagos
- [ ] Exportación a Excel
- [ ] Impresión de recibos
- [ ] Control de mora/intereses
- [ ] Pagos parciales múltiples

---

**Sistema desarrollado con:**
- PHP 8.2
- MySQL/MariaDB
- jQuery 3.6
- SweetAlert2
- Bootstrap 4

**Fecha:** 2025-11-12
**Versión:** 1.0.0
