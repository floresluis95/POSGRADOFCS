<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right 
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed 
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left 
kt-aside--fixed kt-page--loading">

  <!-- Header Mobile -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
        <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40">
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
  <!-- End Header Mobile -->

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background-color: #EAEAEA; font-size:12pt;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

        <?php 
          $NavBar = new FuncionesControladores(); 
          $NavBar->NavBarControlador(); 
        ?>

        <button class="kt-aside-close" id="kt_aside_close_btn">
          <i class="la la-close"></i>
        </button>

        <?php 
          $Sidebar = new FuncionesControladores(); 
          $Sidebar->SidebarControlador(); 
        ?>

        <!-- Cuerpo principal -->
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>MATRICULACION</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>NUEVO REGISTRO DE INSCRIPCIÓN</h3>
                    </a>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contenido principal -->
            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="row justify-content-md-center">
                <div class="col-lg-11">

                  <!-- Card principal con diseño mejorado -->
                  <div class="kt-portlet kt-portlet--height-fluid" style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 10px;">

                    <div class="kt-portlet__body" style="padding: 2.5rem;">
                      <form method="POST" id="formMatriculacion" class="needs-validation" enctype="multipart/form-data" novalidate>

                        <!-- SECCIÓN 1: DATOS DEL ESTUDIANTE -->
                        <div class="card mb-4" style="border-left: 4px solid #5867dd; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                          <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 6px 6px 0 0;">
                            <h4 class="mb-0" style="font-weight: 600;">
                              <i class="flaticon2-user-outline-symbol"></i> DATOS DEL ESTUDIANTE
                            </h4>
                          </div>
                          <div class="card-body" style="background-color: #f8f9fa; padding: 2rem;">
                            <div class="row align-items-end">
                              <div class="col-lg-6">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-search text-primary"></i> Buscar Estudiante
                                </label>
                                <select class="form-control kt-select2 kt-select2-general" name="idcliente"
                                        style="border: 2px solid #e1e3ea; border-radius: 6px; font-size: 1rem;" required>
                                  <option value="">Buscar por cédula de identidad...</option>
                                  <?php
                                    $Lista = new EstudiantesControladores();
                                    $Lista->EstudianteActivoControlador();
                                  ?>
                                </select>
                                <small class="form-text text-muted">
                                  <i class="flaticon2-information"></i> Seleccione un estudiante existente
                                </small>
                              </div>
                              <div class="col-lg-3">
                                <button type="button" class="btn btn-primary btn-lg btn-block"
                                        data-toggle="modal" data-target="#ModalInsertarEstudiante"
                                        style="border-radius: 6px; box-shadow: 0 3px 10px rgba(88, 103, 221, 0.3);">
                                  <i class="flaticon2-plus"></i> Nuevo Estudiante
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- SECCIÓN 2: DATOS DE MATRICULACIÓN -->
                        <div class="card mb-4" style="border-left: 4px solid #1dc9b7; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                          <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 6px 6px 0 0;">
                            <h4 class="mb-0" style="font-weight: 600;">
                              <i class="flaticon2-writing"></i> INFORMACIÓN ACADÉMICA
                            </h4>
                          </div>
                          <div class="card-body" style="background-color: #f8f9fa; padding: 2rem;">
                            <div class="row">
                              <div class="col-lg-4 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-list-3 text-success"></i> Grado Académico *
                                </label>
                                <select class="form-control form-control-lg" id="gradoAcademico" name="gradoAcademico"
                                        style="border: 2px solid #e1e3ea; border-radius: 6px;" required>
                                  <option value="" disabled selected>Seleccione el grado...</option>
                                  <option value="DIPLOMADO">📚 DIPLOMADO</option>
                                  <option value="MAESTRIA">🎓 MAESTRÍA</option>
                                  <option value="ESPECIALIDAD">🏆 ESPECIALIDAD</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un grado académico.</div>
                              </div>

                              <div class="col-lg-8 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-document text-info"></i> Programa *
                                </label>
                                <select class="form-control form-control-lg" id="programa" name="programa"
                                        style="border: 2px solid #e1e3ea; border-radius: 6px;" required>
                                  <option value="" disabled selected>Seleccione un programa...</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un programa.</div>
                              </div>
                            </div>

                            <!-- Detalles del programa -->
                            <div id="detalle-programa" class="alert"
                                 style="display:none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                              <h5 style="color: white; font-weight: 600; margin-bottom: 1.5rem;">
                                <i class="flaticon2-information"></i> Detalles del Programa Seleccionado
                              </h5>
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="mb-2"><strong><i class="flaticon2-tag"></i> Programa:</strong> <span id="detalle-nombre-programa"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-box-1"></i> Código:</strong> <span id="detalle-codigo"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-calendar-9"></i> Duración:</strong> <span id="detalle-duracion"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-layers-2"></i> Módulos:</strong> <span id="detalle-modulos"></span></div>
                                </div>
                                <div class="col-md-6">
                                  <div class="mb-2"><strong><i class="flaticon2-hourglass"></i> Costo Total:</strong> <span id="detalle-costo-total" style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px;"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-credit-card"></i> Matrícula:</strong> <span id="detalle-costo-matricula" style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px;"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-location"></i> Sede:</strong> <span id="detalle-sede"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-calendar-3"></i> Inicio:</strong> <span id="detalle-inicio"></span></div>
                                  <div class="mb-2"><strong><i class="flaticon2-medical-records"></i> Grado:</strong> <span id="detalle-tipo"></span></div>
                                </div>
                              </div>
                            </div>

                            <!-- Opción de Pago Completo -->
                            <div class="row mt-3">
                              <div class="col-lg-12">
                                <div class="card" style="border: 2px solid #28a745; background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%); border-radius: 10px;">
                                  <div class="card-body">
                                    <div class="custom-control custom-checkbox">
                                      <input type="checkbox" class="custom-control-input" id="pagoCompleto" name="pagoCompleto" value="1">
                                      <label class="custom-control-label" for="pagoCompleto" style="font-size: 1.1rem; font-weight: 600; color: #28a745; cursor: pointer;">
                                        <i class="flaticon2-check-mark"></i>
                                        <strong>PAGO COMPLETO DEL PROGRAMA</strong>
                                      </label>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="padding-left: 24px;">
                                      <i class="flaticon2-information"></i>
                                      Al marcar esta opción, el estudiante paga el costo total del programa y queda inscrito automáticamente en todos los módulos.
                                      <strong>No se cobrará matrícula.</strong>
                                    </small>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- SECCIÓN 3: DATOS DE PAGO -->
                        <div class="card mb-4" style="border-left: 4px solid #ffb822; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                          <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 6px 6px 0 0;">
                            <h4 class="mb-0" style="font-weight: 600;">
                              <i class="flaticon2-crisp-icons"></i> INFORMACIÓN DE PAGO
                            </h4>
                          </div>
                          <div class="card-body" style="background-color: #f8f9fa; padding: 2rem;">
                            <div class="row">
                              <div class="col-lg-4 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;" id="labelMonto">
                                  <i class="flaticon2-piggy-bank text-warning"></i> <span id="textoMonto">Monto de Matrícula</span> *
                                </label>
                                <div class="input-group input-group-lg">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: #667eea; color: white; border: none;">Bs.</span>
                                  </div>
                                  <input type="number" class="form-control" name="montoMatricula" id="montoMatricula"
                                         placeholder="0.00" min="0" step="0.01"
                                         style="border: 2px solid #e1e3ea; border-left: none;" required>
                                  <input type="hidden" name="costoTotalPrograma" id="costoTotalPrograma" value="0">
                                </div>
                                <small class="form-text text-muted" id="infoMonto">
                                  <i class="flaticon2-information"></i> <span id="textoInfoMonto">Pago inicial de inscripción</span>
                                </small>
                              </div>

                              <div class="col-lg-4 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-file-1 text-info"></i> N° de Voucher *
                                </label>
                                <input type="text" class="form-control form-control-lg" name="numeroVaucher"
                                       placeholder="Ej: 123456789"
                                       style="border: 2px solid #e1e3ea; border-radius: 6px;" required>
                                <small class="form-text text-muted">
                                  <i class="flaticon2-information"></i> Número del comprobante de pago
                                </small>
                              </div>

                              <div class="col-lg-4 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-calendar-8 text-danger"></i> Fecha de Inscripción *
                                </label>
                                <input type="date" class="form-control form-control-lg" name="fechaInscripcion"
                                       style="border: 2px solid #e1e3ea; border-radius: 6px;" required>
                                <small class="form-text text-muted">
                                  <i class="flaticon2-information"></i> Fecha del registro
                                </small>
                              </div>

                              <div class="col-lg-12 mb-3">
                                <label class="font-weight-bold mb-2" style="color: #3f4254; font-size: 0.95rem;">
                                  <i class="flaticon2-image-file text-success"></i> Comprobante de Pago (Imagen)
                                </label>
                                <div class="custom-file">
                                  <input type="file" class="custom-file-input" name="comprobanteImagen"
                                         id="comprobanteFile" accept="image/*">
                                  <label class="custom-file-label" for="comprobanteFile"
                                         style="border: 2px dashed #e1e3ea; border-radius: 6px; padding: 1rem;">
                                    <i class="flaticon2-photograph"></i> Seleccionar imagen del comprobante...
                                  </label>
                                </div>
                                <small class="form-text text-muted">
                                  <i class="flaticon2-information"></i> Formatos: JPG, PNG (Máx. 5MB)
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="text-center mt-4">
                          <button type="submit" name="registrarMatricula" class="btn btn-success btn-lg"
                                  style="padding: 1rem 3rem; border-radius: 8px; font-size: 1.1rem; font-weight: 600; box-shadow: 0 4px 15px rgba(29, 201, 183, 0.4);">
                            <i class="flaticon2-check-mark"></i> Guardar Matriculación
                          </button>
                        </div>

                        <?php
                          $RegistrarMatricula = new MatriculaControladores();
                          $RegistrarMatricula->RegistrarMatriculaControlador();
                        ?>
                      </form>
                    </div>

                  </div>
                </div>
              </div>
            </div><!-- End kt-content -->
        </div> <!-- End kt-body -->
      </div> <!-- End wrapper -->
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="vistas/recursos/assets/js/scripts/programa.js"></script>
  <script src="vistas/recursos/assets/js/scripts/estudiante.js"></script>
  <script src="vistas/recursos/assets/js/scripts/inscripcion.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <?php 
    $Footer = new FuncionesControladores(); 
    $Footer->FooterControlador(); 
  ?>


 <!-- Modal: Nuevo Estudiante -->
