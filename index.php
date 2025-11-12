<?php
    // Coontroladores
    require_once 'controladores/plantilla.controlador.php';
    require_once 'controladores/enlaces.controlador.php';
    require_once 'controladores/ingreso.controlador.php';
    require_once 'controladores/funciones.controlador.php';
    require_once 'controladores/usuario.controlador.php';
    require_once 'controladores/vehiculo.controlador.php';
    require_once 'controladores/marca.controlador.php';
    require_once 'controladores/kit.controlador.php';
    require_once 'controladores/cilindro.controlador.php';
    require_once 'controladores/propietario.controlador.php';
    require_once 'controladores/nentrega.controlador.php';
    require_once 'controladores/prtrabajo.controlador.php';
    require_once 'controladores/solicitud.controlador.php';
    require_once 'controladores/tecnico.controlador.php';
    require_once 'controladores/asignados.controlador.php';
    require_once 'controladores/concluidos.controlador.php';
    require_once 'controladores/consultas.controlador.php';
    require_once 'controladores/particular.controlador.php';
    require_once 'controladores/detalles.controlador.php';
        require_once 'controladores/programa.controlador.php';
        require_once 'controladores/estudiantes.controlador.php';
        require_once 'controladores/inscripcion.controlador.php';
        require_once 'controladores/docentes.controlador.php';
    // Modelos
    require_once 'modelos/enlaces.modelo.php';
    require_once 'modelos/ingreso.modelo.php';
    require_once 'modelos/usuario.modelo.php';
    require_once 'modelos/heredado.modelo.php';
    require_once 'modelos/vehiculo.modelo.php';
    require_once 'modelos/marca.modelo.php';
    require_once 'modelos/kit.modelo.php';
    require_once 'modelos/cilindro.modelo.php';
    require_once 'modelos/propietario.modelo.php';
    require_once 'modelos/nentrega.modelo.php';
    require_once 'modelos/prtrabajo.modelo.php';
    require_once 'modelos/solicitud.modelo.php';
    require_once 'modelos/tecnico.modelo.php';
    require_once 'modelos/asignados.modelo.php';
    require_once 'modelos/concluidos.modelo.php';
    require_once 'modelos/consultas.modelo.php';
    require_once 'modelos/particular.modelo.php';
    require_once 'modelos/detalles.modelo.php';
        require_once 'modelos/programa.modelo.php';
        require_once 'modelos/estudiantes.modelo.php';
        require_once 'modelos/inscripcion.modelo.php';
         require_once 'modelos/docentes.modelo.php';
    
    
    // Llamar plantilla
    $Plantilla = new PlantillaControladores();
    $Plantilla -> LlamarPlantillaControlador();