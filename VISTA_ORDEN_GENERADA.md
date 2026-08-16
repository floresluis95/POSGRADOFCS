# Nueva Vista: Orden de Pago Generada

## Funcionalidad Implementada

Después de generar una orden de pago, el sistema ahora **redirige a una vista dedicada** donde el estudiante puede:

✅ **Ver todos los detalles de su orden de pago**
✅ **Descargar el PDF cuando lo necesite**
✅ **Ver instrucciones de pago**
✅ **Generar una nueva orden si lo desea**

## Flujo del Usuario

### ANTES (Problema)
1. Usuario completa formulario
2. Clic en "Generar Orden de Pago"
3. **PDF se abre automáticamente**
4. **Si cierra el PDF, lo pierde**
5. Debe volver a generar la orden

### AHORA (Solución)
1. Usuario completa formulario
2. Clic en "Generar Orden de Pago"
3. **Mensaje de éxito con SweetAlert**
4. **Redirige a vista de orden generada**
5. **Usuario puede descargar PDF cuando quiera**
6. **Puede volver a descargarlo múltiples veces**

## Archivos Creados/Modificados

### 1. Nueva Vista

**Archivo**: `vistas/componentes/orden-generada.php`

**Funcionalidad**:
- Muestra información completa de la orden
- Botón para descargar PDF
- Botón para generar nueva orden
- Instrucciones de pago
- Diseño moderno y responsive

**URL de Acceso**:
```
http://localhost/POSGRADOFCS/orden-generada&id=[ID_ORDEN]
```

**Ejemplo**:
```
http://localhost/POSGRADOFCS/orden-generada&id=1
```

### 2. Modelo de Enlaces

**Archivo**: `modelos/enlaces.modelo.php` (línea 30)

**Cambio**: Agregada ruta `orden-generada`

```php
$enlace == 'orden-generada'||
```

### 3. Controlador

**Archivo**: `controladores/ordenpago.controlador.php` (líneas 89-101)

**ANTES** (Generaba PDF automáticamente):
```php
if ($resultado['status'] == 'exitoso') {
    // Crear formulario PDF y enviarlo automáticamente
    echo '<form id="formPDF">...</form>';
    echo '<script>form.submit();</script>';
}
```

**AHORA** (Redirige a vista):
```php
if ($resultado['status'] == 'exitoso') {
    $idOrdenPago = $resultado['idOrdenPago'];

    echo '<script>
    swal("EXITOSO!", "Orden de Pago registrada correctamente", "success")
    .then(function () {
        window.location.href = "orden-generada&id=' . $idOrdenPago . '";
    });
    </script>';
}
```

### 4. Plantilla

**Archivo**: `vistas/plantilla.php` (líneas 172 y 228)

**Cambio**: Excluir `orden-generada` de cargar SweetAlert2

```php
if ($action !== 'ordenpago' && $action !== 'orden-generada'):
```

## Diseño de la Vista

### Secciones de la Vista

1. **Encabezado de Éxito**
   - Ícono de check animado
   - Título "¡Orden de Pago Generada Exitosamente!"
   - Fondo con gradiente morado

2. **Número de Orden**
   - Destacado en caja con fondo gris
   - Formato: `ORD-YmdHis-XXXX`
   - Tamaño grande para fácil lectura

3. **Información del Estudiante**
   - Nombre completo
   - Cédula de identidad
   - Correo electrónico
   - Celular

4. **Información del Programa**
   - Nombre completo del programa
   - Código
   - Versión
   - Tipo de pago (Solo matrícula / Pago completo)
   - Fecha de generación

5. **Información de Facturación**
   - Nombre para factura
   - NIT/CI

6. **Monto a Pagar**
   - Caja destacada con gradiente
   - Monto en números grandes
   - Monto en letras
   - Descuento aplicado (si existe)

7. **Botones de Acción**
   - **Descargar Orden de Pago**: Abre PDF en nueva pestaña
   - **Generar Nueva Orden**: Vuelve al formulario

8. **Instrucciones de Pago**
   - Caja amarilla con instrucciones
   - Pasos numerados
   - Destacado del número de orden

### Características de Diseño

✅ **Responsive**: Adaptable a móviles y tablets
✅ **Moderno**: Uso de gradientes y sombras
✅ **Animaciones**: Ícono de éxito con animación
✅ **Colores consistentes**: Paleta morada del sistema
✅ **Legible**: Fuente Poppins, jerarquía clara
✅ **Imprimible**: (si se desea agregar función de impresión)

