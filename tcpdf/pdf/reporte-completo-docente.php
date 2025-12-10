<?php
/**
 * Generador de PDF - Reporte Completo de Calificaciones del Docente
 * Genera un reporte con TODOS los módulos del docente y sus calificaciones
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORAL: Mostrar errores en pantalla
ini_set('log_errors', 1);

// Iniciar sesión
session_start();

// TEMPORAL: Depuración
if (isset($_GET['debug'])) {
    echo "<h2>DEPURACIÓN - Reporte Completo</h2>";
    echo "<h3>Sesión:</h3><pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// Verificar sesión válida
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida. <br><a href="../../index.php">Ir al inicio de sesión</a>');
}

// Incluir autoload de Composer para TCPDF
require_once '../../vendor/autoload.php';

// Incluir modelos necesarios
require_once '../../modelos/conexion.modelo.php';
require_once '../../modelos/calificacion.modelo.php';

// Configurar zona horaria
date_default_timezone_set("America/La_Paz");

// Obtener información del docente logueado
$usuario = $_SESSION['Usuario'] ?? '';

try {
    $pdo = Conexion::Conectar();

    // Buscar docente por CI (que es el usuario)
    $stmt = $pdo->prepare("
        SELECT
            DocenteID,
            Ci,
            Complemento,
            Exp,
            Nombre,
            Apaterno,
            Amaterno,
            Especialidad
        FROM docente
        WHERE Ci = :ci
        AND Estado = 1
        LIMIT 1
    ");
    $stmt->bindParam(":ci", $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$docente) {
        die('Error: No se encontró información del docente.');
    }

    $docenteID = $docente['DocenteID'];
    $docenteNombre = $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'];

    // Obtener módulos asignados al docente
    $modulos = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

    if (empty($modulos)) {
        die('Error: No tiene módulos asignados.');
    }

} catch (PDOException $e) {
    die('Error al obtener datos: ' . $e->getMessage());
}

/**
 * Convertir nota numérica a literal
 */
function convertirNotaALiteral($nota) {
    if ($nota === null || $nota === '') {
        return '';
    }

    $notaInt = intval($nota);

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
$pdf->SetTitle('Reporte de Calificaciones - ' . $docenteNombre);
$pdf->SetSubject('Reporte Completo de Calificaciones');

// Eliminar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// Fecha actual
$fechaActual = date('d/m/Y');

// Iterar por cada módulo y crear una página
foreach ($modulos as $index => $modulo) {
    // Agregar nueva página para cada módulo
    $pdf->AddPage();

    $moduloID = $modulo['Idmodulo'];
    $programaID = $modulo['ProgramaID'];
    $moduloNombre = $modulo['nombremodulo'];
    $moduloCodigo = $modulo['codigomodulo'];
    $programaNombre = $modulo['NombrePrograma'];
    $grado = $modulo['GradoAcademico'];

    // Obtener estudiantes y calificaciones
    $estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

    // ===================================
    // ENCABEZADO DEL DOCUMENTO
    // ===================================

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
    $pdf->Ln(3);

    // Información del módulo
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'PROGRAMA:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, strtoupper($programaNombre), 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'GRADO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, strtoupper($grado), 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'MÓDULO:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, strtoupper($moduloNombre) . ' (' . $moduloCodigo . ')', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 6, 'DOCENTE:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, strtoupper($docenteNombre), 0, 1, 'L');

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
    $pdf->Cell(0, 6, 'FECHA: ' . $fechaActual, 0, 1, 'L');

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

    // Nota de página al final
    if ($index < count($modulos) - 1) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Módulo ' . ($index + 1) . ' de ' . count($modulos), 0, 1, 'C');
    }
}

// Pie de página en la última página
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'Fecha de generación: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
$pdf->Cell(0, 5, 'Generado por: Sistema de Gestión Académica - POSGRADO ODONTOLOGÍA', 0, 1, 'R');

// ===================================
// SALIDA DEL PDF
// ===================================

// Limpiar buffer de salida (si existe)
if (ob_get_contents()) {
    ob_end_clean();
}

// Nombre del archivo
$nombreArchivo = 'Reporte_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $docenteNombre) . '_' . date('YmdHis') . '.pdf';

// Salida del PDF (Inline en navegador)
$pdf->Output($nombreArchivo, 'I');

exit;
?>
