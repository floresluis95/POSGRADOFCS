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
                  <h3>PROGRAMAS</h3>
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
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white;">
                          <i class="fa fa-filter"></i> FILTROS DE BÚSQUEDA
                        </h3>
                      </div>
                    </div>
                    <div class="kt-portlet__body">
                      <form id="formBusquedaProgramas" method="GET">
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label><i class="fa fa-graduation-cap"></i> Grado Académico</label>
                              <select class="form-control" id="filtroGrado" name="grado">
                                <option value="">Todos los grados</option>
                                <option value="DIPLOMADO">DIPLOMADO</option>
                                <option value="MAESTRIA">MAESTRÍA</option>
                                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label><i class="fa fa-calendar"></i> Fecha Inicio Desde</label>
                              <input type="date" class="form-control" id="filtroFechaInicio" name="fechaInicio">
                            </div>
                          </div>
                          <div class="col-md-3">
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
                        <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#nprograma">
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
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
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
                        <div class="table-responsive">
                          <table class="table table-hover table-bordered" id="tabladetalle" style="background: white;">
                            <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                              <tr>
                                <th class="text-center" style="color: white;">N°</th>
                                <th style="color: white;">PROGRAMA</th>
                                <th class="text-center" style="color: white;">GRADO ACADÉMICO</th>
                                <th class="text-center" style="color: white;">CÓDIGO</th>
                                <th class="text-center" style="color: white;">FECHA INICIO</th>
                                <th class="text-center" style="color: white;">SEDE</th>
                                <th class="text-center" style="color: white;">ESTADO</th>
                                <th class="text-center" style="color: white;">DETALLE</th>
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

                                // Debug activado - ver en el código fuente HTML (Ctrl+U)
                                echo "<!-- DEBUG VISTA: Grado='$grado', FechaInicio='$fechaInicio', FechaFin='$fechaFin' -->";
                                echo "<!-- URL actual: " . $_SERVER['REQUEST_URI'] . " -->";

                                $listaprogramas->ListaProgramaControlador($grado, $fechaInicio, $fechaFin);
                              ?>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <center>
                        <a href="extensiones/tcpdf/pdf/pdfnotacil.php?codigo=6" class="btn btn-primary" target="_blank">
                          <i class="fa fa-print" aria-hidden="true"></i> REPORTE DE PROGRAMAS
                        </a>
                      </center>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
          <script>
          $(document).on('click', '#btnDetallePrograma', function() {
              var idprograma = $(this).attr("idcod");

              $.ajax({
                  url: "ajax/programa.ajax.php",
                  method: "POST",
                  data: { idprograma: idprograma },
                  dataType: "json",
                  success: function(respuesta) {
                      // Mostrar en el panel o modal
                      $("#detalleNombre").text(respuesta.NombrePrograma);
                      $("#detalleCodigo").text(respuesta.Codigo);
                      $("#detalleGrado").text(respuesta.GradoAcademico);
                      $("#detalleDuracion").text(respuesta.DuracionMeses);
                      $("#detalleModulos").text(respuesta.Modulos);
                      $("#detalleFecha").text(respuesta.FechaInicio);
                      $("#detalleSede").text(respuesta.Sede);
                      $("#detalleCosto").text(respuesta.Costo);
                      $("#detalleCostoMatricula").text(respuesta.CostoMatricula || 'No especificado');
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
                              <option value="MAESTRIA">MAESTRÍA</option>
                              <option value="ESPECIALIDAD">ESPECIALIDAD</option>
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

                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>DURACIÓN (meses)</label>
                            <input type="number" class="form-control" name="DuracionMeses" required placeholder="Ej: 12">
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="modal-body">
                            <label>MÓDULOS</label>
                            <input type="number" class="form-control" name="Modulos" required placeholder="Ej: 6">
                          </div>
                        </div>
                      </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="modal-body">
                          <?php $fecha_minima = date('Y-m-d'); ?>
                          <label>FECHA DE INICIO</label>
                          <input type="date" class="form-control" name="FechaInicio" min="<?php echo $fecha_minima; ?>" required>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="modal-body">
                          <label>COSTO TOTAL DEL MODULO</label>
                          <input type="number" class="form-control" name="Costo" step="0.01" required>
                        </div>
                      </div>
                       <div class="col-md-4">
                        <div class="modal-body">
                          <label>COSTO DE LA MATRICULA</label>
                          <input type="number" class="form-control" name="CostoMatricula" step="0.01" required>
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
            
            <?php
              $Footer = new FuncionesControladores();
              $Footer->FooterControlador();
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>


</body>
<style>
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
              <label>DURACIÓN (meses)</label>
              <input type="number" class="form-control" id="DuracionMesesEditar" name="DuracionMesesEditar" required>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>MÓDULOS</label>
              <input type="number" class="form-control" id="ModulosEditar" name="ModulosEditar" required>
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
              <input type="number" class="form-control" id="CostoEditar" name="CostoEditar" step="0.01" required>
            </div>
          </div>

          <div class="col-md-4">
            <div class="modal-body">
              <label>COSTO DE LA MATRÍCULA</label>
              <input type="number" class="form-control" id="CostoMatriculaEditar" name="CostoMatriculaEditar" step="0.01" required>
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
        var fechaInicio = $('#filtroFechaInicio').val();
        var fechaFin = $('#filtroFechaFin').val();

        // Debug en consola
        console.log('Búsqueda iniciada:');
        console.log('Grado:', grado);
        console.log('Fecha Inicio:', fechaInicio);
        console.log('Fecha Fin:', fechaFin);

        // Construir URL con parámetros
        var url = 'programas';
        var params = [];

        if (grado && grado !== '') {
            params.push('grado=' + encodeURIComponent(grado));
        }
        if (fechaInicio && fechaInicio !== '') {
            params.push('fechaInicio=' + encodeURIComponent(fechaInicio));
        }
        if (fechaFin && fechaFin !== '') {
            params.push('fechaFin=' + encodeURIComponent(fechaFin));
        }

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        console.log('URL final:', url);
        window.location.href = url;
    });

    // Permitir buscar con Enter
    $('#filtroGrado, #filtroFechaInicio, #filtroFechaFin').keypress(function(e) {
        if (e.which == 13) { // Enter key
            $('#btnBuscar').click();
            return false;
        }
    });

    // Botón Limpiar
    $('#btnLimpiar').click(function() {
        $('#filtroGrado').val('');
        $('#filtroFechaInicio').val('');
        $('#filtroFechaFin').val('');
        window.location.href = 'programas';
    });

    // Mantener valores de filtros después de búsqueda
    var urlParams = new URLSearchParams(window.location.search);
    console.log('Parámetros URL actuales:', window.location.search);

    if (urlParams.has('grado')) {
        var gradoParam = urlParams.get('grado');
        console.log('Restaurando grado:', gradoParam);
        $('#filtroGrado').val(gradoParam);
    }
    if (urlParams.has('fechaInicio')) {
        var fechaInicioParam = urlParams.get('fechaInicio');
        console.log('Restaurando fechaInicio:', fechaInicioParam);
        $('#filtroFechaInicio').val(fechaInicioParam);
    }
    if (urlParams.has('fechaFin')) {
        var fechaFinParam = urlParams.get('fechaFin');
        console.log('Restaurando fechaFin:', fechaFinParam);
        $('#filtroFechaFin').val(fechaFinParam);
    }
});

// JavaScript para cargar datos en el modal de edición
$(document).on('click', '.btnEditarPrograma', function() {
    var idprograma = $(this).data('idprograma');
    var nombre = $(this).data('nombre');
    var grado = $(this).data('grado');
    var duracion = $(this).data('duracion');
    var modulos = $(this).data('modulos');
    var fecha = $(this).data('fecha');
    var sede = $(this).data('sede');
    var costo = $(this).data('costo');
    var costomatricula = $(this).data('costomatricula');
    var detalle = $(this).data('detalle');

    $('#ProgramaIDEditar').val(idprograma);
    $('#NombreProgramaEditar').val(nombre);
    $('#GradoAcademicoEditar').val(grado);
    $('#DuracionMesesEditar').val(duracion);
    $('#ModulosEditar').val(modulos);
    $('#FechaInicioEditar').val(fecha);
    $('#SedeEditar').val(sede);
    $('#CostoEditar').val(costo);
    $('#CostoMatriculaEditar').val(costomatricula);
    $('#DetalleEditar').val(detalle);
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
</script>

