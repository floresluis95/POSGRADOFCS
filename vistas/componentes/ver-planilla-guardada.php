<?php
/**
 * Abre una planilla de calificaciones ya generada (pdfs_generados/) tal como
 * salió, para volver a imprimirla. La lista la arma el botón "Generadas" de
 * cada módulo (Reporte de Notas y Reporte de calificaciones del docente).
 * ADM, SEC y AUX ven todas; el docente solo las de sus módulos.
 * La carpeta pdfs_generados/ no se puede abrir directo desde el navegador
 * (tiene su .htaccess): los PDF se entregan solo por aquí, con sesión.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Validar'])) {
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
if (!PlanillaGuardadaModelo::PuedeVerArchivo($archivo)) {
    http_response_code(403);
    exit('No tiene permisos para ver esta planilla.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . rawurlencode($archivo) . '"');
header('Content-Length: ' . filesize($ruta));
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($ruta);
exit;
