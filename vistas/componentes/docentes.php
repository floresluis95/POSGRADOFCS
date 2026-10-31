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
<!-- Estilos compartidos de Estudiantes, Docentes y Usuarios (antes un <style> copiado en cada vista).     Van al inicio para que la página no se vea sin estilo al cargar. --><link rel="stylesheet" href="vistas/recursos/assets/css/gestion-registros.css?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/css/gestion-registros.css'); ?>">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="panel">
         <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
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
                  <h2>GESTIÓN DE DOCENTES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>REGISTRO DE DOCENTES</h3>
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
                  <i class="fas fa-chalkboard-teacher"></i> Docentes Registrados
                </h4>
                <div class="heading-elements">
                  <button data-toggle="modal" data-target="#ModalInsertarDocente" type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus mr-1"></i>Nuevo Docente
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="row mb-3">
                    <div class="col-md-4 offset-md-8">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fa fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="buscadorTablaDocentes" placeholder="Buscar por C.I., nombre, matrícula, etc.">
                      </div>
                    </div>
                  </div>
                  <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover table-bordered align-middle TablaDocentes" id="tablaDocentes" data-exportar="Docentes">
                      <thead class="thead-dark" style="position: sticky; top: 0; z-index: 2;">
                        <tr>
                          <th class="text-center" style="width: 4%;">Nº</th>
                          <th class="text-center" style="width: 12%;">
                            <i class="fas fa-id-card"></i> C.I.
                          </th>
                          <th style="width: 28%;">
                            <i class="fas fa-user"></i> Nombre Completo
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-id-badge"></i> Matrícula Nacional
                          </th>
                          <th class="text-center" style="width: 13%;">
                            <i class="fas fa-user-shield"></i> Acceso al Sistema
                          </th>
                          <th class="text-center" style="width: 24%;">
                            <i class="fas fa-cog"></i> Acción
                          </th>
                        </tr>
                      </thead>
                      <tbody id="tablaDocentesBody">
                        <?php
                          $ListaDocentes = new DocentesControlador();
                          $ListaDocentes->ListaDocenteControlador(0, null);
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

                <div class="modal-body" style="padding: 30px; max-height: 65vh; overflow-y: auto;">

                    <h4 class="form-section">
                        <i class="fas fa-globe mr-2"></i> Nacionalidad
                    </h4>

                    <!-- Checkbox de Docente Extranjero -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div style="padding: 15px; background: linear-gradient(135deg, rgba(30, 60, 114, 0.1) 0%, rgba(42, 82, 152, 0.1) 100%); border-radius: 8px; border: 2px solid #1e3c72;">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="checkExtranjero" name="EsExtranjero" value="1">
                                        <label class="custom-control-label" for="checkExtranjero" style="font-size: 1.1rem; font-weight: 600; color: #1e3c72; cursor: pointer;">
                                            <i class="fas fa-passport mr-2"></i> El docente es extranjero
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Si el docente no es boliviano, marque esta opción primero: el campo "Expedido" cambiará a los datos de país/región.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de País y Región (ocultos por defecto) -->
                    <div class="row" id="camposExtranjero" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="selectPais"><i class="fas fa-flag text-primary"></i> País de Origen *</label>
                                <select class="form-control" id="selectPais" name="Pais">
                                    <option value="" selected>Seleccione un país...</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Brasil">Brasil</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="España">España</option>
                                    <option value="Estados Unidos">Estados Unidos</option>
                                    <option value="México">México</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Perú">Perú</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el país de origen.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputRegion"><i class="fas fa-map-marked-alt text-primary"></i> Región/Departamento *</label>
                                <input type="text" id="inputRegion" name="Region" class="form-control text-uppercase"
                                       placeholder="Ej: Buenos Aires, California, etc." maxlength="100">
                                <small class="text-muted">Estado, provincia o departamento del país</small>
                                <div class="invalid-feedback">Ingrese la región o departamento.</div>
                            </div>
                        </div>
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
                        <div class="col-md-5">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="amaterno"><i class="fas fa-user text-primary"></i> Apellido Materno</label>
                                <input type="text" id="amaterno" name="Amaterno" class="form-control" placeholder="López">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cedulaProfesional"><i class="fas fa-certificate text-info"></i> Cédula Profesional</label>
                                <input type="text" id="cedulaProfesional" name="CedulaProfesional" class="form-control"
                                       placeholder="Ej: CP-1234" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="matriculaNacional"><i class="fas fa-id-badge text-danger"></i> Matrícula Nacional <span id="matriculaNacionalAsterisco">*</span></label>
                                <input type="text" id="matriculaNacional" name="MatriculaNacional" class="form-control"
                                       required placeholder="Ej: MN-1234" maxlength="30">
                                <div class="invalid-feedback">Ingrese la matrícula nacional.</div>
                                <small class="text-muted d-block mt-1">Distinta de la cédula profesional. Solo aplica a profesionales nacionales.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="especialidad">
                                    <i class="fas fa-briefcase text-danger"></i> Especialidad *
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#ModalGestionarEspecialidades" title="Administrar especialidades">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                </label>
                                <select id="especialidad" name="Especialidad" class="form-control" required>
                                    <option value="" disabled selected>Seleccione una especialidad</option>
                                    <?php
                                        $opcionesEspecialidad = new EspecialidadControlador();
                                        $opcionesEspecialidad->RenderOpcionesActivasControlador();
                                    ?>
                                </select>
                                <div class="invalid-feedback">Seleccione la especialidad.</div>
                                <small class="text-muted d-block mt-1">
                                    ¿No está en la lista? Usa el ícono <i class="fa fa-cog"></i> para agregarla.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Menciones / Títulos académicos -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-award text-warning"></i> Menciones / Títulos académicos</label>
                                <div id="contenedorMencionesNuevo">
                                    <div class="input-group mb-2 mencion-row">
                                        <input type="text" name="Menciones[]" class="form-control" placeholder="Ej: M. Sc., Lic., Dr.">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-danger btnQuitarMencion"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarMencionNuevo">
                                    <i class="fa fa-plus"></i> Agregar mención
                                </button>
                                <small class="text-muted d-block mt-1">Opcional. Se antepondrán al nombre del docente (ej. "M. SC. LIC. JUAN PÉREZ").</small>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccionInput"><i class="fas fa-home text-warning"></i> Dirección</label>
                                <input type="text" id="direccionInput" name="Direccion" class="form-control" maxlength="100"
                                       placeholder="Av. Principal #123">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputTelefono"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="inputTelefono" name="Tel" class="form-control" pattern="[0-9]{7,8}"
                                       placeholder="2525252">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputCelular"><i class="fas fa-mobile-alt text-warning"></i> Celular *</label>
                                <input type="tel" id="inputCelular" name="Cel" class="form-control"
                                       required pattern="[6-7][0-9]{7}" placeholder="70123456">
                                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert mb-0" style="border-left: 4px solid #d4a017; background-color: #fff8e6; color: #7a5b0a; font-weight: 600;">
                        <i class="fas fa-exclamation-circle mr-2" style="color: #d4a017;"></i>
                        Los campos marcados con <strong style="color: #b7791f;">*</strong> son obligatorios.
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

