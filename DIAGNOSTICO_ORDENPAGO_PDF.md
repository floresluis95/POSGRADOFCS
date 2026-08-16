# Diagnóstico: Problema con Generación de PDF en Orden de Pago

## Pasos para Diagnosticar el Problema

### 1. Verificar TCPDF
Abre en el navegador:
```
http://localhost/POSGRADOFCS/test_pdf_simple.php
```

**Resultado esperado**:
- ✓ TCPDF encontrado
- ✓ PDF creado exitosamente
- Botón para generar PDF de prueba

**Si TCPDF no está instalado**:
```bash
cd C:\xampp\htdocs\POSGRADOFCS
composer require tecnickcom/tcpdf
```

### 2. Verificar Logs del Servidor
Abre el archivo de log de Apache:
```
C:\xampp\apache\logs\error.log
```

Busca mensajes que contengan:
- "RegistrarOrdenPagoControlador ejecutado"
- "POST registrarOrdenPago detectado"
- "PDF: Datos POST recibidos"
- "Error al obtener datos para PDF"

### 3. Verificar que el Formulario Envía Datos Correctamente

#### A. Abrir la consola del navegador (F12)
1. Ir a: `http://localhost/POSGRADOFCS/ordenpago`
2. Completar todos los pasos del formulario
3. Abrir la pestaña "Network" (Red) en las herramientas de desarrollador
4. Hacer clic en "Generar Orden de Pago"

#### B. Verificar la petición POST
En la pestaña "Network", buscar la petición a "ordenpago" y verificar:

**Headers**:
- Request Method: POST
- Status Code: 200

**Form Data** debe incluir:
- `registrarOrdenPago`: (presente)
- `idcliente`: (ID del estudiante)
- `programa`: (ID del programa)
- `montoAPagar`: (monto numérico)
- `fechaInscripcion`: (fecha YYYY-MM-DD)
- `nombreFactura`: (nombre)
- `nitCiFactura`: (NIT/CI)
- `responsable`: (nombre responsable)
- `firma`: (firma opcional)

#### C. Verificar la respuesta
La respuesta HTML debe contener:
```html
<script src="vistas/recursos/sweetalert.min.js"></script>
<script>
swal("EXITOSO!", "Orden de Pago registrada. Se generará el PDF.", "success");
...
</script>
<form id="formPDF" method="POST" action="vistas/componentes/generar-orden-pago-pdf.php" target="_blank">
...
</form>
```

### 4. Verificar que el Formulario PDF se Envía

#### A. En la consola del navegador (Console)
Ejecutar:
```javascript
document.getElementById('formPDF')
```

**Resultado esperado**: Debe mostrar el elemento form

**Si devuelve null**: El formulario no se creó, hay un error en el controlador

#### B. Verificar que se abre nueva pestaña
Después de 1 segundo del mensaje de éxito, debe abrirse una nueva pestaña con:
- URL: `vistas/componentes/generar-orden-pago-pdf.php`
- Contenido: PDF o mensaje de error

### 5. Verificar Errores Comunes

#### Error: "Acceso denegado"
**Causa**: Sesión no válida
**Solución**: Verificar que estás logueado en el sistema

#### Error: "Método no permitido"
**Causa**: Se intentó acceder con GET en lugar de POST
**Solución**: El formulario debe enviar datos por POST automáticamente

#### Error: Página en blanco
**Causa**: Error de PHP no capturado
**Solución**:
1. Habilitar errores PHP en `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
2. Reiniciar Apache
3. Intentar generar PDF nuevamente

#### Error: "No se pudieron obtener los datos para el PDF"
**Causa**: La consulta SQL no retornó datos
**Solución**: Verificar que:
1. El estudiante existe en la tabla `estudiante`
2. El programa existe en la tabla `programa`
3. La inscripción se creó correctamente en `estudianteprograma`

### 6. Verificar Base de Datos

Ejecutar en phpMyAdmin:
```sql
-- Verificar última inscripción
SELECT * FROM estudianteprograma ORDER BY idInscripcion DESC LIMIT 1;

-- Verificar datos completos de la última inscripción
SELECT
    ep.*,
    e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
    p.NombrePrograma, p.Codigo, p.Version, p.NumeroTramite
FROM estudianteprograma ep
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
ORDER BY ep.idInscripcion DESC
LIMIT 1;
```

**Verificar que todos los campos existen**:
- Si falta algún campo (Version, NumeroTramite), la consulta fallará
- Si el estudiante o programa no existe, no retornará filas

### 7. Prueba Manual del PDF

Crear archivo `test_manual_pdf.php` con datos hardcodeados:

```php
<?php
session_start();
$_SESSION['Validar'] = true;

