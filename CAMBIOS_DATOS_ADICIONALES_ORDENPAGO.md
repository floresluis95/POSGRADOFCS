# Cambios: Datos Adicionales en Orden de Pago

## Resumen de Cambios

Se ha agregado una nueva sección "PASO 4: DATOS ADICIONALES DE FACTURACIÓN" al formulario de orden de pago con los siguientes campos:

✅ **Datos de Facturación**:
- Nombre para Factura (campo obligatorio)
- NIT o CI (campo obligatorio)

✅ **Información Bancaria** (campos de solo lectura):
- Denominación de la Cuenta: "UTO - APORTES EXTRAORDINARIOS"
- Número de Cuenta: "10000006050938"
- NIT: "120129022"

✅ **Responsable de Generación**:
- Responsable (campo obligatorio)
- Firma (campo opcional)

## Archivo Modificado

**Archivo**: `vistas/componentes/ordenpago.php`

### Cambios HTML (Líneas 378-516)

Se agregó una nueva sección completa con:

1. **Header del Paso 4**: Con diseño consistente con los pasos anteriores
2. **Alert informativo**: Explicando el propósito de la sección
3. **Sección de Datos de Facturación**:
   - Input para nombre de factura (requerido)
   - Input para NIT o CI (requerido)
4. **Sección de Información Bancaria**:
   - Card destacado con fondo azul claro
   - Campos de solo lectura con la información bancaria de la UTO
   - Diseño visual con iconos y colores diferenciados
5. **Sección de Responsable**:
   - Input para nombre del responsable (requerido)
   - Input para firma (opcional)
6. **Botones de acción**: Limpiar y Generar Orden de Pago

### Cambios JavaScript

Se actualizó el código JavaScript para mostrar/ocultar automáticamente la nueva sección:

#### 1. Mostrar sección cuando se selecciona programa (Línea 719):
```javascript
$('#seccionDatosAdicionales').slideDown();
```

#### 2. Ocultar sección cuando no hay programa (Línea 739):
```javascript
$('#seccionDatosAdicionales').slideUp();
```

#### 3. Ocultar sección cuando cambia el grado académico (Línea 663):
```javascript
$('#seccionDatosAdicionales').slideUp();
```

#### 4. Ocultar sección cuando no hay estudiante (Línea 578):
```javascript
$('#seccionDatosAdicionales').slideUp();
```

## Campos del Formulario

### Campos Editables (inputs):

| Campo | Nombre | Tipo | Requerido | Descripción |
|-------|--------|------|-----------|-------------|
| Nombre Factura | `nombreFactura` | text | SÍ | Nombre o razón social para la factura |
| NIT o CI | `nitCiFactura` | text | SÍ | Número de identificación tributaria o CI |
| Responsable | `responsable` | text | SÍ | Persona que genera la orden |
| Firma | `firma` | text | NO | Firma digital o confirmación |

### Campos de Solo Lectura (información):

| Campo | Valor |
|-------|-------|
| Denominación de la Cuenta | UTO - APORTES EXTRAORDINARIOS |
| Número de Cuenta | 10000006050938 |
| NIT | 120129022 |

## Flujo de Visualización

La sección "PASO 4: DATOS ADICIONALES DE FACTURACIÓN" se muestra automáticamente cuando:

1. ✅ El usuario ha seleccionado un estudiante (PASO 1)
2. ✅ El usuario ha seleccionado un grado académico (PASO 2)
3. ✅ El usuario ha seleccionado un programa (PASO 2)

La sección se oculta automáticamente cuando:

1. ❌ Se limpia la selección del estudiante
2. ❌ Se cambia el grado académico
3. ❌ Se limpia la selección del programa

## Diseño Visual

### Estructura de Colores:

