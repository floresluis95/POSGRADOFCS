# Nota sobre Modal de Insertar Estudiante

**Fecha:** 18/12/2025

## Problema Original

Se mostraba el siguiente error:

```
Warning: include(vistas/modales/insertar-estudiante.php): Failed to open stream: No such file or directory
```

## Solución Aplicada

1. **Se comentó el include del modal** que no existe:
   ```php
   <?php // include 'vistas/modales/insertar-estudiante.php'; ?>
   ```

2. **Se eliminó el botón "Nuevo Estudiante"** del formulario de orden de pago ya que requería el modal.

3. **Se ajustó el diseño** para que el select de estudiante ocupe todo el ancho (col-lg-12).

4. **Se agregó texto informativo** debajo del select:
   > "Busque al estudiante por su cédula de identidad. Si no existe, debe registrarlo primero en el módulo de Estudiantes."

## Estado Actual

✅ **Sin errores de sintaxis PHP**
✅ **Formulario funcional** sin el botón de nuevo estudiante
✅ **El usuario debe registrar estudiantes** en el módulo de Estudiantes antes de crear órdenes de pago

## Si se Requiere el Modal de Nuevo Estudiante

Si en el futuro se desea agregar la funcionalidad de crear estudiantes directamente desde la orden de pago:

### Opción 1: Copiar el modal del archivo de inscripción

El modal completo está en: `vistas/componentes/inscripcion.php` (líneas 364-543)

1. Copiar todo el código del modal
2. Pegarlo antes de la línea `<!-- Scripts -->` en ordenpago.php
3. Descomentar el botón "Nuevo Estudiante"

### Opción 2: Crear archivo de modal compartido

1. Crear el archivo: `vistas/modales/insertar-estudiante.php`
2. Copiar el modal de inscripción.php
3. Descomentar la línea del include
4. Descomentar el botón

## Recomendación

Por ahora, **mantener la solución actual** es suficiente porque:

1. Es más simple y directo
2. Evita duplicación de código
3. Mantiene la responsabilidad de registro de estudiantes en un solo lugar
4. Reduce la complejidad del formulario de orden de pago

El usuario puede ir primero a **Estudiantes** → Registrar, y luego a **Orden de Pago** → Generar.

---

**Desarrollado el:** 18/12/2025
**Estado:** SOLUCIONADO ✅
