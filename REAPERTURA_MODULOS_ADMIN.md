# Funcionalidad de Reapertura de Módulos (Administradores)

## Resumen
Se ha implementado la funcionalidad para que los administradores puedan reabrir módulos cerrados desde la vista `rnotasestudiante`. Esta funcionalidad está restringida exclusivamente a usuarios con tipo 'ADM'.

## Cambios Realizados

### 1. Vista rnotasestudiante.php

#### Líneas 11-12: Obtener tipo de usuario
```php
$tipoUsuario = $_SESSION["Tipo"] ?? '';
$esAdministrador = ($tipoUsuario === 'ADM');
```
**Función:** Determina si el usuario actual es administrador.

#### Líneas 215-220: Pasar variables a JavaScript
```html
<script>
    const esAdministrador = <?php echo $esAdministrador ? 'true' : 'false'; ?>;
    const tipoUsuario = '<?php echo $tipoUsuario; ?>';
    console.log('Usuario tipo:', tipoUsuario, '| Es Admin:', esAdministrador);
</script>
```
**Función:** Hace disponible la variable `esAdministrador` en JavaScript para controlar la visibilidad del botón.

#### Línea 222: Actualización de versión
```html
<script src="vistas/recursos/assets/js/scripts/calificacion.js?v=4.0"></script>
```
**Función:** Fuerza la recarga del archivo JavaScript en el navegador.

### 2. JavaScript calificacion.js

#### Líneas 745-754: Botón de Reabrir (condicional)
```javascript
// Botón de reabrir (solo para administradores)
if (typeof esAdministrador !== 'undefined' && esAdministrador === true) {
    html += `<button class="btn btn-sm btn-warning btn-reabrir-modulo" `;
    html += `data-modulo-id="${asig.Idmodulo}" `;
    html += `data-modulo-nombre="${asig.nombremodulo}" `;
    html += `data-modulo-codigo="${asig.codigomodulo}" `;
    html += `title="Reabrir módulo (solo administradores)">`;
    html += `<i class="la la-unlock"></i> Reabrir`;
    html += `</button>`;
}
```
**Función:** Agrega el botón "Reabrir" solo si el usuario es administrador.

#### Líneas 791-798: Event Listener
```javascript
// Agregar evento a los botones de reabrir (solo para administradores)
$('.btn-reabrir-modulo').on('click', function() {
    const moduloID = $(this).data('modulo-id');
    const moduloNombre = $(this).data('modulo-nombre');
    const moduloCodigo = $(this).data('modulo-codigo');

    reabrirModuloDesdeEstudiante(moduloID, moduloNombre, moduloCodigo);
});
```
**Función:** Vincula el clic del botón con la función de reapertura.

#### Líneas 806-844: Función de Reapertura
```javascript
function reabrirModuloDesdeEstudiante(moduloID, moduloNombre, moduloCodigo) {
    // Verificar permisos
    if (typeof esAdministrador === 'undefined' || !esAdministrador) {
        Swal.fire({
            type: 'error',
            title: 'Acceso denegado',
            text: 'Solo los administradores pueden reabrir módulos cerrados'
        });
        return;
    }

    // Confirmar reapertura con diálogo
    Swal.fire({
        title: '¿Reabrir este módulo?',
        html: `...`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="la la-unlock"></i> Sí, reabrir módulo',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            ejecutarReaperturaModulo(moduloID, moduloNombre);
        }
    });
}
```
**Función:** Valida permisos y solicita confirmación antes de reabrir.

#### Líneas 849-904: Ejecución de Reapertura via AJAX
```javascript
function ejecutarReaperturaModulo(moduloID, moduloNombre) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'reabrirModulo',
            moduloID: moduloID
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Mostrar éxito y recargar asignaciones
                Swal.fire({
                    type: 'success',
                    title: 'Módulo reabierto',
                    html: `...`,
                    timer: 2000
                }).then(function() {
                    // Recargar asignaciones
                    if (docenteSeleccionado && docenteSeleccionado.id) {
                        cargarAsignacionesDocente(docenteSeleccionado.id);
                    }
                });
            }
        }
    });
}
```
**Función:** Envía la petición AJAX para reabrir el módulo y actualiza la vista.

## Flujo de Usuario (Administrador)

### 1. Acceder al Sistema
- Iniciar sesión como administrador (Tipo = 'ADM')
- Ir a "rnotasestudiante"

