# 🔍 DEBUG: Registro de Usuario Estudiante

## 🐛 Problema Reportado
El sistema no está registrando usuarios para estudiantes en la base de datos.

## ✅ Cambios Realizados

### 1. **Modelo Mejorado** (`modelos/usuario.modelo.php`)

**Cambio:** Agregado manejo de errores detallado en `CrearUsuarioEstudianteModelo()`

```php
// ANTES: Solo devolvía 'error'
catch (Exception $e) {
    $conexion->rollBack();
    return 'error';
}

// AHORA: Devuelve el error específico
catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollBack();
    }
    error_log("Exception en CrearUsuarioEstudianteModelo: " . $e->getMessage());
    return 'error: ' . $e->getMessage();
}
```

### 2. **Controlador con Debug** (`controladores/usuario.controlador.php`)

**Cambio:** Agregado logging completo del flujo

```php
// Debug en cada paso del proceso
error_log("=== DEBUG CrearUsuarioEstudianteControlador ===");
error_log("POST recibido: " . print_r($_POST, true));
error_log("Procesando estudiante CI: " . $_POST['estudiante_ci']);
error_log("Nombre recibido: " . $nombre);
error_log("Credenciales generadas - Usuario: " . $usuario);
error_log("Datos a insertar: " . print_r($DatosModelo, true));
error_log("Resultado del modelo: " . $resultado);
```

## 🧪 Cómo Probar y Ver los Errores

### Paso 1: Ubicar el Archivo de Errores de PHP

**En XAMPP, los logs de error están en:**
```
C:\xampp\php\logs\php_error_log
```

### Paso 2: Limpiar el Log (Opcional)

Antes de probar, puedes limpiar el archivo de log para ver solo los nuevos errores:

1. Abre el archivo: `C:\xampp\php\logs\php_error_log`
2. Borra todo el contenido
3. Guarda el archivo vacío

### Paso 3: Intentar Crear un Usuario

1. Accede a: `http://localhost/POSGRADOFCS/?action=estudianteusr`
2. Haz clic en el botón "Asignar" de un estudiante
3. Llena el modal con los datos
4. Haz clic en "Confirmar y Crear"

### Paso 4: Revisar el Log de Errores

Abre nuevamente: `C:\xampp\php\logs\php_error_log`

Deberías ver algo como esto:

```log
[25-Nov-2025 10:30:15] === DEBUG CrearUsuarioEstudianteControlador ===
[25-Nov-2025 10:30:15] POST recibido: Array
(
    [estudiante_ci] => 12345678
    [estudiante_nombre] => JUAN
)
[25-Nov-2025 10:30:15] Procesando estudiante CI: 12345678
[25-Nov-2025 10:30:15] Verificación de usuario existente: NO EXISTE
[25-Nov-2025 10:30:15] Nombre recibido: JUAN
[25-Nov-2025 10:30:15] Credenciales generadas - Usuario: 12345678, Password texto: J12345678
[25-Nov-2025 10:30:15] Datos a insertar: Array
(
    [IdPersonal] => 12345678
    [Usuario] => 12345678
    [Password] => $2y$12$...hash...
    [Tipo] => EST
)
[25-Nov-2025 10:30:15] Resultado del modelo: exitoso
[25-Nov-2025 10:30:15] Usuario creado exitosamente
```

## 🔍 Posibles Problemas y Soluciones

### Problema 1: "No se recibió estudiante_ci en POST"

**Síntoma en el log:**
```log
[25-Nov-2025 10:30:15] No se recibió estudiante_ci en POST
```

**Causa:** El formulario no está enviando los datos correctamente.

**Solución:**
1. Verifica que el modal esté capturando los datos con el JavaScript
2. Abre la consola del navegador (F12) y verifica que los campos hidden se llenen:
   ```javascript
   console.log($('#estudiante_ci').val());  // Debe mostrar el CI
   console.log($('#estudiante_nombre').val());  // Debe mostrar el nombre
   ```

### Problema 2: "Nombre del estudiante vacío"

**Síntoma en el log:**
```log
[25-Nov-2025 10:30:15] ERROR: Nombre del estudiante vacío
```

