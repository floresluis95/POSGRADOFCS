<?php
/**
 * Generador de PDF de Calificaciones con TCPDF
 * Genera el PDF y lo guarda automáticamente
 */

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, las
// fechas impresas en el PDF no corresponden a la hora de Bolivia.
date_default_timezone_set('America/La_Paz');

// display_errors está activo en este servidor: cualquier warning/notice suelto
// (ej. del mkdir de más abajo, o de TCPDF) se imprimía ANTES de los bytes del
// PDF y lo corrompía para el navegador (aunque el archivo guardado en disco sí
// quedaba bien, porque ese se escribe aparte). Con ob_start() se atrapa
// cualquier salida accidental y se descarta justo antes de enviar el PDF real,
// igual que ya hacen generar-lista-asistencia-pdf.php y pdfestudiante.php.
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    ob_end_clean();
    die(json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida'
    ]));
}

// Obtener parámetros
$moduloID = isset($_GET['moduloID']) ? intval($_GET['moduloID']) : 0;
$programaID = isset($_GET['programaID']) ? intval($_GET['programaID']) : 0;
$moduloNombre = isset($_GET['moduloNombre']) ? $_GET['moduloNombre'] : '';
$moduloCodigo = isset($_GET['moduloCodigo']) ? $_GET['moduloCodigo'] : '';
$programaNombre = isset($_GET['programaNombre']) ? $_GET['programaNombre'] : '';
$gradoAcademico = isset($_GET['gradoAcademico']) ? $_GET['gradoAcademico'] : '';
$docenteNombre = isset($_GET['docenteNombre']) ? $_GET['docenteNombre'] : '';
// Sigla del PROGRAMA (no del módulo): antes esta columna de la tabla se
// llenaba con "siglaModulo", un parámetro que ningún llamador enviaba nunca,
// así que la columna "SIGLA" siempre salía vacía.
$sigla = isset($_GET['sigla']) ? $_GET['sigla'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
// Fecha de "Oruro, [fecha]" junto a las firmas: es la fecha en que se firma
// la planilla, editable por separado de la fecha de inicio/fin del módulo.
// Si no se envía (compatibilidad), se usa la fecha de hoy.
$fechaGeneracion = !empty($_GET['fechaGeneracion']) ? $_GET['fechaGeneracion'] : date('Y-m-d');

// Formatear fechas
$fechaInicioFormateada = '';
$fechaFinFormateada = '';

if ($fechaInicio) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaInicio);
    if ($fecha) {
        $fechaInicioFormateada = $fecha->format('d/m/Y');
    }
}

if ($fechaFin) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if ($fecha) {
        $fechaFinFormateada = $fecha->format('d/m/Y');
    }
}

// Incluir TCPDF
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
// modelos/conexion.modelo.php no existe en este servidor (lo eliminó el
// antivirus del equipo; ver el workaround documentado en el .htaccess de la
// raíz), así que hay que caer a la copia de respaldo en core/. Sin este
// fallback, este archivo fallaba con un error fatal en TODAS las peticiones
// (por eso nunca se veía el PDF).
require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php')
    ? __DIR__ . '/../../modelos/conexion.modelo.php'
    : __DIR__ . '/../../core/conexion.modelo.php';
require_once __DIR__ . '/../../modelos/calificacion.modelo.php';

// Obtener estudiantes y calificaciones
$estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