<!-- Modal: Ver Detalle del Docente -->
<div class="modal fade" id="ModalDetalleDocente" tabindex="-1" role="dialog" aria-labelledby="modalDetalleDocenteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
        <h4 class="modal-title text-white" id="modalDetalleDocenteLabel">
          <i class="fas fa-user-circle mr-2"></i> Detalle del Docente
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 25px;">
        <h5 id="detalleDocenteNombre" class="font-weight-bold mb-1"></h5>
        <p class="text-muted mb-3" id="detalleDocenteCi"></p>

        <div class="row">
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-envelope"></i> Correo</small>
            <span id="detalleDocenteCorreo" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-graduation-cap"></i> Especialidad</small>
            <span id="detalleDocenteEspecialidad" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-certificate"></i> Cédula Profesional</small>
            <span id="detalleDocenteCedula" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted d-block"><i class="fas fa-id-badge"></i> Matrícula Nacional</small>
            <span id="detalleDocenteMatricula" class="font-weight-bold"></span>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-md-6 mb-2">
            <small class="text-muted d-block"><i class="fas fa-user-circle"></i> Usuario</small>
            <span id="detalleDocenteUsuario" class="font-weight-bold"></span>
          </div>
          <div class="col-md-6 mb-2">
            <small class="text-muted d-block"><i class="fas fa-user-shield"></i> Estado de acceso</small>
            <span id="detalleDocenteEstadoUsuario" class="font-weight-bold"></span>
          </div>
          <div class="col-md-12" id="detalleDocentePasswordWrap" style="display:none;">
            <small class="text-muted d-block"><i class="fas fa-key"></i> Contraseña</small>
            <span id="detalleDocentePassword" class="badge badge-warning" style="font-size: 13px; font-family: monospace; cursor: pointer;" title="Clic para copiar"></span>
          </div>
          <div class="col-md-12" id="detalleDocenteSinPasswordWrap" style="display:none;">
            <small class="text-muted d-block"><i class="fas fa-key"></i> Contraseña</small>
            <span class="text-muted small">No disponible (cuenta creada antes de guardar la contraseña). Puede restablecerla.</span>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #e9ecef;">
        <?php if (UsuarioControladores::PuedeGestionarCuentas('docente')): ?>
        <button type="button" class="btn btn-outline-warning" id="btnRestablecerPasswordDocenteDetalle">
          <i class="fa fa-key"></i> Restablecer contraseña
        </button>
        <?php endif; ?>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Asignar Usuario a Docente -->
