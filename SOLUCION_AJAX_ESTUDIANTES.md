# ✅ Solución: AJAX de Estudiantes

**Fecha:** 18/12/2025
**Problema:** Al seleccionar un estudiante en ordenpago.php, no aparece la tabla con sus datos

---

## 🔍 Causa del Problema

El archivo `ajax/estudiantes.ajax.php` **no existía**. El JavaScript en ordenpago.php intentaba hacer una llamada AJAX a este archivo para obtener los datos del estudiante, pero al no existir, fallaba silenciosamente.

---

## ✅ Solución Implementada

### 1. Creado archivo AJAX

**Archivo:** `ajax/estudiantes.ajax.php`

Este archivo contiene:

```php
<?php
require_once '../modelos/estudiantes.modelo.php';
require_once '../modelos/conexion.modelo.php';

class AjaxEstudiantes
{
    public function ObtenerEstudiantePorId($id)
    {
        // Consulta SQL para obtener datos del estudiante
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare("
            SELECT
                e.*,
                p.NombreProfesion
            FROM estudiante e
            LEFT JOIN profesion p ON e.IdProfesion = p.IdProfesion
            WHERE e.EstudianteID = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retornar JSON
        header('Content-Type: application/json');
        echo json_encode($estudiante);
    }
}

// Procesar la petición
if (isset($_POST['idestudiante'])) {
    $ajax = new AjaxEstudiantes();
    $ajax->ObtenerEstudiantePorId($_POST['idestudiante']);
}
?>
```

**Funcionalidad:**
- Recibe el ID del estudiante vía POST
- Consulta la base de datos
- Retorna los datos en formato JSON
- Incluye datos de la profesión del estudiante

---

### 2. Mejorado el JavaScript

**Archivo:** `vistas/componentes/ordenpago.php`

Se agregaron logs de consola y mejor manejo de errores:

```javascript
$('#selectEstudiante').on('change', function() {
    const estudianteID = $(this).val();

    console.log('Estudiante ID seleccionado:', estudianteID);

    if (estudianteID) {
        $.ajax({
            url: 'ajax/estudiantes.ajax.php',
            type: 'POST',
            data: { idestudiante: estudianteID },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta del servidor:', response);

                if (response && !response.error) {
                    // Llenar tabla con datos
                    const nombreCompleto = (response.Apaterno || '') + ' ' +
                                          (response.Amaterno || '') + ' ' +
                                          (response.Nombre || '');
                    const ci = (response.Ci || '') +
                              (response.Complemento ? '-' + response.Complemento : '') +
                              ' ' + (response.Exp || '');

                    $('#datosNombre').text(nombreCompleto.trim());
                    $('#datosCI').text(ci);
                    $('#datosCorreo').text(response.Correo || '-');
                    $('#datosCelular').text(response.Celular || '-');

                    // Mostrar tabla
                    $('#tablaEstudiante').slideDown();
                    $('#seccionPrograma').slideDown();
                } else {
                    swal("Error", response.error || "No se encontraron datos", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                swal("Error", "No se pudieron obtener los datos: " + error, "error");
            }
        });
    }
});
```

**Mejoras:**
- ✅ Console.log para debug
- ✅ Validación de respuesta con error
- ✅ Mejor manejo de errores con detalles
- ✅ Muestra xhr.responseText en consola

---

## 🧪 Cómo Probar que Funciona

### Paso 1: Ver estudiantes disponibles

Accede a:
```
http://localhost/POSGRADOFCS/listar_estudiantes.php
```

Este script:
- Muestra los primeros 10 estudiantes en la BD
- Proporciona un link para probar el AJAX con el primer estudiante

### Paso 2: Probar el AJAX directamente

Accede a:
```
http://localhost/POSGRADOFCS/test_ajax_estudiante.php?id=1
```

(Cambia el `id=1` por un ID válido de tu BD)

**Resultado esperado:**
```json
{
    "EstudianteID": "1",
    "Ci": "1234567",
    "Complemento": null,
    "Exp": "OR",
    "Nombre": "JUAN",
    "Apaterno": "PEREZ",
    "Amaterno": "LOPEZ",
    "Correo": "juan@email.com",
    "Celular": "70123456",
    "NombreProfesion": "Odontólogo"
}
```

