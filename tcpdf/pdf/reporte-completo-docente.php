<?php
/**
 * Generador de PDF - Reporte Completo de Calificaciones del Docente
 * Genera un reporte con TODOS los módulos del docente y sus calificaciones:
 * una página por módulo, cada una con el mismo formato institucional que
 * "Planilla de Calificaciones" (FORMATO_CALIFICACIONES.docx).
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Iniciar sesión
session_start();

// Verificar sesión válida
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida. <br><a href="../../index.php">Ir al inicio de sesión</a>');
}

// Incluir autoload de Composer para TCPDF
require_once '../../vendor/autoload.php';

// Incluir modelos necesarios
// modelos/conexion.modelo.php no existe en este servidor (lo eliminó el
// antivirus del equipo; ver el workaround documentado en el .htaccess de la
// raíz), así que hay que caer a la copia de respaldo en core/. Sin este
// fallback, este archivo fallaba con un error fatal en TODAS las peticiones
// (por eso nunca se veía el PDF).
require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php')
    ? __DIR__ . '/../../modelos/conexion.modelo.php'
    : __DIR__ . '/../../core/conexion.modelo.php';
require_once '../../modelos/calificacion.modelo.php';

// Configurar zona horaria
date_default_timezone_set("America/La_Paz");

// Fecha de "Oruro, [fecha]" junto a las firmas: es la fecha en que se firma
// la planilla, editable desde el diálogo que abre el reporte. Si no se
// envía (compatibilidad), se usa la fecha de hoy.
$fechaGeneracion = !empty($_GET['fechaGeneracion']) ? $_GET['fechaGeneracion'] : date('Y-m-d');

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

// Crear instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('UTO - Facultad de Ciencias de la Salud');
$pdf->SetTitle('Reporte de Calificaciones - ' . $docenteNombre);
$pdf->SetSubject('Reporte Completo de Calificaciones');

// Eliminar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

$logoUTO = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
$logoFCS = __DIR__ . '/../../extensiones/imagenespdf/logofcs.png';

// Anchos de columnas de la tabla de calificaciones (total 185mm)
$w0 = 10;  // N°
$w1 = 75;  // NOMBRE COMPLETO
$w2 = 20;  // SIGLA
$w3 = 20;  // MÓDULO
$w4 = 20;  // NUM.
$w5 = 40;  // LITERAL

// Iterar por cada módulo y crear una página con el formato oficial
// (FORMATO_CALIFICACIONES.docx) para cada uno.
foreach ($modulos as $index => $modulo) {
    $pdf->AddPage();

    $moduloID = $modulo['Idmodulo'];
    $programaID = $modulo['ProgramaID'];
    $moduloNombre = $modulo['nombremodulo'];
    $moduloCodigo = $modulo['codigomodulo'];
    $programaNombre = $modulo['NombrePrograma'];
    $sigla = $modulo['Sigla'] ?? '';

    // Obtener estudiantes y calificaciones
    $estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

    // ===================================
    // ENCABEZADO (formato oficial FORMATO_CALIFICACIONES.docx)
    // ===================================
    if (file_exists($logoUTO)) {
        $pdf->Image($logoUTO, 15, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
    }
    if (file_exists($logoFCS)) {
        $pdf->Image($logoFCS, 173, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
    }

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetY(12);
    $pdf->Cell(0, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');
    $pdf->Cell(0, 5, 'COORDINACIÓN POSGRADO ÁREA - ODONTOLOGÍA', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 4, 'Presidente Montes N°6390 y Aldana, edificio Posgrado UTO 2do piso', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Teléfono (591)2- 52 75798 - Fax:(591) 2- 52 55443', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Oruro – Bolivia', 0, 1, 'C');

    $pdf->Ln(5);

    // ===================================
    // INFORMACIÓN DEL MÓDULO
    // ===================================
    $pdf->SetFont('helvetica', '', 9);
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
    // TABLA DE CALIFICACIONES (encabezado agrupado "POSGRADUANTES" /
    // "CALIFICACION", igual al formato oficial)
    // ===================================
    $pdf->SetFont('helvetica', '', 8);

    $pdf->SetFillColor(30, 60, 114); // Azul institucional
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell($w0 + $w1 + $w2 + $w3, 6, 'POSGRADUANTES', 1, 0, 'C', true);
    $pdf->Cell($w4 + $w5, 6, 'CALIFICACION', 1, 1, 'C', true);

    $pdf->Cell($w0, 7, 'N°', 1, 0, 'C', true);
    $pdf->Cell($w1, 7, 'NOMBRE COMPLETO', 1, 0, 'C', true);
    $pdf->Cell($w2, 7, 'SIGLA', 1, 0, 'C', true);
    $pdf->Cell($w3, 7, 'MÓDULO', 1, 0, 'C', true);
    $pdf->Cell($w4, 7, 'NUM.', 1, 0, 'C', true);
    $pdf->Cell($w5, 7, 'LITERAL', 1, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);

    if (empty($estudiantes)) {
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell($w0 + $w1 + $w2 + $w3 + $w4 + $w5, 10, 'No hay estudiantes inscritos en este módulo', 1, 1, 'C');
    } else {
        foreach ($estudiantes as $i => $estudiante) {
            $nombre = $estudiante['Nombre'] ?? '';
            $apaterno = $estudiante['Apaterno'] ?? '';
            $amaterno = $estudiante['Amaterno'] ?? '';
            $nombreCompleto = trim($apaterno . ' ' . $amaterno . ' ' . $nombre);

            $nota = $estudiante['Nota'] ?? null;
            if ($nota === null || $nota === '') {
                $notaMostrar = '-';
                $notaLiteral = '-';
            } else {
                $notaInt = intval($nota);
                $notaMostrar = $notaInt;
                $notaLiteral = numeroALetras($notaInt);
            }

            $pdf->SetFillColor($i % 2 == 0 ? 255 : 248, $i % 2 == 0 ? 255 : 249, $i % 2 == 0 ? 255 : 250);

            $pdf->Cell($w0, 6, $i + 1, 1, 0, 'C', true);
            $pdf->Cell($w1, 6, $nombreCompleto, 1, 0, 'L', true);
            $pdf->Cell($w2, 6, $sigla, 1, 0, 'C', true);
            $pdf->Cell($w3, 6, $moduloCodigo, 1, 0, 'C', true);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell($w4, 6, $notaMostrar, 1, 0, 'C', true);
            $pdf->SetFont('helvetica', '', 8);

            $pdf->Cell($w5, 6, $notaLiteral, 1, 1, 'L', true);
        }
    }

    // ===================================
    // PIE DE PÁGINA: escala de calificación + lugar/fecha + firmas, fijo al
    // pie de esta página (una por módulo).
    // ===================================
    $alturaPie = 55;
    if ($pdf->GetY() > $pdf->getPageHeight() - $pdf->getBreakMargin() - $alturaPie) {
        $pdf->AddPage();
    }
    $pdf->SetAutoPageBreak(false);
    $pdf->SetY(-$alturaPie);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(45, 5, 'ESCALA DE CALIFICACION', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(70, 5, '0  -  75    REPROBADO', 1, 0, 'C');
    $pdf->Cell(70, 5, '76  -  100    APROBADO', 1, 1, 'C');

    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 9);
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
}

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
