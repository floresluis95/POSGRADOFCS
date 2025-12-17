# FUNCIONALIDAD: DOCENTES EXTRANJEROS

**Fecha:** 17 de diciembre de 2025
**Versión:** 1.0

---

## 📋 RESUMEN

Se implementó la funcionalidad completa para registrar y gestionar docentes extranjeros con los siguientes campos:
- ✅ **Checkbox "El docente es extranjero"** - Indica si el docente es de otro país
- ✅ **País de Origen** - Listado de países principales
- ✅ **Región/Departamento** - Estado, provincia o departamento del país

---

## 🎯 CARACTERÍSTICAS IMPLEMENTADAS

### 1. Campos Agregados a la Base de Datos

Se agregaron 3 nuevos campos a la tabla `docente`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `EsExtranjero` | TINYINT(1) | 0 = Boliviano (nacional), 1 = Extranjero |
| `Pais` | VARCHAR(100) | País de origen si es extranjero |
| `Region` | VARCHAR(100) | Región/Departamento del país |

### 2. Interfaz de Usuario

#### **Formulario de Registro de Docente:**
- Nuevo checkbox "El docente es extranjero" con diseño destacado
- Campos de País y Región que se muestran/ocultan dinámicamente
- Validación automática según el tipo de docente
- Lista predefinida de países comunes (Argentina, Brasil, Chile, Colombia, etc.)

#### **Formulario de Edición de Docente:**
- Mismos campos que el formulario de registro
- Carga automática de datos existentes
- Muestra/oculta campos según el estado de "Es Extranjero"

### 3. Validaciones Implementadas

**Docente Nacional (Boliviano):**
- Campo "Expedido" es **obligatorio**
- Campos País y Región **no son visibles** ni requeridos

**Docente Extranjero:**
- Campo "Expedido" **no es obligatorio**
- Campos País y Región son **visibles y obligatorios**
- Región se convierte automáticamente a mayúsculas

---

## 🔧 INSTALACIÓN

### Paso 1: Ejecutar Migración de Base de Datos

Accede a la siguiente URL en tu navegador:

```
http://localhost/POSGRADOFCS/ejecutar_migracion_extranjero_docente.php
```

Esta página:
- ✅ Ejecutará el script SQL para agregar los campos
- ✅ Mostrará el progreso de la migración
- ✅ Verificará la estructura de la tabla actualizada
- ✅ Indicará si fue exitosa

**IMPORTANTE:** Este script debe ejecutarse **UNA SOLA VEZ**.

### Paso 2: Verificar Cambios

Una vez ejecutada la migración, accede al módulo de docentes:

```
http://localhost/POSGRADOFCS/docentes
```

---

## 📝 USO DE LA FUNCIONALIDAD

### Registrar un Docente Extranjero

1. **Ir a Docentes**
   - Click en "Nuevo Docente"

2. **Llenar Datos Básicos**
   - Nombre, apellidos, cédula profesional, especialidad, etc.

3. **Marcar como Extranjero**
   - ✅ Marcar el checkbox "El docente es extranjero"
   - Los campos de País y Región aparecerán automáticamente

4. **Seleccionar País y Región**
   - **País:** Seleccionar del listado (Argentina, Brasil, Chile, etc.)
   - **Región:** Ingresar manualmente (ej: "BUENOS AIRES", "CALIFORNIA")

5. **Guardar**
   - Click en "Guardar"
   - El sistema registrará al docente con todos sus datos

### Editar un Docente Extranjero

1. **Abrir Modal de Edición**
   - Click en el botón de editar del docente

2. **Verificar Estado**
   - Si el docente es extranjero, el checkbox estará marcado
   - Los campos País y Región mostrarán los datos guardados

3. **Modificar Datos**
   - Cambiar cualquier campo necesario
   - Cambiar el estado de extranjero si es necesario

4. **Guardar Cambios**
   - Click en "Actualizar"

---

## 💻 DETALLES TÉCNICOS

### Archivos Modificados

```
bd/
  ├── agregar_campos_extranjero_docente.sql     [NUEVO]

controladores/
  ├── docentes.controlador.php                   [MODIFICADO]

modelos/
  ├── docentes.modelo.php                        [MODIFICADO]

vistas/componentes/
  ├── docentes.php                               [MODIFICADO]

ejecutar_migracion_extranjero_docente.php        [NUEVO]
```

### Cambios en Controlador

