# 🔧 INSTRUCCIONES DE DEPURACIÓN - Sistema de Inscripciones

## ✅ CORRECCIONES REALIZADAS

He corregido y mejorado todo el flujo de inscripción con logs de depuración en cada componente:

### 1. **JavaScript** (`vistas/recursos/assets/js/scripts/inscripcion.js`)
- ✅ Agregados logs en consola para debugging
- ✅ Mejorado manejo de errores con mensajes detallados
- ✅ Validación exhaustiva de datos antes de enviar

### 2. **Controlador** (`controladores/inscripcion.controlador.php`)
- ✅ Agregados logs en error_log de PHP
- ✅ Mejorada validación de grados académicos (incluye variantes con y sin tilde)
- ✅ Respuestas JSON con información de debug

### 3. **Modelo** (`modelos/inscripcion.modelo.php`)
- ✅ Query mejorada para buscar con variantes de acentos
- ✅ Logs detallados de la búsqueda SQL
- ✅ Manejo robusto de errores PDO

### 4. **Archivo de Prueba** (`test_programas.php`)
- ✅ Creado para verificar la base de datos directamente

---

## 🔍 PASOS PARA DIAGNOSTICAR EL PROBLEMA

### **PASO 1: Verificar la Base de Datos**

1. Abre tu navegador y ve a:
   ```
   http://localhost/POSGRADOFCS/test_programas.php
   ```

2. Este archivo mostrará:
   - Todos los programas en la base de datos
   - Los valores EXACTOS de la columna `GradoAcademico`
   - Resultados de búsqueda por cada grado
   - Test directo del modelo

3. **IMPORTANTE**: Verifica si los valores en la columna `GradoAcademico` son:
   - `DIPLOMADO`
   - `MAESTRIA` (sin tilde)
   - `MAESTRÍA` (con tilde)
   - `ESPECIALIDAD`

---

### **PASO 2: Abrir la Consola del Navegador**

1. Ve a la página de inscripción:
   ```
   http://localhost/POSGRADOFCS/inscripcion
   ```

2. Abre la consola del navegador:
   - **Chrome/Edge**: Presiona `F12` o `Ctrl + Shift + I`
   - **Firefox**: Presiona `F12` o `Ctrl + Shift + K`
   - Ve a la pestaña **"Console"**

3. Deberías ver el mensaje:
   ```
   Script de inscripción cargado correctamente
   Todos los eventos han sido configurados correctamente
   ```

---

### **PASO 3: Probar el Select de Grado Académico**

1. En la página de inscripción, selecciona un grado académico (DIPLOMADO, MAESTRÍA, o ESPECIALIDAD)

2. En la consola del navegador deberías ver:
   ```
   Grado académico seleccionado: DIPLOMADO
   Enviando petición AJAX para obtener programas...
   Petición enviada con datos: {action: "obtenerProgramas", gradoAcademico: "DIPLOMADO"}
   Respuesta recibida: {success: true, data: [...], count: X}
   Se cargaron X programas
   ```

3. **SI NO VES ESTOS MENSAJES**:
   - El JavaScript no se está cargando correctamente
   - Verifica que jQuery esté cargado
   - Verifica que el archivo `inscripcion.js` esté en la ruta correcta

4. **SI VES UN ERROR**:
   - Revisa el mensaje de error en la consola
   - Copia el error completo

---

### **PASO 4: Verificar Logs de PHP**

1. Abre el archivo de logs de errores de PHP:
   - En XAMPP: `C:\xampp\apache\logs\error.log`
   - O en la carpeta de logs de PHP según tu configuración

2. Busca líneas que contengan:
   ```
   ObtenerProgramasPorGradoControlador
   ListarProgramasPorGradoModelo
   ```

3. Deberías ver algo como:
   ```
   [timestamp] ObtenerProgramasPorGradoControlador - POST recibido: Array...
   [timestamp] ObtenerProgramasPorGradoControlador - Buscando programas para: DIPLOMADO
   [timestamp] ListarProgramasPorGradoModelo - Buscando: DIPLOMADO
   [timestamp] ListarProgramasPorGradoModelo - Resultados encontrados: X
   ```

---

## 🐛 PROBLEMAS COMUNES Y SOLUCIONES

### **Problema 1: "No se encontraron programas"**

**Causa Posible**: No hay programas en la base de datos para ese grado académico

