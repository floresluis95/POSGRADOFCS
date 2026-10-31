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
         <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
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
                  <h2>CATÁLOGO DE REQUISITOS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>REQUISITOS DE MATRICULACIÓN</h3>
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
          <div class="card-header bg-gradient-primary d-flex justify-content-between align-items-center">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-clipboard-check"></i> Requisitos que se piden al matricularse
            </h4>
            <button type="button" class="btn btn-success btn-sm" id="btnNuevoRequisito">
              <i class="fas fa-plus mr-1"></i> Nuevo requisito
            </button>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">
              <strong>Obligatorio:</strong> cuenta para el avance "N/M" de Matriculados.
              <strong>Automático:</strong> se marca como entregado al validar el voucher de matrícula
              (documentos que genera el sistema). Un requisito <strong>inactivo</strong> deja de pedirse,
              pero se conservan las entregas ya registradas.
              <strong>Se pide en:</strong> los grados académicos a los que aplica (p. ej. requisitos extra de
              Doctorado); si no se elige ninguno, se pide en todos.
            </p>
            <div class="form-inline mb-3">
              <label for="filtroGradoRequisito" class="mr-2">Ver requisitos de:</label>
              <select id="filtroGradoRequisito" class="form-control form-control-sm" style="min-width:200px;">
                <option value="">Todos los grados</option>
                <option value="DIPLOMADO">Diplomado</option>
                <option value="ESPECIALIDAD">Especialidad</option>
                <option value="MAESTRIA">Maestría</option>
                <option value="DOCTORADO">Doctorado</option>
              </select>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="thead-dark">
                  <tr>
                    <th class="text-center" style="width:8%;">Orden</th>
                    <th>Requisito</th>
                    <th class="text-center" style="width:16%;">Se pide en</th>
                    <th class="text-center" style="width:11%;">Obligatorio</th>
                    <th class="text-center" style="width:11%;">Automático</th>
                    <th class="text-center" style="width:11%;">Entregados</th>
                    <th class="text-center" style="width:10%;">Estado</th>
                    <th class="text-center" style="width:12%;">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaCatalogoRequisitos">
                  <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
                </tbody>
              </table>
            </div>
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

<!-- Modal: nuevo / editar requisito -->
<div class="modal fade" id="modalRequisito" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:1500;">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="formRequisito" autocomplete="off">
        <div class="modal-header bg-gradient-primary">
          <h5 class="modal-title text-white" id="tituloModalRequisito">Nuevo requisito</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idRequisito" id="reqId" value="0">
          <div class="form-group">
            <label for="reqNombre">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="reqNombre" maxlength="150" required
                   placeholder="Ej.: Certificado de salud">
          </div>
          <div class="form-group">
            <label for="reqOrden">Orden en la lista</label>
            <input type="number" class="form-control" name="orden" id="reqOrden" min="0" max="999"
                   placeholder="Vacío = al final">
          </div>
          <div class="form-group">
            <label class="d-block">Se pide en <small class="text-muted">(ninguno marcado = todos los grados)</small></label>
            <?php foreach (['DIPLOMADO' => 'Diplomado', 'ESPECIALIDAD' => 'Especialidad', 'MAESTRIA' => 'Maestría', 'DOCTORADO' => 'Doctorado'] as $grado => $etiqueta) { ?>
            <div class="custom-control custom-checkbox custom-control-inline">
              <input type="checkbox" class="custom-control-input chk-grado" id="reqGrado<?php echo $grado; ?>" name="grados[]" value="<?php echo $grado; ?>">
              <label class="custom-control-label" for="reqGrado<?php echo $grado; ?>"><?php echo $etiqueta; ?></label>
            </div>
            <?php } ?>
          </div>
          <div class="custom-control custom-checkbox mb-2">
            <input type="checkbox" class="custom-control-input" id="reqObligatorio" checked>
            <label class="custom-control-label" for="reqObligatorio">Obligatorio</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="reqAutomatico">
            <label class="custom-control-label" for="reqAutomatico">Automático (se marca al validar el voucher de matrícula)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success" id="btnGuardarRequisito"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// jQuery y SweetAlert se cargan al final de la plantilla: esperar a que estén.