<div class="modal fade" id="ModalInsertarEstudiante" tabindex="-1" role="dialog"
         aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
                    <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel" style="font-weight: 600;">
                        <i class="flaticon2-user-outline-symbol"></i> Nuevo Registro de Estudiante
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
                        <span aria-hidden="true" style="font-size: 2rem;">&times;</span>
                    </button>
                </div>
                
                <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="modal-body" style="padding: 2rem; background-color: #fafafa;">

                        <div class="mb-4 p-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-left: 4px solid #667eea; border-radius: 8px;">
                            <h5 class="mb-0" style="color: #667eea; font-weight: 600;">
                                <i class="flaticon2-user"></i> DATOS PERSONALES
                            </h5>
                        </div>

                        <!-- C.I. y complementos -->
                        <div class="row">
                            <div class="col-md-4">
                                <label for="inputCi">C.I. <span class="text-danger">*</span></label>
                                <input type="text" id="inputCi" name="Ci" class="form-control" 
                                       placeholder="1234567" required pattern="[0-9]{6,12}" maxlength="12">
                                <div class="invalid-feedback">Ingrese un CI válido.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="inputComplemento">Complemento</label>
                                <input type="text" id="inputComplemento" name="Complemento" 
                                       class="form-control text-uppercase" pattern="[A-Za-z0-9]{1,5}">
                            </div>
                            <div class="col-md-4">
                                <label for="selectExpedido">Expedido <span class="text-danger">*</span></label>
                                <select class="form-control" id="selectExpedido" name="Exp" required>
                                    <option value="" disabled selected>Seleccione departamento</option>
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

                        <!-- Nombre y fecha -->
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="inputNombres">Nombre(s) <span class="text-danger">*</span></label>
                                <input type="text" id="inputNombres" name="Nombre" class="form-control" 
                                       required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}">
                                <div class="invalid-feedback">Ingrese un nombre válido.</div>
                            </div>
                            <div class="col-md-3">
                                <label for="apaterno">Apellido Paterno <span class="text-danger">*</span></label>
                                <input type="text" id="apaterno" name="Apaterno" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="amaterno">Apellido Materno</label>
                                <input type="text" id="amaterno" name="Amaterno" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="fechaNacimiento">Fecha Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control" required 
                                       max="<?php echo date('Y-m-d'); ?>" 
                                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="Edad">Edad <span class="text-danger">*</span></label>
                                <input type="number" id="Edad" name="Edad" class="form-control" required>
                                <div class="invalid-feedback">Ingrese una edad válida.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="selectLugarn">Lugar de nacimiento <span class="text-danger">*</span></label>
                                <select class="form-control" id="Lugarn" name="Lugarn" required>
                                    <option value="" disabled selected>Seleccione departamento</option>
                                    <option value="La Paz">La Paz</option>
                                    <option value="Cochabamba">Cochabamba</option>
                                    <option value="Santa Cruz">Santa Cruz</option>
                                    <option value="Oruro">Oruro</option>
                                    <option value="Potosí">Potosí</option>
                                    <option value="Chuquisaca">Chuquisaca</option>
                                    <option value="Tarija">Tarija</option>
                                    <option value="Beni">Beni</option>
                                    <option value="Pando">Pando</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <div class="invalid-feedback">Seleccione el lugar de nacimiento.</div>
                            </div>
                        </div>

                        <div class="mb-4 mt-4 p-3" style="background: linear-gradient(135deg, rgba(29, 201, 183, 0.1) 0%, rgba(102, 126, 234, 0.1) 100%); border-left: 4px solid #1dc9b7; border-radius: 8px;">
                            <h5 class="mb-0" style="color: #1dc9b7; font-weight: 600;">
                                <i class="flaticon2-phone"></i> OTROS DATOS
                            </h5>
                        </div>
                        <div class="row">
                            
                            <div class="col-md-4">
                                <label for="emailInput">Correo <span class="text-danger">*</span></label>
                                <input type="email" id="emailInput" name="Correo" class="form-control" required maxlength="100">
                            </div>
                            
                          <div class="col-md-6">
                                <label for="IdProfesion">Profesión <span class="text-danger">*</span></label>
                                <select class="form-control" id="IdProfesion" name="IdProfesion" required>
                                    <option value="">Seleccione una profesión...</option>
                                    <?php
                                        $ListaProfesion = new ProfesionControlador();
                                        $ListaProfesion->ListaProfesionControlador();
                                    ?>
                                </select>
                                <div class="invalid-feedback">Seleccione la profesión.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="trabajoInput">Trabajo actual</label>
                                <input type="text" id="Trabajo" name="Trabajo" class="form-control" maxlength="100">
                            </div>

                            <div class="col-md-6">
                                <label for="direccionInput">Dirección domiciliaria</label>
                                <input type="text" id="direccionInput" name="Direccion" class="form-control" maxlength="100">
                            </div>

                            <div class="col-md-4">
                                <label for="inputTelefono">Teléfono</label>
                                <input type="tel" id="inputTelefono" name="Telefono" class="form-control" pattern="[0-9]{7,8}">
                            </div>

                            <div class="col-md-4">
                                <label for="inputCelular">Celular <span class="text-danger">*</span></label>
                                <input type="tel" id="inputCelular" name="Celular" class="form-control" 
                                       required pattern="[6-7][0-9]{7}">
                                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
                            </div>

                            <div class="col-md-12">
                                <div class="alert mt-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); border: 2px solid #667eea; border-radius: 8px;">
                                    <i class="flaticon2-information"></i> <strong>Importante:</strong> Los campos marcados con
                                    <span class="text-danger font-weight-bold">*</span> son obligatorios.
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer" style="background-color: #f5f5f5; padding: 1.5rem; border-radius: 0 0 15px 15px;">
                        <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"
                                style="padding: 0.75rem 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <i class="flaticon2-cancel"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success btn-lg"
                                style="padding: 0.75rem 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(29, 201, 183, 0.4);">
                            <i class="flaticon2-check-mark"></i> Guardar Estudiante
                        </button>
                    </div>

                    <?php
                        $DatosEstudiante = new EstudiantesControladores();
                        $DatosEstudiante->RegistarEstudianteControlador2();
                    ?>
                </form>
            </div>
        </div>
    </div>

