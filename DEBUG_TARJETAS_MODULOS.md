# 🔧 Guía de Debugging: Tarjetas de Módulos

## Problema Reportado
Las tarjetas de módulos no se ven cuando se abre el modal de "Inscribir a Módulo".

---

## ✅ Correcciones Aplicadas

### 1. JavaScript actualizado (`inscripcionmodulo.js`)
- ✅ Corregido: Limpiar elementos correctos (ahora usa `#contenedorModulos`)
- ✅ Agregado: Logs detallados en consola
- ✅ Agregado: Timeout para cargar módulos después de mostrar modal
- ✅ Mejorado: Manejo de errores con mensajes claros

### 2. Logs de Debugging
Ahora verás en la consola del navegador:
```
=== CARGAR MÓDULOS ===
ProgramaID: 1
idinscripcion: 5
📡 Enviando petición AJAX...
✅ Respuesta AJAX recibida: [...]
📊 Total de módulos: 3
🎨 Generando HTML de tarjetas...
📈 RESUMEN: Total módulos: 3, Pagados: 1, Pendientes: 2
🖱️ Agregando eventos click a tarjetas pendientes...
✅ Tarjetas cargadas correctamente
```

---

## 🔍 Pasos para Debuggear

### **PASO 1: Ejecutar el Script de Prueba**

Abre en tu navegador:
```
http://localhost/POSGRADOFCS/test_modulos.php
```

Esto te mostrará:
1. ✅ Programas disponibles en la BD
2. ✅ Inscripciones disponibles
3. ✅ Sugerencia de URL de prueba

**Ejemplo de URL sugerida:**
```
http://localhost/POSGRADOFCS/test_modulos.php?programaID=1&idinscripcion=1
```

---

### **PASO 2: Verificar que Hay Módulos**

El script de prueba te dirá:

#### ✅ SI HAY MÓDULOS:
```
✅ Se encontraron 3 módulos con estado

┌─────────┬────────────────────┬──────────┬────────────┐
│ Código  │ Nombre             │ Costo    │ Estado     │
├─────────┼────────────────────┼──────────┼────────────┤
│ MOD-001 │ Metodología...     │ Bs. 500  │ 🟢 PAGADO  │
│ MOD-002 │ Estadística...     │ Bs. 500  │ 🔴 PENDIENTE│
│ MOD-003 │ Epistemología      │ Bs. 450  │ 🔴 PENDIENTE│
└─────────┴────────────────────┴──────────┴────────────┘
```

#### ❌ SI NO HAY MÓDULOS:
```
⚠️ No hay módulos en este programa
El programa con ID 1 no tiene módulos registrados.

Solución: Debes crear módulos para este programa primero.
```

---

### **PASO 3: Revisar la Consola del Navegador**

1. Ir a la página **Matriculados**
2. Presionar **F12** para abrir Developer Tools
3. Ir a la pestaña **"Console"**
4. Hacer clic en **"Acciones" > "Inscribir a Módulo"**

#### **Qué buscar en la consola:**

##### ✅ SI FUNCIONA CORRECTAMENTE:
```javascript
=== CARGAR MÓDULOS ===
ProgramaID: 1
idinscripcion: 5
📡 Enviando petición AJAX...
✅ Respuesta AJAX recibida: [{...}, {...}, {...}]
📊 Total de módulos: 3
🎨 Generando HTML de tarjetas...
HTML length: 2456
📈 RESUMEN: Total módulos: 3, Pagados: 1, Pendientes: 2
✅ Tarjetas cargadas correctamente
```

##### ❌ ERROR: No hay ID de inscripción
```javascript
=== CARGAR MÓDULOS ===
ProgramaID: 1
idinscripcion: undefined
❌ ERROR: No se encontró ID de inscripción
```

**Solución:** El botón no tiene el atributo `data-idinscripcion`. Revisar el controlador.

##### ❌ ERROR: AJAX falla
```javascript
❌ ERROR AJAX: parsererror
Status: error
Response: <!DOCTYPE html>...
```

**Solución:** El archivo AJAX está retornando HTML en lugar de JSON. Revisar errores PHP.

---

### **PASO 4: Revisar la Red (Network)**

1. En Developer Tools (F12), ir a pestaña **"Network"**
2. Filtrar por **"XHR"**
3. Hacer clic en **"Inscribir a Módulo"**
4. Buscar la petición a **"modulo.ajax.php"**

#### Ver la petición:
```
Request URL: ajax/modulo.ajax.php
Request Method: POST
Status Code: 200 OK

Form Data:
  programaID: 1
  idinscripcion: 5
```

