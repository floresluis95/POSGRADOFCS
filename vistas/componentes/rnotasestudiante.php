<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir modelos y controladores necesarios
require_once 'controladores/calificacion.controlador.php';
require_once 'modelos/calificacion.modelo.php';

// Obtener tipo de usuario para JavaScript
$tipoUsuario = $_SESSION["Tipo"] ?? '';
$esAdministrador = ($tipoUsuario === 'ADM');
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
                                <h2 class="">REGISTRO DE CALIFICACIONES</h2>
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
                            <div class="kt-portlet__head" style="background-color: #34495e; border-radius: 4px 4px 0 0;">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title text-white">
                                        <i class="fa fa-graduation-cap"></i> Registro de Calificaciones Finales
                                    </h3>
                                </div>
                            </div>

                            <div class="kt-portlet__body" style="padding: 2rem;">

                                <!-- PASO 1: SELECCIÓN DE DOCENTE -->
                                <div id="paso1-container" class="card mb-4" style="border-left: 4px solid #34495e; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                                    <div class="card-header" style="background-color: #f8f9fa;">
                                        <h5 class="mb-0" style="color: #34495e;">
                                            <i class="flaticon2-user-outline-symbol"></i> Paso 1: Seleccionar Docente
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="filtro-sede-docente" class="font-weight-bold">
                                                    <i class="fa fa-map-marker-alt text-primary"></i> Sede:
                                                </label>
                                                <select id="filtro-sede-docente" class="form-control select2-uniforme">
                                                    <option value="">Todas las sedes</option>
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="flaticon2-information"></i> Opcional, acota el listado de docentes
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filtro-programa-docente" class="font-weight-bold">
                                                    <i class="fa fa-university text-primary"></i> Programa:
                                                </label>
                                                <select id="filtro-programa-docente" class="form-control select2-uniforme">
                                                    <option value="">Todos los programas</option>
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="flaticon2-information"></i> Opcional, acota el listado de docentes
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filtro-codigo-docente" class="font-weight-bold">
                                                    <i class="fa fa-code text-primary"></i> Código de Programa:
                                                </label>
                                                <select id="filtro-codigo-docente" class="form-control select2-uniforme">
                                                    <option value="">Todos los códigos</option>
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="flaticon2-information"></i> Opcional, acota el listado de docentes
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="select-docente" class="font-weight-bold">
                                                    <i class="flaticon2-user-outline-symbol text-primary"></i> Docente:
                                                </label>
                                                <select id="select-docente" class="form-control select2-uniforme">
                                                    <option value="">-- Seleccione un docente --</option>
                                                    <?php
                                                        $controlador = new CalificacionControlador();
                                                        $controlador->ListarDocentesSelectControlador();
                                                    ?>
                                                </select>
                                                <small class="form-text text-muted">
                                                    <i class="flaticon2-information"></i> Seleccione el docente para ver sus módulos asignados
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PASO 2: INFORMACIÓN DEL DOCENTE Y SUS ASIGNACIONES -->
                                <div id="paso2-container" class="card mb-4" style="display: none; border-left: 4px solid #34495e; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                                    <div class="card-header" style="background-color: #f8f9fa;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-0" style="color: #34495e;">
                                                    <i class="flaticon2-user"></i> Paso 2: Asignaciones del Docente
                                                </h5>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <button class="btn btn-sm btn-secondary" id="btn-cambiar-docente">
                                                    <i class="la la-refresh"></i> Cambiar Docente
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Información del docente -->
                                        <div class="docente-info-formal mb-3" id="info-docente-container">
                                            <div class="docente-info-item">
                                                <i class="fa fa-user-tie"></i>
                                                <span class="docente-info-label">Docente:</span>
                                                <span class="docente-info-valor" id="docente-nombre"></span>
                                            </div>
                                            <span class="docente-info-sep">|</span>
                                            <div class="docente-info-item">
                                                <i class="fa fa-id-card"></i>
                                                <span class="docente-info-label">C.I.:</span>
                                                <span class="docente-info-valor" id="docente-ci"></span>
                                            </div>
                                            <span class="docente-info-sep">|</span>
                                            <div class="docente-info-item">
                                                <i class="fa fa-graduation-cap"></i>
                                                <span class="docente-info-label">Especialidad:</span>
                                                <span class="docente-info-valor" id="docente-especialidad"></span>
                                            </div>
                                            <span class="docente-info-sep">|</span>
                                            <div class="docente-info-item">
                                                <i class="fa fa-book"></i>
                                                <span class="docente-info-label">Total Asignaciones:</span>
                                                <span class="kt-badge kt-badge--light kt-badge--inline kt-badge--bold" id="total-asignaciones"></span>
                                            </div>
                                        </div>

                                        <!-- Tabla de asignaciones -->
                                        <div id="asignaciones-container"></div>
                                    </div>
                                </div>

                                <!-- PASO 3: ESTUDIANTES DEL MÓDULO SELECCIONADO -->
                                <div id="paso3-container" class="card mb-4" style="display: none; border-left: 4px solid #34495e; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                                    <div class="card-header" style="background-color: #f8f9fa;">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="mb-0" style="color: #34495e;">
                                                    <i class="flaticon2-edit"></i> Paso 3: Ingresar Calificaciones
                                                </h5>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <button type="button" class="btn btn-sm btn-success" id="btn-imprimir-calificaciones" style="margin-right: 10px;">
                                                    <i class="fa fa-print"></i> Imprimir Calificaciones
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" id="btn-generar-lista-asistencia" style="margin-right: 10px;">
                                                    <i class="fa fa-file-pdf"></i> Lista de Asistencia
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary" id="btn-volver-asignaciones">
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
                                                    <p class="mb-1">
                                                        <i class="fa fa-graduation-cap"></i> <strong>Grado:</strong>
                                                        <span id="grado-nombre"></span>
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="fa fa-calendar-check-o"></i> <strong>Fecha Inicio:</strong>
                                                        <span id="modulo-fecha-inicio"></span>
                                                    </p>
                                                    <p class="mb-0">
                                                        <i class="fa fa-calendar-times-o"></i> <strong>Fecha Final:</strong>
                                                        <span id="modulo-fecha-final"></span>
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
                                                <button class="btn btn-success" id="btn-guardar-notas" style="padding: 0.6rem 2rem;">
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

                    <?php $Footer = new FuncionesControladores(); $Footer->FooterResumidoControlador(); ?>
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

