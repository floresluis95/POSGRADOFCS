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
<div class="app-content content">
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
                  <button data-toggle="modal" data-target="#ModalInsertarEstudiante" type="button" class="btn btn-light btn-sm">
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
                                <select class="form-control" id="IdProfesion" name="IdProfesion" required>
                                    <option value="">Seleccione una profesión...</option>
                                    <?php
                                        $ListaProfesion = new ProfesionControlador();
                                        $ListaProfesion->ListaProfesionControlador();
                                    ?>
                                </select>
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
              <li><strong>Contraseña:</strong> Primera letra del nombre + Número de Carnet</li>
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

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
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

    // Obtener la primera letra del nombre en mayúscula
    var primeraLetra = nombrePila ? nombrePila.charAt(0).toUpperCase() : 'X';

    // Generar usuario (será el CI)
    var usuario = ci;

    // Generar contraseña (primera letra del nombre + CI)
    var password = primeraLetra + ci;

    // Llenar campos hidden que se enviarán al servidor
    jQuery('#estudiante_id').val(estudianteID);
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

// Validación del formulario de nuevo estudiante
jQuery(document).ready(function() {
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
});
</script>

</body>
