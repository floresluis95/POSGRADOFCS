<?php
/**
 * Generador de PDF de Planilla de Calificaciones
 * Formato institucional - Universidad Técnica de Oruro
 * Facultad de Ciencias de la Salud - Coordinación Posgrado Odontología
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Iniciar sesión para validación
session_start();

// Verificar sesión válida
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}

// Incluir autoload de Composer para TCPDF
require_once '../../vendor/autoload.php';

// Incluir modelos necesarios
require_once '../../modelos/conexion.modelo.php';
require_once '../../modelos/calificacion.modelo.php';

// Configurar zona horaria
date_default_timezone_set("America/La_Paz");

// Recibir datos del POST
$programaNombre = $_POST['programaNombre'] ?? '';
$moduloNombre = $_POST['moduloNombre'] ?? '';
$moduloCodigo = $_POST['moduloCodigo'] ?? '';
$docenteNombre = $_POST['docenteNombre'] ?? '';
$siglaModulo = $_POST['siglaModulo'] ?? '';
$fechaInicio = $_POST['fechaInicio'] ?? '';
$fechaFin = $_POST['fechaFin'] ?? '';
$moduloID = $_POST['moduloID'] ?? 0;
$programaID = $_POST['programaID'] ?? 0;
$grado = $_POST['grado'] ?? '';

// Soporte para formato antiguo (fechaPlanilla) para compatibilidad
if (empty($fechaInicio) && !empty($_POST['fechaPlanilla'])) {
    $fechaInicio = $_POST['fechaPlanilla'];
    $fechaFin = $_POST['fechaPlanilla'];
}

// Validar datos requeridos
if (empty($programaNombre) || empty($moduloNombre) || empty($docenteNombre) ||
    empty($fechaInicio) || empty($fechaFin) || empty($moduloID) || empty($programaID)) {
    echo "<h2>Error: Faltan datos requeridos</h2>";
    echo "<h3>Datos recibidos:</h3>";
    echo "<pre>";
    echo "programaNombre: '" . $programaNombre . "'\n";
    echo "moduloNombre: '" . $moduloNombre . "'\n";
    echo "moduloCodigo: '" . $moduloCodigo . "'\n";
    echo "docenteNombre: '" . $docenteNombre . "'\n";
    echo "fechaInicio: '" . $fechaInicio . "'\n";
    echo "fechaFin: '" . $fechaFin . "'\n";
    echo "moduloID: " . $moduloID . "\n";
    echo "programaID: " . $programaID . "\n";
    echo "</pre>";
    die();
}

// Obtener estudiantes y calificaciones del módulo
$estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

// Formatear fechas para mostrar
$fechaInicioFormateada = date('d/m/Y', strtotime($fechaInicio));
$fechaFinFormateada = date('d/m/Y', strtotime($fechaFin));

/**
 * Función para convertir números a letras
 */
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

// ===================================
// ENCABEZADO
// ===================================
$logoUTO = '../../extensiones/imagenespdf/logouto.png';
$logoFCS = '../../extensiones/imagenespdf/logofcs.png';

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

// ===================================
// INFORMACIÓN DEL MÓDULO
// ===================================
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

// ===================================
// TÍTULO DE LA PLANILLA
// ===================================
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'PLANILLA DE CALIFICACIONES', 0, 1, 'C');
$pdf->Ln(2);

// ===================================
// TABLA DE CALIFICACIONES
// ===================================
$pdf->SetFont('helvetica', '', 8);

// Definir anchos de columnas (total debe ser 185mm = ancho útil de la página)
$w1 = 70;  // NOMBRE COMPLETO
$w2 = 25;  // SIGLA
$w3 = 30;  // MÓDULO
$w4 = 20;  // NUM.
$w5 = 40;  // LITERAL

// Encabezados de la tabla
$pdf->SetFillColor(102, 126, 234); // Color morado #667eea
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->SetFont('helvetica', 'B', 9);

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
    $pdf->Cell($w1 + $w2 + $w3 + $w4 + $w5, 10, 'No hay estudiantes inscritos en este módulo', 1, 1, 'C');
} else {
    // Iterar sobre los estudiantes
    foreach ($estudiantes as $index => $estudiante) {
        // Obtener datos del estudiante
        $nombre = isset($estudiante['Nombre']) ? $estudiante['Nombre'] : '';
        $apaterno = isset($estudiante['Apaterno']) ? $estudiante['Apaterno'] : '';
        $amaterno = isset($estudiante['Amaterno']) ? $estudiante['Amaterno'] : '';
        $nombreCompleto = trim($nombre . ' ' . $apaterno . ' ' . $amaterno);

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
        $pdf->Cell($w1, 6, $nombreCompleto, 1, 0, 'L', true);
        $pdf->Cell($w2, 6, $siglaModulo, 1, 0, 'C', true);
        $pdf->Cell($w3, 6, $moduloCodigo, 1, 0, 'C', true);

        // Nota numérica en negrita
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell($w4, 6, $notaMostrar, 1, 0, 'C', true);
        $pdf->SetFont('helvetica', '', 8);

        $pdf->Cell($w5, 6, $notaLiteral, 1, 1, 'L', true);
    }
}

// ===================================
// PIE DE PÁGINA
// ===================================
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

// ===================================
// SALIDA DEL PDF
// ===================================

// Limpiar buffer de salida
if (ob_get_contents()) {
    ob_end_clean();
}

// Nombre del archivo
$fechaInicioArchivo = date('Ymd', strtotime($fechaInicio));
$fechaFinArchivo = date('Ymd', strtotime($fechaFin));
if ($fechaInicioArchivo === $fechaFinArchivo) {
    $nombreArchivo = 'Planilla_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $moduloCodigo) . '_' . $fechaInicioArchivo . '.pdf';
} else {
    $nombreArchivo = 'Planilla_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $moduloCodigo) . '_' . $fechaInicioArchivo . '_' . $fechaFinArchivo . '.pdf';
}

// Salida del PDF (inline en navegador)
$pdf->Output($nombreArchivo, 'I');

exit;
?>
