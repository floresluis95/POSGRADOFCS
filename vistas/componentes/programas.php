<?php
    $Validar = new FuncionesControladores();
    $Validar->ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right 
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed 
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

  <!-- Header móvil -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
        <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
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
  <!-- end:: Header Mobile -->

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

        <?php
          $NavBar = new FuncionesControladores();
          $NavBar->NavBarControlador();
        ?>

        <button class="kt-aside-close" id="kt_aside_close_btn"><i class="la la-close"></i></button>

        <?php
          $Sidebar = new FuncionesControladores();
          $Sidebar->SidebarControlador();
        ?>

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>PROGRAMAS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h4>DETALLE DE PROGRAMAS HABILITADOS</h4>
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
            <div class="kt-container">
              <!-- Filtros de Búsqueda -->
              <div class="row justify-content-md-right mb-3">
                <div class="col-lg-12">
                  <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white;">
                          <i class="fa fa-filter"></i> FILTROS DE BÚSQUEDA
                        </h3>
                      </div>
                    </div>
                    <div class="kt-portlet__body">
                      <form id="formBusquedaProgramas" method="GET">
                        <div class="row">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label><i class="fa fa-graduation-cap"></i> Grado Académico</label>
                              <select class="form-control select2-uniforme" id="filtroGrado" name="grado">
                                <option value="">Todos los grados</option>
                                <option value="DIPLOMADO">DIPLOMADO</option>
                                <option value="MAESTRIA">MAESTRIA</option>
                                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                                <option value="DOCTORADO">DOCTORADO</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label><i class="fa fa-map-marker"></i> Sede</label>
                              <select class="form-control select2-uniforme" id="filtroSede" name="sede">
                                <option value="">Todas las sedes</option>
                                <?php
                                  $sedesFiltroProgramas = ProgramasModelos::ListarSedesModelo();
                                  foreach ($sedesFiltroProgramas as $sedeFiltroOpcion) {
                                      $sedeFiltroValor = htmlspecialchars($sedeFiltroOpcion, ENT_QUOTES);
                                      echo '<option value="' . $sedeFiltroValor . '">' . $sedeFiltroValor . '</option>';
                                  }
                                ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label><i class="fa fa-calendar"></i> Fecha Inicio Desde</label>
                              <input type="date" class="form-control" id="filtroFechaInicio" name="fechaInicio">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label><i class="fa fa-calendar"></i> Fecha Inicio Hasta</label>
                              <input type="date" class="form-control" id="filtroFechaFin" name="fechaFin">
                            </div>
                          </div>
                          <div class="col-md-2">
                            <div class="form-group">
                              <label>&nbsp;</label>
                              <button type="button" id="btnBuscar" class="btn btn-primary btn-block">
                                <i class="fa fa-search"></i> Buscar
                              </button>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <button type="button" id="btnLimpiar" class="btn btn-secondary btn-sm">
                              <i class="fa fa-eraser"></i> Limpiar Filtros
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botón Nuevo Programa -->
              <div class="row justify-content-md-right">
                <div class="col-lg-12">
                  <div class="kt-portlet">
                    <div class="kt-portlet__head">
                      <div class="kt-portlet__head-label">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#nprograma">
                          <i class="fa fa-plus-circle"></i> Nuevo programa
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="kt-container">
              <div class="row justify-content-md-center">
                <div class="col-lg-12">
                  <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%);">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white;">
                          <i class="fa fa-list-alt"></i> LISTA DE PROGRAMAS
                        </h3>
                      </div>
                      <div class="kt-portlet__head-toolbar">
                        <div class="kt-portlet__head-wrapper">
                          <!-- Leyenda de estados -->
                          <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <span style="color: white; font-size: 11px;">
                              <i class="fa fa-check-circle" style="color: #28a745;"></i> ACTIVO
                            </span>
                            <span style="color: white; font-size: 11px;">
                              <i class="fa fa-play-circle" style="color: #007bff;"></i> EN CURSO
                            </span>
                            <span style="color: white; font-size: 11px;">
                              <i class="fa fa-flag-checkered" style="color: #6c757d;"></i> CONCLUIDO
                            </span>
                            <span style="color: white; font-size: 11px;">
                              <i class="fa fa-times-circle" style="color: #dc3545;"></i> INACTIVO
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <form action="" method="post">
                      <div class="kt-portlet__body">
                        <div class="row mb-3">
                          <div class="col-md-4 offset-md-8">
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                              </div>
                              <input type="text" class="form-control" id="buscadorTablaProgramas" placeholder="Buscar en la tabla (nombre, sigla, código, sede, etc.)">
                            </div>
                          </div>
                        </div>
                        <div class="table-responsive">
                          <table class="table table-hover table-bordered" id="tabladetalle" style="background: white;">
                            <thead style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                              <tr>
                                <th class="text-center" style="color: white;">N°</th>
                                <th style="color: white;">PROGRAMA</th>
                                <th class="text-center" style="color: white;">GRADO ACADÉMICO</th>
                                <th class="text-center" style="color: white;">CÓDIGO</th>
                                <th class="text-center" style="color: white;">SIGLA</th>
                                <th class="text-center" style="color: white;">FECHA INICIO</th>
                                <th class="text-center" style="color: white;">SEDE</th>
                                <th class="text-center" style="color: white;">ESTADO</th>
                                <th class="text-center" style="color: white;">DETALLE</th>
                                <th class="text-center" style="color: white;">CUENTAS</th>
                                <th class="text-center" style="color: white;">EDITAR</th>
                                <th class="text-center" style="color: white;">ACTIVAR</th>
                                <th class="text-center" style="color: white;">DAR BAJA</th>
                              </tr>
                            </thead>
                            <tbody id="tablaProgramasBody">
                              <?php
                                $listaprogramas = new ProgramasControladores();
                                // Capturar filtros de búsqueda y limpiar valores vacíos
                                $grado = isset($_GET['grado']) && !empty($_GET['grado']) ? $_GET['grado'] : null;
                                $fechaInicio = isset($_GET['fechaInicio']) && !empty($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
                                $fechaFin = isset($_GET['fechaFin']) && !empty($_GET['fechaFin']) ? $_GET['fechaFin'] : null;
                                $sede = isset($_GET['sede']) && !empty($_GET['sede']) ? $_GET['sede'] : null;

                                // Debug activado - ver en el código fuente HTML (Ctrl+U)
                                echo "<!-- DEBUG VISTA: Grado='$grado', FechaInicio='$fechaInicio', FechaFin='$fechaFin', Sede='$sede' -->";
                                echo "<!-- URL actual: " . $_SERVER['REQUEST_URI'] . " -->";

                                $listaprogramas->ListaProgramaControlador($grado, $fechaInicio, $fechaFin, $sede);
                              ?>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="vistas/recursos/sweetalert.min.js"></script>
          <script>
          $(document).on('click', '#btnDetallePrograma', function() {
              var idprograma = $(this).attr("idcod");

              $.ajax({
                  url: "ajax/programa.ajax.php",
                  method: "POST",
                  data: { idprograma: idprograma },
                  dataType: "json",
                  success: function(respuesta) {
                      // Formatear costos a 2 decimales
                      var costoFormateado = parseFloat(respuesta.Costo).toFixed(2);
                      var costoMatriculaFormateado = respuesta.CostoMatricula
                          ? parseFloat(respuesta.CostoMatricula).toFixed(2)
                          : 'No especificado';

                      // Mostrar en el panel o modal
                      $("#detalleNombre").text(respuesta.NombrePrograma);
                      $("#detalleCodigo").text(respuesta.Codigo);
                      $("#detalleSigla").text(respuesta.Sigla || '-');
                      $("#detalleGrado").text(respuesta.GradoAcademico);
                      $("#detalleDuracion").text(respuesta.DuracionMeses);
                      $("#detalleModulos").text(respuesta.Modulos);
                      $("#detalleFecha").text(respuesta.FechaInicio);
                      $("#detalleSede").text(respuesta.Sede);
                      $("#detalleCosto").text(costoFormateado);
                      $("#detalleCostoMatricula").text(costoMatriculaFormateado);
                      $("#detalleDetalle").text(respuesta.Detalle);

                      // Abrir modal si no se abre automáticamente
                      $("#DetallePrograma").modal("show");
                  }
              });
          });
          </script>
            <!-- end:: Content -->

            <!-- Modal Registrar nuevo programa -->
            <div class="modal fade bd-example-modal-lg" id="nprograma" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registrar un nuevo programa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <form method="post" style="width: 100%; margin: 0 auto;">
                    <div class="modal-body">
                      <label>REGISTRE EL NUEVO PROGRAMA</label>
                      <input type="text" class="form-control" name="NombrePrograma" autocomplete="off" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>GRADO ACADÉMICO</label>
                            <select class="form-control" name="GradoAcademico" required>
                              <option value="" disabled selected>Elija el grado académico</option>
                              <option value="DIPLOMADO">DIPLOMADO</option>
                              <option value="MAESTRIA">MAESTRIA</option>
                              <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                              <option value="DOCTORADO">DOCTORADO</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>SEDE</label>
                            <select class="form-control" name="Sede" required>
                              <option value="" disabled selected>Elija la ciudad</option>
                              <option value="ORURO">ORURO</option>
                              <option value="LA PAZ">LA PAZ</option>
                              <option value="COCHABAMBA">COCHABAMBA</option>
                            </select>
                          </div>
                        </div>

                            <div class="col-md-4" >
                              <div class="modal-body">
                                  <label>DURACIÓN</label>
                                  <div class="input-group">
                                      <input type="number" class="form-control" name="DuracionMeses" required placeholder="Ej: 12">
                                      <span class="input-group-text">meses</span>
                                  </div>
                              </div>
                          </div>

                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>MÓDULOS</label>
                            <input type="number" class="form-control" name="Modulos" required placeholder="Ej: 6">
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>SIGLA</label>
                            <input type="text" class="form-control" name="Sigla" autocomplete="off" maxlength="20" placeholder="Ej: ODO-01" style="text-transform: uppercase;">
                            <p class="text-muted mb-0" style="font-size: 11px;">
                              <i class="fa fa-info-circle"></i> Identificador corto del programa. Se mostrará en el listado y en la planilla de calificaciones.
                            </p>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>VERSIÓN</label>
                            <select class="form-control" name="Version">
                              <option value="V-1" selected>V-1</option>
                              <option value="V-2">V-2</option>
                              <option value="V-3">V-3</option>
                              <option value="V-4">V-4</option>
                              <option value="V-5">V-5</option>
                              <option value="V-6">V-6</option>
                              <option value="V-7">V-7</option>
                              <option value="V-8">V-8</option>
                              <option value="V-9">V-9</option>
                              <option value="V-10">V-10</option>
                            </select>
                          </div>
                        </div>

                      </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="modal-body">
                          <label>FECHA DE INICIO</label>
                          <input type="date" class="form-control" id="FechaInicioNuevo" name="FechaInicio" required>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="modal-body">
                          <label>COSTO DEL PROGRAMA S/M</label>
                            <div class="input-group">
                              <input type="number" class="form-control" name="Costo" step="0.01" required>
                              <span class="input-group-text">Bs.</span>
                            </div>
                        </div>
                      </div>
                       <div class="col-md-4">
                        <div class="modal-body">
                          <label>COSTO DE LA MATRICULA</label>
                            <div class="input-group">
                          <input type="number" class="form-control" name="CostoMatricula" step="0.01" required>
                          <span class="input-group-text">Bs.</span>
                        </div>
                      </div>
                    </div>
                    </div>

                    <div class="modal-body">
                      <label>DETALLE</label>
                      <textarea class="form-control" name="Detalle" rows="3" placeholder="Descripción o detalle del programa"></textarea>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Guardar</button>
                      <?php
                        $registroprograma = new ProgramasControladores();
                        $registroprograma->RegistrarProgramaControlador();
                      ?>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- end:: Modal -->

            <!-- Modal Gestionar cuentas (por programa) -->
            <div class="modal fade" id="gestionarCuentas" tabindex="-1" role="dialog" aria-labelledby="gestionarCuentasLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="gestionarCuentasLabel">Administrar cuentas — <span id="cuentasProgramaNombre"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <div class="modal-body">
                    <p class="text-muted" style="font-size: 12px;">
                      <i class="fa fa-info-circle"></i> Un programa puede tener varias cuentas por tipo
                      (ej. Matrícula, Cuotas, Clínicas). La cuenta marcada como <strong>Principal</strong> de
                      cada tipo es la que se imprime automáticamente en las órdenes de pago.
                    </p>

                    <input type="hidden" id="cuentasProgramaID">

                    <form id="formRegistrarCuentaPrograma" class="form-row align-items-end mb-3">
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Tipo de cuenta</label>
                        <input type="text" class="form-control" id="inputTipoCuenta" name="TipoCuenta" list="listaTiposCuenta" placeholder="Ej: MATRICULA" required>
                        <datalist id="listaTiposCuenta"></datalist>
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Número de cuenta</label>
                        <input type="text" class="form-control" id="inputNumeroCuenta" name="NumeroCuenta" placeholder="Número de cuenta" required>
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Banco</label>
                        <input type="text" class="form-control" id="inputBancoCuenta" name="Banco" placeholder="Banco (opcional)">
                      </div>
                      <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Agregar cuenta</button>
                      </div>
                    </form>

                    <table class="table table-bordered table-sm">
                      <thead>
                        <tr>
                          <th>Tipo</th>
                          <th>N° Cuenta</th>
                          <th>Banco</th>
                          <th class="text-center">Principal</th>
                          <th class="text-center">Estado</th>
                        </tr>
                      </thead>
                      <tbody id="tablaCuentasBody">
                      </tbody>
                    </table>
                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- end:: Modal -->

            <?php
              $Footer = new FuncionesControladores();
              $Footer->FooterResumidoControlador();
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>


</body>
<style>
  /* Select2 (filtros de Grado académico y Sede): mismo alto/borde que
     .form-control de Bootstrap. El bundle del tema le pone a
     .select2-selection__rendered un padding vertical propio que, si no se
     anula en los 4 lados, sigue sumandose y el texto se sale de la caja;
     display:flex + align-items:center evita depender de un line-height
     calculado a mano. */
  .select2-container--default .select2-selection--single {
    height: calc(1.5em + .75rem + 2px) !important;
    display: flex !important;
    align-items: center;
    padding: 0 !important;
    border: 1px solid #ced4da !important;
    border-radius: .25rem !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    padding: 0 20px 0 .75rem !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    /* El bundle del tema posiciona esta flecha con "top: 50%" sin transform,
       así que queda corrida hacia arriba; top+translateY(-50%) la centra
       sin importar el alto real de la caja. */
    top: 50% !important;
    transform: translateY(-50%) !important;
    height: auto !important;
  }

  #DetallePrograma .modal-dialog {
    max-width: 95% !important;
  }

  /* Estilos para tabla de programas */
  #tabladetalle {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  #tabladetalle tbody tr {
    transition: all 0.3s ease;
  }

  #tabladetalle tbody tr:hover {
    background-color: #f8f9fa !important;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  #tabladetalle th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
  }

  #tabladetalle td {
    vertical-align: middle;
    font-size: 13px;
  }

  /* Botones mejorados */
  .btn {
    transition: all 0.3s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  /* Badges de estado */
  .badge {
    font-weight: 500;
    letter-spacing: 0.5px;
  }

  /* Filtros de búsqueda */
  .form-control {
    border-radius: 5px;
    border: 1px solid #ddd;
  }

  .form-control:focus {
    border-color: #1e3c72;
    box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
  }

  /* Estilos para input type="date" */
  input[type="date"] {
    background-color: #ffffff !important;
    color: #495057 !important;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    cursor: pointer;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer !important;
    filter: invert(0.5);
    font-size: 18px;
    padding: 5px;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
    opacity: 1 !important;
  }

  input[type="date"]::-webkit-datetime-edit {
    color: #495057 !important;
    background-color: transparent !important;
    padding: 2px;
    cursor: pointer !important;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-datetime-edit-text {
    color: #495057 !important;
    padding: 0 4px;
    cursor: pointer !important;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-datetime-edit-month-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-month-field:hover,
  input[type="date"]::-webkit-datetime-edit-month-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  input[type="date"]::-webkit-datetime-edit-day-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-day-field:hover,
  input[type="date"]::-webkit-datetime-edit-day-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  input[type="date"]::-webkit-datetime-edit-year-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-year-field:hover,
  input[type="date"]::-webkit-datetime-edit-year-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  /* Para Firefox */
  input[type="date"]::-moz-placeholder {
    color: #6c757d !important;
  }

  /* Cuando el input está vacío */
  input[type="date"]:invalid {
    color: #6c757d;
  }

  /* Asegurar que el modal no bloquee el date picker */
  .modal-body input[type="date"] {
    z-index: 1;
    position: relative;
  }

  /* Portlet mejorado */
  .kt-portlet {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  }

  .kt-portlet__head {
    border-radius: 10px 10px 0 0;
  }

  /* Animaciones */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  #tabladetalle tbody tr {
    animation: fadeIn 0.5s ease;
  }

  /* Responsive */
  @media (max-width: 768px) {
    #tabladetalle {
      font-size: 11px;
    }

    .btn-sm {
      padding: 4px 8px;
      font-size: 10px;
    }
  }
</style>

<!-- Modal Detalle Programa -->
<div class="modal fade" id="DetallePrograma" tabindex="-1" aria-labelledby="detalleProgramaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- tamaño pequeño -->
    <div class="modal-content">

      <!-- Encabezado -->
      <div class="modal-header bg-primary text-white py-2">
        <h6 class="modal-title fw-bold mb-0" id="detalleProgramaLabel">Detalle del Programa</h6>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <!-- Cuerpo -->
      <div class="modal-body p-2"> <!-- padding reducido -->
        <table class="table table-sm table-bordered mb-0">
          <tbody>
            <tr><th>Programa</th><td id="detalleNombre"></td></tr>
            <tr><th>Código</th><td id="detalleCodigo"></td></tr>
            <tr><th>Sigla</th><td id="detalleSigla"></td></tr>
            <tr><th>Grado</th><td id="detalleGrado"></td></tr>
            <tr><th>Duración</th><td><span id="detalleDuracion"></span> meses</td></tr>
            <tr><th>Módulos</th><td><span id="detalleModulos"></span> módulos</td></tr>
            <tr><th>Inicio</th><td id="detalleFecha"></td></tr>
            <tr><th>Sede</th><td id="detalleSede"></td></tr>
            <tr><th>Costo Programa</th><td><span id="detalleCosto"></span> Bs.</td></tr>
            <tr><th>Costo Matrícula</th><td><span id="detalleCostoMatricula"></span> Bs.</td></tr>
            <tr><th>Detalles</th><td id="detalleDetalle"></td></tr>
          </tbody>
        </table>
      </div>

      <!-- Pie -->
      <div class="modal-footer py-1">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- Subir -->
<div class="modal fade" id="modalsubir" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">ACTIVAR PROGRAMA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
        <div class="modal-body">
          <input type="hidden" id="idProgramaSubir" name="idProgramaSubir">

          <center>
            <h4>El programa estará disponible para matricular.</h4>
          </center>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary" id="confirmarSubir">Aceptar</button>
        </div>

        <?php
          $estado = new ProgramaEstadoControlador();
          $estado->SubirProgramaControlador();
        ?>
      </form>
    </div>
  </div>
</div>

<!-- Dar baja -->
<div class="modal fade" id="modalBorrar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">DAR DE BAJA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
    <input type="hidden"  id="idProgramaBaja" name="idProgramaBaja">
      <center>
      <h4>El programa ya no estara disponible para matricular</h4>
      </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary"  id="confirmarBaja">Aceptar</button>
	
      </div>
	  <?php
             $estadopr = new ProgramaEstadoControlador();
            $estadopr -> BajarProgramaControlador();
		?> 
	  </form>
    
    </div>
  </div>
</div>
<!-- eliminar-->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">ELIMINAR PROGRAMA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
    <input type="hidden"  id="idProgramaEliminar" name="idProgramaEliminar">
      <center>
      <h4>El programa se eliminara de la base de datos</h4>
      </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary"  id="confirmarBaja">Aceptar</button>

      </div>
	  <?php
             $estadopr = new ProgramaEstadoControlador();
            $estadopr -> BajarProgramaControlador();
		?>
	  </form>

    </div>
  </div>
</div>

<!-- Modal Editar Programa -->
<div class="modal fade bd-example-modal-lg" id="modalEditarPrograma" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar Programa</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="post" style="width: 100%; margin: 0 auto;">
        <input type="hidden" id="ProgramaIDEditar" name="ProgramaIDEditar">

        <div class="modal-body">
          <label>NOMBRE DEL PROGRAMA</label>
          <input type="text" class="form-control" id="NombreProgramaEditar" name="NombreProgramaEditar" autocomplete="off" required>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="modal-body">
              <label>GRADO ACADÉMICO</label>
              <select class="form-control" id="GradoAcademicoEditar" name="GradoAcademicoEditar" required>
                <option value="" disabled>Elija el grado académico</option>
                <option value="DIPLOMADO">DIPLOMADO</option>
                <option value="MAESTRIA">MAESTRÍA</option>
                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                <option value="DOCTORADO">DOCTORADO</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="modal-body">
              <label>SEDE</label>
              <select class="form-control" id="SedeEditar" name="SedeEditar" required>
                <option value="" disabled>Elija la ciudad</option>
                <option value="ORURO">ORURO</option>
                <option value="LA PAZ">LA PAZ</option>
                <option value="COCHABAMBA">COCHABAMBA</option>
              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>DURACIÓN </label>
              <div class="input-group">
                  <input type="number" class="form-control" id="DuracionMesesEditar" name="DuracionMesesEditar" required>
                  <span class="input-group-text">meses</span>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>MÓDULOS</label>
              <input type="number" class="form-control" id="ModulosEditar" name="ModulosEditar" required>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>SIGLA</label>
              <input type="text" class="form-control" id="SiglaEditar" name="SiglaEditar" autocomplete="off" maxlength="20" placeholder="Ej: ODO-01" style="text-transform: uppercase;">
              <p class="text-muted mb-0" style="font-size: 11px;">
                <i class="fa fa-info-circle"></i> Identificador corto del programa. Se mostrará en el listado y en la planilla de calificaciones.
              </p>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>VERSIÓN</label>
              <select class="form-control" id="VersionEditar" name="VersionEditar">
                <option value="V-1">V-1</option>
                <option value="V-2">V-2</option>
                <option value="V-3">V-3</option>
                <option value="V-4">V-4</option>
                <option value="V-5">V-5</option>
                <option value="V-6">V-6</option>
                <option value="V-7">V-7</option>
                <option value="V-8">V-8</option>
                <option value="V-9">V-9</option>
                <option value="V-10">V-10</option>
              </select>
            </div>
          </div>

        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="modal-body">
              <label>FECHA DE INICIO</label>
              <input type="date" class="form-control" id="FechaInicioEditar" name="FechaInicioEditar" required>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>COSTO DEL PROGRAMA</label>
              <div class="input-group">
                <input type="number" class="form-control" id="CostoEditar" name="CostoEditar" step="0.01" required>
                <span class="input-group-text">Bs.</span>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>COSTO DE LA MATRÍCULA</label>
              <div class="input-group">
                <input type="number" class="form-control" id="CostoMatriculaEditar" name="CostoMatriculaEditar" step="0.01" required>
                <span class="input-group-text">Bs.</span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-body">
          <label>DETALLE</label>
          <textarea class="form-control" id="DetalleEditar" name="DetalleEditar" rows="3"></textarea>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Actualizar</button>
          <?php
            $actualizarPrograma = new ProgramasControladores();
            $actualizarPrograma->ActualizarProgramaControlador();
          ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// JavaScript para búsqueda de programas
$(document).ready(function() {
    // Botón Buscar
    $('#btnBuscar').click(function() {
        var grado = $('#filtroGrado').val();
        var sede = $('#filtroSede').val();
        var fechaInicio = $('#filtroFechaInicio').val();
        var fechaFin = $('#filtroFechaFin').val();

        // Construir URL con parámetros - USAR index.php?action=
        var url = 'index.php?action=programas';
        var params = [];

        if (grado && grado !== '') {
            params.push('grado=' + encodeURIComponent(grado));
        }
        if (sede && sede !== '') {
            params.push('sede=' + encodeURIComponent(sede));
        }
        if (fechaInicio && fechaInicio !== '') {
            params.push('fechaInicio=' + encodeURIComponent(fechaInicio));
        }
        if (fechaFin && fechaFin !== '') {
            params.push('fechaFin=' + encodeURIComponent(fechaFin));
        }

        if (params.length > 0) {
            url += '&' + params.join('&');
        }

        window.location.href = url;
    });

    // Buscador de texto libre sobre la tabla ya cargada (filtra por cualquier patrón, en cualquier columna)
    $('#buscadorTablaProgramas').on('keyup', function() {
        var patron = $(this).val().trim().toLowerCase();
        $('#tablaProgramasBody tr').each(function() {
            var textoFila = $(this).text().toLowerCase();
            $(this).toggle(textoFila.indexOf(patron) !== -1);
        });
    });

    // Permitir buscar con Enter
    $('#filtroGrado, #filtroSede, #filtroFechaInicio, #filtroFechaFin').keypress(function(e) {
        if (e.which == 13) { // Enter key
            $('#btnBuscar').click();
            return false;
        }
    });

    // Botón Limpiar
    $('#btnLimpiar').click(function() {
        $('#filtroGrado').val('');
        $('#filtroSede').val('');
        $('#filtroFechaInicio').val('');
        $('#filtroFechaFin').val('');
        window.location.href = 'index.php?action=programas';
    });

    // Mantener valores de filtros después de búsqueda
    var urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('grado')) {
        $('#filtroGrado').val(urlParams.get('grado'));
    }
    if (urlParams.has('sede')) {
        $('#filtroSede').val(urlParams.get('sede'));
    }
    if (urlParams.has('fechaInicio')) {
        $('#filtroFechaInicio').val(urlParams.get('fechaInicio'));
    }
    if (urlParams.has('fechaFin')) {
        $('#filtroFechaFin').val(urlParams.get('fechaFin'));
    }

    // Si la página se recargó después de una acción dentro de un modal (ej: registrar/dar de baja
    // una cuenta), reabrir ese mismo modal en vez de dejar al usuario en la lista principal
    if (urlParams.has('panel')) {
        var panelAReabrir = '#' + urlParams.get('panel');
        if ($(panelAReabrir).length) {
            $(panelAReabrir).modal('show');
        }
    }

    // Select2 (mismo tamaño/estilo en Grado académico y Sede). Se inicializa
    // después de restaurar los valores desde la URL para que ya se muestren
    // seleccionados desde el primer render.
    $('.select2-uniforme').select2({ width: '100%' });
});

