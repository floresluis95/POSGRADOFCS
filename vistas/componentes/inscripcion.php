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
                  <div class="kt-portlet">

                    <!-- Datos del estudiante -->
                    <form class="kt-form kt-form--label-right" method="POST">
                      <div class="kt-portlet__head">
                        <div>
                          <h3>
                            <img src="vistas/recursos/assets/media/icons/gr.png" width="40" alt="">
                            DATOS DEL ESTUDIANTE
                          </h3>
                        </div>
                        <?php 
                          $UltimaSolicitud = HeredadoModelos::UltimoIdModelo('codsolicitud', 'solicitud') + 1; 
                        ?>
                        <h3 class="float-right">
                          <i class="kt-menu__link-icon fa fa-file-word"></i>
                          <?php echo 'SOLICITUD-'.$UltimaSolicitud; ?>
                        </h3>
                      </div>

                      <div class="kt-portlet__body">
                        <div class="form-group row">
                          <label class="col-lg-2 col-form-label">ESTUDIANTE:</label>
                          <div class="col-lg-5">
                            <select class="form-control kt-select2 kt-select2-general" name="idcliente" required>
                              <option>Buscar estudiante por cédula de identidad</option>
                              <?php 
                                $Lista = new EstudiantesControladores(); 
                                $Lista->EstudianteActivoControlador(); 
                              ?>
                            </select>
                          </div>
                          <div class="col-lg-4">
                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#ModalInsertarEstudiante">
                              <img src="vistas/recursos/assets/media/icons/svg/Communication/Add-user.svg"/>
                            </button>
                          </div>
                        </div>
                      </div>
                    </form>

                  </div>
                </div>
              </div>
            </div>

            <!-- Matriculación -->
          <div class="kt-container kt-grid__item kt-grid__item--fluid">
                <div class="row justify-content-md-center">
                    <div class="col-lg-11">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <hr>
                            <h3>
                            <img src="vistas/recursos/assets/media/icons/inscripcion.png" width="40" alt="">
                            MATRICULACIÓN
                            </h3>
                        </div>
                        </div>

                        <div class="kt-portlet__body">
                        <form method="POST" id="formMatriculacion" class="needs-validation" novalidate>

                            <!-- Grado Académico y Programa -->
                            <div class="form-group row form-group-marginless kt-margin-t-20">
                            <label class="col-lg-2 col-form-label">GRADO ACADÉMICO:</label>
                            <div class="col-lg-3">
                                <select class="form-control" id="gradoAcademico" name="gradoAcademico" required>
                                <option value="" disabled selected>Elija el grado académico</option>
                                <option value="DIPLOMADO">DIPLOMADO</option>
                                <option value="MAESTRIA">MAESTRÍA</option>
                                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un grado académico.</div>
                            </div>

                            <label class="col-lg-1 col-form-label">PROGRAMA:</label>
                            <div class="col-lg-5">
                                <select class="form-control" id="programa" name="programa" required>
                                <option value="" disabled selected>Seleccione un programa</option>
                                </select>
                                <div class="invalid-feedback">Seleccione un programa.</div>
                            </div>
                            </div>

                            <hr>

                            <!-- Detalles del Programa (oculto inicialmente) -->
                            <div id="detalle-programa" class="alert alert-info" style="display:none;">
                                <h5 class="text-primary"><i class="fa fa-info-circle"></i> Detalles del Programa</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Programa:</strong> <span id="detalle-nombre-programa"></span></p>
                                        <p><strong>Código:</strong> <span id="detalle-codigo"></span></p>
                                        <p><strong>Duración:</strong> <span id="detalle-duracion"></span></p>
                                        <p><strong>Módulos:</strong> <span id="detalle-modulos"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Costo Total:</strong> <span id="detalle-costo-total" class="text-success font-weight-bold"></span></p>
                                        <p><strong>Sede:</strong> <span id="detalle-sede"></span></p>
                                        <p><strong>Fecha Inicio:</strong> <span id="detalle-inicio"></span></p>
                                        <p><strong>Modalidad:</strong> <span id="detalle-tipo"></span></p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Pago de Matrícula y Módulos -->
                            <div class="form-group row form-group-marginless kt-margin-t-20">
                            <label class="col-lg-2 col-form-label">PAGO MATRÍCULA:</label>
                            <div class="col-lg-3">
                                <input type="number" class="form-control" name="pagoMatricula" placeholder="Bs." min="0" step="0.01" required>
                                <div class="invalid-feedback">Ingrese el monto de matrícula.</div>
                                <small class="form-text text-muted">Pago inicial/matrícula</small>
                            </div>

                            <label class="col-lg-2 col-form-label">NÚMERO DE MÓDULOS:</label>
                            <div class="col-lg-2">
                                <input type="number" class="form-control" name="numModulos" placeholder="Cantidad" min="1" required readonly>
                                <div class="invalid-feedback">Ingrese la cantidad de módulos.</div>
                            </div>

                            <label class="col-lg-1 col-form-label">PAGO MÓDULOS:</label>
                            <div class="col-lg-2">
                                <input type="number" class="form-control" name="pagoModulos" placeholder="Bs." min="0" step="0.01" required readonly>
                                <div class="invalid-feedback">Ingrese el monto total de módulos.</div>
                            </div>
                            </div>

                            <!-- Campo oculto para costo total -->
                            <input type="hidden" name="costoTotal" value="0">

                            <!-- Costo por Módulo Calculado -->
                            <div class="form-group row">
                                <div class="col-lg-12 text-center">
                                    <div class="alert alert-success" style="display:inline-block;">
                                        <h5 class="mb-0">
                                            <i class="fa fa-calculator"></i> Costo por Módulo:
                                            <span id="costo-por-modulo" class="font-weight-bold text-success">Bs. 0.00</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview del Plan de Pagos -->
                            <div id="plan-pagos-preview" style="display:none;">
                                <hr>
                                <h5 class="text-info"><i class="fa fa-calendar-alt"></i> Preview del Plan de Pagos</h5>
                                <div class="table-responsive">
                                    <table id="tabla-plan-pagos" class="table table-bordered table-hover table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Módulo</th>
                                                <th class="text-right">Monto</th>
                                                <th class="text-center">Fecha Vencimiento</th>
                                                <th class="text-center">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Se llena con JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Botón de Guardar -->
                            <div class="form-group row">
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Guardar Matriculación
                                </button>
                            </div>
                            </div>

                        </form>
                        </div>

                    </div>
                    </div>
                </div>
                </div>

          </div> <!-- End kt-content -->
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
<div class="modal fade" id="ModalInsertarEstudiante" tabindex="-1" role="dialog" aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <!-- Encabezado -->
      <div class="modal-header bg-info text-white">
        <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel">
          <i class="bi bi-person-plus-fill"></i> Nuevo Registro de Estudiante
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Formulario -->
      <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
        <!-- Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="modal-body">
          <!-- Sección: Datos Personales -->
          <h5 class="form-section text-info">
            <i class="bi bi-person-vcard"></i> DATOS PERSONALES
          </h5>

          <!-- Identificación -->
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="inputCi">C.I. <span class="text-danger">*</span></label>
                <input type="text" id="inputCi" name="Ci" class="form-control"
                       placeholder="Ej: 1234567" required
                       pattern="[0-9]{6,12}" maxlength="12"
                       title="Solo números, entre 6 y 12 dígitos" autocomplete="off">
                <div class="invalid-feedback">Por favor ingrese un CI válido.</div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="inputComplemento">Complemento</label>
                <input type="text" id="inputComplemento" name="Complemento" class="form-control"
                       placeholder="Ej: 1A" pattern="[A-Za-z0-9]{1,5}" maxlength="5"
                       title="Máximo 5 caracteres alfanuméricos" style="text-transform: uppercase;">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
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
                </select>
                <div class="invalid-feedback">Seleccione el lugar de expedición.</div>
              </div>
            </div>
          </div>

          <!-- Nombres y Apellidos -->
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="inputNombres">Nombre(s) <span class="text-danger">*</span></label>
                <input type="text" id="inputNombres" name="Nombre" class="form-control"
                       placeholder="Juan Carlos" required
                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" maxlength="50"
                       title="Solo letras, entre 2 y 50 caracteres">
                <div class="invalid-feedback">Ingrese un nombre válido.</div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="apaterno">Apellido Paterno <span class="text-danger">*</span></label>
                <input type="text" id="apaterno" name="Apaterno" class="form-control"
                       placeholder="López" required
                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" maxlength="50"
                       title="Solo letras, entre 2 y 50 caracteres">
                <div class="invalid-feedback">Ingrese un apellido válido.</div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="amaterno">Apellido Materno</label>
                <input type="text" id="amaterno" name="Amaterno" class="form-control"
                       placeholder="Pérez"
                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" maxlength="50"
                       title="Solo letras, entre 2 y 50 caracteres">
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="fechaNacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control"
                       required max="<?php echo date('Y-m-d'); ?>"
                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                <div class="invalid-feedback">Ingrese una fecha válida.</div>
              </div>
            </div>
          </div>

          <hr>

          <!-- Sección: Contacto -->
          <h5 class="form-section text-info">
            <i class="bi bi-telephone-fill"></i> INFORMACIÓN DE CONTACTO
          </h5>

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label for="direccionInput">Dirección Domiciliaria</label>
                <input type="text" id="direccionInput" name="Direccion" class="form-control"
                       placeholder="Calle, número, zona..." maxlength="100" autocomplete="off">
                <div class="invalid-feedback">Ingrese la dirección del domicilio.</div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="emailInput">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" id="emailInput" name="Correo" class="form-control"
                       placeholder="ejemplo@dominio.com" required maxlength="100" autocomplete="off">
                <div class="invalid-feedback">Ingrese un correo válido.</div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="inputTelefono">Teléfono Fijo</label>
                <input type="tel" id="inputTelefono" name="Telefono" class="form-control"
                       placeholder="2525252" pattern="[0-9]{7,8}" maxlength="8"
                       title="7 u 8 dígitos">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="inputCelular">Celular <span class="text-danger">*</span></label>
                <input type="tel" id="inputCelular" name="Celular" class="form-control"
                       placeholder="75123456" pattern="[6-7][0-9]{7}" maxlength="8"
                       title="Debe comenzar con 6 o 7 y tener 8 dígitos" required>
                <div class="invalid-feedback">Ingrese un celular válido (8 dígitos).</div>
              </div>
            </div>
          </div>

          <hr>

          <div class="alert alert-info mt-3" role="alert">
            <i class="bi bi-info-circle"></i>
            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
          </div>
        </div>

        <!-- Pie del modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Guardar Estudiante
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


<script>
 // Validación personalizada del formulario de estudiante
$(document).ready(function() {

    
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
});
</script>

</body>