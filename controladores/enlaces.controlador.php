<?php
    class EnlacesControladores
    {
        // Páginas que son de UN solo rol: a cualquier otro se le muestra el error 404
        // (no el panel), p. ej. el registro de notas del docente abierto por el ADM.
        const PAGINAS_EXCLUSIVAS = ['reportecalificaciones' => 'DOC'];

        // Páginas que puede abrir cualquier usuario con sesión
        const PAGINAS_COMUNES = ['index', 'inicio', 'ingreso', 'salir', '404', 'panel', 'calendario', 'autoinscripcion'];

        // Páginas de cada rol (además de las comunes; el ADM ve todas las del personal,
        // incluidas Autoridades y el Catálogo de Requisitos). Es una LISTA BLANCA: lo que no
        // está aquí no se abre aunque se escriba la URL a mano (antes las vistas solo
        // exigían sesión, así un estudiante podía abrir "usuario" o "matriculados").
        // Al agregar una página nueva, agregarla también aquí al rol que corresponda.
        const PAGINAS_POR_ROL = [
            'SEC' => [
                'programas', 'modulos', 'reportemodulos', 'preregistro', 'inscripcion', 'matriculados',
                'historialpagos', 'ordenespago', 'solicitudesinscripcion', 'rnotasestudiante', 'reportenotas',
                'bnotaestudiante', 'historialnotaestudiante', 'asignaciondocente',
                'estudiantes', 'docentes', 'usuario',
                'certificadocalificaciones', 'certificadohistorialacademico',
                'certificadoaprobacionmonografia', 'certificadomediomagnetico', 'certificadorevision', 'registrodocumentos', 'reportes',
                'orden-generada', 'matriculas', 'detalles',
            ],
            // Auxiliar: como Secretaría, pero sin Usuarios, Certificados ni Reportes PDF
            'AUX' => [
                'programas', 'modulos', 'reportemodulos', 'preregistro', 'inscripcion', 'matriculados',
                'historialpagos', 'ordenespago', 'solicitudesinscripcion', 'rnotasestudiante', 'reportenotas',
                'bnotaestudiante', 'historialnotaestudiante', 'asignaciondocente',
                'estudiantes', 'docentes',
                'orden-generada', 'matriculas', 'detalles',
            ],
            'DOC' => ['reportecalificaciones'],
            'EST' => ['historialnotaestudiante', 'historialmodulosestudiante', 'historialreciboestudiante', 'misordenespago', 'misrequisitos', 'miscertificados'],
        ];

        /**
         * ¿El tipo de usuario puede abrir esa página?
         */
        public static function PuedeVerPagina($tipo, $enlace)
        {
            if (in_array($enlace, self::PAGINAS_COMUNES, true)) {
                return true;
            }
            if (isset(self::PAGINAS_EXCLUSIVAS[$enlace])) {
                return $tipo === self::PAGINAS_EXCLUSIVAS[$enlace];
            }
            if ($tipo === 'ADM') {
                // El ADM ve todo lo del personal; las páginas del estudiante no le sirven
                return !in_array($enlace, self::PAGINAS_POR_ROL['EST'], true) || $enlace === 'historialnotaestudiante';
            }
            return in_array($enlace, self::PAGINAS_POR_ROL[$tipo] ?? [], true);
        }

        public function EnlacesControlador()
        {
            if (isset($_GET["action"]))
            {
                $enlace = $_GET["action"];
            }
            else
            {
                $enlace = "index";
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Con sesión iniciada, cada rol solo abre sus páginas; si escribe otra
            // URL a mano se le muestra el panel. (Sin sesión, cada vista ya manda al login.)
            $tipoSesion = $_SESSION['Tipo'] ?? '';
            if (!empty($_SESSION['Validar']) && $tipoSesion !== '' && !self::PuedeVerPagina($tipoSesion, $enlace)) {
                $enlace = isset(self::PAGINAS_EXCLUSIVAS[$enlace]) ? '404' : 'panel';
            }

            $respuesta = EnlacesModelos::EnlacesModelo($enlace);
            include $respuesta;

        }
    }
