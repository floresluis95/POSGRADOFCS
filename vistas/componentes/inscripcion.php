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

                  <!-- BUSCAR ESTUDIANTE CON PREREGISTRO PENDIENTE -->
                  <div class="kt-portlet inscripcion-portlet">
                    <div class="kt-portlet__body py-3">
                      <div class="row align-items-end">
                        <div class="col-lg-8">
                          <label class="small font-weight-bold mb-1">
                            <i class="flaticon2-search text-primary"></i> Buscar estudiante con pre-registro pendiente
                          </label>
                          <div class="input-group input-group-sm">
                            <input type="text" id="buscarTermino" class="form-control"
                                   placeholder="C.I., nombre o apellido..." maxlength="60">
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
                          <a href="preregistro" class="btn btn-sm btn-outline-primary btn-block">
                            <i class="flaticon2-plus"></i> Nuevo Estudiante
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- DETALLE DEL PREREGISTRO SELECCIONADO / VALIDACIÓN DEL VOUCHER -->
                  <div class="kt-portlet mt-3 inscripcion-portlet" id="panelDetalle" style="display:none;">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); min-height: 46px;">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white; font-size: 14px;">
                          <i class="fa fa-check-circle"></i> Validar Voucher de Matrícula
                        </h3>
                      </div>
                      <div class="kt-portlet__head-toolbar">
                        <button type="button" id="btnBuscarOtro" class="btn btn-sm btn-light">
                          <i class="flaticon2-back"></i> Buscar otro estudiante
                        </button>
                      </div>
                    </div>
                    <div class="kt-portlet__body py-3">

                      <input type="hidden" id="detalleIdOrdenPago">

                      <!-- Datos del estudiante -->
                      <div class="chip-summary mb-2">
                        <div class="chip-summary-row">
                          <span><i class="flaticon2-user-outline-symbol"></i> <strong id="detalleNombre"></strong></span>
                          <span>CI: <span id="detalleCi"></span></span>
                          <span id="detalleCorreo"></span>
                          <span id="detalleCelular"></span>
                        </div>
                      </div>

                      <!-- Datos del programa -->
                      <div class="chip-summary mb-2">
                        <div class="chip-summary-row flex-wrap">
                          <span><strong id="detallePrograma"></strong> (<span id="detalleCodigo"></span>)</span>
                          <span>Grado: <span id="detalleGrado"></span></span>
                          <span>Sede: <span id="detalleSede"></span></span>
                          <span>Modalidad de pago: <span class="badge badge-primary" id="detalleModalidad"></span></span>
                          <span>Monto: <strong class="text-primary">Bs. <span id="detalleMonto">0.00</span></strong></span>
                        </div>
                      </div>

                      <hr class="my-2">

                      <!-- Formulario de validación del voucher -->
                      <form id="formValidarVoucher" enctype="multipart/form-data">

                        <!-- PLAN DE PAGO DEL PROGRAMA (posgrado, aparte de la matrícula) -->
                        <div class="plan-pago-programa mb-2">
                          <div class="step-title" style="font-size:13px;">
                            <i class="fa fa-money"></i> Plan de Pago del Programa
                          </div>
                          <small class="text-muted d-block mb-2">
                            Defina cómo se cancelará el costo del programa (posgrado), aparte de la matrícula.
                            Costo total del programa: <strong class="text-primary">Bs. <span id="planCostoTotal">0.00</span></strong>
                          </small>

                          <div class="row">
                            <div class="col-lg-4 form-group mb-2">
                              <label class="small font-weight-bold mb-1">Tipo de Plan</label>
                              <select class="form-control form-control-sm" id="planTipoPago">
                                <option value="REGULAR">Plan Regular (cuotas)</option>
                                <option value="DESCUENTO">Plan al Contado (con descuento)</option>
                              </select>
                            </div>
                            <div class="col-lg-4 form-group mb-2" id="grupoNumeroCuotas">
                              <label class="small font-weight-bold mb-1">N° de Cuotas</label>
                              <input type="number" class="form-control form-control-sm" id="planNumeroCuotas" min="1" max="36" value="1">
                            </div>
                            <div class="col-lg-4 form-group mb-2">
                              <label class="small font-weight-bold mb-1">% Descuento</label>
                              <input type="number" class="form-control form-control-sm" id="planPorcentajeDescuento" min="0" max="100" step="0.01" value="0">
                            </div>
                          </div>

                          <small class="text-muted d-block mb-2">
                            <i class="fa fa-users"></i> ¿Es un <strong>Plan Grupal</strong> (varios inscritos)? Ese plan se define
                            desde <strong>Matriculados</strong> una vez que los integrantes del grupo estén matriculados,
                            para poder seleccionarlos a todos juntos.
                          </small>

                          <div class="text-right mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary" id="btnGenerarCuotas">
                              <i class="fa fa-list-ol"></i> Redistribuir Cuotas
                            </button>
                          </div>

                          <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-1" id="tablaCuotasPlan">
                              <thead class="thead-light">
                                <tr>
                                  <th class="text-center" style="width:40px;">N°</th>
                                  <th>Monto (Bs.)</th>
                                </tr>
                              </thead>
                              <tbody id="tablaCuotasBody"></tbody>
                            </table>
                          </div>
                          <small class="d-block text-right mb-2">
                            Total cuotas: <strong>Bs. <span id="planTotalCuotas">0.00</span></strong>
                            <span id="planCuotasEstadoSuma" class="ml-1"></span>
                          </small>

                          <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="planAceptaCancelacion">
                            <label class="custom-control-label small" for="planAceptaCancelacion">
                              Se acepta cancelar cada cuota <strong>antes de que inicie el módulo correspondiente</strong>,
                              sin fijar una fecha exacta de vencimiento.
                            </label>
                          </div>
                        </div>

                        <hr class="my-2">

                        <div class="row">
                          <div class="col-lg-4 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Código de Voucher <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="voucherNumero" maxlength="25" required>
                          </div>
                          <div class="col-lg-4 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Fecha de Inscripción <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="voucherFecha" required
                                   max="<?php echo date('Y-m-d'); ?>">
                          </div>
                          <div class="col-lg-4 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Foto del Voucher</label>
                            <div class="custom-file custom-file-sm">
                              <input type="file" class="custom-file-input" id="voucherFoto" accept="image/*">
                              <label class="custom-file-label" for="voucherFoto" id="voucherFotoLabel">Seleccionar imagen...</label>
                            </div>
                            <small class="form-text text-muted">JPG, PNG, GIF o WEBP (máx. 5MB)</small>
                          </div>
                        </div>

                        <div id="voucherFotoPreview" class="mb-2" style="display:none;">
                          <img id="voucherFotoPreviewImg" src="" class="img-thumbnail" style="max-width: 140px;">
                        </div>

                        <small class="text-warning d-block mb-2">
                          <i class="fa fa-exclamation-triangle"></i>
                          Verifique que la matrícula ya fue cancelada en caja antes de validar. Esta acción
                          <strong>inscribe formalmente</strong> al estudiante en el programa y no se puede deshacer desde aquí.
                        </small>

                        <div class="text-right">
                          <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-check-circle"></i> Validar Inscripción
                          </button>
                        </div>
                      </form>

                    </div>
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

  <!-- Modal: Resultados de la búsqueda -->
  <div class="modal fade" id="ModalResultadosBusqueda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title"><i class="flaticon2-search"></i> Resultados de la búsqueda</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="resultadosBusquedaBody" style="padding: 1.5rem; max-height: 60vh; overflow-y: auto;">
          <!-- Se llena por JS -->
        </div>
        <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="vistas/recursos/sweetalert.min.js"></script>

  <script>
  function iniciarInscripcion() {

      let costoTotalProgramaActual = 0;

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

                  // Reiniciar y precargar el plan de pago del programa con el costo del programa elegido
                  costoTotalProgramaActual = parseFloat(p.CostoPrograma) || 0;
                  $('#planCostoTotal').text(costoTotalProgramaActual.toFixed(2));
                  $('#planTipoPago').val('REGULAR');
                  $('#planNumeroCuotas').val(1);
                  $('#planPorcentajeDescuento').val(0);
                  $('#grupoNumeroCuotas').show();
                  generarCuotasPlan();

                  $('#ModalResultadosBusqueda').modal('hide');
                  $('#panelDetalle').slideDown();
                  $('html, body').animate({ scrollTop: $('#panelDetalle').offset().top - 100 }, 400);
              },
              error: function() {
                  swal('Error', 'No se pudo cargar el preregistro', 'error');
              }
          });
      });

      // Volver a la búsqueda
      $('#btnBuscarOtro').on('click', function() {
          $('#panelDetalle').slideUp();
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
      // Se define ANTES de validar el voucher; las cuotas se registran junto
      // con la inscripción formal (misma transacción en el backend).
      // ============================================
      function generarCuotasPlan() {
          const tipoPlan = $('#planTipoPago').val();
          let porcentaje = parseFloat($('#planPorcentajeDescuento').val()) || 0;
          if (porcentaje < 0) porcentaje = 0;
          if (porcentaje > 100) porcentaje = 100;

          const montoTotalPagar = Math.round((costoTotalProgramaActual * (1 - porcentaje / 100)) * 100) / 100;
          const numeroCuotas = (tipoPlan === 'REGULAR') ? (parseInt($('#planNumeroCuotas').val(), 10) || 1) : 1;

          let html = '';
          let acumulado = 0;
          for (let i = 1; i <= numeroCuotas; i++) {
              let monto;
              if (i < numeroCuotas) {
                  monto = Math.round((montoTotalPagar / numeroCuotas) * 100) / 100;
                  acumulado += monto;
              } else {
                  monto = Math.round((montoTotalPagar - acumulado) * 100) / 100;
              }

              html += '<tr>' +
                  '<td class="text-center">' + i + '</td>' +
                  '<td><input type="number" class="form-control form-control-sm cuota-monto" step="0.01" min="0" value="' + monto.toFixed(2) + '"></td>' +
                  '</tr>';
          }

          $('#tablaCuotasBody').html(html);
          recalcularSumaCuotas();
      }

      function recalcularSumaCuotas() {
          let suma = 0;
          $('.cuota-monto').each(function() {
              suma += parseFloat($(this).val()) || 0;
          });

          let porcentaje = parseFloat($('#planPorcentajeDescuento').val()) || 0;
          const montoTotalPagar = Math.round((costoTotalProgramaActual * (1 - porcentaje / 100)) * 100) / 100;

          $('#planTotalCuotas').text(suma.toFixed(2));

          if (Math.abs(suma - montoTotalPagar) <= 0.5) {
              $('#planCuotasEstadoSuma').html('<span class="text-success"><i class="fa fa-check-circle"></i> Coincide con el monto a pagar (Bs. ' + montoTotalPagar.toFixed(2) + ')</span>');
          } else {
              $('#planCuotasEstadoSuma').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> Debe sumar Bs. ' + montoTotalPagar.toFixed(2) + '</span>');
          }
      }

      $('#planTipoPago').on('change', function() {
          const tipoPlan = $(this).val();
          $('#grupoNumeroCuotas').toggle(tipoPlan === 'REGULAR');
          if (tipoPlan !== 'REGULAR') {
              $('#planNumeroCuotas').val(1);
          }
          generarCuotasPlan();
      });

      $('#planNumeroCuotas, #planPorcentajeDescuento').on('change', generarCuotasPlan);
      $('#btnGenerarCuotas').on('click', generarCuotasPlan);
      $(document).on('input', '.cuota-monto', recalcularSumaCuotas);

      function obtenerCuotasPlanValidas() {
          if (!$('#planAceptaCancelacion').is(':checked')) {
              return null;
          }

          const cuotas = [];
          let valido = true;

          $('#tablaCuotasBody tr').each(function() {
              const monto = parseFloat($(this).find('.cuota-monto').val()) || 0;
              if (monto <= 0) {
                  valido = false;
              }
              cuotas.push({ monto: monto, fecha: null });
          });

          if (!valido || cuotas.length === 0) {
              return null;
          }

          let porcentaje = parseFloat($('#planPorcentajeDescuento').val()) || 0;
          const montoTotalPagar = Math.round((costoTotalProgramaActual * (1 - porcentaje / 100)) * 100) / 100;
          const suma = cuotas.reduce((acc, c) => acc + c.monto, 0);

          if (Math.abs(suma - montoTotalPagar) > 0.5) {
              return null;
          }

          return cuotas;
      }

      // ============================================
      // VALIDAR EL VOUCHER (inscribe formalmente al estudiante)
      // ============================================
      $('#formValidarVoucher').on('submit', function(e) {
          e.preventDefault();

          const idOrdenPago = $('#detalleIdOrdenPago').val();
          const numeroVoucher = $('#voucherNumero').val().trim();
          const fechaInscripcion = $('#voucherFecha').val();

          if (!numeroVoucher) {
              swal('Atención', 'Ingrese el código de voucher', 'warning');
              return;
          }
          if (!fechaInscripcion) {
              swal('Atención', 'Seleccione la fecha de inscripción', 'warning');
              return;
          }

          if (!$('#planAceptaCancelacion').is(':checked')) {
              swal('Atención', 'Marque la casilla para aceptar cancelar cada cuota antes de que inicie su módulo correspondiente', 'warning');
              return;
          }

          const cuotasPlan = obtenerCuotasPlanValidas();
          if (!cuotasPlan) {
              swal('Atención', 'Revise el Plan de Pago del Programa: las cuotas deben tener un monto válido y su suma debe coincidir con el monto a pagar', 'warning');
              return;
          }

          const formData = new FormData();
          formData.append('accion', 'validarVoucherMatricula');
          formData.append('idOrdenPago', idOrdenPago);
          formData.append('numeroVoucher', numeroVoucher);
          formData.append('fechaInscripcion', fechaInscripcion);
          formData.append('planPago', JSON.stringify({
              TipoPlan: $('#planTipoPago').val(),
              CostoTotalPrograma: costoTotalProgramaActual,
              PorcentajeDescuento: parseFloat($('#planPorcentajeDescuento').val()) || 0,
              Cuotas: cuotasPlan
          }));

          const archivoFoto = $('#voucherFoto')[0].files[0];
          if (archivoFoto) {
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
                      swal({
                          title: '¡Voucher validado!',
                          text: 'El estudiante quedó inscrito formalmente en el programa.',
                          icon: 'success',
                          buttons: false,
                          timer: 2200
                      }).then(function() {
                          $('#panelDetalle').slideUp();
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border-radius: 6px;
}

.chip-summary {
    padding: 8px 12px;
    background: #f4f6fb;
    border-left: 3px solid #667eea;
    border-radius: 6px;
}

.chip-summary-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #444;
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

.card, .kt-portlet {
    transition: box-shadow 0.3s ease;
}

.plan-pago-programa {
    padding: 10px 12px;
    background: #fbfbfd;
    border: 1px solid #e1e3ea;
    border-radius: 6px;
}

.plan-pago-programa .step-title {
    font-size: 13px;
    font-weight: 700;
    color: #3b3f5c;
    margin-bottom: 4px;
}

#tablaCuotasPlan th, #tablaCuotasPlan td {
    padding: 4px 6px;
    vertical-align: middle;
}
</style>

</body>
</html>
