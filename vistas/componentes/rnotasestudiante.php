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

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
/* =========================================================
   REGISTRAR NOTAS (prefijo rn-): docente → módulo → notas finales.
   Mismo lenguaje visual que Inscripción e Historial de Pagos. Las tablas
   las arma vistas/recursos/assets/js/scripts/calificacion.js (solo usa los
   id de este HTML, no sus clases).
   ========================================================= */
.rn {
    --rn-primario: #1e3c72;
    --rn-primario-suave: #eef2f9;
    --rn-texto: #1f2937;
    --rn-texto-2: #64748b;
    --rn-borde: #e3e7ef;
    max-width: 1240px;
    margin: 0 auto;
}

/* ---------- Pasos ---------- */
/* ---------- Calificaciones pendientes (ADM) ---------- */
.rn-pend { border-left: 4px solid #b91c1c; }
.rn-pend .rn-card-head h3 i { color: #b91c1c; }
.rn-pend-n { display: inline-block; min-width: 22px; padding: 1px 8px; margin-left: 6px; border-radius: 11px; background: #b91c1c;
    color: #fff; font-size: 12px; font-weight: 700; text-align: center; vertical-align: middle; }
.rn-pend-n.is-cero { background: #15803d; }
.rn-pend-vacio { text-align: center; color: var(--rn-texto-2); padding: 14px; font-size: 13px; }
.rn-pend-lista-scroll { max-height: 380px; overflow: auto; border: 1px solid var(--rn-borde); border-radius: 8px; }
.rn-pend-tabla { width: 100%; font-size: 13px; margin: 0; }
.rn-pend-tabla thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
.rn-pend-tabla th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: var(--rn-texto-2);
    padding: 6px 10px; border-bottom: 1px solid var(--rn-borde); white-space: nowrap; }
.rn-pend-tabla td { padding: 7px 10px; border-top: 1px solid #f1f5f9; vertical-align: middle; }
.rn-pend-tabla tr.is-marcada td { background: #fff7f7; }
.rn-pend-tabla td small { display: block; color: var(--rn-texto-2); font-size: 11.5px; }
.rn-pend-atraso { color: #b91c1c; font-weight: 600; }
.rn-pend-barra { display: inline-block; width: 70px; height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; vertical-align: middle; margin-right: 6px; }
.rn-pend-barra span { display: block; height: 100%; background: var(--rn-primario); }
.rn-pend-aviso-no { color: var(--rn-texto-2); font-style: italic; }
.rn-pend-visto { color: #15803d; }
.rn-pend-novisto { color: #b45309; }
.rn-pend-caja { margin-top: 12px; padding: 12px; border: 1px solid var(--rn-borde); border-radius: 8px; background: #fafbfd; }
.rn-pend-botones { display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; }
.rn-pend-enviar { background: #b91c1c; border: 1px solid #b91c1c; color: #fff; font-weight: 600; }
.rn-pend-enviar:hover, .rn-pend-enviar:focus { background: #991b1b; color: #fff; }
.rn-pend-enviar:disabled { opacity: .6; }
/* Módulo del docente que ya terminó y falta calificar (tabla del paso 2) */
.table-calificaciones tr.rn-fila-pendiente td { background: #fff7f7; }
.table-calificaciones tr.rn-fila-pendiente td:first-child { box-shadow: inset 3px 0 0 #b91c1c; }
.rn-marca-pendiente { display: inline-block; margin-top: 4px; font-size: 11px; font-weight: 700; color: #b91c1c; }
.rn-pasos { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
.rn-paso { display: flex; align-items: center; gap: 12px; }
.rn-paso strong { display: block; font-size: 13.5px; color: var(--rn-texto-2); }
.rn-paso small { display: block; font-size: 12px; color: #94a3b8; }
.rn-paso-num {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 700;
    font-size: 14px;
    background: #fff;
    border: 2px solid #cbd5e1;
    color: #94a3b8;
}
.rn-paso.is-hecho .rn-paso-num { border-color: var(--rn-primario); color: var(--rn-primario); }
.rn-paso.is-hecho strong { color: var(--rn-texto); }
.rn-paso.active .rn-paso-num {
    background: var(--rn-primario);
    border-color: var(--rn-primario);
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .3);
}
.rn-paso.active strong { color: var(--rn-primario); }
.rn-paso.active small { color: var(--rn-texto-2); }
.rn-paso-linea { flex: 1; height: 2px; background: #d8dde8; border-radius: 2px; }

/* ---------- Tarjetas ---------- */
.rn-card {
    background: #fff;
    border: 1px solid var(--rn-borde);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    margin-bottom: 18px;
}
.rn-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px 16px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--rn-borde);
}
.rn-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--rn-texto); }
.rn-card-head h3 i { color: var(--rn-primario); margin-right: 4px; }
.rn-card-head p { font-size: 12.5px; color: var(--rn-texto-2); margin: 3px 0 0; }
.rn-card-head .rn-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
.rn-card-body { padding: 22px 24px; }

.rn-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--rn-texto);
    margin-bottom: 6px;
}
.rn-label i { color: var(--rn-primario); width: 14px; }
.rn-ayuda-campo { display: block; margin-top: 5px; font-size: 11.5px; color: var(--rn-texto-2); }
.rn .form-control:focus {
    border-color: var(--rn-primario) !important;
    box-shadow: 0 0 0 3px rgba(30, 60, 114, .12) !important;
}

/* ---------- Ficha del docente ---------- */
.rn-ficha {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}
.rn-ficha-item {
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid var(--rn-borde);
    border-radius: 10px;
    min-width: 0;
}
.rn-ficha-item > span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: var(--rn-texto-2); }
.rn-ficha-item > span i { color: #94a3b8; margin-right: 3px; }
.rn-ficha-item strong { display: block; margin-top: 3px; font-size: 13.5px; color: var(--rn-texto); overflow-wrap: anywhere; }
.rn-ficha-item.is-destacado { background: var(--rn-primario-suave); border-color: #d5deee; }
.rn-ficha-item.is-destacado strong { color: var(--rn-primario); font-size: 18px; }
.rn-ficha .kt-badge { background: transparent !important; color: inherit !important; padding: 0; font-size: inherit; height: auto; }

/* ---------- Tablas (armadas por calificacion.js) ---------- */
.rn .table-calificaciones {
    margin: 0;
    font-size: 13px;
    border: 1px solid var(--rn-borde);
    border-radius: 10px;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    box-shadow: none;
}
.rn .table-calificaciones thead,
.rn .table-calificaciones thead th {
    background: #f8fafc;
    color: var(--rn-texto-2);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-color: var(--rn-borde);
    border-top: 0;
    white-space: nowrap;
}
.rn .table-calificaciones td { vertical-align: middle; border-color: #eef1f6; color: var(--rn-texto); }
.rn .table-calificaciones tbody tr:hover { background-color: #f8fafc; }
.rn .asignacion-row { cursor: pointer; }

.input-nota {
    text-align: center;
    font-weight: 700;
    font-size: 1.05rem;
    height: 40px;
    border-radius: 8px;
    max-width: 120px;
    margin: 0 auto;
}
.input-nota.is-valid {
    border-color: #1e3c72 !important;
    background-color: #f0fdf4 !important;
}
.input-nota.is-invalid {
    border-color: #c53030 !important;
    background-color: #fef2f2 !important;
}

/* Badges informativos (Grado, Código): tono neutro; se conservan en color
   solo los badges de estado de calificación (sin calificar/parcial/completo,
   aprobado/reprobado), que sí aportan información útil de un vistazo. */
.kt-badge--primary,
.kt-badge--dark {
    background-color: #eef2f9 !important;
    color: #1e3c72 !important;
}

/* ---------- Pie de "Guardar calificaciones" ---------- */
.rn-pie {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid var(--rn-borde);
}
.rn-pie-aviso { font-size: 12.5px; color: #8a6116; }
.rn-pie-aviso i { color: #d69e2e; margin-right: 4px; }
.rn-btn-guardar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 26px;
    background: #1e3c72;
    border: 1px solid #1e3c72;
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
}
.rn-btn-guardar:hover, .rn-btn-guardar:focus {
    background: #16305c;
    border-color: #16305c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .25);
}

.rn-vacio { text-align: center; padding: 30px 20px; color: var(--rn-texto-2); background: #f8fafc; border-radius: 10px; }
.rn-vacio i { display: block; font-size: 28px; color: #c7cdd8; margin-bottom: 8px; }

/* ---------- Select2: alto uniforme ---------- */
.select2-container--default .select2-selection--single {
    height: 40px !important;
    display: flex !important;
    align-items: center;
    padding: 0 !important;
    border: 1px solid #d8dde8 !important;
    border-radius: 8px !important;
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

@media (max-width: 991.98px) {
    .rn-ficha { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .rn-paso small { display: none; }
    .rn-paso strong { font-size: 12px; }
    .rn-ficha { grid-template-columns: 1fr; }
    .rn-card-head .rn-acciones { width: 100%; }
}
</style>

  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="panel">
        <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler">
        <i class="flaticon-more-1"></i>
      </button>
    </div>
  </div>

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
                    <div class="kt-container kt-grid__item kt-grid__item--fluid">
                        <div class="rn">

                            <?php if (($_SESSION['Tipo'] ?? '') === 'ADM'): ?>
                            <!-- Calificaciones pendientes (solo ADM): módulos terminados con estudiantes sin nota
                                 y aviso a sus docentes (calificacion-pendientes.js) -->
                            <div class="rn-card rn-pend" id="rnPendientes">
                                <div class="rn-card-head">
                                    <div>
                                        <h3><i class="fa fa-bell"></i> Calificaciones pendientes <span class="rn-pend-n" id="rnPendN"></span></h3>
                                        <p>Módulos que ya terminaron y tienen estudiantes sin nota. Marque los módulos y avise a sus docentes.</p>
                                    </div>
                                    <button type="button" class="btn btn-abrir-panel" id="rnPendToggle"><i class="fa fa-chevron-up"></i> Ocultar</button>
                                </div>
                                <div class="rn-card-body" id="rnPendCuerpo">
                                    <div id="rnPendLista"><div class="rn-pend-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</div></div>
                                    <div class="rn-pend-caja" id="rnPendCaja" style="display:none;">
                                        <label for="rnPendMensaje" class="rn-label">Mensaje para los docentes</label>
                                        <textarea class="form-control" id="rnPendMensaje" rows="3" maxlength="600">La Coordinación de Posgrado le recuerda que el módulo a su cargo ya concluyó. Le solicitamos registrar las calificaciones finales en el sistema a la brevedad posible.</textarea>
                                        <small class="rn-ayuda-campo">Cada docente lo verá al ingresar al sistema, junto con sus módulos pendientes.</small>
                                        <div class="rn-pend-botones">
                                            <button type="button" class="btn btn-sm btn-light" id="rnPendCancelar">Cancelar</button>
                                            <button type="button" class="btn btn-sm rn-pend-enviar" id="rnPendEnviar"><i class="fa fa-paper-plane"></i> Enviar aviso</button>
                                        </div>
                                    </div>
                                    <div class="rn-pend-botones" id="rnPendAcciones" style="display:none;">
                                        <button type="button" class="btn btn-sm rn-pend-enviar" id="rnPendNotificar"><i class="fa fa-bell"></i> Notificar a los docentes</button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Indicador de pasos: lo actualiza actualizarPasosNotas() según
                                 cuál de los tres contenedores muestra calificacion.js -->
                            <div class="rn-pasos">
                                <div class="rn-paso active" id="rnPaso1">
                                    <span class="rn-paso-num">1</span>
                                    <div><strong>Docente</strong><small>Seleccionar docente</small></div>
                                </div>
                                <div class="rn-paso-linea"></div>
                                <div class="rn-paso" id="rnPaso2">
                                    <span class="rn-paso-num">2</span>
                                    <div><strong>Módulo</strong><small>Elegir la asignación</small></div>
                                </div>
                                <div class="rn-paso-linea"></div>
                                <div class="rn-paso" id="rnPaso3">
                                    <span class="rn-paso-num">3</span>
                                    <div><strong>Calificaciones</strong><small>Registrar notas finales</small></div>
                                </div>
                            </div>

                            <!-- PASO 1: SELECCIÓN DE DOCENTE -->
                            <div id="paso1-container" class="rn-card">
                                <div class="rn-card-head">
                                    <div>
                                        <h3><i class="fa fa-chalkboard-teacher"></i> Seleccionar docente</h3>
                                        <p>Los filtros son opcionales: acotan la lista de docentes por sede, programa o código.</p>
                                    </div>
                                </div>
                                <div class="rn-card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <label for="filtro-sede-docente" class="rn-label"><i class="fa fa-map-marker-alt"></i> Sede</label>
                                            <select id="filtro-sede-docente" class="form-control select2-uniforme">
                                                <option value="">Todas las sedes</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <label for="filtro-programa-docente" class="rn-label"><i class="fa fa-university"></i> Programa</label>
                                            <select id="filtro-programa-docente" class="form-control select2-uniforme">
                                                <option value="">Todos los programas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 mb-md-0">
                                            <label for="filtro-codigo-docente" class="rn-label"><i class="fa fa-code"></i> Código de programa</label>
                                            <select id="filtro-codigo-docente" class="form-control select2-uniforme">
                                                <option value="">Todos los códigos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="select-docente" class="rn-label"><i class="fa fa-user-tie"></i> Docente <span class="text-danger">*</span></label>
                                            <select id="select-docente" class="form-control select2-uniforme">
                                                <option value="">-- Seleccione un docente --</option>
                                                <?php
                                                    $controlador = new CalificacionControlador();
                                                    $controlador->ListarDocentesSelectControlador();
                                                ?>
                                            </select>
                                            <small class="rn-ayuda-campo">Al elegirlo se muestran sus módulos asignados.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASO 2: INFORMACIÓN DEL DOCENTE Y SUS ASIGNACIONES -->
                            <div id="paso2-container" class="rn-card" style="display: none;">
                                <div class="rn-card-head">
                                    <div>
                                        <h3><i class="fa fa-book"></i> Módulos asignados</h3>
                                        <p>Pulse <strong>Evaluar</strong> en el módulo cuyas notas desea registrar.</p>
                                    </div>
                                    <button class="btn btn-abrir-panel" id="btn-cambiar-docente">
                                        <i class="fa fa-exchange-alt"></i> Cambiar docente
                                    </button>
                                </div>
                                <div class="rn-card-body">
                                    <!-- Información del docente -->
                                    <div class="rn-ficha" id="info-docente-container">
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-user-tie"></i> Docente</span>
                                            <strong id="docente-nombre"></strong>
                                        </div>
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-id-card"></i> C.I.</span>
                                            <strong id="docente-ci"></strong>
                                        </div>
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-graduation-cap"></i> Especialidad</span>
                                            <strong id="docente-especialidad"></strong>
                                        </div>
                                        <div class="rn-ficha-item is-destacado">
                                            <span><i class="fa fa-book"></i> Asignaciones</span>
                                            <strong><span class="kt-badge" id="total-asignaciones"></span></strong>
                                        </div>
                                    </div>

                                    <!-- Tabla de asignaciones -->
                                    <div id="asignaciones-container"></div>
                                </div>
                            </div>

                            <!-- PASO 3: ESTUDIANTES DEL MÓDULO SELECCIONADO -->
                            <div id="paso3-container" class="rn-card" style="display: none;">
                                <div class="rn-card-head">
                                    <div>
                                        <h3><i class="fa fa-edit"></i> Registrar calificaciones</h3>
                                        <p>Nota final de 0 a 100; se aprueba con 76 o más.</p>
                                    </div>
                                    <div class="rn-acciones">
                                        <button type="button" class="btn btn-abrir-panel" id="btn-imprimir-calificaciones">
                                            <i class="fa fa-print"></i> Imprimir calificaciones
                                        </button>
                                        <button type="button" class="btn btn-abrir-panel" id="btn-volver-asignaciones">
                                            <i class="fa fa-arrow-left"></i> Volver a módulos
                                        </button>
                                    </div>
                                </div>
                                <div class="rn-card-body">
                                    <!-- Información del módulo seleccionado -->
                                    <div class="rn-ficha">
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-book"></i> Módulo</span>
                                            <strong id="modulo-nombre"></strong>
                                            <span class="mt-1" style="text-transform:none;font-weight:500;"><i class="fa fa-code"></i> <span id="modulo-codigo"></span></span>
                                        </div>
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-university"></i> Programa</span>
                                            <strong id="programa-nombre"></strong>
                                        </div>
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-graduation-cap"></i> Grado</span>
                                            <strong id="grado-nombre"></strong>
                                        </div>
                                        <div class="rn-ficha-item">
                                            <span><i class="fa fa-calendar-alt"></i> Periodo</span>
                                            <strong><span id="modulo-fecha-inicio"></span> – <span id="modulo-fecha-final"></span></strong>
                                        </div>
                                    </div>

                                    <!-- Tabla de estudiantes -->
                                    <div id="estudiantes-container">
                                        <div class="rn-vacio">
                                            <i class="fa fa-user-graduate"></i> Seleccione un módulo para cargar los estudiantes
                                        </div>
                                    </div>
                                </div>

                                <!-- PIE CON BOTÓN DE GUARDAR -->
                                <div class="rn-pie" id="footer-guardar" style="display: none;">
                                    <span class="rn-pie-aviso">
                                        <i class="fa fa-exclamation-triangle"></i> Verifique todas las notas antes de guardar.
                                    </span>
                                    <button class="btn rn-btn-guardar" id="btn-guardar-notas">
                                        <i class="fa fa-save"></i> Guardar calificaciones
                                    </button>
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
</script>

<script src="vistas/recursos/assets/js/scripts/calificacion.js?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/js/scripts/calificacion.js'); ?>"></script>
<?php if (($_SESSION['Tipo'] ?? '') === 'ADM'): ?>
<script src="vistas/recursos/assets/js/scripts/calificacion-pendientes.js?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/js/scripts/calificacion-pendientes.js'); ?>"></script>
<?php endif; ?>

<script>
// Actualizar fecha
function actualizarFecha() {
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
}

// Indicador de pasos: calificacion.js muestra/oculta #paso1/2/3-container con
// slideUp/slideDown; aquí solo se observa cuál quedó visible para marcar el
// paso actual (y los anteriores como hechos), sin tocar esa lógica.
function actualizarPasosNotas() {
    let actual = 1;
    if ($('#paso3-container').is(':visible')) actual = 3;
    else if ($('#paso2-container').is(':visible')) actual = 2;

    [1, 2, 3].forEach(function(n) {
        $('#rnPaso' + n).toggleClass('active', n === actual).toggleClass('is-hecho', n < actual);
    });
}

$(document).ready(function() {
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    const observador = new MutationObserver(function() {
        // Espera a que termine la animación de slideDown/slideUp
        setTimeout(actualizarPasosNotas, 450);
    });
    ['paso1-container', 'paso2-container', 'paso3-container'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) observador.observe(el, { attributes: true, attributeFilter: ['style'] });
    });
    actualizarPasosNotas();
});
</script>

</body>