## Cómo Funciona

### 1. Usuario Completa Formulario

```
[Formulario ordenpago.php]
         ↓
[Clic en "Generar Orden de Pago"]
         ↓
[POST a ordenpago.controlador.php]
```

### 2. Controlador Registra y Redirige

```php
// En ordenpago.controlador.php
$resultado = OrdenPagoModelos::RegistrarPreregistroModelo($datosOrdenPago);

if ($resultado['status'] == 'exitoso') {
    $idOrdenPago = $resultado['idOrdenPago'];

    // SweetAlert + Redirección
    echo '<script>
        swal("EXITOSO!", "...", "success")
        .then(() => {
            window.location.href = "orden-generada&id=' . $idOrdenPago . '";
        });
    </script>';
}
```

### 3. Vista Carga Datos de la Orden

```php
// En orden-generada.php
$idOrdenPago = $_GET['id'];

// Consulta SQL para obtener todos los datos
$stmt = $pdo->prepare("
    SELECT op.*, e.*, p.*
    FROM ordenpago op
    INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
    INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
    WHERE op.IdOrdenPago = :id
");

$orden = $stmt->fetch(PDO::FETCH_ASSOC);
```

### 4. Usuario Descarga PDF

```html
<!-- Formulario oculto con todos los datos -->
<form method="POST" action="vistas/componentes/generar-orden-pago-pdf.php" target="_blank">
    <input type="hidden" name="apaterno" value="...">
    <input type="hidden" name="nombres" value="...">
    <!-- ... más campos ... -->

    <button type="submit">
        Descargar Orden de Pago
    </button>
</form>
```

**Resultado**: PDF se abre en **nueva pestaña**, la vista actual permanece abierta.

## Ventajas de Esta Implementación

### 1. Mejor Experiencia de Usuario

✅ **No pierde el PDF**: Puede descargarlo cuantas veces quiera
✅ **Información clara**: Ve todos los detalles antes de descargar
✅ **Instrucciones**: Sabe qué hacer después
✅ **Control**: Decide cuándo descargar

### 2. Menos Errores

✅ **No necesita regenerar**: Si cierra el PDF por error
✅ **Validación**: Ve que los datos son correctos antes de descargar
✅ **Confirmación visual**: Pantalla de éxito clara

### 3. Mejor Flujo

✅ **Separación de responsabilidades**:
   - Formulario = Captura datos
   - Vista orden generada = Muestra resultado
   - PDF = Documento descargable

### 4. Facilita Futuras Mejoras

✅ **Fácil agregar**:
   - Botón de imprimir
   - Envío por correo
   - Compartir en WhatsApp
   - Código QR de pago
   - Estado de la orden

## Consultas SQL Útiles

### Ver todas las órdenes generadas hoy

```sql
SELECT
    op.NumeroOrden,
    e.Nombre,
    e.Apaterno,
    p.NombrePrograma,
    op.MontoFinal,
    op.FechaGeneracion
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE DATE(op.FechaGeneracion) = CURDATE()
ORDER BY op.FechaGeneracion DESC;
```

### Ver detalle de una orden específica

```sql
SELECT
    op.*,
    e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp,
    p.NombrePrograma, p.GradoAcademico, p.Version
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE op.IdOrdenPago = 1;
```

### Verificar que la orden existe antes de mostrar

```sql
SELECT COUNT(*) as existe
FROM ordenpago
WHERE IdOrdenPago = 1;
```

## Manejo de Errores

### 1. ID de Orden Inválido

```php
$idOrdenPago = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idOrdenPago == 0) {
    echo '<script>window.location.href = "ordenpago";</script>';
    exit;
}
```

**Resultado**: Redirige al formulario de orden de pago

### 2. Orden No Existe

```php
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    echo '<script>window.location.href = "ordenpago";</script>';
    exit;
}
```

**Resultado**: Redirige al formulario de orden de pago

### 3. Datos Incompletos

La vista verifica que todos los campos existan antes de mostrarlos:

```php
<?php if (!empty($orden['Complemento'])): ?>
    -<?php echo $orden['Complemento']; ?>
<?php endif; ?>
```

## Personalización

### Cambiar Colores

En `orden-generada.php`, línea ~40:

