<?php
/**
 * Generador de PDF de Planilla de Calificaciones
 * Formato institucional - Universidad Técnica de Oruro
 * Facultad de Ciencias de la Salud - Coordinación Posgrado Odontología
 */

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar sesión para validación
session_start();

// Verificar sesión válida
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}
// Reporte de calificaciones: solo Administración, Secretaría y Docentes (no Estudiantes).
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'DOC'], true)) {
    die('Acceso no autorizado.');
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
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)

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
// Fecha de "Oruro, [fecha]" junto a las firmas: es la fecha en que se firma
// la planilla, editable por separado de la fecha de inicio/fin del módulo.
// Si no se envía (compatibilidad), se usa la fecha de hoy.
$fechaGeneracion = !empty($_POST['fechaGeneracion']) ? $_POST['fechaGeneracion'] : date('Y-m-d');

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
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

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

// ===================================
// ENCABEZADO (formato oficial FORMATO_CALIFICACIONES.docx)
// ===================================
$logoUTO = InstitucionModelo::LogoPdf('LOGO_IZQUIERDO');
$logoFCS = InstitucionModelo::LogoPdf('LOGO_DERECHO');

// Logo izquierdo y derecho: misma caja de 22x22mm para los dos (con
// $fitbox='CM' cada logo se escala manteniendo su proporción y se centra
// dentro de la caja), porque logouto.png y logofcs.png tienen proporciones
// muy distintas y con solo el ancho fijo salían de alturas muy diferentes.
if ($logoUTO) {
    $pdf->Image($logoUTO, 15, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
}
if ($logoFCS) {
    $pdf->Image($logoFCS, 173, 10, 22, 22, 'PNG', '', '', true, 300, '', false, false, 0, 'CM');
}

// Título centrado
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetY(12);
$pdf->Cell(0, 5, InstitucionModelo::Universidad(), 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 5, InstitucionModelo::Facultad(), 0, 1, 'C');
$pdf->Cell(0, 5, InstitucionModelo::Unidad(), 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 4, InstitucionModelo::Direccion(), 0, 1, 'C');
$pdf->Cell(0, 4, InstitucionModelo::Telefonos(), 0, 1, 'C');
$pdf->Cell(0, 4, InstitucionModelo::Ciudad(), 0, 1, 'C');

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
// TABLA DE CALIFICACIONES (encabezado agrupado "POSGRADUANTES" / "CALIFICACION",
// igual al formato oficial FORMATO_CALIFICACIONES.docx)
// ===================================
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
// PIE DE PÁGINA: escala de calificación + lugar/fecha + firmas, fijo al pie
// de la página (si no entra en la página actual, se pasa a una nueva antes
// de fijarlo, para que no quede pegado justo debajo de la tabla en módulos
// con pocos estudiantes).
// ===================================
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
$pdf->Cell(0, 5, 'Oruro, ' . fechaLarga($fechaGeneracion), 0, 1, 'C');

$pdf->Ln(12);

$pdf->Cell(90, 1, '', 'T', 0, 'C');
$pdf->Cell(10, 1, '', 0, 0);
$pdf->Cell(90, 1, '', 'T', 1, 'C');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(90, 5, 'DOCENTE', 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, InstitucionModelo::CargoFirma(), 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(90, 4, htmlspecialchars($docenteNombre), 0, 0, 'C');
$pdf->Cell(10, 4, '', 0, 0);
$pdf->Cell(90, 4, '', 0, 1, 'C');
$pdf->SetAutoPageBreak(true, 15);

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