<div style="z-index: 1500;" class="modal fade" id="ModalAsignarUsuarioDocente" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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

          <div class="credenciales-policy-card mb-3">
            <div class="credenciales-policy-header">
              <i class="fas fa-shield-alt"></i> Credenciales que se generarán automáticamente
            </div>
            <div class="credenciales-policy-body">
              <div class="credenciales-policy-item">
                <i class="fas fa-user-circle text-success"></i>
                <span><strong>Usuario:</strong> C.I. del docente</span>
              </div>
              <div class="credenciales-policy-item">
                <i class="fas fa-key text-warning"></i>
                <span><strong>Contraseña:</strong> aleatoria y segura (12 caracteres). Se muestra una única vez.</span>
              </div>
              <div class="credenciales-policy-item">
                <i class="fas fa-user-tag text-primary"></i>
                <span><strong>Cargo:</strong> DOC (Docente)</span>
              </div>
            </div>
          </div>

          <style>
            .credenciales-policy-card {
              background: #fff;
              border: 1px solid #e3e7ed;
              border-radius: 8px;
              overflow: hidden;
              box-shadow: 0 1px 3px rgba(30, 60, 114, 0.06);
            }
            .credenciales-policy-header {
              background: #f7f9fc;
              border-bottom: 1px solid #e3e7ed;
              padding: 6px 12px;
              font-weight: 700;
              font-size: 11px;
              color: #1e3c72;
              text-transform: uppercase;
              letter-spacing: .3px;
            }
            .credenciales-policy-body {
              padding: 8px 12px;
            }
            .credenciales-policy-item {
              display: flex;
              align-items: center;
              gap: 8px;
              padding: 3px 0;
              line-height: 1.3;
            }
            .credenciales-policy-item i {
              font-size: 13px;
              width: 14px;
              text-align: center;
              flex-shrink: 0;
            }
            .credenciales-policy-item span {
              font-size: 11.5px;
              color: #74788d;
            }
            .credenciales-policy-item strong {
              color: #33415c;
            }
          </style>

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

          <div class="alert mb-3" style="background-color: #fff3cd; border: 1px solid #ffe08a; border-radius: 8px; padding: 12px 16px;">
            <i class="fas fa-key text-warning mr-2"></i>
            <strong>Contraseña:</strong> se generará de forma aleatoria y segura al confirmar. Se mostrará
            <strong>una única vez</strong> en el aviso de confirmación, así que anótela. Si se olvida, use "Restablecer contraseña".
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
            <div class="modal-header" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%); border: none;">
                <h4 class="modal-title text-white" id="modalEditarDocenteLabel">
                    <i class="fas fa-edit mr-2"></i> Editar Datos del Docente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" id="formEditarDocente" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="editDocenteID" id="editDocenteID">

                <div class="modal-body" style="padding: 30px; max-height: 65vh; overflow-y: auto;">

                    <h4 class="form-section">
                        <i class="fas fa-globe mr-2"></i> Nacionalidad
                    </h4>

                    <!-- Checkbox de Docente Extranjero (Editar) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div style="padding: 15px; background: linear-gradient(135deg, rgba(30, 60, 114, 0.1) 0%, rgba(42, 82, 152, 0.1) 100%); border-radius: 8px; border: 2px solid #1e3c72;">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="editCheckExtranjero" name="editEsExtranjero" value="1">
                                        <label class="custom-control-label" for="editCheckExtranjero" style="font-size: 1.1rem; font-weight: 600; color: #1e3c72; cursor: pointer;">
                                            <i class="fas fa-passport mr-2"></i> El docente es extranjero
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Si el docente no es boliviano, marque esta opción primero: el campo "Expedido" cambiará a los datos de país/región.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de País y Región para Editar (ocultos por defecto) -->
                    <div class="row" id="editCamposExtranjero" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editSelectPais"><i class="fas fa-flag text-primary"></i> País de Origen *</label>
                                <select class="form-control" id="editSelectPais" name="editPais">
                                    <option value="">Seleccione un país...</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Brasil">Brasil</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="España">España</option>
                                    <option value="Estados Unidos">Estados Unidos</option>
                                    <option value="México">México</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Perú">Perú</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editInputRegion"><i class="fas fa-map-marked-alt text-primary"></i> Región/Departamento *</label>
                                <input type="text" id="editInputRegion" name="editRegion" class="form-control text-uppercase"
                                       placeholder="Ej: Buenos Aires, California, etc." maxlength="100">
                                <small class="text-muted">Estado, provincia o departamento del país</small>
                            </div>
                        </div>
                    </div>

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
                        <div class="col-md-5">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editAmaterno"><i class="fas fa-user text-primary"></i> Apellido Materno</label>
                                <input type="text" id="editAmaterno" name="editAmaterno" class="form-control" placeholder="López">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editCedulaProfesional"><i class="fas fa-certificate text-info"></i> Cédula Profesional</label>
                                <input type="text" id="editCedulaProfesional" name="editCedulaProfesional" class="form-control"
                                       placeholder="Ej: CP-1234" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editMatriculaNacional"><i class="fas fa-id-badge text-danger"></i> Matrícula Nacional <span id="editMatriculaNacionalAsterisco">*</span></label>
                                <input type="text" id="editMatriculaNacional" name="editMatriculaNacional" class="form-control"
                                       required placeholder="Ej: MN-1234" maxlength="30">
                                <small class="text-muted d-block mt-1">Distinta de la cédula profesional. Solo aplica a profesionales nacionales.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEspecialidad">
                                    <i class="fas fa-briefcase text-danger"></i> Especialidad *
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#ModalGestionarEspecialidades" title="Administrar especialidades">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                </label>
                                <select id="editEspecialidad" name="editEspecialidad" class="form-control" required>
                                    <option value="" disabled>Seleccione una especialidad</option>
                                    <?php $opcionesEspecialidad->RenderOpcionesActivasControlador(); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Menciones / Títulos académicos (Editar) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-award text-warning"></i> Menciones / Títulos académicos</label>
                                <div id="contenedorMencionesEditar">
                                    <div class="input-group mb-2 mencion-row">
                                        <input type="text" name="editMenciones[]" class="form-control" placeholder="Ej: M. Sc., Lic., Dr.">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-danger btnQuitarMencion"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarMencionEditar">
                                    <i class="fa fa-plus"></i> Agregar mención
                                </button>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDireccion"><i class="fas fa-home text-warning"></i> Dirección</label>
                                <input type="text" id="editDireccion" name="editDireccion" class="form-control" maxlength="100"
                                       placeholder="Av. Principal #123">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="editTel"><i class="fas fa-phone text-warning"></i> Teléfono</label>
                                <input type="tel" id="editTel" name="editTel" class="form-control" pattern="[0-9]{7,8}"
                                       placeholder="2525252">
                            </div>
                        </div>
                        <div class="col-md-3">
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