document.addEventListener('DOMContentLoaded', function () {
    var catalogo = [];
    var nombresGrado = { DIPLOMADO: 'Diplomado', ESPECIALIDAD: 'Especialidad', MAESTRIA: 'Maestría', DOCTORADO: 'Doctorado' };

    function gradosDe(r) {
        return r.Grados ? r.Grados.split(',') : [];
    }

    function textoGrados(r) {
        var grados = gradosDe(r);
        if (!grados.length) return '<span class="text-muted">Todos</span>';
        return grados.map(function (g) {
            return '<span class="badge badge-info mr-1">' + esc(nombresGrado[g] || g) + '</span>';
        }).join('');
    }

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function marca(valor) {
        return parseInt(valor) === 1
            ? '<i class="fas fa-check text-success"></i>'
            : '<span class="text-muted">—</span>';
    }

    // Dibuja la tabla; con un grado elegido en el filtro muestra solo lo que se
    // pide en ese grado (los de "Todos" más los propios del grado).
    function dibujar() {
        var filtro = $('#filtroGradoRequisito').val();
        var html = '';
        catalogo.forEach(function (r, i) {
            var grados = gradosDe(r);
            if (filtro && grados.length && grados.indexOf(filtro) === -1) return;
            var activo = parseInt(r.Estado) === 1;
            html += '<tr' + (activo ? '' : ' class="text-muted" style="background:#f8f9fa;"') + '>' +
                '<td class="text-center">' + parseInt(r.Orden) + '</td>' +
                '<td>' + esc(r.Nombre) + '</td>' +
                '<td class="text-center">' + textoGrados(r) + '</td>' +
                '<td class="text-center">' + marca(r.Obligatorio) + '</td>' +
                '<td class="text-center">' + marca(r.Automatico) + '</td>' +
                '<td class="text-center">' + parseInt(r.Entregados) + '</td>' +
                '<td class="text-center">' + (activo
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-secondary">Inactivo</span>') + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-warning btn-editar-req" data-i="' + i + '" title="Editar"><i class="fas fa-edit"></i></button> ' +
                    '<button type="button" class="btn btn-sm ' + (activo ? 'btn-danger' : 'btn-success') + ' btn-estado-req" data-i="' + i + '" title="' + (activo ? 'Desactivar' : 'Activar') + '">' +
                        '<i class="fas ' + (activo ? 'fa-ban' : 'fa-check') + '"></i></button>' +
                '</td></tr>';
        });
        $('#tablaCatalogoRequisitos').html(html || '<tr><td colspan="8" class="text-center text-muted">No hay requisitos para este grado</td></tr>');
    }

    function cargar() {
        $.post('ajax/requisito.ajax.php', { accion: 'listarCatalogo' }, function (resp) {
            if (!resp || !resp.success) return;
            catalogo = resp.requisitos;
            dibujar();
        }, 'json');
    }

    $('#filtroGradoRequisito').on('change', dibujar);

    function abrirModal(r) {
        $('#tituloModalRequisito').text(r ? 'Editar requisito' : 'Nuevo requisito');
        $('#reqId').val(r ? r.IdRequisito : 0);
        $('#reqNombre').val(r ? r.Nombre : '');
        $('#reqOrden').val(r ? r.Orden : '');
        $('#reqObligatorio').prop('checked', r ? parseInt(r.Obligatorio) === 1 : true);
        $('#reqAutomatico').prop('checked', r ? parseInt(r.Automatico) === 1 : false);
        // Nuevo requisito: si se está filtrando por un grado, se propone ese grado
        var grados = r ? gradosDe(r) : ($('#filtroGradoRequisito').val() ? [$('#filtroGradoRequisito').val()] : []);
        $('.chk-grado').each(function () { this.checked = grados.indexOf(this.value) !== -1; });
        $('#modalRequisito').modal('show');
    }

    $('#btnNuevoRequisito').on('click', function () { abrirModal(null); });
    $('#tablaCatalogoRequisitos').on('click', '.btn-editar-req', function () {
        abrirModal(catalogo[$(this).data('i')]);
    });

    $('#tablaCatalogoRequisitos').on('click', '.btn-estado-req', function () {
        var r = catalogo[$(this).data('i')];
        var activar = parseInt(r.Estado) !== 1;
        Swal.fire({
            title: (activar ? '¿Activar' : '¿Desactivar') + ' "' + r.Nombre + '"?',
            text: activar
                ? 'Volverá a pedirse a los matriculados.'
                : 'Dejará de pedirse. Las entregas ya registradas se conservan.',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: activar ? 'Sí, activar' : 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.value) return;
            $.post('ajax/requisito.ajax.php', { accion: 'estadoCatalogo', idRequisito: r.IdRequisito, estado: activar ? '1' : '0' }, function (resp) {
                Swal.fire(resp.success ? 'Listo' : 'Atención', resp.mensaje, resp.success ? 'success' : 'warning');
                cargar();
            }, 'json');
        });
    });

    $('#formRequisito').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnGuardarRequisito').prop('disabled', true);
        var datos = $(this).serialize() + '&accion=guardarCatalogo' +
            '&obligatorio=' + ($('#reqObligatorio').is(':checked') ? '1' : '0') +
            '&automatico=' + ($('#reqAutomatico').is(':checked') ? '1' : '0');

        $.post('ajax/requisito.ajax.php', datos, function (resp) {
            if (resp && resp.success) {
                $('#modalRequisito').modal('hide');
                Swal.fire({ type: 'success', title: resp.mensaje, timer: 1600, showConfirmButton: false });
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