**Solución**:
1. Ejecuta `test_programas.php` para ver qué programas existen
2. Verifica que tengas al menos un programa con `Estado = 1` (activo)
3. Verifica que el valor de `GradoAcademico` en la BD coincida exactamente

---

### **Problema 2: Error 404 al hacer AJAX**

**Causa**: La ruta del controlador es incorrecta

**Solución**:
1. Verifica que el archivo existe en: `controladores/inscripcion.controlador.php`
2. Verifica los permisos del archivo
3. Prueba acceder directamente: `http://localhost/POSGRADOFCS/controladores/inscripcion.controlador.php`

---

### **Problema 3: Error 500 del servidor**

**Causa**: Error de PHP en el servidor

**Solución**:
1. Revisa el log de errores de PHP (`error.log`)
2. Busca el mensaje de error específico
3. Verifica que la conexión a la base de datos funcione

---

### **Problema 4: El evento onChange no se dispara**

**Causa**: jQuery no está cargado o conflicto de scripts

**Solución**:
1. Abre la consola y escribe: `jQuery` o `$`
2. Si sale "undefined", jQuery no está cargado
3. Verifica en el HTML que jQuery se cargue ANTES de `inscripcion.js`
4. Orden correcto:
   ```html
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="vistas/recursos/assets/js/scripts/inscripcion.js"></script>
   ```

---

### **Problema 5: Diferencia entre "MAESTRIA" y "MAESTRÍA"**

**Causa**: Mismatch entre el valor del select y la base de datos

**Solución**:
- Ya está corregido en el modelo
- El sistema ahora busca ambas variantes (con y sin tilde)
- Funciona con: `MAESTRIA`, `MAESTRÍA`, `maestria`, `maestría`

---

## 📋 CHECKLIST DE VERIFICACIÓN

Marca cada punto que verifiques:

- [ ] XAMPP está corriendo (Apache + MySQL)
- [ ] La base de datos tiene programas con Estado = 1
- [ ] jQuery se carga correctamente (verificar en consola)
- [ ] El archivo `inscripcion.js` se carga (mensaje en consola)
- [ ] El select de grado académico tiene el ID correcto: `gradoAcademico`
- [ ] El archivo `inscripcion.controlador.php` existe y tiene permisos
- [ ] Los logs de PHP se están generando
- [ ] La consola del navegador muestra los logs del JavaScript

---

## 🚀 PRUEBA RÁPIDA

Ejecuta esto en la consola del navegador (en la página de inscripción):

```javascript
// Test 1: Verificar que jQuery está cargado
console.log('jQuery cargado:', typeof $ !== 'undefined');

// Test 2: Verificar que el select existe
console.log('Select existe:', $('#gradoAcademico').length > 0);

// Test 3: Probar AJAX manualmente
$.ajax({
    url: 'controladores/inscripcion.controlador.php',
    type: 'POST',
    dataType: 'json',
    data: {
        action: 'obtenerProgramas',
        gradoAcademico: 'DIPLOMADO'
    },
    success: function(response) {
        console.log('Test AJAX exitoso:', response);
    },
    error: function(xhr, status, error) {
        console.error('Test AJAX falló:', error);
        console.error('Response:', xhr.responseText);
    }
});
```

---

## 📞 REPORTAR EL PROBLEMA

Si después de seguir todos estos pasos el problema persiste, reporta:

1. ✅ Resultado de `test_programas.php` (captura de pantalla)
2. ✅ Logs de la consola del navegador (captura o copia el texto)
3. ✅ Logs del archivo `error.log` de PHP (últimas 50 líneas)
4. ✅ Resultado del "Test Rápido" en consola
5. ✅ Versión de PHP (`php -v` en terminal)
6. ✅ Versión de MySQL

---

## 🎯 RESUMEN DE ARCHIVOS MODIFICADOS

1. ✅ `vistas/recursos/assets/js/scripts/inscripcion.js` - JavaScript con logs
2. ✅ `controladores/inscripcion.controlador.php` - Controlador con debug
3. ✅ `modelos/inscripcion.modelo.php` - Modelo con búsqueda mejorada
4. ✅ `test_programas.php` - Archivo de prueba (NUEVO)
5. ✅ `INSTRUCCIONES_DEPURACION.md` - Este archivo (NUEVO)

**TODOS LOS CAMBIOS MANTIENEN LA ARQUITECTURA MVC Y SON RETROCOMPATIBLES**
