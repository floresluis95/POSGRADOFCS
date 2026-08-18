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
                <div class="col-lg-10">

                  <!-- BUSCAR ESTUDIANTE CON PREREGISTRO PENDIENTE -->
                  <div class="kt-portlet" style="box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 10px;">
                    <div class="kt-portlet__body" style="padding: 2rem;">
                      <div class="row align-items-end">
                        <div class="col-lg-8">
                          <label class="font-weight-bold mb-2" style="color: #3f4254;">
                            <i class="flaticon2-search text-primary"></i> Buscar estudiante con pre-registro pendiente
                          </label>
                          <div class="input-group input-group-lg">
                            <input type="text" id="buscarTermino" class="form-control"
                                   placeholder="C.I., nombre o apellido..." maxlength="60"
                                   style="border: 2px solid #e1e3ea; border-radius: 6px 0 0 6px;">
                            <div class="input-group-append">
                              <button type="button" id="btnBuscarPreregistro" class="btn btn-primary"
                                      style="border-radius: 0 6px 6px 0;">
                                <i class="flaticon2-search"></i> Buscar
                              </button>
                            </div>
                          </div>
                          <small class="form-text text-muted">
                            <i class="flaticon2-information"></i> Solo se muestran estudiantes con una orden de pago de matrícula pendiente (generada en Pre-Registro).
                          </small>
                        </div>
                        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                          <a href="preregistro" class="btn btn-outline-primary btn-lg btn-block"
                             style="border-radius: 6px;">
                            <i class="flaticon2-plus"></i> Nuevo Estudiante
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- DETALLE DEL PREREGISTRO SELECCIONADO / VALIDACIÓN DEL VOUCHER -->
                  <div class="kt-portlet mt-4" id="panelDetalle" style="display:none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 10px;">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white;">
                          <i class="fa fa-check-circle"></i> Validar Voucher de Matrícula
                        </h3>
                      </div>
                      <div class="kt-portlet__head-toolbar">
                        <button type="button" id="btnBuscarOtro" class="btn btn-sm btn-light">
                          <i class="flaticon2-back"></i> Buscar otro estudiante
                        </button>
                      </div>
                    </div>
                    <div class="kt-portlet__body" style="padding: 2rem;">

                      <input type="hidden" id="detalleIdOrdenPago">

                      <!-- Datos del estudiante -->
                      <div class="chip-summary mb-3">
                        <div class="chip-summary-row">
                          <span><i class="flaticon2-user-outline-symbol"></i> <strong id="detalleNombre"></strong></span>
                          <span>CI: <span id="detalleCi"></span></span>
                          <span id="detalleCorreo"></span>
                          <span id="detalleCelular"></span>
                        </div>
                      </div>

                      <!-- Datos del programa -->
                      <div class="chip-summary mb-3">
                        <div class="chip-summary-row flex-wrap">
                          <span><strong id="detallePrograma"></strong> (<span id="detalleCodigo"></span>)</span>
                          <span>Grado: <span id="detalleGrado"></span></span>
                          <span>Sede: <span id="detalleSede"></span></span>
                          <span>Modalidad de pago: <span class="badge badge-primary" id="detalleModalidad"></span></span>
                        </div>
                      </div>

                      <!-- Monto a cancelar -->
                      <div class="text-center mb-4">
                        <div class="d-inline-block" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                          <div style="font-size: 0.9rem; color: rgba(255,255,255,0.85);">MONTO DE MATRÍCULA A CANCELAR</div>
                          <div style="font-size: 2rem; font-weight: 700; color: white;">Bs. <span id="detalleMonto">0.00</span></div>
                        </div>
                      </div>

                      <hr>

                      <!-- Formulario de validación del voucher -->
                      <form id="formValidarVoucher" enctype="multipart/form-data">
                        <div class="row">
                          <div class="col-lg-4 mb-3">
                            <label class="font-weight-bold mb-2"><i class="flaticon2-file-1"></i> Código de Voucher <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="voucherNumero" maxlength="25" required
                                   style="border: 2px solid #e1e3ea; border-radius: 6px;">
                          </div>
                          <div class="col-lg-4 mb-3">
                            <label class="font-weight-bold mb-2"><i class="flaticon2-calendar-8"></i> Fecha de Inscripción <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-lg" id="voucherFecha" required
                                   max="<?php echo date('Y-m-d'); ?>"
                                   style="border: 2px solid #e1e3ea; border-radius: 6px;">
                          </div>
                          <div class="col-lg-4 mb-3">
                            <label class="font-weight-bold mb-2"><i class="flaticon2-image-file"></i> Foto del Voucher</label>
                            <div class="custom-file">
                              <input type="file" class="custom-file-input" id="voucherFoto" accept="image/*">
                              <label class="custom-file-label" for="voucherFoto" id="voucherFotoLabel">Seleccionar imagen...</label>
                            </div>
                            <small class="form-text text-muted">JPG, PNG, GIF o WEBP (máx. 5MB)</small>
                          </div>
                        </div>

                        <div id="voucherFotoPreview" class="mb-3" style="display:none;">
                          <img id="voucherFotoPreviewImg" src="" class="img-thumbnail" style="max-width: 220px;">
                        </div>

                        <div class="alert alert-warning">
                          <i class="fa fa-exclamation-triangle"></i>
                          Verifique que la matrícula ya fue cancelada en caja antes de validar. Esta acción
                          <strong>inscribe formalmente</strong> al estudiante en el programa y no se puede deshacer desde aquí.
                        </div>

                        <div class="text-center">
                          <button type="submit" class="btn btn-success btn-lg" style="padding: 0.9rem 3rem; border-radius: 8px; font-weight: 600;">
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

          const formData = new FormData();
          formData.append('accion', 'validarVoucherMatricula');
          formData.append('idOrdenPago', idOrdenPago);
          formData.append('numeroVoucher', numeroVoucher);
          formData.append('fechaInscripcion', fechaInscripcion);

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
.chip-summary {
    padding: 10px 14px;
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

.card, .kt-portlet {
    transition: box-shadow 0.3s ease;
}
</style>

</body>
</html>
