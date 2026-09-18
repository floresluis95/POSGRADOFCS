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
                  <h2>MATRICULACIÓN</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>VALIDAR VOUCHER DE MATRÍCULA</h3>
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
                <div class="col-lg-9">

                  <!-- Indicador de pasos -->
                  <div class="pasos-inscripcion mb-3">
                    <div class="paso-item active" id="pasoBuscar">
                      <span class="paso-numero">1</span> Buscar estudiante
                    </div>
                    <div class="paso-separador"></div>
                    <div class="paso-item" id="pasoValidar">
                      <span class="paso-numero">2</span> Validar voucher de matrícula
                    </div>
                  </div>

                  <!-- PASO 1: BUSCAR ESTUDIANTE CON PREREGISTRO PENDIENTE -->
                  <div class="kt-portlet inscripcion-portlet">
                    <div class="kt-portlet__head">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title">
                          <i class="flaticon2-search"></i> Buscar estudiante con pre-registro pendiente
                        </h3>
                      </div>
                    </div>
                    <div class="kt-portlet__body py-3">
                      <div class="row align-items-end">
                        <div class="col-lg-8">
                          <label class="filtro-label">C.I., nombre o apellido</label>
                          <div class="input-group input-group-sm">
                            <input type="text" id="buscarTermino" class="form-control"
                                   placeholder="Ej: 1234567 o Pérez" maxlength="60">
                            <div class="input-group-append">
                              <button type="button" id="btnBuscarPreregistro" class="btn btn-primary">
                                <i class="flaticon2-search"></i> Buscar
                              </button>
                            </div>
                          </div>
                          <small class="form-text text-muted">
                            <i class="flaticon2-information"></i> Solo se muestran estudiantes con una orden de pago de matrícula pendiente (generada en Pre-Registro).
                          </small>
                        </div>
                        <div class="col-lg-4 text-lg-right mt-2 mt-lg-0">
                          <a href="preregistro" class="btn btn-sm btn-outline-secondary btn-block">
                            <i class="flaticon2-plus"></i> Nuevo estudiante
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer->FooterResumidoControlador();
        ?>
      </div>
    </div>
  </div>

  <!-- ============================================ -->
  <!-- MODAL PASO 2: VALIDAR VOUCHER DE MATRÍCULA    -->
  <!-- (antes un panel inline; ahora modal, mismo contenido e ids). Se coloca
       fuera de #kt_wrapper, al mismo nivel que ModalResultadosBusqueda: dentro
       de #kt_wrapper el tema aplica transform a contenedores del layout, lo
       que rompe el position:fixed de un modal anidado ahí adentro. -->
  <!-- ============================================ -->
  <div class="modal fade" id="modalValidarVoucher" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white"><i class="fa fa-check-circle"></i> Validar Voucher de Matrícula</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Formulario de validación del voucher: envuelve todo el cuerpo del modal
             (igual que en los demás modales de esta app), aunque el submit arma su
             propio FormData a mano, no depende de la posición de los campos. -->
        <form id="formValidarVoucher" enctype="multipart/form-data">

          <div class="modal-body">
            <div id="panelDetalle" style="display:none;">

              <input type="hidden" id="detalleIdOrdenPago">

              <!-- Datos del estudiante -->
              <div class="seccion-detalle mb-3">
                <div class="seccion-detalle-titulo">Datos del estudiante</div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="label-modern">Nombre completo</div>
                    <div class="value-modern" id="detalleNombre"></div>
                  </div>
                  <div class="col-md-2">
                    <div class="label-modern">C.I.</div>
                    <div class="value-modern" id="detalleCi"></div>
                  </div>
                  <div class="col-md-4">
                    <div class="label-modern">Correo</div>
                    <div class="value-modern" id="detalleCorreo"></div>
                  </div>
                  <div class="col-md-3">
                    <div class="label-modern">Celular</div>
                    <div class="value-modern" id="detalleCelular"></div>
                  </div>
                </div>
              </div>

              <!-- Datos del programa -->
              <div class="seccion-detalle mb-3">
                <div class="seccion-detalle-titulo">Datos del programa</div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="label-modern">Programa</div>
                    <div class="value-modern"><span id="detallePrograma"></span> (<span id="detalleCodigo"></span>)</div>
                  </div>
                  <div class="col-md-2">
                    <div class="label-modern">Grado</div>
                    <div class="value-modern" id="detalleGrado"></div>
                  </div>
                  <div class="col-md-2">
                    <div class="label-modern">Sede</div>
                    <div class="value-modern" id="detalleSede"></div>
                  </div>
                  <div class="col-md-2">
                    <div class="label-modern">Modalidad</div>
                    <div class="value-modern"><span class="badge badge-secondary" id="detalleModalidad"></span></div>
                  </div>
                  <div class="col-md-2">
                    <div class="label-modern">Monto</div>
                    <div class="value-modern">Bs. <span id="detalleMonto">0.00</span></div>
                  </div>
                </div>
                <div class="mt-2">
                  <span class="label-modern">Plan del programa</span><br>
                  <span class="badge badge-warning" id="detalleEstadoPlan"><i class="fa fa-clock-o"></i> Pendiente de confirmación</span>
                </div>
              </div>

              <!-- PLAN DE PAGO DEL PROGRAMA: ya se eligió en Pre-Registro y queda
                   definitivo al validar el voucher; aquí solo se muestra como referencia. -->
              <div class="seccion-detalle mb-3">
                <div class="seccion-detalle-titulo">
                  <i class="fa fa-money"></i> Plan de pago del programa
                </div>
                <div id="planPropuestaInfo"></div>
              </div>

              <!-- DATOS DEL VOUCHER -->
              <div class="seccion-detalle mb-3">
                <div class="seccion-detalle-titulo">Datos del voucher</div>

                <div class="custom-control custom-checkbox mb-3">
                  <input type="checkbox" class="custom-control-input" id="sinPagoMatricula">
                  <label class="custom-control-label font-weight-bold" for="sinPagoMatricula">
                    Matricular sin pago de matrícula (el programa completo se cancelará después)
                  </label>
                </div>

                <div class="row" id="filaCamposVoucher">
                  <div class="col-lg-4 form-group mb-2" id="grupoVoucherNumero">
                    <label class="filtro-label">Código de voucher <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="voucherNumero" maxlength="25" required>
                  </div>
                  <div class="col-lg-4 form-group mb-2">
                    <label class="filtro-label">Fecha de inscripción <span class="text-danger">*</span></label>
                    <input type="date" class="form-control form-control-sm" id="voucherFecha" required
                           max="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div class="col-lg-4 form-group mb-2" id="grupoVoucherFoto">
                    <label class="filtro-label">Foto del voucher <span class="text-danger">*</span></label>
                    <div class="custom-file custom-file-sm">
                      <input type="file" class="custom-file-input" id="voucherFoto" accept="image/*" required>
                      <label class="custom-file-label" for="voucherFoto" id="voucherFotoLabel">Seleccionar imagen...</label>
                    </div>
                    <small class="form-text text-muted">JPG, PNG, GIF o WEBP (máx. 5MB)</small>
                  </div>
                </div>

                <div id="voucherFotoPreview" style="display:none;">
                  <img id="voucherFotoPreviewImg" src="" class="img-thumbnail" style="max-width: 140px;">
                </div>
              </div>

              <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:12px;" id="avisoValidacion">
                <i class="fa fa-exclamation-triangle"></i>
                Verifique que la matrícula ya fue cancelada en caja antes de validar. Esta acción
                <strong>inscribe formalmente</strong> al estudiante en el programa y no se puede deshacer desde aquí.
              </div>

            </div>
          </div>

          <div class="modal-footer">
            <button type="button" id="btnBuscarOtro" class="btn btn-outline-secondary">
              <i class="flaticon2-back"></i> Buscar otro estudiante
            </button>
            <button type="submit" class="btn btn-success">
              <i class="fa fa-check-circle"></i> Validar Inscripción
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Modal: Resultados de la búsqueda -->
  <div class="modal fade" id="ModalResultadosBusqueda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="flaticon2-search"></i> Resultados de la búsqueda</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="resultadosBusquedaBody" style="max-height: 60vh; overflow-y: auto;">
          <!-- Se llena por JS -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="vistas/recursos/sweetalert.min.js"></script>

  <script>
  function iniciarInscripcion() {

      function actualizarFecha() {
          const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          const fecha = new Date().toLocaleDateString('es-ES', opciones);
          $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
      }
      actualizarFecha();
      setInterval(actualizarFecha, 60000);

      // ============================================
      // BÚSQUEDA DE PREREGISTROS PENDIENTES
      // ============================================
      function buscarPreregistros() {
          const termino = $('#buscarTermino').val().trim();

          if (termino.length < 2) {
              swal('Atención', 'Ingrese al menos 2 caracteres para buscar', 'warning');
              return;
          }

          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: { accion: 'buscarPreregistrosPendientes', termino: termino },
              dataType: 'json',
              success: function(resp) {
                  if (!resp.success) {
                      swal('Atención', resp.mensaje || 'No se pudo realizar la búsqueda', 'warning');
                      return;
                  }
                  renderResultadosBusqueda(resp.resultados || []);
                  $('#ModalResultadosBusqueda').modal('show');
              },
              error: function() {
                  swal('Error', 'No se pudo realizar la búsqueda', 'error');
              }
          });
      }

      function renderResultadosBusqueda(lista) {
          if (!lista || lista.length === 0) {
              $('#resultadosBusquedaBody').html(
                  '<div class="text-center text-muted py-4"><i class="flaticon2-information"></i> No se encontraron preregistros pendientes con ese criterio</div>'
              );
              return;
          }

          let html = '<div class="list-group">';
          lista.forEach(function(pr) {
              const nombreCompleto = ((pr.Apaterno || '') + ' ' + (pr.Amaterno || '') + ' ' + (pr.Nombre || '')).trim();
              html += '' +
                  '<div class="list-group-item d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">' +
                      '<div>' +
                          '<div class="font-weight-bold">' + nombreCompleto + '</div>' +
                          '<small class="text-muted">CI: ' + (pr.Ci || '') + ' &nbsp;|&nbsp; ' + (pr.NombrePrograma || '') + ' (' + (pr.CodigoPrograma || '') + ')</small>' +
                      '</div>' +
                      '<button type="button" class="btn btn-sm btn-success btn-seleccionar-preregistro" data-id="' + pr.IdOrdenPago + '">' +
                          '<i class="fa fa-check"></i> Seleccionar' +
                      '</button>' +
                  '</div>';
          });
          html += '</div>';

          $('#resultadosBusquedaBody').html(html);
      }

      $('#btnBuscarPreregistro').on('click', buscarPreregistros);
      $('#buscarTermino').on('keypress', function(e) {
          if (e.which === 13) {
              e.preventDefault();
              buscarPreregistros();
          }
      });

      // ============================================
      // SELECCIONAR UN PREREGISTRO (muestra el panel de validación)
      // ============================================
      $(document).on('click', '.btn-seleccionar-preregistro', function() {
          const id = $(this).data('id');

          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: { accion: 'obtenerPreregistro', idOrdenPago: id },
              dataType: 'json',
              success: function(resp) {
                  if (!resp.success) {
                      swal('Error', resp.mensaje || 'No se pudo cargar el preregistro', 'error');
                      return;
                  }

                  const p = resp.preregistro;
                  const nombreCompleto = ((p.Apaterno || '') + ' ' + (p.Amaterno || '') + ' ' + (p.Nombre || '')).trim();

                  $('#detalleIdOrdenPago').val(p.IdOrdenPago);
                  $('#detalleNombre').text(nombreCompleto);
                  $('#detalleCi').text(p.CiCompleto || '');
                  $('#detalleCorreo').text(p.Correo || '-');
                  $('#detalleCelular').text(p.Celular || '-');
                  $('#detallePrograma').text(p.NombrePrograma || '');
                  $('#detalleCodigo').text(p.CodigoPrograma || '');
                  $('#detalleGrado').text(p.GradoAcademico || '');
                  $('#detalleSede').text(p.Sede || '-');
                  $('#detalleModalidad').text(p.Observaciones || (p.PagoCompleto == 1 ? 'Programa Completo' : 'Solo Matrícula'));
                  $('#detalleMonto').text(parseFloat(p.MontoFinal).toFixed(2));

                  // Reiniciar el formulario de validación
                  $('#formValidarVoucher')[0].reset();
                  $('#voucherFecha').val(new Date().toISOString().split('T')[0]);
                  $('#voucherFotoLabel').text('Seleccionar imagen...');
                  $('#voucherFotoPreview').hide();
                  $('#voucherFotoPreviewImg').attr('src', '');

                  // Si la matrícula ya vino exonerada (Bs. 0) desde Pre-Registro, se marca
                  // automáticamente "sin pago de matrícula" para no exigir un voucher que no existe.
                  const montoFinal = parseFloat(p.MontoFinal) || 0;
                  $('#sinPagoMatricula').prop('checked', montoFinal <= 0).trigger('change');

                  // El plan de pago del programa ya se definió en Pre-Registro; aquí solo se
                  // muestra como referencia. Queda definitivo al validar el voucher, salvo
                  // Grupal, que se arma después en Matriculados.
                  const planTipo = p.PlanPropuestoTipo || 'REGULAR';
                  const planPorcentaje = parseFloat(p.PlanPropuestoPorcentaje) || 0;
                  const nombresPlan = { REGULAR: 'Regular', CONTADO: 'al Contado', GRUPAL: 'Grupal' };

                  if (planTipo === 'GRUPAL') {
                      $('#planPropuestaInfo').html(
                          '<div class="alert alert-light border py-2 px-3 mb-0" style="font-size:12px;">' +
                          '<i class="fa fa-users text-primary"></i> Se eligió <strong>Plan Grupal</strong> en Pre-Registro. ' +
                          'No se fija ningún plan al validar este voucher: el estudiante quedará disponible para armarse ' +
                          'en un grupo después, desde <strong>Matriculados</strong>.</div>'
                      );
                      $('#detalleEstadoPlan').html('<i class="fa fa-clock-o"></i> Pendiente (Grupal, se arma en Matriculados)');
                  } else {
                      $('#planPropuestaInfo').html(
                          '<div class="alert alert-light border py-2 px-3 mb-0" style="font-size:12px;">' +
                          '<i class="fa fa-info-circle text-primary"></i> Plan definido en Pre-Registro: <strong>Plan ' +
                          nombresPlan[planTipo] + '</strong>' +
                          (planPorcentaje > 0 ? ' con <strong>' + planPorcentaje.toFixed(2) + '%</strong> de descuento' : '') +
                          '. Quedará definitivo al validar el voucher.</div>'
                      );
                      $('#detalleEstadoPlan').html('<i class="fa fa-clock-o"></i> Se confirmará al validar');
                  }

                  $('#ModalResultadosBusqueda').modal('hide');
                  $('#panelDetalle').slideDown();
                  $('#pasoBuscar').removeClass('active');
                  $('#pasoValidar').addClass('active');
                  // Nota: ya no se anima el scroll de la página hacia #panelDetalle
                  // (quedaba dentro de un modal oculto en este punto, así que
                  // .offset() daba {top:0} y el intento de mover el scroll del
                  // body mientras Bootstrap lo bloquea para abrir el modal es lo
                  // que generaba el aviso de "scroll-linked effect" y trababa el
                  // scroll). El modal maneja su propio scroll interno.
              },
              error: function() {
                  swal('Error', 'No se pudo cargar el preregistro', 'error');
              }
          });
      });

      // Volver a la búsqueda
      $('#btnBuscarOtro').on('click', function() {
          $('#panelDetalle').slideUp();
          $('#pasoValidar').removeClass('active');
          $('#pasoBuscar').addClass('active');
          $('#buscarTermino').val('').focus();
      });

      // Vista previa de la foto del voucher
      $('#voucherFoto').on('change', function() {
          const file = this.files[0];
          const fileName = file ? file.name : 'Seleccionar imagen...';
          $('#voucherFotoLabel').text(fileName);

          if (file && file.type.startsWith('image/')) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  $('#voucherFotoPreviewImg').attr('src', e.target.result);
                  $('#voucherFotoPreview').show();
              };
              reader.readAsDataURL(file);
          } else {
              $('#voucherFotoPreview').hide();
          }
      });

      // ============================================
      // PLAN DE PAGO DEL PROGRAMA (posgrado, aparte de la matrícula)
      // Se define en Pre-Registro; el backend lo confirma automáticamente en la misma
      // transacción en que se valida el voucher de matrícula (ver ValidarVoucherMatriculaModelo).
      // ============================================

      // ============================================
      // "MATRICULAR SIN PAGO DE MATRÍCULA": oculta los campos de voucher (no hay nada que
      // validar) y avisa que el programa completo se cobrará después, desde Matriculados.
      // Se marca automáticamente si la matrícula ya vino exonerada desde Pre-Registro, pero
      // también se puede activar manualmente aquí para cualquier preregistro.
      // ============================================
      $('#sinPagoMatricula').on('change', function() {
          const sinPago = $(this).is(':checked');

          $('#grupoVoucherNumero, #grupoVoucherFoto').toggle(!sinPago);
          $('#voucherNumero').prop('required', !sinPago);
          $('#voucherFoto').prop('required', !sinPago);

          if (sinPago) {
              $('#avisoValidacion').html(
                  '<i class="fa fa-info-circle"></i> Se inscribirá al estudiante <strong>sin registrar pago de matrícula</strong>. ' +
                  'El programa completo se deberá cancelar después, desde <strong>Matriculados</strong>.'
              );
          } else {
              $('#avisoValidacion').html(
                  '<i class="fa fa-exclamation-triangle"></i> Verifique que la matrícula ya fue cancelada en caja antes de validar. Esta acción ' +
                  '<strong>inscribe formalmente</strong> al estudiante en el programa y no se puede deshacer desde aquí.'
              );
          }
      });

      // ============================================
      // VALIDAR EL VOUCHER (inscribe formalmente al estudiante)
      // ============================================
      $('#formValidarVoucher').on('submit', function(e) {
          e.preventDefault();

          const idOrdenPago = $('#detalleIdOrdenPago').val();
          const sinPago = $('#sinPagoMatricula').is(':checked');
          const numeroVoucher = sinPago ? 'SIN MATRÍCULA' : $('#voucherNumero').val().trim();
          const fechaInscripcion = $('#voucherFecha').val();

          if (!sinPago && !numeroVoucher) {
              swal('Atención', 'Ingrese el código de voucher', 'warning');
              return;
          }
          if (!fechaInscripcion) {
              swal('Atención', 'Seleccione la fecha de inscripción', 'warning');
              return;
          }

          const archivoFoto = $('#voucherFoto')[0].files[0];
          if (!sinPago && !archivoFoto) {
              swal('Atención', 'Debe subir la foto del voucher', 'warning');
              return;
          }

          const formData = new FormData();
          formData.append('accion', 'validarVoucherMatricula');
          formData.append('idOrdenPago', idOrdenPago);
          formData.append('numeroVoucher', numeroVoucher);
          formData.append('fechaInscripcion', fechaInscripcion);
          formData.append('sinMatricula', sinPago ? '1' : '0');

          if (!sinPago) {
              formData.append('fotoVoucher', archivoFoto);
          }

          const btnSubmit = $(this).find('button[type="submit"]');
          btnSubmit.prop('disabled', true);

          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(resp) {
                  btnSubmit.prop('disabled', false);

                  if (resp.success) {
                      if (sinPago) {
                          swal({
                              title: '¡Estudiante inscrito!',
                              text: 'Quedó inscrito sin pago de matrícula. A continuación se le llevará a Matriculados para registrar el pago del programa completo.',
                              icon: 'success',
                              buttons: false,
                              timer: 2500
                          }).then(function() {
                              window.location.href = 'matriculados';
                          });
                          return;
                      }

                      swal({
                          title: '¡Voucher validado!',
                          text: 'El estudiante quedó inscrito formalmente en el programa y su plan de pago quedó CONFIRMADO.',
                          icon: 'success',
                          buttons: false,
                          timer: 2200
                      }).then(function() {
                          $('#panelDetalle').slideUp();
                          $('#pasoValidar').removeClass('active');
                          $('#pasoBuscar').addClass('active');
                          $('#buscarTermino').val('').focus();
                      });
                  } else {
                      swal('Error', resp.mensaje || 'No se pudo validar el voucher', 'error');
                  }
              },
              error: function() {
                  btnSubmit.prop('disabled', false);
                  swal('Error', 'No se pudo validar el voucher', 'error');
              }
          });
      });

      // ============================================
      // MODAL "VALIDAR VOUCHER DE MATRÍCULA": puramente de presentación. Todo
      // el código de arriba sigue igual (busca, valida y hace slideDown()/
      // slideUp() sobre #panelDetalle exactamente como antes); este bloque
      // solo abre/cierra el modal que ahora lo contiene, enganchándose a los
      // mismos eventos AJAX ya existentes (no duplica ninguna validación ni
      // toca los manejadores de arriba).
      // ============================================
      $(document).ajaxSuccess(function(event, jqXHR, settings, respuesta) {
          // Se cargó el detalle de un preregistro seleccionado: abrir el modal.
          // Si el modal de resultados todavía se está cerrando (el código de
          // arriba ya llamó a su .modal('hide')), se espera a que termine esa
          // transición antes de abrir el siguiente: mostrar un modal mientras
          // otro todavía se está ocultando es lo que deja el scroll bloqueado
          // en Bootstrap 4 (backdrop duplicado).
          if (typeof settings.data === 'string' && settings.data.indexOf('accion=obtenerPreregistro') !== -1 && respuesta && respuesta.success) {
              if ($('#ModalResultadosBusqueda').hasClass('show')) {
                  $('#ModalResultadosBusqueda').one('hidden.bs.modal', function() {
                      $('#modalValidarVoucher').modal('show');
                  });
              } else {
                  $('#modalValidarVoucher').modal('show');
              }
          }
          // Se validó el voucher correctamente: cerrar el modal (el mensaje de
          // éxito y la limpieza del panel los sigue manejando el código de arriba).
          if (settings.data instanceof FormData && settings.data.get('accion') === 'validarVoucherMatricula' && respuesta && respuesta.success) {
              $('#modalValidarVoucher').modal('hide');
          }
      });

      $('#btnBuscarOtro').on('click', function() {
          $('#modalValidarVoucher').modal('hide');
      });

      console.log('=== INSCRIPCIÓN (VALIDAR VOUCHER) INICIALIZADO ===');
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', iniciarInscripcion);
  } else {
      iniciarInscripcion();
  }
  </script>

