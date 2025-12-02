# 📄 GUÍA DE PERSONALIZACIÓN DEL REPORTE PDF DE CALIFICACIONES

## 📍 Archivo a Editar
**Ubicación:** `vistas/componentes/reporte-calificaciones-pdf.php`

---

## 🎨 PASO 1: CAMBIAR EL ENCABEZADO (Logo y Nombre de Institución)

### Ubicación en el código:
Busca las líneas 109-115 (sección `<!-- ENCABEZADO -->`):

```html
<div class="header">
    <h1>Universidad / Institución</h1>
    <h2>Facultad de Ciencias Sociales</h2>
    <p>Reporte de Calificaciones</p>
    <p style="font-size: 9pt; margin-top: 5px;">Generado el: <?php echo $fechaImpresion; ?></p>
</div>
```

### Cómo personalizarlo:

#### OPCIÓN A: Solo cambiar el texto
```html
<div class="header">
    <h1>UNIVERSIDAD AUTÓNOMA GABRIEL RENÉ MORENO</h1>
    <h2>Facultad de Humanidades</h2>
    <p>Reporte de Calificaciones Finales</p>
    <p style="font-size: 9pt; margin-top: 5px;">Generado el: <?php echo $fechaImpresion; ?></p>
</div>
```

#### OPCIÓN B: Agregar logo de la institución
```html
<div class="header">
    <!-- Logo de la institución -->
    <img src="../recursos/assets/img/logo-institucion.png"
         alt="Logo"
         style="width: 100px; height: auto; margin-bottom: 10px;">

    <h1>UNIVERSIDAD AUTÓNOMA GABRIEL RENÉ MORENO</h1>
    <h2>Facultad de Humanidades</h2>
    <p>Reporte de Calificaciones Finales</p>
    <p style="font-size: 9pt; margin-top: 5px;">Generado el: <?php echo $fechaImpresion; ?></p>
</div>
```

**Nota:** Asegúrate de tener el logo en la carpeta especificada.

---

## 🎨 PASO 2: CAMBIAR COLORES DEL DISEÑO

### Ubicación: Estilos CSS (líneas 30-60)

Los colores principales están definidos en variables. Busca estas secciones:

#### Color del encabezado y bordes principales:
```css
.header {
    border-bottom: 3px solid #667eea;  /* ← Cambia este color */
}

.header h1 {
    color: #667eea;  /* ← Cambia este color */
}
```

#### Colores sugeridos:
- **Azul oscuro:** `#1e3a8a`
- **Verde:** `#047857`
- **Rojo:** `#dc2626`
- **Morado:** `#7c3aed`
- **Naranja:** `#ea580c`

#### Ejemplo de cambio a verde:
```css
.header {
    border-bottom: 3px solid #047857;
}

.header h1 {
    color: #047857;
}

.header h2 {
    color: #065f46;
}
```

#### Cambiar colores de la tabla:
```css
.tabla-calificaciones thead {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);  /* Verde */
    color: white;
}
```

---

## 🎨 PASO 3: MODIFICAR LOS BADGES DE ESTADO

### Ubicación: líneas 155-175

```css
.badge-aprobado {
    background-color: #1dc9b7;  /* Verde aprobado */
    color: white;
}

.badge-reprobado {
    background-color: #fd397a;  /* Rojo reprobado */
    color: white;
}

.badge-pendiente {
    background-color: #6c757d;  /* Gris pendiente */
    color: white;
}
```

### Personalización sugerida:
```css
.badge-aprobado {
    background-color: #10b981;  /* Verde más vibrante */
    color: white;
    border: 2px solid #059669;
}

.badge-reprobado {
    background-color: #ef4444;  /* Rojo más intenso */
    color: white;
    border: 2px solid #dc2626;
}

.badge-pendiente {
    background-color: #f59e0b;  /* Amarillo/naranja */
    color: white;
    border: 2px solid #d97706;
}
```

---

## 📊 PASO 4: CAMBIAR LA NOTA MÍNIMA DE APROBACIÓN

### Ubicación: línea 252

Por defecto, la nota mínima es **51**. Búscala aquí:

```php
if ($notaFloat >= 51) {  // ← Cambia este número
    $estadoClase = 'badge-aprobado';
    $estadoTexto = 'Aprobado';
    $aprobados++;
}
```

### Ejemplo: Cambiar a nota mínima 60:
```php
if ($notaFloat >= 60) {
    $estadoClase = 'badge-aprobado';
    $estadoTexto = 'Aprobado';
    $aprobados++;
}
```

---

## 📝 PASO 5: PERSONALIZAR LAS FIRMAS DEL PIE DE PÁGINA

### Ubicación: líneas 310-325

```html
<div class="firmas">
    <div class="firma">
        <div class="linea"></div>
        <p>Firma del Docente</p>
        <p style="font-weight: normal; font-size: 9pt;"><?php echo htmlspecialchars($docenteNombre); ?></p>
    </div>
    <div class="firma">
        <div class="linea"></div>
        <p>Sello y Firma de Autorización</p>
        <p style="font-weight: normal; font-size: 9pt;">Dirección Académica</p>
    </div>
</div>
```

### Personalización ejemplo:
```html
<div class="firmas">
    <div class="firma">
        <div class="linea"></div>
        <p>DOCENTE TITULAR</p>
        <p style="font-weight: normal; font-size: 9pt;"><?php echo htmlspecialchars($docenteNombre); ?></p>
    </div>
    <div class="firma">
        <div class="linea"></div>
        <p>V°B° DIRECTOR</p>
        <p style="font-weight: normal; font-size: 9pt;">Lic. Juan Pérez García</p>
        <p style="font-weight: normal; font-size: 8pt;">Director Académico</p>
    </div>
</div>
```

