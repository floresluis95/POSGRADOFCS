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
    // Modelos
    require_once 'modelos/enlaces.modelo.php';
    require_once 'modelos/ingreso.modelo.php';
    require_once 'modelos/usuario.modelo.php';
    require_once 'modelos/heredado.modelo.php';
    require_once 'modelos/programa.modelo.php';
    require_once 'modelos/estudiantes.modelo.php';
    require_once 'modelos/inscripcion.modelo.php';
    require_once 'modelos/matricula.modelo.php';
    require_once 'modelos/inscripcionmodulo.modelo.php';
    require_once 'modelos/docentes.modelo.php';
    require_once 'modelos/modulo.modelo.php';
    require_once 'modelos/reportemodulos.modelo.php';


    // Llamar plantilla
    $Plantilla = new PlantillaControladores();
    $Plantilla -> LlamarPlantillaControlador();