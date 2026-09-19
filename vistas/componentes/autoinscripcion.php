<?php
/**
 * Formulario suelto de "Inscribirme" (Solicitud de Inscripción).
 * Independiente del flujo formal de Pre-Registro/Inscripción: solo registra
 * el interés del estudiante en la tabla `solicitudinscripcion` para que
 * ADM/SEC lo revise y contacte desde "Solicitudes de Inscripción". No crea
 * estudiantes, órdenes de pago ni matrículas, y no afecta esa secuencia.
 */
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

require_once 'modelos/programa.modelo.php';
$programasActivos = ProgramasModelos::ListaProgramaModelo();

$programaIDPreseleccionado = isset($_GET['programaID']) ? (int) $_GET['programaID'] : 0;
$programaNombrePreseleccionado = isset($_GET['programaNombre']) ? trim($_GET['programaNombre']) : '';
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
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

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2><i class="fa fa-file-signature"></i> Solicitud de Inscripción</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>QUIERO INSCRIBIRME</h3>
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

            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="row justify-content-md-center">
                <div class="col-lg-8">

                  <div class="alert alert-light border mb-4" style="font-size: 13px;">
                    <i class="fa fa-info-circle text-primary"></i>
                    Complete este formulario para manifestar su interés en un programa de posgrado.
                    La Secretaría de Posgrado revisará su solicitud y se comunicará con usted para
                    continuar con el trámite formal de inscripción. Este formulario <strong>no</strong>
                    genera ninguna orden de pago ni matrícula.
                  </div>

                  <div class="kt-portlet autoinscripcion-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title text-white">
                          <i class="fa fa-user-plus"></i> Datos del interesado
                        </h3>
                      </div>
                    </div>
                    <div class="kt-portlet__body py-4">
                      <form id="formAutoinscripcion">
                        <div class="row">
                          <div class="col-md-4 form-group">
                            <label class="filtro-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="autoNombre" maxlength="50" required>
                          </div>
                          <div class="col-md-4 form-group">
                            <label class="filtro-label">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="autoApaterno" maxlength="50" required>
                          </div>
                          <div class="col-md-4 form-group">
                            <label class="filtro-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="autoAmaterno" maxlength="50">
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3 form-group">
                            <label class="filtro-label">C.I. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="autoCi" maxlength="15" required>
                          </div>
                          <div class="col-md-3 form-group">
                            <label class="filtro-label">Complemento</label>
                            <input type="text" class="form-control" id="autoComplemento" maxlength="5">
                          </div>
                          <div class="col-md-3 form-group">
                            <label class="filtro-label">Celular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="autoCelular" maxlength="15" required>
                          </div>
                          <div class="col-md-3 form-group">
                            <label class="filtro-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="autoCorreo" maxlength="100">
                          </div>
                        </div>
                        <div class="form-group">
                          <label class="filtro-label">Programa de interés</label>
                          <select class="form-control select2-uniforme" id="autoPrograma">
                            <option value="">Aún no lo tengo definido</option>
                            <?php foreach ($programasActivos as $prog): ?>
                              <option value="<?php echo (int) $prog['ProgramaID']; ?>"
                                <?php echo ($programaIDPreseleccionado === (int) $prog['ProgramaID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prog['GradoAcademico'] . ' - ' . $prog['NombrePrograma'] . ' (' . $prog['Sede'] . ')'); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-group mb-0">
                          <label class="filtro-label">Mensaje (opcional)</label>
                          <textarea class="form-control" id="autoMensaje" rows="3" maxlength="500"
                                    placeholder="Alguna consulta o dato adicional que quiera dejarnos..."></textarea>
                        </div>
                      </form>
                    </div>
                    <div class="kt-portlet__foot text-right">
                      <button type="button" class="btn btn-primary" id="btnEnviarAutoinscripcion">
                        <i class="fa fa-paper-plane"></i> Enviar Solicitud
                      </button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer -> FooterResumidoControlador();
        ?>

      </div>
    </div>
  </div>
</div>

<style>
.autoinscripcion-portlet {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
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
</style>

<script>
$(document).ready(function() {
    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    $('.select2-uniforme').select2({ width: '100%' });

    <?php if ($programaIDPreseleccionado === 0 && $programaNombrePreseleccionado !== ''): ?>
    // Vino del carrusel del panel sin ProgramaID (solo el nombre): se deja
    // constancia del programa de interés como texto libre en el mensaje.
    $('#autoMensaje').val('Interesado en: <?php echo addslashes($programaNombrePreseleccionado); ?>');
    <?php endif; ?>

    $('#btnEnviarAutoinscripcion').on('click', function() {
        const nombre = $('#autoNombre').val().trim();
        const apaterno = $('#autoApaterno').val().trim();
        const amaterno = $('#autoAmaterno').val().trim();
        const ci = $('#autoCi').val().trim();
        const complemento = $('#autoComplemento').val().trim();
        const celular = $('#autoCelular').val().trim();
        const correo = $('#autoCorreo').val().trim();
        const programaID = $('#autoPrograma').val();
        const programaNombre = $('#autoPrograma option:selected').text().trim();
        const mensaje = $('#autoMensaje').val().trim();

        if (!nombre || !apaterno || !ci || !celular) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Complete los campos obligatorios: nombres, apellido paterno, C.I. y celular' });
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: 'ajax/solicitudinscripcion.ajax.php',
            type: 'POST',
            data: {
                accion: 'crear',
                nombre: nombre,
                apaterno: apaterno,
                amaterno: amaterno,
                ci: ci,
                complemento: complemento,
                celular: celular,
                correo: correo,
                programaID: programaID,
                programaNombre: programaID ? programaNombre : '',
                mensaje: mensaje
            },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar Solicitud');

                if (!resp.success) {
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo registrar la solicitud' });
                    return;
                }

                Swal.fire({ icon: 'success', title: '¡Solicitud enviada!', text: resp.mensaje }).then(function() {
                    window.location.href = 'panel';
                });
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar Solicitud');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo enviar la solicitud' });
            }
        });
    });
});
</script>
</body>
