<?php
/**
 * Test del controlador de orden de pago
 */

// Simular sesión
session_start();
$_SESSION['Validar'] = true;
$_SESSION['UsuarioID'] = 1;

// Datos de prueba
$_POST['registrarOrdenPago'] = true;
$_POST['idcliente'] = 1; // Ajusta con un ID real
$_POST['programa'] = 1; // Ajusta con un ID real
$_POST['montoAPagar'] = 250.50;
$_POST['fechaInscripcion'] = date('Y-m-d');
$_POST['nombreFactura'] = 'JUAN PEREZ';
$_POST['nitCiFactura'] = '12345678';
$_POST['responsable'] = 'Maria Gonzalez';
$_POST['firma'] = 'Firma digital';
$_POST['pagoCompleto'] = 0;
$_POST['porcentajeDescuento'] = 0;
$_POST['montoDescuento'] = 0;

echo "<h1>Test Controlador Orden de Pago</h1>";
echo "<pre>";
echo "POST Data:\n";
print_r($_POST);
echo "</pre>";

// Incluir el controlador
require_once 'controladores/procesarpreinscripcion.php';
?>
