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
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
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
<div class="app-content content kt-container">
  <div class="content-wrapper">
    <div class="content-body">
      <section id="html">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm mb-3">
              <div class="card-header" style="background-color: #34495e;">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-filter"></i> Filtrar por Sede y Programa
                </h4>
              </div>
              <div class="card-content collapse show">
                <div class="card-body">
                  <div class="form-group row mb-0 align-items-end">
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                      <label class="mb-1"><strong>Sede:</strong></label>
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
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                      <label class="mb-1"><strong>Programa:</strong></label>
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
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-2 mb-xl-0">
                      <label class="mb-1"><strong>Código:</strong></label>
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
                      <button type="button" class="btn btn-secondary btn-block" id="btnLimpiarFiltroReporte">
                        <i class="fa fa-times"></i> Limpiar
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
            </div>

            <div class="card shadow-sm">
              <div class="card-header bg-gradient-primary">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-file-alt"></i> Programas Académicos - Reportes de Notas
                </h4>
                <div class="heading-elements">
                  <button type="button" class="btn btn-light btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt mr-1"></i>Actualizar
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="tablaProgramas">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 5%;">Nº</th>
                          <th style="width: 30%;">
                            <i class="fas fa-graduation-cap"></i> Programa Académico
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-map-marker-alt"></i> Sede
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-award"></i> Grado
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-code"></i> Código
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-book"></i> Módulos
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-users"></i> Estudiantes
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-cog"></i> Acciones
                          </th>
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
        </div>
      </section>
    </div>
  </div>
</div>

<style>
  .table thead.thead-dark th {
    background-color: #34495e;
    color: white;
    font-weight: 600;
    border: none;
    padding: 15px 10px;
    font-size: 13px;
    vertical-align: middle;
  }

  .table tbody tr {
    transition: background-color 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: #f4f5f7;
  }

  .table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border-color: #e9ecef;
    font-size: 13px;
  }

  .card.shadow-sm {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
  }

  .card-header.bg-gradient-primary {
    background-color: #34495e;
    padding: 15px 20px;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .btn-sm {
    padding: 5px 10px;
    font-size: 12px;
  }

  .badge {
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 3px;
  }

  .badge-primary,
  .badge-danger,
  .badge-success,
  .badge-secondary {
    background-color: #e9ecef !important;
    color: #34495e !important;
    border: 1px solid #ced4da;
  }
</style>

<!-- Modal: Ver Módulos del Programa -->
<div class="modal fade" id="ModalModulosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalModulosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background-color: #34495e; color: white;">
        <h4 class="modal-title text-white" id="modalModulosLabel">
          <i class="fas fa-book mr-2"></i> Módulos del Programa: <span id="nombreProgramaModal"></span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 30px;">
        <div id="loadingModulos" class="text-center" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-3">Cargando módulos...</p>
        </div>
        <div id="contenedorModulos"></div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Panel de Auditoría -->
<div class="modal fade" id="ModalAuditoria" tabindex="-1" role="dialog" aria-labelledby="modalAuditoriaLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background-color: #34495e; color: white;">
        <h4 class="modal-title text-white" id="modalAuditoriaLabel">
          <i class="fas fa-history mr-2"></i> Panel de Auditoría: <span id="nombreModuloAuditoria"></span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 30px;">
        <!-- Resumen de Auditoría -->
        <div id="resumenAuditoria" class="mb-4"></div>

        <!-- Loading -->
        <div id="loadingAuditoria" class="text-center" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-3">Cargando auditoría...</p>
        </div>

        <!-- Tabla de Auditoría -->
        <div id="contenedorAuditoria"></div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>

<!-- Scripts necesarios -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="vistas/recursos/assets/js/scripts/reportenotas.js?v=<?php echo time(); ?>"></script>

</body>
