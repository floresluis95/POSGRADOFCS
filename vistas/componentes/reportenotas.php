<?php
/**
 * Vista: Reporte de Notas por Programa
 * Lista todos los programas, sus módulos y permite generar reportes PDF
 */

// Validación de sesión
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();

date_default_timezone_set("America/La_Paz");

// Generar Token CSRF
$csrf_token = bin2hex(random_bytes(32));
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
/* =========================================================
   REPORTE DE NOTAS (prefijo rnt-): programas → módulos → PDF / auditoría.
   Mismo lenguaje visual que Registrar Notas e Historial de Pagos. Las
   tablas de módulos y de auditoría las arma reportenotas.js.
   ========================================================= */
.rnt {
    --rnt-primario: #1e3c72;
    --rnt-primario-suave: #eef2f9;
    --rnt-texto: #1f2937;
    --rnt-texto-2: #64748b;
    --rnt-borde: #e3e7ef;
}

.rnt-card {
    background: #fff;
    border: 1px solid var(--rnt-borde);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    margin-bottom: 18px;
}
.rnt-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px 16px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--rnt-borde);
}
.rnt-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--rnt-texto); }
.rnt-card-head h3 i { color: var(--rnt-primario); margin-right: 4px; }
.rnt-card-head p { font-size: 12.5px; color: var(--rnt-texto-2); margin: 3px 0 0; }
.rnt-card-body { padding: 20px 24px; }

.rnt-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--rnt-texto);
    margin-bottom: 6px;
}
.rnt-label i { color: var(--rnt-primario); width: 14px; }
.rnt .btn-abrir-panel { height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }

/* ---------- Tablas (programas, módulos y auditoría) ---------- */
.rnt-tabla,
#contenedorModulos .table,
#contenedorAuditoria .table {
    margin: 0;
    font-size: 13px;
    border: 1px solid var(--rnt-borde, #e3e7ef);
    border-radius: 10px;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
}
.rnt-tabla thead th,
#contenedorModulos .table thead th,
#contenedorAuditoria .table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-top: 0;
    border-bottom: 1px solid #e3e7ef;
    border-left: 0;
    border-right: 0;
    white-space: nowrap;
    vertical-align: middle;
}
.rnt-tabla thead th i { color: #94a3b8; margin-right: 3px; }
.rnt-tabla td,
#contenedorModulos .table td,
#contenedorAuditoria .table td {
    vertical-align: middle;
    border-color: #eef1f6;
    border-left: 0;
    border-right: 0;
    color: #1f2937;
}
.rnt-tabla tbody tr:hover,
#contenedorModulos .table tbody tr:hover { background: #f8fafc; }
#contenedorModulos .table-striped tbody tr:nth-of-type(odd),
#contenedorAuditoria .table-striped tbody tr:nth-of-type(odd) { background: #fff; }

/* Etiquetas: tono suave; en color solo las que indican un estado real */
.rnt .badge,
#contenedorModulos .badge,
#contenedorAuditoria .badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    background: #eef2f9 !important;
    color: #1e3c72 !important;
    border: 0;
}
#contenedorModulos .badge-success,
#contenedorAuditoria .badge-success { background: #e7f6ee !important; color: #2f855a !important; }
#contenedorModulos .badge-warning,
#contenedorAuditoria .badge-warning { background: #fff6e0 !important; color: #b7791f !important; }
#contenedorModulos .badge-secondary,
#contenedorAuditoria .badge-secondary { background: #f1f5f9 !important; color: #475569 !important; }

