<?php
/**
 * Generar Lista de Asistencia en PDF usando TCPDF
 */

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, las
// fechas impresas en el PDF no corresponden a la hora de Bolivia.
date_default_timezone_set('America/La_Paz');

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 0);

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
// Listas de asistencia/calificaciones: solo Administración, Secretaría y Docentes
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX', 'DOC'], true)) {
    ob_end_clean();
    die('Acceso denegado.');
}

// Incluir archivos necesarios
try {
    require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
    require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
    require_once __DIR__ . '/../../modelos/matricula.modelo.php'; // quién cursa el módulo (abandonos)
    require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)
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
            AND " . MatriculaModelos::SqlCursaModulo('ep', ':moduloID') . "
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
    $stmt->bindParam(':moduloID', $moduloID, PDO::PARAM_INT);
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
$pdf->Image(InstitucionModelo::LogoPdf('LOGO_IZQUIERDO'), 10, $yPos, $anchoLogo, $altoLogo, '', '', '', false, 300, '', false, false, 0, 'CM');
$pdf->Image(InstitucionModelo::LogoPdf('LOGO_DERECHO'), 247.4, $yPos, $anchoLogo, $altoLogo, '', '', '', false, 300, '', false, false, 0, 'CM');

// --- ENCABEZADO CON INTERLINEADO REDUCIDO ---
// Reducimos de 7 a 4 o 5 para que el texto esté más pegado
$pdf->Cell(0, 4, InstitucionModelo::Universidad(), 0, 1, 'C', false);
$pdf->Cell(0, 4, InstitucionModelo::Facultad(), 0, 1, 'C', false);
$pdf->Cell(0, 4, InstitucionModelo::Unidad(), 0, 1, 'C', false);

$pdf->SetFont('helvetica', '', 8); // Fuente ligeramente más pequeña para datos de contacto
$pdf->Cell(0, 3.5, InstitucionModelo::DireccionCompleta(' - ', false), 0, 1, 'C', false);
$pdf->Cell(0, 3.5, InstitucionModelo::Ciudad(), 0, 1, 'C', false);

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

// Los nombres se guardan con htmlspecialchars; se decodifican para no imprimir "&quot;"
$programaNombre = html_entity_decode($programaNombre, ENT_QUOTES, 'UTF-8');
$moduloNombre = html_entity_decode($moduloNombre, ENT_QUOTES, 'UTF-8');
$docenteNombre = html_entity_decode($docenteNombre, ENT_QUOTES, 'UTF-8');

// Anchos de columna (ancho útil de la hoja horizontal: 279.4 - 10 - 10 = 259.4 mm)
$anchoEtiqueta = 25;
$anchoValor = 170;
$anchoEtiqueta2 = 20;
$anchoValor2 = 259.4 - $anchoEtiqueta - $anchoValor - $anchoEtiqueta2;

/**
 * Imprime una fila "ETIQUETA: valor   ETIQUETA2: valor2". El valor usa MultiCell
 * para que un nombre largo (ej. de un módulo) baje a una segunda línea en vez de
 * salirse de su columna y montarse sobre la columna de la derecha.
 */
$filaDato = function ($etiqueta, $valor, $etiqueta2 = null, $valor2 = '') use ($pdf, $anchoEtiqueta, $anchoValor, $anchoEtiqueta2, $anchoValor2) {
    $alto = 5;
    $anchoPrincipal = $etiqueta2 === null ? $anchoValor + $anchoEtiqueta2 + $anchoValor2 : $anchoValor;

    // Altura de la fila según el texto más alto
    $pdf->SetFont('helvetica', '', 9);
    $altoFila = max($alto, $pdf->getStringHeight($anchoPrincipal, $valor));
    if ($etiqueta2 !== null) {
        $altoFila = max($altoFila, $pdf->getStringHeight($anchoValor2, $valor2));
    }

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell($anchoEtiqueta, $alto, $etiqueta, 0, 'L', false, 0, $x, $y);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell($anchoPrincipal, $alto, $valor, 0, 'L', false, 0);

    if ($etiqueta2 !== null) {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->MultiCell($anchoEtiqueta2, $alto, $etiqueta2, 0, 'L', false, 0);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell($anchoValor2, $alto, $valor2, 0, 'L', false, 0);
    }

    $pdf->SetXY($x, $y + $altoFila);
};

$filaDato('PROGRAMA:', $programaNombre, 'GRADO:', $grado);
$filaDato('MÓDULO:', $moduloNombre, 'CÓDIGO:', $moduloCodigo);
$filaDato('DOCENTE:', $docenteNombre);

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
