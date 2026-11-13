<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
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
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>UNIVERSIDADES Y PROFESIONES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>CORREGIR CATÁLOGOS</h3>
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
      <section>
        <div class="card shadow-sm">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-university"></i> Universidades y profesiones de los estudiantes
            </h4>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">
              Se agregan con el botón <strong>"+"</strong> del formulario del estudiante. Aquí se corrigen los errores de tipeo.
              <strong>Corregir:</strong> cambia el nombre en todos los estudiantes que la usan.
              <strong>Unir con…:</strong> si la misma quedó registrada dos veces (una mal escrita), pasa sus estudiantes
              a la correcta y quita la mal escrita.
              <strong>Eliminar:</strong> solo si ningún estudiante la usa.
            </p>
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" href="#" data-catalogo="universidad" id="tabUniversidades"><i class="fas fa-university mr-1"></i> Universidades</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" data-catalogo="profesion" id="tabProfesiones"><i class="fas fa-user-graduate mr-1"></i> Profesiones</a>
              </li>
            </ul>
            <div class="form-inline mb-3">
              <input type="search" id="buscarCatalogo" class="form-control form-control-sm" style="min-width:280px;" placeholder="Buscar por nombre...">
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-bordered align-middle mb-0">
                <thead class="thead-dark">
                  <tr>
                    <th>Nombre</th>
                    <th class="text-center col-universidad" style="width:13%;">Tipo</th>
                    <th class="text-center" style="width:12%;">Estudiantes</th>
                    <th class="text-center col-universidad" style="width:10%;">Estado</th>
                    <th class="text-center" style="width:20%;">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaCatalogo">
                  <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
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

<!-- Modal: corregir -->
<div class="modal fade" id="modalCorregirCatalogo" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:1500;">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="formCorregirCatalogo" autocomplete="off">
        <div class="modal-header bg-gradient-primary">
          <h5 class="modal-title text-white" id="tituloCorregirCatalogo">Corregir</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="catId" value="0">
          <div class="form-group">
            <label for="catNombre">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control text-uppercase" id="catNombre" maxlength="150" required>
          </div>
          <div class="campos-universidad">
            <div class="form-group">
              <label for="catTipo">Tipo de universidad <span class="text-danger">*</span></label>
              <select class="form-control" id="catTipo">
                <option value="PUBLICA">Pública</option>
                <option value="PRIVADA">Privada</option>
                <option value="EXTRANJERA">Extranjera</option>
                <option value="CONVENIO">Convenio</option>
              </select>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="catActiva">
              <label class="custom-control-label" for="catActiva">Activa (aparece en la lista del formulario del estudiante)</label>
            </div>
          </div>
          <small class="text-muted d-block mt-2" id="avisoUsoCatalogo"></small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success" id="btnGuardarCatalogo"><i class="fas fa-save"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: unir con otro registro -->