$_POST['apaterno'] = 'PEREZ';
$_POST['amaterno'] = 'GOMEZ';
$_POST['nombres'] = 'JUAN';
$_POST['correo'] = 'juan@example.com';
$_POST['ci'] = '12345678 LP';
$_POST['celular'] = '77777777';
$_POST['programa'] = 'DIPLOMADO EN ODONTOLOGIA';
$_POST['modulo'] = '';
$_POST['montoNumeral'] = 'Bs. 250.00';
$_POST['montoLiteral'] = 'DOSCIENTOS CINCUENTA CON 00/100 BOLIVIANOS';
$_POST['version'] = 'V-01';
$_POST['numeroTramite'] = '2024-001';
$_POST['cuentaAuxiliar'] = '';
$_POST['nombreFactura'] = 'JUAN PEREZ';
$_POST['nitCiFactura'] = '12345678';
$_POST['responsable'] = 'MARIA GONZALEZ';
$_POST['firma'] = 'Firma digital';
$_POST['numeroOrden'] = 'ORD-000001';

require_once 'vistas/componentes/generar-orden-pago-pdf.php';
?>
```

Abrir: `http://localhost/POSGRADOFCS/test_manual_pdf.php`

**Resultado esperado**: Debe generar y mostrar el PDF

### 8. Verificar Campos Vacíos en Programa

Si algunos programas no tienen `Version` o `NumeroTramite`:

```sql
-- Actualizar programas sin version
UPDATE programa SET Version = 'V-01' WHERE Version IS NULL OR Version = '';

-- Actualizar programas sin numero de tramite
UPDATE programa SET NumeroTramite = 'SIN-TRAMITE' WHERE NumeroTramite IS NULL OR NumeroTramite = '';
```

## Soluciones Rápidas

### Solución 1: Verificar que todos los archivos existen
```bash
# En C:\xampp\htdocs\POSGRADOFCS
dir controladores\ordenpago.controlador.php
dir vistas\componentes\generar-orden-pago-pdf.php
dir vendor\tecnickcom\tcpdf\tcpdf.php
```

### Solución 2: Verificar permisos
Asegurarse de que Apache tenga permisos de lectura en:
- `controladores/`
- `vistas/componentes/`
- `vendor/`

### Solución 3: Limpiar cache del navegador
1. Ctrl + Shift + Delete
2. Borrar cache y cookies
3. Reintentar

### Solución 4: Verificar JavaScript
En la consola del navegador, ejecutar:
```javascript
// Verificar que SweetAlert está cargado
typeof swal

// Verificar que jQuery está cargado
typeof jQuery

// Verificar que el formulario existe después del submit
setTimeout(function() {
    console.log(document.getElementById('formPDF'));
}, 2000);
```

## Checklist de Diagnóstico

- [ ] TCPDF está instalado correctamente
- [ ] El formulario se envía con todos los campos
- [ ] La base de datos tiene los datos correctos
- [ ] Los logs no muestran errores
- [ ] La sesión está activa
- [ ] JavaScript no muestra errores en consola
- [ ] El formulario PDF se crea en el HTML
- [ ] El formulario PDF se envía después de 1 segundo
- [ ] Se abre nueva pestaña con el PDF
- [ ] El PDF se genera correctamente

## Información de Debugging

### Activar logs detallados en el controlador

Editar `controladores/ordenpago.controlador.php` y agregar más logs:

```php
error_log("=== INICIO GENERACION PDF ===");
error_log("ID Inscripcion: " . $idInscripcion);
error_log("Datos obtenidos: " . print_r($datos, true));
error_log("Monto literal: " . $montoLiteral);
error_log("=== FIN GENERACION PDF ===");
```

### Ver logs en tiempo real (Windows)

Abrir PowerShell y ejecutar:
```powershell
Get-Content C:\xampp\apache\logs\error.log -Wait -Tail 20
```

Luego generar la orden de pago y ver los logs en tiempo real.

## Contacto de Soporte

Si después de todos estos pasos el problema persiste:

1. Captura de pantalla de:
   - Consola del navegador (tab Console)
   - Network tab mostrando la petición POST
   - Mensaje de error (si hay)

2. Copia del error.log:
   ```
   C:\xampp\apache\logs\error.log
   ```

3. Resultado de la consulta SQL:
   ```sql
   SELECT * FROM estudianteprograma ORDER BY idInscripcion DESC LIMIT 1;
   ```

Con esta información se puede diagnosticar el problema exacto.
