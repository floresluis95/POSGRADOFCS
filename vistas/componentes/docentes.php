<?php
  $Validar = new FuncionesControladores();
  $Validar->ValidarSessionControlador();
  date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right 
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed 
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed 
kt-page--loading">

  <!-- Header Mobile -->
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
    <div class="kt-header-mobile__logo">
      <a href="#">
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
          $NavBar->NavBarControlador();

          $Sidebar = new FuncionesControladores();
          $Sidebar->SidebarControlador();
        ?>

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>DOCENTES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="fas fa-chalkboard-teacher"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>LISTA DE DOCENTES</h3>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botón Registrar -->
            <div class="kt-container">
              <div class="row justify-content-md-right">
                <div class="col-lg-12">
                  <div class="kt-portlet">
                    <div class="kt-portlet__head">
                      <div class="kt-portlet__head-label">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#nDocente">
                          <i class="kt-menu__link-icon flaticon-add"></i> Registrar docente
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tabla de docentes -->
              <div class="row justify-content-md-center">
                <div class="col-lg-12">
                  <div class="kt-portlet kt-portlet--mobile">
                    <div class="kt-portlet__head kt-portlet__head--lg">
                      <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon">
                          <i class="fas fa-list"></i>
                        </span>
                        <h3 class="kt-portlet__head-title">REPORTE DE DOCENTES</h3>
                      </div>
                    </div>
                    <div class="kt-portlet__body">
                      <table class="table table-striped table-bordered table-hover table-checkable" id="tablaDocentes">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>CI</th>
                            <th>Nombre Completo</th>
                            <th>Cédula Profesional</th>
                            <th>Email</th>
                            <th>Especialidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php
                                $Listadoc = new DocentesControlador();
                                $Listadoc->ListaDocenteControlador();
                            ?>
                        </tbody>
                    </table>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <?php
            $Footer = new FuncionesControladores();
            $Footer->FooterControlador();
          ?>
          

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL REGISTRO DOCENTE -->
  <div class="modal fade" id="nDocente" tabindex="-1" role="dialog" 
     aria-labelledby="modalNuevoDocenteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title text-white" id="modalNuevoDocenteLabel">
          <i class="bi bi-person-workspace"></i> Nuevo Registro de Docente
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="post" id="formNuevoDocente" enctype="multipart/form-data" class="needs-validation" novalidate>
        <!-- Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="modal-body">
          <!-- Sección: Datos Personales -->
          <h5 class="form-section text-primary">
            <i class="bi bi-person-vcard"></i> DATOS PERSONALES
          </h5>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="inputCi">C.I. <span class="text-danger">*</span></label>
                <input type="text" id="inputCi" name="Ci" class="form-control" 
                       placeholder="Ej: 1234567" required pattern="[0-9]{6,12}" maxlength="12"
                       title="Solo números, entre 6 y 12 dígitos" autocomplete="off">
                <div class="invalid-feedback">Ingrese un CI válido.</div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="inputComplemento">Complemento</label>
                <input type="text" id="inputComplemento" name="Complemento" class="form-control"
                       placeholder="Ej: 1A" pattern="[A-Za-z0-9]{1,5}" maxlength="5"
                       style="text-transform: uppercase;">
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
                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" maxlength="50">
                <div class="invalid-feedback">Ingrese un apellido válido.</div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="amaterno">Apellido Materno</label>
                <input type="text" id="amaterno" name="Amaterno" class="form-control"
                       placeholder="Pérez" pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}" maxlength="50">
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-group">
                <label for="fechaNacimiento">Fecha de Nacimiento</label>
                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control"
                       max="<?php echo date('Y-m-d'); ?>" 
                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
              </div>
            </div>
          </div>

          <hr>

          <!-- Sección: Información Profesional -->
          <h5 class="form-section text-primary">
            <i class="bi bi-mortarboard-fill"></i> INFORMACIÓN PROFESIONAL
          </h5>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="cedulaProfesional">Cédula Profesional <span class="text-danger">*</span></label>
                <input type="text" id="cedulaProfesional" name="CedulaProfesional" class="form-control"
                       placeholder="Ej: CP-4567" required maxlength="20">
                <div class="invalid-feedback">Ingrese la cédula profesional.</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="especialidad">Especialidad <span class="text-danger">*</span></label>
                <input type="text" id="especialidad" name="Especialidad" class="form-control"
                       placeholder="Ej: Endodoncia, Rehabilitación Oral..." required maxlength="100">
                <div class="invalid-feedback">Ingrese la especialidad.</div>
              </div>
            </div>
          </div>

          <hr>

          <!-- Sección: Contacto -->
          <h5 class="form-section text-primary">
            <i class="bi bi-telephone-fill"></i> INFORMACIÓN DE CONTACTO
          </h5>

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label for="direccionInput">Dirección Domiciliaria</label>
                <input type="text" id="direccionInput" name="Direccion" class="form-control"
                       maxlength="100" placeholder="Calle, Zona, Nº">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="emailInput">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" id="emailInput" name="Correo" class="form-control"
                       placeholder="ejemplo@dominio.com" required maxlength="100">
                <div class="invalid-feedback">Ingrese un correo válido.</div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="telefonoInput">Teléfono Fijo</label>
                <input type="tel" id="telefonoInput" name="Tel" class="form-control"
                       placeholder="2 525252" pattern="[0-9]{7,8}" maxlength="8">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label for="celularInput">Celular <span class="text-danger">*</span></label>
                <input type="tel" id="celularInput" name="Cel" class="form-control"
                       placeholder="75123456" pattern="[6-7][0-9]{7}" maxlength="8" required>
                <div class="invalid-feedback">Ingrese un celular válido (8 dígitos).</div>
              </div>
            </div>
          </div>

          <hr>

          <div class="alert alert-primary mt-3" role="alert">
            <i class="bi bi-info-circle"></i>
            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Guardar Docente
          </button>
        </div>

        <?php
  
         $DatosDocente = new DocentesControlador();
        $DatosDocente ->RegistrarDocenteControlador();
         ?>
        
      </form>
    </div>
  </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            

            // Validación personalizada del formulario
            const form = document.getElementById('formNuevoDocente');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);

            // Convertir complemento a mayúsculas
            $('#inputComplemento').on('input', function() {
                this.value = this.value.toUpperCase();
            });
            // Convertir Nombre a mayúsculas
             $('#inputNombres').on('input', function() {
                this.value = this.value.toUpperCase();
            });

              $('#apaterno').on('input', function() {
                this.value = this.value.toUpperCase();
            });
            $('#amaterno').on('input', function() {
                this.value = this.value.toUpperCase();
            });

            // Validar que solo se ingresen números en CI
            $('#inputCi, #inputTelefono, #inputCelular').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            });

            // Validar que solo se ingresen letras en nombres
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
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (age < 15 || (age === 15 && monthDiff < 0)) {
                    alert('El Docente debe tener al menos 18 años de edad.');
                    this.value = '';
                }
            });

            // Actualizar fecha automáticamente
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
            setInterval(actualizarFecha, 60000); // Actualizar cada minuto
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('#tablaDocentes').DataTable({
        responsive: true,
        dom: 'Bfrtip', // Posición de botones
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-primary' },
            { extend: 'csv', className: 'btn btn-sm btn-primary' },
            { extend: 'excel', className: 'btn btn-sm btn-primary' },
            { extend: 'pdf', className: 'btn btn-sm btn-primary' },
            { extend: 'print', className: 'btn btn-sm btn-primary' }
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay datos disponibles"
        }
    });
});
</script>


</body>