#### Ver la respuesta:
```json
[
  {
    "ModuloID": "1",
    "NombreModulo": "Metodología de Investigación",
    "Codigo": "MOD-001",
    "Creditos": "4",
    "Pagado": "1",
    "FechaPago": "2025-01-15",
    ...
  }
]
```

##### ❌ Si ves HTML en lugar de JSON:
Significa que hay un error PHP. Revisar:
- `modelos/pagomodulo.modelo.php`
- `ajax/modulo.ajax.php`

---

## 🛠️ Soluciones a Problemas Comunes

### **Problema 1: No hay módulos en el programa**

**Síntoma:** El script de prueba dice "No hay módulos en este programa"

**Solución:**
1. Ir a la sección de **Módulos** en el sistema
2. Crear módulos para el programa
3. O ejecutar SQL:
```sql
INSERT INTO modulo (ProgramaID, NombreModulo, Codigo, Creditos, Costo, Estado)
VALUES
(1, 'Metodología de Investigación', 'MOD-001', 4, 500.00, 1),
(1, 'Estadística Aplicada', 'MOD-002', 4, 500.00, 1),
(1, 'Epistemología', 'MOD-003', 3, 450.00, 1);
```

---

### **Problema 2: No se encuentra ID de inscripción**

**Síntoma:** Console dice "❌ ERROR: No se encontró ID de inscripción"

**Solución:**
Verificar que el botón tiene el atributo:
```php
data-idinscripcion="' . $estudiante['idInscripcion'] . '"
```

Revisar archivo: `controladores/inscripcionmodulo.controlador.php` línea 55

---

### **Problema 3: Error de SQL**

**Síntoma:** AJAX retorna error 500 o HTML con error

**Solución:**
1. Verificar que la tabla `pagomodulo` existe:
```sql
SHOW TABLES LIKE 'pagomodulo';
```

2. Si no existe, ejecutar:
```
http://localhost/POSGRADOFCS/ejecutar_crear_tabla_pagomodulo.php
```

---

### **Problema 4: Tarjetas aparecen pero no se pueden seleccionar**

**Síntoma:** Las tarjetas rojas se ven pero no pasa nada al hacer clic

**Solución:**
Verificar en consola que aparece:
```
🖱️ Agregando eventos click a tarjetas pendientes...
```

Si no aparece, limpiar caché del navegador (Ctrl + Shift + R)

---

### **Problema 5: CSS no se aplica**

**Síntoma:** Las tarjetas se ven pero sin colores ni estilos

**Solución:**
1. Limpiar caché del navegador (Ctrl + F5)
2. Verificar que el archivo se cargó:
   - Ir a Developer Tools > Sources
   - Buscar `matriculados.php`
   - Verificar que tenga los estilos `.modulo-card`

---

## 📋 Checklist de Verificación

Marca cada ítem conforme lo verifiques:

- [ ] ✅ La tabla `pagomodulo` existe en la BD
- [ ] ✅ El programa tiene módulos registrados
- [ ] ✅ El estudiante está matriculado (tabla `estudianteprograma`)
- [ ] ✅ El botón tiene `data-idinscripcion`
- [ ] ✅ La consola muestra los logs de carga
- [ ] ✅ El AJAX retorna JSON (no HTML)
- [ ] ✅ Las tarjetas aparecen en el HTML (inspeccionar elemento)
- [ ] ✅ Los estilos CSS están aplicados
- [ ] ✅ Los eventos click están agregados

---

## 🎯 Prueba Rápida

Para hacer una prueba rápida:

1. **Ejecutar:**
```
http://localhost/POSGRADOFCS/test_modulos.php
```

2. **Copiar la URL sugerida**

3. **Verificar que aparezcan módulos con estados**

4. **Si todo está OK, probar en el sistema real**

5. **Abrir consola (F12) y verificar logs**

---

## 📞 Si Aún No Funciona

Envía esta información:

1. **Captura de pantalla de:** `test_modulos.php`
2. **Logs de consola** (copiar todo el texto)
3. **Respuesta del Network** (pestaña XHR > modulo.ajax.php)
4. **HTML generado** (inspeccionar elemento `#contenedorModulos`)

---

## ✨ Archivos Modificados en Esta Corrección

1. ✅ `vistas/recursos/assets/js/scripts/inscripcionmodulo.js`
   - Agregados logs de debugging
   - Corregida limpieza de elementos
   - Agregado timeout para carga

2. ✅ `test_modulos.php` (NUEVO)
   - Script de diagnóstico
   - Verifica BD y consultas
   - Muestra JSON de respuesta

3. ✅ `DEBUG_TARJETAS_MODULOS.md` (NUEVO)
   - Esta guía de debugging

---

**Fecha:** 2025
**Versión:** 2.1 - Debug Edition
**Estado:** Listo para pruebas
