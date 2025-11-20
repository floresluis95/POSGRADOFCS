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

                    <!-- Formulario Único de Matriculación -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Matriculación -->
          <div class="kt-container kt-grid__item kt-grid__item--fluid">
    <div class="row justify-content-md-center">
        <div class="col-lg-11">
            <div class="kt-portlet">

                <div class="kt-portlet__body">
                    <form method="POST" id="formMatriculacion" class="needs-validation" enctype="multipart/form-data" novalidate>

                        <!-- DATOS DEL ESTUDIANTE -->
                        <div class="kt-portlet__head">
                            <div>
                                <h3>
                                    <img src="vistas/recursos/assets/media/icons/gr.png" width="40" alt="">
                                    DATOS DEL ESTUDIANTE
                                </h3>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">ESTUDIANTE:</label>
                            <div class="col-lg-5">
                                <select class="form-control kt-select2 kt-select2-general" name="idcliente" required>
                                    <option value="">Buscar estudiante por cédula de identidad</option>
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

                        <hr>

                        <!-- DATOS DE MATRICULACIÓN -->
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3>
                                    <img src="vistas/recursos/assets/media/icons/inscripcion.png" width="40" alt="Icono Inscripción">
                                    MATRICULACIÓN
                                </h3>
                            </div>
                        </div>

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
                                    <p><strong>Costo Del Programa:</strong> <span id="detalle-costo-total" class="text-success font-weight-bold"></span></p>
                                    <p><strong>Costo Matrícula:</strong> <span id="detalle-costo-matricula" class="text-info font-weight-bold"></span></p>
                                    <p><strong>Sede:</strong> <span id="detalle-sede"></span></p>
                                    <p><strong>Fecha Inicio:</strong> <span id="detalle-inicio"></span></p>
                                    <p><strong>Grado Académico:</strong> <span id="detalle-tipo"></span></p>
                                </div>
                            </div>
                        </div>

            <hr>

            <div class="form-group row form-group-marginless kt-margin-t-20">
    
                <div class="col-lg-4">
                    <div class="form-group row">
                        <label class="col-5 col-form-label">PAGO MATRÍCULA:</label>
                        <div class="col-7">
                            <input type="number" class="form-control" name="montoMatricula" placeholder="Bs. 0.00" min="0" step="0.01" required>
                            <div class="invalid-feedback">Ingrese el monto de matrícula.</div>
                            <small class="form-text text-muted">Pago inicial/matrícula</small>
                        </div>
                    </div>
                </div>

              <div class="col-lg-4">
                  <div class="form-group row">
                      <label class="col-5 col-form-label">N° VAUCHER:</label>
                      <div class="col-7">
                          <input type="text" class="form-control" name="numeroVaucher" placeholder="Número de comprobante" required>
                          <div class="invalid-feedback">Ingrese el número de vaucher.</div>
                          
                      </div>
                  </div>
              </div>

             
                   
          </div>

           <div class="form-group row form-group-marginless kt-margin-t-20">
           <div class="col-lg-4">
                  <div class="form-group row">
                      <label class="col-5 col-form-label">FECHA INSCRIPCIÓN:</label>
                      <div class="col-7">
                          <input type="date" class="form-control" name="fechaInscripcion" required>
                          <div class="invalid-feedback">Ingrese la fecha de inscripción.</div>
                         
                      </div>
                  </div>
              </div>
                <div class="col-lg-6">
              <div class="form-group row">
                  <label class="col-5 col-form-label">COMPROBANTE (IMAGEN):</label>
                  <div class="col-7">
                      <input 
                          type="file" 
                          class="form-control" 
                          name="comprobanteImagen" 
                          accept="image/*"            
                      >
                      <div class="invalid-feedback">Seleccione la imagen del comprobante de pago.</div>
                      <small class="form-text text-muted">Formatos aceptados: JPG, PNG, PDF (si se permite)</small>
                  </div>
              </div>
          </div> 
              
            </div>

                        <hr>
                        <div class="form-group row">
                            <div class="col-lg-12 text-center">
                                <button type="submit" name="registrarMatricula" class="btn btn-success">
                                    <i class="bi bi-save"></i> Guardar Matriculación
                                </button>
                            </div>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel">
                        <i class="bi bi-person-plus-fill"></i> Nuevo Registro de Estudiante
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="modal-body">

                        <h5 class="form-section text-info">
                            <i class="bi bi-person-vcard"></i> DATOS PERSONALES
                        </h5>

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

                        <hr>

                        <!-- Contacto -->
                        <h5 class="form-section text-info"><i class="bi bi-telephone-fill"></i> OTROS DATOS</h5>
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

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i> Campos marcados con 
                                <span class="text-danger">*</span> son obligatorios.
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar
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