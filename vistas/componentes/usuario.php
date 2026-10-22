<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
<!-- Estilos compartidos de Estudiantes, Docentes y Usuarios (antes un <style> copiado en cada vista).     Van al inicio para que la página no se vea sin estilo al cargar. --><link rel="stylesheet" href="vistas/recursos/assets/css/gestion-registros.css?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/css/gestion-registros.css'); ?>">
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
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>GESTIÓN DE USUARIOS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>PERSONAL DEL SISTEMA</h3>
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
            <div class="card shadow-sm">
              <div class="card-header bg-gradient-primary">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-users"></i> Usuarios del Sistema
                </h4>
                <div class="heading-elements">
                  <button data-toggle="modal" data-target="#ModalInsertarUsuario" type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus mr-1"></i>Nuevo Usuario
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle TablaUsuarios" id="kt_table_1" data-exportar="Usuarios">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 5%;">Nº</th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-id-card"></i> C.I.
                          </th>
                          <th style="width: 25%;">
                            <i class="fas fa-user"></i> Nombre Completo
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-user-circle"></i> Usuario
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-key"></i> Contraseña
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-phone"></i> Celular
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-calendar"></i> Ingreso
                          </th>
                          <th class="text-center" style="width: 12%;">
                            <i class="fas fa-briefcase"></i> Cargo
                          </th>
                          <th class="text-center" style="width: 8%;">
                            <i class="fas fa-toggle-on"></i> Estado
                          </th>
                          <th class="text-center" style="width: 8%;">
                            <i class="fas fa-cog"></i> Acciones
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $ListaUsuarios = new UsuarioControladores();
                          $ListaUsuarios -> ListaUsuariosControlador();
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


<!-- Modal Insertar Usuario -->
<div style="z-index: 1500;" class="modal fade" id="ModalInsertarUsuario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header bg-gradient-info">
        <h4 class="modal-title text-white" id="myModalLabel">
          <i class="fas fa-user-plus mr-2"></i> Registrar Nuevo Usuario
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data" class="needs-validation">
        <div class="modal-body" style="padding: 30px;">

          <h4 class="form-section">
            <i class="fas fa-user mr-2"></i> Datos Personales
          </h4>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="validationCustum01">
                  <i class="fas fa-id-card text-primary"></i> Cédula de Identidad *
                </label>
                <input type="text" id="validationCustum01" name="ci" class="form-control"
                       placeholder="Ej: 1234567" required
                       onkeypress='return event.charCode >= 48 && event.charCode <= 57'
                       title="Solo números">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="validationCustum02">
                  <i class="fas fa-user text-primary"></i> Apellido Paterno *
                </label>
                <input type="text" id="validationCustum02" name="apaterno" class="form-control"
                       placeholder="Ej: García" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+"
                       title="Solo letras">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="validationCustum03">
                  <i class="fas fa-user text-primary"></i> Apellido Materno *
                </label>
                <input type="text" id="validationCustum03" name="amaterno" class="form-control"
                       placeholder="Ej: López" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+"
                       title="Solo letras">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="validationCustum04">
                  <i class="fas fa-user text-primary"></i> Nombres Completos *
                </label>
                <input type="text" id="validationCustum04" name="nombres" class="form-control"
                       placeholder="Ej: Juan Carlos" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ ]+"
                       title="Solo letras">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="validationCustum05">
                  <i class="fas fa-map-marker-alt text-primary"></i> Dirección *
                </label>
                <input type="text" id="validationCustum05" name="direccion" class="form-control"
                       placeholder="Ej: Av. Principal #123" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="timesheetinput1">
                  <i class="fas fa-mobile-alt text-success"></i> Celular
                </label>
                <input type="text" id="timesheetinput1" name="celular" class="form-control"
                       placeholder="Ej: 70123456">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="timesheetinput2">
                  <i class="fas fa-phone text-success"></i> Teléfono
                </label>
                <input type="text" id="timesheetinput2" name="telefono" class="form-control"
                       placeholder="Ej: 4123456">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="UICorreo">
                  <i class="fas fa-envelope text-success"></i> Correo electrónico *
                </label>
                <input type="email" id="UICorreo" name="correo" class="form-control"
                       placeholder="Ej: juan.garcia@gmail.com" required maxlength="150">
                <small class="text-muted">Aquí recibirá el código si olvida su contraseña</small>
              </div>
            </div>
          </div>

          <h4 class="form-section">
            <i class="fas fa-user-lock mr-2"></i> Credenciales de Acceso
          </h4>

          <div class="alert alert-info" style="border-left: 4px solid #17a2b8; background-color: #d1ecf1;">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Nota:</strong> Escriba la contraseña del usuario: mínimo 8 caracteres, con al menos una letra y un número. No se podrá volver a ver; si se olvida, el administrador debe restablecerla.
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="validationCustum06">
                  <i class="fas fa-user-circle text-warning"></i> Nombre de Usuario *
                </label>
                <input type="text" id="validationCustum06" name="usuario" class="form-control"
                       placeholder="Ej: juangarcia" required>
                <small class="text-muted">Este será el nombre de usuario para acceder al sistema</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="validationCustum07">
                  <i class="fas fa-briefcase text-danger"></i> Cargo/Rol *
                </label>
                <select class="form-control" name="UICargo" id="validationCustum07" required>
                  <option disabled selected value="">Seleccione un cargo...</option>
                  <option value="ADM">ADMINISTRADOR</option>
                  <option value="SEC">SECRETARIA</option>
                  <option value="AUX">AUXILIAR</option>

                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="UIPassword">
                  <i class="fas fa-key text-warning"></i> Contraseña *
                </label>
                <input type="password" id="UIPassword" name="password" class="form-control"
                       minlength="8" autocomplete="new-password" required
                       pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
                       title="Mínimo 8 caracteres, con al menos una letra y un número">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="UIPasswordConfirmar">
                  <i class="fas fa-key text-warning"></i> Confirmar contraseña *
                </label>
                <input type="password" id="UIPasswordConfirmar" name="password_confirmar" class="form-control"
                       minlength="8" autocomplete="new-password" required>
                <small id="UIPasswordAviso" class="text-danger" style="display:none;">Las contraseñas no coinciden</small>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success" style="min-width: 120px;">
            <i class="fas fa-save"></i> Guardar
          </button>
        </div>
      </form>
      <?php
        $insusuario = new UsuarioControladores();
        $insusuario -> InsertarUsuarioControlador();
      ?>
    </div>
  </div>
