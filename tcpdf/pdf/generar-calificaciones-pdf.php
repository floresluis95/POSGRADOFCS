<?php
/**
 * Generador de PDF de Planilla de Calificaciones
 * Utiliza TCPDF para generar la planilla de calificaciones con datos del módulo
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORAL: Mostrar errores en pantalla
ini_set('log_errors', 1);

// Iniciar sesión para validación
session_start();

// TEMPORAL: Depuración de datos POST
if (isset($_GET['debug'])) {
    echo "<h2>DEPURACIÓN - Datos recibidos</h2>";
    echo "<h3>POST:</h3><pre>";
    print_r($_POST);
    echo "</pre>";
    echo "<h3>Sesión:</h3><pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

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
$fechaPlanilla = $_POST['fechaPlanilla'] ?? '';
$moduloID = $_POST['moduloID'] ?? 0;
$programaID = $_POST['programaID'] ?? 0;
$grado = $_POST['grado'] ?? '';

// Validar datos requeridos
if (empty($programaNombre) || empty($moduloNombre) || empty($docenteNombre) ||
    empty($fechaPlanilla) || empty($moduloID) || empty($programaID)) {
    echo "<h2>Error: Faltan datos requeridos</h2>";
    echo "<h3>Datos recibidos:</h3>";
    echo "<pre>";
    echo "programaNombre: '" . $programaNombre . "'\n";
    echo "moduloNombre: '" . $moduloNombre . "'\n";
    echo "moduloCodigo: '" . $moduloCodigo . "'\n";
    echo "docenteNombre: '" . $docenteNombre . "'\n";
    echo "fechaPlanilla: '" . $fechaPlanilla . "'\n";
    echo "moduloID: " . $moduloID . "\n";
    echo "programaID: " . $programaID . "\n";
    echo "grado: '" . $grado . "'\n";
    echo "</pre>";
    echo "<h3>POST completo:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    die();
}

// Obtener estudiantes y calificaciones del módulo
$estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

// Formatear fecha para mostrar
$fechaFormateada = date('d/m/Y', strtotime($fechaPlanilla));

/**
 * Convertir nota numérica a literal
 */
function convertirNotaALiteral($nota) {
    if ($nota === null || $nota === '') {
        return '';
    }

    $notaInt = intval($nota);

    // Números del 0 al 29
    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = [
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
        15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE'
    ];

    if ($notaInt == 0) {
        return 'CERO';
    } elseif ($notaInt == 100) {
        return 'CIEN';
    } elseif ($notaInt >= 10 && $notaInt <= 19) {
        return $especiales[$notaInt];
    } elseif ($notaInt < 10) {
        return $unidades[$notaInt];
    } else {
        $decena = intval($notaInt / 10);
        $unidad = $notaInt % 10;

        if ($unidad == 0) {
            return $decenas[$decena];
        } else {
            if ($decena == 2) {
                return 'VEINTI' . $unidades[$unidad];
            } else {
                return $decenas[$decena] . ' Y ' . $unidades[$unidad];
            }
        }
    }
}

// Crear instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Gestión Académica - POSGRADO ODONTOLOGÍA');
$pdf->SetAuthor('POSGRADO ODONTOLOGÍA');
$pdf->SetTitle('Planilla de Calificaciones - ' . $moduloNombre);
$pdf->SetSubject('Planilla de Calificaciones');

// Eliminar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// Agregar página
$pdf->AddPage();

// Configurar fuente
$pdf->SetFont('helvetica', '', 10);

// ===================================
// ENCABEZADO DEL DOCUMENTO
// ===================================

// Logo o título institucional
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 7, 'UNIVERSIDAD MAYOR DE SAN ANDRÉS', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'FACULTAD DE ODONTOLOGÍA', 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'POSGRADO ODONTOLOGÍA', 0, 1, 'C');
$pdf->Ln(5);

// Título del documento
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'PLANILLA DE CALIFICACIONES', 0, 1, 'C');
$pdf->Ln(5);

// ===================================
// TABLA DE POSGRADUANTES
// ===================================

// Encabezado de la tabla
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(200, 200, 200);

$pdf->Cell(10, 7, 'N°', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'NOMBRE COMPLETO', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'SIGLA', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'MÓDULO', 1, 0, 'C', true);
$pdf->Cell(15, 7, 'NUM', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'LITERAL', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetFillColor(255, 255, 255);

// Verificar si hay estudiantes
if (empty($estudiantes)) {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(0, 10, 'No hay estudiantes registrados en este módulo', 1, 1, 'C');
} else {
    // Iterar sobre los estudiantes
    $contador = 1;
    foreach ($estudiantes as $estudiante) {
        $nombreCompleto = strtoupper(trim($estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'] . ' ' . $estudiante['Nombre']));
        $nota = $estudiante['Nota'] !== null ? number_format(floatval($estudiante['Nota']), 0) : '';
        $notaLiteral = $estudiante['Nota'] !== null ? convertirNotaALiteral(floatval($estudiante['Nota'])) : '';

        // Dibujar fila
        $pdf->Cell(10, 6, $contador, 1, 0, 'C', false);
        $pdf->Cell(70, 6, $nombreCompleto, 1, 0, 'L', false);
        $pdf->Cell(25, 6, $moduloCodigo, 1, 0, 'C', false);
        $pdf->Cell(40, 6, strtoupper($moduloNombre), 1, 0, 'L', false);
        $pdf->Cell(15, 6, $nota, 1, 0, 'C', false);
        $pdf->Cell(25, 6, $notaLiteral, 1, 1, 'C', false);

        $contador++;
    }
}

$pdf->Ln(10);

// ===================================
// ESCALA DE CALIFICACIÓN
// ===================================

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'ESCALA DE CALIFICACIÓN', 0, 1, 'C');
$pdf->Ln(2);

// Recuadro de escala
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(92.5, 7, '0 - 75 = REPROBADO', 1, 0, 'C', false);
$pdf->Cell(92.5, 7, '76 - 100 = APROBADO', 1, 1, 'C', false);

$pdf->Ln(10);

// ===================================
// FECHA
// ===================================

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'FECHA: ' . $fechaFormateada, 0, 1, 'L');

$pdf->Ln(15);

// ===================================
// FIRMAS
// ===================================

$pdf->SetFont('helvetica', '', 9);

// Líneas para firmas
$pdf->Cell(92.5, 0, '', 'T', 0, 'C');
$pdf->Cell(92.5, 0, '', 'T', 1, 'C');

$pdf->Ln(2);

// Textos de las firmas
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(92.5, 5, 'FIRMA DEL DOCENTE', 0, 0, 'C');
$pdf->Cell(92.5, 5, 'COORDINADOR POSGRADO ODONTOLOGÍA', 0, 1, 'C');

$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(92.5, 5, strtoupper($docenteNombre), 0, 0, 'C');
$pdf->Cell(92.5, 5, '', 0, 1, 'C');

// ===================================
// SALIDA DEL PDF
// ===================================

// Limpiar buffer de salida (si existe)
if (ob_get_contents()) {
    ob_end_clean();
}

// Nombre del archivo
$nombreArchivo = 'Planilla_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $moduloCodigo) . '_' . date('YmdHis') . '.pdf';

// Salida del PDF (Descarga)
$pdf->Output($nombreArchivo, 'I'); // 'I' = inline (mostrar en navegador), 'D' = download

exit;
?>