**Causa:** El campo hidden `estudiante_nombre` no se está llenando.

**Solución:**
1. Verifica en el navegador (F12 > Elements) que el botón tenga el atributo:
   ```html
   data-nombre-pila="JUAN"
   ```
2. Verifica que el JavaScript esté funcionando (ver consola del navegador)

### Problema 3: Error SQL

**Síntoma en el log:**
```log
[25-Nov-2025 10:30:15] Error SQL en CrearUsuarioEstudianteModelo: Array(...)
```

**Causas posibles:**
- El CI ya existe en la tabla `usuario` (clave primaria duplicada)
- El CI no existe en la tabla `estudiante`
- Problemas de conexión con la base de datos

**Solución:**
1. Verifica que el CI del estudiante exista en la tabla `estudiante`:
   ```sql
   SELECT * FROM estudiante WHERE Ci = 12345678;
   ```
2. Verifica que NO exista ya en `usuario`:
   ```sql
   SELECT * FROM usuario WHERE IdPersonal = 12345678;
   ```
3. Si existe, elimínalo para poder volver a crear:
   ```sql
   DELETE FROM usuario WHERE IdPersonal = 12345678 AND Tipo = 'EST';
   ```

### Problema 4: Exception en el Modelo

**Síntoma en el log:**
```log
[25-Nov-2025 10:30:15] Exception en CrearUsuarioEstudianteModelo: SQLSTATE[...]
```

**Causa:** Error grave en la consulta SQL o conexión.

**Solución:**
1. Verifica que la tabla `usuario` existe y tiene los campos correctos:
   ```sql
   DESCRIBE usuario;
   ```
2. Verifica que los campos sean:
   - `IdPersonal` (int)
   - `Usuario` (varchar)
   - `Password` (text)
   - `Tipo` (varchar)
   - `Estado` (char)

## 📊 Estructura Esperada en la Base de Datos

### Tabla: `usuario`
```sql
CREATE TABLE `usuario` (
  `IdPersonal` int(11) NOT NULL PRIMARY KEY,
  `Usuario` varchar(20) NOT NULL,
  `Password` text NOT NULL,
  `FechaIngreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Estado` char(1) NOT NULL DEFAULT '1',
  `Tipo` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Verificación Rápida en phpMyAdmin

1. Abre phpMyAdmin
2. Selecciona tu base de datos `proyecto`
3. Ejecuta esta consulta:
   ```sql
   -- Ver estudiantes sin usuario
   SELECT e.Ci, e.Nombre, e.Apaterno, e.Amaterno
   FROM estudiante e
   LEFT JOIN usuario u ON e.Ci = u.IdPersonal
   WHERE u.IdPersonal IS NULL AND e.Estado = 1;
   ```

## 🎯 Checklist de Verificación

Antes de reportar un problema, verifica:

- [ ] El modal se abre correctamente
- [ ] Los campos del modal muestran los datos del estudiante
- [ ] Al hacer clic en "Confirmar", el formulario se envía (la página recarga)
- [ ] Revisaste el archivo de log: `C:\xampp\php\logs\php_error_log`
- [ ] Los logs muestran que se recibieron los datos POST
- [ ] Verificaste en phpMyAdmin si el usuario se creó o no
- [ ] No hay errores SQL en el log

## 📞 Información para Reportar

Si el problema persiste, reporta:

1. **Log completo** del intento de crear usuario (desde `=== DEBUG ===` hasta el final)
2. **Mensaje de error** mostrado en pantalla (si hay)
3. **Resultado de esta consulta SQL**:
   ```sql
   SELECT * FROM estudiante WHERE Ci = [EL_CI_QUE_USASTE];
   SELECT * FROM usuario WHERE IdPersonal = [EL_CI_QUE_USASTE];
   ```

## 🚀 Siguiente Paso

1. **Limpia el log de PHP**
2. **Intenta crear un usuario**
3. **Revisa el log**
4. **Comparte los resultados**

---

**Archivos modificados:**
- ✅ `modelos/usuario.modelo.php` (líneas 128-159)
- ✅ `controladores/usuario.controlador.php` (líneas 272-371)

**Log de errores:**
- 📁 `C:\xampp\php\logs\php_error_log`
