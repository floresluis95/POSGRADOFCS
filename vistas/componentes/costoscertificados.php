<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
<link rel="stylesheet" href="vistas/recursos/assets/css/gestion-registros.css?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/css/gestion-registros.css'); ?>">
<style>
  /* Costos de certificados: tarifario oficial (certificado x grado académico) */
  .cc-pagina { max-width: 1120px; margin: 0 auto 40px; --cc-azul: #1e3c72; --cc-dorado: #d9a941; --cc-borde: #e3e7ef; --cc-gris: #64748b; }
  .cc-card { background: #fff; border: 1px solid var(--cc-borde); border-radius: 12px; box-shadow: 0 2px 10px rgba(20, 40, 80, .05); overflow: hidden; }

  /* Encabezado institucional: azul con línea dorada */
  .cc-cab { background: var(--cc-azul); color: #fff; padding: 18px 24px; border-bottom: 3px solid var(--cc-dorado); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
  .cc-cab h3 { margin: 0; font-size: 17px; font-weight: 700; color: #fff; letter-spacing: .2px; }
  .cc-cab p { margin: 3px 0 0; font-size: 12.5px; color: #c9d5ea; }
  .cc-resumen { display: flex; gap: 8px; flex-wrap: wrap; }
  .cc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: rgba(255, 255, 255, .12); color: #fff; }
  .cc-chip.is-falta { background: #fff7e6; color: #8a5a00; }

  /* Pasos de uso, en una línea */
  .cc-guia { display: flex; flex-wrap: wrap; gap: 18px; padding: 12px 24px; background: #f8fafc; border-bottom: 1px solid var(--cc-borde); font-size: 12.5px; color: #475569; }
  .cc-guia span { display: inline-flex; align-items: center; gap: 7px; }
  .cc-guia b { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: var(--cc-azul); color: #fff; font-size: 11px; }

  .cc-tabla { margin: 0; }
  .cc-tabla thead th { background: #fff; color: var(--cc-azul); font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; border-top: 0; border-bottom: 2px solid var(--cc-borde); vertical-align: bottom; text-align: center; padding: 14px 10px 10px; }
  .cc-tabla thead th:first-child { text-align: left; padding-left: 24px; }
  .cc-tabla thead th small { display: block; color: #94a3b8; font-weight: 500; text-transform: none; letter-spacing: 0; font-size: 11px; margin-top: 2px; }
  .cc-tabla td { padding: 12px 10px; border-color: #eef1f6; vertical-align: middle; }
  .cc-tabla tbody tr:hover { background: #fafbfd; }
  .cc-tipo { padding-left: 24px !important; min-width: 230px; }
  .cc-tipo strong { display: block; color: #1f2937; font-size: 13.5px; }
  .cc-tipo small { display: block; color: #94a3b8; font-size: 11.5px; }
  .cc-copiar { font-size: 11.5px; color: var(--cc-azul); padding: 0; margin-top: 3px; font-weight: 600; }
  .cc-copiar:hover { color: #16305c; text-decoration: underline; }

  .cc-precio { min-width: 138px; max-width: 170px; margin: 0 auto; }
  .cc-precio .input-group-text { background: #f3f5f9; color: var(--cc-gris); font-size: 12px; border-color: #dfe3ea; }
  .cc-precio .form-control { border-color: #dfe3ea; text-align: right; font-weight: 600; color: #1f2937; font-variant-numeric: tabular-nums; }
  .cc-precio .form-control::placeholder { color: #c2410c; font-weight: 500; font-size: 12px; opacity: .75; }
  .cc-precio .form-control:focus { border-color: var(--cc-azul); box-shadow: 0 0 0 3px rgba(30, 60, 114, .12); }
  .cc-precio.is-cambiado .form-control, .cc-precio.is-cambiado .input-group-text { border-color: var(--cc-dorado); background: #fffaf0; }
  .cc-precio.is-invalido .form-control { border-color: #dc2626; background: #fef2f2; }

  /* Pie: cambios sin guardar y acciones */
  .cc-pie { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 14px 24px; background: #f8fafc; border-top: 1px solid var(--cc-borde); }
  .cc-pie-info { font-size: 12px; color: var(--cc-gris); }
  .cc-pie-info .cc-pendientes { color: #8a5a00; font-weight: 700; }
  .cc-pie .btn { font-weight: 600; }
  .cc-pie .btn-guardar { background: var(--cc-azul); border-color: var(--cc-azul); color: #fff; }
  .cc-pie .btn-guardar:hover { background: #16305c; color: #fff; }
  .cc-pie .btn-guardar:disabled { opacity: .5; }

  @media (max-width: 767.98px) {
    .cc-cab, .cc-guia, .cc-pie { padding-left: 16px; padding-right: 16px; }
    .cc-tipo { padding-left: 16px !important; }
  }
</style>
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
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>CERTIFICADOS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>COSTOS POR GRADO ACADÉMICO</h3>
                  </div>
                </div>
              </div>
            </div>

<div class="kt-container kt-grid__item kt-grid__item--fluid">
  <div class="cc-pagina">
    <div class="cc-card">
      <div class="cc-cab">
        <div>
          <h3><i class="fas fa-file-invoice-dollar"></i> Tarifario de certificados</h3>
          <p>Precio oficial de cada certificado según el grado académico del programa.</p>
        </div>
        <div class="cc-resumen" id="resumenCostos"></div>
      </div>

      <div class="cc-guia">
        <span><b>1</b> Escriba el precio en Bs. de cada grado</span>
        <span><b>2</b> Pulse Guardar</span>
        <span><b>3</b> Al emitir el certificado, el monto llega solo y se puede ajustar</span>
      </div>

      <form id="formCostosCertificados" autocomplete="off">
        <div class="table-responsive">
          <table class="table cc-tabla" id="tablaCostosCertificados">
            <thead><tr id="cabCostos"><th>Certificado</th></tr></thead>
            <tbody id="cuerpoCostos">
              <tr><td class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="cc-pie">
          <div class="cc-pie-info">
            <div id="estadoCambios">Sin cambios.</div>
            <div id="actualizadoCostos"></div>
          </div>
          <div>
            <button type="button" class="btn btn-outline-secondary" id="btnDescartarCostos" disabled>Descartar cambios</button>
            <button type="submit" class="btn btn-guardar" id="btnGuardarCostos">
              <i class="fas fa-save"></i> Guardar
            </button>
          </div>
        </div>
      </form>
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
  </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Qué es cada certificado (texto corto bajo su nombre)
    var DESCRIPCION = {
        CALIFICACIONES: 'Formato pre-impreso de Posgrado Central',
        HISTORIAL_ACADEMICO: 'Formato pre-impreso, hoja oficio',
        APROBACION_MONOGRAFIA: 'Aprobación del trabajo de grado',
        MEDIO_MAGNETICO: 'Carta con membrete propio',
        REVISION: 'Informe de cumplimiento de requisitos'
    };
    var grados = {};
    var tipos = {};

    function precioTexto(valor) {
        return valor === null || valor === undefined ? '' : Number(valor).toFixed(2);
    }

    function formatoBs(valor) {
        return 'Bs. ' + Number(valor).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fechaTexto(fechaHora) {
        var p = String(fechaHora || '').match(/^(\d{4})-(\d{2})-(\d{2})\s*(\d{2}:\d{2})?/);
        return p ? p[3] + '/' + p[2] + '/' + p[1] + (p[4] ? ' ' + p[4] : '') : fechaHora;
    }

    // Cuántos precios faltan y cuántos cambios hay sin guardar
    function actualizarEstado() {
        var $campos = $('.costo-certificado');
        var faltan = $campos.filter(function () { return $(this).val().trim() === ''; }).length;
        var cambiados = $campos.filter(function () { return $(this).val() !== String($(this).data('original')); }).length;

        $('#resumenCostos').html(
            '<span class="cc-chip"><i class="fas fa-check-circle"></i> ' + ($campos.length - faltan) + ' de ' + $campos.length + ' definidos</span>' +
            (faltan ? '<span class="cc-chip is-falta"><i class="fas fa-exclamation-circle"></i> ' + faltan + ' sin precio</span>' : '')
        );
        $('#estadoCambios').html(cambiados
            ? '<span class="cc-pendientes"><i class="fas fa-pen"></i> ' + cambiados + (cambiados === 1 ? ' cambio sin guardar' : ' cambios sin guardar') + '</span>'
            : 'Sin cambios.');
        $('#btnDescartarCostos').prop('disabled', !cambiados);
    }

    function cargarCostos() {
        $.post('ajax/autoridad.ajax.php', { accion: 'obtenerCostosCertificados' }, function (resp) {
            if (!resp || !resp.success) return;
            tipos = resp.tipos;
            grados = resp.grados;

            var $cab = $('#cabCostos').html('<th>Certificado</th>');
            Object.keys(grados).forEach(function (grado) {
                $cab.append($('<th></th>').text(grados[grado]));
            });

            var primerGrado = grados[Object.keys(grados)[0]];
            var $cuerpo = $('#cuerpoCostos').empty();
            Object.keys(tipos).forEach(function (tipo) {
                var $tipo = $('<td class="cc-tipo"></td>')
                    .append($('<strong></strong>').text(tipos[tipo]))
                    .append($('<small></small>').text(DESCRIPCION[tipo] || ''))
                    .append($('<button type="button" class="btn btn-link btn-sm cc-copiar"></button>')
                        .attr('title', 'Copiar el precio de ' + primerGrado + ' a los demás grados')
                        .html('<i class="fas fa-copy"></i> Mismo precio en todos los grados'));
                var $fila = $('<tr></tr>').append($tipo);
                Object.keys(grados).forEach(function (grado) {
                    var valor = precioTexto(resp.precios[tipo] ? resp.precios[tipo][grado] : null);
                    var $input = $('<input type="number" class="form-control costo-certificado" min="0" step="0.01" placeholder="Sin precio">')
                        .attr({ 'data-tipo': tipo, 'data-grado': grado, 'data-original': valor, 'aria-label': tipos[tipo] + ' · ' + grados[grado] })
                        .val(valor);
                    $fila.append($('<td></td>').append(
                        $('<div class="input-group input-group-sm cc-precio"></div>')
                            .append('<div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>')
                            .append($input)
                    ));
                });
                $cuerpo.append($fila);
            });

            var d = resp.ultimaActualizacion;
            $('#actualizadoCostos').text(d && d.ResponsableActualizacion
                ? 'Última actualización: ' + fechaTexto(d.FechaActualizacion) + ' · ' + d.ResponsableActualizacion
                : '');
            actualizarEstado();
        }, 'json');
    }

    $('#cuerpoCostos').on('input', '.costo-certificado', function () {
        var valor = $(this).val().trim();
        $(this).closest('.cc-precio')
            .toggleClass('is-cambiado', $(this).val() !== String($(this).data('original')))
            .toggleClass('is-invalido', valor !== '' && (isNaN(valor) || parseFloat(valor) < 0));
        actualizarEstado();
    });

    // Al salir del campo el monto queda con dos decimales (120 → 120.00)
    $('#cuerpoCostos').on('change', '.costo-certificado', function () {
        var valor = $(this).val().trim();
        if (valor !== '' && !isNaN(valor) && parseFloat(valor) >= 0) {
            $(this).val(parseFloat(valor).toFixed(2)).trigger('input');
        }
    });

    $('#cuerpoCostos').on('click', '.cc-copiar', function () {
        var $campos = $(this).closest('tr').find('.costo-certificado');
        var valor = $campos.first().val();
        if (valor === '') {
            Swal.fire({ type: 'info', title: 'Primero el precio de ' + grados[Object.keys(grados)[0]], text: 'Escriba el precio en la primera columna y vuelva a pulsar.' });
            return;
        }
        $campos.val(valor).trigger('input');
    });

    $('#btnDescartarCostos').on('click', function () {
        $('.costo-certificado').each(function () { $(this).val(String($(this).data('original'))).trigger('input'); });
    });

    $('#formCostosCertificados').on('submit', function (e) {
        e.preventDefault();
        if ($('.cc-precio.is-invalido').length) {
            Swal.fire({ type: 'warning', title: 'Revise los montos', text: 'Los precios deben ser montos en Bs. iguales o mayores a 0.' });
            return;
        }
        var costos = {};
        var cambios = [];
        $('.costo-certificado').each(function () {
            var tipo = $(this).data('tipo');
            var grado = $(this).data('grado');
            var valor = $(this).val().trim();
            costos[tipo] = costos[tipo] || {};
            costos[tipo][grado] = valor;
            if (valor !== String($(this).data('original'))) {
                cambios.push('<tr><td style="text-align:left;padding:4px 8px;">' + $('<div>').text(tipos[tipo] + ' · ' + grados[grado]).html() + '</td>' +
                    '<td style="text-align:right;padding:4px 8px;font-weight:600;">' + (valor === '' ? 'Sin precio' : formatoBs(valor)) + '</td></tr>');
            }
        });
        if (!cambios.length) {
            Swal.fire({ type: 'info', title: 'Sin cambios', text: 'No se modificó ningún precio.', timer: 1800, showConfirmButton: false });
            return;
        }

        // Antes de guardar: qué precios cambian
        Swal.fire({
            type: 'question',
            title: 'Confirmar precios',
            html: '<table style="width:100%;font-size:13px;border-collapse:collapse;">' + cambios.join('') + '</table>',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Revisar',
            reverseButtons: true
        }).then(function (r) {
            if (!r.value) return;
            var $btn = $('#btnGuardarCostos').prop('disabled', true);
            $.post('ajax/autoridad.ajax.php', { accion: 'guardarCostosCertificados', costos: costos }, function (resp) {
                if (resp && resp.success) {
                    Swal.fire({ type: 'success', title: resp.mensaje, timer: 1800, showConfirmButton: false });
                    cargarCostos();
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

    // Aviso al salir con cambios sin guardar
    window.addEventListener('beforeunload', function (e) {
        if (!$('#btnDescartarCostos').prop('disabled')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    cargarCostos();
});
</script>

</body>
