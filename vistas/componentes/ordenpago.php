<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir controlador
require_once 'controladores/ordenpago.controlador.php';

// Procesar formulario
$ordenPago = new OrdenPagoControladores();
$ordenPago->RegistrarOrdenPagoControlador();
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
                  <h2>ORDEN DE PAGO</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>GENERAR ORDEN DE PAGO</h3>
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
              <div class="row">
                <div class="col-lg-12">

                  <form method="POST" id="formOrdenPago">

                    <!-- PASO 1: SELECCIONAR ESTUDIANTE -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-user-outline-symbol"></i> PASO 1: SELECCIONAR ESTUDIANTE
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">
                        <div class="row">
                          <div class="col-lg-12">
                            <label class="font-weight-bold">Buscar Estudiante por CI:</label>
                            <select class="form-control kt-select2 kt-select2-general" name="idcliente" id="selectEstudiante" required>
                              <option value="">Buscar por cédula de identidad...</option>
                              <?php
                                $Lista = new EstudiantesControladores();
                                $Lista->EstudianteActivoControlador();
                              ?>
                            </select>
                            <small class="text-muted">
                              <i class="flaticon2-information"></i> Busque al estudiante por su cédula de identidad. Si no existe, debe registrarlo primero en el módulo de Estudiantes.
                            </small>
                          </div>
                        </div>

                        <!-- Tabla de datos del estudiante -->
                        <div id="tablaEstudiante" style="display: none; margin-top: 20px;">
                          <h5 class="font-weight-bold mb-3">
                            <i class="flaticon2-information"></i> Datos del Estudiante Seleccionado
                          </h5>
                          <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                              <tbody>
                                <tr>
                                  <th width="25%" style="background-color: #f8f9fa;">Nombre Completo:</th>
                                  <td id="datosNombre"></td>
                                  <th width="20%" style="background-color: #f8f9fa;">CI:</th>
                                  <td id="datosCI"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Correo:</th>
                                  <td id="datosCorreo"></td>
                                  <th style="background-color: #f8f9fa;">Celular:</th>
                                  <td id="datosCelular"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- PASO 2: SELECCIONAR PROGRAMA -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4" id="seccionPrograma" style="display: none;">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-writing"></i> PASO 2: SELECCIONAR PROGRAMA
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">
                        <div class="row">
                          <div class="col-lg-4">
                            <label class="font-weight-bold">Grado Académico:</label>
                            <select class="form-control" id="gradoAcademico" name="gradoAcademico" required>
                              <option value="">Seleccione el grado...</option>
                              <option value="DIPLOMADO">DIPLOMADO</option>
                              <option value="MAESTRIA">MAESTRÍA</option>
                              <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                            </select>
                          </div>
                          <div class="col-lg-8">
                            <label class="font-weight-bold">Programa:</label>
                            <select class="form-control" id="programa" name="programa" required>
                              <option value="">Seleccione un programa...</option>
                            </select>
                          </div>
                        </div>

                        <!-- Tabla de datos del programa -->
                        <div id="tablaPrograma" style="display: none; margin-top: 20px;">
                          <h5 class="font-weight-bold mb-3">
                            <i class="flaticon2-document"></i> Datos del Programa Seleccionado
                          </h5>
                          <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                              <tbody>
                                <tr>
                                  <th width="25%" style="background-color: #f8f9fa;">Programa:</th>
                                  <td id="proNombre"></td>
                                  <th width="20%" style="background-color: #f8f9fa;">Código:</th>
                                  <td id="proCodigo"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Grado:</th>
                                  <td id="proGrado"></td>
                                  <th style="background-color: #f8f9fa;">Duración:</th>
                                  <td id="proDuracion"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Costo Matrícula:</th>
                                  <td id="proMatricula" class="font-weight-bold text-primary"></td>
                                  <th style="background-color: #f8f9fa;">Costo Programa:</th>
                                  <td id="proPrograma" class="font-weight-bold text-primary"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Módulos:</th>
                                  <td id="proModulos"></td>
                                  <th style="background-color: #f8f9fa;">Sede:</th>
                                  <td id="proSede"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- PASO 3: ESPECIFICAR TIPO DE PAGO -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4" id="seccionPago" style="display: none;">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-crisp-icons"></i> PASO 3: ESPECIFICAR TIPO DE PAGO
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">

                        <!-- Opción de tipo de pago -->
                        <div class="row mb-4">
                          <div class="col-lg-12">
                            <h5 class="font-weight-bold mb-3">Seleccione el tipo de pago:</h5>
                            <div class="row">
                              <div class="col-lg-6">
                                <div class="card border border-primary" style="cursor: pointer;" id="cardSoloMatricula">
                                  <div class="card-body text-center">
                                    <div class="custom-control custom-radio">
                                      <input type="radio" class="custom-control-input" id="radioSoloMatricula" name="tipoPago" value="0" checked>
                                      <label class="custom-control-label" for="radioSoloMatricula">
                                        <h4 class="font-weight-bold text-primary mb-2">
                                          <i class="flaticon2-file"></i> SOLO MATRÍCULA
                                        </h4>
                                      </label>
                                    </div>
                                    <p class="text-muted mb-0">Generar orden de pago únicamente por el costo de matrícula</p>
                                  </div>
                                </div>
                              </div>
                              <div class="col-lg-6">
                                <div class="card border" style="cursor: pointer;" id="cardPagoCompleto">
                                  <div class="card-body text-center">
                                    <div class="custom-control custom-radio">
                                      <input type="radio" class="custom-control-input" id="radioPagoCompleto" name="tipoPago" value="1">
                                      <label class="custom-control-label" for="radioPagoCompleto">
                                        <h4 class="font-weight-bold text-success mb-2">
                                          <i class="flaticon2-check-mark"></i> PROGRAMA COMPLETO
                                        </h4>
                                      </label>
                                    </div>
                                    <p class="text-muted mb-0">Generar orden de pago por el programa completo (Matrícula + Programa)</p>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Área para SOLO MATRÍCULA -->
                        <div id="areaSoloMatricula">
                          <div class="alert alert-info">
                            <h5><i class="flaticon2-information"></i> Orden de Pago: Solo Matrícula</h5>
                            <p class="mb-0">Se generará una orden de pago únicamente por el costo de matrícula del programa seleccionado.</p>
                          </div>
                          <div class="row">
                            <div class="col-lg-4">
                              <label class="font-weight-bold">Monto de Matrícula:</label>
                              <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="montoSoloMatricula" readonly>
                              </div>
                            </div>
                            <div class="col-lg-4">
                              <label class="font-weight-bold">Fecha de Orden:</label>
                              <input type="date" class="form-control" name="fechaInscripcion" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                          </div>
                        </div>

                        <!-- Área para PROGRAMA COMPLETO -->
                        <div id="areaPagoCompleto" style="display: none;">
                          <div class="alert alert-success">
                            <h5><i class="flaticon2-check-mark"></i> Orden de Pago: Programa Completo</h5>
                            <p class="mb-0">Se generará una orden de pago por el costo total del programa (Matrícula + Programa). Puede aplicar descuentos.</p>
                          </div>

                          <div class="row">
                            <div class="col-lg-3">
                              <label class="font-weight-bold">Matrícula:</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="text" class="form-control" id="montoMatriculaPago" readonly>
                              </div>
                            </div>
                            <div class="col-lg-3">
                              <label class="font-weight-bold">Programa:</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="text" class="form-control" id="montoProgramaPago" readonly>
                              </div>
                            </div>
                            <div class="col-lg-3">
                              <label class="font-weight-bold">Total Original:</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text bg-primary text-white">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="montoTotalOriginal" readonly>
                              </div>
                            </div>
                          </div>

                          <hr class="my-4">

                          <h5 class="font-weight-bold mb-3">Descuento (opcional):</h5>
                          <div class="row">
                            <div class="col-lg-3">
                              <label>Descuento:</label>
                              <div class="input-group">
                                <input type="number" class="form-control" id="inputDescuento" placeholder="0.00" min="0" step="0.01">
                                <div class="input-group-append">
                                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" id="tipoDescuentoBtn">
                                    Bs.
                                  </button>
                                  <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#" data-tipo="monto">Bs. (Bolivianos)</a>
                                    <a class="dropdown-item" href="#" data-tipo="porcentaje">% (Porcentaje)</a>
                                  </div>
                                </div>
                              </div>
                              <small class="text-muted" id="textoDescuento">Ingrese el descuento en Bs.</small>
                            </div>
                            <div class="col-lg-3">
                              <label>Monto Descuento:</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text bg-success text-white">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold text-success" id="montoDescuentoDisplay" value="0.00" readonly>
                              </div>
                            </div>
                            <div class="col-lg-3">
                              <label class="font-weight-bold">TOTAL A PAGAR:</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text bg-danger text-white">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold text-danger" id="montoTotalPagar" readonly>
                              </div>
                            </div>
                            <div class="col-lg-3">
                              <label class="font-weight-bold">Fecha de Orden:</label>
                              <input type="date" class="form-control" name="fechaInscripcionCompleto" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                          </div>
                        </div>

                        <!-- Campos hidden -->
                        <input type="hidden" name="costoTotalPrograma" id="costoTotalPrograma" value="0">
                        <input type="hidden" name="costoMatriculaPrograma" id="costoMatriculaPrograma" value="0">
                        <input type="hidden" name="costoTotalConMatricula" id="costoTotalConMatricula" value="0">
                        <input type="hidden" name="montoAPagar" id="montoAPagar" value="0">
                        <input type="hidden" name="porcentajeDescuento" id="porcentajeDescuento" value="0">
                        <input type="hidden" name="montoDescuento" id="montoDescuento" value="0">
                        <input type="hidden" name="pagoCompleto" id="pagoCompleto" value="0">

                        <!-- Botones -->
                        <hr class="my-4">
                        <div class="row">
                          <div class="col-lg-12 text-right">
                            <button type="reset" class="btn btn-secondary btn-lg">
                              <i class="flaticon2-reload"></i> Limpiar
                            </button>
                            <button type="submit" name="registrarOrdenPago" class="btn btn-success btn-lg">
                              <i class="flaticon2-files-and-folders"></i> Generar Orden de Pago
                            </button>
                          </div>
                        </div>

                      </div>
                    </div>

                  </form>

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

