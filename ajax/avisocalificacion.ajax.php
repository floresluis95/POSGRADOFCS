<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX de avisos de calificaciones pendientes:
 *  - pendientes / notificar: el Administrador ve los módulos que faltan calificar y
 *    avisa a sus docentes (Registrar Notas).
 *  - entendido: el docente confirma que vio el aviso (panel y Reporte de calificaciones).
 */
require_once __DIR__ . '/validar-sesion.php';

$accion = $_POST['accion'] ?? '';
ExigirSesionAjax($accion === 'entendido' ? ['DOC'] : ['ADM']);

require_once __DIR__ . '/../modelos/avisocalificacion.modelo.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php'; // nombre de quien envía

header('Content-Type: application/json; charset=utf-8');

switch ($accion) {
    case 'pendientes':
        echo json_encode(['success' => true, 'modulos' => AvisoCalificacionModelo::ModulosPendientesModelo()]);
        break;

    case 'notificar':
        $mensaje = trim(preg_replace('/\s+/', ' ', $_POST['mensaje'] ?? ''));
        $modulos = array_filter(array_map('intval', (array)($_POST['modulos'] ?? [])));
        if (mb_strlen($mensaje) < 10) {
            echo json_encode(['success' => false, 'mensaje' => 'Escriba el mensaje para los docentes']);
            break;
        }
        if (!$modulos) {
            echo json_encode(['success' => false, 'mensaje' => 'Marque al menos un módulo']);
            break;
        }
        $resultado = AvisoCalificacionModelo::NotificarModelo($modulos, mb_substr($mensaje, 0, 600), OrdenPagoModelos::ResponsableSesion());
        if ($resultado === 'sin_tabla') {
            echo json_encode(['success' => false, 'mensaje' => 'Falta crear la tabla de avisos (mantenimiento/crear_avisocalificacion.sql)']);
        } elseif ($resultado === 'error') {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo enviar el aviso']);
        } elseif ($resultado === 0) {
            echo json_encode(['success' => false, 'mensaje' => 'Los módulos marcados ya no tienen calificaciones pendientes']);
        } else {
            echo json_encode(['success' => true, 'enviados' => $resultado]);
        }
        break;

    case 'entendido':
        echo json_encode(['success' => AvisoCalificacionModelo::MarcarLeidosModelo($_SESSION['DocenteID'] ?? 0)]);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
}
