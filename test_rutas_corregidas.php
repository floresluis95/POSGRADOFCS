<?php
session_start();
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1234567'; // Usa un CI real de tu BD

$_POST['accion'] = 'obtenerDocente';

// Capturar output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivar para no contaminar JSON

include 'ajax/calificacion.ajax.php';

$output = ob_get_clean();

header('Content-Type: application/json; charset=utf-8');
echo $output;
?>
