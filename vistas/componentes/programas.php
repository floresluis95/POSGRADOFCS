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
              <div class="row justify-content-md-right">
                <div class="col-lg-12">
                  <div class="kt-portlet">
                    <div class="kt-portlet__head">
                      <div class="kt-portlet__head-label">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#nprograma">
                          <i class="kt-menu__link-icon flaticon-add"></i> Nuevo programa
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
                    <form action="" method="post">
                      <div class="kt-portlet__head">
                        <table class="table" id="tabladetalle">
                          <thead>
                            <tr>
                              <th>N°</th>
                              <th style="width:400px;">PROGRAMA</th>
                              <th style="width:200px;">GRADO ACADÉMICO</th>    
                              <th style="width:200px;">CODIGO</th>  
                              <th style="width:150px;">FECHA DE INICIO</th>
                              <th style="width:150px;">SEDE</th>
                              <th style="width:150px;">ESTADO</th>
                              <th>DETALLE</th>
                              <th>SUBIR</th>
                              <th>BAJAR</th>
                             
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                              $listaprogramas = new ProgramasControladores();
                              $listaprogramas->ListaProgramaControlador();
                            ?>
                          </tbody>
                        </table>
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
                      $("#detalleGrado").text(respuesta.GradoAcademico);
                      $("#detalleDuracion").text(respuesta.DuracionMeses);
                      $("#detalleModulos").text(respuesta.Modulos);
                      $("#detalleFecha").text(respuesta.FechaInicio);
                      $("#detalleSede").text(respuesta.Sede);
                      $("#detalleCosto").text(respuesta.Costo);
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
                      <div class="col-md-4">
                        <div class="modal-body">
                          <?php $fecha_minima = date('Y-m-d'); ?>
                          <label>FECHA DE INICIO</label>
                          <input type="date" class="form-control" name="FechaInicio" min="<?php echo $fecha_minima; ?>" required>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="modal-body">
                          <label>COSTO DEL PROGRAMA</label>
                          <input type="number" class="form-control" name="Costo" step="0.01" required>
                        </div>
                      </div>
                       <div class="col-md-4">
                        <div class="modal-body">
                          <label>COSTO DE LA MATRICULA</label>
                          <input type="number" class="form-control" name="Costom" step="0.01" required>
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
            <tr><th>Grado</th><td id="detalleGrado"></td></tr>
             
            <tr><th>Duración</th><td><span id="detalleDuracion"></span> meses</td></tr>
            <tr><th>Módulos</th><td><span id="detalleModulos"></span> módulos</td></tr>
            <tr><th>Inicio</th><td id="detalleFecha"></td></tr>
            
            <tr><th>Sede</th><td id="detalleSede"></td></tr>
            <tr><th>Costo</th><td><span id="detalleCosto"></span> Bs.</td></tr>
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

