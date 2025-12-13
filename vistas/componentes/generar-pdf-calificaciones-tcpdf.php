<?php
/**
 * Generador de PDF de Calificaciones con TCPDF
 * Genera el PDF y lo guarda automáticamente
 */

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
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
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';

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
require_once __DIR__ . '/../../modelos/conexion.modelo.php';
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

// ENCABEZADO
$logoUTO = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
$logoFCS = __DIR__ . '/../../extensiones/imagenespdf/logofcs.png';

// Logo izquierdo
if (file_exists($logoUTO)) {
    $pdf->Image($logoUTO, 15, 10, 25, 0, 'PNG');
}

// Logo derecho
if (file_exists($logoFCS)) {
    $pdf->Image($logoFCS, 170, 10, 25, 0, 'PNG');
}

// Título centrado
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetY(12);
$pdf->Cell(0, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');
$pdf->Cell(0, 5, 'COORDINACIÓN POSGRADO ÁREA - ODONTOLOGÍA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 4, 'AV. Del Moreno Edificio San Agustín II (Ex Almacenes COMIBOL)', 0, 1, 'C');
$pdf->Cell(0, 4, 'Teléfonos: 52 - 37317 - Fax: 52 - 47110', 0, 1, 'C');
$pdf->Cell(0, 4, 'Oruro - Bolivia', 0, 1, 'C');

$pdf->Ln(5);

// INFORMACIÓN DEL MÓDULO
$pdf->SetFont('helvetica', '', 9);

// Tabla de información
$tbl = '
<table border="1" cellpadding="4">
    <tr>
        <td width="25%" style="font-weight:bold; background-color:#f0f0f0;">PROGRAMA:</td>
        <td width="75%">' . htmlspecialchars($programaNombre) . '</td>
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

// TABLA DE CALIFICACIONES
$pdf->SetFont('helvetica', '', 8);

// Encabezado de tabla
$html = '
<table border="1" cellpadding="3">
    <thead>
        <tr style="background-color:#667eea; color:#ffffff; font-weight:bold;">
            <th width="4%" align="center">#</th>
            <th width="30%" align="center">NOMBRE COMPLETO</th>
            <th width="10%" align="center">SIGLA</th>
            <th width="12%" align="center">MÓDULO</th>
            <th width="8%" align="center">NUM.</th>
            <th width="36%" align="center">LITERAL</th>
        </tr>
    </thead>
    <tbody>';

if (count($estudiantes) > 0) {
    foreach ($estudiantes as $index => $est) {
        $nombreCompleto = htmlspecialchars($est['Nombre'] . ' ' . $est['Apaterno'] . ' ' . $est['Amaterno']);
        $nota = $est['Nota'];

        if ($nota === null || $nota === '') {
            $notaMostrar = '-';
            $notaLiteral = '-';
        } else {
            $notaInt = intval($nota);
            $notaMostrar = $notaInt;
            $notaLiteral = numeroALetras($notaInt);
        }

        $bgcolor = ($index % 2 == 0) ? '#ffffff' : '#f8f9fa';

        $html .= '
        <tr style="background-color:' . $bgcolor . ';">
            <td align="center">' . ($index + 1) . '</td>
            <td>' . $nombreCompleto . '</td>
            <td align="center"></td>
            <td align="center">' . htmlspecialchars($moduloCodigo) . '</td>
            <td align="center"><strong>' . $notaMostrar . '</strong></td>
            <td>' . $notaLiteral . '</td>
        </tr>';
    }
} else {
    $html .= '
    <tr>
        <td colspan="6" align="center" style="color:#999; font-style:italic;">
            No hay estudiantes inscritos en este módulo
        </td>
    </tr>';
}

$html .= '
    </tbody>
</table>';

$pdf->writeHTML($html, true, false, false, false, '');

// PIE DE PÁGINA
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 9);

// Firmas
$pdf->Cell(90, 5, '', 0, 0);
$pdf->Cell(90, 5, '', 0, 1);

$pdf->Ln(10);

$pdf->Cell(90, 1, '', 'T', 0, 'C');
$pdf->Cell(10, 1, '', 0, 0);
$pdf->Cell(90, 1, '', 'T', 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(90, 5, 'Firma del Docente', 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, 'Sello y Firma de Autorización', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(90, 4, htmlspecialchars($docenteNombre), 0, 0, 'C');
$pdf->Cell(10, 4, '', 0, 0);
$pdf->Cell(90, 4, 'COORDINACIÓN POSGRADO ODONTOLOGÍA', 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 7);
$pdf->Cell(0, 4, 'Documento generado automáticamente por el Sistema de Gestión Académica - ' . date('d/m/Y H:i:s'), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

// Crear directorio si no existe
$dirPDFs = __DIR__ . '/../../pdfs_generados';
if (!file_exists($dirPDFs)) {
    mkdir($dirPDFs, 0777, true);
}

// Nombre del archivo
$nombreArchivo = 'calificaciones_' . $moduloCodigo . '_' . date('Ymd_His') . '.pdf';
$rutaCompleta = $dirPDFs . '/' . $nombreArchivo;

// Guardar PDF
$pdf->Output($rutaCompleta, 'F');

// También generar salida para el navegador
$pdf->Output($nombreArchivo, 'I');

// Si se requiere JSON response (para AJAX)
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    echo json_encode([
        'status' => 'success',
        'message' => 'PDF generado correctamente',
        'archivo' => $nombreArchivo,
        'ruta' => 'pdfs_generados/' . $nombreArchivo,
        'totalEstudiantes' => count($estudiantes)
    ]);
}
?>