<style>
/* Estilos adicionales para mejorar la vista */
.form-control:focus, .custom-file-input:focus ~ .custom-file-label {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

/* Animación para el detalle del programa */
#detalle-programa {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mejorar select2 */
.select2-container--default .select2-selection--single {
    border: 2px solid #e1e3ea !important;
    border-radius: 6px !important;
    height: calc(2.25rem + 2px) !important;
}

.select2-container--default .select2-selection--single:focus {
    border-color: #667eea !important;
}

/* Custom file input */
.custom-file-label::after {
    background: #667eea;
    color: white;
    content: "Buscar";
    border-radius: 0 6px 6px 0;
}
</style>

<script>
 // Validación personalizada del formulario de estudiante
$(document).ready(function() {

    // Mostrar nombre del archivo seleccionado
    $('#comprobanteFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html('<i class="flaticon2-image-file text-success"></i> ' + (fileName || 'Seleccionar imagen del comprobante...'));
    });

    const form = document.getElementById('formNuevoEstudiante');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);

    // Convertir texto a mayúsculas automáticamente
    $('#inputComplemento, #inputNombres, #apaterno, #amaterno').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Validar solo números en CI, Teléfono y Celular
    $('#inputCi, #inputTelefono, #inputCelular').on('keypress', function(e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    // Validar solo letras en nombres y apellidos
    $('#inputNombres, #apaterno, #amaterno').on('keypress', function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[A-Za-zñÑáéíóúÁÉÍÓÚ\s]/.test(char)) {
            e.preventDefault();
        }
    });

    // Validar edad mínima (debe ser mayor de 15 años)
    $('#fechaNacimiento').on('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();

        let edad = today.getFullYear() - birthDate.getFullYear();
        const mes = today.getMonth() - birthDate.getMonth();
        if (mes < 0 || (mes === 0 && today.getDate() < birthDate.getDate())) {
            edad--;
        }

        if (edad < 15) {
            alert('El estudiante debe tener al menos 15 años de edad.');
            this.value = '';
        }
    });

    // Mostrar fecha actual en el encabezado (si existe #lafecha)
    function actualizarFecha() {
        const opciones = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }

    actualizarFecha();
    setInterval(actualizarFecha, 60000); // Actualiza cada minuto

    // Manejar checkbox de pago completo
    $('#pagoCompleto').on('change', function() {
        if ($(this).is(':checked')) {
            // Pago completo activado
            const costoTotal = parseFloat($('#costoTotalPrograma').val()) || 0;

            $('#montoMatricula').val(costoTotal.toFixed(2));
            $('#montoMatricula').prop('readonly', true);
            $('#montoMatricula').css('background-color', '#e9ecef');

            $('#textoMonto').html('Monto Total del Programa');
            $('#textoInfoMonto').html('Pago completo - Sin matrícula - Inscripción automática a todos los módulos');
            $('#infoMonto').removeClass('text-muted').addClass('text-success');
            $('#labelMonto').find('i').removeClass('text-warning').addClass('text-success');

        } else {
            // Pago completo desactivado
            $('#montoMatricula').val('');
            $('#montoMatricula').prop('readonly', false);
            $('#montoMatricula').css('background-color', '#ffffff');

            $('#textoMonto').html('Monto de Matrícula');
            $('#textoInfoMonto').html('Pago inicial de inscripción');
            $('#infoMonto').removeClass('text-success').addClass('text-muted');
            $('#labelMonto').find('i').removeClass('text-success').addClass('text-warning');
        }
    });
});
</script>

</body>