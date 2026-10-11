<?php
/**
 * Certificado de Aprobación Programa de Posgrado - PDF
 * (antes "de Monografía": el trabajo de grado no siempre es una monografía)
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro), en hoja
 * oficio vertical (8.5 x 14 in).
 * Las coordenadas se midieron sobre el formato en Word que usa Posgrado
 * (CERT_APROBACION_*.docx); si algo se desalinea en el papel real, ajustar
 * las constantes del bloque "COORDENADAS".
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}
// Los certificados los emite solo Administración o Secretaría (un estudiante no puede
// generarlos, y así tampoco saltarse el bloqueo de calificaciones por pago vencido).
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)) {
    die('Acceso no autorizado.');
}

require_once '../../vendor/autoload.php';
require_once '../../modelos/certificado.modelo.php';

date_default_timezone_set('America/La_Paz');

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$nombre = $_POST['nombre'] ?? '';
$programa = $_POST['programa'] ?? '';
$curso = $_POST['curso'] ?? '';
$vaucher = $_POST['vaucher'] ?? '';
// Siempre en MAYÚSCULAS, igual que se guarda en trabajogrado
$titulo = CertificadoModelo::NormalizarTitulo($_POST['titulo'] ?? '');
$tribunalRaw = $_POST['tribunal'] ?? '';
$entrega = $_POST['entrega'] ?? '';
$horaEntrega = $_POST['horaEntrega'] ?? '';
$calificacion = $_POST['calificacion'] ?? '';

if (empty($nombre) || empty($titulo) || empty($vaucher) || $calificacion === '') {
    die('Error: Faltan datos requeridos para generar el certificado');
}

$miembrosTribunal = array_filter(array_map('trim', explode('|', $tribunalRaw)));

// Se guarda (o actualiza) el trabajo de grado para reutilizar el título
// automáticamente en el certificado de Medio Magnético
if ($estudianteID && $programaID) {
    CertificadoModelo::GuardarTrabajoGradoModelo([
        'estudianteID' => $estudianteID,
        'programaID' => $programaID,
        'titulo' => $titulo,
        'tipoTrabajo' => 'MEDIO FISICO Y DIGITAL DEL TRABAJO DE TESIS',
        'tribunal' => $tribunalRaw,
        'calificacion' => $calificacion,
        'fechaEntrega' => $entrega,
        'horaEntrega' => $horaEntrega,
        'fechaAprobacion' => date('Y-m-d'),
        'usuarioID' => $_SESSION['IdUsuario'] ?? null,
    ]);
}

// Se guardan los datos del formulario para poder reimprimirlo (y corregirlo) desde
// Registro de documentos; con idDocumento es una reimpresión de ese registro.
CertificadoModelo::RegistrarEmisionModelo(
    'APROBACION_MONOGRAFIA', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
    $_POST, intval($_POST['idDocumento'] ?? 0)
);

/**
 * Convierte un número entero (0-100) a su literal en español, en mayúsculas.
 */
