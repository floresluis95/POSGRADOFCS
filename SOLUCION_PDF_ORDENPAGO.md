# Solución: Generación de PDF de Orden de Pago

## Problema Identificado

El PDF de la orden de pago no se generaba correctamente con el formato solicitado (una sola hoja con ORIGINAL y COPIA).

## Solución Implementada

Se actualizó el controlador para usar el formato correcto de PDF que incluye:
- ✅ Todos los nuevos campos de facturación
- ✅ ORIGINAL y COPIA en una sola hoja
- ✅ Información bancaria de la UTO
- ✅ Responsable y firma

## Archivos Modificados

### 1. **controladores/ordenpago.controlador.php**

#### Cambios Realizados:

**A. Validación de campos obligatorios** (Líneas 23-38):
```php
// Se agregaron validaciones para los nuevos campos
if (empty($_POST['nombreFactura']) || empty($_POST['nitCiFactura']) ||
    empty($_POST['responsable'])) {
    // Error
}
```

**B. Generación del PDF** (Líneas 78-156):
- Se obtienen los datos del estudiante y programa de la base de datos
- Se capturan los nuevos campos del formulario:
  - `nombreFactura`
  - `nitCiFactura`
  - `responsable`
  - `firma`
- Se crea un formulario HTML oculto que envía los datos por POST a `generar-orden-pago-pdf.php`
- El PDF se abre automáticamente en una nueva pestaña

**C. Función de conversión a letras** (Líneas 187-261):
- `numeroALetras()`: Convierte el monto numérico a letras
- `convertirEnteroALetras()`: Función auxiliar para convertir números enteros
- Formato de salida: "DOSCIENTOS CINCUENTA CON 50/100 BOLIVIANOS"

### 2. **vistas/componentes/generar-orden-pago-pdf.php** (Ya existía)

Este archivo **YA TENÍA** el formato correcto:
- ✅ Genera ORIGINAL y COPIA en una sola hoja
- ✅ Incluye todos los campos nuevos
- ✅ Información bancaria fija de la UTO
- ✅ Formato profesional con logos

**No se modificó** porque ya estaba correcto. Solo se corrigió el controlador para que lo use.

## Flujo de Generación del PDF

### Paso 1: Usuario completa el formulario
- Datos del estudiante (selección)
- Datos del programa (selección)
- Tipo de pago (solo matrícula o pago completo)
- **Datos de facturación** (nombre factura, NIT/CI)
- **Responsable y firma**

### Paso 2: Envío del formulario
Al hacer clic en "Generar Orden de Pago":
1. Los datos se envían al controlador `ordenpago.controlador.php`
2. Se validan todos los campos obligatorios
3. Se registra la orden de pago en la base de datos (`estudianteprograma`)
4. Se obtiene el `idInscripcion` generado

### Paso 3: Preparación de datos para el PDF
El controlador:
1. Consulta la base de datos para obtener todos los datos del estudiante y programa
2. Construye el CI completo (con complemento y expedido)
3. Convierte el monto a formato numeral: "Bs. 250.00"
4. Convierte el monto a literal: "DOSCIENTOS CINCUENTA CON 00/100 BOLIVIANOS"
5. Captura los datos adicionales del formulario

### Paso 4: Generación del PDF
1. Se crea un formulario HTML oculto con todos los datos
2. El formulario se envía automáticamente por POST a `generar-orden-pago-pdf.php`
3. Se abre en una nueva pestaña (target="_blank")
4. El PDF se genera con:
   - **Primera mitad de la hoja**: ORIGINAL
   - **Línea punteada divisoria**
   - **Segunda mitad de la hoja**: COPIA (marcada con "** COPIA **" en rojo)

### Paso 5: Resultado
- ✅ El usuario ve un mensaje de éxito
- ✅ Se abre automáticamente el PDF en nueva pestaña
- ✅ El sistema redirige a la página de orden de pago
- ✅ El PDF se puede imprimir o guardar

## Estructura del PDF Generado

### Cabecera:
- Logo UTO (izquierda)
- Logo FCS (derecha)
- Texto institucional:
  - UNIVERSIDAD TÉCNICA DE ORURO
  - FACULTAD DE CIENCIAS DE LA SALUD
  - COORDINACIÓN DE POSGRADO - ODONTOLOGÍA
  - Dirección y teléfonos

### Sección 1: Datos del Posgraduante
| Campo | Contenido |
|-------|-----------|
| Apellido Paterno | Del estudiante |
| Apellido Materno | Del estudiante |
| Nombres | Del estudiante |
| Correo Electrónico | Del estudiante |
| C.I. | Con complemento y expedido |
| N° Celular | Del estudiante |

### Sección 2: Datos para Emisión de Comprobante de Pago
| Campo | Contenido |
|-------|-----------|
| Programa | Nombre del programa |
| Versión | Versión del programa |
| N° de Trámite | Cuenta auxiliar |

