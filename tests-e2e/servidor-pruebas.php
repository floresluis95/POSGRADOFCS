<?php
/**
 * Enrutador para el servidor embebido de PHP, SOLO para las pruebas del proceso completo
 * (tests-e2e/proceso/). Reemplaza lo que hace el .htaccess con Apache:
 *  - core/ en el include_path (los modelos hacen require_once 'conexion.modelo.php')
 *  - "/pagina" => index.php?action=pagina
 *  - los .sql no se sirven
 * Lo arranca Playwright (playwright.config.js) con POSGRADO_BD=proyecto_pruebas,
 * así las pruebas que crean estudiantes, órdenes y pagos nunca tocan la base real.
 */
if (getenv('POSGRADO_BD') !== 'proyecto_pruebas') {
    http_response_code(500);
    exit('El servidor de pruebas solo funciona con POSGRADO_BD=proyecto_pruebas');
}

$raiz = realpath(__DIR__ . '/..');
set_include_path('.' . PATH_SEPARATOR . $raiz . DIRECTORY_SEPARATOR . 'core');
chdir($raiz);

$ruta = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (preg_match('/\.sql$/i', $ruta)) {
    http_response_code(403);
    exit;
}

if ($ruta !== '/' && is_file($raiz . $ruta)) {
    // Scripts PHP (ajax, PDFs...): se ejecutan aquí, desde su carpeta, con el include_path de arriba
    if (preg_match('/\.php$/i', $ruta)) {
        $archivo = realpath($raiz . $ruta);
        $_SERVER['SCRIPT_FILENAME'] = $archivo;
        $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $ruta;
        chdir(dirname($archivo));
        require $archivo;
        return true;
    }
    // Archivos estáticos (css, js, imágenes): los sirve el propio servidor
    return false;
}

// Igual que: RewriteRule ^([-a-zA-Z0-9]+)$ index.php?action=$1
if (preg_match('#^/([-a-zA-Z0-9]+)$#', $ruta, $m)) {
    $_GET['action'] = $m[1];
}
require $raiz . '/index.php';
