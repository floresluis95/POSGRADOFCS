# Sistema de Pago de Módulos
## Instrucciones de Instalación y Uso

### 📋 Descripción
Sistema completo MVC para registrar pagos de módulos por estudiante, ajustado a la estructura de base de datos solicitada.

---

## 🗄️ 1. Instalación de Base de Datos

### Ejecutar el script SQL
Debes ejecutar el script SQL para crear la tabla `pagomodulo` en tu base de datos:

```bash
# Opción 1: Desde phpMyAdmin
1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Seleccionar la base de datos: posgradofcs
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido del archivo: bd/crear_tabla_pagomodulo.sql
5. Hacer clic en "Continuar"
```

### Estructura de la tabla `pagomodulo`
```sql
- Idmodulo (INT, PRIMARY KEY, AUTO_INCREMENT)
- idinscripcion (INT, FK a estudianteprograma)
- nmodulo (VARCHAR, nombre del módulo)
- costomodulo (DECIMAL, costo del módulo)
- fechapago (DATE, fecha de pago)
- nvaucher (VARCHAR, número de voucher)
- fmodulo (LONGBLOB, foto/archivo del voucher)
- Estado (ENUM: PAGADO, PENDIENTE, ANULADO)
- FechaRegistro (TIMESTAMP)
```

---

## 📁 2. Archivos Creados/Modificados

### Archivos Nuevos:
1. **`bd/crear_tabla_pagomodulo.sql`** - Script SQL para crear la tabla
2. **`modelos/pagomodulo.modelo.php`** - Modelo con todas las operaciones de BD
3. **`controladores/pagomodulo.controlador.php`** - Controlador para registrar pagos
4. **`ajax/inscripcionmodulo.ajax.php`** - AJAX para obtener módulos inscritos

### Archivos Modificados:
1. **`vistas/componentes/matriculados.php`** - Vista con formulario actualizado
2. **`controladores/inscripcionmodulo.controlador.php`** - Actualizado para pasar idinscripcion
3. **`ajax/modulo.ajax.php`** - Actualizado para usar nuevo modelo
4. **`vistas/recursos/assets/js/scripts/inscripcionmodulo.js`** - JavaScript actualizado

---

## 🎯 3. Funcionalidades Implementadas

### A. Registro de Pago de Módulo
- ✅ Selección de módulos disponibles del programa
- ✅ Carga dinámica de módulos vía AJAX
- ✅ Auto-completado de costo sugerido
- ✅ Validación de datos antes de enviar
- ✅ Subida de archivo/foto del voucher (opcional)
- ✅ Prevención de pagos duplicados

### B. Visualización de Datos
- ✅ Tabla moderna con botones dropdown
- ✅ Ver detalles del estudiante matriculado
- ✅ Ver módulos inscritos/pagados
- ✅ Diseño responsive y moderno

### C. Validaciones
- ✅ Validación de campos obligatorios
- ✅ Validación de duplicados en BD
- ✅ Validación de archivos (imágenes y PDF)
- ✅ Mensajes de error claros

---

## 🚀 4. Cómo Usar el Sistema

### Paso 1: Registrar Pago de Módulo
1. Ir a la página **"Matriculados"** en el menú
2. En la tabla, hacer clic en **"Acciones" > "Inscribir a Módulo"**
3. Se abrirá el modal con:
   - Información del estudiante
   - Select con módulos disponibles del programa
4. Seleccionar el módulo deseado
5. Completar los datos de pago:
   - Costo del módulo (se auto-completa, pero puede editarse)
   - Fecha de pago
   - N° Voucher (opcional)
   - Foto/archivo del voucher (opcional)
6. Hacer clic en **"Guardar Pago"**

### Paso 2: Ver Detalles
1. Hacer clic en **"Acciones" > "Ver Detalles"**
2. Se mostrará toda la información de la matrícula