### 2. Ver Módulos Cerrados
- Seleccionar un docente
- Ver asignaciones activas en tabla principal
- Si hay módulos cerrados, aparecer botón "Ver Módulos Cerrados"
- Click en el botón para abrir modal

### 3. Reabrir Módulo
- En el modal, cada módulo cerrado muestra dos botones:
  - **Imprimir** (verde) - Todos los usuarios
  - **Reabrir** (amarillo) - Solo administradores
- Click en botón "Reabrir"
- Confirmar la acción en el diálogo de advertencia
- Esperar procesamiento

### 4. Resultado
- Mensaje de éxito
- El módulo desaparece del modal de cerrados
- El módulo aparece nuevamente en la tabla principal con estado "ACTIVO"
- Los docentes pueden volver a editar calificaciones

## Seguridad y Permisos

### Control de Acceso Frontend
```javascript
if (typeof esAdministrador !== 'undefined' && esAdministrador === true) {
    // Mostrar botón Reabrir
}
```
- El botón solo se renderiza si `esAdministrador === true`
- Usuarios no administradores nunca ven el botón

### Control de Acceso Backend
En `controladores/calificacion.controlador.php`:
```php
public function ReabrirModuloControlador() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Solo administradores pueden reabrir
    if (!isset($_SESSION['Tipo']) || $_SESSION['Tipo'] !== 'ADM') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Solo los administradores pueden reabrir módulos'
        ]);
        return;
    }
    // ... resto del código
}
```
- Validación adicional en el servidor
- Doble capa de seguridad

## Tipos de Usuario

### Administrador (ADM)
- ✅ Ve botón "Reabrir" en modal
- ✅ Puede reabrir cualquier módulo cerrado
- ✅ Puede imprimir reportes de módulos cerrados

### Docente (DOC)
- ❌ NO ve botón "Reabrir"
- ✅ Puede imprimir reportes de módulos cerrados
- ℹ️ Solo puede ver sus propios módulos

### Estudiante (EST)
- ❌ NO ve botón "Reabrir"
- ✅ Puede imprimir reportes de módulos cerrados
- ℹ️ Solo puede ver módulos donde está inscrito

## Interfaz de Usuario

### Botón "Reabrir" (Solo Administradores)
```html
<button class="btn btn-sm btn-warning">
    <i class="la la-unlock"></i> Reabrir
</button>
```
- **Color:** Amarillo/Warning (indica precaución)
- **Icono:** Candado abierto (la-unlock)
- **Tamaño:** Pequeño (btn-sm)
- **Posición:** Al lado del botón "Imprimir"

### Diálogo de Confirmación
- **Título:** "¿Reabrir este módulo?"
- **Contenido:** Nombre y código del módulo
- **Advertencia:** "Al reabrir el módulo, los docentes podrán volver a editar las calificaciones"
- **Tipo:** Warning (amarillo)
- **Botones:**
  - Confirmar: "Sí, reabrir módulo" (amarillo)
  - Cancelar: "Cancelar" (gris)

### Mensaje de Éxito
- **Título:** "Módulo reabierto"
- **Mensaje:** Confirmación con nombre del módulo
- **Tipo:** Success (verde)
- **Duración:** 2 segundos (auto-cierre)
- **Acción:** Recarga automática de asignaciones

## Pruebas

### Archivo de Prueba
`test_reapertura_admin.php`

### Contenido del Test
1. ✅ Verifica tipo de usuario (ADM)
2. ✅ Lista módulos cerrados de la BD
3. ✅ Permite probar reapertura directamente
4. ✅ Muestra instrucciones completas
5. ✅ Incluye enlaces rápidos

### Cómo Ejecutar el Test
```
http://localhost/POSGRADOFCS/test_reapertura_admin.php
```

### Pasos de Prueba Manual
1. Crear un módulo cerrado en "notasdocente"
2. Acceder como administrador a "rnotasestudiante"
3. Verificar que el módulo NO aparece en tabla principal
4. Click en "Ver Módulos Cerrados"
5. Verificar que aparecen 2 botones (Imprimir y Reabrir)
6. Click en "Reabrir"
7. Confirmar acción
8. Verificar que el módulo vuelve a la tabla principal

## Registro de Cambios

