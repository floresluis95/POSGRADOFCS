# 📚 Sistema de Gestión de Módulos por Programa

## Descripción General
Sistema completo MVC para registrar módulos de cada programa educativo. El número de módulos a registrar se obtiene automáticamente del campo `Modulos` de la tabla `programa`.

---

## 🗄️ Estructura de Base de Datos

### Tabla `modulos` (Tu estructura existente)
```sql
CREATE TABLE modulos (
    Idmodulo INT(11) PRIMARY KEY AUTO_INCREMENT,
    idinscripcion INT(11) NOT NULL,
    nombremodulo VARCHAR(100) NOT NULL,
    codigomodulo INT(11) NOT NULL,
    estadomodulo VARCHAR(15) DEFAULT 'ACTIVO',
    FOREIGN KEY (idinscripcion) REFERENCES estudianteprograma(idInscripcion)
);
```

### Tabla `programa` (Campo importante)
El campo **`Modulos`** contiene el número de módulos que tiene cada programa:
```sql
-- Ejemplo:
ProgramaID | NombrePrograma              | Modulos
-----------|-----------------------------|--------
1          | Maestría en Educación       | 6
2          | Doctorado en Ciencias       | 8
3          | Especialidad en Pedagogía   | 4
```

---

## 📁 Archivos del Sistema

### 1. **Modelo** - `modelos/modulo.modelo.php`
**Funciones:**
- ✅ `ObtenerModulosPorInscripcionModelo($idinscripcion)` - Obtiene módulos de una inscripción
- ✅ `RegistrarModulosModelo($datos)` - Registra múltiples módulos
- ✅ `ActualizarEstadoModuloModelo($idmodulo, $estado)` - Cambia estado
- ✅ `EliminarModuloModelo($idmodulo)` - Elimina un módulo
- ✅ `ObtenerNumeroModulosProgramaModelo($programaID)` - Lee el campo `Modulos` de programa
- ✅ `ListarTodosModulosModelo()` - Lista todos con JOIN a estudiante y programa

### 2. **Controlador** - `controladores/modulo.controlador.php`
**Métodos:**
- ✅ `RegistrarModulosControlador()` - Procesa el formulario POST
- ✅ `ListarModulosControlador()` - Genera HTML de la tabla

### 3. **Vista** - `vistas/componentes/modulos.php`
**Componentes:**
- ✅ Select de estudiantes matriculados
- ✅ Generación dinámica de tarjetas según campo `Modulos`
- ✅ Formulario con validaciones JavaScript
- ✅ Tabla de módulos registrados con DataTables
- ✅ Integración con SweetAlert

---

## 🚀 Funcionamiento del Sistema

### Flujo Completo:

```
1. Usuario selecciona un estudiante matriculado
   ↓
2. Sistema consulta: SELECT Modulos FROM programa WHERE ...
   ↓
3. Obtiene el número (ej: 6 módulos)
   ↓
4. Genera 6 tarjetas automáticamente
   ↓
5. Usuario completa:
   - Módulo 1: Código 1, Nombre "Metodología de Investigación"
   - Módulo 2: Código 2, Nombre "Estadística Aplicada"
   - ... y así sucesivamente
   ↓
6. Click en "Registrar Módulos"
   ↓
7. Se guardan en tabla `modulos` con el idinscripcion
   ↓
8. Aparecen en la tabla de módulos registrados
```

---

## 📋 Uso Paso a Paso

### Paso 1: Configurar el Campo `Modulos` en Programa

Si aún no existe:
```sql
ALTER TABLE programa ADD COLUMN Modulos INT DEFAULT 0;
```

Actualizar programas existentes:
```sql
UPDATE programa SET Modulos = 6 WHERE ProgramaID = 1;
UPDATE programa SET Modulos = 8 WHERE ProgramaID = 2;
UPDATE programa SET Modulos = 4 WHERE ProgramaID = 3;
```

### Paso 2: Agregar la Ruta en el Sistema

En tu archivo principal de rutas (ej: `index.php`):
```php
case "modulos":
    include "vistas/componentes/modulos.php";
    break;
```

### Paso 3: Agregar al Menú de Navegación

