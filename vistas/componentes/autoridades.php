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
                  <h2>CONFIGURACIÓN</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>AUTORIDADES Y PARÁMETROS</h3>
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

        <!-- Datos de la institución: encabezado de los reportes, firmas y pantallas (InstitucionModelo) -->
        <div class="card shadow-sm mt-4">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-university"></i> Datos de la institución
            </h4>
          </div>
          <div class="card-body">
            <p class="text-muted" style="font-size:13px;">
              Salen en el <strong>encabezado de todos los reportes PDF</strong> (planillas, órdenes de pago, recibos,
              situación académica, compromiso, lista de asistencia...), bajo la firma del coordinador y en las pantallas
              del sistema. Si deja un campo vacío, vuelve al valor original.
            </p>
            <form id="formInstitucion" autocomplete="off">
              <div class="row">
                <?php foreach (InstitucionModelo::TEXTOS as $claveTexto => $campoTexto) { ?>
                <div class="col-md-6 form-group">
                  <label for="<?php echo $claveTexto; ?>"><strong><?php echo $campoTexto[0]; ?></strong></label>
                  <input type="text" class="form-control campo-institucion" id="<?php echo $claveTexto; ?>"
                         name="institucion[<?php echo $claveTexto; ?>]" maxlength="255"
                         placeholder="<?php echo htmlspecialchars($campoTexto[1]); ?>">
                  <?php if ($campoTexto[2] !== '') { ?><small class="text-muted"><?php echo htmlspecialchars($campoTexto[2]); ?></small><?php } ?>
                </div>
                <?php } ?>
              </div>

              <!-- Vista previa del encabezado de los reportes -->
              <div class="inst-vista mb-3">
                <img id="vistaLogoIzq" alt="Logo izquierdo">
                <div class="inst-vista-textos">
                  <div class="inst-uni" id="vistaUniversidad"></div>
                  <div class="inst-fac" id="vistaFacultad"></div>
                  <div class="inst-fac" id="vistaUnidad"></div>
                  <div class="inst-dir" id="vistaDireccion"></div>
                </div>
                <img id="vistaLogoDer" alt="Logo derecho">
              </div>

              <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <small class="text-muted" id="actualizadoInstitucion"></small>
                <button type="submit" class="btn btn-success" id="btnGuardarInstitucion">
                  <i class="fas fa-save"></i> Guardar datos de la institución
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Logos de la institución (tabla imagenfirma, claves LOGO_*) -->
        <div class="card shadow-sm mt-4">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-image"></i> Logos
            </h4>
          </div>
          <div class="card-body">
            <p class="text-muted" style="font-size:13px;">
              Los logos <strong>izquierdo y derecho</strong> salen en el encabezado de los reportes PDF. El
              <strong>logo del sistema</strong> es el de las pantallas (ingreso, inicio y barra superior).
              Mientras no suba uno, se usa el original.
            </p>
            <div class="row">
              <?php foreach (InstitucionModelo::LOGOS as $claveImagen => $logo) { ?>
              <div class="col-md-4 mb-3">
                <div class="firma-caja" data-clave="<?php echo $claveImagen; ?>">
                  <div class="firma-titulo"><?php echo $logo[0]; ?></div>
                  <div class="firma-vista" id="vista_<?php echo $claveImagen; ?>"><span class="text-muted small">Sin imagen</span></div>
                  <small class="text-muted d-block mb-2" id="actualizado_<?php echo $claveImagen; ?>"></small>
                  <div class="d-flex flex-wrap" style="gap:8px;">
                    <label class="btn btn-sm btn-primary mb-0">
                      <i class="fas fa-upload"></i> Subir logo
                      <input type="file" accept="image/png,image/jpeg" class="d-none input-imagen-firma" data-clave="<?php echo $claveImagen; ?>">
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-firma" data-clave="<?php echo $claveImagen; ?>" style="display:none;">
                      <i class="fas fa-undo"></i> Volver al original
                    </button>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>
            <small class="text-muted">PNG (mejor con fondo transparente) o JPG, hasta 3 MB. Se ven mejor los logos cuadrados para los reportes.</small>
          </div>
        </div>
        <style>
          .inst-vista { display: flex; align-items: center; gap: 12px; border: 1px dashed #c9d2e3; border-radius: 10px; padding: 12px 16px; background: #fff; }
          .inst-vista img { width: 64px; height: 64px; object-fit: contain; flex-shrink: 0; }
          .inst-vista-textos { flex: 1; text-align: center; min-width: 0; }
          .inst-uni { font-weight: 700; color: #1e3c72; font-size: 15px; }
          .inst-fac { font-weight: 700; color: #1e3c72; font-size: 12px; }
          .inst-dir { color: #646464; font-size: 10.5px; margin-top: 2px; }
          @media (max-width: 575px) { .inst-vista img { width: 40px; height: 40px; } }
        </style>

        <!-- Parámetros académicos (tabla configuracion) -->
        <div class="card shadow-sm mt-4">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-sliders-h"></i> Parámetros académicos
            </h4>
          </div>
          <div class="card-body">
            <form id="formParametros" autocomplete="off">
              <div class="row align-items-end">
                <div class="col-md-4 form-group">
                  <label for="notaAprobacion"><strong>Nota mínima de aprobación</strong> <small class="text-muted">(sobre 100)</small></label>
                  <input type="number" class="form-control" id="notaAprobacion" name="notaAprobacion" min="1" max="100" step="1" required>
                </div>
                <div class="col-md-8 form-group">
                  <p class="text-muted mb-1" style="font-size:13px;">
                    Con esta nota o más el módulo cuenta como <strong>APROBADO</strong> en el registro de notas, la
                    búsqueda de notas, "Mis calificaciones" del estudiante, Matriculados, los reportes y el
                    Documento de compromiso. Las notas ya registradas no cambian: solo cambia si se consideran
                    aprobadas o no.
                  </p>
                  <small class="text-muted" id="actualizadoNota"></small>
                </div>
              </div>
              <div class="text-right">
                <button type="submit" class="btn btn-success" id="btnGuardarParametros">
                  <i class="fas fa-save"></i> Guardar nota mínima
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Firma del coordinador y sello (imágenes): solo en las órdenes que genera el estudiante -->
        <div class="card shadow-sm mt-4">
          <div class="card-header bg-gradient-primary">
            <h4 class="card-title text-white mb-0">
              <i class="fas fa-signature"></i> Firma y sello para órdenes de pago
            </h4>
          </div>
          <div class="card-body">
            <p class="text-muted" style="font-size:13px;">
              Se imprimen solo en las órdenes de pago de <strong>matrícula y cuotas que el estudiante genera desde el sistema</strong>.
              Las que genera el personal se firman a mano. Cuando cambie el coordinador, suba aquí su firma.
            </p>
            <div class="row" id="imagenesFirma">
              <?php foreach (['FIRMA' => 'Firma del coordinador', 'SELLO' => 'Sello de Posgrado'] as $claveImagen => $nombreImagen) { ?>
              <div class="col-md-6 mb-3">
                <div class="firma-caja" data-clave="<?php echo $claveImagen; ?>">
                  <div class="firma-titulo"><?php echo $nombreImagen; ?></div>
                  <div class="firma-vista" id="vista_<?php echo $claveImagen; ?>"><span class="text-muted small">Sin imagen</span></div>
                  <small class="text-muted d-block mb-2" id="actualizado_<?php echo $claveImagen; ?>"></small>
                  <div class="d-flex flex-wrap" style="gap:8px;">
                    <label class="btn btn-sm btn-primary mb-0">
                      <i class="fas fa-upload"></i> Subir imagen
                      <input type="file" accept="image/png,image/jpeg" class="d-none input-imagen-firma" data-clave="<?php echo $claveImagen; ?>">
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-firma" data-clave="<?php echo $claveImagen; ?>" style="display:none;">
                      <i class="fas fa-trash"></i> Quitar
                    </button>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>
            <small class="text-muted">PNG o JPG. Al subir la firma, su fondo blanco se vuelve transparente.</small>
          </div>
        </div>
        <style>
          .firma-caja { border: 1px solid #e3e7ef; border-radius: 10px; padding: 14px; height: 100%; }
          .firma-titulo { font-weight: 700; color: #1e3c72; font-size: 13px; margin-bottom: 8px; }
          .firma-vista { height: 130px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px;
            border-radius: 8px; background: repeating-conic-gradient(#f1f3f7 0% 25%, #fff 0% 50%) 50% / 16px 16px; }
          .firma-vista img { max-height: 120px; max-width: 100%; }
        </style>

        <!-- Los costos de los certificados (por grado académico) están en su propia pantalla -->
        <div class="text-right mt-3">
          <a href="costoscertificados" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-invoice-dollar"></i> Costos de certificados</a>
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

    // ---- Parámetros académicos ----
    var notaGuardada = null;

    function cargarParametros() {
        $.post('ajax/autoridad.ajax.php', { accion: 'obtenerParametros' }, function (resp) {
            if (!resp || !resp.success) return;
            notaGuardada = resp.notaAprobacion;
            $('#notaAprobacion').val(resp.notaAprobacion);
            var d = resp.detalle;
            $('#actualizadoNota').text(d && d.ResponsableActualizacion
                ? 'Actualizado: ' + d.FechaActualizacion + ' por ' + d.ResponsableActualizacion : '');
        }, 'json');
    }

    $('#formParametros').on('submit', function (e) {
        e.preventDefault();
        var nueva = parseInt($('#notaAprobacion').val(), 10);
        if (isNaN(nueva) || nueva < 1 || nueva > 100) {
            Swal.fire('Atención', 'La nota mínima debe ser un número entero entre 1 y 100', 'warning');
            return;
        }
        if (nueva === notaGuardada) {
            Swal.fire({ type: 'info', title: 'Sin cambios', text: 'La nota mínima ya es ' + nueva, timer: 1500, showConfirmButton: false });
            return;
        }
        // Afecta a todo el sistema: se confirma antes de guardar
        Swal.fire({
            type: 'warning',
            title: '¿Cambiar la nota mínima de ' + notaGuardada + ' a ' + nueva + '?',
            text: 'Desde ahora, en todo el sistema, un módulo se aprobará con ' + nueva + ' o más. Las notas registradas no cambian.',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (!r.value) return; // SweetAlert2 v8
            var $btn = $('#btnGuardarParametros').prop('disabled', true);
            $.post('ajax/autoridad.ajax.php', { accion: 'guardarNotaAprobacion', notaAprobacion: nueva }, function (resp) {
                if (resp && resp.success) {
                    window.NOTA_APROBACION = nueva;
                    Swal.fire({ type: 'success', title: resp.mensaje, timer: 1800, showConfirmButton: false });
                    cargarParametros();
                } else {
                    Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo guardar', 'warning');
                }
            }, 'json').fail(function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });
    });

    // ---- Firma y sello para las órdenes que genera el estudiante ----
    function cargarImagenesFirma() {
        $.post('ajax/autoridad.ajax.php', { accion: 'estadoImagenesFirma' }, function (resp) {
            if (!resp || !resp.success) return;
            Object.keys(resp.imagenes).forEach(function (clave) {
                var img = resp.imagenes[clave];
                var src = img.imagen || img.porDefecto;
                $('#vista_' + clave).html(src
                    ? $('<img>').attr({ src: src, alt: img.nombre })
                    : '<span class="text-muted small">Sin imagen</span>');
                $('.btn-quitar-firma[data-clave="' + clave + '"]').toggle(!!img.imagen);
                var d = img.actualizado;
                $('#actualizado_' + clave).text(d && d.ResponsableActualizacion
                    ? 'Actualizada: ' + d.FechaActualizacion + ' por ' + d.ResponsableActualizacion
                    : (img.porDefecto ? 'Logo original' : ''));
                // Vista previa del encabezado de los reportes
                if (clave === 'LOGO_IZQUIERDO') $('#vistaLogoIzq').attr('src', src || '');
                if (clave === 'LOGO_DERECHO') $('#vistaLogoDer').attr('src', src || '');
            });
        }, 'json');
    }

    $(document).on('change', '.input-imagen-firma', function () {
        var archivo = this.files[0];
        if (!archivo) return;
        var datos = new FormData();
        datos.append('accion', 'subirImagenFirma');
        datos.append('clave', $(this).data('clave'));
        datos.append('imagen', archivo);
        var input = this;
        $.ajax({ url: 'ajax/autoridad.ajax.php', type: 'POST', data: datos, processData: false, contentType: false, dataType: 'json' })
            .done(function (resp) {
                if (resp && resp.success) {
                    Swal.fire({ type: 'success', title: resp.mensaje, timer: 1500, showConfirmButton: false });
                    cargarImagenesFirma();
                } else {
                    Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo guardar la imagen', 'warning');
                }
            })
            .fail(function () { Swal.fire('Error', 'No se pudo conectar con el servidor', 'error'); })
            .always(function () { input.value = ''; });
    });

    $(document).on('click', '.btn-quitar-firma', function () {
        var clave = $(this).data('clave');
        var esLogo = String(clave).indexOf('LOGO_') === 0;
        Swal.fire({
            type: 'warning',
            title: esLogo ? '¿Volver al logo original?' : '¿Quitar la imagen?',
            text: esLogo ? 'Se quita el logo subido y se usa el original.' : 'Las órdenes que genere el estudiante saldrán sin ella.',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (r) {
            if (!r.value) return;
            $.post('ajax/autoridad.ajax.php', { accion: 'quitarImagenFirma', clave: clave }, function () {
                cargarImagenesFirma();
            }, 'json');
        });
    });

    // ---- Datos de la institución ----
    var porDefectoInstitucion = {};

    function valorInstitucion(clave) {
        var v = $.trim($('#' + clave).val());
        return v !== '' ? v : (porDefectoInstitucion[clave] || '');
    }

    function actualizarVistaInstitucion() {
        $('#vistaUniversidad').text(valorInstitucion('INST_UNIVERSIDAD'));
        $('#vistaFacultad').text(valorInstitucion('INST_FACULTAD'));
        $('#vistaUnidad').text(valorInstitucion('INST_UNIDAD'));
        $('#vistaDireccion').text(['INST_DIRECCION', 'INST_TELEFONOS', 'INST_CIUDAD']
            .map(valorInstitucion).filter(function (v) { return v !== ''; }).join(' - '));
    }

    function cargarInstitucion() {
        $.post('ajax/autoridad.ajax.php', { accion: 'obtenerInstitucion' }, function (resp) {
            if (!resp || !resp.success) return;
            Object.keys(resp.campos).forEach(function (clave) {
                porDefectoInstitucion[clave] = resp.campos[clave].porDefecto;
                $('#' + clave).val(resp.campos[clave].valor);
            });
            var d = resp.actualizado;
            $('#actualizadoInstitucion').text(d ? 'Actualizado: ' + d.FechaActualizacion + ' por ' + d.ResponsableActualizacion : '');
            actualizarVistaInstitucion();
        }, 'json');
    }

    $('.campo-institucion').on('input', actualizarVistaInstitucion);

    $('#formInstitucion').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#btnGuardarInstitucion').prop('disabled', true);
        $.post('ajax/autoridad.ajax.php', $(this).serialize() + '&accion=guardarInstitucion', function (resp) {
            if (resp && resp.success) {
                Swal.fire({ type: 'success', title: resp.mensaje, timer: 1800, showConfirmButton: false });
                cargarInstitucion();
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
    cargarParametros();
    cargarInstitucion();
    cargarImagenesFirma();
});
</script>

</body>