---

## 📏 PASO 6: AJUSTAR EL TAMAÑO DE LA FUENTE

### Para toda la página:
Busca línea 27:
```css
body {
    font-size: 11pt;  /* ← Cambia este tamaño */
}
```

### Para la tabla específicamente:
Busca línea 125:
```css
.tabla-calificaciones tbody td {
    font-size: 10pt;  /* ← Cambia aquí */
}
```

---

## 🖼️ PASO 7: AGREGAR MARCA DE AGUA (OPCIONAL)

Agrega este código después de la línea 25 (dentro del `<style>`):

```css
body::before {
    content: "CONFIDENCIAL";
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 120pt;
    color: rgba(0, 0, 0, 0.05);
    z-index: -1;
    font-weight: bold;
    pointer-events: none;
}
```

---

## 📊 PASO 8: AGREGAR O QUITAR COLUMNAS DE LA TABLA

### Ubicación: líneas 143-150 (encabezado) y 228-260 (cuerpo)

#### Para AGREGAR una columna (ejemplo: Email):

**1. En el encabezado (`<thead>`):**
```html
<thead>
    <tr>
        <th class="col-numero">#</th>
        <th class="col-ci">C.I.</th>
        <th class="col-estudiante">ESTUDIANTE</th>
        <th class="col-email">EMAIL</th>  <!-- ← NUEVA COLUMNA -->
        <th class="col-nota">NOTA</th>
        <th class="col-estado">ESTADO</th>
        <th class="col-fecha">FECHA REGISTRO</th>
    </tr>
</thead>
```

**2. En el cuerpo (`<tbody>`):**
```php
<tr>
    <td class="col-numero"><?php echo $index + 1; ?></td>
    <td class="col-ci"><?php echo $ci; ?></td>
    <td class="col-estudiante"><?php echo $nombreCompleto; ?></td>
    <td class="col-email"><?php echo htmlspecialchars($est['Correo']); ?></td>  <!-- ← NUEVA COLUMNA -->
    <td class="col-nota"><?php echo $notaMostrar; ?></td>
    <td class="col-estado">
        <span class="badge <?php echo $estadoClase; ?>"><?php echo $estadoTexto; ?></span>
    </td>
    <td class="col-fecha"><?php echo $fechaRegistro; ?></td>
</tr>
```

**3. Agregar estilo para la nueva columna:**
```css
.col-email { width: 20%; font-size: 9pt; }
```

#### Para QUITAR una columna:
Simplemente elimina las etiquetas `<th>` del encabezado y `<td>` del cuerpo.

---

## 🎯 PASO 9: PERSONALIZAR EL FORMATO DE FECHA

### Ubicación: línea 11

```php
$fechaImpresion = date('d/m/Y H:i:s');  // ← Formato actual
```

### Formatos alternativos:
```php
// Formato largo en español
$fechaImpresion = date('l, d \d\e F \d\e Y - H:i:s');

// Solo fecha sin hora
$fechaImpresion = date('d/m/Y');

// Formato internacional
$fechaImpresion = date('Y-m-d H:i:s');

// Formato personalizado
$fechaImpresion = date('d/m/Y') . ' a las ' . date('H:i');
```

---

## 📐 PASO 10: CAMBIAR TAMAÑO DE PÁGINA Y MÁRGENES

### Ubicación: líneas 203-209

```css
@page {
    size: letter;        /* ← Cambia a: A4, legal, letter */
    margin: 1.5cm;       /* ← Ajusta los márgenes */
}
```

### Opciones de tamaño:
- `letter` - Carta (21.59 × 27.94 cm)
- `A4` - A4 (21 × 29.7 cm)
- `legal` - Legal (21.59 × 35.56 cm)

### Márgenes personalizados:
```css
@page {
    size: A4;
    margin-top: 2cm;
    margin-bottom: 2cm;
    margin-left: 1.5cm;
    margin-right: 1.5cm;
}
```

---

## 🖨️ CÓMO USAR EL REPORTE

1. **Inicia sesión** en el sistema
2. Ve a **Registro de Calificaciones**
3. **Selecciona un docente**
4. En la tabla de módulos, haz clic en el botón **🖨️** (verde)
5. Se abrirá una **nueva ventana** con el reporte
6. Haz clic en **"Imprimir / Guardar PDF"** o presiona **Ctrl+P**
7. Selecciona:
   - **Impresora física** para imprimir
   - **"Guardar como PDF"** para guardar el archivo

---

## ✅ CONSEJOS FINALES

1. **Haz pruebas antes de imprimir**: Siempre revisa la vista previa
2. **Guarda una copia del original**: Antes de modificar, haz una copia del archivo
3. **Prueba en diferentes navegadores**: Chrome, Firefox, Edge
4. **Verifica la impresión**: Algunos estilos pueden verse diferentes al imprimir
5. **Usa colores compatibles**: No todos los colores se ven bien al imprimir

---

## 🆘 PROBLEMAS COMUNES

### El PDF no se genera:
- Verifica que hayas iniciado sesión
- Revisa que el módulo tenga estudiantes inscritos

### Los colores no se ven al imprimir:
- En la ventana de impresión, activa "Gráficos de fondo"

### El diseño se ve diferente:
- Algunos estilos CSS no funcionan en PDF
- Usa estilos simples para mejor compatibilidad

---

**¡Listo!** Con esta guía puedes personalizar completamente tu reporte de calificaciones. 🎉
