<?php
    // Coontroladores
    require_once 'controladores/plantilla.controlador.php';
    require_once 'controladores/enlaces.controlador.php';
    require_once 'controladores/ingreso.controlador.php';
    require_once 'controladores/funciones.controlador.php';
    require_once 'controladores/usuario.controlador.php';
    require_once 'controladores/programa.controlador.php';
    require_once 'controladores/estudiantes.controlador.php';
    require_once 'controladores/inscripcion.controlador.php';
    require_once 'controladores/matricula.controlador.php';
    require_once 'controladores/inscripcionmodulo.controlador.php';
    require_once 'controladores/docentes.controlador.php';
    require_once 'controladores/modulo.controlador.php';
    require_once 'controladores/reportemodulos.controlador.php';
    require_once 'controladores/reportes.controlador.php';
    require_once 'controladores/reportenotas.controlador.php';
    require_once 'controladores/calificacion.controlador.php';
    require_once 'controladores/pagomodulo.controlador.php';
    require_once 'controladores/cuenta.controlador.php';
    require_once 'controladores/programacuenta.controlador.php';
    require_once 'controladores/especialidad.controlador.php';
    require_once 'controladores/calendarioevento.controlador.php';
    require_once 'controladores/plazocuotaprograma.controlador.php';
    require_once 'controladores/noticiapanel.controlador.php';
    require_once 'controladores/cronogramadefensa.controlador.php';
    require_once 'controladores/carruselpanel.controlador.php';
    require_once 'controladores/tablero.controlador.php';
    // Modelos
    require_once 'modelos/enlaces.modelo.php';
    require_once 'modelos/ingreso.modelo.php';
    require_once 'modelos/usuario.modelo.php';
    require_once 'modelos/heredado.modelo.php';
    require_once 'modelos/programa.modelo.php';
    require_once 'modelos/estudiantes.modelo.php';
    require_once 'modelos/universidad.modelo.php';
    require_once 'modelos/historialpagos.modelo.php';
    require_once file_exists(__DIR__ . '/modelos/inscripcion.modelo.php') ? 'modelos/inscripcion.modelo.php' : 'core/inscripcion.modelo.php';
    require_once 'modelos/matricula.modelo.php';
    require_once 'modelos/inscripcionmodulo.modelo.php';
    require_once 'modelos/docentes.modelo.php';
    require_once 'modelos/modulo.modelo.php';
    require_once 'modelos/reportemodulos.modelo.php';
    require_once 'modelos/reportes.modelo.php';
    require_once 'modelos/reportenotas.modelo.php';
    require_once 'modelos/configuracion.modelo.php'; // nota mínima de aprobación y demás parámetros
    require_once 'modelos/institucion.modelo.php'; // datos y logos de la institución (encabezados, firmas, pantallas)
    require_once 'modelos/calificacion.modelo.php';
    require_once 'modelos/cuenta.modelo.php';
    require_once 'modelos/programacuenta.modelo.php';
    require_once 'modelos/especialidad.modelo.php';
    require_once 'modelos/modulopagos_core.php';
    require_once 'modelos/calendarioevento.modelo.php';
    require_once 'modelos/plazocuotaprograma.modelo.php';
    require_once 'modelos/noticiapanel.modelo.php';
    require_once 'modelos/cronogramadefensa.modelo.php';
    require_once 'modelos/carruselpanel.modelo.php';


    // Llamar plantilla
    $Plantilla = new PlantillaControladores();
    $Plantilla -> LlamarPlantillaControlador();