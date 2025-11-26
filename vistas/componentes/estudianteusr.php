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
                  <h2 class="">Gestión de Usuarios</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>Asignar Usuarios a Estudiantes</h3>
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
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-gradient-info">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-users"></i> Estudiantes sin Usuario Asignado
                </h4>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="kt_table_1" style="border-collapse: separate; border-spacing: 0;">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-id-card"></i> C.I.
                          </th>
                          <th style="width: 40%;">
                            <i class="fas fa-user"></i> Nombre Completo
                          </th>
                          <th style="width: 30%;">
                            <i class="fas fa-envelope"></i> Correo
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-cog"></i> Acciones
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $ListaEstudiantes = new UsuarioControladores();
                          $ListaEstudiantes -> ListaEstudiantesSinUsuarioControlador();
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 15px;
    font-size: 14px;
  }

  .table tbody tr {
    transition: all 0.3s ease;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  .table tbody td {
    padding: 12px 15px;
    vertical-align: middle;
    border-color: #e9ecef;
  }

  .card.shadow-sm {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    border: none;
    border-radius: 10px;
    overflow: hidden;
  }

  .card-header.bg-gradient-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border: none;
  }

  .btn-asignar {
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .btn-asignar:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
  }
</style>

<!-- Modal Asignar Usuario -->
<div style="z-index: 1500;" class="modal fade" id="ModalAsignarUsuario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
        <h4 class="modal-title text-white" id="myModalLabel">
          <i class="fas fa-user-plus mr-2"></i> Asignar Usuario a Estudiante
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <div class="modal-body" style="padding: 30px;">

          <div class="alert alert-info" style="border-left: 4px solid #17a2b8; background-color: #d1ecf1;">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Credenciales que se generarán automáticamente:</strong>
            <ul class="mb-0 mt-2">
              <li><strong>Usuario:</strong> Número de Carnet (C.I.)</li>
              <li><strong>Contraseña:</strong> Primera letra del nombre + Número de Carnet</li>
            </ul>
          </div>

          <input type="hidden" id="estudiante_ci" name="estudiante_ci">
          <input type="hidden" id="estudiante_nombre" name="estudiante_nombre">

          <div class="form-group">
            <label for="nombre_estudiante_display" class="font-weight-bold">
              <i class="fas fa-user text-primary"></i> Estudiante Seleccionado
            </label>
            <input type="text" id="nombre_estudiante_display" class="form-control form-control-lg" readonly
                   style="background-color: #f8f9fa; border: 2px solid #e9ecef; font-weight: 600;">
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="ci_display" class="font-weight-bold">
                  <i class="fas fa-id-card text-info"></i> C.I.
                </label>
                <input type="text" id="ci_display" class="form-control" readonly
                       style="background-color: #f8f9fa; border: 2px solid #e9ecef;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="usuario_generado" class="font-weight-bold">
                  <i class="fas fa-user-circle text-success"></i> Usuario
                </label>
                <input type="text" id="usuario_generado" class="form-control" readonly
                       style="background-color: #f8f9fa; border: 2px solid #e9ecef;">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="password_generada" class="font-weight-bold">
              <i class="fas fa-key text-warning"></i> Contraseña Generada
            </label>
            <input type="text" id="password_generada" class="form-control" readonly
                   style="background-color: #fff3cd; border: 2px solid #ffc107; font-weight: 600; font-size: 16px;">
          </div>

          <div class="form-group">
            <label for="correo_estudiante" class="font-weight-bold">
              <i class="fas fa-envelope text-danger"></i> Correo Electrónico
            </label>
            <input type="email" id="correo_estudiante" class="form-control" readonly
                   style="background-color: #f8f9fa; border: 2px solid #e9ecef;">
          </div>

        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success" style="min-width: 150px;">
            <i class="fas fa-check"></i> Confirmar y Crear
          </button>
        </div>
      </form>
      <?php
        $crearUsuario = new UsuarioControladores();
        $crearUsuario -> CrearUsuarioEstudianteControlador();
      ?>
    </div>
  </div>
</div>

<!-- Scripts de Asignación de Usuario -->
<script>
// Función que se ejecuta cuando jQuery está disponible
function inicializarModalAsignarUsuario() {
  if (typeof jQuery === 'undefined') {
    console.log('jQuery no está cargado aún, reintentando...');
    setTimeout(inicializarModalAsignarUsuario, 100);
    return;
  }

  console.log('✅ jQuery cargado - Inicializando modal de asignación de usuario');

  // Capturar datos del estudiante al hacer clic en el botón
  jQuery(document).on('click', '.btnAsignarUsuario', function(){
    // Obtener todos los atributos data del botón
    var ci = jQuery(this).attr('data-ci');
    var ciCompleto = jQuery(this).attr('data-ci-completo');
    var nombreCompleto = jQuery(this).attr('data-nombre-completo');
    var nombrePila = jQuery(this).attr('data-nombre-pila');
    var correo = jQuery(this).attr('data-correo');

    console.log('📋 Datos capturados:', {
      ci: ci,
      ciCompleto: ciCompleto,
      nombreCompleto: nombreCompleto,
      nombrePila: nombrePila,
      correo: correo
    });

    // Obtener la primera letra del nombre en mayúscula
    var primeraLetra = nombrePila ? nombrePila.charAt(0).toUpperCase() : 'X';

    // Generar usuario (será el CI)
    var usuario = ci;

    // Generar contraseña (primera letra del nombre + CI)
    var password = primeraLetra + ci;

    // Llenar campos hidden que se enviarán al servidor
    jQuery('#estudiante_ci').val(ci);
    jQuery('#estudiante_nombre').val(nombrePila);

    // Llenar campos de visualización en el modal
    jQuery('#nombre_estudiante_display').val(nombreCompleto);
    jQuery('#ci_display').val(ciCompleto);
    jQuery('#usuario_generado').val(usuario);
    jQuery('#password_generada').val(password);
    jQuery('#correo_estudiante').val(correo || 'No registrado');

    console.log('✅ Modal actualizado con:', {
      usuario: usuario,
      password: password
    });
  });

  console.log('✅ Event listener configurado para botones .btnAsignarUsuario');
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', inicializarModalAsignarUsuario);
} else {
  inicializarModalAsignarUsuario();
}
</script>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
?>

</body>
