<?php
/**
 * Generar PDF de Orden de Pago desde Preregistro
 */

session_start();

// Validar sesión
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso denegado');
}

// Verificar ID de la orden de pago
$idOrdenPago = isset($_GET['idordenpago']) ? intval($_GET['idordenpago']) : 0;
if ($idOrdenPago === 0) {
    die('ID de orden de pago inválido');
}

require_once '../../modelos/conexion.modelo.php';
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

date_default_timezone_set("America/La_Paz");

try {
    $pdo = Conexion::Conectar();

    // Obtener datos del preregistro (tabla ordenpago; no depende de estudianteprograma)
    $stmt = $pdo->prepare("
        SELECT
            e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
            op.IdOrdenPago, op.NumeroOrden, op.ProgramaID, op.FechaGeneracion, op.CostoMatricula as costomatricula,
            op.MontoFinal as montoPagado, op.MontoDescuento as montoDescuento,
            op.PorcentajeDescuento as porcentajeDescuento, op.PagoCompleto as pagoCompleto,
            p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico,
            p.Costo AS CostoTotalPrograma, p.CostoMatricula, p.Version, p.NumeroTramite, p.Sede, p.FechaInicio
        FROM ordenpago op
        INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
        WHERE op.IdOrdenPago = :idordenpago
    ");
    $stmt->bindParam(':idordenpago', $idOrdenPago, PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        die('No se encontró el registro');
    }

    // Datos del estudiante
    $nombreCompleto = trim($datos['Apaterno'] . ' ' . $datos['Amaterno'] . ' ' . $datos['Nombre']);
    $ciCompleto = $datos['Ci'] . (!empty($datos['Complemento']) ? '-' . $datos['Complemento'] : '') . ' ' . $datos['Exp'];

    // Calcular montos
    $pagoCompleto = intval($datos['pagoCompleto']);
    $montoPagado = floatval($datos['montoPagado']);
    $montoDescuento = floatval($datos['montoDescuento']);
    $porcentajeDescuento = floatval($datos['porcentajeDescuento']);
    $costoMatriculaBD = floatval($datos['CostoMatricula']);
    $costoProgramaBD = floatval($datos['CostoTotalPrograma']);

    // Calcular total original
    if ($pagoCompleto == 1) {
        $costoOriginal = $costoMatriculaBD + $costoProgramaBD;
    } else {
        $costoOriginal = $costoMatriculaBD;
    }

    // Número de orden (el generado al crear la orden)
    $numeroOrden = $datos['NumeroOrden'];

    // Crear PDF
    $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

    // Configurar documento
    $pdf->SetCreator('Sistema Posgrado FCS');
    $pdf->SetAuthor('Posgrado FCS');
    $pdf->SetTitle('Orden de Pago - ' . $nombreCompleto);
    $pdf->SetSubject('Orden de Pago');

    // Quitar header y footer automáticos
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Configurar márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Agregar página
    $pdf->AddPage();

    // ========================================
    // CABECERA CON LOGOS
    // ========================================
    $logoUto = '../../extensiones/imagenespdf/logouto.png';
    $logoFcs = '../../extensiones/imagenespdf/logofcs.png';

    if (file_exists($logoUto)) {
        $pdf->Image($logoUto, 15, 10, 25);
    }

    if (file_exists($logoFcs)) {
        $pdf->Image($logoFcs, 170, 10, 25);
    }

    // Textos del encabezado
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY(45, 12);
    $pdf->Cell(120, 6, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY(45, 18);
    $pdf->Cell(120, 5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetXY(45, 23);
    $pdf->Cell(120, 5, 'POSGRADO - ODONTOLOGÍA', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(45, 28);
    $pdf->Cell(120, 4, 'Av. Del Moreno Edificio San Agustín II', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(45, 32);
    $pdf->Cell(120, 4, 'Teléfonos: 52-37317 - Fax: 52-47110', 0, 1, 'C');

    // Línea separadora
    $pdf->SetDrawColor(102, 126, 234);
    $pdf->SetLineWidth(0.8);
    $pdf->Line(15, 42, 195, 42);

    // ========================================
    // TÍTULO ORDEN DE PAGO
    // ========================================
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->SetXY(15, 48);
    $pdf->Cell(0, 8, 'ORDEN DE PAGO', 0, 1, 'C');

    // Número de orden y fecha
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(15, 56);
    $pdf->Cell(0, 6, 'N° ' . $numeroOrden, 0, 1, 'C');

    $pdf->SetXY(15, 61);
    $pdf->Cell(0, 6, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // ========================================
    // DATOS DEL ESTUDIANTE
    // ========================================
    $y = 72;

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->SetXY(15, $y);
    $pdf->Cell(0, 8, 'DATOS DEL ESTUDIANTE', 0, 1, 'L');

    $y += 12;

    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY(15, $y);
    $pdf->Cell(50, 7, 'Nombre Completo:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(130, 7, $nombreCompleto, 1, 1, 'L', true);

    $y += 7;

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY(15, $y);
    $pdf->Cell(50, 7, 'C.I.:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 7, $ciCompleto, 1, 0, 'L', true);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->Cell(30, 7, 'Correo:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(40, 7, $datos['Correo'] ?: '-', 1, 1, 'L', true);

    $y += 7;

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY(15, $y);
    $pdf->Cell(50, 7, 'Programa:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(130, 7, $datos['NombrePrograma'], 1, 1, 'L', true);

    $y += 7;

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY(15, $y);
    $pdf->Cell(50, 7, 'Grado Académico:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(60, 7, $datos['GradoAcademico'], 1, 0, 'L', true);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->Cell(30, 7, 'Código:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(40, 7, $datos['CodigoPrograma'], 1, 1, 'L', true);

    // ========================================
    // DETALLE DE PAGO
    // ========================================
    $y += 15;

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->SetXY(15, $y);
    $pdf->Cell(0, 8, 'DETALLE DEL MONTO A PAGAR', 0, 1, 'L');

    $y += 12;

    $pdf->SetFillColor(231, 243, 255);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.2);

    if ($pagoCompleto == 1) {
        // Desglose para pago completo

        // 1. Matrícula
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY(15, $y);
        $pdf->Cell(130, 8, '1. MATRÍCULA DEL PROGRAMA:', 1, 0, 'L', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 8, 'Bs. ' . number_format($costoMatriculaBD, 2), 1, 1, 'R', true);

        $y += 8;

        // 2. Programa
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY(15, $y);
        $pdf->Cell(130, 8, '2. COSTO DEL PROGRAMA:', 1, 0, 'L', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 8, 'Bs. ' . number_format($costoProgramaBD, 2), 1, 1, 'R', true);

        $y += 8;

        // 3. Subtotal
        $pdf->SetFillColor(255, 243, 205);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY(15, $y);
        $pdf->Cell(130, 8, 'SUBTOTAL (MATRÍCULA + PROGRAMA):', 1, 0, 'L', true);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 8, 'Bs. ' . number_format($costoOriginal, 2), 1, 1, 'R', true);

        $y += 8;

        // 4. Descuento (si existe)
        if ($montoDescuento > 0) {
            $pdf->SetFillColor(212, 237, 218);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(40, 167, 69);
            $pdf->SetXY(15, $y);
            $pdf->Cell(130, 8, 'DESCUENTO APLICADO (' . number_format($porcentajeDescuento, 1) . '%):', 1, 0, 'L', true);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(50, 8, '- Bs. ' . number_format($montoDescuento, 2), 1, 1, 'R', true);

            $y += 8;
        }

    } else {
        // Solo matrícula
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY(15, $y);
        $pdf->Cell(130, 8, 'MATRÍCULA DEL PROGRAMA:', 1, 0, 'L', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(50, 8, 'Bs. ' . number_format($montoPagado, 2), 1, 1, 'R', true);

        $y += 8;
    }

    // Línea de separación más gruesa
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, $y, 195, $y);
    $y += 0.5;

    // TOTAL A PAGAR
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(15, $y);
    $pdf->Cell(130, 10, 'TOTAL A PAGAR:', 1, 0, 'L', true);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Bs. ' . number_format($montoPagado, 2), 1, 1, 'R', true);

    // ========================================
    // INSTRUCCIONES DE PAGO
    // ========================================
    $y += 15;

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->SetXY(15, $y);
    $pdf->Cell(0, 8, 'INSTRUCCIONES DE PAGO', 0, 1, 'L');

    $y += 10;

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(60, 60, 60);

    $instrucciones = "1. Realice el depósito o transferencia bancaria por el monto indicado.\n";
    $instrucciones .= "2. La cuenta bancaria y los datos serán proporcionados en ventanilla.\n";
    $instrucciones .= "3. Presente esta orden de pago junto con el comprobante de pago.\n";
    $instrucciones .= "4. El registro quedará confirmado una vez verificado el pago.";

    $pdf->SetXY(15, $y);
    $pdf->MultiCell(180, 6, $instrucciones, 0, 'L');

    // ========================================
    // FOOTER
    // ========================================
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetY(-25);
    $pdf->Cell(0, 5, 'Este documento es válido como orden de pago', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Sistema de Gestión Académica - Posgrado FCS', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // ========================================
    // SALIDA DEL PDF
    // ========================================
    $pdf->Output('Orden-Pago-' . $numeroOrden . '.pdf', 'I');

} catch (PDOException $e) {
    die('Error al generar el PDF: ' . $e->getMessage());
}
?>
