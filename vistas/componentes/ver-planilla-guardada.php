<?php
/**
 * Abre una planilla de calificaciones ya generada (pdfs_generados/) tal como
 * salió, para volver a imprimirla. La lista la arma "Reporte de Notas" →
 * botón "Generadas" de cada módulo. Solo el personal (ADM, SEC, AUX).
 * La carpeta pdfs_generados/ no se puede abrir directo desde el navegador
 * (tiene su .htaccess): los PDF se entregan solo por aquí, con sesión.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Validar']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    http_response_code(403);
    exit('No tiene permisos para ver esta planilla.');
}

require_once __DIR__ . '/../../modelos/planillaguardada.modelo.php';

$archivo = (string)($_GET['archivo'] ?? '');
$ruta = PlanillaGuardadaModelo::Ruta($archivo);
if ($ruta === '') {
    http_response_code(404);
    exit('La planilla no existe o ya fue eliminada.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . rawurlencode($archivo) . '"');
header('Content-Length: ' . filesize($ruta));
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($ruta);
exit;