### Sección 3: Monto
| Campo | Contenido |
|-------|-----------|
| Monto (Numeral) | Bs. XXX.XX |
| Monto (Literal) | CANTIDAD EN LETRAS |

### Sección 4: Datos para Emisión de Factura
| Campo | Contenido |
|-------|-----------|
| Nombre de la Factura | Nombre ingresado por el usuario |
| NIT o CI | NIT o CI ingresado por el usuario |

### Sección 5: Denominación de la Cuenta (Destacado en recuadro)
```
DENOMINACIÓN DE LA CUENTA
UTO - APORTES EXTRAORDINARIOS - N° CUENTA 10000006050938
NIT: 120129022
```

### Sección 6: Responsable y Firma
| Campo | Contenido |
|-------|-----------|
| Responsable | Nombre del responsable |
| Firma | Firma ingresada (o línea para firmar) |
| Firma del Responsable | Línea para firma manual |

### Pie de página:
```
Documento generado electrónicamente el DD/MM/YYYY a las HH:MM:SS
```

## Datos Enviados al PDF

El controlador envía los siguientes datos por POST:

```php
[
    'apaterno' => 'Apellido Paterno',
    'amaterno' => 'Apellido Materno',
    'nombres' => 'Nombres',
    'correo' => 'correo@ejemplo.com',
    'ci' => '12345678-1A LP',
    'celular' => '77777777',
    'programa' => 'Nombre del Programa',
    'modulo' => '',
    'montoNumeral' => 'Bs. 250.00',
    'montoLiteral' => 'DOSCIENTOS CINCUENTA CON 00/100 BOLIVIANOS',
    'version' => 'V-01',
    'numeroTramite' => '2024-001',
    'cuentaAuxiliar' => '',
    'nombreFactura' => 'JUAN PEREZ',
    'nitCiFactura' => '12345678',
    'responsable' => 'María González',
    'firma' => 'Firma digital',
    'numeroOrden' => 'ORD-000001'
]
```

## Información Bancaria Fija en el PDF

La siguiente información está hardcodeada en el PDF:

```
Denominación de la Cuenta: UTO - APORTES EXTRAORDINARIOS
Número de Cuenta: 10000006050938
NIT: 120129022
```

Esta información se muestra en un recuadro destacado con fondo gris claro y bordes azules.

## Campos del Formulario Requeridos

Para que el PDF se genere correctamente, el formulario DEBE incluir:

| Campo | Nombre | Tipo | Obligatorio |
|-------|--------|------|-------------|
| Estudiante | `idcliente` | select | SÍ |
| Programa | `programa` | select | SÍ |
| Monto a Pagar | `montoAPagar` | hidden | SÍ |
| Fecha | `fechaInscripcion` | date | SÍ |
| Nombre Factura | `nombreFactura` | text | SÍ |
| NIT o CI | `nitCiFactura` | text | SÍ |
| Responsable | `responsable` | text | SÍ |
| Firma | `firma` | text | NO |
| Pago Completo | `pagoCompleto` | hidden | NO |

## Validaciones Implementadas

### En el Controlador:
1. ✅ Verifica que todos los campos obligatorios estén presentes
2. ✅ Valida que el estudiante exista
3. ✅ Valida que el programa exista
4. ✅ Calcula correctamente el monto según tipo de pago
5. ✅ Sanitiza todos los inputs con `htmlspecialchars()`
6. ✅ Verifica que no exista duplicado (estudiante ya inscrito en ese programa)

### En el PDF:
1. ✅ Valida sesión del usuario
2. ✅ Verifica que se reciban datos por POST
3. ✅ Maneja errores de generación del PDF

## Función de Conversión a Letras

La función `numeroALetras()` convierte montos numéricos a texto:

### Ejemplos:
```php
numeroALetras(250.50)  → "DOSCIENTOS CINCUENTA CON 50/100 BOLIVIANOS"
numeroALetras(1000.00) → "MIL CON 00/100 BOLIVIANOS"
numeroALetras(15.75)   → "QUINCE CON 75/100 BOLIVIANOS"
```

### Características:
- ✅ Maneja montos hasta millones
- ✅ Incluye centavos en formato XX/100
- ✅ Todo en mayúsculas
- ✅ Formato correcto según normas bolivianas

## Formato de Impresión

El PDF está optimizado para:
- 📄 **Tamaño**: LETTER (215.9mm x 279.4mm)
- 📐 **Orientación**: Vertical (Portrait)
- 🖨️ **Impresión**: Una sola hoja con ORIGINAL y COPIA
- ✂️ **División**: Línea punteada en la mitad de la hoja

### Distribución en la hoja:
```
┌─────────────────────────┐
│                         │
│      ORIGINAL           │ ← Primera mitad
│      (sin marca)        │
│                         │
├─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─┤ ← Línea punteada
│                         │
│      ** COPIA **        │ ← Segunda mitad
│      (marcada en rojo)  │
│                         │
└─────────────────────────┘
```