Agregar enlace en el menú:
```html
<a href="modulos">
    <i class="fa fa-book"></i> Gestión de Módulos
</a>
```

### Paso 4: Usar el Sistema

1. **Ir a la página "Módulos"**
2. **Seleccionar un estudiante** del dropdown
   - Muestra: Nombre (CI) - Programa
3. **Se genera el formulario automáticamente**
   - Número de tarjetas = valor del campo `Modulos`
4. **Completar cada módulo:**
   - Código: Se auto-numera (1, 2, 3, ...)
   - Nombre: Campo libre (ej: "Metodología de Investigación")
5. **Click en "Registrar Módulos"**
6. **Confirmar** en el SweetAlert
7. **¡Listo!** Los módulos se guardan

---

## 💡 Ejemplo Práctico

### Escenario:
**Programa:** Maestría en Educación
**Campo `Modulos`:** 6
**Estudiante:** Juan Pérez

### El sistema genera:

```
┌─────────────────────────┐  ┌─────────────────────────┐
│ 📖 MÓDULO 1            │  │ 📖 MÓDULO 2            │
│                         │  │                         │
│ Código: [1]            │  │ Código: [2]            │
│ Nombre: [_________]    │  │ Nombre: [_________]    │
└─────────────────────────┘  └─────────────────────────┘

┌─────────────────────────┐  ┌─────────────────────────┐
│ 📖 MÓDULO 3            │  │ 📖 MÓDULO 4            │
│                         │  │                         │
│ Código: [3]            │  │ Código: [4]            │
│ Nombre: [_________]    │  │ Nombre: [_________]    │
└─────────────────────────┘  └─────────────────────────┘

┌─────────────────────────┐  ┌─────────────────────────┐
│ 📖 MÓDULO 5            │  │ 📖 MÓDULO 6            │
│                         │  │                         │
│ Código: [5]            │  │ Código: [6]            │
│ Nombre: [_________]    │  │ Nombre: [_________]    │
└─────────────────────────┘  └─────────────────────────┘
```

### Usuario completa:

| Módulo | Código | Nombre |
|--------|--------|--------|
| 1 | 1 | Metodología de Investigación |
| 2 | 2 | Estadística Aplicada |
| 3 | 3 | Epistemología |
| 4 | 4 | Pedagogía Contemporánea |
| 5 | 5 | Diseño Curricular |
| 6 | 6 | Evaluación Educativa |

### Resultado en BD:

```sql
INSERT INTO modulos (idinscripcion, nombremodulo, codigomodulo, estadomodulo) VALUES
(5, 'Metodología de Investigación', 1, 'ACTIVO'),
(5, 'Estadística Aplicada', 2, 'ACTIVO'),
(5, 'Epistemología', 3, 'ACTIVO'),
(5, 'Pedagogía Contemporánea', 4, 'ACTIVO'),
(5, 'Diseño Curricular', 5, 'ACTIVO'),
(5, 'Evaluación Educativa', 6, 'ACTIVO');
```

---

## ✅ Validaciones Implementadas

### En JavaScript:
- ✅ Verifica que haya al menos 1 módulo completado
- ✅ Valida que nombre no esté vacío
- ✅ Valida que código sea mayor a 0
- ✅ Confirmación antes de enviar
- ✅ Loading mientras procesa

### En PHP:
- ✅ Previene duplicados (mismo idinscripcion)
- ✅ Valida datos obligatorios
- ✅ Transacciones de BD (todo o nada)
- ✅ Sanitización de datos
- ✅ Manejo de errores con try-catch

---

## 🎨 Características de Diseño

### Interfaz:
- ✨ Header con gradiente morado
- ✨ Tarjetas visuales para cada módulo
- ✨ Efectos hover en tarjetas
- ✨ Animaciones suaves (fadeIn)
- ✨ Alerts informativos con colores
- ✨ DataTables para búsqueda y ordenamiento
- ✨ Diseño responsive (mobile-friendly)

### UX:
- 📱 Responsive desde 320px
- ⚡ Carga dinámica de campos
- 🎯 Auto-numeración de códigos
- 💾 Confirmación antes de guardar
- ✅ Mensajes claros de éxito/error
- 🔄 Recarga automática después de guardar

---

