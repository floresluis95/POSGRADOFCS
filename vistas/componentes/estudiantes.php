<?php
/**
 * Vista: Registro de Estudiantes
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
                  <h2 class="">Gestión de Estudiantes</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>Registro de Estudiantes</h3>
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
                  <i class="fas fa-user-graduate"></i> Estudiantes Registrados
                </h4>
                <div class="heading-elements">
                  <button data-toggle="modal" data-target="#ModalInsertarEstudiante" type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus mr-1"></i>Nuevo Estudiante
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle TablaEstudiantes" id="kt_table_1">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 5%;">Nº</th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-id-card"></i> C.I.
                          </th>
                          <th style="width: 20%;">
                            <i class="fas fa-user"></i> Nombre Completo
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-graduation-cap"></i> Profesión
                          </th>
                          <th style="width: 15%;">
                            <i class="fas fa-envelope"></i> Correo
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-phone"></i> Celular
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-user-circle"></i> Usuario
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-cog"></i> Acción
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $ListaEstudiantes = new EstudiantesControladores();
                          $ListaEstudiantes -> ListaEstudianteControladores();
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
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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

<!-- Modal: Nuevo Estudiante -->
<div class="modal fade" id="ModalInsertarEstudiante" tabindex="-1" role="dialog"
     aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header bg-gradient-info">
                <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel">
                    <i class="fas fa-user-plus mr-2"></i> Nuevo Registro de Estudiante
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="modal-body" style="padding: 30px;">

                    <!-- Búsqueda previa por CI: evita registrar un estudiante duplicado -->
                    <div class="alert alert-light d-flex align-items-end flex-wrap" style="gap: 10px; border: 1px dashed #c8d3e0; background: #f8f9fc;">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold mb-1">Verificar CI antes de registrar</label>
                            <input type="text" class="form-control form-control-sm" id="buscarPorCiNuevoEstudiante"
                                   style="max-width: 220px;" placeholder="Ingrese el CI..." maxlength="12">
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="btnBuscarPorCiNuevoEstudiante">
                            <i class="fas fa-search"></i> Verificar
                        </button>
                        <span id="estadoBusquedaCiNuevoEstudiante" class="ml-2"></span>
                    </div>

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
                                    <option value="OTR">Otro</option>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fechaNacimiento"><i class="fas fa-calendar text-info"></i> Fecha Nacimiento *</label>
                                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control" required
                                       max="<?php echo date('Y-m-d'); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Edad"><i class="fas fa-hashtag text-info"></i> Edad *</label>
                                <input type="number" id="Edad" name="Edad" class="form-control" required min="15" max="100">
                                <div class="invalid-feedback">Ingrese una edad válida (mínimo 15 años).</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="Lugarn"><i class="fas fa-map-marker-alt text-info"></i> Lugar de nacimiento *</label>
                                <select class="form-control" id="Lugarn" name="Lugarn" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <!-- El value va en MAYÚSCULAS porque así queda guardado en la BD
                                         (el backend aplica strtoupper() al registrar el estudiante). -->
                                    <option value="LA PAZ">La Paz</option>
                                    <option value="COCHABAMBA">Cochabamba</option>
                                    <option value="SANTA CRUZ">Santa Cruz</option>
                                    <option value="ORURO">Oruro</option>
                                    <!-- "í" queda así (minúscula) porque strtoupper() de PHP no convierte
                                         acentos UTF-8: 'Potosí' termina guardado como 'POTOSí' en la BD. -->
                                    <option value="POTOSí">Potosí</option>
                                    <option value="CHUQUISACA">Chuquisaca</option>
                                    <option value="TARIJA">Tarija</option>
                                    <option value="BENI">Beni</option>
                                    <option value="PANDO">Pando</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el lugar de nacimiento.</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-info-circle mr-2"></i> Otros Datos
                    </h4>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emailInput"><i class="fas fa-envelope text-danger"></i> Correo *</label>
                                <input type="email" id="emailInput" name="Correo" class="form-control" required maxlength="100"
                                       placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="IdProfesion"><i class="fas fa-graduation-cap text-danger"></i> Profesión *</label>
                                <div class="input-group">
                                    <select class="form-control" id="IdProfesion" name="IdProfesion" required>
                                        <option value="">Seleccione una profesión...</option>
                                        <?php
                                            $ListaProfesion = new ProfesionControlador();
                                            $ListaProfesion->ListaProfesionControlador();
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btnAbrirNuevaProfesion"
                                                data-target-select="#IdProfesion"
                                                title="Agregar una profesión que no está en la lista">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Seleccione la profesión.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Trabajo"><i class="fas fa-briefcase text-success"></i> Trabajo actual</label>
                                <input type="text" id="Trabajo" name="Trabajo" class="form-control" maxlength="100"
                                       placeholder="Empresa o institución">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccionInput"><i class="fas fa-home text-success"></i> Dirección</label>
                                <input type="text" id="direccionInput" name="Direccion" class="form-control" maxlength="100"
                                       placeholder="Av. Principal #123">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputTelefono"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="inputTelefono" name="Telefono" class="form-control" pattern="[0-9]{7,8}"
                                       placeholder="4123456">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputCelular"><i class="fas fa-mobile-alt text-warning"></i> Celular *</label>
                                <input type="tel" id="inputCelular" name="Celular" class="form-control"
                                       required pattern="[6-7][0-9]{7}" placeholder="70123456">
                                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
                            </div>
                        </div>
                    </div>

                    <!-- Universidad de egreso: opcional, no todos los estudiantes tienen este
                         dato al momento del registro (se puede completar después editando). -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="IdUniversidad"><i class="fas fa-university text-primary"></i> Universidad de Egreso</label>
                                <div class="input-group">
                                    <select class="form-control" id="IdUniversidad" name="IdUniversidad">
                                        <option value="">Seleccione una universidad...</option>
                                        <?php
                                            $ListaUniversidad = new UniversidadControlador();
                                            $ListaUniversidad->ListaUniversidadControlador();
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btnAbrirNuevaUniversidad"
                                                data-target-select="#IdUniversidad"
                                                title="Agregar una universidad que no está en la lista">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted" id="IdUniversidadTipo"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="FechaEgreso"><i class="fas fa-calendar-check text-primary"></i> Fecha de Egreso</label>
                                <input type="date" id="FechaEgreso" name="FechaEgreso" class="form-control"
                                       max="<?php echo date('Y-m-d'); ?>">
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
                    $DatosEstudiante = new EstudiantesControladores();
                    $DatosEstudiante->RegistarEstudianteControlador();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Estudiante -->
<div class="modal fade" id="ModalEditarEstudiante" tabindex="-1" role="dialog"
     aria-labelledby="modalEditarEstudianteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header bg-gradient-info">
                <h4 class="modal-title text-white" id="modalEditarEstudianteLabel">
                    <i class="fas fa-user-edit mr-2"></i> Editar Datos del Estudiante
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="formEditarEstudiante" class="needs-validation" novalidate
                  style="display: flex; flex-direction: column; min-height: 0; overflow: hidden;">
                <input type="hidden" id="editarEstudianteID" name="EstudianteID">

                <div class="modal-body" style="padding: 30px;">

                    <h4 class="form-section">
                        <i class="fas fa-id-card mr-2"></i> Datos de Identificación
                    </h4>

                    <!-- C.I. y complementos: el C.I. no es editable (identifica al estudiante
                         en el resto del sistema: matrícula, pagos, etc.) -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputCiEditar"><i class="fas fa-id-card text-primary"></i> C.I.</label>
                                <input type="text" id="inputCiEditar" class="form-control" readonly
                                       style="background-color: #f8f9fa;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputComplementoEditar"><i class="fas fa-bookmark text-primary"></i> Complemento</label>
                                <input type="text" id="inputComplementoEditar" name="Complemento"
                                       class="form-control text-uppercase" pattern="[A-Za-z0-9]{1,5}" maxlength="5">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selectExpedidoEditar"><i class="fas fa-map-marker-alt text-primary"></i> Expedido *</label>
                                <select class="form-control" id="selectExpedidoEditar" name="Exp" required>
                                    <option value="" disabled>Seleccione...</option>
                                    <option value="LP">La Paz</option>
                                    <option value="CB">Cochabamba</option>
                                    <option value="SC">Santa Cruz</option>
                                    <option value="OR">Oruro</option>
                                    <option value="PT">Potosí</option>
                                    <option value="CH">Chuquisaca</option>
                                    <option value="TJ">Tarija</option>
                                    <option value="BN">Beni</option>
                                    <option value="PD">Pando</option>
                                    <option value="OTR">Otro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el lugar de expedición.</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-user mr-2"></i> Datos Personales
                    </h4>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputNombresEditar"><i class="fas fa-user text-primary"></i> Nombre(s) *</label>
                                <input type="text" id="inputNombresEditar" name="Nombre" class="form-control"
                                       required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}">
                                <div class="invalid-feedback">Ingrese un nombre válido.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="apaternoEditar"><i class="fas fa-user text-primary"></i> Apellido Paterno *</label>
                                <input type="text" id="apaternoEditar" name="Apaterno" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amaternoEditar"><i class="fas fa-user text-primary"></i> Apellido Materno</label>
                                <input type="text" id="amaternoEditar" name="Amaterno" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fechaNacimientoEditar"><i class="fas fa-calendar text-info"></i> Fecha Nacimiento *</label>
                                <input type="date" id="fechaNacimientoEditar" name="FechaNacimiento" class="form-control" required
                                       max="<?php echo date('Y-m-d'); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="EdadEditar"><i class="fas fa-hashtag text-info"></i> Edad *</label>
                                <input type="number" id="EdadEditar" name="Edad" class="form-control" required min="15" max="100">
                                <div class="invalid-feedback">Ingrese una edad válida (mínimo 15 años).</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="LugarnEditar"><i class="fas fa-map-marker-alt text-info"></i> Lugar de nacimiento *</label>
                                <select class="form-control" id="LugarnEditar" name="Lugarn" required>
                                    <option value="" disabled>Seleccione...</option>
                                    <!-- El value va en MAYÚSCULAS porque así queda guardado en la BD
                                         (el backend aplica strtoupper() al registrar/editar el estudiante).
                                         Antes estaba en formato título y por eso nunca coincidía con el
                                         dato guardado: el campo quedaba en blanco al editar. -->
                                    <option value="LA PAZ">La Paz</option>
                                    <option value="COCHABAMBA">Cochabamba</option>
                                    <option value="SANTA CRUZ">Santa Cruz</option>
                                    <option value="ORURO">Oruro</option>
                                    <!-- "í" queda así (minúscula) porque strtoupper() de PHP no convierte
                                         acentos UTF-8: 'Potosí' termina guardado como 'POTOSí' en la BD. -->
                                    <option value="POTOSí">Potosí</option>
                                    <option value="CHUQUISACA">Chuquisaca</option>
                                    <option value="TARIJA">Tarija</option>
                                    <option value="BENI">Beni</option>
                                    <option value="PANDO">Pando</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el lugar de nacimiento.</div>
                            </div>
                        </div>
                    </div>

                    <h4 class="form-section">
                        <i class="fas fa-info-circle mr-2"></i> Otros Datos
                    </h4>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emailInputEditar"><i class="fas fa-envelope text-danger"></i> Correo *</label>
                                <input type="email" id="emailInputEditar" name="Correo" class="form-control" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="IdProfesionEditar"><i class="fas fa-graduation-cap text-danger"></i> Profesión *</label>
                                <div class="input-group">
                                    <select class="form-control" id="IdProfesionEditar" name="IdProfesion" required>
                                        <option value="">Seleccione una profesión...</option>
                                        <?php
                                            $ListaProfesionEditar = new ProfesionControlador();
                                            $ListaProfesionEditar->ListaProfesionControlador();
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btnAbrirNuevaProfesion"
                                                data-target-select="#IdProfesionEditar"
                                                title="Agregar una profesión que no está en la lista">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Seleccione la profesión.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="TrabajoEditar"><i class="fas fa-briefcase text-success"></i> Trabajo actual</label>
                                <input type="text" id="TrabajoEditar" name="Trabajo" class="form-control" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccionInputEditar"><i class="fas fa-home text-success"></i> Dirección</label>
                                <input type="text" id="direccionInputEditar" name="Direccion" class="form-control" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputTelefonoEditar"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="inputTelefonoEditar" name="Telefono" class="form-control" pattern="[0-9]{7,8}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputCelularEditar"><i class="fas fa-mobile-alt text-warning"></i> Celular *</label>
                                <input type="tel" id="inputCelularEditar" name="Celular" class="form-control"
                                       required pattern="[6-7][0-9]{7}">
                                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="IdUniversidadEditar"><i class="fas fa-university text-primary"></i> Universidad de Egreso</label>
                                <div class="input-group">
                                    <select class="form-control" id="IdUniversidadEditar" name="IdUniversidad">
                                        <option value="">Seleccione una universidad...</option>
                                        <?php
                                            $ListaUniversidadEditar = new UniversidadControlador();
                                            $ListaUniversidadEditar->ListaUniversidadControlador();
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary btnAbrirNuevaUniversidad"
                                                data-target-select="#IdUniversidadEditar"
                                                title="Agregar una universidad que no está en la lista">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted" id="IdUniversidadTipoEditar"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="FechaEgresoEditar"><i class="fas fa-calendar-check text-primary"></i> Fecha de Egreso</label>
                                <input type="date" id="FechaEgresoEditar" name="FechaEgreso" class="form-control"
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" style="min-width: 120px;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nueva Profesión (agregar una que no está en el select, sin salir del formulario) -->
<div class="modal fade" id="ModalNuevaProfesionEstudiante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title text-white"><i class="fa fa-plus mr-2"></i> Nueva Profesión</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevaProfesionEstudiante">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label for="inputNuevaProfesionEstudiante">Nombre de la profesión</label>
                        <input type="text" class="form-control" id="inputNuevaProfesionEstudiante" maxlength="100" required placeholder="Ej: Bioquímico">
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nueva Universidad (agregar una que no está en el select, sin salir del formulario) -->
<div class="modal fade" id="ModalNuevaUniversidadEstudiante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title text-white"><i class="fa fa-plus mr-2"></i> Nueva Universidad</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevaUniversidadEstudiante">
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label for="inputNuevaUniversidadEstudiante">Nombre de la universidad</label>
                        <input type="text" class="form-control" id="inputNuevaUniversidadEstudiante" maxlength="150" required placeholder="Ej: Universidad Mayor de San Andrés">
                    </div>
                    <div class="form-group mb-0">
                        <label for="selectNuevaUniversidadTipo">Tipo de universidad</label>
                        <select class="form-control" id="selectNuevaUniversidadTipo" required>
                            <option value="" disabled selected>Seleccione...</option>
                            <option value="PUBLICA">Pública</option>
                            <option value="PRIVADA">Privada</option>
                            <option value="EXTRANJERA">Extranjera</option>
                            <option value="CONVENIO">De convenio</option>
                        </select>
                        <div class="invalid-feedback">Seleccione el tipo de universidad.</div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detalle del Estudiante (correo, profesión, celular, usuario y contraseña) -->
<div class="modal fade" id="ModalDetalleEstudiante" tabindex="-1" role="dialog" aria-labelledby="modalDetalleEstudianteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
        <h4 class="modal-title text-white" id="modalDetalleEstudianteLabel">
          <i class="fas fa-user-circle mr-2"></i> Detalle del Estudiante
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 25px;">
        <h5 id="detalleEstudianteNombre" class="font-weight-bold mb-1"></h5>
        <p class="text-muted mb-3" id="detalleEstudianteCi"></p>

        <div class="row">
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-envelope"></i> Correo</small>
            <span id="detalleEstudianteCorreo" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-graduation-cap"></i> Profesión</small>
            <span id="detalleEstudianteProfesion" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-mobile-alt"></i> Celular</small>
            <span id="detalleEstudianteCelular" class="font-weight-bold"></span>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-md-6 mb-2">
            <small class="text-muted d-block"><i class="fas fa-user-circle"></i> Usuario</small>
            <span id="detalleEstudianteUsuario" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-2">
            <small class="text-muted d-block"><i class="fas fa-user-shield"></i> Estado de acceso</small>
            <span id="detalleEstudianteEstadoUsuario" class="font-weight-bold"></span>
          </div>
          <div class="col-md-12" id="detalleEstudiantePasswordWrap" style="display:none;">
            <small class="text-muted d-block"><i class="fas fa-key"></i> Contraseña</small>
            <span id="detalleEstudiantePassword" class="badge badge-warning" style="font-size: 13px; font-family: monospace; cursor: pointer;" title="Clic para copiar"></span>
          </div>
          <div class="col-md-12" id="detalleEstudianteSinPasswordWrap" style="display:none;">
            <small class="text-muted d-block"><i class="fas fa-key"></i> Contraseña</small>
            <span class="text-muted small">No disponible (cuenta creada antes de guardar la contraseña). Puede restablecerla.</span>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
        <button type="button" class="btn btn-outline-warning btnRestablecerPasswordEstudiante" id="btnRestablecerPasswordEstudianteDetalle">
          <i class="fa fa-key"></i> Restablecer contraseña
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Asignar Usuario a Estudiante -->
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
              <li><strong>Contraseña:</strong> aleatoria (8 caracteres, letras y números). Se muestra una única vez al confirmar.</li>
              <li><strong>Cargo:</strong> EST (Estudiante)</li>
            </ul>
          </div>

          <input type="hidden" id="estudiante_id" name="estudiante_id">
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

<?php
  $cambiarAcceso = new EstudiantesControladores();
  $cambiarAcceso -> CambiarEstadoUsuarioEstudianteControlador();
?>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>

<!-- Scripts -->
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
    var estudianteID = jQuery(this).attr('data-estudiante-id');
    var ci = jQuery(this).attr('data-ci');
    var ciCompleto = jQuery(this).attr('data-ci-completo');
    var nombreCompleto = jQuery(this).attr('data-nombre-completo');
    var nombrePila = jQuery(this).attr('data-nombre-pila');
    var correo = jQuery(this).attr('data-correo');

    console.log('📋 Datos capturados:', {
      estudianteID: estudianteID,
      ci: ci,
      ciCompleto: ciCompleto,
      nombreCompleto: nombreCompleto,
      nombrePila: nombrePila,
      correo: correo
    });

    // Generar usuario (será el CI)
    var usuario = ci;

    // La contraseña ya NO se adelanta aquí: se genera de forma aleatoria en el
    // servidor recién al confirmar, para no mostrar un valor que no coincidirá
    // con el real (ver GenerarPasswordEstudiante() en usuario.controlador.php).

    // Llenar campos hidden que se enviarán al servidor
    jQuery('#estudiante_id').val(estudianteID);
    jQuery('#estudiante_ci').val(ci);
    jQuery('#estudiante_nombre').val(nombrePila);

    // Llenar campos de visualización en el modal
    jQuery('#nombre_estudiante_display').val(nombreCompleto);
    jQuery('#ci_display').val(ciCompleto);
    jQuery('#usuario_generado').val(usuario);
    jQuery('#correo_estudiante').val(correo || 'No registrado');

    console.log('✅ Modal actualizado con usuario:', usuario);
  });

  console.log('✅ Event listener configurado para botones .btnAsignarUsuario');
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', inicializarModalAsignarUsuario);
} else {
  inicializarModalAsignarUsuario();
}

// Validación del formulario de nuevo estudiante
// NOTA: se usa window 'load' (en vez de jQuery(document).ready) porque jQuery
// se imprime más abajo en el HTML final de la página; para cuando el evento
// 'load' dispara, ya está disponible.
window.addEventListener('load', function() {
    const form = document.getElementById('formNuevoEstudiante');

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

            jQuery('#Edad').val(edad);

            if (edad < 15) {
                alert('El estudiante debe tener al menos 15 años.');
                this.value = '';
                jQuery('#Edad').val('');
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

    // ============================================
    // VERIFICAR CI ANTES DE REGISTRAR (igual que en Pre-Registro):
    // evita que Administración llene todo el formulario para luego
    // enterarse, al guardar, de que ese estudiante ya existe.
    // ============================================
    const $btnGuardarEstudiante = jQuery('#formNuevoEstudiante button[type="submit"]');

    function verificarCiNuevoEstudiante() {
        const ci = jQuery('#buscarPorCiNuevoEstudiante').val().trim();

        if (!ci) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un CI para verificar' });
            return;
        }

        jQuery('#estadoBusquedaCiNuevoEstudiante').html('<i class="fa fa-spinner fa-spin"></i> Verificando...');

        jQuery.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'buscarEstudiantePorCi', ci: ci },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    jQuery('#estadoBusquedaCiNuevoEstudiante').html('<span class="text-danger">Error al verificar</span>');
                    return;
                }

                if (resp.encontrado) {
                    const nombreExistente = [resp.estudiante.Apaterno, resp.estudiante.Amaterno, resp.estudiante.Nombre]
                        .filter(Boolean).join(' ');
                    jQuery('#estadoBusquedaCiNuevoEstudiante').html(
                        '<span class="badge badge-danger"><i class="fa fa-times"></i> Ya registrado: ' + nombreExistente + '</span>'
                    );
                    $btnGuardarEstudiante.prop('disabled', true);
                    Swal.fire({ icon: 'error', title: 'Ya existe', text: 'Ese CI ya está registrado como ' + nombreExistente + '. No puede registrarlo de nuevo.' });
                } else {
                    jQuery('#estadoBusquedaCiNuevoEstudiante').html('<span class="badge badge-success"><i class="fa fa-check"></i> CI disponible</span>');
                    jQuery('#inputCi').val(ci);
                    $btnGuardarEstudiante.prop('disabled', false);
                }
            },
            error: function() {
                jQuery('#estadoBusquedaCiNuevoEstudiante').html('<span class="text-danger">Error al verificar</span>');
            }
        });
    }

    jQuery('#btnBuscarPorCiNuevoEstudiante').on('click', verificarCiNuevoEstudiante);
    jQuery('#buscarPorCiNuevoEstudiante').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            verificarCiNuevoEstudiante();
        }
    });

    // Si cambia el CI manualmente después de verificar, se limpia el estado
    // (evita quedar bloqueado o con un falso "disponible" desactualizado).
    jQuery('#inputCi, #buscarPorCiNuevoEstudiante').on('input', function() {
        jQuery('#estadoBusquedaCiNuevoEstudiante').html('');
        $btnGuardarEstudiante.prop('disabled', false);
    });

    // Al abrir el modal desde cero, limpiar el estado de la última verificación
    jQuery('#ModalInsertarEstudiante').on('show.bs.modal', function() {
        jQuery('#buscarPorCiNuevoEstudiante').val('');
        jQuery('#estadoBusquedaCiNuevoEstudiante').html('');
        $btnGuardarEstudiante.prop('disabled', false);
    });

    // ============================================
    // FIX: modales "anidados" (Nueva Profesión / Nueva Universidad, abiertos con
    // el botón "+" desde dentro de Nuevo Estudiante o Editar Estudiante).
    // Bootstrap 4 tiene un bug conocido: al cerrarse un modal abierto encima de
    // otro, quita la clase "modal-open" del <body> aunque el modal de atrás siga
    // visible. Esa clase es la que le da "overflow-y: auto" al modal (ver CSS
    // ".modal-open .modal"); sin ella, el modal de atrás vuelve a su "overflow:
    // hidden" por defecto y ya no se puede hacer scroll para llegar al botón de
    // guardar. Se restaura la clase a mano si, al cerrarse, todavía queda algún
    // modal abierto.
    // ============================================
    jQuery('#ModalNuevaProfesionEstudiante, #ModalNuevaUniversidadEstudiante').on('hidden.bs.modal', function() {
        if (jQuery('.modal.show').length) {
            jQuery('body').addClass('modal-open');
        }
    });

    // ============================================
    // AGREGAR PROFESIÓN NUEVA (botón "+" junto al select), igual que en Pre-Registro.
    // Un único modal compartido por el formulario de Nuevo Estudiante y el de Editar:
    // se recuerda en qué select hay que insertar la profesión agregada.
    // ============================================
    let $selectProfesionDestino = jQuery('#IdProfesion');

    jQuery(document).on('click', '.btnAbrirNuevaProfesion', function() {
        $selectProfesionDestino = jQuery(jQuery(this).data('target-select'));
        jQuery('#formNuevaProfesionEstudiante')[0].reset();
        jQuery('#ModalNuevaProfesionEstudiante').modal('show');
    });

    jQuery('#formNuevaProfesionEstudiante').on('submit', function(e) {
        e.preventDefault();

        const nombre = jQuery('#inputNuevaProfesionEstudiante').val().trim();
        if (!nombre) return;

        const $btnSubmit = jQuery(this).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true);

        jQuery.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarProfesionRapida', nombreProfesion: nombre },
            dataType: 'json',
            success: function(resp) {
                $btnSubmit.prop('disabled', false);

                if (!resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo agregar la profesión' });
                    return;
                }

                const prof = resp.profesion;
                const yaExiste = $selectProfesionDestino.find('option[value="' + prof.IdProfesion + '"]').length > 0;
                if (!yaExiste) {
                    $selectProfesionDestino.append(
                        jQuery('<option></option>').attr('value', prof.IdProfesion).text(prof.NombreProfesion)
                    );
                }
                $selectProfesionDestino.val(prof.IdProfesion);

                jQuery('#ModalNuevaProfesionEstudiante').modal('hide');
                if (resp.duplicado) {
                    Swal.fire({ icon: 'info', title: 'Atención', text: 'Esa profesión ya existía, se seleccionó del listado' });
                }
            },
            error: function() {
                $btnSubmit.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo agregar la profesión' });
            }
        });
    });

    // ============================================
    // AGREGAR UNIVERSIDAD NUEVA (botón "+" junto al select), mismo patrón que
    // Profesión. Cada universidad del catálogo tiene un tipo fijo (pública,
    // privada, extranjera o de convenio) que se define al agregarla.
    // ============================================
    let $selectUniversidadDestino = jQuery('#IdUniversidad');

    const etiquetasTipoUniversidad = {
        PUBLICA: 'Pública',
        PRIVADA: 'Privada',
        EXTRANJERA: 'Extranjera',
        CONVENIO: 'De convenio'
    };

    function actualizarTipoUniversidad($select, $badge) {
        const tipo = $select.find('option:selected').data('tipo');
        $badge.text(tipo ? ('Tipo: ' + (etiquetasTipoUniversidad[tipo] || tipo)) : '');
    }

    jQuery('#IdUniversidad').on('change', function() {
        actualizarTipoUniversidad(jQuery(this), jQuery('#IdUniversidadTipo'));
    });
    jQuery('#IdUniversidadEditar').on('change', function() {
        actualizarTipoUniversidad(jQuery(this), jQuery('#IdUniversidadTipoEditar'));
    });

    jQuery(document).on('click', '.btnAbrirNuevaUniversidad', function() {
        $selectUniversidadDestino = jQuery(jQuery(this).data('target-select'));
        jQuery('#formNuevaUniversidadEstudiante')[0].reset();
        jQuery('#ModalNuevaUniversidadEstudiante').modal('show');
    });

    jQuery('#formNuevaUniversidadEstudiante').on('submit', function(e) {
        e.preventDefault();

        const nombre = jQuery('#inputNuevaUniversidadEstudiante').val().trim();
        const tipo = jQuery('#selectNuevaUniversidadTipo').val();
        if (!nombre || !tipo) return;

        const $btnSubmit = jQuery(this).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true);

        jQuery.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarUniversidadRapida', nombreUniversidad: nombre, tipoUniversidad: tipo },
            dataType: 'json',
            success: function(resp) {
                $btnSubmit.prop('disabled', false);

                if (!resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo agregar la universidad' });
                    return;
                }

                const univ = resp.universidad;
                const yaExiste = $selectUniversidadDestino.find('option[value="' + univ.IdUniversidad + '"]').length > 0;
                if (!yaExiste) {
                    $selectUniversidadDestino.append(
                        jQuery('<option></option>').attr('value', univ.IdUniversidad).attr('data-tipo', univ.TipoUniversidad).text(univ.NombreUniversidad)
                    );
                }
                $selectUniversidadDestino.val(univ.IdUniversidad).trigger('change');

                jQuery('#ModalNuevaUniversidadEstudiante').modal('hide');
                if (resp.duplicado) {
                    Swal.fire({ icon: 'info', title: 'Atención', text: 'Esa universidad ya existía, se seleccionó del listado' });
                }
            },
            error: function() {
                $btnSubmit.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo agregar la universidad' });
            }
        });
    });

    // ============================================
    // EDITAR ESTUDIANTE: cargar sus datos en el modal y guardar los cambios
    // ============================================
    jQuery(document).on('click', '.btnEditarEstudiante', function() {
        const estudianteID = jQuery(this).data('estudiante-id');

        jQuery('#formEditarEstudiante')[0].reset();
        jQuery('#formEditarEstudiante').removeClass('was-validated');

        jQuery.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerDatosEstudiante', estudianteID: estudianteID },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success || !resp.estudiante) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudieron cargar los datos del estudiante' });
                    return;
                }

                const est = resp.estudiante;
                jQuery('#editarEstudianteID').val(est.EstudianteID);
                jQuery('#inputCiEditar').val(est.Ci || '');
                jQuery('#inputComplementoEditar').val(est.Complemento || '');
                jQuery('#selectExpedidoEditar').val(est.Exp || '');
                jQuery('#inputNombresEditar').val(est.Nombre || '');
                jQuery('#apaternoEditar').val(est.Apaterno || '');
                jQuery('#amaternoEditar').val(est.Amaterno || '');
                jQuery('#fechaNacimientoEditar').val(est.FechaNacimiento ? est.FechaNacimiento.substring(0, 10) : '');
                jQuery('#EdadEditar').val(est.Edad || '');
                jQuery('#LugarnEditar').val(est.Lugarn || '');
                jQuery('#emailInputEditar').val(est.Correo || '');
                jQuery('#IdProfesionEditar').val(est.IdProfesion || '');
                jQuery('#TrabajoEditar').val(est.Trabajo || '');
                jQuery('#direccionInputEditar').val(est.Direccion || '');
                jQuery('#inputTelefonoEditar').val(est.Telefono || '');
                jQuery('#inputCelularEditar').val(est.Celular || '');
                jQuery('#IdUniversidadEditar').val(est.IdUniversidad || '');
                actualizarTipoUniversidad(jQuery('#IdUniversidadEditar'), jQuery('#IdUniversidadTipoEditar'));
                jQuery('#FechaEgresoEditar').val(est.FechaEgreso ? est.FechaEgreso.substring(0, 10) : '');

                jQuery('#ModalEditarEstudiante').modal('show');
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los datos del estudiante' });
            }
        });
    });

    jQuery('#inputComplementoEditar, #inputNombresEditar, #apaternoEditar, #amaternoEditar').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    jQuery('#fechaNacimientoEditar').on('change', function() {
        const nacimiento = new Date(this.value);
        const hoy = new Date();
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;

        jQuery('#EdadEditar').val(edad);

        if (edad < 15) {
            alert('El estudiante debe tener al menos 15 años.');
            this.value = '';
            jQuery('#EdadEditar').val('');
        }
    });

    jQuery('#formEditarEstudiante').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const $btnGuardar = jQuery(form).find('button[type="submit"]');
        $btnGuardar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        jQuery.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: jQuery(form).serialize() + '&accion=actualizarEstudiante',
            dataType: 'json',
            success: function(resp) {
                $btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');

                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Guardado', text: resp.mensaje, timer: 1800, showConfirmButton: false })
                        .then(function() { location.href = 'estudiantes'; });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo actualizar al estudiante' });
                }
            },
            error: function() {
                $btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar al estudiante' });
            }
        });
    });

    // ============================================
    // VER DETALLE DEL ESTUDIANTE (correo, profesión, celular, usuario y contraseña)
    // ============================================
    jQuery(document).on('click', '.btnVerDetalleEstudiante', function() {
        const $btn = jQuery(this);
        const tieneUsuario = $btn.attr('data-tiene-usuario') === '1';

        jQuery('#detalleEstudianteNombre').text($btn.attr('data-nombre-completo'));
        jQuery('#detalleEstudianteCi').text('C.I. ' + $btn.attr('data-ci-completo'));
        jQuery('#detalleEstudianteCorreo').text($btn.attr('data-correo'));
        jQuery('#detalleEstudianteProfesion').text($btn.attr('data-profesion'));
        jQuery('#detalleEstudianteCelular').text($btn.attr('data-celular'));
        jQuery('#detalleEstudianteUsuario').text($btn.attr('data-usuario'));
        jQuery('#detalleEstudianteEstadoUsuario').text($btn.attr('data-estado-usuario'));

        // Se guarda en el propio botón de restablecer para saber a quién aplica
        // cuando se haga clic en él (el modal es compartido por todas las filas).
        jQuery('#btnRestablecerPasswordEstudianteDetalle')
            .attr('data-estudiante-id', $btn.attr('data-estudiante-id'))
            .toggle(tieneUsuario);

        const password = $btn.attr('data-password') || '';
        if (password) {
            jQuery('#detalleEstudiantePassword').text(password);
            jQuery('#detalleEstudiantePasswordWrap').show();
            jQuery('#detalleEstudianteSinPasswordWrap').hide();
        } else {
            jQuery('#detalleEstudiantePasswordWrap').hide();
            jQuery('#detalleEstudianteSinPasswordWrap').toggle(tieneUsuario);
        }
    });

    // Restablecer contraseña: genera una nueva y la deja visible de inmediato
    // (cubre tanto "olvidó su contraseña" como cuentas creadas antes de guardar
    // PasswordTexto, que no tienen forma de mostrar la original).
    jQuery(document).on('click', '#btnRestablecerPasswordEstudianteDetalle', function() {
        const estudianteID = jQuery(this).attr('data-estudiante-id');
        const nombre = jQuery('#detalleEstudianteNombre').text();

        Swal.fire({
            icon: 'warning',
            title: '¿Restablecer contraseña?',
            text: 'Se generará una nueva contraseña para ' + nombre + '. La anterior dejará de funcionar.',
            showCancelButton: true,
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then(function(resultado) {
            if (!resultado.isConfirmed) return;

            jQuery.ajax({
                url: 'ajax/ordenpago.ajax.php',
                type: 'POST',
                data: { accion: 'restablecerPasswordEstudiante', EstudianteID: estudianteID },
                dataType: 'json',
                success: function(resp) {
                    if (!resp.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo restablecer la contraseña' });
                        return;
                    }

                    jQuery('#detalleEstudiantePassword').text(resp.password);
                    jQuery('#detalleEstudiantePasswordWrap').show();
                    jQuery('#detalleEstudianteSinPasswordWrap').hide();

                    Swal.fire({ icon: 'success', title: 'Contraseña restablecida', text: 'Nueva contraseña: ' + resp.password });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo restablecer la contraseña' });
                }
            });
        });
    });

    // Copiar la contraseña del modal de detalle al hacer clic sobre ella
    jQuery(document).on('click', '#detalleEstudiantePassword', function() {
        copyToClipboard(jQuery(this).text(), this);
    });

    // ============================================
    // BLOQUEAR / HABILITAR ACCESO AL SISTEMA (login del estudiante)
    // No afecta datos académicos ni inscripciones/pagos, solo el ingreso al sistema.
    // ============================================
    jQuery(document).on('click', '.btnBloquearAccesoEstudiante', function() {
        const estudianteID = jQuery(this).attr('data-estudiante-id');
        const nombreCompleto = jQuery(this).attr('data-nombre-completo');
        const nuevoEstado = jQuery(this).attr('data-nuevo-estado');
        const vaABloquear = nuevoEstado === '0';

        Swal.fire({
            icon: 'warning',
            title: vaABloquear ? '¿Bloquear el acceso al sistema?' : '¿Habilitar el acceso al sistema?',
            text: 'Estudiante: ' + nombreCompleto + (vaABloquear
                ? '. No podrá iniciar sesión hasta que se le vuelva a habilitar. Sus datos académicos no se ven afectados.'
                : '. Podrá volver a iniciar sesión con su usuario y contraseña.'),
            showCancelButton: true,
            confirmButtonText: vaABloquear ? 'Sí, bloquear' : 'Sí, habilitar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: vaABloquear ? '#6c757d' : '#28a745'
        }).then(function(resultado) {
            if (!resultado.isConfirmed) return;

            const form = jQuery('<form>', { method: 'POST', action: '' });
            form.append(jQuery('<input>', { type: 'hidden', name: 'cambiarEstadoUsuarioEstudiante', value: '1' }));
            form.append(jQuery('<input>', { type: 'hidden', name: 'estudianteID', value: estudianteID }));
            form.append(jQuery('<input>', { type: 'hidden', name: 'nuevoEstado', value: nuevoEstado }));
            jQuery('body').append(form);
            form.submit();
        });
    });
});

// Función para copiar contraseña al portapapeles (igual que en Docentes)
function copyToClipboard(text, element) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();

    try {
        document.execCommand('copy');
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
</script>

</body>