<!-- Modal Insertar Estudiante -->
<!-- TODO: Agregar modal de insertar estudiante si se require -->
<?php // include 'vistas/modales/insertar-estudiante.php'; ?>

<!-- Scripts adicionales (después de cargar jQuery desde Footer) -->
<script src="vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js"></script>
<script src="vistas/recursos/sweetalert.min.js"></script>

<script>
$(document).ready(function() {

    console.log('=== INICIANDO ORDEN DE PAGO ===');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Select2 disponible:', typeof $.fn.select2);

    // Variable para tipo de descuento
    let tipoDescuento = 'monto';

    // ============================================
    // PASO 1: SELECCIONAR ESTUDIANTE
    // ============================================

    // IMPORTANTE: Inicializar Select2 DESPUÉS de definir el evento change
    $('.kt-select2-general').select2({
        placeholder: "Buscar por cédula de identidad...",
        allowClear: true
    });

    console.log('Select2 inicializado en elementos:', $('.kt-select2-general').length);
    console.log('ID del select de estudiante:', $('#selectEstudiante').attr('id'));
    console.log('Tiene clase kt-select2-general:', $('#selectEstudiante').hasClass('kt-select2-general'));

    // Manejar cambio con Select2 (usar 'select2:select' en lugar de 'change')
    $('#selectEstudiante').on('select2:select change', function(e) {
        const estudianteID = $(this).val();

        console.log('=== EVENTO DISPARADO ===');
        console.log('Tipo de evento:', e.type);
        console.log('Estudiante ID seleccionado:', estudianteID);
        console.log('Valor del select:', $('#selectEstudiante').val());

        if (estudianteID) {
            // Obtener datos del estudiante
            $.ajax({
                url: 'ajax/estudiantes.ajax.php',
                type: 'POST',
                data: { idestudiante: estudianteID },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta del servidor:', response);

                    if (response && !response.error) {
                        // Llenar tabla de datos
                        const nombreCompleto = (response.Apaterno || '') + ' ' + (response.Amaterno || '') + ' ' + (response.Nombre || '');
                        const ci = (response.Ci || '') + (response.Complemento ? '-' + response.Complemento : '') + ' ' + (response.Exp || '');

                        $('#datosNombre').text(nombreCompleto.trim());
                        $('#datosCI').text(ci);
                        $('#datosCorreo').text(response.Correo || '-');
                        $('#datosCelular').text(response.Celular || '-');

                        // Mostrar tabla y siguiente sección
                        $('#tablaEstudiante').slideDown();
                        $('#seccionPrograma').slideDown();
                    } else {
                        swal("Error", response.error || "No se encontraron datos del estudiante", "error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText);
                    swal("Error", "No se pudieron obtener los datos del estudiante: " + error, "error");
                }
            });
        } else {
            $('#tablaEstudiante').slideUp();
            $('#seccionPrograma').slideUp();
            $('#seccionPago').slideUp();
        }
    });

    // ============================================
    // PASO 2: SELECCIONAR GRADO Y PROGRAMA
    // ============================================
    $('#gradoAcademico').on('change', function() {
        const grado = $(this).val();

        $('#tablaPrograma').slideUp();
        $('#seccionPago').slideUp();

        if (grado) {
            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { grado: grado },
                dataType: 'json',
                success: function(response) {
                    $('#programa').empty().append('<option value="">Seleccione un programa</option>');

                    if (response.length > 0) {
                        response.forEach(function(p) {
                            $('#programa').append('<option value="' + p.ProgramaID + '">' + p.Codigo + ' - ' + p.NombrePrograma + '</option>');
                        });
                    }
                },
                error: function() {
                    swal("Error", "No se pudieron obtener los programas", "error");
                }
            });
        } else {
            $('#programa').empty().append('<option value="">Seleccione un grado primero</option>');
        }
    });

    $('#programa').on('change', function() {
        const programaId = $(this).val();

        if (programaId) {
            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { idprograma: programaId },
                dataType: 'json',
                success: function(respuesta) {
                    // Llenar tabla de datos del programa
                    $('#proNombre').text(respuesta.NombrePrograma);
                    $('#proCodigo').text(respuesta.Codigo);
                    $('#proGrado').text(respuesta.GradoAcademico);
                    $('#proDuracion').text(respuesta.DuracionMeses + ' meses');
                    $('#proModulos').text(respuesta.Modulos);
                    $('#proSede').text(respuesta.Sede);

                    const costoMatricula = parseFloat(respuesta.CostoMatricula) || 0;
                    const costoPrograma = parseFloat(respuesta.Costo) || 0;

                    $('#proMatricula').text('Bs. ' + costoMatricula.toFixed(2));
                    $('#proPrograma').text('Bs. ' + costoPrograma.toFixed(2));

                    // Guardar en campos hidden
                    $('#costoMatriculaPrograma').val(costoMatricula.toFixed(2));
                    $('#costoTotalPrograma').val(costoPrograma.toFixed(2));

                    // Mostrar tabla y siguiente sección
                    $('#tablaPrograma').slideDown();
                    $('#seccionPago').slideDown();

                    // Establecer monto de solo matrícula
                    $('#montoSoloMatricula').val(costoMatricula.toFixed(2));

                    // Establecer montos para pago completo
                    $('#montoMatriculaPago').val(costoMatricula.toFixed(2));
                    $('#montoProgramaPago').val(costoPrograma.toFixed(2));
                    const total = costoMatricula + costoPrograma;
                    $('#montoTotalOriginal').val(total.toFixed(2));
                    $('#montoTotalPagar').val(total.toFixed(2));
                    $('#costoTotalConMatricula').val(total.toFixed(2));
                },
                error: function() {
                    swal("Error", "No se pudieron obtener los detalles del programa", "error");
                }
            });
        } else {
            $('#tablaPrograma').slideUp();
            $('#seccionPago').slideUp();
        }
    });

    // ============================================
    // PASO 3: TIPO DE PAGO
    // ============================================

    // Click en las cards
    $('#cardSoloMatricula').on('click', function() {
        $('#radioSoloMatricula').prop('checked', true).trigger('change');
    });

    $('#cardPagoCompleto').on('click', function() {
        $('#radioPagoCompleto').prop('checked', true).trigger('change');
    });

    // Cambio de radio buttons
    $('input[name="tipoPago"]').on('change', function() {
        const valor = $(this).val();

        // Actualizar estilos de cards
        if (valor === '0') {
            $('#cardSoloMatricula').removeClass('border').addClass('border border-primary');
            $('#cardPagoCompleto').removeClass('border-success').addClass('border');

            // Mostrar área correspondiente
            $('#areaSoloMatricula').slideDown();
            $('#areaPagoCompleto').slideUp();

            // Establecer valores
            $('#pagoCompleto').val('0');
            const costoMatricula = parseFloat($('#costoMatriculaPrograma').val()) || 0;
            $('#montoAPagar').val(costoMatricula.toFixed(2));

        } else {
            $('#cardSoloMatricula').removeClass('border-primary').addClass('border');
            $('#cardPagoCompleto').removeClass('border').addClass('border border-success');

            // Mostrar área correspondiente
            $('#areaSoloMatricula').slideUp();
            $('#areaPagoCompleto').slideDown();

            // Establecer valores
            $('#pagoCompleto').val('1');
            const total = parseFloat($('#costoTotalConMatricula').val()) || 0;
            $('#montoAPagar').val(total.toFixed(2));
        }
    });

    // ============================================
    // CÁLCULO DE DESCUENTOS
    // ============================================

    // Cambiar tipo de descuento
    $('.dropdown-menu a[data-tipo]').on('click', function(e) {
        e.preventDefault();
        tipoDescuento = $(this).data('tipo');

        if (tipoDescuento === 'porcentaje') {
            $('#tipoDescuentoBtn').text('%');
            $('#textoDescuento').text('Ingrese el descuento en %');
            $('#inputDescuento').attr('max', '100').attr('placeholder', '0.00 %');
        } else {
            $('#tipoDescuentoBtn').text('Bs.');
            $('#textoDescuento').text('Ingrese el descuento en Bs.');
            const total = parseFloat($('#montoTotalOriginal').val()) || 0;
            $('#inputDescuento').attr('max', total).attr('placeholder', '0.00 Bs.');
        }

        $('#inputDescuento').trigger('input');
    });

    // Calcular descuento
    $('#inputDescuento').on('input', function() {
        const totalOriginal = parseFloat($('#montoTotalOriginal').val()) || 0;
        let valorIngresado = parseFloat($(this).val()) || 0;
        let montoDescuentoCalc = 0;
        let porcentajeCalc = 0;

        if (tipoDescuento === 'porcentaje') {
            // Validar rango
            if (valorIngresado > 100) {
                valorIngresado = 100;
                $(this).val(100);
            }
            if (valorIngresado < 0) {
                valorIngresado = 0;
                $(this).val(0);
            }

            porcentajeCalc = valorIngresado;
            montoDescuentoCalc = (totalOriginal * valorIngresado) / 100;

        } else {
            // Validar rango
            if (valorIngresado > totalOriginal) {
                valorIngresado = totalOriginal;
                $(this).val(totalOriginal);
            }
            if (valorIngresado < 0) {
                valorIngresado = 0;
                $(this).val(0);
            }

            montoDescuentoCalc = valorIngresado;
            porcentajeCalc = totalOriginal > 0 ? (valorIngresado / totalOriginal) * 100 : 0;
        }

        const montoFinal = totalOriginal - montoDescuentoCalc;

        // Actualizar displays
        $('#montoDescuentoDisplay').val(montoDescuentoCalc.toFixed(2));
        $('#montoTotalPagar').val(montoFinal.toFixed(2));

        // Actualizar hidden fields
        $('#montoDescuento').val(montoDescuentoCalc.toFixed(2));
        $('#porcentajeDescuento').val(porcentajeCalc.toFixed(2));
        $('#montoAPagar').val(montoFinal.toFixed(2));
    });

    // ============================================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ============================================
    $('#formOrdenPago').on('submit', function(e) {
        console.log('=== FORMULARIO EN PROCESO DE ENVÍO ===');

        // Obtener el tipo de pago
        const tipoPago = $('input[name="tipoPago"]:checked').val();

        // Copiar la fecha correcta según el tipo de pago
        if (tipoPago === '0') {
            // Solo matrícula: usar fechaInscripcion
            const fechaMatricula = $('input[name="fechaInscripcion"]').val();
            if (!fechaMatricula) {
                e.preventDefault();
                swal("Error", "Por favor seleccione la fecha de orden", "error");
                return false;
            }
        } else {
            // Pago completo: copiar fechaInscripcionCompleto a fechaInscripcion
            const fechaCompleto = $('input[name="fechaInscripcionCompleto"]').val();
            if (!fechaCompleto) {
                e.preventDefault();
                swal("Error", "Por favor seleccione la fecha de orden", "error");
                return false;
            }
            // Crear campo hidden con el nombre correcto
            $('<input>').attr({
                type: 'hidden',
                name: 'fechaInscripcion',
                value: fechaCompleto
            }).appendTo('#formOrdenPago');
        }

        // Validar que se haya seleccionado estudiante y programa
        const estudianteID = $('#selectEstudiante').val();
        const programaID = $('#programa').val();

        if (!estudianteID) {
            e.preventDefault();
            swal("Error", "Por favor seleccione un estudiante", "error");
            return false;
        }

        if (!programaID) {
            e.preventDefault();
            swal("Error", "Por favor seleccione un programa", "error");
            return false;
        }

        // Validar que el monto a pagar sea mayor a 0
        const montoAPagar = parseFloat($('#montoAPagar').val()) || 0;
        if (montoAPagar <= 0) {
            e.preventDefault();
            swal("Error", "El monto a pagar debe ser mayor a 0", "error");
            return false;
        }

        console.log('Formulario validado correctamente');
        console.log('Tipo de pago:', tipoPago === '0' ? 'Solo Matrícula' : 'Programa Completo');
        console.log('Monto a pagar:', montoAPagar);
        console.log('Descuento:', $('#montoDescuento').val());

        // Permitir que el formulario se envíe
        return true;
    });

    // ============================================
    // RESET FORM
    // ============================================
    $('button[type="reset"]').on('click', function() {
        location.reload();
    });

    console.log('=== ORDEN DE PAGO INICIALIZADO CORRECTAMENTE ===');
    console.log('Eventos registrados en #selectEstudiante');

});
</script>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.kt-portlet__head {
    border-radius: 4px 4px 0 0;
}

.table th {
    font-weight: 600;
}

.custom-control-label {
    cursor: pointer;
    width: 100%;
}

#cardSoloMatricula:hover,
#cardPagoCompleto:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

</body>
</html>