<!-- Pasar variables PHP a JavaScript -->
<script>
    // Variable global para verificar si el usuario es administrador
    const esAdministrador = <?php echo $esAdministrador ? 'true' : 'false'; ?>;
    const tipoUsuario = '<?php echo $tipoUsuario; ?>';
    console.log('Usuario tipo:', tipoUsuario, '| Es Admin:', esAdministrador);
</script>

<script src="vistas/recursos/assets/js/scripts/calificacion.js?v=7.1"></script>

<style>
.form-control:focus {
    border-color: #34495e !important;
    box-shadow: 0 0 0 0.15rem rgba(52, 73, 94, 0.2) !important;
}

/* Información del docente: una sola franja formal, en lugar de párrafos
   sueltos en dos columnas. */
.docente-info-formal {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px 0;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    border-radius: 8px;
    padding: 14px 20px;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(30, 60, 114, 0.2);
}

.docente-info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.docente-info-label {
    font-weight: 700;
}

.docente-info-valor {
    font-weight: 400;
}

.docente-info-sep {
    opacity: .45;
    margin: 0 18px;
}

.docente-info-formal .kt-badge {
    background-color: rgba(255, 255, 255, 0.18) !important;
    color: #fff !important;
}

@media (max-width: 991px) {
    .docente-info-item {
        white-space: normal;
    }
    .docente-info-sep {
        display: none;
        width: 100%;
    }
    .docente-info-formal {
        gap: 8px 0;
    }
}

.select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px) !important;
    display: flex !important;
    align-items: center;
    padding: 0 !important;
    border: 1px solid #ced4da !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    /* El bundle del tema (demo9/style.bundle.css) le pone a este mismo
       elemento "padding: .65rem 3rem .65rem 1rem"; si aquí solo se
       sobreescriben padding-left/right, el padding vertical de esa regla
       se sigue sumando y el texto se sale de la caja. Hay que anular los
       cuatro lados con el shorthand. */
    padding: 0 20px 0 0.75rem !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    /* El bundle del tema posiciona esta flecha con "top: 50%" sin transform,
       así que queda corrida hacia arriba; top+translateY(-50%) la centra
       sin importar el alto real de la caja. */
    top: 50% !important;
    transform: translateY(-50%) !important;
    height: auto !important;
}

.card {
    transition: box-shadow 0.2s ease;
}

.table-calificaciones {
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.table-calificaciones thead {
    background-color: #34495e;
    color: white;
}

.table-calificaciones tbody tr:hover {
    background-color: #f4f5f7;
}

.input-nota {
    text-align: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.input-nota.is-valid {
    border-color: #1e3c72 !important;
    background-color: #f0fdf4 !important;
}

.input-nota.is-invalid {
    border-color: #fd397a !important;
    background-color: #fef2f2 !important;
}

.resultado-docente {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.resultado-docente:hover {
    background-color: #f4f5f7;
}

.asignacion-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.asignacion-row:hover {
    background-color: #eef1f4 !important;
}

/* Badges informativos (Grado, Código): tono neutro; se conservan en color
   solo los badges de estado de calificación (sin calificar/parcial/completo),
   que sí aportan información útil de un vistazo. */
.kt-badge--primary,
.kt-badge--dark {
    background-color: #e9ecef !important;
    color: #34495e !important;
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

    // DEBUG: Verificar botón de lista de asistencia
    console.log('=== DEBUG VISTA RNOTASESTUDIANTE ===');

    // Verificar si el botón existe inicialmente
    const botonInicial = $('#btn-generar-lista-asistencia');
    console.log('Botón encontrado al cargar:', botonInicial.length > 0 ? 'SÍ' : 'NO');

    // Verificar periódicamente si el botón aparece
    setInterval(function() {
        const botonActual = $('#btn-generar-lista-asistencia');
        if (botonActual.length > 0 && botonActual.is(':visible')) {
            console.log('✅ Botón de lista de asistencia está visible');
        }
    }, 5000);

    // Test manual del botón
    window.testBotonAsistencia = function() {
        console.log('=== TEST MANUAL DEL BOTÓN ===');
        const boton = $('#btn-generar-lista-asistencia');
        console.log('Botón existe:', boton.length > 0);
        console.log('Botón visible:', boton.is(':visible'));
        console.log('Elemento:', boton[0]);

        if (boton.length > 0) {
            console.log('Intentando hacer clic programáticamente...');
            boton.trigger('click');
        }
    };

    console.log('Para probar manualmente, escribe en consola: testBotonAsistencia()');
});
</script>

</body>