// JavaScript para cargar datos en el modal de edición
$(document).on('click', '.btnEditarPrograma', function() {
    var idprograma = $(this).data('idprograma');
    var nombre = $(this).data('nombre');
    var grado = $(this).data('grado');
    var sigla = $(this).data('sigla');
    var duracion = $(this).data('duracion');
    var modulos = $(this).data('modulos');
    var fecha = $(this).data('fecha');
    var sede = $(this).data('sede');
    var costo = $(this).data('costo');
    var costomatricula = $(this).data('costomatricula');
    var detalle = $(this).data('detalle');
    var version = $(this).data('version');

    // Formatear costos a 2 decimales
    var costoFormateado = parseFloat(costo).toFixed(2);
    var costoMatriculaFormateado = costomatricula ? parseFloat(costomatricula).toFixed(2) : '';

    $('#ProgramaIDEditar').val(idprograma);
    $('#NombreProgramaEditar').val(nombre);
    $('#GradoAcademicoEditar').val(grado);
    $('#SiglaEditar').val(sigla || '');
    $('#DuracionMesesEditar').val(duracion);
    $('#ModulosEditar').val(modulos);
    $('#FechaInicioEditar').val(fecha);
    $('#SedeEditar').val(sede);
    $('#CostoEditar').val(costoFormateado);
    $('#CostoMatriculaEditar').val(costoMatriculaFormateado);
    $('#DetalleEditar').val(detalle);
    $('#VersionEditar').val(version || 'V-1');
});