function numeroALetrasMonografia($numero)
{
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

$calificacionLiteral = numeroALetrasMonografia($calificacion);

// Apellidos y nombres van separados (cada uno con su rótulo debajo). Se toman del
// registro del estudiante; si el nombre se corrigió al reimprimir y ya no coincide,
// se toman las dos primeras palabras como apellidos.
$apellidos = '';
$nombres = '';
if ($estudianteID) {
    $stmtEst = Conexion::Conectar()->prepare("SELECT Apaterno, Amaterno, Nombre FROM estudiante WHERE EstudianteID = :id");
    $stmtEst->execute([':id' => $estudianteID]);
    $est = $stmtEst->fetch(PDO::FETCH_ASSOC);
    if ($est) {
        $apellidosBD = trim($est['Apaterno'] . ' ' . $est['Amaterno']);
        $completoBD = preg_replace('/\s+/', ' ', trim($apellidosBD . ' ' . $est['Nombre']));
        if (mb_strtoupper($completoBD, 'UTF-8') === mb_strtoupper(preg_replace('/\s+/', ' ', trim($nombre)), 'UTF-8')) {
            $apellidos = $apellidosBD;
            $nombres = trim($est['Nombre']);
        }
    }
}
if ($apellidos === '') {
    $palabras = preg_split('/\s+/', trim($nombre));
    $apellidos = implode(' ', array_slice($palabras, 0, min(2, max(1, count($palabras) - 1))));
    $nombres = implode(' ', array_slice($palabras, min(2, max(1, count($palabras) - 1))));
}

// El grado va en su propia línea ("ESPECIALIDAD"): del nombre del programa se quita
// el "ESPECIALIDAD DE" / "MAESTRÍA EN" del inicio
$programaTexto = preg_replace('/^(ESPECIALIDAD|MAESTR[IÍ]A|DIPLOMADO|DOCTORADO)\s+(DE|EN)\s+/iu', '', trim($programa));

// Fecha y hora: "26 de marzo de 2026, a horas 08:30"
$mesesCertificado = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaEntregaTexto = '';
if (!empty($entrega) && ($t = strtotime($entrega))) {
    $fechaEntregaTexto = date('j', $t) . ' de ' . $mesesCertificado[(int)date('n', $t)] . ' de ' . date('Y', $t);
    if (preg_match('/^\d{2}:\d{2}/', $horaEntrega)) {
        $fechaEntregaTexto .= ', a horas ' . substr($horaEntrega, 0, 5);
    }
}

// Tribunal: cada miembro llega como "Rol: Nombre". Presidente a la izquierda,
// secretario a la derecha y vocal al centro; el nombre se escribe tal como se
// registró y el cargo, en mayúsculas, debajo. Si hay más miembros, van debajo del
// vocal, centrados.
$espaciosTribunal = ['PRESIDENTE' => null, 'SECRETARIO' => null, 'VOCAL' => null];
$otrosTribunal = [];
foreach ($miembrosTribunal as $miembro) {
    $partes = explode(':', $miembro, 2);
    $rol = count($partes) === 2 ? trim($partes[0]) : '';
    $persona = trim(count($partes) === 2 ? $partes[1] : $partes[0]);
    $dato = ['nombre' => $persona, 'rol' => mb_strtoupper($rol, 'UTF-8')];
    $clave = null;
    foreach (array_keys($espaciosTribunal) as $posible) {
        if ($espaciosTribunal[$posible] === null && stripos($rol, $posible) === 0) {
            $clave = $posible;
            break;
        }
    }
    if ($clave) {
        $espaciosTribunal[$clave] = $dato;
    } else {
        $otrosTribunal[] = $dato;
    }
}
// Los que no dijeron su cargo ocupan los espacios libres, en orden
foreach (array_keys($espaciosTribunal) as $clave) {
    if ($espaciosTribunal[$clave] === null && $otrosTribunal) {
        $espaciosTribunal[$clave] = array_shift($otrosTribunal);
    }
}

// ===================================
// COORDENADAS (mm) - Hoja Oficio vertical (215.9 x 355.6 mm)
// Medidas sobre el formato en Word que usa Posgrado
// (CERT_APROBACION_*.docx): X_* es el centro del texto (o su inicio, si dice
// "inicio") e Y_* la parte de arriba de la línea.
// ===================================
$ALTO_LINEA = 5.2;
$X_APELLIDOS = 83.5;   $X_NOMBRES = 155;      $Y_NOMBRE = 53.2;    $Y_ROTULO_NOMBRE = 58.5;
$X_PROGRAMA = 84.7;    $Y_PROGRAMA = 64;
$X_CURSO = 50.9;       $Y_CURSO = 73;          // inicio
$X_TITULO = 21.4;      $Y_TITULO = 95.8;       $W_TITULO = 174.5;   $INTERLINEA_TITULO = 5.95; // inicio
$X_PRESIDENTE = 59.5;  $X_SECRETARIO = 155.8;  $Y_TRIBUNAL_1 = 129.1;
$X_VOCAL = 104.7;      $Y_TRIBUNAL_2 = 140;    $SEPARACION_CARGO = 5.3;
$X_ENTREGA = 27.4;     $Y_ENTREGA = 164.6;     // inicio
$X_NUMERAL = 126.2;    $Y_NUMERAL = 164.6;
$X_LITERAL = 126.4;    $Y_LITERAL = 179.4;
$X_RECIBO = 64.2;      $Y_RECIBO = 303.2;

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('P', 'mm', array(215.9, 355.6), true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificado de Aprobación Programa de Posgrado - ' . $nombre);
$pdf->SetSubject('Certificado de Aprobación Programa de Posgrado');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->setCellPaddings(0, 0, 0, 0);
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);
// Helvetica 11 un poco angosta (88 %) ocupa casi lo mismo que la Calibri 12 del
// formato en Word, así el título corta las líneas en las mismas palabras. (La
// Calibri no se incluye en el sistema: su licencia no permite distribuirla.)
$pdf->SetFont('helvetica', '', 11);
$pdf->setFontStretching(88);

// Texto centrado en una X (ancho 0 = solo lo que mide el texto)
$centrado = function ($texto, $xCentro, $y) use ($pdf, $ALTO_LINEA) {
    $ancho = $pdf->GetStringWidth($texto);
    $pdf->SetXY($xCentro - $ancho / 2, $y);
    $pdf->Cell($ancho, $ALTO_LINEA, $texto, 0, 0, 'L');
};
$desde = function ($texto, $x, $y) use ($pdf, $ALTO_LINEA) {
    $pdf->SetXY($x, $y);
    $pdf->Cell(0, $ALTO_LINEA, $texto, 0, 0, 'L');
};

// Apellidos y nombres, con sus rótulos
$centrado(mb_strtoupper($apellidos, 'UTF-8'), $X_APELLIDOS, $Y_NOMBRE);
$centrado(mb_strtoupper($nombres, 'UTF-8'), $X_NOMBRES, $Y_NOMBRE);
$centrado('(Apellidos)', $X_APELLIDOS, $Y_ROTULO_NOMBRE);
$centrado('(Nombres)', $X_NOMBRES, $Y_ROTULO_NOMBRE);

// Programa y grado académico
$centrado(mb_strtoupper($programaTexto, 'UTF-8'), $X_PROGRAMA, $Y_PROGRAMA);
$desde(mb_strtoupper($curso, 'UTF-8'), $X_CURSO, $Y_CURSO);

// Título del trabajo de grado, justificado
$pdf->setCellHeightRatio($INTERLINEA_TITULO / ($pdf->getFontSize() ?: 3.88));
// (el salto final evita que TCPDF estire también la última línea)
$pdf->MultiCell($W_TITULO, $INTERLINEA_TITULO, mb_strtoupper($titulo, 'UTF-8') . "\n", 0, 'J', false, 1, $X_TITULO, $Y_TITULO);
$pdf->setCellHeightRatio(1.25);

// Tribunal
$posiciones = [
    'PRESIDENTE' => [$X_PRESIDENTE, $Y_TRIBUNAL_1],
    'SECRETARIO' => [$X_SECRETARIO, $Y_TRIBUNAL_1],
    'VOCAL' => [$X_VOCAL, $Y_TRIBUNAL_2],
];
foreach ($posiciones as $clave => $pos) {
    $miembro = $espaciosTribunal[$clave];
    if (!$miembro) continue;
    $centrado($miembro['nombre'], $pos[0], $pos[1]);
    $centrado($miembro['rol'] !== '' ? $miembro['rol'] : ($clave === 'SECRETARIO' ? 'SECRETARIO/A' : $clave), $pos[0], $pos[1] + $SEPARACION_CARGO);
}
$yExtra = $Y_TRIBUNAL_2 + 2 * $SEPARACION_CARGO + 1;
foreach ($otrosTribunal as $miembro) {
    $centrado($miembro['nombre'], $X_VOCAL, $yExtra);
    if ($miembro['rol'] !== '') $centrado($miembro['rol'], $X_VOCAL, $yExtra + $SEPARACION_CARGO);
    $yExtra += 2 * $SEPARACION_CARGO + 1;
}

// Fecha y hora; calificación numeral y literal
$desde($fechaEntregaTexto, $X_ENTREGA, $Y_ENTREGA);
$centrado((string)intval($calificacion), $X_NUMERAL, $Y_NUMERAL);
$centrado($calificacionLiteral, $X_LITERAL, $Y_LITERAL);

// N° de recibo de caja (voucher)
$centrado(mb_strtoupper($vaucher, 'UTF-8'), $X_RECIBO, $Y_RECIBO);

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Aprobacion_Posgrado_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
