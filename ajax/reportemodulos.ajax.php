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

// Obtener estadísticas generales
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerEstadisticas') {
    ReporteModulosControlador::ObtenerEstadisticasGeneralesControlador();
}

// Obtener conteo de módulos por programa
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerConteoModulosPorPrograma') {
    ReporteModulosControlador::ObtenerConteoModulosPorProgramaControlador();
}

// Reporte de pagos por programa (cuántos por tipo de plan, completados, saldo pendiente)
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerReportePagosPrograma') {
    ReporteModulosControlador::ObtenerReportePagosPorProgramaControlador();
}

// Detalle de pagos por estudiante, para un programa específico
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerReportePagosEstudiantes') {
    ReporteModulosControlador::ObtenerReportePagosPorEstudianteControlador();
}
?>