### Base de Datos
- ✅ Campo `estadomodulo` en tabla `modulos`
- ✅ Campo `ValidadoPor` en tabla `modulos`
- ✅ Campo `FechaValidacion` en tabla `modulos`

### Modelo
Ya implementado en versión anterior:
- ✅ `ReabrirModuloModelo($moduloID, $usuarioID)` en `modelos/calificacion.modelo.php`

### Controlador
Ya implementado en versión anterior:
- ✅ `ReabrirModuloControlador()` en `controladores/calificacion.controlador.php`

### AJAX
Ya implementado en versión anterior:
- ✅ Case 'reabrirModulo' en `ajax/calificacion.ajax.php`

### Vista
- ✅ Variables PHP a JavaScript en `vistas/componentes/rnotasestudiante.php`
- ✅ Versión actualizada a 4.0

### JavaScript
- ✅ Botón condicional en modal
- ✅ Event listener para reapertura
- ✅ Función `reabrirModuloDesdeEstudiante()`
- ✅ Función `ejecutarReaperturaModulo()`

## Comparación: Docente vs Estudiante

### Vista "notasdocente" (Docentes y Admins)
- Botón "Validar y Cerrar" en asignaciones activas
- Botón "Reabrir" solo para administradores
- Formulario completo de edición de calificaciones
- Control de permisos al intentar editar módulos cerrados

### Vista "rnotasestudiante" (Todos los usuarios)
- Modal con módulos cerrados
- Botón "Imprimir" para todos
- Botón "Reabrir" solo para administradores
- No permite editar, solo consultar e imprimir

## Estados del Módulo

### ACTIVO
- Aparece en tabla principal
- Editable por docentes
- Color: Verde
- Icono: Abierto

### VALIDADO / CERRADO
- NO aparece en tabla principal
- Solo aparece en modal de cerrados
- No editable (excepto si admin reabre)
- Color: Rojo
- Icono: Candado cerrado
- Botón "Reabrir" solo para administradores

## Auditoría

### Al Cerrar un Módulo
- Se registra `ValidadoPor` (Usuario que cerró)
- Se registra `FechaValidacion` (Fecha y hora de cierre)
- Se cambia `estadomodulo` a 'VALIDADO'

### Al Reabrir un Módulo
- Se limpia `ValidadoPor` (NULL)
- Se limpia `FechaValidacion` (NULL)
- Se cambia `estadomodulo` a 'ACTIVO'
- Acción registrada en sesión y logs

## Casos de Uso

### Caso 1: Error en Calificaciones
**Escenario:** Un docente cerró un módulo pero hay un error en las notas.
**Solución:**
1. Docente solicita al administrador reabrir el módulo
2. Administrador accede a rnotasestudiante
3. Encuentra el módulo cerrado en el modal
4. Click en "Reabrir"
5. Docente puede corregir las calificaciones
6. Docente vuelve a cerrar el módulo

### Caso 2: Estudiante Faltante
**Escenario:** Se cerró un módulo pero falta registrar a un estudiante.
**Solución:**
1. Administrador reabre el módulo
2. Se inscribe al estudiante faltante
3. Docente registra la calificación
4. Se vuelve a cerrar el módulo

### Caso 3: Auditoría de Calificaciones
**Escenario:** Necesitan verificar quién y cuándo se cerraron los módulos.
**Solución:**
1. Administrador accede al modal de módulos cerrados
2. Visualiza fecha y usuario que cerró cada módulo
3. Puede reabrir si es necesario
4. Puede imprimir reportes

## Mejores Prácticas

### Para Administradores
1. Solo reabrir módulos cuando sea estrictamente necesario
2. Comunicar al docente antes de reabrir
3. Verificar que las correcciones sean válidas
4. Volver a cerrar el módulo una vez corregido

### Para Docentes
1. Verificar bien las calificaciones antes de cerrar
2. Informar al administrador si necesitan reapertura
3. No solicitar reaperturas innecesarias
4. Cerrar nuevamente después de corregir

### Para el Sistema
1. Mantener registro de todas las aperturas/cierres
2. Notificar por email cuando se reabra un módulo
3. Generar reportes de módulos reabiertos
4. Implementar límite de reaperturas por módulo

---

**Versión:** 4.0
**Fecha de Implementación:** 2025-12-12
**Estado:** ✅ Completado y Probado
**Autor:** Sistema de Gestión de Posgrado FCS
