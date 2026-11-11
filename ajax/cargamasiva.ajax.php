<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX de la Carga masiva desde Excel (solo ADM; ver modelos/cargamasiva.modelo.php):
 *  - plantilla: datos para armar el Excel de un programa (módulos y catálogos).
 *  - validar:   revisa las filas leídas del Excel, sin guardar nada.
 *  - importar:  registra las filas válidas (cada una por separado).
 */
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../modelos/cargamasiva.modelo.php';

header('Content-Type: application/json; charset=utf-8');

$accion = $_POST['accion'] ?? '';
switch ($accion) {
    case 'plantilla':
        $datos = CargaMasivaModelo::DatosPlantillaModelo((int) ($_POST['programaID'] ?? 0));
        if (!$datos) {
            echo json_encode(['success' => false, 'mensaje' => 'Elija un programa']);
        } elseif (!$datos['modulos']) {
            echo json_encode(['success' => false, 'mensaje' => 'El programa no tiene módulos. Créelos primero en Módulos: cada módulo es una columna de notas del Excel']);
        } else {
            echo json_encode(['success' => true] + $datos);
        }
        break;

    case 'validar':
    case 'importar':
        // Las filas llegan como JSON (pueden ser cientos: no caben en max_input_vars como campos sueltos)
        $filas = json_decode((string) ($_POST['filas'] ?? ''), true);
        if (!is_array($filas)) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudieron leer las filas del Excel']);
            break;
        }
        if ($accion === 'importar') {
            @set_time_limit(300);
        }
        echo json_encode(CargaMasivaModelo::Analizar(
            (int) ($_POST['programaID'] ?? 0),
            $filas,
            $accion === 'importar',
            (int) ($_SESSION['IdUsuario'] ?? 0)
        ));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
}
