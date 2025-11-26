<?php
/**
 * AJAX para Reportes de Módulos
 */

require_once '../controladores/reportemodulos.controlador.php';

// Obtener grados académicos
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerGrados') {
    ReporteModulosControlador::ObtenerGradosAcademicosControlador();
}

// Obtener programas por grado
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerProgramas') {
    ReporteModulosControlador::ObtenerProgramasPorGradoControlador();
}

// Obtener módulos por programa
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerModulos') {
    ReporteModulosControlador::ObtenerModulosPorProgramaControlador();
}

// Obtener inscritos (reporte)
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerInscritos') {
    ReporteModulosControlador::ObtenerInscritosReporteControlador();
}

// Obtener estadísticas generales
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerEstadisticas') {
    ReporteModulosControlador::ObtenerEstadisticasGeneralesControlador();
}

// Obtener conteo de módulos por programa
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerConteoModulosPorPrograma') {
    ReporteModulosControlador::ObtenerConteoModulosPorProgramaControlador();
}

// Obtener conteo de inscritos por módulo
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerConteoInscritosPorModulo') {
    ReporteModulosControlador::ObtenerConteoInscritosPorModuloControlador();
}
?>
