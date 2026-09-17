<?php
/**
 * Controlador del Calendario Académico (clases, actividades y clínicas)
 * Permisos:
 *  - ADM y SEC: pueden crear/editar/anular cualquier evento.
 *  - DOC: solo puede crear eventos a su propio nombre, y editar/anular
 *         únicamente los eventos donde él es el docente asignado.
 *  - EST: solo lectura (no tiene acceso a ninguno de los métodos de escritura).
 */

require_once __DIR__ . '/../modelos/calendarioevento.modelo.php';
require_once __DIR__ . '/../modelos/modulo.modelo.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php';
require_once __DIR__ . '/../modelos/plazocuotaprograma.modelo.php';

class CalendarioEventoControlador
{
    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC', 'DOC'], true);
    }

    /**
     * Listar eventos en formato compatible con FullCalendar (JSON).
     */
    public function ListarEventosAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        $filtros = [
            'programaID' => isset($_POST['programaID']) && $_POST['programaID'] !== '' ? (int)$_POST['programaID'] : null,
            'sede'       => isset($_POST['sede']) && $_POST['sede'] !== '' ? trim($_POST['sede']) : null,
            'docenteID'  => isset($_POST['docenteID']) && $_POST['docenteID'] !== '' ? (int)$_POST['docenteID'] : null,
            'desde'      => isset($_POST['desde']) && $_POST['desde'] !== '' ? trim($_POST['desde']) : null,
            'hasta'      => isset($_POST['hasta']) && $_POST['hasta'] !== '' ? trim($_POST['hasta']) : null,
        ];

        $eventos = CalendarioEventoModelo::ListarEventosModelo($filtros);

        $coloresPorTipo = [
            'CLASE'     => '#2a5298',
            'ACTIVIDAD' => '#159895',
            'CLINICA'   => '#6b2737',
        ];

        $puedeEscribir = self::puedeEscribir();
        $tipo = $_SESSION['Tipo'] ?? '';
        $miDocenteID = isset($_SESSION['DocenteID']) ? (int)$_SESSION['DocenteID'] : null;

        $resultado = array_map(function ($ev) use ($coloresPorTipo, $puedeEscribir, $tipo, $miDocenteID) {
            // Un docente solo puede editar SUS propios eventos; ADM/SEC editan todos.
            $editable = $puedeEscribir && ($tipo !== 'DOC' || (int)$ev['DocenteID'] === $miDocenteID);

            return [
                'id' => $ev['IdEvento'],
                'title' => $ev['Titulo'],
                'start' => $ev['FechaInicio'],
                'end' => $ev['FechaFin'],
                'color' => $coloresPorTipo[$ev['TipoEvento']] ?? '#2a5298',
                'editable' => $editable,
                'extendedProps' => [
                    'tipoEvento' => $ev['TipoEvento'],
                    'descripcion' => $ev['Descripcion'],
                    'programaID' => $ev['ProgramaID'],
                    'nombrePrograma' => $ev['NombrePrograma'],
                    'codigoPrograma' => $ev['CodigoPrograma'],
                    'gradoAcademico' => $ev['GradoAcademico'],
                    'sede' => $ev['Sede'],
                    'docenteID' => $ev['DocenteID'],
                    'nombreDocente' => $ev['NombreDocente'],
                    'editable' => $editable,
                ]
            ];
        }, $eventos);

        // Vencimientos de cuota (fechas límite de pago). Administrador/Secretaría ven
        // los de todos (para cobranza, con WhatsApp); el Estudiante ve únicamente los
        // suyos (para saber cuándo le vence a él, sin ver la deuda de otros).
        $verVencimientos = in_array($tipo, ['ADM', 'SEC', 'EST'], true);
        $esPropioEstudiante = $tipo === 'EST';

        if ($verVencimientos) {
            $estudianteIDFiltro = $esPropioEstudiante ? ($_SESSION['EstudianteID'] ?? 0) : null;

            $vencimientos = OrdenPagoModelos::ObtenerVencimientosPorRangoModelo(
                $filtros['desde'],
                $filtros['hasta'],
                $filtros['programaID'],
                $filtros['sede'],
                $estudianteIDFiltro
            );

            $hoy = date('Y-m-d');
            foreach ($vencimientos as $v) {
                $vencido = $v['FechaVencimiento'] < $hoy;
                $nombreCompleto = trim($v['Nombre'] . ' ' . $v['Apaterno'] . ' ' . ($v['Amaterno'] ?? ''));
                $montoTexto = 'Bs. ' . number_format((float)$v['MontoTotal'], 2);

                $titulo = $esPropioEstudiante
                    ? (($vencido ? '⚠ Tienes una cuota vencida: ' : '💰 Vence tu cuota: ') . $montoTexto)
                    : (($vencido ? '⚠ Vencido: ' : '💰 Vence: ') . $nombreCompleto . ' (' . $montoTexto . ')');

                $resultado[] = [
                    'id' => 'venc-' . $v['IdOrdenPago'],
                    'title' => $titulo,
                    'start' => $v['FechaVencimiento'],
                    'allDay' => true,
                    'color' => $vencido ? '#c0392b' : '#d4a017',
                    'editable' => false,
                    'extendedProps' => [
                        'tipoEvento' => 'PAGO',
                        'descripcion' => 'Orden ' . $v['NumeroOrden'] . ' - Monto: ' . $montoTexto,
                        'alcance' => 'Vencimiento individual: corresponde a la solicitud de pago (orden) de este estudiante',
                        'accionSugerida' => 'Debe cancelar su cuota antes de esta fecha, según el plazo de su propia solicitud de pago.',
                        'programaID' => $v['ProgramaID'],
                        'nombrePrograma' => $v['NombrePrograma'],
                        'codigoPrograma' => $v['CodigoPrograma'],
                        'gradoAcademico' => $v['GradoAcademico'],
                        'sede' => $v['Sede'],
                        'docenteID' => null,
                        'nombreDocente' => $nombreCompleto,
                        'editable' => false,
                        // El enlace de WhatsApp solo tiene sentido para el staff contactando
                        // al estudiante; no se manda el celular cuando lo ve el propio estudiante.
                        'celular' => $esPropioEstudiante ? null : $v['Celular'],
                        'nombreEstudiante' => $nombreCompleto,
                        'vencido' => $vencido,
                    ]
                ];
            }
        }

        // Plazos de cuota por Programa (fecha límite general definida por
        // Administración/Secretaría). Visibles para todos los roles que ven
        // el calendario, como aviso informativo.
        $plazos = PlazoCuotaProgramaModelo::ObtenerPlazosParaCalendarioModelo(
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['programaID'],
            $filtros['sede']
        );

        $hoy = date('Y-m-d');
        foreach ($plazos as $pl) {
            $vencido = $pl['FechaPlazo'] < $hoy;
            $resultado[] = [
                'id' => 'plazo-' . $pl['IdPlazo'],
                'title' => ($vencido ? '⛔ Plazo de cuota vencido: ' : '🗓 Plazo de cuota: ') . $pl['NombrePrograma'],
                'start' => $pl['FechaPlazo'],
                'allDay' => true,
                'color' => $vencido ? '#c0392b' : '#8e44ad',
                'editable' => false,
                'extendedProps' => [
                    'tipoEvento' => 'PLAZOCUOTA',
                    'idPlazo' => $pl['IdPlazo'],
                    'fechaPlazo' => $pl['FechaPlazo'],
                    'descripcion' => $pl['Descripcion'] ?: 'Fecha límite de pago de cuota del programa',
                    'descripcionOriginal' => $pl['Descripcion'],
                    'alcance' => 'Plazo general del programa: aplica a todos los estudiantes en Plan Regular que aún no cancelaron su cuota vigente',
                    'accionSugerida' => 'Los estudiantes en esta situación deben contactarse con Administración para regularizar su pago.',
                    'programaID' => $pl['ProgramaID'],
                    'nombrePrograma' => $pl['NombrePrograma'],
                    'codigoPrograma' => $pl['CodigoPrograma'],
                    'gradoAcademico' => $pl['GradoAcademico'],
                    'sede' => $pl['Sede'],
                    'docenteID' => null,
                    'nombreDocente' => null,
                    'editable' => false,
                    'vencido' => $vencido,
                ]
            ];
        }

        echo json_encode($resultado);
        exit();
    }

    /**
     * Listar docentes activos, para el select del formulario (solo ADM/SEC lo usan).
     */
    public function ListarDocentesAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(ModuloModelo::ListarDocentesActivosModelo());
        exit();
    }

    /**
     * Registrar un nuevo evento (clase, actividad o clínica).
     */
    public function RegistrarEventoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para registrar eventos']);
            exit();
        }

        $tipoEvento = strtoupper(trim($_POST['tipoEvento'] ?? ''));
        $titulo = trim($_POST['titulo'] ?? '');
        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaInicio = trim($_POST['fechaInicio'] ?? '');
        $fechaFin = trim($_POST['fechaFin'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!in_array($tipoEvento, ['CLASE', 'ACTIVIDAD', 'CLINICA'], true) || empty($titulo) || empty($programaID) || empty($fechaInicio) || empty($fechaFin)) {
            echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios (programa, título, tipo y horario)']);
            exit();
        }

        if (strtotime($fechaFin) <= strtotime($fechaInicio)) {
            echo json_encode(['success' => false, 'mensaje' => 'La hora de fin debe ser posterior a la hora de inicio']);
            exit();
        }

        // Un docente solo puede registrar eventos a su propio nombre.
        $tipoSesion = $_SESSION['Tipo'] ?? '';
        if ($tipoSesion === 'DOC') {
            $docenteID = isset($_SESSION['DocenteID']) ? (int)$_SESSION['DocenteID'] : null;
        } else {
            $docenteID = isset($_POST['docenteID']) && $_POST['docenteID'] !== '' ? (int)$_POST['docenteID'] : null;
        }

        $idEvento = CalendarioEventoModelo::RegistrarEventoModelo([
            'ProgramaID' => $programaID,
            'DocenteID' => $docenteID,
            'TipoEvento' => $tipoEvento,
            'Titulo' => $titulo,
            'Descripcion' => $descripcion,
            'FechaInicio' => $fechaInicio,
            'FechaFin' => $fechaFin,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idEvento === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar el evento']);
            exit();
        }

        echo json_encode(['success' => true, 'idEvento' => $idEvento, 'mensaje' => 'Evento registrado correctamente']);
        exit();
    }

    /**
     * Actualizar un evento existente.
     */
    public function ActualizarEventoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para editar eventos']);
            exit();
        }

        $idEvento = isset($_POST['idEvento']) ? (int)$_POST['idEvento'] : 0;
        $evento = $idEvento ? CalendarioEventoModelo::ObtenerEventoPorIdModelo($idEvento) : null;

        if (!$evento) {
            echo json_encode(['success' => false, 'mensaje' => 'El evento no existe']);
            exit();
        }

        $tipoSesion = $_SESSION['Tipo'] ?? '';
        $miDocenteID = isset($_SESSION['DocenteID']) ? (int)$_SESSION['DocenteID'] : null;

        // Un docente solo puede editar SUS propios eventos, y no puede reasignarlos a otro docente.
        if ($tipoSesion === 'DOC') {
            if ((int)$evento['DocenteID'] !== $miDocenteID) {
                echo json_encode(['success' => false, 'mensaje' => 'Solo puede editar sus propias clases']);
                exit();
            }
            $docenteID = $miDocenteID;
        } else {
            $docenteID = isset($_POST['docenteID']) && $_POST['docenteID'] !== '' ? (int)$_POST['docenteID'] : null;
        }

        $tipoEvento = strtoupper(trim($_POST['tipoEvento'] ?? ''));
        $titulo = trim($_POST['titulo'] ?? '');
        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaInicio = trim($_POST['fechaInicio'] ?? '');
        $fechaFin = trim($_POST['fechaFin'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!in_array($tipoEvento, ['CLASE', 'ACTIVIDAD', 'CLINICA'], true) || empty($titulo) || empty($programaID) || empty($fechaInicio) || empty($fechaFin)) {
            echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios (programa, título, tipo y horario)']);
            exit();
        }

        if (strtotime($fechaFin) <= strtotime($fechaInicio)) {
            echo json_encode(['success' => false, 'mensaje' => 'La hora de fin debe ser posterior a la hora de inicio']);
            exit();
        }

        $ok = CalendarioEventoModelo::ActualizarEventoModelo([
            'IdEvento' => $idEvento,
            'ProgramaID' => $programaID,
            'DocenteID' => $docenteID,
            'TipoEvento' => $tipoEvento,
            'Titulo' => $titulo,
            'Descripcion' => $descripcion,
            'FechaInicio' => $fechaInicio,
            'FechaFin' => $fechaFin,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Evento actualizado correctamente' : 'No se pudo actualizar el evento'
        ]);
        exit();
    }

    /**
     * Anular (baja lógica) un evento.
     */
    public function AnularEventoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para eliminar eventos']);
            exit();
        }

        $idEvento = isset($_POST['idEvento']) ? (int)$_POST['idEvento'] : 0;
        $evento = $idEvento ? CalendarioEventoModelo::ObtenerEventoPorIdModelo($idEvento) : null;

        if (!$evento) {
            echo json_encode(['success' => false, 'mensaje' => 'El evento no existe']);
            exit();
        }

        $tipoSesion = $_SESSION['Tipo'] ?? '';
        $miDocenteID = isset($_SESSION['DocenteID']) ? (int)$_SESSION['DocenteID'] : null;

        if ($tipoSesion === 'DOC' && (int)$evento['DocenteID'] !== $miDocenteID) {
            echo json_encode(['success' => false, 'mensaje' => 'Solo puede eliminar sus propias clases']);
            exit();
        }

        $ok = CalendarioEventoModelo::AnularEventoModelo($idEvento);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Evento eliminado correctamente' : 'No se pudo eliminar el evento'
        ]);
        exit();
    }
}
