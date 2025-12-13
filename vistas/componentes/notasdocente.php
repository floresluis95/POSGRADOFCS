<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir modelos y controladores necesarios
require_once 'controladores/calificacion.controlador.php';
require_once 'modelos/calificacion.modelo.php';
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

<div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

            <?php $NavBar = new FuncionesControladores(); $NavBar->NavBarControlador(); ?>
            <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
            <?php $Sidebar = new FuncionesControladores(); $Sidebar->SidebarControlador(); ?>

            <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                    <!-- begin:: Subheader -->
                    <div class="kt-subheader kt-grid__item" id="kt_subheader">
                        <div class="kt-container ">
                            <div class="kt-subheader__main">
                                <h2 class="">MIS CALIFICACIONES</h2>
                                <span class="kt-subheader__separator kt-hidden"></span>
                                <div class="kt-subheader__breadcrumbs">
                                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                    <span class="kt-subheader__breadcrumbs-separator"></span>
                                    <h4>CARGA DE NOTAS FINALES</h4>
                                </div>
                            </div>
                            <div class="kt-subheader__toolbar">
                                <div class="kt-subheader__wrapper">
                                    <div id="lafecha" style="font-size:13pt"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENIDO PRINCIPAL -->
                    <div class="container-fluid">
                        <div class="kt-portlet kt-portlet--mobile">
                            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px 10px 0 0;">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title text-white">
                                        <i class="fa fa-graduation-cap"></i> Registro de Calificaciones Finales
                                    </h3>
                                </div>
                            </div>

                            <div class="kt-portlet__body" style="padding: 2rem;">

                                <!-- LOADING INICIAL -->
                                <div id="loading-inicial" class="card mb-4" style="border-left: 4px solid #5867dd; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <div class="card-body text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando...</span>
                                        </div>
                                        <p class="mt-3 mb-0">Cargando información del docente...</p>
                                    </div>
                                </div>

                                <!-- PASO 1: INFORMACIÓN DEL DOCENTE Y SUS ASIGNACIONES -->
                                <div id="paso1-container" class="card mb-4" style="display: none; border-left: 4px solid #1dc9b7; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <div class="card-header" style="background-color: #f8f9fa;">
                                        <h5 class="mb-0" style="color: #1dc9b7;">
                                            <i class="flaticon2-user"></i> Mis Asignaciones
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Información del docente -->
                                        <div class="alert alert-light mb-3" id="info-docente-container">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Docente:</strong> <span id="docente-nombre"></span></p>
                                                    <p class="mb-0"><strong>C.I.:</strong> <span id="docente-ci"></span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Especialidad:</strong> <span id="docente-especialidad"></span></p>
                                                    <p class="mb-0"><strong>Total Asignaciones:</strong> <span id="total-asignaciones" class="kt-badge kt-badge--brand kt-badge--inline"></span></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tabla de asignaciones -->
                                        <div id="asignaciones-container"></div>
                                    </div>
                                </div>

                                <!-- PASO 2: ESTUDIANTES DEL MÓDULO SELECCIONADO -->
                                <div id="paso2-container" class="card mb-4" style="display: none; border-left: 4px solid #ffb822; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                    <div class="card-header" style="background-color: #f8f9fa;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-0" style="color: #ffb822;">
                                                    <i class="flaticon2-edit"></i> Ingresar Calificaciones
                                                </h5>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <button class="btn btn-sm btn-secondary" id="btn-volver-asignaciones">
                                                    <i class="la la-arrow-left"></i> Volver a Asignaciones
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Información del módulo seleccionado -->
                                        <div class="alert alert-info mb-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fa fa-book"></i> <strong>Módulo:</strong>
                                                        <span id="modulo-nombre"></span>
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="fa fa-code"></i> <strong>Código:</strong>
                                                        <span id="modulo-codigo"></span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <i class="fa fa-university"></i> <strong>Programa:</strong>
                                                        <span id="programa-nombre"></span>
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="fa fa-graduation-cap"></i> <strong>Grado:</strong>
                                                        <span id="grado-nombre"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tabla de estudiantes -->
                                        <div id="estudiantes-container">
                                            <div class="alert alert-secondary text-center">
                                                <i class="flaticon-edit"></i> Seleccione un módulo para cargar los estudiantes
                                            </div>
                                        </div>

                                        <!-- SECCIÓN DE GENERACIÓN DE PDF -->
                                        <div id="pdf-section" class="mt-4" style="display: none;">
                                            <div class="card" style="border: 2px solid #5867dd; border-radius: 10px;">
                                                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <h5 class="mb-0 text-white">
                                                        <i class="fa fa-file-pdf"></i> Imprimir Planilla de Calificaciones (PDF)
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="fecha-planilla" class="font-weight-bold">
                                                                <i class="fa fa-calendar"></i> Fecha:
                                                            </label>
                                                            <input type="date" class="form-control" id="fecha-planilla" required>
                                                        </div>
                                                        <div class="col-md-8 d-flex align-items-end">
                                                            <button class="btn btn-danger btn-lg" id="btn-generar-pdf" style="width: 100%;">
                                                                <i class="fa fa-print"></i> IMPRIMIR PLANILLA DE CALIFICACIONES
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- FOOTER CON BOTÓN DE GUARDAR -->
                                    <div class="card-footer" id="footer-guardar" style="display: none;">
                                        <div class="row align-items-center">
                                            <div class="col-lg-6 kt-align-left">
                                                <span class="text-danger small">
                                                    <i class="fa fa-exclamation-triangle"></i> Verifique todas las notas antes de guardar.
                                                </span>
                                            </div>
                                            <div class="col-lg-6 kt-align-right">
                                                <button class="btn btn-success btn-lg" id="btn-guardar-notas" style="padding: 0.75rem 2.5rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(29, 201, 183, 0.4);">
                                                    <i class="fa fa-save"></i> Guardar Calificaciones
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- FIN DEL CONTENIDO PRINCIPAL -->

                    <?php $Footer = new FuncionesControladores(); $Footer->FooterControlador(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="vistas/recursos/assets/js/scripts/calificacion-docente.js?v=2.0"></script>

<style>
.form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.table-calificaciones {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table-calificaciones thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-calificaciones tbody tr:hover {
    background-color: #f8f9fa;
}

.input-nota {
    text-align: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.input-nota.is-valid {
    border-color: #1dc9b7 !important;
    background-color: #f0fdf4 !important;
}

.input-nota.is-invalid {
    border-color: #fd397a !important;
    background-color: #fef2f2 !important;
}

.asignacion-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.asignacion-row:hover {
    background-color: #e8f5e9 !important;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>

<script>
// Actualizar fecha
function actualizarFecha() {
    const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
}

$(document).ready(function() {
    // Actualizar fecha
    actualizarFecha();
    setInterval(actualizarFecha, 1000);
});
</script>

</body>