<div class="modal fade" id="modalUnirCatalogo" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:1500;">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="formUnirCatalogo" autocomplete="off">
        <div class="modal-header bg-gradient-primary">
          <h5 class="modal-title text-white">Unir con el registro correcto</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="unirId" value="0">
          <p class="mb-2">Se quita: <strong id="unirOrigen"></strong></p>
          <div class="form-group">
            <label for="unirDestino">Sus estudiantes pasan a: <span class="text-danger">*</span></label>
            <select class="form-control" id="unirDestino" required></select>
          </div>
          <small class="text-muted">No se puede deshacer: el registro mal escrito se borra.</small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnUnirCatalogo"><i class="fas fa-object-group"></i> Unir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var catalogo = 'universidad';
    var registros = [];
    var etiquetas = { universidad: 'universidad', profesion: 'profesión' };
    var tipos = { PUBLICA: 'Pública', PRIVADA: 'Privada', EXTRANJERA: 'Extranjera', CONVENIO: 'Convenio' };
    var URL_AJAX = 'ajax/catalogos.ajax.php';

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function porId(id) {
        return registros.filter(function (r) { return parseInt(r.Id) === parseInt(id); })[0];
    }

    function dibujar() {
        var esUniversidad = catalogo === 'universidad';
        $('.col-universidad').toggle(esUniversidad);
        var filtro = $('#buscarCatalogo').val().trim().toUpperCase();
        var html = '';
        registros.forEach(function (r) {
            if (filtro && String(r.Nombre).toUpperCase().indexOf(filtro) === -1) return;
            var activa = parseInt(r.Estado) === 1;
            var usos = parseInt(r.Estudiantes) || 0;
            html += '<tr' + (activa ? '' : ' class="text-muted" style="background:#f8f9fa;"') + '>' +
                '<td>' + esc(r.Nombre) + '</td>' +
                (esUniversidad ? '<td class="text-center">' + esc(tipos[r.TipoUniversidad] || r.TipoUniversidad) + '</td>' : '') +
                '<td class="text-center">' + usos + '</td>' +
                (esUniversidad ? '<td class="text-center">' + (activa
                    ? '<span class="badge badge-success">Activa</span>'
                    : '<span class="badge badge-secondary">Inactiva</span>') + '</td>' : '') +
                '<td class="text-center text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-warning btn-corregir" data-id="' + r.Id + '" title="Corregir"><i class="fas fa-edit"></i></button> ' +
                    '<button type="button" class="btn btn-sm btn-primary btn-unir" data-id="' + r.Id + '" title="Unir con..."><i class="fas fa-object-group"></i></button> ' +
                    (usos === 0
                        ? '<button type="button" class="btn btn-sm btn-danger btn-eliminar" data-id="' + r.Id + '" title="Eliminar"><i class="fas fa-trash"></i></button>'
                        : '') +
                '</td></tr>';
        });
        $('#tablaCatalogo').html(html || '<tr><td colspan="5" class="text-center text-muted">Sin resultados</td></tr>');
    }

    function cargar() {
        $.post(URL_AJAX, { accion: 'listar', catalogo: catalogo }, function (resp) {
            if (!resp || !resp.success) {
                $('#tablaCatalogo').html('<tr><td colspan="5" class="text-center text-danger">' + esc((resp && resp.mensaje) || 'No se pudo cargar') + '</td></tr>');
                return;
            }
            registros = resp.registros;
            dibujar();
        }, 'json');
    }

    function avisar(resp) {
        if (resp && resp.success) {
            Swal.fire({ type: 'success', title: resp.mensaje, timer: 1800, showConfirmButton: false });
        } else {
            Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo guardar', 'warning');
        }
    }

    $('.nav-tabs .nav-link').on('click', function (e) {
        e.preventDefault();
        $('.nav-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        catalogo = $(this).data('catalogo');
        $('#buscarCatalogo').val('');
        $('#tablaCatalogo').html('<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>');
        cargar();
    });
    $('#buscarCatalogo').on('input', dibujar);

    // Corregir
    $('#tablaCatalogo').on('click', '.btn-corregir', function () {
        var r = porId($(this).data('id'));
        if (!r) return;
        var usos = parseInt(r.Estudiantes) || 0;
        $('#tituloCorregirCatalogo').text('Corregir ' + etiquetas[catalogo]);
        $('#catId').val(r.Id);
        $('#catNombre').val(r.Nombre).attr('maxlength', catalogo === 'universidad' ? 150 : 100);
        $('.campos-universidad').toggle(catalogo === 'universidad');
        $('#catTipo').val(r.TipoUniversidad || 'PUBLICA');
        $('#catActiva').prop('checked', parseInt(r.Estado) === 1);
        $('#avisoUsoCatalogo').text(usos
            ? 'El cambio se verá en los ' + usos + ' estudiante(s) que la tienen.'
            : 'Ningún estudiante la usa todavía.');
        $('#modalCorregirCatalogo').modal('show');
    });

    $('#formCorregirCatalogo').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnGuardarCatalogo').prop('disabled', true);
        $.post(URL_AJAX, {
            accion: 'editar', catalogo: catalogo, id: $('#catId').val(), nombre: $('#catNombre').val(),
            tipo: $('#catTipo').val(), estado: $('#catActiva').is(':checked') ? 1 : 0
        }, function (resp) {
            if (resp && resp.success) {
                $('#modalCorregirCatalogo').modal('hide');
                cargar();
            }
            avisar(resp);
        }, 'json').fail(function () {
            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
        }).always(function () { $btn.prop('disabled', false); });
    });

    // Unir con...
    $('#tablaCatalogo').on('click', '.btn-unir', function () {
        var r = porId($(this).data('id'));
        if (!r) return;
        $('#unirId').val(r.Id);
        $('#unirOrigen').text(r.Nombre + ' (' + (parseInt(r.Estudiantes) || 0) + ' estudiante(s))');
        var opciones = '<option value="">— Elija la ' + etiquetas[catalogo] + ' correcta —</option>';
        registros.forEach(function (o) {
            if (parseInt(o.Id) !== parseInt(r.Id)) {
                opciones += '<option value="' + o.Id + '">' + esc(o.Nombre) + '</option>';
            }
        });
        $('#unirDestino').html(opciones);
        $('#modalUnirCatalogo').modal('show');
    });

    $('#formUnirCatalogo').on('submit', function (e) {
        e.preventDefault();
        var origen = porId($('#unirId').val());
        var destino = porId($('#unirDestino').val());
        if (!origen || !destino) return;
        Swal.fire({
            title: '¿Unir?',
            html: '<b>' + esc(origen.Nombre) + '</b> se quitará y sus estudiantes pasarán a <b>' + esc(destino.Nombre) + '</b>.',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, unir',
            cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.value) return;
            var $btn = $('#btnUnirCatalogo').prop('disabled', true);
            $.post(URL_AJAX, { accion: 'unir', catalogo: catalogo, id: origen.Id, destinoId: destino.Id }, function (resp) {
                if (resp && resp.success) {
                    $('#modalUnirCatalogo').modal('hide');
                    cargar();
                }
                avisar(resp);
            }, 'json').fail(function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }).always(function () { $btn.prop('disabled', false); });
        });
    });

    // Eliminar (solo sin estudiantes)
    $('#tablaCatalogo').on('click', '.btn-eliminar', function () {
        var r = porId($(this).data('id'));
        if (!r) return;
        Swal.fire({
            title: '¿Eliminar "' + r.Nombre + '"?',
            text: 'Ningún estudiante la usa.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.value) return;
            $.post(URL_AJAX, { accion: 'eliminar', catalogo: catalogo, id: r.Id }, function (resp) {
                if (resp && resp.success) cargar();
                avisar(resp);
            }, 'json');
        });
    });

    cargar();
});
</script>

</body>
