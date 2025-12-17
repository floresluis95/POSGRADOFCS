<?php
/**
 * Generar Lista de Asistencia en PDF usando TCPDF
 */

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar búfer de salida
ob_start();

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    ob_end_clean();
    die('Acceso denegado. Sesión no válida.');
}

// Incluir archivos necesarios
try {
    require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
    require_once __DIR__ . '/../../modelos/conexion.modelo.php';
} catch (Exception $e) {
    ob_end_clean();
    die('Error al cargar librerías: ' . $e->getMessage());
}

// Recibir parámetros
$moduloID = $_GET['moduloID'] ?? '';
$programaID = $_GET['programaID'] ?? '';
$moduloNombre = $_GET['moduloNombre'] ?? '';
$moduloCodigo = $_GET['moduloCodigo'] ?? '';
$programaNombre = $_GET['programaNombre'] ?? '';
$grado = $_GET['grado'] ?? '';
$docenteNombre = $_GET['docenteNombre'] ?? '';

// Validar parámetros
if (empty($moduloID) || empty($programaID)) {
    ob_end_clean();
    die('Parámetros inválidos. ModuloID: ' . $moduloID . ', ProgramaID: ' . $programaID);
}

// Obtener lista de estudiantes inscritos en el módulo
try {
    $conexion = Conexion::Conectar();

    if (!$conexion) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    $sql = "SELECT
                e.EstudianteID,
                e.Ci,
                e.Complemento,
                e.Exp,
                e.Nombre,
                e.Apaterno,
                e.Amaterno
            FROM estudiante e
            INNER JOIN estudianteprograma ep ON e.EstudianteID = ep.EstudianteID
            WHERE ep.ProgramaID = :programaID
            AND ep.Estado = 'ACTIVO'
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
    $stmt->execute();
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($estudiantes)) {
        ob_end_clean();
        die('No se encontraron estudiantes inscritos en este programa. ProgramaID: ' . $programaID);
    }

} catch (Exception $e) {
    ob_end_clean();
    die('Error al consultar estudiantes: ' . $e->getMessage() . ' - Línea: ' . $e->getLine());
}

// Crear instancia de TCPDF en orientación horizontal
try {
    $pdf = new TCPDF('L', 'mm', 'LETTER', true, 'UTF-8', false);
} catch (Exception $e) {
    ob_end_clean();
    die('Error al crear instancia de TCPDF: ' . $e->getMessage());
}

// Configurar información del documento
$pdf->SetCreator('UNIVERSIDAD TECNICA DE ORURO');
$pdf->SetAuthor('Facultad de Ciencias de la Salud - Postgrado');
$pdf->SetTitle('Lista de Asistencia - ' . $moduloNombre);
$pdf->SetSubject('Lista de Asistencia');