```css
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.success-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

**Cambiar a verde**:
```css
background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
```

### Agregar Logo

En `orden-generada.php`, dentro de `.success-header`:

```html
<div class="success-header">
    <img src="vistas/recursos/assets/media/logos/logo.png" alt="Logo" style="max-width: 150px; margin-bottom: 20px;">
    <div class="success-icon">✓</div>
    ...
</div>
```

### Cambiar Mensaje de Instrucciones

En `orden-generada.php`, línea ~280:

```html
<ol style="margin: 0; padding-left: 20px; color: #856404;">
    <li>Descarga tu orden de pago...</li>
    <li>Realiza el pago en el Banco Unión...</li>
    <li>Presenta el voucher...</li>
    <li>Guarda este número de orden...</li>
</ol>
```

## Futuras Mejoras Sugeridas

### 1. Envío por Correo

Agregar botón para enviar PDF por email:

```html
<button onclick="enviarPorCorreo(<?php echo $idOrdenPago; ?>)">
    <i class="flaticon2-mail"></i>
    Enviar por Correo
</button>
```

### 2. Código QR de Pago

Generar QR con información de pago:

```php
// Usar librería PHP QR Code
$qrData = "ORDEN:" . $orden['NumeroOrden'] . "|MONTO:" . $orden['MontoFinal'];
$qrCode = QRcode::png($qrData, 'qr-temp.png');
```

### 3. Compartir en WhatsApp

```html
<a href="https://wa.me/?text=Mi%20orden%20de%20pago:%20<?php echo urlencode($orden['NumeroOrden']); ?>"
   class="btn-download" target="_blank">
    <i class="flaticon2-whatsapp"></i>
    Compartir en WhatsApp
</a>
```

### 4. Estado de la Orden en Tiempo Real

Mostrar si la orden está:
- PENDIENTE (esperando pago)
- EN REVISIÓN (voucher presentado)
- CONFIRMADA (pago verificado)
- VENCIDA (más de X días)

### 5. Histórico de Órdenes

Crear vista para ver todas las órdenes del estudiante:

```
http://localhost/POSGRADOFCS/mis-ordenes
```

## Pruebas Recomendadas

### Test 1: Generación Normal

1. Ir a `ordenpago`
2. Completar formulario con datos válidos
3. Generar orden
4. **Verificar**: Redirige a `orden-generada&id=X`
5. **Verificar**: Todos los datos se muestran correctamente
6. Descargar PDF
7. **Verificar**: PDF se abre en nueva pestaña
8. **Verificar**: Vista original sigue abierta

### Test 2: URL Directa

1. Ir directamente a: `orden-generada&id=1`
2. **Verificar**: Muestra la orden correctamente
3. **Verificar**: Puede descargar PDF

### Test 3: ID Inválido

1. Ir a: `orden-generada&id=99999`
2. **Verificar**: Redirige a `ordenpago`

### Test 4: Sin ID

1. Ir a: `orden-generada`
2. **Verificar**: Redirige a `ordenpago`

### Test 5: Múltiples Descargas

1. Generar orden
2. Descargar PDF 3 veces
3. **Verificar**: Cada vez se genera correctamente
4. **Verificar**: Vista permanece funcional

## Troubleshooting

### Problema: Página en blanco

**Causa**: Error de PHP
**Solución**:
1. Ver `C:\xampp\apache\logs\error.log`
2. Verificar que la orden existe en la base de datos

### Problema: Datos no se muestran

**Causa**: Consulta SQL no retorna datos
**Solución**:
```sql
-- Verificar que la orden existe
SELECT * FROM ordenpago WHERE IdOrdenPago = [ID];

-- Verificar joins
SELECT COUNT(*)
FROM ordenpago op
INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
WHERE op.IdOrdenPago = [ID];
```

### Problema: PDF no se descarga

**Causa**: Formulario POST no funciona
**Solución**:
1. Verificar en consola del navegador (F12)
2. Verificar que `generar-orden-pago-pdf.php` existe
3. Verificar permisos del archivo

### Problema: Estilos no se cargan

**Causa**: Rutas CSS incorrectas
**Solución**: Verificar que las rutas en `<link>` apunten correctamente desde la raíz del proyecto

---

**Fecha de implementación**: 19/12/2025
**Versión**: 2.1 - Vista de Orden Generada
**Estado**: ✅ **IMPLEMENTADO Y FUNCIONAL**
