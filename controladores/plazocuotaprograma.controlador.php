<?php
/**
 * Controlador del Plazo de Cuota por Programa (Calendario Académico).
 * Permisos:
 *  - ADM y SEC: pueden crear/editar/anular plazos y listar el panel.
 *  - EST: solo consulta el aviso propio (si tiene cuota vencida).
 */

require_once __DIR__ . '/../modelos/plazocuotaprograma.modelo.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php';
require_once __DIR__ . '/../modelos/calendarioevento.modelo.php';
require_once __DIR__ . '/../modelos/modulopagos_core.php';

class PlazoCuotaProgramaControlador
{
    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC'], true);
    }

    /**
     * Listar plazos registrados (panel de administración).
     */
    public function ListarPlazosAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para ver esta información']);
            exit();
        }

        echo json_encode(['success' => true, 'data' => PlazoCuotaProgramaModelo::ListarPlazosModelo()]);
        exit();
    }

    /**
     * Registrar un nuevo plazo de cuota para un programa.
     */
    public function RegistrarPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para registrar plazos']);
            exit();
        }

        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaPlazo = trim($_POST['fechaPlazo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($programaID) || empty($fechaPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe indicar el programa y la fecha plazo']);
            exit();
        }

        $idPlazo = PlazoCuotaProgramaModelo::RegistrarPlazoModelo([
            'ProgramaID' => $programaID,
            'FechaPlazo' => $fechaPlazo,
            'Descripcion' => $descripcion,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idPlazo === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar el plazo']);
            exit();
        }

        echo json_encode(['success' => true, 'idPlazo' => $idPlazo, 'mensaje' => 'Plazo de cuota registrado correctamente']);
        exit();
    }

    /**
     * Actualizar un plazo existente.
     */
    public function ActualizarPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para editar plazos']);
            exit();
        }

        $idPlazo = isset($_POST['idPlazo']) ? (int)$_POST['idPlazo'] : 0;
        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaPlazo = trim($_POST['fechaPlazo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($idPlazo) || empty($programaID) || empty($fechaPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe indicar el programa y la fecha plazo']);
            exit();
        }

        $ok = PlazoCuotaProgramaModelo::ActualizarPlazoModelo([
            'IdPlazo' => $idPlazo,
            'ProgramaID' => $programaID,
            'FechaPlazo' => $fechaPlazo,
            'Descripcion' => $descripcion,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Plazo actualizado correctamente' : 'No se pudo actualizar el plazo'
        ]);
        exit();
    }

    /**
     * Anular (baja lógica) un plazo de cuota.
     */
    public function AnularPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para eliminar plazos']);
            exit();
        }

        $idPlazo = isset($_POST['idPlazo']) ? (int)$_POST['idPlazo'] : 0;
        if (empty($idPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'El plazo no existe']);
            exit();
        }

        $ok = PlazoCuotaProgramaModelo::AnularPlazoModelo($idPlazo);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Plazo eliminado correctamente' : 'No se pudo eliminar el plazo'
        ]);
        exit();
    }

    /**
     * Avisos de pago del Estudiante logueado, para la burbuja emergente que se
     * muestra en cada página (aviso-cuota-estudiante.js). Dos tipos:
     *  - PERSONAL: sus propias órdenes de cuota pendientes con fecha límite.
     *  - GENERAL: el plazo de cuota de cada programa donde está inscrito (el
     *    próximo o, si ya pasaron todos, el último), aunque aún no tenga una
     *    orden generada. No se muestra si ya pagó el programa completo.
     * 'bloqueado' indica si ya no puede ver calificaciones (plazo vencido con
     * orden pendiente: PlazoCuotaProgramaModelo::TieneBloqueoProgramaModelo).
     */
    public function AvisoEstudianteAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        $tipo = $_SESSION['Tipo'] ?? '';
        $estudianteID = (int)($_SESSION['EstudianteID'] ?? 0);

        if ($tipo !== 'EST' || $estudianteID <= 0) {
            echo json_encode(['success' => true, 'hayAviso' => false, 'avisos' => [], 'bloqueado' => false]);
            exit();
        }

        // El php.ini del servidor tiene otra zona horaria: los días se cuentan en hora de Bolivia
        date_default_timezone_set('America/La_Paz');
        $hoy = new DateTime('today');
        $diasDesdeHoy = function ($fecha) use ($hoy) {
            // Positivo = días de atraso; negativo = días que faltan
            $diff = $hoy->diff(new DateTime($fecha));
            return $diff->invert ? $diff->days : -$diff->days;
        };

        $avisos = [];

        // Personales: órdenes de cuota pendientes del propio estudiante
        foreach (OrdenPagoModelos::ObtenerVencimientosPorRangoModelo(null, null, null, null, $estudianteID) as $v) {
            $avisos[] = [
                'Tipo' => 'PERSONAL',
                'ProgramaID' => (int)$v['ProgramaID'],
                'NombrePrograma' => $v['NombrePrograma'],
                'Fecha' => $v['FechaVencimiento'],
                'DiasVencido' => $diasDesdeHoy($v['FechaVencimiento']),
                'Monto' => (float)$v['MontoTotal'],
                'NumeroOrden' => $v['NumeroOrden'],
            ];
        }

        // Generales: plazo del programa (solo programas inscritos y aún no pagados por completo)
        $inscripciones = CalendarioEventoModelo::ObtenerInscripcionesDeEstudianteModelo($estudianteID);
        $plazosPorPrograma = [];
        foreach (PlazoCuotaProgramaModelo::ObtenerPlazosParaCalendarioModelo(null, null, null, null, array_keys($inscripciones)) as $pl) {
            $plazosPorPrograma[(int)$pl['ProgramaID']][] = $pl; // vienen ordenados por fecha
        }
        $hoyTexto = $hoy->format('Y-m-d');
        foreach ($plazosPorPrograma as $programaID => $plazos) {
            $resumen = PagoModuloModelo::ObtenerResumenPagoProgramaModelo($inscripciones[$programaID], $programaID);
            if (!empty($resumen['completado'])) {
                continue;
            }
            $elegido = end($plazos);
            foreach ($plazos as $pl) {
                if ($pl['FechaPlazo'] >= $hoyTexto) {
                    $elegido = $pl;
                    break;
                }
            }
            $avisos[] = [
                'Tipo' => 'GENERAL',
                'ProgramaID' => $programaID,
                'NombrePrograma' => $elegido['NombrePrograma'],
                'Fecha' => $elegido['FechaPlazo'],
                'DiasVencido' => $diasDesdeHoy($elegido['FechaPlazo']),
                'Descripcion' => $elegido['Descripcion'],
                'SaldoPendiente' => (float)($resumen['saldoPendiente'] ?? 0),
            ];
        }

        $bloqueado = count(PlazoCuotaProgramaModelo::ObtenerAvisoEstudianteModelo($estudianteID)) > 0;

        echo json_encode([
            'success' => true,
            'hayAviso' => count($avisos) > 0,
            'avisos' => $avisos,
            'bloqueado' => $bloqueado,
        ]);
        exit();
    }
}