// Configurar márgenes (más pequeños para orientación horizontal)
$pdf->SetMargins(10, 15, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Quitar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar auto page break
$pdf->SetAutoPageBreak(TRUE, 10);

// Agregar página
$pdf->AddPage();

// Configurar fuente
$pdf->SetFont('helvetica', '', 9);

// ========================================
// HEADER DEL DOCUMENTO
// ========================================
// --- CONFIGURACIÓN DE COLORES Y FUENTE ---
// --- CONFIGURACIÓN DE COLORES Y FUENTE ---
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('helvetica', 'B', 10);
$anchoLogo = 22; // Reducido un poco para que no se vea tan tosco con texto apretado
$altoLogo = 22;
$yPos = 10;

// --- LOGOS ---
$pdf->Image('../../extensiones/imagenespdf/logouto.png', 10, $yPos, $anchoLogo, $altoLogo);
$pdf->Image('../../extensiones/imagenespdf/logofcs.png', 247.4, $yPos, $anchoLogo, $altoLogo);

// --- ENCABEZADO CON INTERLINEADO REDUCIDO ---
// Reducimos de 7 a 4 o 5 para que el texto esté más pegado
$pdf->Cell(0, 4, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C', false);
$pdf->Cell(0, 4, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C', false);
$pdf->Cell(0, 4, 'COORDINACIÓN DE POSGRADO - ODONTOLOGÍA', 0, 1, 'C', false);

$pdf->SetFont('helvetica', '', 8); // Fuente ligeramente más pequeña para datos de contacto
$pdf->Cell(0, 3.5, 'Av. Del Minero Edificio San Agustín II (Ex Almacenes COMIBOL) Teléfonos: 5237317 - Fax: 5247110', 0, 1, 'C', false);
$pdf->Cell(0, 3.5, 'Oruro - Bolivia', 0, 1, 'C', false);

$pdf->Ln(2); // Salto pequeño antes de la línea

// --- LÍNEA DIVISORIA ---
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 269.4, $pdf->GetY()); 

$pdf->Ln(4); // Espacio antes del título

// --- TÍTULO DEL DOCUMENTO ---
$pdf->SetFont('helvetica', 'B', 12); 
$pdf->Cell(0, 6, 'LISTA DE ASISTENCIA', 0, 1, 'C');
$pdf->Ln(2);

// ========================================
// INFORMACIÓN DEL MÓDULO
// ========================================
$pdf->SetTextColor(70, 78, 95);

$pdf->SetFont('helvetica', 'B', 9);

// Fila 1: Programa y Grado
$pdf->Cell(65, 5, 'PROGRAMA:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(130, 5, $programaNombre, 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'GRADO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $grado, 0, 1, 'L');

// Fila 2: Módulo y Código
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(65, 5, 'MÓDULO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(130, 5, $moduloNombre, 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'CÓDIGO:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $moduloCodigo, 0, 1, 'L');

// Fila 3: Docente
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(65, 5, 'DOCENTE:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $docenteNombre, 0, 1, 'L');

$pdf->Ln(3);

// Línea separadora
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 269, $pdf->GetY());

$pdf->Ln(3);

// ========================================
// TABLA DE ASISTENCIA
// ========================================

// Configurar colores para la tabla
$pdf->SetFillColor(102, 126, 234);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('helvetica', 'B', 7);

// Ancho de las columnas
$colNro = 10;
$colEstudiante = 70;
$colFecha = 22.25; // 89 / 4 = 22.25 (para 4 fechas)
$colFirma = 22.25;
$anchoTotal = $colNro + $colEstudiante + ($colFecha + $colFirma) * 4;

// ENCABEZADO DE LA TABLA - Fila 1 (Fechas)
$pdf->Cell($colNro, 8, 'N°', 1, 0, 'C', true);
$pdf->Cell($colEstudiante, 8, 'ESTUDIANTE', 1, 0, 'C', true);

// 4 Fechas
for ($i = 1; $i <= 4; $i++) {
    $pdf->Cell($colFecha + $colFirma, 4, 'FECHA ' . $i . ': ____/____/____', 1, 0, 'C', true);
}
$pdf->Ln();

// ENCABEZADO DE LA TABLA - Fila 2 (Presente/Firma)
$pdf->Cell($colNro, 4, '', 0, 0, 'C');
$pdf->Cell($colEstudiante, 4, '', 0, 0, 'C');

for ($i = 1; $i <= 4; $i++) {
    $pdf->Cell($colFecha, 4, 'PRESENTE', 1, 0, 'C', true);
    $pdf->Cell($colFirma, 4, 'FIRMA', 1, 0, 'C', true);
}
$pdf->Ln();

// CONTENIDO DE LA TABLA
$pdf->SetTextColor(51, 51, 51);
$pdf->SetFont('helvetica', '', 7);

$alturaFila = 8;
$numeroEstudiante = 1;

foreach ($estudiantes as $estudiante) {
    // Verificar si necesita nueva página
    if ($pdf->GetY() > 170) {
        $pdf->AddPage();

        // Re-imprimir encabezado de tabla
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 7);

        $pdf->Cell($colNro, 8, 'N°', 1, 0, 'C', true);
        $pdf->Cell($colEstudiante, 8, 'ESTUDIANTE', 1, 0, 'C', true);

        for ($i = 1; $i <= 4; $i++) {
            $pdf->Cell($colFecha + $colFirma, 4, 'FECHA ' . $i . ': ____/____/____', 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->Cell($colNro, 4, '', 0, 0, 'C');
        $pdf->Cell($colEstudiante, 4, '', 0, 0, 'C');

        for ($i = 1; $i <= 4; $i++) {
            $pdf->Cell($colFecha, 4, 'PRESENTE', 1, 0, 'C', true);
            $pdf->Cell($colFirma, 4, 'FIRMA', 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetFont('helvetica', '', 7);
    }

    // Número de estudiante
    $pdf->Cell($colNro, $alturaFila, $numeroEstudiante, 1, 0, 'C');

    // Nombre completo del estudiante con CI
    $nombreCompleto = trim($estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'] . ' ' . $estudiante['Nombre']);
    $ci = $estudiante['Ci'];
    if (!empty($estudiante['Complemento'])) {
        $ci .= '-' . $estudiante['Complemento'];
    }
    if (!empty($estudiante['Exp'])) {
        $ci .= ' ' . $estudiante['Exp'];
    }
    $textoEstudiante = $nombreCompleto . ' (CI: ' . $ci . ')';

    $pdf->Cell($colEstudiante, $alturaFila, $textoEstudiante, 1, 0, 'L');

    // 4 Fechas con checkboxes y espacios para firma
    for ($i = 1; $i <= 4; $i++) {
        // Columna para marcar presente (checkbox)
        $pdf->Cell($colFecha, $alturaFila, '', 1, 0, 'C');

        // Columna para firma
        $pdf->Cell($colFirma, $alturaFila, '', 1, 0, 'C');
    }

    $pdf->Ln();
    $numeroEstudiante++;
}

$pdf->Ln(5);

// ========================================
// INFORMACIÓN ADICIONAL
// ========================================
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 4, 'INSTRUCCIONES: Marcar con una X o ✓ en la columna "PRESENTE" cuando el estudiante asista a clase. El estudiante deberá firmar en la columna "FIRMA" correspondiente.', 0, 'L');

$pdf->Ln(3);

// Totales
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 5, 'Total de Estudiantes Inscritos: ' . count($estudiantes), 0, 1, 'L');

$pdf->Ln(5);

// ========================================
// FIRMAS DE RESPONSABLES
// ========================================
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(51, 51, 51);

// Líneas para firmas
$espacioFirma = 85;
$pdf->Cell($espacioFirma, 5, '', 0, 0, 'C');
$pdf->Cell($espacioFirma, 5, '', 0, 0, 'C');
$pdf->Cell($espacioFirma, 5, '', 0, 1, 'C');

$pdf->Ln(8);

$pdf->SetLineWidth(0.3);
$y = $pdf->GetY();

// Firma 1: Docente
$x1 = 40;
$pdf->Line($x1, $y, $x1 + 60, $y);
$pdf->SetXY($x1, $y + 1);
$pdf->Cell(60, 5, 'FIRMA DEL DOCENTE', 0, 0, 'C');

// Firma 2: Director
$x2 = 170;
$pdf->Line($x2, $y, $x2 + 60, $y);
$pdf->SetXY($x2, $y + 1);
$pdf->Cell(60, 5, 'FIRMA DEL DIRECTOR', 0, 1, 'C');

$pdf->Ln(3);

// Pie de página
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 4, 'Documento generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s'), 0, 1, 'C');

// ========================================
// SALIDA DEL PDF
// ========================================
try {
    ob_end_clean();

    $nombreArchivo = 'Lista_Asistencia_' . str_replace(' ', '_', $moduloCodigo) . '_' . date('YmdHis') . '.pdf';
    $pdf->Output($nombreArchivo, 'I'); // I = inline (mostrar en navegador)

} catch (Exception $e) {
    ob_end_clean();
    die('Error al generar PDF: ' . $e->getMessage());
}

exit;
?>