// Función para convertir números a letras
function numeroALetras($numero) {
    $entero = intval($numero);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    if ($entero == 0) {
        $literal = 'CERO';
    } elseif ($entero == 100) {
        $literal = 'CIEN';
    } elseif ($entero > 100) {
        $literal = 'CIENTO ';
        $resto = $entero - 100;

        if ($resto >= 10 && $resto < 20) {
            $literal .= $especiales[$resto - 10];
        } elseif ($resto >= 20 && $resto < 30) {
            $literal .= $veintenas[$resto - 20];
        } elseif ($resto >= 30) {
            $dec = floor($resto / 10);
            $uni = $resto % 10;
            $literal .= $decenas[$dec];
            if ($uni > 0) {
                $literal .= ' Y ' . $unidades[$uni];
            }
        } else {
            $literal .= $unidades[$resto];
        }
    } elseif ($entero >= 10 && $entero < 20) {
        $literal = $especiales[$entero - 10];
    } elseif ($entero >= 20 && $entero < 30) {
        $literal = $veintenas[$entero - 20];
    } else {
        $dec = floor($entero / 10);
        $uni = $entero % 10;
        $literal = $decenas[$dec];
        if ($uni > 0) {
            if ($dec > 0) {
                $literal .= ' Y ';
            }
            $literal .= $unidades[$uni];
        }
    }

    return trim($literal);
}

/**
 * Formatea una fecha "Y-m-d" como "D de MES del AAAA" (para la línea de
 * lugar/fecha de firma de la planilla).
 */
function fechaLarga($fechaYmd) {
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($fechaYmd);
    if (!$ts) {
        return '__________';
    }
    return date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' del ' . date('Y', $ts);
}

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('UMSS - Facultad de Ciencias de la Salud');
$pdf->SetTitle('Reporte de Calificaciones - ' . $moduloCodigo);
$pdf->SetSubject('Planilla de Calificaciones');

// Configuración de la página
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 9);

// Quitar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Agregar página
$pdf->AddPage();

// ENCABEZADO (formato oficial FORMATO_CALIFICACIONES.docx)
$logoUTO = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
$logoFCS = __DIR__ . '/../../extensiones/imagenespdf/logofcs.png';