</div>

<!-- Modal Editar Usuario -->
<div style="z-index: 1500;" class="modal fade" id="ModalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header bg-gradient-info">
        <h4 class="modal-title text-white" id="myModalLabel2">
          <i class="fas fa-edit mr-2"></i> Editar Información de Usuario
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data" id="formEditarUsuario">
        <div class="modal-body" style="padding: 30px;">

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="UECedulaIdentidad">
                  <i class="fas fa-id-card text-primary"></i> C.I.
                </label>
                <input type="hidden" id="UEIdPersonal" name="editaridpersonal">
                <input type="hidden" id="UEIdUsuario" name="editaridusuario">
                <input type="text" id="UECedulaIdentidad" name="editarci" class="form-control" readonly
                       style="background-color: #e9ecef;">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="UEApellidoPaterno">
                  <i class="fas fa-user text-primary"></i> Apellido Paterno
                </label>
                <input type="text" id="UEApellidoPaterno" name="editarapaterno" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="UEApellidoMaterno">
                  <i class="fas fa-user text-primary"></i> Apellido Materno
                </label>
                <input type="text" id="UEApellidoMaterno" name="editaramaterno" class="form-control">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="UENombres">
                  <i class="fas fa-user text-primary"></i> Nombres
                </label>
                <input type="text" id="UENombres" name="editarnombres" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="UEDireccion">
                  <i class="fas fa-map-marker-alt text-primary"></i> Dirección
                </label>
                <input type="text" id="UEDireccion" name="editardireccion" class="form-control">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="UECelular">
                  <i class="fas fa-mobile-alt text-success"></i> Celular
                </label>
                <input type="text" id="UECelular" name="editarcelular" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="UETelefono">
                  <i class="fas fa-phone text-success"></i> Teléfono
                </label>
                <input type="text" id="UETelefono" name="editartelefono" class="form-control">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="UECorreo">
                  <i class="fas fa-envelope text-success"></i> Correo electrónico
                </label>
                <input type="email" id="UECorreo" name="editarcorreo" class="form-control" maxlength="150"
                       placeholder="Necesario para recuperar la contraseña por correo">
              </div>
            </div>
          </div>

          <!-- Rol: cambia lo que la persona puede ver y hacer. No se puede cambiar el propio
               ni dejar el sistema sin administrador (lo valida el servidor). -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-0">
                <label for="UECargo">
                  <i class="fas fa-user-shield text-danger"></i> Rol
                </label>
                <select class="form-control" name="editarcargo" id="UECargo">
                  <option value="ADM">ADMINISTRADOR</option>
                  <option value="SEC">SECRETARIA</option>
                  <option value="AUX">AUXILIAR</option>
                </select>
                <small class="text-muted" id="UECargoAyuda">Si la persona tiene la sesión abierta, deberá volver a ingresar.</small>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success" style="min-width: 150px;">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
        </div>
      </form>
      <?php
        $edsusuario = new UsuarioControladores();
        $edsusuario -> EditarUsuarioControlador();
      ?>
    </div>
  </div>
</div>

<!-- Modal Dar de Baja -->
<div class="modal fade" id="modalu" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
        <h5 class="modal-title text-white" id="exampleModalLabel">
          <i class="fas fa-ban mr-2"></i> Dar de Baja Usuario
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
        <div class="modal-body text-center" style="padding: 30px;">
          <input type="hidden" id="idusr" name="idusr">
          <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <h5 style="color: #495057; margin-bottom: 10px;">¿Está seguro de dar de baja a este usuario?</h5>
          <p style="color: #6c757d;">El usuario ya no podrá ingresar al sistema</p>
        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-check"></i> Confirmar
          </button>
        </div>
        <?php
          $estadousr = new UsuarioControladores();
          $estadousr -> CambiarEstadoModelos();
        ?>
      </form>
    </div>
  </div>
</div>

<!-- Modal Activar Usuario -->
<div class="modal fade" id="modals" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header bg-gradient-success">
        <h5 class="modal-title text-white" id="exampleModalLabel2">
          <i class="fas fa-check-circle mr-2"></i> Activar Usuario
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
        <div class="modal-body text-center" style="padding: 30px;">
          <input type="hidden" id="idusrs" name="idusrs">
          <div style="font-size: 48px; color: #28a745; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
          </div>
          <h5 style="color: #495057; margin-bottom: 10px;">¿Desea activar este usuario?</h5>
          <p style="color: #6c757d;">El usuario tendrá permiso para ingresar al sistema</p>
        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check"></i> Confirmar
          </button>
        </div>
        <?php
          $subirusr = new UsuarioControladores();
          $subirusr -> CambiarEstadosControlador();
        ?>
      </form>
    </div>
  </div>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>

</body>
