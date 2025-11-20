# 📚 Sistema de Inscripción a Módulos

## ✅ Archivos Creados

### Modelos:
1. **`modelos/inscripcionmodulo.modelo.php`**
   - ListarEstudiantesMatriculadosModelo()
   - ObtenerModulosPorProgramaModelo()
   - RegistrarInscripcionModuloModelo()
   - ObtenerModulosInscritosEstudianteModelo()

### Controladores:
2. **`controladores/inscripcionmodulo.controlador.php`**
   - ListarEstudiantesMatriculadosControlador()
   - RegistrarInscripcionModuloControlador()

### Vistas:
3. **`vistas/componentes/matriculados.php`**
   - Tabla de estudiantes matriculados
   - Modal para inscribir a módulos
   - Formulario de inscripción

### JavaScript:
4. **`vistas/recursos/assets/js/scripts/inscripcionmodulo.js`**
   - Manejo del modal
   - Carga dinámica de módulos (AJAX)
   - Validaciones del formulario

### AJAX:
5. **`ajax/modulo.ajax.php`**
   - Obtener módulos por programa

### Scripts SQL:
6. **`crear_tablas_modulos.sql`**
   - Script SQL para crear tablas manualmente

7. **`ejecutar_crear_tablas_modulos.php`**
   - Script PHP para crear tablas automáticamente

---

## 🚀 PASOS PARA USAR EL SISTEMA

### Paso 1: Crear las Tablas en la Base de Datos

Abre tu navegador y ejecuta:

```
http://localhost/POSGRADOFCS/ejecutar_crear_tablas_modulos.php
```

Este script creará automáticamente:
- Tabla `modulo` (si no existe)
- Tabla `estudiantemodulo` (si no existe)
- Índices necesarios

### Paso 2: Ejecutar el Fix de la Columna 'foto' (si no lo hiciste antes)

```
http://localhost/POSGRADOFCS/ejecutar_fix_foto.php
```

Este script permite que la columna `foto` de la tabla `estudianteprograma` acepte valores NULL.

### Paso 3: Agregar Módulos al Sistema

Necesitas tener módulos registrados en tu base de datos. Puedes:

**Opción A:** Insertar módulos manualmente en phpMyAdmin

```sql
INSERT INTO `modulo` (`ProgramaID`, `NombreModulo`, `Codigo`, `Descripcion`, `Creditos`, `HorasTeoricas`, `HorasPracticas`, `Costo`) VALUES
(1, 'Metodología de la Investigación', 'MOD-001', 'Fundamentos de investigación científica', 4, 40, 20, 500.00),
(1, 'Estadística Aplicada', 'MOD-002', 'Análisis estadístico de datos', 4, 40, 20, 500.00),
(2, 'Gestión de Proyectos', 'MOD-003', 'Administración y dirección de proyectos', 5, 50, 25, 600.00);
```

**IMPORTANTE:** Reemplaza `ProgramaID` con los IDs reales de tus programas.

**Opción B:** Crear una interfaz de administración de módulos (futura tarea)

### Paso 4: Acceder a la Vista de Estudiantes Matriculados

```
http://localhost/POSGRADOFCS/index.php?ruta=matriculados
```

O agregar un enlace en el menú del sistema:

```html
<a href="index.php?ruta=matriculados">
    <i class="fa fa-users"></i> Estudiantes Matriculados
</a>
```

---

## 📋 FUNCIONALIDADES DEL SISTEMA

### Vista de Estudiantes Matriculados
- ✅ Lista todos los estudiantes matriculados en programas
- ✅ Muestra detalles de matrícula (costo, voucher, fecha)
- ✅ Botón "Inscribir a Módulo" por cada estudiante
- ✅ DataTable con búsqueda, paginación y ordenamiento

### Modal de Inscripción a Módulo
- ✅ Carga módulos según el programa del estudiante (AJAX)
- ✅ Muestra detalles del módulo seleccionado:
  - Código
  - Créditos
  - Horas teóricas y prácticas
  - Costo
- ✅ Auto-completa el costo del módulo
- ✅ Campos:
  - Módulo (select dinámico)
  - Costo del módulo (auto-completado)
  - Número de voucher
  - Fecha de inscripción
- ✅ Validaciones completas (JavaScript + PHP)
- ✅ Previene inscripciones duplicadas

---

## 🗄️ ESTRUCTURA DE LAS TABLAS

### Tabla: `modulo`
```
ModuloID (PK, AUTO_INCREMENT)
ProgramaID (FK → programa)
NombreModulo
Codigo
Descripcion
Creditos
HorasTeoricas
HorasPracticas
Costo
Estado (1 = activo, 0 = inactivo)
FechaCreacion
```

### Tabla: `estudiantemodulo`
```
idEstudianteModulo (PK, AUTO_INCREMENT)
EstudianteID (FK → estudiante)
ModuloID (FK → modulo)
costomodulo
nvauchermodulo
FechaInscripcion
Estado ('ACTIVO', 'INACTIVO', 'RETIRADO')
FechaRegistro
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Error: "No se pudieron cargar los módulos"
- Verifica que existan módulos en la tabla `modulo`
- Verifica que el `ProgramaID` del módulo coincida con el programa del estudiante
- Revisa la consola del navegador (F12) para ver errores de AJAX

### Error: "El estudiante ya está inscrito en este módulo"
- Es normal, el sistema previene inscripciones duplicadas
- Verifica en la tabla `estudiantemodulo` si ya existe el registro

### Error: "No hay estudiantes matriculados"
- Primero debes matricular estudiantes en programas
- Ve a: `index.php?ruta=inscripcion` para registrar matrículas

### Error: Foreign key constraint fails
- Verifica que las tablas `estudiante`, `programa` y `modulo` existan
- Verifica que los IDs de estudiante y módulo sean válidos

---

## 📊 PRÓXIMOS PASOS SUGERIDOS

1. **Crear interfaz de gestión de módulos**
   - Agregar, editar, eliminar módulos
   - Asignar módulos a programas

2. **Reporte de módulos inscritos por estudiante**
   - Ver historial de módulos de un estudiante

3. **Dashboard de inscripciones**
   - Estadísticas de inscripciones a módulos
   - Módulos más populares

4. **Sistema de calificaciones**
   - Registrar notas por módulo

---

## 📞 SOPORTE

Si tienes algún problema:
1. Verifica los logs de error de Apache: `C:\xampp\apache\logs\error.log`
2. Revisa la consola del navegador (F12)
3. Verifica que todas las tablas estén creadas correctamente

---

**Fecha de creación:** $(date)
**Versión:** 1.0