// Logo izquierdo y derecho: se fuerza la misma caja de 22x22mm para los dos
// (con $fitbox='CM' cada logo se escala manteniendo su proporción y se centra
// dentro de la caja) porque logouto.png y logofcs.png tienen proporciones muy
// distintas (1047x1006 vs 239x305) y, con solo el ancho fijo, salían de alturas
// visiblemente diferentes.
if (file_exists($logoUTO)) {
    $pdf->Image($logoUTO, 15, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
}

if (file_exists($logoFCS)) {
    $pdf->Image($logoFCS, 173, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
}

// Título centrado
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetY(12);
$pdf->Cell(0, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');
$pdf->Cell(0, 5, 'COORDINACIÓN POSGRADO ÁREA - ODONTOLOGÍA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
// Dirección y teléfono según el formato oficial FORMATO_CALIFICACIONES.docx
// proporcionado por la Coordinación de Posgrado - Odontología.
$pdf->Cell(0, 4, 'Presidente Montes N°6390 y Aldana, edificio Posgrado UTO 2do piso', 0, 1, 'C');
$pdf->Cell(0, 4, 'Teléfono (591)2- 52 75798 - Fax:(591) 2- 52 55443', 0, 1, 'C');
$pdf->Cell(0, 4, 'Oruro – Bolivia', 0, 1, 'C');

$pdf->Ln(5);

// INFORMACIÓN DEL MÓDULO
$pdf->SetFont('helvetica', '', 9);

// Tabla de información
$tbl = '
<table border="1" cellpadding="4">
    <tr>
        <td width="25%" style="font-weight:bold; background-color:#f0f0f0;">PROGRAMA:</td>
        <td width="75%">' . htmlspecialchars($programaNombre) . ($sigla ? ' (' . htmlspecialchars($sigla) . ')' : '') . '</td>
    </tr>
    <tr>
        <td style="font-weight:bold; background-color:#f0f0f0;">MÓDULO:</td>
        <td><strong>' . htmlspecialchars($moduloCodigo) . ' - ' . htmlspecialchars($moduloNombre) . '</strong></td>
    </tr>
    <tr>
        <td style="font-weight:bold; background-color:#f0f0f0;">DOCENTE:</td>
        <td>' . htmlspecialchars($docenteNombre) . '</td>
    </tr>
    <tr>
        <td style="font-weight:bold; background-color:#f0f0f0;">FECHA:</td>
        <td>
            <strong>Inicio:</strong> ' . ($fechaInicioFormateada ?: '__________') . ' &nbsp;&nbsp;&nbsp;
            <strong>Finalización:</strong> ' . ($fechaFinFormateada ?: '__________') . '
        </td>
    </tr>
</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');

$pdf->Ln(5);

// TÍTULO DE LA PLANILLA
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'PLANILLA DE CALIFICACIONES', 0, 1, 'C');
$pdf->Ln(2);

// TABLA DE CALIFICACIONES (encabezado agrupado "POSGRADUANTES" / "CALIFICACION",
// igual al formato oficial FORMATO_CALIFICACIONES.docx)
$pdf->SetFont('helvetica', '', 8);

// Definir anchos de columnas (total 185mm = ancho útil de la página)
$w0 = 10;  // N°
$w1 = 75;  // NOMBRE COMPLETO
$w2 = 20;  // SIGLA
$w3 = 20;  // MÓDULO
$w4 = 20;  // NUM.
$w5 = 40;  // LITERAL

// Fila 1 del encabezado: grupos "POSGRADUANTES" / "CALIFICACION"
$pdf->SetFillColor(30, 60, 114); // Azul institucional
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($w0 + $w1 + $w2 + $w3, 6, 'POSGRADUANTES', 1, 0, 'C', true);
$pdf->Cell($w4 + $w5, 6, 'CALIFICACION', 1, 1, 'C', true);

// Fila 2 del encabezado: columnas individuales
$pdf->Cell($w0, 7, 'N°', 1, 0, 'C', true);
$pdf->Cell($w1, 7, 'NOMBRE COMPLETO', 1, 0, 'C', true);
$pdf->Cell($w2, 7, 'SIGLA', 1, 0, 'C', true);
$pdf->Cell($w3, 7, 'MÓDULO', 1, 0, 'C', true);
$pdf->Cell($w4, 7, 'NUM.', 1, 0, 'C', true);
$pdf->Cell($w5, 7, 'LITERAL', 1, 1, 'C', true);

// Restaurar colores para el contenido
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

// Verificar si hay estudiantes
if (empty($estudiantes) || count($estudiantes) === 0) {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell($w0 + $w1 + $w2 + $w3 + $w4 + $w5, 10, 'No hay estudiantes inscritos en este módulo', 1, 1, 'C');
} else {
    // Iterar sobre los estudiantes
    foreach ($estudiantes as $index => $estudiante) {
        // Obtener datos del estudiante
        $nombre = isset($estudiante['Nombre']) ? $estudiante['Nombre'] : '';
        $apaterno = isset($estudiante['Apaterno']) ? $estudiante['Apaterno'] : '';
        $amaterno = isset($estudiante['Amaterno']) ? $estudiante['Amaterno'] : '';
        $nombreCompleto = trim($apaterno . ' ' . $amaterno . ' ' . $nombre);

        $nota = isset($estudiante['Nota']) ? $estudiante['Nota'] : null;

        if ($nota === null || $nota === '') {
            $notaMostrar = '-';
            $notaLiteral = '-';
        } else {
            $notaInt = intval($nota);
            $notaMostrar = $notaInt;
            $notaLiteral = numeroALetras($notaInt);
        }

        // Alternar color de fondo
        if ($index % 2 == 0) {
            $pdf->SetFillColor(255, 255, 255); // Blanco
        } else {
            $pdf->SetFillColor(248, 249, 250); // Gris claro
        }

        // Dibujar fila
        $pdf->Cell($w0, 6, $index + 1, 1, 0, 'C', true);
        $pdf->Cell($w1, 6, $nombreCompleto, 1, 0, 'L', true);
        $pdf->Cell($w2, 6, $sigla, 1, 0, 'C', true);
        $pdf->Cell($w3, 6, $moduloCodigo, 1, 0, 'C', true);

        // Nota numérica en negrita
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell($w4, 6, $notaMostrar, 1, 0, 'C', true);
        $pdf->SetFont('helvetica', '', 8);

        $pdf->Cell($w5, 6, $notaLiteral, 1, 1, 'L', true);
    }
}

// PIE DE PÁGINA: escala de calificación + lugar/fecha + firmas, fijo al
// fondo de la hoja. Antes la escala se imprimía justo después de la tabla y
// las firmas por separado con Ln(15), así que en módulos con pocos
// estudiantes quedaban pegadas debajo de la tabla en vez de al pie de la
// página. Se reserva una altura fija y, si ya no entra en la página actual
// (tabla muy larga), se pasa a una nueva antes de fijarla. Se desactiva el
// salto de página automático mientras se dibuja el pie porque, con
// AutoPageBreak activo, cada Cell() individual vuelve a chequear el margen
// de 15mm y puede partir el bloque del pie a la mitad al acercarse al fondo
// real de la hoja (bug detectado: firmas en la página 1 y el resto del pie
// solo en la página 2).
$alturaPie = 55;
if ($pdf->GetY() > $pdf->getPageHeight() - $pdf->getBreakMargin() - $alturaPie) {
    $pdf->AddPage();
}
$pdf->SetAutoPageBreak(false);
$pdf->SetY(-$alturaPie);

// ESCALA DE CALIFICACIÓN
$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell(45, 5, 'ESCALA DE CALIFICACION', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(70, 5, '0  -  75    REPROBADO', 1, 0, 'C');
$pdf->Cell(70, 5, '76  -  100    APROBADO', 1, 1, 'C');

$pdf->Ln(8);
$pdf->SetFont('helvetica', '', 9);
// Lugar y fecha de firma (editable, independiente de inicio/fin del módulo)
$pdf->Cell(0, 5, 'Oruro, ' . fechaLarga($fechaGeneracion), 0, 1, 'C');

$pdf->Ln(12);

$pdf->Cell(90, 1, '', 'T', 0, 'C');
$pdf->Cell(10, 1, '', 0, 0);
$pdf->Cell(90, 1, '', 'T', 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(90, 5, 'DOCENTE', 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, 'COORDINADOR POSGRADO ODONTOLOGIA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(90, 4, htmlspecialchars($docenteNombre), 0, 0, 'C');
$pdf->Cell(10, 4, '', 0, 0);
$pdf->Cell(90, 4, '', 0, 1, 'C');
$pdf->SetAutoPageBreak(true, 15);

// Crear directorio si no existe
$dirPDFs = __DIR__ . '/../../pdfs_generados';
if (!file_exists($dirPDFs)) {
    @mkdir($dirPDFs, 0777, true);
}

// Nombre del archivo
$nombreArchivo = 'calificaciones_' . $moduloCodigo . '_' . date('Ymd_His') . '.pdf';
$rutaCompleta = $dirPDFs . '/' . $nombreArchivo;

// Obtener el PDF como string una sola vez ('S'): de ahí se guarda una copia en
// disco (para el historial en pdfs_generados) y se envía la misma copia al
// navegador, en vez de llamar Output() dos veces sobre el mismo objeto (guardar
// y mostrar por separado), que en TCPDF puede dejar la segunda salida corrupta.
$pdfContenido = $pdf->Output('', 'S');

if (is_dir($dirPDFs) && is_writable($dirPDFs)) {
    @file_put_contents($rutaCompleta, $pdfContenido);
}

// Si se requiere JSON response (para AJAX), no se envía el PDF inline
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'PDF generado correctamente',
        'archivo' => $nombreArchivo,
        'ruta' => 'pdfs_generados/' . $nombreArchivo,
        'totalEstudiantes' => count($estudiantes)
    ]);
    exit;
}

// Descartar cualquier salida accidental (warnings/notices) acumulada antes de
// enviar el PDF real, y mostrarlo inline en el navegador.
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
header('Content-Length: ' . strlen($pdfContenido));
header('Cache-Control: private, max-age=0, must-revalidate');
echo $pdfContenido;
exit;
?>