<!-- Modal Gestionar Especialidades -->
<div class="modal fade" id="ModalGestionarEspecialidades" tabindex="-1" role="dialog" aria-labelledby="ModalGestionarEspecialidadesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalGestionarEspecialidadesLabel">Administrar especialidades</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarEspecialidad" method="post" class="form-inline mb-3">
                    <input type="text" class="form-control mr-2 mb-2" id="inputNombreEspecialidad" name="Nombre" placeholder="Nombre de la especialidad" required>
                    <button type="submit" class="btn btn-success mb-2"><i class="fa fa-plus"></i> Agregar especialidad</button>
                </form>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Especialidad</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEspecialidadesBody">
                        <?php
                            $listaEspecialidades = new EspecialidadControlador();
                            $listaEspecialidades->ListarEspecialidadesControlador();
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
    $darDeBaja = new DocentesControlador();
    $darDeBaja->DarDeBajaDocenteControlador();
    $cambiarAcceso = new DocentesControlador();
    $cambiarAcceso->CambiarEstadoUsuarioDocenteControlador();
    $eliminarDocente = new DocentesControlador();
    $eliminarDocente->EliminarDocenteControlador();
?>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>

<!-- Scripts -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="vistas/recursos/sweetalert.min.js"></script>
<script>
// Buscador de texto libre sobre la tabla de docentes (filtra por cualquier patrón, en cualquier columna)
// Buscador de docentes: consulta TODA la base de datos vía AJAX (no solo lo cargado en la vista)
var timeoutBuscadorDocentes = null;
jQuery(document).on('keyup', '#buscadorTablaDocentes', function() {
    var patron = jQuery(this).val().trim();

    clearTimeout(timeoutBuscadorDocentes);
    timeoutBuscadorDocentes = setTimeout(function() {
        jQuery.ajax({
            url: 'ajax/docentes.ajax.php',
            method: 'POST',
            data: { buscarPatron: patron },
            dataType: 'json',
            success: function(respuesta) {
                if (!respuesta.success) {
                    return;
                }
                jQuery('#tablaDocentesBody').html(respuesta.html);
            }
        });
    }, 300);
});

// ========================================
// MENCIONES / TÍTULOS ACADÉMICOS (dinámico)
// ========================================

// Agrega una fila de mención vacía al contenedor indicado
function agregarFilaMencion(idContenedor, nombreCampo, valor) {
    valor = valor || '';
    var fila = jQuery('<div class="input-group mb-2 mencion-row"></div>');
    var input = jQuery('<input type="text" class="form-control" placeholder="Ej: M. Sc., Lic., Dr.">')
        .attr('name', nombreCampo)
        .val(valor);
    var btnQuitar = jQuery('<div class="input-group-append"><button type="button" class="btn btn-outline-danger btnQuitarMencion"><i class="fa fa-times"></i></button></div>');

    fila.append(input).append(btnQuitar);
    jQuery(idContenedor).append(fila);
}

// Vacía el contenedor y lo llena con las menciones existentes (separadas por coma);
// si no hay ninguna, deja una fila vacía para que se pueda escribir la primera.
function cargarMencionesEnFormulario(idContenedor, nombreCampo, mencionesTexto) {
    jQuery(idContenedor).empty();

    var menciones = (mencionesTexto || '').split(',')
        .map(function(m) { return m.trim(); })
        .filter(function(m) { return m !== ''; });

    if (menciones.length === 0) {
        agregarFilaMencion(idContenedor, nombreCampo, '');
    } else {
        menciones.forEach(function(mencion) {
            agregarFilaMencion(idContenedor, nombreCampo, mencion);
        });
    }
}

