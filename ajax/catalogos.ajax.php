<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX de la pantalla "Universidades y Profesiones" (vistas/componentes/catalogos.php).
 * Corregir, unir duplicados y eliminar registros de esos catálogos: solo el ADM.
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../modelos/catalogos.modelo.php';

header('Content-Type: application/json; charset=utf-8');

$accion = $_POST['accion'] ?? '';
$catalogo = $_POST['catalogo'] ?? '';

if (!isset(CatalogosModelo::CATALOGOS[$catalogo])) {
    echo json_encode(['success' => false, 'mensaje' => 'Catálogo no válido']);
    exit;
}

switch ($accion) {
    case 'listar':
        echo json_encode(['success' => true, 'registros' => CatalogosModelo::ListarModelo($catalogo)]);
        break;

    case 'editar':
        echo json_encode(CatalogosModelo::EditarModelo(
            $catalogo,
            (int) ($_POST['id'] ?? 0),
            $_POST['nombre'] ?? '',
            $_POST['tipo'] ?? null,
            $_POST['estado'] ?? 1
        ));
        break;

    case 'unir':
        echo json_encode(CatalogosModelo::UnirModelo($catalogo, (int) ($_POST['id'] ?? 0), (int) ($_POST['destinoId'] ?? 0)));
        break;

    case 'eliminar':
        echo json_encode(CatalogosModelo::EliminarModelo($catalogo, (int) ($_POST['id'] ?? 0)));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
