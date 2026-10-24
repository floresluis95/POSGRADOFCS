<?php
/**
 * Controlador del Tablero (Panel principal)
 * Arma, según el tipo de usuario de la sesión, los datos que muestra el panel:
 * saludo, indicadores, listas y accesos rápidos. La vista (panel.php) solo los dibuja.
 */

require_once __DIR__ . '/../modelos/tablero.modelo.php';
require_once __DIR__ . '/../modelos/plazocuotaprograma.modelo.php';
require_once __DIR__ . '/../modelos/calendarioevento.modelo.php';
require_once __DIR__ . '/enlaces.controlador.php';

class TableroControlador
{
    /**
     * Accesos rápidos del personal, agrupados por tema: [ruta, ícono, título, descripción].
     * El mismo listado sirve para ADM, SEC y AUX: cada uno ve solo las páginas que su rol
     * puede abrir (EnlacesControladores::PuedeVerPagina), así no aparece un enlace que
     * después lo devuelva al panel.
     */
    private static $accesosPersonal = [
        'Matrícula y pagos' => [
            ['preregistro', 'fa-user-plus', 'Pre-Registro', 'Nuevas órdenes de matrícula'],
            ['inscripcion', 'fa-file-signature', 'Inscripción', 'Validar vouchers'],
            ['matriculados', 'fa-credit-card', 'Matriculados', 'Pagos, cuotas y clínicas'],
            ['historialpagos', 'fa-history', 'Historial de pagos', 'Pagos por estudiante'],
            ['ordenespago', 'fa-file-invoice-dollar', 'Órdenes de pago', 'Buscar y reimprimir'],
            ['estudiantes', 'fa-users', 'Estudiantes', 'Datos y cuentas'],
        ],
        'Calificaciones' => [
            ['rnotasestudiante', 'fa-clipboard-check', 'Registrar notas', 'Por módulo'],
            ['reportenotas', 'fa-print', 'Imprimir notas', 'Actas por módulo'],
            ['bnotaestudiante', 'fa-search', 'Notas por estudiante', 'Buscar por C.I. o nombre'],
        ],
        'Certificados' => [
            ['certificadocalificaciones', 'fa-file-alt', 'Calificaciones', 'Certificado de notas'],
            ['certificadohistorialacademico', 'fa-list-ol', 'Historial académico', 'Todo el programa'],
            ['certificadoaprobacionmonografia', 'fa-award', 'Aprobación', 'Programa de posgrado'],
            ['certificadomediomagnetico', 'fa-compact-disc', 'Medio magnético', 'Entrega del trabajo'],
            ['certificadorevision', 'fa-spell-check', 'Revisión', 'Certificado de revisión'],
            ['registrodocumentos', 'fa-folder-open', 'Registro de documentos', 'Emitidos, recibos y titulación'],
        ],
        'Reportes y agenda' => [
            ['reportemodulos', 'fa-chart-bar', 'Reporte de pagos', 'Cobranza por programa'],
            ['reportes', 'fa-file-pdf', 'Reportes PDF', 'Listados y recaudación'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
        ],
    ];

    /** Accesos rápidos del docente y del estudiante (un solo grupo, sin título) */
    private static $accesosPropios = [
        'DOC' => [
            ['reportecalificaciones', 'fa-clipboard-check', 'Calificaciones', 'Registrar notas'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Mis clases y actividades'],
        ],
        'EST' => [
            ['misordenespago', 'fa-file-invoice-dollar', 'Mis órdenes de pago', 'Imprimir y consultar'],
            ['historialmodulosestudiante', 'fa-receipt', 'Mis pagos', 'Cuotas y vouchers'],
            ['historialnotaestudiante', 'fa-graduation-cap', 'Mis calificaciones', 'Historial académico'],
            ['misrequisitos', 'fa-tasks', 'Mis requisitos', 'Documentos y titulación'],
            ['miscertificados', 'fa-award', 'Mis certificados', 'Certificados pagados'],
            ['calendario', 'fa-calendar-alt', 'Calendario', 'Clases y actividades'],
        ],
    ];

    /**
     * Accesos rápidos del tipo de usuario: [nombreGrupo => [acceso, ...]], solo con las
     * páginas que puede abrir. Los grupos que quedan vacíos no se muestran.
     */
    private static function AccesosPorTipo($tipo)
    {
        if (isset(self::$accesosPropios[$tipo])) {
            $grupos = ['' => self::$accesosPropios[$tipo]];
        } elseif (in_array($tipo, ['ADM', 'SEC', 'AUX'], true)) {
            $grupos = self::$accesosPersonal;
        } else {
            return [];
        }

        $resultado = [];
        foreach ($grupos as $nombre => $accesos) {
            $permitidos = array_values(array_filter($accesos, function ($acceso) use ($tipo) {
                return EnlacesControladores::PuedeVerPagina($tipo, $acceso[0]);
            }));
            if ($permitidos) {
                $resultado[$nombre] = $permitidos;
            }
        }
        return $resultado;
    }

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
            'accesos' => self::AccesosPorTipo($tipo),
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