<style>
.inscripcion-portlet {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: none;
}

.inscripcion-portlet .kt-portlet__head {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    min-height: 46px;
}

.inscripcion-portlet .kt-portlet__head-title {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #3b3f5c;
}

/* Indicador de pasos */
.pasos-inscripcion {
    display: flex;
    align-items: center;
}

.paso-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #9aa1b1;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.paso-item.active {
    color: #3b3f5c;
}

.paso-numero {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    font-size: 12px;
    font-weight: 700;
}

.paso-item.active .paso-numero {
    background-color: #3b3f5c;
    color: #fff;
}

.paso-separador {
    flex: 1;
    height: 1px;
    background-color: #dee2e6;
    margin: 0 14px;
}

/* #formValidarVoucher envuelve el modal-body y el modal-footer (ver comentario
   junto a su apertura en el HTML). Bootstrap necesita que ambos sean hijos
   directos de .modal-content para que el layout flex de modal-dialog-scrollable
   funcione (.modal-body con flex:1 1 auto + overflow-y:auto); si quedan dentro
   de un <form> normal, ese flex no se aplica y el contenido se corta sin poder
   desplazarse. display:contents saca al form del árbol de cajas sin quitarlo
   del DOM, así el scroll interno vuelve a funcionar y el submit sigue igual. */
#formValidarVoucher {
    display: contents;
}

/* Secciones formales dentro del detalle */
.seccion-detalle {
    padding: 12px 16px;
    background-color: #fbfbfd;
    border: 1px solid #e1e3ea;
    border-radius: 6px;
}

.seccion-detalle-titulo {
    font-size: 12px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e1e3ea;
}

.label-modern {
    font-size: 0.72rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.value-modern {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2b2b2b;
}

.filtro-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.custom-file-sm .custom-file-label,
.custom-file-sm .custom-file-input {
    height: calc(1.5em + 0.5rem + 2px);
    font-size: 0.775rem;
}

.custom-file-sm .custom-file-label {
    padding: 0.25rem 0.5rem;
    line-height: 1.5;
}

.custom-file-sm .custom-file-label::after {
    height: calc(1.5em + 0.5rem);
    padding: 0.25rem 0.5rem;
    line-height: 1.5;
}
</style>

</body>
</html>