**Registro (`RegistrarDocenteControlador`):**
```php
"EsExtranjero" => isset($_POST['EsExtranjero']) && $_POST['EsExtranjero'] == '1' ? 1 : 0,
"Pais"         => isset($_POST['Pais']) ? strtoupper(htmlspecialchars(trim($_POST['Pais']))) : null,
"Region"       => isset($_POST['Region']) ? strtoupper(htmlspecialchars(trim($_POST['Region']))) : null,
```

**Edición (`EditarDocenteControlador`):**
```php
"EsExtranjero" => isset($_POST['editEsExtranjero']) && $_POST['editEsExtranjero'] == '1' ? 1 : 0,
"Pais"         => isset($_POST['editPais']) ? strtoupper(htmlspecialchars(trim($_POST['editPais']))) : null,
"Region"       => isset($_POST['editRegion']) ? strtoupper(htmlspecialchars(trim($_POST['editRegion']))) : null
```

### Cambios en Modelo

**Registro (`RegistrarDocenteModelo`):**
```sql
INSERT INTO `docente` (..., `EsExtranjero`, `Pais`, `Region`)
VALUES (..., :EsExtranjero, :Pais, :Region)
```

**Edición (`EditarDocenteModelo`):**
```sql
UPDATE `docente` SET ...,
`EsExtranjero` = :EsExtranjero,
`Pais` = :Pais,
`Region` = :Region
WHERE `Ci` = :Ci
```

### JavaScript Dinámico

```javascript
jQuery('#checkExtranjero').on('change', function() {
    const isChecked = jQuery(this).is(':checked');

    if (isChecked) {
        // Mostrar campos de país y región
        jQuery('#camposExtranjero').slideDown(300);
        jQuery('#selectPais').prop('required', true);
        jQuery('#inputRegion').prop('required', true);
        jQuery('#selectExpedido').prop('required', false);
    } else {
        // Ocultar y limpiar
        jQuery('#camposExtranjero').slideUp(300);
        jQuery('#selectPais').prop('required', false).val('');
        jQuery('#inputRegion').prop('required', false).val('');
        jQuery('#selectExpedido').prop('required', true);
    }
});
```

---

## 🌍 PAÍSES DISPONIBLES

El sistema incluye los siguientes países en el selector:

- Argentina
- Brasil
- Chile
- Colombia
- Ecuador
- España
- Estados Unidos
- México
- Paraguay
- Perú
- Uruguay
- Venezuela
- Otro (para países no listados)

---

## ✅ VALIDACIONES DE SINTAXIS

Todos los archivos fueron verificados sin errores:

```bash
✅ No syntax errors detected in vistas/componentes/docentes.php
✅ No syntax errors detected in controladores/docentes.controlador.php
✅ No syntax errors detected in modelos/docentes.modelo.php
```

---

## 🎨 DISEÑO VISUAL

### Checkbox de Docente Extranjero
- Fondo con gradiente violeta claro
- Borde violeta de 2px
- Icono de pasaporte
- Mensaje informativo debajo

### Campos de País y Región
- Ocultos por defecto
- Aparecen con animación slideDown
- Iconos específicos (bandera y mapa)
- Textos de ayuda descriptivos

---

## 🔍 EJEMPLOS DE USO

### Ejemplo 1: Docente Argentino
```
Checkbox: ✅ El docente es extranjero
País: Argentina
Región: BUENOS AIRES
Expedido: (no requerido)
```

### Ejemplo 2: Docente Estadounidense
```
Checkbox: ✅ El docente es extranjero
País: Estados Unidos
Región: CALIFORNIA
Expedido: (no requerido)
```

### Ejemplo 3: Docente Boliviano
```
Checkbox: ❌ (sin marcar)
País: (oculto)
Región: (oculto)
Expedido: LP (requerido)
```

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema: Los campos no aparecen al marcar el checkbox
**Solución:** Verificar que jQuery esté cargado correctamente en la página.

### Problema: Error al guardar docente extranjero
**Solución:** Ejecutar la migración de la base de datos con el script proporcionado.

### Problema: No se guardan los datos de país y región
**Solución:** Verificar que los campos tengan los atributos `name` correctos:
- `name="Pais"` (registro)
- `name="editPais"` (edición)
- `name="Region"` (registro)
- `name="editRegion"` (edición)

---

## 📊 PRÓXIMAS MEJORAS (OPCIONAL)

1. Agregar más países al listado
2. Implementar selector de región dinámico según el país
3. Agregar bandera del país en la visualización
4. Filtro de búsqueda por país en la tabla
5. Reportes de docentes por nacionalidad
6. Validación de formato de documentos según país

---

## 📞 SOPORTE

Para cualquier consulta o problema con esta funcionalidad, contactar al equipo de desarrollo.

---

**FIN DEL DOCUMENTO**