jQuery(document).ready(function() {
    jQuery('#btnAgregarMencionNuevo').on('click', function() {
        agregarFilaMencion('#contenedorMencionesNuevo', 'Menciones[]', '');
    });

    jQuery('#btnAgregarMencionEditar').on('click', function() {
        agregarFilaMencion('#contenedorMencionesEditar', 'editMenciones[]', '');
    });

    // Quitar una fila de mención (deja al menos una fila en el contenedor)
    jQuery(document).on('click', '.btnQuitarMencion', function() {
        var $contenedor = jQuery(this).closest('#contenedorMencionesNuevo, #contenedorMencionesEditar');
        if ($contenedor.find('.mencion-row').length > 1) {
            jQuery(this).closest('.mencion-row').remove();
        } else {
            jQuery(this).closest('.mencion-row').find('input').val('');
        }
    });
});

// ========================================
// CATÁLOGO DE ESPECIALIDADES (administrar / refrescar selects)
// ========================================
function refrescarTablaEspecialidades() {
    jQuery.ajax({
        url: 'ajax/especialidad.ajax.php',
        method: 'POST',
        data: { accion: 'listarHtml' },
        success: function(html) {
            jQuery('#tablaEspecialidadesBody').html(html);
        }
    });
}

function refrescarSelectsEspecialidad() {
    jQuery.ajax({
        url: 'ajax/especialidad.ajax.php',
        method: 'POST',
        data: { accion: 'listarOpcionesHtml' },
        success: function(html) {
            ['#especialidad', '#editEspecialidad'].forEach(function(selector) {
                var valorActual = jQuery(selector).val();
                jQuery(selector).html('<option value="" disabled>Seleccione una especialidad</option>' + html);
                jQuery(selector).val(valorActual);
            });
        }
    });
}