/* ---------- Ventanas ---------- */
.rnt-modal .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
.rnt-modal-head {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-bottom: 0;
    align-items: flex-start;
}
.rnt-modal-head small {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #fff;
    opacity: .75;
}
.rnt-modal-head .modal-title,
.rnt-modal-head .modal-title i { color: #fff !important; font-weight: 700; font-size: 16px; opacity: 1; }
.rnt-modal-head .close,
.rnt-modal-head .close span { color: #fff !important; opacity: .9; text-shadow: none; }
.rnt-modal .modal-body { padding: 22px 24px; }
.rnt-cargando { text-align: center; padding: 30px 0; color: #64748b; }
#contenedorModulos .table td:nth-child(2) { white-space: nowrap; } /* código del módulo en un renglón */

/* Resumen de auditoría (lo arma reportenotas.js) */
.rnt-resumen {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}
.rnt-resumen-item {
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e3e7ef;
    border-radius: 10px;
}
.rnt-resumen-item span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: #64748b; }
.rnt-resumen-item strong { display: block; margin-top: 3px; font-size: 18px; color: #1e3c72; }
.rnt-resumen-item strong.is-texto { font-size: 13px; color: #1f2937; }
.rnt-resumen-item.is-alerta strong { color: #b7791f; }

.rnt-vacio { text-align: center; padding: 28px 20px; color: #64748b; background: #f8fafc; border-radius: 10px; }
.rnt-vacio i { display: block; font-size: 26px; color: #c7cdd8; margin-bottom: 8px; }

@media (max-width: 991.98px) {
    .rnt-resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
    .rnt-card-head { flex-direction: column; align-items: flex-start; }
}
</style>

  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="panel">
         <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
    </div>
  </div>

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

    <?php
      $NavBar = new FuncionesControladores();
      $NavBar -> NavBarControlador();
    ?>
    <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
    <?php
      $Sidebar = new FuncionesControladores();
      $Sidebar -> SidebarControlador();
    ?>

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 class="">REPORTES DE CALIFICACIONES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>REPORTE DE NOTAS POR PROGRAMA</h3>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                  <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>
            <!-- end:: Subheader -->

            <!-- begin:: Content -->
            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="rnt">

                <!-- Filtros -->
                <div class="rnt-card">
                  <div class="rnt-card-head">
                    <div>
                      <h3><i class="fas fa-filter"></i> Filtrar programas</h3>
                      <p>Acote la lista por sede, programa o código.</p>
                    </div>
                  </div>
                  <div class="rnt-card-body">
                    <div class="row align-items-end">
                      <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <label class="rnt-label" for="filtroSedeReporte"><i class="fas fa-map-marker-alt"></i> Sede</label>
                        <select class="form-control select2-uniforme" id="filtroSedeReporte">
                          <option value="">Todas las sedes...</option>
                          <?php
                            require_once 'modelos/programa.modelo.php';
                            $sedesReporteNotas = ProgramasModelos::ListarSedesModelo();
                            foreach ($sedesReporteNotas as $sedeOpcionReporte) {
                                $sedeValorReporte = htmlspecialchars($sedeOpcionReporte, ENT_QUOTES);
                                echo '<option value="' . $sedeValorReporte . '">' . $sedeValorReporte . '</option>';
                            }
                          ?>
                        </select>
                      </div>
                      <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <label class="rnt-label" for="filtroProgramaReporte"><i class="fas fa-university"></i> Programa</label>
                        <select class="form-control select2-uniforme" id="filtroProgramaReporte">
                          <option value="">Todos los programas...</option>
                          <?php
                            $programasReporteNotas = ProgramasModelos::ListaProgramaModelo();
                            foreach ($programasReporteNotas as $progReporte) {
                                $nombreProgReporte = htmlspecialchars($progReporte['NombrePrograma'] ?? '', ENT_QUOTES);
                                echo '<option value="' . $nombreProgReporte . '">' . $nombreProgReporte . '</option>';
                            }
                          ?>
                        </select>
                      </div>
                      <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-md-0">
                        <label class="rnt-label" for="filtroCodigoReporte"><i class="fas fa-code"></i> Código</label>
                        <select class="form-control select2-uniforme" id="filtroCodigoReporte">
                          <option value="">Todos los códigos...</option>
                          <?php
                            $codigosReporteNotas = array();
                            foreach ($programasReporteNotas as $progCodigoReporte) {
                                $codigoValorReporte = trim($progCodigoReporte['Codigo'] ?? '');
                                if ($codigoValorReporte !== '' && !in_array($codigoValorReporte, $codigosReporteNotas, true)) {
                                    $codigosReporteNotas[] = $codigoValorReporte;
                                }
                            }
                            sort($codigosReporteNotas);
                            foreach ($codigosReporteNotas as $codigoOpcionReporte) {
                                $codigoEscapadoReporte = htmlspecialchars($codigoOpcionReporte, ENT_QUOTES);
                                echo '<option value="' . $codigoEscapadoReporte . '">' . $codigoEscapadoReporte . '</option>';
                            }
                          ?>
                        </select>
                      </div>
                      <div class="col-xl-3 col-lg-6 col-md-6">
                        <button type="button" class="btn btn-abrir-panel btn-block" id="btnLimpiarFiltroReporte">
                          <i class="fa fa-times"></i> Limpiar filtros
                        </button>
                      </div>
                    </div>
                    <script>
                      // Relación Programa -> Código, para que el filtro de Código
                      // se limite a los códigos del/los programa(s) seleccionado(s).
                      window.programasParaFiltroCodigo = <?php
                        $programasParaFiltroCodigo = array();
                        foreach ($programasReporteNotas as $progMapReporte) {
                            $nombreMapReporte = trim($progMapReporte['NombrePrograma'] ?? '');
                            $codigoMapReporte = trim($progMapReporte['Codigo'] ?? '');
                            if ($codigoMapReporte !== '') {
                                $programasParaFiltroCodigo[] = array('nombre' => $nombreMapReporte, 'codigo' => $codigoMapReporte);
                            }
                        }
                        echo json_encode($programasParaFiltroCodigo, JSON_UNESCAPED_UNICODE);
                      ?>;
                    </script>
                  </div>
                </div>

                <!-- Programas -->
                <div class="rnt-card">
                  <div class="rnt-card-head">
                    <div>
                      <h3><i class="fas fa-file-alt"></i> Programas académicos</h3>
                      <p>Pulse <strong>Ver módulos</strong> para imprimir la planilla de calificaciones o revisar la auditoría de un módulo.</p>
                    </div>
                    <button type="button" class="btn btn-abrir-panel" onclick="location.reload()">
                      <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                  </div>
                  <div class="rnt-card-body">
                    <div class="table-responsive">
                      <table class="table rnt-tabla" id="tablaProgramas">
                        <thead>
                          <tr>
                            <th class="text-center" style="width: 5%;">Nº</th>
                            <th style="width: 30%;"><i class="fas fa-graduation-cap"></i> Programa académico</th>
                            <th class="text-center" style="width: 10%;"><i class="fas fa-map-marker-alt"></i> Sede</th>
                            <th class="text-center" style="width: 15%;"><i class="fas fa-award"></i> Grado</th>
                            <th class="text-center" style="width: 10%;"><i class="fas fa-code"></i> Código</th>
                            <th class="text-center" style="width: 10%;"><i class="fas fa-book"></i> Módulos</th>
                            <th class="text-center" style="width: 10%;"><i class="fas fa-users"></i> Estudiantes</th>
                            <th class="text-center" style="width: 15%;"><i class="fas fa-cog"></i> Acciones</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                            $ListaProgramas = new ReporteNotasControlador();
                            $ListaProgramas -> ListarProgramasConModulosControlador();
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <!-- end:: Content -->

          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer -> FooterResumidoControlador();
        ?>

      </div>
    </div>
  </div>

<!-- Modales fuera de #kt_wrapper: dentro, el tema aplica transform a los
     contenedores del layout y eso rompe el position:fixed de un modal. -->

<!-- Modal: Ver Módulos del Programa -->
<div class="modal fade rnt-modal" id="ModalModulosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalModulosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header rnt-modal-head">
        <div>
          <small>Módulos del programa</small>
          <h4 class="modal-title" id="modalModulosLabel">
            <i class="fas fa-book"></i> <span id="nombreProgramaModal"></span>
          </h4>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="loadingModulos" class="rnt-cargando" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-3 mb-0">Cargando módulos...</p>
        </div>
        <div id="contenedorModulos"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Panel de Auditoría -->
<div class="modal fade rnt-modal" id="ModalAuditoria" tabindex="-1" role="dialog" aria-labelledby="modalAuditoriaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header rnt-modal-head">
        <div>
          <small>Auditoría de calificaciones</small>
          <h4 class="modal-title" id="modalAuditoriaLabel">
            <i class="fas fa-history"></i> <span id="nombreModuloAuditoria"></span>
          </h4>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Resumen de Auditoría -->
        <div id="resumenAuditoria"></div>

        <!-- Loading -->
        <div id="loadingAuditoria" class="rnt-cargando" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-3 mb-0">Cargando auditoría...</p>
        </div>

        <!-- Tabla de Auditoría -->
        <div id="contenedorAuditoria"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts necesarios -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="vistas/recursos/assets/js/scripts/reportenotas.js?v=<?php echo time(); ?>"></script>

</body>
