<?php
/**
 * AJAX de Autoridades (Director y Coordinador de Posgrado).
 * Solo el ADM puede verlas y cambiarlas.
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../controladores/autoridad.controlador.php';

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['accion'] ?? '') {
    case 'listar':
        echo json_encode(['success' => true, 'autoridades' => AutoridadModelo::ListarModelo()]);
        break;

    case 'guardar':
        echo json_encode(AutoridadControlador::GuardarControlador(
            $_POST['autoridades'] ?? null,
            AutoridadControlador::UsuarioSesion()
        ));
        break;

    // Parámetros académicos (tabla configuracion)
    case 'obtenerParametros':
        require_once __DIR__ . '/../modelos/configuracion.modelo.php';
        echo json_encode([
            'success' => true,
            'notaAprobacion' => ConfiguracionModelo::NotaAprobacion(),
            'detalle' => ConfiguracionModelo::Detalle('NOTA_APROBACION'),
        ]);
        break;

    case 'guardarNotaAprobacion':
        require_once __DIR__ . '/../modelos/configuracion.modelo.php';
        $nota = trim($_POST['notaAprobacion'] ?? '');
        if (!ctype_digit($nota) || (int)$nota < 1 || (int)$nota > 100) {
            echo json_encode(['success' => false, 'mensaje' => 'La nota mínima debe ser un número entero entre 1 y 100']);
            break;
        }
        $ok = ConfiguracionModelo::Guardar('NOTA_APROBACION', (int)$nota, AutoridadControlador::UsuarioSesion());
        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Nota mínima de aprobación actualizada a ' . (int)$nota : 'No se pudo guardar',
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