jQuery(document).ready(function() {
    jQuery('#formRegistrarEspecialidad').on('submit', function(e) {
        e.preventDefault();
        var nombre = jQuery('#inputNombreEspecialidad').val();

        jQuery.ajax({
            url: 'ajax/especialidad.ajax.php',
            method: 'POST',
            data: { accion: 'registrar', Nombre: nombre },
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.estado === 'exitoso') {
                    jQuery('#formRegistrarEspecialidad')[0].reset();
                    refrescarTablaEspecialidades();
                    refrescarSelectsEspecialidad();
                }
                swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
            },
            error: function() {
                swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
            }
        });
    });

    jQuery(document).on('click', '.btnCambiarEstadoEspecialidad', function() {
        var idespecialidad = jQuery(this).data('idespecialidad');
        var nuevoestado = jQuery(this).data('nuevoestado');

        jQuery.ajax({
            url: 'ajax/especialidad.ajax.php',
            method: 'POST',
            data: { accion: 'cambiarEstado', EspecialidadID: idespecialidad, NuevoEstado: nuevoestado },
            dataType: 'json',
            success: function(respuesta) {
                refrescarTablaEspecialidades();
                refrescarSelectsEspecialidad();
                swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
            },
            error: function() {
                swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
            }
        });
    });
});

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

    // El usuario sí es predecible (siempre el C.I.); la contraseña ya NO se
    // adelanta aquí: se genera de forma aleatoria en el servidor recién al
    // confirmar, para no mostrar un valor que no coincidirá con el real.
    var usuario = ci;

    // Llenar campos hidden que se enviarán al servidor
    jQuery('#docente_id').val(docenteID);
    jQuery('#docente_ci').val(ci);
    jQuery('#docente_nombre').val(nombrePila);

    // Llenar campos de visualización en el modal
    jQuery('#nombre_docente_display').val(nombreCompleto);
    jQuery('#ci_display_docente').val(ciCompleto);
    jQuery('#usuario_generado_docente').val(usuario);
    jQuery('#correo_docente').val(correo || 'No registrado');

    console.log('✅ Modal actualizado con usuario:', usuario);
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
    const matriculaNacional = jQuery(this).attr('data-matricula-nacional');
    const especialidad = jQuery(this).attr('data-especialidad');
    const menciones = jQuery(this).attr('data-menciones');
    const direccion = jQuery(this).attr('data-direccion');
    const correo = jQuery(this).attr('data-correo');
    const tel = jQuery(this).attr('data-tel');
    const cel = jQuery(this).attr('data-cel');
    const esExtranjero = jQuery(this).attr('data-es-extranjero');
    const pais = jQuery(this).attr('data-pais');
    const region = jQuery(this).attr('data-region');

    console.log('Datos del docente:', {
        docenteID, ci, nombre, apaterno, amaterno, esExtranjero, pais, region
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
    jQuery('#editMatriculaNacional').val(matriculaNacional);
    // Si la especialidad guardada ya no está en el catálogo (ej. fue dada de baja),
    // se agrega temporalmente como opción para no perder el dato del docente.
    var $selectEspecialidad = jQuery('#editEspecialidad');
    if (especialidad && $selectEspecialidad.find('option[value="' + especialidad.replace(/"/g, '\\"') + '"]').length === 0) {
        $selectEspecialidad.append(jQuery('<option>', { value: especialidad, text: especialidad }));
    }
    $selectEspecialidad.val(especialidad);
    cargarMencionesEnFormulario('#contenedorMencionesEditar', 'editMenciones[]', menciones);
    jQuery('#editDireccion').val(direccion);
    jQuery('#editCorreo').val(correo);
    jQuery('#editTel').val(tel);
    jQuery('#editCel').val(cel);

    // Manejar datos de extranjero
    if (esExtranjero == '1') {
        jQuery('#editCheckExtranjero').prop('checked', true);
        jQuery('#editCamposExtranjero').show();
        jQuery('#editSelectPais').val(pais).prop('required', true);
        jQuery('#editInputRegion').val(region).prop('required', true);
        jQuery('#editExp').prop('required', false).prop('disabled', true).val('');
        jQuery('#editCel').attr('pattern', '[0-9]{6,15}').attr('maxlength', 15);
    } else {
        jQuery('#editCheckExtranjero').prop('checked', false);
        jQuery('#editCamposExtranjero').hide();
        jQuery('#editSelectPais').val('').prop('required', false);
        jQuery('#editInputRegion').val('').prop('required', false);
        jQuery('#editExp').prop('required', true).prop('disabled', false).val(exp);
        jQuery('#editCel').attr('pattern', '[6-7][0-9]{7}').attr('maxlength', 8);
    }

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
        type: 'warning',
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

// ========================================
// ELIMINAR DOCENTE (solo si no tiene módulos ni cursos asignados)
// El backend verifica dependencias; si tiene actividad, rechaza el
// borrado y sugiere "Dar de baja" en su lugar.
// ========================================
jQuery(document).on('click', '.btnEliminarDocente', function() {
    const docenteID = jQuery(this).attr('data-docente-id');
    const nombreCompleto = jQuery(this).attr('data-nombre-completo');

    swal({
        title: '¿Eliminar este docente?',
        text: 'Docente: ' + nombreCompleto + '\n\nEsta acción es irreversible. Solo se eliminará si no tiene módulos ni cursos asignados.',
        type: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-secondary',
                closeModal: true
            },
            confirm: {
                text: 'Sí, eliminar',
                value: true,
                visible: true,
                className: 'btn btn-danger',
                closeModal: false
            }
        },
        dangerMode: true
    }).then((confirmado) => {
        if (confirmado) {
            const form = jQuery('<form>', {
                method: 'POST',
                action: ''
            });

            form.append(jQuery('<input>', {
                type: 'hidden',
                name: 'eliminarDocente',
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

// ========================================
// VER DETALLE DEL DOCENTE (correo, especialidad, cédula, matrícula, usuario y contraseña)
// ========================================
jQuery(document).on('click', '.btnVerDetalleDocente', function() {
    const $btn = jQuery(this);

    jQuery('#detalleDocenteNombre').text($btn.attr('data-nombre-completo'));
    jQuery('#detalleDocenteCi').text('C.I. ' + $btn.attr('data-ci-completo'));
    jQuery('#detalleDocenteCorreo').text($btn.attr('data-correo'));
    jQuery('#detalleDocenteEspecialidad').text($btn.attr('data-especialidad'));
    jQuery('#detalleDocenteCedula').text($btn.attr('data-cedula'));
    jQuery('#detalleDocenteMatricula').text($btn.attr('data-matricula-nacional'));
    jQuery('#detalleDocenteUsuario').text($btn.attr('data-usuario'));
    jQuery('#detalleDocenteEstadoUsuario').text($btn.attr('data-estado-usuario'));

    const tieneUsuario = $btn.attr('data-tiene-usuario') === '1';

    // Se guarda en el propio botón de restablecer para saber a quién aplica
    // cuando se haga clic en él (el modal es compartido por todas las filas).
    jQuery('#btnRestablecerPasswordDocenteDetalle')
        .attr('data-docente-id', $btn.attr('data-docente-id'))
        .toggle(tieneUsuario);

    const password = $btn.attr('data-password') || '';
    if (password) {
        jQuery('#detalleDocentePassword').text(password);
        jQuery('#detalleDocentePasswordWrap').show();
        jQuery('#detalleDocenteSinPasswordWrap').hide();
    } else {
        jQuery('#detalleDocentePasswordWrap').hide();
        jQuery('#detalleDocenteSinPasswordWrap').toggle(tieneUsuario);
    }
});

// Restablecer contraseña (cuando el docente la olvidó): genera una nueva y
// la deja visible de inmediato para entregársela.
jQuery(document).on('click', '#btnRestablecerPasswordDocenteDetalle', function() {
    const docenteID = jQuery(this).attr('data-docente-id');
    const nombre = jQuery('#detalleDocenteNombre').text();

    // Esta página usa SweetAlert v1 (swal); SweetAlert2 (Swal) no se carga aquí (plantilla.php),
    // por eso antes el botón no hacía nada: "Swal is not defined"
    swal({
        title: '¿Restablecer contraseña?',
        text: 'Se generará una nueva contraseña para ' + nombre + '. La anterior dejará de funcionar.',
        icon: 'warning',
        buttons: {
            cancel: { text: 'Cancelar', value: null, visible: true, className: 'btn btn-secondary', closeModal: true },
            confirm: { text: 'Sí, restablecer', value: true, visible: true, className: 'btn btn-warning', closeModal: false }
        }
    }).then(function(confirmado) {
        if (!confirmado) return;

        jQuery.ajax({
            url: 'ajax/restablecerpassword.ajax.php',
            type: 'POST',
            data: { accion: 'docente', id: docenteID },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    swal('Error', resp.mensaje || 'No se pudo restablecer la contraseña', 'error');
                    return;
                }

                jQuery('#detalleDocentePassword').text(resp.password);
                jQuery('#detalleDocentePasswordWrap').show();
                jQuery('#detalleDocenteSinPasswordWrap').hide();

                // Actualizar también el botón "Ver detalle" de la fila, para que
                // al reabrir el modal se muestre la nueva contraseña.
                jQuery('.btnVerDetalleDocente[data-docente-id="' + docenteID + '"]').attr('data-password', resp.password);

                swal('Contraseña restablecida', 'Nueva contraseña: ' + resp.password + '\n\nAnótela y entréguela al docente.', 'success');
            },
            error: function() {
                swal('Error', 'No se pudo restablecer la contraseña', 'error');
            }
        });
    });
});

// Copiar la contraseña del modal de detalle al hacer clic sobre ella
jQuery(document).on('click', '#detalleDocentePassword', function() {
    copyToClipboard(jQuery(this).text(), this);
});

// ========================================
// BLOQUEAR / HABILITAR ACCESO AL SISTEMA (login del docente)
// No afecta datos académicos ni módulos asignados, solo el ingreso al sistema.
// ========================================
jQuery(document).on('click', '.btnBloquearAccesoDocente', function() {
    const docenteID = jQuery(this).attr('data-docente-id');
    const nombreCompleto = jQuery(this).attr('data-nombre-completo');
    const nuevoEstado = jQuery(this).attr('data-nuevo-estado');
    const vaABloquear = nuevoEstado === '0';

    swal({
        title: vaABloquear ? '¿Bloquear el acceso al sistema?' : '¿Habilitar el acceso al sistema?',
        text: 'Docente: ' + nombreCompleto + (vaABloquear
            ? '\n\nNo podrá iniciar sesión hasta que se le vuelva a habilitar. Sus datos académicos no se ven afectados.'
            : '\n\nPodrá volver a iniciar sesión con su usuario y contraseña.'),
        type: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-secondary',
                closeModal: true
            },
            confirm: {
                text: vaABloquear ? 'Sí, bloquear' : 'Sí, habilitar',
                value: true,
                visible: true,
                className: vaABloquear ? 'btn btn-secondary' : 'btn btn-success',
                closeModal: false
            }
        },
        dangerMode: vaABloquear
    }).then((confirmado) => {
        if (confirmado) {
            const form = jQuery('<form>', { method: 'POST', action: '' });
            form.append(jQuery('<input>', { type: 'hidden', name: 'cambiarEstadoUsuarioDocente', value: '1' }));
            form.append(jQuery('<input>', { type: 'hidden', name: 'docenteID', value: docenteID }));
            form.append(jQuery('<input>', { type: 'hidden', name: 'nuevoEstado', value: nuevoEstado }));
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

        // Mayúsculas automáticas (todos los datos del docente, excepto correo y menciones)
        jQuery('#inputComplemento, #inputNombres, #apaterno, #amaterno, #cedulaProfesional, #matriculaNacional, #direccionInput').on('input', function() {
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

// ========================================
// FUNCIONALIDAD DOCENTE EXTRANJERO
// ========================================
jQuery(document).ready(function() {
    console.log('>>> Inicializando funcionalidad de docente extranjero');

    jQuery('#checkExtranjero').on('change', function() {
        const isChecked = jQuery(this).is(':checked');
        const $camposExtranjero = jQuery('#camposExtranjero');
        const $selectPais = jQuery('#selectPais');
        const $inputRegion = jQuery('#inputRegion');

        console.log('>>> Checkbox extranjero cambiado. Marcado:', isChecked);
        console.log('>>> Campos encontrados:', $camposExtranjero.length);

        if (isChecked) {
            // Mostrar campos de país y región
            console.log('>>> Mostrando campos de extranjero');
            $camposExtranjero.show(); // Usar show() en lugar de slideDown() para depuración
            $selectPais.prop('required', true);
            $inputRegion.prop('required', true);

            // Bloquear el campo "Expedido" (solo aplica a docentes bolivianos)
            jQuery('#selectExpedido').prop('required', false).prop('disabled', true).val('').removeClass('is-invalid');

            // La Matrícula Nacional solo aplica a profesionales nacionales
            jQuery('#matriculaNacional').prop('required', false).prop('disabled', true).val('').removeClass('is-invalid');
            jQuery('#matriculaNacionalAsterisco').hide();

            // El carnet/documento de identidad extranjero puede incluir letras y caracteres especiales
            // (pasaportes, cédulas extranjeras, etc.), a diferencia del C.I. boliviano que es solo numérico.
            jQuery('#inputCi').attr('pattern', '[A-Za-z0-9-]{4,20}')
                               .attr('maxlength', 20)
                               .attr('placeholder', 'Ej: AB123456');

            // El celular extranjero no sigue el formato boliviano (8 dígitos, empieza
            // con 6 o 7): se admite más dígitos (códigos de país incluidos) y sin
            // exigir con qué número empieza.
            jQuery('#inputCelular').attr('pattern', '[0-9]{6,15}')
                                    .attr('maxlength', 15)
                                    .attr('placeholder', 'Ej: 5491123456789')
                                    .next('.invalid-feedback').text('Celular inválido (solo números, 6 a 15 dígitos).');
        } else {
            // Ocultar campos y limpiar valores
            console.log('>>> Ocultando campos de extranjero');
            $camposExtranjero.hide(); // Usar hide() en lugar de slideUp() para depuración
            $selectPais.prop('required', false).val('');
            $inputRegion.prop('required', false).val('');

            // Habilitar de nuevo el campo "Expedido"
            jQuery('#selectExpedido').prop('required', true).prop('disabled', false);

            // Habilitar de nuevo la Matrícula Nacional (obligatoria para nacionales)
            jQuery('#matriculaNacional').prop('required', true).prop('disabled', false);
            jQuery('#matriculaNacionalAsterisco').show();

            // Restaurar la validación numérica del C.I. boliviano
            jQuery('#inputCi').attr('pattern', '[0-9]{6,12}')
                               .attr('maxlength', 12)
                               .attr('placeholder', '1234567');

            // Restaurar la validación del celular boliviano
            jQuery('#inputCelular').attr('pattern', '[6-7][0-9]{7}')
                                    .attr('maxlength', 8)
                                    .attr('placeholder', '70123456')
                                    .next('.invalid-feedback').text('Celular inválido (8 dígitos, empieza con 6 o 7).');
        }
    });

    // Convertir región a mayúsculas
    jQuery('#inputRegion').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // ========================================
    // EDITAR: FUNCIONALIDAD DOCENTE EXTRANJERO
    // ========================================
    console.log('>>> Inicializando funcionalidad de docente extranjero (EDITAR)');

    jQuery('#editCheckExtranjero').on('change', function() {
        const isChecked = jQuery(this).is(':checked');
        const $camposExtranjero = jQuery('#editCamposExtranjero');
        const $selectPais = jQuery('#editSelectPais');
        const $inputRegion = jQuery('#editInputRegion');

        console.log('>>> Checkbox EDITAR extranjero cambiado. Marcado:', isChecked);
        console.log('>>> Campos EDITAR encontrados:', $camposExtranjero.length);

        if (isChecked) {
            console.log('>>> Mostrando campos EDITAR de extranjero');
            $camposExtranjero.show();
            $selectPais.prop('required', true);
            $inputRegion.prop('required', true);
            jQuery('#editExp').prop('required', false).prop('disabled', true).val('');

            // La Matrícula Nacional solo aplica a profesionales nacionales
            jQuery('#editMatriculaNacional').prop('required', false).prop('disabled', true).val('');
            jQuery('#editMatriculaNacionalAsterisco').hide();

            // El celular extranjero no sigue el formato boliviano (8 dígitos, empieza
            // con 6 o 7): se admite más dígitos (códigos de país incluidos) y sin
            // exigir con qué número empieza.
            jQuery('#editCel').attr('pattern', '[0-9]{6,15}')
                               .attr('maxlength', 15)
                               .attr('placeholder', 'Ej: 5491123456789');
        } else {
            console.log('>>> Ocultando campos EDITAR de extranjero');
            $camposExtranjero.hide();
            $selectPais.prop('required', false).val('');
            $inputRegion.prop('required', false).val('');
            jQuery('#editExp').prop('required', true).prop('disabled', false);

            // Habilitar de nuevo la Matrícula Nacional (obligatoria para nacionales)
            jQuery('#editMatriculaNacional').prop('required', true).prop('disabled', false);
            jQuery('#editMatriculaNacionalAsterisco').show();

            // Restaurar la validación del celular boliviano
            jQuery('#editCel').attr('pattern', '[6-7][0-9]{7}')
                               .attr('maxlength', 8)
                               .attr('placeholder', '70123456');
        }
    });

    jQuery('#editInputRegion').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Mayúsculas automáticas en el formulario de edición (excepto correo y menciones)
    jQuery('#editComplemento, #editNombre, #editApaterno, #editAmaterno, #editCedulaProfesional, #editMatriculaNacional, #editDireccion').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>

</body>