// JavaScript para modal de subir programa
$(document).on('click', '.btnSubir', function() {
    var idprograma = $(this).data('idprograma');
    $('#idProgramaSubir').val(idprograma);
});

// JavaScript para modal de dar de baja
$(document).on('click', '.btnDarBaja', function() {
    var idprograma = $(this).data('idprograma');
    $('#idProgramaBaja').val(idprograma);
});

// Manejo correcto de modales de Bootstrap apilados (Gestionar cuentas se abre
// sobre Nuevo programa). Sin esto, al cerrar el modal hijo Bootstrap quita
// "modal-open" del body aunque el padre siga abierto y la pantalla queda bloqueada.
$(document).on('show.bs.modal', '.modal', function() {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
        $('.modal-backdrop').not('.modal-stack-fixed').css('z-index', zIndex - 1).addClass('modal-stack-fixed');
    }, 0);
});
$(document).on('hidden.bs.modal', '.modal', function() {
    if ($('.modal:visible').length) {
        $('body').addClass('modal-open');
    }
});

// Gestión de cuentas POR PROGRAMA: un programa puede tener varias cuentas
// clasificadas por tipo (Matrícula, Cuotas, Clínicas...). Todo vía AJAX,
// para no recargar la página ni romper el modal apilado.
function refrescarTablaCuentasPrograma() {
    var programaID = $('#cuentasProgramaID').val();
    if (!programaID) { return; }

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'listarHtml', ProgramaID: programaID },
        success: function(html) {
            $('#tablaCuentasBody').html(html);
        }
    });
}