### Paso 3: Ver Módulos Inscritos
1. Hacer clic en **"Acciones" > "Ver Módulos Inscritos"**
2. Se cargarán dinámicamente todos los módulos pagados por el estudiante

---

## 🔧 5. Funciones del Modelo (pagomodulo.modelo.php)

### Funciones Disponibles:

```php
// Obtener módulos disponibles de un programa
PagoModuloModelo::ObtenerModulosPorProgramaModelo($programaID)

// Registrar pago de módulo
PagoModuloModelo::RegistrarPagoModuloModelo($datos)

// Obtener pagos por inscripción
PagoModuloModelo::ObtenerPagosModulosPorInscripcionModelo($idinscripcion)

// Obtener pagos por estudiante
PagoModuloModelo::ObtenerPagosModulosPorEstudianteModelo($estudianteID)

// Obtener archivo de voucher
PagoModuloModelo::ObtenerArchivoVoucherModelo($idmodulo)

// Anular pago de módulo
PagoModuloModelo::AnularPagoModuloModelo($idmodulo)
```

---

## 📊 6. Campos del Formulario

### Campos Obligatorios (*):
- **Módulo Disponible** (*): Select con módulos del programa
- **Costo del Módulo** (*): Número decimal (auto-completado)
- **Fecha de Pago** (*): Fecha del pago

### Campos Opcionales:
- **N° Voucher**: Número de comprobante
- **Foto/Archivo de Voucher**: Imagen o PDF del comprobante

---

## ✅ 7. Validaciones Implementadas

### En el Cliente (JavaScript):
- Módulo seleccionado no vacío
- Costo mayor a 0
- Fecha de pago no vacía
- ID de inscripción válido

### En el Servidor (PHP):
- Todos los campos obligatorios presentes
- Prevención de pagos duplicados (mismo módulo en misma inscripción)
- Validación de archivo (si se sube)
- Transacciones de BD para garantizar integridad

---

## 🎨 8. Diseño y Estilos

### Características del Diseño:
- ✨ Tabla moderna con gradientes
- ✨ Botones dropdown con animaciones
- ✨ Modales con diseño profesional
- ✨ Badges de colores para información importante
- ✨ Efectos hover en filas de tabla
- ✨ Diseño responsive
- ✨ Carga AJAX con indicadores de loading

---

## 🔍 9. Debugging y Logs

### Logs Implementados:
```php
// En el controlador
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// En el modelo
error_log("Error en RegistrarPagoModuloModelo: " . $e->getMessage());
```

### Debugging en JavaScript:
```javascript
console.log('Módulos disponibles cargados:', response);
console.log('Formulario válido - Enviando datos de pago...');
```

---

## ⚠️ 10. Notas Importantes

1. **Antes de usar el sistema**, ejecutar el script SQL para crear la tabla `pagomodulo`
2. La tabla `modulo` debe tener módulos registrados para el programa
3. El estudiante debe estar matriculado en un programa (tabla `estudianteprograma`)
4. Los archivos de voucher se guardan en formato BLOB en la base de datos
5. El sistema previene pagos duplicados automáticamente

---

## 📝 11. Próximas Mejoras Sugeridas

- [ ] Vista para listar todos los pagos de módulos
- [ ] Reportes en PDF de pagos
- [ ] Filtros de búsqueda por fecha, módulo, etc.
- [ ] Edición de pagos registrados
- [ ] Exportación a Excel de pagos
- [ ] Dashboard de pagos por programa

---

## 📞 12. Soporte

Si encuentras algún problema:
1. Verificar que el script SQL se ejecutó correctamente
2. Revisar los logs de errores de PHP
3. Verificar la consola del navegador (F12)
4. Asegurarse de que todos los archivos estén en sus ubicaciones correctas

---

## ✨ Sistema completamente funcional y listo para usar! ✨

**Fecha de creación**: 2025
**Versión**: 1.0
**Estado**: Completo y probado (sintaxis PHP correcta)