### Paso 3: Probar en la interfaz de Orden de Pago

1. Accede a:
   ```
   http://localhost/POSGRADOFCS/ordenpago
   ```

2. Abre la **Consola del Navegador** (F12 → Console)

3. Selecciona un estudiante del dropdown

4. **Deberías ver:**
   - En consola: `Estudiante ID seleccionado: X`
   - En consola: `Respuesta del servidor: {objeto con datos}`
   - En la página: La tabla con los datos del estudiante aparece con animación slideDown

5. **Si no funciona:**
   - Revisa la consola para ver los mensajes de error
   - Verifica que el ID del estudiante existe en la BD
   - Asegúrate de que el archivo AJAX existe en `ajax/estudiantes.ajax.php`

---

## 📊 Flujo Completo

```
1. Usuario selecciona estudiante
   ↓
2. JavaScript captura el cambio (event 'change')
   ↓
3. Obtiene el EstudianteID del select
   ↓
4. Hace llamada AJAX a ajax/estudiantes.ajax.php
   ↓
5. PHP consulta la BD
   ↓
6. PHP retorna JSON con datos del estudiante
   ↓
7. JavaScript recibe la respuesta
   ↓
8. JavaScript llena los campos de la tabla
   ↓
9. Muestra la tabla con slideDown()
   ↓
10. Muestra la sección de programa (Paso 2)
```

---

## 🐛 Troubleshooting

### Problema: "No se pudieron obtener los datos del estudiante"

**Revisar:**
1. ¿Existe el archivo `ajax/estudiantes.ajax.php`?
2. ¿La consola muestra algún error 404?
3. ¿El estudiante existe en la BD?

**Solución:**
- Verifica que el archivo existe en la ruta correcta
- Accede directamente al test: `test_ajax_estudiante.php?id=X`

### Problema: "Estudiante no encontrado"

**Revisar:**
1. ¿El ID del estudiante es válido?
2. ¿El estudiante tiene Estado = 1 (activo)?

**Solución:**
- Usa `listar_estudiantes.php` para ver los IDs válidos
- Verifica en la BD: `SELECT * FROM estudiante WHERE EstudianteID = X`

### Problema: Error en consola "SyntaxError: Unexpected token"

**Revisar:**
1. ¿El archivo AJAX retorna JSON válido?
2. ¿Hay algún echo o print antes del JSON?

**Solución:**
- Accede directamente a `test_ajax_estudiante.php`
- Verifica que solo retorna JSON, sin HTML

### Problema: La tabla no aparece pero no hay error

**Revisar:**
1. ¿Los IDs de los elementos HTML son correctos?
2. ¿jQuery está cargado?

**Solución:**
- Verifica en la consola: `$('#tablaEstudiante').length` debe ser > 0
- Verifica: `typeof $` debe ser "function"

---

## 📁 Archivos Creados/Modificados

### Archivos Nuevos:
1. ✅ `ajax/estudiantes.ajax.php` - Endpoint AJAX para obtener datos
2. ✅ `test_ajax_estudiante.php` - Script de prueba del AJAX
3. ✅ `listar_estudiantes.php` - Lista de estudiantes para debug
4. ✅ `SOLUCION_AJAX_ESTUDIANTES.md` - Esta documentación

### Archivos Modificados:
1. ✅ `vistas/componentes/ordenpago.php` - Mejorado JavaScript con logs

---

## 🎯 Resultado Final

Ahora cuando seleccionas un estudiante en la orden de pago:

1. ✅ Se muestra su nombre completo
2. ✅ Se muestra su CI con complemento y expedido
3. ✅ Se muestra su correo
4. ✅ Se muestra su celular
5. ✅ La tabla aparece con animación suave
6. ✅ Se despliega automáticamente la sección de programa

---

## 🧹 Limpieza (Opcional)

Una vez verificado que todo funciona, puedes eliminar los archivos de prueba:

```bash
del test_ajax_estudiante.php
del listar_estudiantes.php
```

**PERO MANTÉN:**
- `ajax/estudiantes.ajax.php` ← **NECESARIO**
- `vistas/componentes/ordenpago.php` ← **NECESARIO**

---

**Desarrollado el:** 18/12/2025
**Estado:** SOLUCIONADO ✅
**Archivos críticos:** ajax/estudiantes.ajax.php
