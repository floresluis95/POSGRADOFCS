<?php
/**
 * Generar Orden de Pago en PDF (formato oficial UTO - Coordinación de Posgrado)
 * Punto de entrada usado por el ADMINISTRADOR justo al generar la orden (vía POST).
 * El PDF en sí se arma en orden-pago-pdf-render.php, compartido con la vista del
 * estudiante (orden-pago-pdf.php), para que ambos impriman el mismo documento.
 */

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, las
// fechas impresas en el PDF no corresponden a la hora de Bolivia.
date_default_timezone_set('America/La_Paz');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
// Solo el personal genera órdenes de pago
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar'] || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    error_log("PDF: Acceso denegado - sesión no válida o sin permiso");
    die('Acceso denegado - Por favor inicie sesión nuevamente');
}

// Verificar que se recibieron los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("PDF: Método no permitido - Se esperaba POST");
    die('Método no permitido - Se esperaba POST');
}

require_once __DIR__ . '/orden-pago-pdf-render.php';

// Recibir datos del POST
$datos = [
    'apaterno' => $_POST['apaterno'] ?? '',
    'amaterno' => $_POST['amaterno'] ?? '',
    'nombres' => $_POST['nombres'] ?? '',
    'correo' => $_POST['correo'] ?? '',
    'ci' => $_POST['ci'] ?? '',
    'celular' => $_POST['celular'] ?? '',
    'programa' => $_POST['programa'] ?? '',
    'montoNumeral' => $_POST['montoNumeral'] ?? '',
    'montoLiteral' => $_POST['montoLiteral'] ?? '',
    'version' => $_POST['version'] ?? '',
    'numeroTramite' => $_POST['numeroTramite'] ?? '',
    'nombreFactura' => $_POST['nombreFactura'] ?? '',
    'nitCiFactura' => $_POST['nitCiFactura'] ?? '',
    // Cuenta bancaria a la que el estudiante debe depositar. Si no se selecciona una del
    // catálogo (ej. órdenes generadas desde Pre-Registro, que aún no tienen este selector),
    // se mantiene la cuenta por defecto que ya se usaba antes.
    'numeroCuentaDeposito' => !empty($_POST['numeroCuentaDeposito']) ? $_POST['numeroCuentaDeposito'] : '10000006050938',
    // Nombre del responsable de la firma (coordinador/a), elegido del catálogo de firmantes;
    // se imprime junto a la línea de firma. No confundir con el usuario que generó la orden
    // en el sistema (ese dato no se imprime, solo queda registrado para auditoría).
    'firma' => $_POST['firma'] ?? '',
];

generarPdfOrdenPagoOficial($datos);