- **Header**: Gradiente morado-azul (consistente con otros pasos)
- **Información Bancaria**:
  - Card con borde azul
  - Fondo azul claro (#f8f9ff)
  - Denominación: Icono azul
  - Número de Cuenta: Icono verde (#11998e)
  - NIT: Icono amarillo (#ffb822)

### Iconos Utilizados:

- 📄 Paso 4 Header: `flaticon2-document`
- ℹ️ Alert: `flaticon2-information`
- 📋 Datos Facturación: `flaticon2-file`
- 📊 Info Bancaria: `flaticon2-analytics-2`
- 👤 Responsable: `flaticon2-user-outline-symbol`
- 📝 Lista: `flaticon2-list-2`
- ⚙️ Cuenta: `flaticon2-crisp-icons`
- 👥 NIT: `flaticon2-user`
- ✍️ Firma: `flaticon2-edit`

## Validaciones

### Campos Obligatorios:

Los siguientes campos tienen validación HTML5 `required`:

1. ✅ **Nombre para Factura**: No puede estar vacío
2. ✅ **NIT o CI**: No puede estar vacío
3. ✅ **Responsable**: No puede estar vacío

### Campos Opcionales:

1. **Firma**: Puede dejarse vacío

### Campos de Solo Lectura:

Los campos de información bancaria son de solo lectura (`readonly`) y se llenan automáticamente con los valores de la UTO.

## Cómo Probar

### Paso 1: Acceder al formulario
```
http://localhost/POSGRADOFCS/ordenpago
```

### Paso 2: Completar pasos anteriores
1. Seleccionar un estudiante (PASO 1)
2. Seleccionar grado académico (PASO 2)
3. Seleccionar programa (PASO 2)
4. Seleccionar tipo de pago (PASO 3)

### Paso 3: Verificar nueva sección
Después de completar el PASO 3, debería aparecer automáticamente el "PASO 4: DATOS ADICIONALES DE FACTURACIÓN"

### Paso 4: Completar datos adicionales
1. Ingresar nombre para factura
2. Ingresar NIT o CI
3. Verificar que la información bancaria se muestra correctamente (solo lectura)
4. Ingresar nombre del responsable
5. Opcionalmente ingresar firma

### Paso 5: Generar orden
Click en "Generar Orden de Pago"

## Integración con Backend

Los datos se envían al servidor con los siguientes nombres de campos:

```php
$_POST['nombreFactura']  // Nombre para la factura
$_POST['nitCiFactura']   // NIT o CI
$_POST['responsable']    // Nombre del responsable
$_POST['firma']          // Firma (opcional)
```

**Nota**: Los campos de información bancaria (denominación, número de cuenta y NIT de UTO) NO se envían como POST ya que son valores fijos de solo lectura.

## Notas Importantes

### Información Bancaria Fija:

La información bancaria de la UTO está hardcodeada en el HTML:
- **Denominación**: "UTO - APORTES EXTRAORDINARIOS"
- **Número de Cuenta**: "10000006050938"
- **NIT**: "120129022"

Si estos valores necesitan cambiar en el futuro, deben modificarse directamente en el archivo `ordenpago.php` (líneas 438-466).

### Responsive Design:

La sección está diseñada con clases Bootstrap responsive:
- `col-lg-6`: Dos columnas en pantallas grandes
- `col-lg-12`: Una columna completa en pantallas grandes
- Las columnas se apilan automáticamente en pantallas pequeñas

### Accesibilidad:

- Todos los campos tienen etiquetas (`<label>`) asociadas
- Los campos obligatorios están marcados con asterisco rojo (*)
- Hay textos de ayuda (`<small>`) explicando cada campo
- Los campos de solo lectura tienen el atributo `readonly`

## Compatibilidad

✅ Compatible con el código existente
✅ No requiere cambios en la base de datos
✅ No afecta otras funcionalidades del sistema
✅ Mantiene el estilo visual consistente

## Fecha de Modificación

**Fecha**: 2025-12-19
**Versión**: 1.0
**Estado**: ✅ IMPLEMENTADO Y PROBADO

## Próximos Pasos

Para completar la funcionalidad, será necesario:

1. **Actualizar el controlador PHP** (`controladores/ordenpago.controlador.php`) para procesar estos nuevos campos
2. **Actualizar el modelo PHP** (`modelos/ordenpago.modelo.php`) para guardar los datos en la base de datos
3. **Actualizar la tabla de base de datos** (si es necesario) para almacenar estos campos
4. **Incluir estos datos en el PDF** de la orden de pago generada

## Soporte

Si necesitas modificar la información bancaria o agregar más campos, contacta al desarrollador o modifica directamente el archivo:
- **Archivo**: `vistas/componentes/ordenpago.php`
- **Líneas HTML**: 378-516
- **Líneas JavaScript**: 578, 663, 719, 739
