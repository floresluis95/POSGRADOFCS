<?php
/**
 * Controlador del Tablero (Panel principal)
 * Arma, según el tipo de usuario de la sesión, los datos que muestra el panel:
 * saludo, indicadores, listas y accesos rápidos. La vista (panel.php) solo los dibuja.
 */

require_once __DIR__ . '/../modelos/tablero.modelo.php';
require_once __DIR__ . '/../modelos/plazocuotaprograma.modelo.php';
require_once __DIR__ . '/../modelos/calendarioevento.modelo.php';

class TableroControlador
{
    /** Accesos rápidos por tipo de usuario: [ruta, ícono, título, descripción] */
    private static $accesos = [
        'ADM' => [
            ['preregistro', 'fa-user-plus', 'Pre-Registro', 'Nuevas órdenes de matrícula'],
            ['matriculados', 'fa-credit-card', 'Matriculados', 'Pagos, cuotas y clínicas'],
            ['inscripcion', 'fa-file-signature', 'Inscripción', 'Validar vouchers'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
            ['reportemodulos', 'fa-chart-bar', 'Reporte de pagos', 'Cobranza por programa'],
            ['reportes', 'fa-file-pdf', 'Reportes PDF', 'Listados y recaudación'],
        ],
        'SEC' => [
            ['preregistro', 'fa-user-plus', 'Pre-Registro', 'Nuevas órdenes de matrícula'],
            ['matriculados', 'fa-credit-card', 'Matriculados', 'Pagos, cuotas y clínicas'],
            ['inscripcion', 'fa-file-signature', 'Inscripción', 'Validar vouchers'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
            ['reportemodulos', 'fa-chart-bar', 'Reporte de pagos', 'Cobranza por programa'],
            ['reportes', 'fa-file-pdf', 'Reportes PDF', 'Listados y recaudación'],
        ],
        'AUX' => [
            ['preregistro', 'fa-user-plus', 'Pre-Registro', 'Nuevas órdenes de matrícula'],
            ['matriculados', 'fa-credit-card', 'Matriculados', 'Pagos, cuotas y clínicas'],
            ['inscripcion', 'fa-file-signature', 'Inscripción', 'Validar vouchers'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
            ['reportemodulos', 'fa-chart-bar', 'Reporte de pagos', 'Cobranza por programa'],
        ],
        'DOC' => [
            ['reportecalificaciones', 'fa-clipboard-check', 'Calificaciones', 'Registrar notas'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Mis clases y actividades'],
        ],
        'EST' => [
            ['misordenespago', 'fa-file-invoice-dollar', 'Mis órdenes de pago', 'Imprimir y consultar'],
            ['historialnotaestudiante', 'fa-graduation-cap', 'Mis calificaciones', 'Historial académico'],
            ['historialmodulosestudiante', 'fa-receipt', 'Mis pagos', 'Cuotas y vouchers'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
        ],
    ];

    /**
     * Datos del tablero para la sesión actual.
     * @return array
     */
    public static function DatosTableroControlador()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        $nombres = trim($_SESSION['Nombres'] ?? '');
        $primerNombre = $nombres !== '' ? mb_convert_case(explode(' ', $nombres)[0], MB_CASE_TITLE, 'UTF-8') : '';

        $hora = (int)date('G');
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');

        $roles = ['ADM' => 'Administrador', 'SEC' => 'Secretaría', 'AUX' => 'Auxiliar', 'DOC' => 'Docente', 'EST' => 'Estudiante'];

        $datos = [
            'tipo' => $tipo,
            'saludo' => $saludo . ($primerNombre !== '' ? ', ' . $primerNombre : ''),
            'rol' => $roles[$tipo] ?? 'Usuario',
            'fecha' => self::FechaLarga(),
            'accesos' => self::$accesos[$tipo] ?? [],
            'eventos' => [],
        ];

        if (in_array($tipo, ['ADM', 'SEC', 'AUX'], true)) {
            $datos['resumen'] = TableroModelo::ResumenGestionModelo();
            $datos['ultimosPagos'] = TableroModelo::UltimosPagosModelo(6);
            $datos['eventos'] = TableroModelo::ProximosEventosModelo(null, null, 30, 6);
        } elseif ($tipo === 'EST') {
            $estudianteID = (int)($_SESSION['EstudianteID'] ?? 0);
            $datos['programas'] = TableroModelo::ProgramasEstudianteModelo($estudianteID);
            $datos['avisosMora'] = PlazoCuotaProgramaModelo::ObtenerAvisoEstudianteModelo($estudianteID);
            $datos['eventos'] = TableroModelo::ProximosEventosModelo(
                CalendarioEventoModelo::ObtenerProgramaIDsDeEstudianteModelo($estudianteID), null, 30, 6
            );
        } elseif ($tipo === 'DOC') {
            $docenteID = (int)($_SESSION['DocenteID'] ?? 0);
            $datos['modulos'] = TableroModelo::ModulosDocenteModelo($docenteID);
            $datos['eventos'] = TableroModelo::ProximosEventosModelo(null, $docenteID, 30, 6);
        }

        return $datos;
    }

    /** "Sábado, 26 de septiembre de 2026" */
    private static function FechaLarga()
    {
        $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return mb_convert_case($dias[(int)date('w')], MB_CASE_TITLE, 'UTF-8') . ', ' . date('j') . ' de ' . $meses[(int)date('n') - 1] . ' de ' . date('Y');
    }
}