function refrescarTiposCuentaDatalist() {
    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'listarTiposHtml' },
        success: function(html) {
            $('#listaTiposCuenta').html(html);
        }
    });
}

// Abrir el modal de cuentas desde el botón de la fila del programa
$(document).on('click', '.btnAbrirCuentasPrograma', function() {
    var programaID = $(this).data('idprograma');
    var nombrePrograma = $(this).data('nombreprograma');

    $('#cuentasProgramaID').val(programaID);
    $('#cuentasProgramaNombre').text(nombrePrograma);
    $('#formRegistrarCuentaPrograma')[0].reset();

    refrescarTablaCuentasPrograma();
    refrescarTiposCuentaDatalist();
});

$('#formRegistrarCuentaPrograma').on('submit', function(e) {
    e.preventDefault();

    var programaID = $('#cuentasProgramaID').val();
    var tipoCuenta = $('#inputTipoCuenta').val();
    var numeroCuenta = $('#inputNumeroCuenta').val();
    var banco = $('#inputBancoCuenta').val();

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'registrar', ProgramaID: programaID, TipoCuenta: tipoCuenta, NumeroCuenta: numeroCuenta, Banco: banco },
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.estado === 'exitoso') {
                $('#formRegistrarCuentaPrograma')[0].reset();
                refrescarTablaCuentasPrograma();
                refrescarTiposCuentaDatalist();
            }
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});

$(document).on('click', '.btnCambiarEstadoCuentaPrograma', function() {
    var idcuenta = $(this).data('idcuenta');
    var nuevoestado = $(this).data('nuevoestado');

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'cambiarEstado', ProgramaCuentaID: idcuenta, NuevoEstado: nuevoestado },
        dataType: 'json',
        success: function(respuesta) {
            refrescarTablaCuentasPrograma();
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});

$(document).on('click', '.btnMarcarPrincipalCuenta', function() {
    var idcuenta = $(this).data('idcuenta');

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'marcarPrincipal', ProgramaCuentaID: idcuenta },
        dataType: 'json',
        success: function(respuesta) {
            refrescarTablaCuentasPrograma();
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});
</script>

