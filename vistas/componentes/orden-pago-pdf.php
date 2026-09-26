<?php
/**
 * Imprimir una Orden de Pago ya generada, desde "Mis Órdenes de Pago" (Estudiante).
 * Usa el mismo formato oficial UTO (doble copia ORIGINAL/COPIA) que genera el
 * administrador: arma los mismos datos desde la base y llama al render compartido
 * en orden-pago-pdf-render.php, para que ambos impriman el mismo documento.
 */

session_start();
date_default_timezone_set('America/La_Paz');

// Validar sesión
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso denegado');
}

// Verificar ID de la orden de pago
$idOrdenPago = isset($_GET['idordenpago']) ? intval($_GET['idordenpago']) : 0;
if ($idOrdenPago === 0) {
    die('ID de orden de pago inválido');
}

require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
require_once __DIR__ . '/orden-pago-pdf-render.php';

try {
    $pdo = Conexion::Conectar();

    $stmt = $pdo->prepare("
        SELECT
            e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
            op.IdOrdenPago, op.NumeroOrden, op.ProgramaID, op.idinscripcion, op.FechaGeneracion,
            op.MontoTotal, op.MontoFinal, op.MontoDescuento, op.PorcentajeDescuento, op.PagoCompleto,
            op.Observaciones, op.NombreFactura, op.NitCiFactura, op.Firma, op.ProgramaCuentaID,
            p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Version, p.NumeroTramite
        FROM ordenpago op
        INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
        WHERE op.IdOrdenPago = :idordenpago
    ");
    $stmt->bindParam(':idordenpago', $idOrdenPago, PDO::PARAM_INT);
    $stmt->execute();
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        die('No se encontró el registro');
    }

    // Un Estudiante solo puede imprimir sus propias órdenes de pago (nunca las de otro).
    if (($_SESSION['Tipo'] ?? '') === 'EST') {
        $estudianteSesionID = (int)($_SESSION['EstudianteID'] ?? 0);
        if ($estudianteSesionID === 0 || $estudianteSesionID !== (int)$orden['EstudianteID']) {
            die('Acceso denegado: esta orden de pago no le corresponde');
        }
    }

    // Las órdenes de cuota del programa se distinguen por esta marca (ver
    // registrarOrdenPagoCuotaPrograma en ajax/ordenpago.ajax.php); el resto son
    // órdenes de matrícula/preregistro.
    $esCuota = $orden['Observaciones'] === 'CUOTA PROGRAMA';
    // Órdenes de clínica (ver PagoClinicaModelo): monto libre en MontoTotal y cuenta CLINICA.
    $esClinica = $orden['Observaciones'] === 'CLINICA';

    $nombreCompleto = trim($orden['Apaterno'] . ' ' . $orden['Amaterno'] . ' ' . $orden['Nombre']);
    $ciCompleto = trim($orden['Ci'] . (!empty($orden['Complemento']) ? '-' . $orden['Complemento'] : '') . ' ' . $orden['Exp']);

    // Monto: las órdenes de matrícula/preregistro guardan el monto en MontoFinal;
    // las de cuota del programa lo guardan en MontoTotal (MontoFinal les queda en 0).
    $monto = ($esCuota || $esClinica) ? (float)$orden['MontoTotal'] : (float)$orden['MontoFinal'];

    // Cuenta bancaria a la que se debe depositar: la elegida al generar la orden
    // (ProgramaCuentaID) o, si no hay, la cuenta principal del programa para el tipo
    // que corresponda (CUOTAS o MATRICULA); por último, el N° de trámite del programa.
    $tipoCuenta = $esClinica ? 'CLINICA' : ($esCuota ? 'CUOTAS' : 'MATRICULA');
    $numeroCuentaDeposito = null;

    if (!empty($orden['ProgramaCuentaID'])) {
        $stmtCuenta = $pdo->prepare("SELECT NumeroCuenta FROM programacuenta WHERE ProgramaCuentaID = :id");
        $stmtCuenta->bindParam(':id', $orden['ProgramaCuentaID'], PDO::PARAM_INT);
        $stmtCuenta->execute();
        $fila = $stmtCuenta->fetch(PDO::FETCH_ASSOC);
        $numeroCuentaDeposito = $fila ? $fila['NumeroCuenta'] : null;
    }

    if (empty($numeroCuentaDeposito)) {
        $stmtCuentaPrincipal = $pdo->prepare(
            "SELECT NumeroCuenta FROM programacuenta
             WHERE ProgramaID = :programaID AND TipoCuenta = :tipoCuenta AND Principal = 1 AND Estado = 1
             LIMIT 1"
        );
        $stmtCuentaPrincipal->bindParam(':programaID', $orden['ProgramaID'], PDO::PARAM_INT);
        $stmtCuentaPrincipal->bindParam(':tipoCuenta', $tipoCuenta, PDO::PARAM_STR);
        $stmtCuentaPrincipal->execute();
        $fila = $stmtCuentaPrincipal->fetch(PDO::FETCH_ASSOC);
        $numeroCuentaDeposito = $fila ? $fila['NumeroCuenta'] : null;
    }

    if (empty($numeroCuentaDeposito)) {
        $numeroCuentaDeposito = !empty($orden['NumeroTramite']) ? $orden['NumeroTramite'] : '10000006050938';
    }

    if ($esClinica) {
        $programaTexto = $orden['NombrePrograma'] . "\nPago de clínica";
        $version = !empty($orden['Version']) ? $orden['Version'] : 'V-1';
    } elseif ($esCuota) {
        // Número de cuota al momento en que se generó esta orden: las cuotas ya
        // pagadas (PAGADO) antes de esa fecha, +1. No queda guardado en la propia
        // orden, se reconstruye igual que lo hizo el administrador al imprimirla.
        $numeroCuota = 1;
        if (!empty($orden['idinscripcion'])) {
            $stmtCuota = $pdo->prepare(
                "SELECT COUNT(*) as total FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion AND Estado = 'PAGADO' AND FechaRegistro < :fechaGeneracion"
            );
            $stmtCuota->bindParam(':idinscripcion', $orden['idinscripcion'], PDO::PARAM_INT);
            $stmtCuota->bindParam(':fechaGeneracion', $orden['FechaGeneracion'], PDO::PARAM_STR);
            $stmtCuota->execute();
            $numeroCuota = (int)$stmtCuota->fetch(PDO::FETCH_ASSOC)['total'] + 1;
        }

        $programaTexto = $orden['NombrePrograma'] . "\n" . etiquetaCuotaOrdenPago($numeroCuota);
        $version = !empty($orden['Version']) ? $orden['Version'] : 'V-1';
    } else {
        // No se imprime la versión "V-1": es la versión base/por defecto y no aporta
        // información distinta al nombre del programa. Solo se muestran versiones
        // reales (V-2, V-3, etc.).
        $mostrarVersion = !empty($orden['Version']) && strtoupper($orden['Version']) !== 'V-1';
        $programaTexto = 'PROGRAMA DE POSGRADO EN LA ' . mb_strtoupper($orden['GradoAcademico'], 'UTF-8') .
            ' EN ' . mb_strtoupper($orden['NombrePrograma'], 'UTF-8') .
            ($mostrarVersion ? ' "' . mb_strtoupper($orden['Version'], 'UTF-8') . '"' : '') .
            ' ' . ((int)$orden['PagoCompleto'] === 1 ? 'PROGRAMA COMPLETO' : 'MATRICULA');
        $version = $orden['Version'] ?: '';
    }

    $datos = [
        'apaterno' => $orden['Apaterno'],
        'amaterno' => $orden['Amaterno'],
        'nombres' => $orden['Nombre'],
        'correo' => $orden['Correo'],
        'ci' => $ciCompleto,
        'celular' => $orden['Celular'],
        'programa' => $programaTexto,
        'montoNumeral' => 'Bs. ' . number_format($monto, 2),
        'montoLiteral' => numeroALetrasOrdenPagoOficial($monto),
        'version' => $version,
        'numeroTramite' => $numeroCuentaDeposito,
        'nombreFactura' => !empty($orden['NombreFactura']) ? $orden['NombreFactura'] : $nombreCompleto,
        'nitCiFactura' => !empty($orden['NitCiFactura']) ? $orden['NitCiFactura'] : $ciCompleto,
        // El N° de cuenta de "APORTES EXTRAORDINARIOS" (Banco Unión) es la cuenta oficial
        // fija de la UTO: nunca cambia según el programa o la cuenta que se haya elegido
        // para "Cuenta auxiliar". Es el mismo valor fijo que usa el administrador al
        // generar la orden (ver generar-orden-pago-pdf.php).
        'numeroCuentaDeposito' => '10000006050938',
        'firma' => $orden['Firma'],
    ];

    generarPdfOrdenPagoOficial($datos);
} catch (PDOException $e) {
    die('Error al generar el PDF: ' . $e->getMessage());
}
