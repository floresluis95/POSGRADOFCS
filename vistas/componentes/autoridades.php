<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
<!-- Estilos compartidos de las pantallas de gestión (encabezado de tarjeta, tabla y modales). Sin ellos el título blanco queda sobre fondo blanco. -->
<link rel="stylesheet" href="vistas/recursos/assets/css/gestion-registros.css?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/css/gestion-registros.css'); ?>">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="panel">
         <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
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
        <!-- Estructura del tema (kt-body / kt-content con el pie adentro): sin ella
             el alto de la página se calcula mal y no deja desplazarse hasta abajo. -->
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>AUTORIDADES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>FIRMAS DE DOCUMENTOS</h3>
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

<div class="kt-container kt-grid__item kt-grid__item--fluid">
      <section>
        <div class="card shadow-sm">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-user-tie"></i> Director y Coordinador de Posgrado
            </h4>
          </div>
          <div class="card-body">
            <p class="text-muted mb-4">
              Estos nombres se imprimen en el <strong>Documento Privado de Compromiso</strong>.
              Cuando cambie una autoridad, actualícela aquí; los documentos que se impriman
              desde ese momento saldrán con el nuevo nombre.
            </p>

            <form id="formAutoridades" autocomplete="off">
              <div class="row">
                <?php foreach (['DIRECTOR' => 'Director de Posgrado', 'COORDINADOR' => 'Coordinador de Posgrado'] as $cargo => $titulo) { ?>
                <div class="col-md-6 mb-3">
                  <div class="border rounded p-3 h-100">
                    <h5 class="mb-3" style="color:#1e3c72;"><?php echo $titulo; ?></h5>
                    <div class="form-group">
                      <label for="titulo<?php echo $cargo; ?>">Título <small class="text-muted">(ej.: MSc. Ing.)</small></label>
                      <input type="text" class="form-control" id="titulo<?php echo $cargo; ?>"
                             name="autoridades[<?php echo $cargo; ?>][titulo]" maxlength="50">
                    </div>
                    <div class="form-group">
                      <label for="nombre<?php echo $cargo; ?>">Nombre completo <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="nombre<?php echo $cargo; ?>"
                             name="autoridades[<?php echo $cargo; ?>][nombre]" maxlength="150" required>
                    </div>
                    <div class="small text-muted">
                      Así se imprime: <strong id="vista<?php echo $cargo; ?>">—</strong><br>
                      <span id="actualizado<?php echo $cargo; ?>"></span>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
              <div class="text-right">
                <button type="submit" class="btn btn-success" id="btnGuardarAutoridades">
                  <i class="fas fa-save"></i> Guardar cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
// jQuery y SweetAlert se cargan al final de la plantilla: esperar a que estén.
document.addEventListener('DOMContentLoaded', function () {
    var cargos = ['DIRECTOR', 'COORDINADOR'];

    // Vista previa "Título Nombre", igual que en el documento
    function actualizarVista(cargo) {
        var texto = ($('#titulo' + cargo).val().trim() + ' ' + $('#nombre' + cargo).val().trim()).trim();
        $('#vista' + cargo).text(texto || '—');
    }

    function cargar() {
        $.post('ajax/autoridad.ajax.php', { accion: 'listar' }, function (resp) {
            if (!resp || !resp.success) return;
            cargos.forEach(function (cargo) {
                var a = resp.autoridades[cargo] || {};
                $('#titulo' + cargo).val(a.Titulo || '');
                $('#nombre' + cargo).val(a.Nombre || '');
                $('#actualizado' + cargo).text(a.FechaActualizacion
                    ? 'Actualizado: ' + a.FechaActualizacion + (a.ResponsableActualizacion ? ' por ' + a.ResponsableActualizacion : '')
                    : '');
                actualizarVista(cargo);
            });
        }, 'json');
    }

    cargos.forEach(function (cargo) {
        $('#titulo' + cargo + ', #nombre' + cargo).on('input', function () { actualizarVista(cargo); });
    });

    $('#formAutoridades').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnGuardarAutoridades').prop('disabled', true);

        $.post('ajax/autoridad.ajax.php', $(this).serialize() + '&accion=guardar', function (resp) {
            if (resp && resp.success) {
                Swal.fire({ type: 'success', title: resp.mensaje, timer: 1800, showConfirmButton: false });
                cargar();
            } else {
                Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo guardar', 'warning');
            }
        }, 'json').fail(function () {
            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    cargar();
});
</script>

</body>