## Cómo Probar

### 1. Acceder al formulario:
```
http://localhost/POSGRADOFCS/ordenpago
```

### 2. Completar todos los pasos:
1. **PASO 1**: Seleccionar estudiante
2. **PASO 2**: Seleccionar grado y programa
3. **PASO 3**: Elegir tipo de pago (solo matrícula o pago completo)
4. **PASO 4**: Completar datos adicionales:
   - Nombre para factura
   - NIT o CI
   - Responsable
   - Firma (opcional)

### 3. Generar orden de pago:
- Hacer clic en "Generar Orden de Pago"
- Esperar mensaje de éxito
- El PDF se abrirá automáticamente en nueva pestaña

### 4. Verificar el PDF:
- ✅ Debe mostrar ORIGINAL en la parte superior
- ✅ Debe mostrar ** COPIA ** en rojo en la parte inferior
- ✅ Ambas secciones deben tener el mismo contenido
- ✅ Línea punteada divisoria debe ser visible
- ✅ Todos los datos deben estar completos
- ✅ Información bancaria debe ser visible

## Solución de Problemas

### Problema: El PDF no se genera
**Solución**: Verificar en consola del navegador si hay errores JavaScript

### Problema: El PDF se genera pero sin datos
**Solución**: Verificar que todos los campos del formulario estén completos

### Problema: Error "Todos los campos obligatorios deben ser completados"
**Solución**: Completar todos los campos marcados con asterisco (*)

### Problema: El PDF muestra datos incorrectos
**Solución**: Verificar que los datos en la base de datos sean correctos

### Problema: No se abre en nueva pestaña
**Solución**: Verificar que el navegador no esté bloqueando pop-ups

## Logs y Debugging

El controlador genera logs en el servidor:
```
=== RegistrarOrdenPagoControlador ejecutado ===
POST data: Array(...)
POST registrarOrdenPago detectado
Datos de orden de pago preparados: Array(...)
```

Para ver los logs en Windows (XAMPP):
```
C:\xampp\apache\logs\error.log
```

## Archivos Relacionados

### Archivos Principales:
- `controladores/ordenpago.controlador.php` - Controlador modificado
- `vistas/componentes/generar-orden-pago-pdf.php` - Generador de PDF
- `vistas/componentes/ordenpago.php` - Formulario

### Archivos de Soporte:
- `modelos/ordenpago.modelo.php` - Modelo de datos
- `modelos/conexion.modelo.php` - Conexión a BD
- `vendor/tecnickcom/tcpdf/tcpdf.php` - Librería TCPDF

### Archivos de Imágenes:
- `extensiones/imagenespdf/logouto.png` - Logo UTO
- `extensiones/imagenespdf/logofcs.png` - Logo FCS

## Notas Importantes

### 1. Información Bancaria:
La información bancaria está hardcodeada en el archivo `generar-orden-pago-pdf.php`:
```php
UTO - APORTES EXTRAORDINARIOS - N° CUENTA 10000006050938
NIT: 120129022
```

Si esta información cambia, debe modificarse en las líneas 296-304.

### 2. Logos:
Los logos deben existir en la ruta `extensiones/imagenespdf/`:
- `logouto.png` (15x15mm)
- `logofcs.png` (15x15mm)

### 3. Sesión:
El usuario debe estar logueado para generar el PDF. Si no hay sesión activa, el PDF mostrará "Acceso denegado".

### 4. Base de Datos:
La tabla `estudianteprograma` debe tener la estructura correcta con los campos:
- `idInscripcion` (PK, AUTO_INCREMENT)
- `EstudianteID`
- `ProgramaID`
- `costomatricula`
- `montoPagado`
- `pagoCompleto`
- `porcentajeDescuento`
- `montoDescuento`
- `FechaInscripcion`

## Compatibilidad

✅ **PHP**: 7.4 o superior
✅ **MySQL**: 5.7 o superior
✅ **TCPDF**: Instalado via Composer
✅ **Navegadores**: Chrome, Firefox, Edge (últimas versiones)
✅ **Sistema Operativo**: Windows, Linux, macOS

## Fecha de Implementación

**Fecha**: 2025-12-19
**Versión**: 1.0
**Estado**: ✅ IMPLEMENTADO Y FUNCIONANDO

## Conclusión

El sistema ahora genera correctamente el PDF de la orden de pago con:
- ✅ Formato de una sola hoja (ORIGINAL + COPIA)
- ✅ Todos los campos de facturación incluidos
- ✅ Información bancaria de la UTO
- ✅ Responsable y firma
- ✅ Conversión automática de monto a letras
- ✅ Diseño profesional y listo para imprimir

El PDF se puede imprimir directamente y cortar por la línea punteada para obtener ORIGINAL y COPIA.
