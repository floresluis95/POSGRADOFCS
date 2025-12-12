<?php
/**
 * Vista: Registro de Docentes
 * Versión mejorada con gestión de usuarios
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
                  <h2 class="">Gestión de Docentes</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="fas fa-chalkboard-teacher"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>Registro de Docentes</h3>
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
              <div class="card-header bg-gradient-primary">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-chalkboard-teacher"></i> Docentes Registrados
                </h4>
                <div class="heading-elements">
                  <button data-toggle="modal" data-target="#ModalInsertarDocente" type="button" class="btn btn-light btn-sm">
                    <i class="fas fa-user-plus mr-1"></i>Nuevo Docente
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle TablaDocentes" id="kt_table_1">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 4%;">Nº</th>
                          <th class="text-center" style="width: 9%;">
                            <i class="fas fa-id-card"></i> C.I.
                          </th>
                          <th style="width: 16%;">
                            <i class="fas fa-user"></i> Nombre Completo
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-certificate"></i> Cédula Prof.
                          </th>
                          <th style="width: 13%;">
                            <i class="fas fa-envelope"></i> Correo
                          </th>
                          <th style="width: 11%;">
                            <i class="fas fa-graduation-cap"></i> Especialidad
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-user-circle"></i> Usuario
                          </th>
                          <th class="text-center" style="width: 11%;">
                            <i class="fas fa-key"></i> Contraseña
                          </th>
                          <th class="text-center" style="width: 16%;">
                            <i class="fas fa-cog"></i> Acción
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $ListaDocentes = new DocentesControlador();
                          $ListaDocentes -> ListaDocenteControlador();
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
    padding: 15px 10px;
    font-size: 13px;
    vertical-align: middle;
  }

  .table tbody tr {
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.005);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border-color: #e9ecef;
    font-size: 13px;
  }

  .card.shadow-sm {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    border: none;
    border-radius: 10px;
    overflow: hidden;
  }

  .card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
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
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .modal-header.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: none;
  }

  .modal-header.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
  }

  .form-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 20px 0 15px 0;
  }

  .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
  }
</style>

<!-- Modal: Nuevo Docente -->
<div class="modal fade" id="ModalInsertarDocente" tabindex="-1" role="dialog"
     aria-labelledby="modalNuevoDocenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header bg-gradient-info">
                <h4 class="modal-title text-white" id="modalNuevoDocenteLabel">
                    <i class="fas fa-chalkboard-teacher mr-2"></i> Nuevo Registro de Docente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="formNuevoDocente" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="modal-body" style="padding: 30px;">

                    <h4 class="form-section">
                        <i class="fas fa-id-card mr-2"></i> Datos de Identificación
                    </h4>

                    <!-- C.I. y complementos -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputCi"><i class="fas fa-id-card text-primary"></i> C.I. *</label>
                                <input type="text" id="inputCi" name="Ci" class="form-control"
                                       placeholder="1234567" required pattern="[0-9]{6,12}" maxlength="12">
                                <div class="invalid-feedback">Ingrese un CI válido.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputComplemento"><i class="fas fa-bookmark text-primary"></i> Complemento</label>
                                <input type="text" id="inputComplemento" name="Complemento"
                                       class="form-control text-uppercase" pattern="[A-Za-z0-9]{1,5}" maxlength="5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selectExpedido"><i class="fas fa-map-marker-alt text-primary"></i> Expedido *</label>
                                <select class="form-control" id="selectExpedido" name="Exp" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="LP">La Paz</option>
                                    <option value="CB">Cochabamba</option>
                                    <option value="SC">Santa Cruz</option>
                                    <option value="OR">Oruro</option>
                                    <option value="PT">Potosí</option>
                                    <option value="CH">Chuquisaca</option>
                                    <option value="TJ">Tarija</option>
                                    <option value="BN">Beni</option>
                                    <option value="PD">Pando</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el lugar de expedición.</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-user mr-2"></i> Datos Personales
                    </h4>

                    <!-- Nombre y fecha -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputNombres"><i class="fas fa-user text-primary"></i> Nombre(s) *</label>
                                <input type="text" id="inputNombres" name="Nombre" class="form-control"
                                       required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" placeholder="Juan Carlos">
                                <div class="invalid-feedback">Ingrese un nombre válido.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apaterno"><i class="fas fa-user text-primary"></i> Apellido Paterno *</label>
                                <input type="text" id="apaterno" name="Apaterno" class="form-control" required placeholder="García">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amaterno"><i class="fas fa-user text-primary"></i> Apellido Materno</label>
                                <input type="text" id="amaterno" name="Amaterno" class="form-control" placeholder="López">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="fechaNacimiento"><i class="fas fa-calendar text-info"></i> Fecha Nacimiento</label>
                                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control"
                                       max="<?php echo date('Y-m-d'); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-graduation-cap mr-2"></i> Información Profesional
                    </h4>

                    <!-- Información profesional -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cedulaProfesional"><i class="fas fa-certificate text-danger"></i> Cédula Profesional *</label>
                                <input type="text" id="cedulaProfesional" name="CedulaProfesional" class="form-control"
                                       required placeholder="Ej: CP-1234" maxlength="30">
                                <div class="invalid-feedback">Ingrese la cédula profesional.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="especialidad"><i class="fas fa-briefcase text-danger"></i> Especialidad *</label>
                                <input type="text" id="especialidad" name="Especialidad" class="form-control"
                                       required placeholder="Ej: Odontología General" maxlength="100">
                                <div class="invalid-feedback">Ingrese la especialidad.</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-phone mr-2"></i> Información de Contacto
                    </h4>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="emailInput"><i class="fas fa-envelope text-success"></i> Correo Electrónico *</label>
                                <input type="email" id="emailInput" name="Correo" class="form-control" required maxlength="100"
                                       placeholder="ejemplo@correo.com">
                                <div class="invalid-feedback">Ingrese un correo válido.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="direccionInput"><i class="fas fa-home text-warning"></i> Dirección</label>
                                <input type="text" id="direccionInput" name="Direccion" class="form-control" maxlength="100"
                                       placeholder="Av. Principal #123">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputTelefono"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="inputTelefono" name="Tel" class="form-control" pattern="[0-9]{7,8}"
                                       placeholder="2525252">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputCelular"><i class="fas fa-mobile-alt text-warning"></i> Celular *</label>
                                <input type="tel" id="inputCelular" name="Cel" class="form-control"
                                       required pattern="[6-7][0-9]{7}" placeholder="70123456">
                                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" style="border-left: 4px solid #17a2b8; background-color: #d1ecf1;">
                        <i class="fas fa-info-circle mr-2"></i>
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
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

                <?php
                    $DatosDocente = new DocentesControlador();
                    $DatosDocente->RegistrarDocenteControlador();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal Asignar Usuario a Docente -->
<div style="z-index: 1500;" class="modal fade" id="ModalAsignarUsuarioDocente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;">
        <h4 class="modal-title text-white" id="myModalLabel">
          <i class="fas fa-user-plus mr-2"></i> Asignar Usuario a Docente
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
              <li><strong>Cargo:</strong> DOC (Docente)</li>
            </ul>
          </div>

          <input type="hidden" id="docente_id" name="docente_id">
          <input type="hidden" id="docente_ci" name="docente_ci">
          <input type="hidden" id="docente_nombre" name="docente_nombre">

          <div class="form-group">
            <label for="nombre_docente_display" class="font-weight-bold">
              <i class="fas fa-chalkboard-teacher text-primary"></i> Docente Seleccionado
            </label>
            <input type="text" id="nombre_docente_display" class="form-control form-control-lg" readonly
                   style="background-color: #f8f9fa; border: 2px solid #e9ecef; font-weight: 600;">
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="ci_display_docente" class="font-weight-bold">
                  <i class="fas fa-id-card text-info"></i> C.I.
                </label>
                <input type="text" id="ci_display_docente" class="form-control" readonly
                       style="background-color: #f8f9fa; border: 2px solid #e9ecef;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="usuario_generado_docente" class="font-weight-bold">
                  <i class="fas fa-user-circle text-success"></i> Usuario
                </label>
                <input type="text" id="usuario_generado_docente" class="form-control" readonly
                       style="background-color: #f8f9fa; border: 2px solid #e9ecef;">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="password_generada_docente" class="font-weight-bold">
              <i class="fas fa-key text-warning"></i> Contraseña Generada
            </label>
            <input type="text" id="password_generada_docente" class="form-control" readonly
                   style="background-color: #fff3cd; border: 2px solid #ffc107; font-weight: 600; font-size: 16px;">
          </div>

          <div class="form-group">
            <label for="correo_docente" class="font-weight-bold">
              <i class="fas fa-envelope text-danger"></i> Correo Electrónico
            </label>
            <input type="email" id="correo_docente" class="form-control" readonly
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
        $crearUsuario -> CrearUsuarioDocenteControlador();
      ?>
    </div>
  </div>
</div>

<!-- Modal: Editar Docente -->
<div class="modal fade" id="ModalEditarDocente" tabindex="-1" role="dialog"
     aria-labelledby="modalEditarDocenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); border: none;">
                <h4 class="modal-title text-white" id="modalEditarDocenteLabel">
                    <i class="fas fa-edit mr-2"></i> Editar Datos del Docente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="formEditarDocente" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="editDocenteID" id="editDocenteID">

                <div class="modal-body" style="padding: 30px;">

                    <h4 class="form-section">
                        <i class="fas fa-id-card mr-2"></i> Datos de Identificación
                    </h4>

                    <!-- C.I. y complementos -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editCi"><i class="fas fa-id-card text-primary"></i> C.I. *</label>
                                <input type="text" id="editCi" name="editCi" class="form-control"
                                       placeholder="1234567" required readonly style="background-color: #e9ecef;">
                                <small class="text-muted">El CI no se puede modificar</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editComplemento"><i class="fas fa-bookmark text-primary"></i> Complemento</label>
                                <input type="text" id="editComplemento" name="editComplemento"
                                       class="form-control text-uppercase" pattern="[A-Za-z0-9]{1,5}" maxlength="5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editExp"><i class="fas fa-map-marker-alt text-primary"></i> Expedido *</label>
                                <select class="form-control" id="editExp" name="editExp" required>
                                    <option value="">Seleccione...</option>
                                    <option value="LP">La Paz</option>
                                    <option value="CB">Cochabamba</option>
                                    <option value="SC">Santa Cruz</option>
                                    <option value="OR">Oruro</option>
                                    <option value="PT">Potosí</option>
                                    <option value="CH">Chuquisaca</option>
                                    <option value="TJ">Tarija</option>
                                    <option value="BN">Beni</option>
                                    <option value="PD">Pando</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-user mr-2"></i> Datos Personales
                    </h4>

                    <!-- Nombre y fecha -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editNombre"><i class="fas fa-user text-primary"></i> Nombre(s) *</label>
                                <input type="text" id="editNombre" name="editNombre" class="form-control"
                                       required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" placeholder="Juan Carlos">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editApaterno"><i class="fas fa-user text-primary"></i> Apellido Paterno *</label>
                                <input type="text" id="editApaterno" name="editApaterno" class="form-control" required placeholder="García">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAmaterno"><i class="fas fa-user text-primary"></i> Apellido Materno</label>
                                <input type="text" id="editAmaterno" name="editAmaterno" class="form-control" placeholder="López">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editFechaNacimiento"><i class="fas fa-calendar text-info"></i> Fecha Nacimiento</label>
                                <input type="date" id="editFechaNacimiento" name="editFechaNacimiento" class="form-control">
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-graduation-cap mr-2"></i> Información Profesional
                    </h4>

                    <!-- Información profesional -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editCedulaProfesional"><i class="fas fa-certificate text-danger"></i> Cédula Profesional *</label>
                                <input type="text" id="editCedulaProfesional" name="editCedulaProfesional" class="form-control"
                                       required placeholder="Ej: CP-1234" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEspecialidad"><i class="fas fa-briefcase text-danger"></i> Especialidad *</label>
                                <input type="text" id="editEspecialidad" name="editEspecialidad" class="form-control"
                                       required placeholder="Ej: Odontología General" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-phone mr-2"></i> Información de Contacto
                    </h4>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editCorreo"><i class="fas fa-envelope text-success"></i> Correo Electrónico *</label>
                                <input type="email" id="editCorreo" name="editCorreo" class="form-control" required maxlength="100"
                                       placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editDireccion"><i class="fas fa-home text-warning"></i> Dirección</label>
                                <input type="text" id="editDireccion" name="editDireccion" class="form-control" maxlength="100"
                                       placeholder="Av. Principal #123">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editTel"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="editTel" name="editTel" class="form-control" pattern="[0-9]{7,8}"
                                       placeholder="2525252">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editCel"><i class="fas fa-mobile-alt text-warning"></i> Celular *</label>
                                <input type="tel" id="editCel" name="editCel" class="form-control"
                                       required pattern="[6-7][0-9]{7}" placeholder="70123456">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="editarDocente" class="btn btn-warning" style="min-width: 120px;">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>

                <?php
                    $editarDocente = new DocentesControlador();
                    $editarDocente->EditarDocenteControlador();
                ?>
            </form>
        </div>
    </div>
</div>

<?php
    $darDeBaja = new DocentesControlador();
    $darDeBaja->DarDeBajaDocenteControlador();
?>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
?>

<!-- Scripts -->
<script>
// Función que se ejecuta cuando jQuery está disponible
function inicializarModalAsignarUsuarioDocente() {
  if (typeof jQuery === 'undefined') {
    console.log('jQuery no está cargado aún, reintentando...');
    setTimeout(inicializarModalAsignarUsuarioDocente, 100);
    return;
  }

  console.log('✅ jQuery cargado - Inicializando modal de asignación de usuario para docente');

  // Capturar datos del docente al hacer clic en el botón
  jQuery(document).on('click', '.btnAsignarUsuarioDocente', function(){
    // Obtener todos los atributos data del botón
    var docenteID = jQuery(this).attr('data-docente-id');
    var ci = jQuery(this).attr('data-ci');
    var ciCompleto = jQuery(this).attr('data-ci-completo');
    var nombreCompleto = jQuery(this).attr('data-nombre-completo');
    var nombrePila = jQuery(this).attr('data-nombre-pila');
    var correo = jQuery(this).attr('data-correo');

    console.log('📋 Datos capturados:', {
      docenteID: docenteID,
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
    jQuery('#docente_id').val(docenteID);
    jQuery('#docente_ci').val(ci);
    jQuery('#docente_nombre').val(nombrePila);

    // Llenar campos de visualización en el modal
    jQuery('#nombre_docente_display').val(nombreCompleto);
    jQuery('#ci_display_docente').val(ciCompleto);
    jQuery('#usuario_generado_docente').val(usuario);
    jQuery('#password_generada_docente').val(password);
    jQuery('#correo_docente').val(correo || 'No registrado');

    console.log('✅ Modal actualizado con:', {
      usuario: usuario,
      password: password
    });
  });

  console.log('✅ Event listener configurado para botones .btnAsignarUsuarioDocente');
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', inicializarModalAsignarUsuarioDocente);
} else {
  inicializarModalAsignarUsuarioDocente();
}

// ========================================
// FUNCIONALIDAD EDITAR DOCENTE
// ========================================
jQuery(document).on('click', '.btnEditarDocente', function() {
    console.log('Botón Editar Docente clickeado');

    // Capturar datos del docente
    const docenteID = jQuery(this).attr('data-docente-id');
    const ci = jQuery(this).attr('data-ci');
    const complemento = jQuery(this).attr('data-complemento');
    const exp = jQuery(this).attr('data-exp');
    const nombre = jQuery(this).attr('data-nombre');
    const apaterno = jQuery(this).attr('data-apaterno');
    const amaterno = jQuery(this).attr('data-amaterno');
    const fechaNacimiento = jQuery(this).attr('data-fecha-nacimiento');
    const cedula = jQuery(this).attr('data-cedula');
    const especialidad = jQuery(this).attr('data-especialidad');
    const direccion = jQuery(this).attr('data-direccion');
    const correo = jQuery(this).attr('data-correo');
    const tel = jQuery(this).attr('data-tel');
    const cel = jQuery(this).attr('data-cel');

    console.log('Datos del docente:', {
        docenteID, ci, nombre, apaterno, amaterno
    });

    // Llenar el formulario del modal
    jQuery('#editDocenteID').val(docenteID);
    jQuery('#editCi').val(ci);
    jQuery('#editComplemento').val(complemento);
    jQuery('#editExp').val(exp);
    jQuery('#editNombre').val(nombre);
    jQuery('#editApaterno').val(apaterno);
    jQuery('#editAmaterno').val(amaterno);
    jQuery('#editFechaNacimiento').val(fechaNacimiento);
    jQuery('#editCedulaProfesional').val(cedula);
    jQuery('#editEspecialidad').val(especialidad);
    jQuery('#editDireccion').val(direccion);
    jQuery('#editCorreo').val(correo);
    jQuery('#editTel').val(tel);
    jQuery('#editCel').val(cel);

    console.log('✓ Modal de edición llenado con datos del docente');
});

// ========================================
// FUNCIONALIDAD DAR DE BAJA DOCENTE
// ========================================
jQuery(document).on('click', '.btnDarDeBaja', function() {
    const docenteID = jQuery(this).attr('data-docente-id');
    const nombreCompleto = jQuery(this).attr('data-nombre-completo');

    console.log('Solicitud de baja para docente:', docenteID, nombreCompleto);

    swal({
        title: '¿Dar de baja al docente?',
        text: 'Docente: ' + nombreCompleto + '\n\nNOTA: Si el docente tiene módulos asignados, no se podrá dar de baja.',
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-secondary',
                closeModal: true
            },
            confirm: {
                text: 'Sí, dar de baja',
                value: true,
                visible: true,
                className: 'btn btn-danger',
                closeModal: false
            }
        },
        dangerMode: true
    }).then((willDelete) => {
        if (willDelete) {
            // Crear formulario y enviarlo
            const form = jQuery('<form>', {
                method: 'POST',
                action: ''
            });

            form.append(jQuery('<input>', {
                type: 'hidden',
                name: 'darDeBajaDocente',
                value: '1'
            }));

            form.append(jQuery('<input>', {
                type: 'hidden',
                name: 'docenteID',
                value: docenteID
            }));

            jQuery('body').append(form);
            form.submit();
        }
    });
});

// Función para copiar contraseña al portapapeles
function copyToClipboard(text, element) {
    // Crear un elemento temporal para copiar el texto
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();

    try {
        document.execCommand('copy');
        // Mostrar feedback visual
        const originalHTML = element.innerHTML;
        element.innerHTML = '<i class="fa fa-check"></i> Copiado!';
        element.style.backgroundColor = '#28a745';

        setTimeout(function() {
            element.innerHTML = originalHTML;
            element.style.backgroundColor = '';
        }, 2000);
    } catch (err) {
        console.error('Error al copiar:', err);
        alert('No se pudo copiar la contraseña');
    }

    document.body.removeChild(tempInput);
}

// Validación del formulario de nuevo docente
jQuery(document).ready(function() {
    const form = document.getElementById('formNuevoDocente');

    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Mayúsculas automáticas
        jQuery('#inputComplemento, #inputNombres, #apaterno, #amaterno').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Validar edad mínima
        jQuery('#fechaNacimiento').on('change', function() {
            const nacimiento = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;

            if (edad < 18) {
                alert('El docente debe tener al menos 18 años.');
                this.value = '';
            }
        });

        // Mostrar fecha actual
        function actualizarFecha() {
            const opciones = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
            const fecha = new Date().toLocaleDateString('es-ES', opciones);
            jQuery('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
        }
        actualizarFecha();
        setInterval(actualizarFecha, 60000);
    }
});
</script>

</body>
