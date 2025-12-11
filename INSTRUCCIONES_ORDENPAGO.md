# INSTRUCCIONES - Sistema de Orden de Pago

## Resumen de Cambios Implementados

Se ha implementado un sistema completo de órdenes de pago con soporte para múltiples módulos. A continuación, los detalles:

### Funcionalidades Implementadas

1. **Visualización Categorizada por Programa**
   - Los módulos pagados se muestran agrupados por programa
   - Cada programa muestra: código, nombre, versión y número de trámite
   - Incluye estadísticas de módulos con/sin orden generada

2. **Selección Múltiple de Módulos**
   - Checkbox en cada módulo para seleccionar
   - Checkbox "Seleccionar todos" por programa
   - Los módulos que ya tienen orden generada aparecen deshabilitados con fondo azul

3. **Registro en Base de Datos**
   - Tabla `ordenpago` creada exitosamente
   - Cada orden genera un número único: `ORD-{EstudianteID}-{ProgramaID}-{timestamp}`
   - Se registran todos los módulos incluidos en la orden

4. **Generación de PDF**
   - Para órdenes múltiples: muestra tabla con todos los módulos y subtotales
   - Para órdenes simples: mantiene el formato anterior
   - Incluye dos copias (original + copia) en la misma página

## Cómo Usar el Sistema

### Paso 1: Buscar Estudiante
1. Ir a la vista "ORDEN DE PAGO"
2. Seleccionar un estudiante del dropdown
3. El sistema cargará todos los programas y módulos pagados

### Paso 2: Seleccionar Módulos
1. Revisar los módulos agrupados por programa
2. Marcar los checkboxes de los módulos deseados
3. Los módulos que ya tienen orden generada NO se pueden seleccionar

### Paso 3: Generar Orden de Pago
1. Hacer clic en "Generar Orden de Pago" del programa deseado
2. Completar los campos obligatorios en el modal:
   - **Versión** (se completa automáticamente)
   - **N° de Trámite** (se completa automáticamente)
   - **Nombre de la Factura** (requerido)
   - **NIT o CI** (requerido)
   - **Responsable** (requerido)
   - Firma (opcional)
3. Hacer clic en "Imprimir Orden de Pago"
4. El sistema:
   - Registra la orden en la base de datos
   - Genera el PDF automáticamente
   - Actualiza la vista mostrando que la orden fue generada

## Depuración de Problemas

Si el PDF **NO se genera**, siga estos pasos:

### 1. Abrir Consola del Navegador
- Presione **F12** en su navegador
- Vaya a la pestaña "Console"
- Busque mensajes de error en rojo

### 2. Verificar Logs
La consola debe mostrar:
```
=== GENERAR PDF ORDEN DE PAGO ===
Validación de campos: {...}
Valor de modal_pago_id: {...}
Es orden múltiple: true/false
Generando orden de pago múltiple/simple...
```

### 3. Verificar Campos Obligatorios
Asegúrese de que todos los campos marcados con * estén completos:
- Nombre de la Factura
- NIT o CI
- Responsable

### 4. Verificar Ventanas Emergentes (Popups)
- El navegador debe permitir ventanas emergentes para abrir el PDF
- Si aparece un mensaje de bloqueo, permita los popups para este sitio

### 5. Verificar Diagnóstico del Sistema
Puede ejecutar el archivo de diagnóstico:
```
http://localhost/POSGRADOFCS/diagnostico_ordenpago.php
```

Este mostrará:
- Estado de la conexión a la base de datos
- Existencia de la tabla ordenpago
- Verificación de archivos
- Estado de TCPDF

## Archivos Modificados

1. **ajax/ordenpago.ajax.php**
   - Función `obtenerModulosPagados`: incluye datos de programa e inscripción
   - Función `registrarOrdenPago`: registra órdenes en la base de datos

2. **vistas/componentes/ordenpago.php**
   - Corregido ID del mensaje inicial

3. **vistas/componentes/generar-orden-pago-pdf.php**
   - Soporte para múltiples módulos
   - Tabla de módulos con subtotales
   - Incluye número de orden

4. **vistas/recursos/assets/js/scripts/ordenpago.js**
   - Función `mostrarTablaPagos`: agrupa módulos por programa
   - Función `agregarEventListenersCheckboxes`: manejo de checkboxes
   - Función `abrirModalOrdenMultiple`: modal para múltiples módulos
   - Función `generarPDFOrdenPago`: detección automática simple/múltiple
   - Función `generarOrdenMultiple`: registro en BD + generación PDF
   - Función `generarOrdenSimple`: orden de un solo módulo
   - Logs de depuración en consola

## Estructura de la Tabla `ordenpago`

```sql
IdOrdenPago         INT AUTO_INCREMENT PRIMARY KEY
EstudianteID        INT NOT NULL
idinscripcion       INT NOT NULL
ProgramaID          INT NOT NULL
ListaPagosModulo    TEXT (IDs separados por comas)
MontoTotal          DECIMAL(10,2)
FechaGeneracion     DATETIME
ResponsableGeneracion VARCHAR(200)
NombreFactura       VARCHAR(200)
NitCiFactura        VARCHAR(50)
NumeroOrden         VARCHAR(100) UNIQUE
```

## Notas Importantes

1. **Número de Trámite = Cuenta Auxiliar**: Son el mismo valor del programa
2. **Una orden por vez**: No se pueden generar múltiples órdenes simultáneas del mismo programa
3. **Los módulos ya facturados** aparecen con fondo azul y NO se pueden volver a seleccionar
4. **El sistema actualiza automáticamente** la vista después de generar cada orden

## Contacto de Soporte

Si el problema persiste después de seguir estos pasos:
1. Capture el mensaje de error de la consola (F12)
2. Tome una captura de pantalla del formulario
3. Verifique que todos los servicios (Apache, MySQL) estén activos

---

**Sistema implementado exitosamente**
Fecha: 2025-12-11