## 🔧 Personalización

### Cambiar el número máximo de caracteres del nombre:
```javascript
// En modulos.php, línea del input
maxlength="100"  // Cambiar a lo que necesites
```

```php
// En modulo.modelo.php, ALTER TABLE
nombremodulo VARCHAR(100)  // Ajustar según necesidad
```

### Agregar más validaciones:
```javascript
// En modulos.php, función de validación
if (nombre.length < 3) {
    // Nombre muy corto
}
```

### Cambiar estados disponibles:
```php
// En modulo.modelo.php
estadomodulo VARCHAR(15) DEFAULT 'ACTIVO'
// Posibles valores: ACTIVO, INACTIVO, COMPLETADO, etc.
```

---

## 📊 Consultas Útiles

### Ver módulos de un estudiante:
```sql
SELECT m.*, e.Nombre, e.Apaterno, p.NombrePrograma
FROM modulos m
INNER JOIN estudianteprograma ep ON m.idinscripcion = ep.idInscripcion
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
WHERE e.EstudianteID = 1;
```

### Contar módulos por programa:
```sql
SELECT p.NombrePrograma, p.Modulos, COUNT(m.Idmodulo) as Registrados
FROM programa p
LEFT JOIN estudianteprograma ep ON p.ProgramaID = ep.ProgramaID
LEFT JOIN modulos m ON ep.idInscripcion = m.idinscripcion
GROUP BY p.ProgramaID;
```

### Ver estudiantes sin módulos registrados:
```sql
SELECT e.Nombre, e.Apaterno, p.NombrePrograma
FROM estudianteprograma ep
INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
LEFT JOIN modulos m ON ep.idInscripcion = m.idinscripcion
WHERE m.Idmodulo IS NULL
AND ep.Estado = 'ACTIVO';
```

---

## 🐛 Solución de Problemas

### Problema 1: No aparecen campos de módulos

**Causa:** El programa tiene `Modulos = 0` o NULL

**Solución:**
```sql
UPDATE programa SET Modulos = 6 WHERE ProgramaID = X;
```

---

### Problema 2: Error "Ya existen módulos registrados"

**Causa:** Ya se registraron módulos para esa inscripción

**Solución:** El sistema previene duplicados. Si necesitas re-registrar:
```sql
DELETE FROM modulos WHERE idinscripcion = X;
-- Luego volver a registrar desde el formulario
```

---

### Problema 3: No se guarda ningún módulo

**Causa:** Todos los campos están vacíos

**Solución:** Completar al menos un módulo con nombre y código

---

## 📝 Notas Importantes

1. ⚠️ **Campo `Modulos` obligatorio** - Cada programa debe tener definido cuántos módulos tiene
2. ⚠️ **No duplicados** - No se pueden registrar módulos 2 veces para la misma inscripción
3. ⚠️ **Transacciones** - Si falla 1 módulo, no se guarda ninguno (todo o nada)
4. ⚠️ **Estado por defecto** - Todos los módulos se crean como 'ACTIVO'

---

## ✨ Archivos Verificados

```bash
✅ modelos/modulo.modelo.php - Sin errores de sintaxis
✅ controladores/modulo.controlador.php - Sin errores de sintaxis
✅ vistas/componentes/modulos.php - Sin errores de sintaxis
```

---

## 🎯 Resumen

| Aspecto | Detalle |
|---------|---------|
| **Campo clave** | `programa.Modulos` (número de módulos) |
| **Tabla destino** | `modulos` |
| **Relación** | `modulos.idinscripcion` → `estudianteprograma.idInscripcion` |
| **Generación** | Dinámica según `Modulos` |
| **Validación** | JavaScript + PHP |
| **UX** | Tarjetas visuales + SweetAlert |

---

## 🚀 ¡Listo para Usar!

El sistema está completamente funcional y ajustado al campo **`Modulos`** de tu tabla `programa`.

**Próximo paso:**
1. Asegúrate que cada programa tenga el campo `Modulos` con un valor > 0
2. Accede a la página "Módulos"
3. ¡Empieza a registrar módulos!

---

**Fecha de creación:** 2025
**Versión:** 1.0
**Estado:** ✅ Completado y funcional
