<?php
$Validar = new FuncionesControladores();
$Validar -> ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
         <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
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
                  <h2 class="">Generación de Reportes</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>Reportes PDF</h3>
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
<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-body">
      <section id="html">
        <div class="row">

          <!-- Reporte 1: Lista de Estudiantes -->
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
              <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-users"></i> Lista de Estudiantes
                </h4>
              </div>
              <div class="card-body" style="padding: 30px;">
                <div class="text-center mb-3">
                  <i class="fas fa-file-pdf" style="font-size: 60px; color: #667eea;"></i>
                </div>
                <p class="text-center" style="color: #666; min-height: 60px;">
                  Genera un reporte completo de todos los estudiantes activos con sus datos personales y de contacto.
                </p>
                <form action="ajax/reportes.ajax.php" method="POST" target="_blank">
                  <input type="hidden" name="tipo_reporte" value="estudiantes">
                  <button type="submit" class="btn btn-primary btn-block" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px;">
                    <i class="fas fa-download"></i> Generar Reporte
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Reporte 2: Estudiantes por Programa -->
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
              <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 20px;">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-graduation-cap"></i> Por Programa
                </h4>
              </div>
              <div class="card-body" style="padding: 30px;">
                <div class="text-center mb-3">
                  <i class="fas fa-file-pdf" style="font-size: 60px; color: #28a745;"></i>
                </div>
                <p class="text-center" style="color: #666; min-height: 60px;">
                  Reporte de estudiantes organizados por programa académico, con fecha de inscripción.
                </p>
                <form action="ajax/reportes.ajax.php" method="POST" target="_blank">
                  <input type="hidden" name="tipo_reporte" value="por_programa">
                  <div class="form-group">
                    <label for="programa_id">Programa (opcional):</label>
                    <select name="programa_id" id="programa_id" class="form-control">
                      <option value="">Todos los programas</option>
                      <?php
                        $programas = ReportesModelos::ListaProgramas();
                        foreach ($programas as $prog) {
                          echo '<option value="' . $prog['ProgramaID'] . '">' . $prog['NombrePrograma'] . '</option>';
                        }
                      ?>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-success btn-block" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 12px;">
                    <i class="fas fa-download"></i> Generar Reporte
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Reporte 3: Módulos y Matriculados -->
          <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
              <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); padding: 20px;">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-book"></i> Módulos
                </h4>
              </div>
              <div class="card-body" style="padding: 30px;">
                <div class="text-center mb-3">
                  <i class="fas fa-file-pdf" style="font-size: 60px; color: #17a2b8;"></i>
                </div>
                <p class="text-center" style="color: #666; min-height: 60px;">
                  Reporte de módulos con información de matriculados, pagos realizados y pendientes.
                </p>
                <form action="ajax/reportes.ajax.php" method="POST" target="_blank">
                  <input type="hidden" name="tipo_reporte" value="modulos">
                  <button type="submit" class="btn btn-info btn-block" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border: none; padding: 12px;">
                    <i class="fas fa-download"></i> Generar Reporte
                  </button>
                </form>
              </div>
            </div>
          </div>

        </div>

        <!-- Información adicional -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
              <div class="card-body" style="padding: 25px;">
                <h5 style="color: #667eea;">
                  <i class="fas fa-info-circle"></i> Información sobre los Reportes
                </h5>
                <hr>
                <div class="row">
                  <div class="col-md-4">
                    <h6><i class="fas fa-check-circle text-success"></i> Formato</h6>
                    <p style="color: #666; font-size: 14px;">
                      Todos los reportes se generan en formato PDF profesional, listos para imprimir o compartir.
                    </p>
                  </div>
                  <div class="col-md-4">
                    <h6><i class="fas fa-check-circle text-success"></i> Contenido</h6>
                    <p style="color: #666; font-size: 14px;">
                      Los reportes incluyen solo información de registros activos en el sistema.
                    </p>
                  </div>
                  <div class="col-md-4">
                    <h6><i class="fas fa-check-circle text-success"></i> Actualización</h6>
                    <p style="color: #666; font-size: 14px;">
                      Los datos se generan en tiempo real, mostrando la información más actualizada.
                    </p>
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
  .card.shadow-sm {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card.shadow-sm:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0,0,0,0.2) !important;
  }

  .btn {
    transition: all 0.3s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }

  .form-control {
    border: 2px solid #e9ecef;
    border-radius: 5px;
  }

  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }
</style>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
?>

<script>
// Mostrar fecha actual
function actualizarFecha() {
    const opciones = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    document.getElementById('lafecha').textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
}
actualizarFecha();
setInterval(actualizarFecha, 60000);
</script>

</body>
