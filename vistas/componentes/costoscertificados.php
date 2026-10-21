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

  /* Activar / bloquear edición */
  .cc-barra-edicion { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; padding: 10px 16px; background: #fff; border: 1px solid var(--cc-borde); border-left: 4px solid #94a3b8; border-radius: 10px; }
  .cc-modo { font-size: 13px; font-weight: 600; color: #475569; }
  .cc-modo i { margin-right: 4px; }
  .cc-btn-modo { height: 38px; padding: 0 16px; border-radius: 8px; font-weight: 600; background: var(--cc-azul); border: 1px solid var(--cc-azul); color: #fff; }
  .cc-btn-modo:hover { background: #16305c; color: #fff; }
  .cc-pagina:not(.bloqueado) .cc-barra-edicion { border-left-color: var(--cc-dorado); background: #fffaf0; }
  .cc-pagina:not(.bloqueado) .cc-modo { color: #8a5a00; }
  .cc-pagina:not(.bloqueado) .cc-btn-modo { background: #fff; color: #1f2937; border-color: var(--cc-borde); }
  /* Bloqueado: sin acciones de edición a la vista */
  .cc-pagina.bloqueado .cc-copiar,
  .cc-pagina.bloqueado .cc-tit-fila,
  .cc-pagina.bloqueado .cc-btn-editar,
  .cc-pagina.bloqueado .cc-otros-form,
  .cc-pagina.bloqueado #btnDescartarCostos,
  .cc-pagina.bloqueado #btnGuardarCostos,
  .cc-pagina.bloqueado .cc-guia { display: none !important; }
  .cc-pagina.bloqueado .costo-certificado { background: #f8fafc; }
  .cc-pagina.bloqueado .cc-tit { cursor: default; }

  /* Casilla "Titulación" bajo cada precio */
  .cc-tit { display: flex; align-items: center; justify-content: center; gap: 5px; margin: 6px 0 0; font-size: 11.5px; font-weight: 600; color: var(--cc-gris); cursor: pointer; user-select: none; }
  .cc-tit input { width: 14px; height: 14px; accent-color: var(--cc-azul); cursor: pointer; }
  .cc-tit.is-exigido { color: var(--cc-azul); }
  .cc-otros .cc-tit { margin: 0; }
  /* "Titulación en todos los grados": marca o desmarca toda la fila */
  .cc-tit-fila { display: block; font-size: 11.5px; color: var(--cc-azul); padding: 0; margin-top: 2px; font-weight: 600; text-align: left; }
  .cc-tit-fila:hover { color: #16305c; text-decoration: underline; }
  .cc-tit-fila.is-todas { color: #b91c1c; }
  .cc-otros .cc-tabla thead th small { display: block; font-weight: 500; text-transform: none; letter-spacing: 0; color: #94a3b8; }

  /* Otros certificados (sin documento) */
  .cc-otros { margin-top: 22px; }
  .cc-otros-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid var(--cc-borde); }
  .cc-otros-form label { display: block; font-size: 12px; font-weight: 600; color: #1f2937; margin-bottom: 6px; }
  .cc-otros-nombre { flex: 1 1 280px; }
  .cc-otros-nombre .form-control { height: 38px; border-color: #dfe3ea; }
  .cc-otros-monto { flex: 0 0 180px; }
  .cc-otros-monto .cc-precio { max-width: none; margin: 0; }
  .cc-otros-activo { height: 38px; display: flex; align-items: center; font-weight: 600; font-size: 13px; }
  .cc-otros-botones { display: flex; gap: 8px; }
  .cc-otros-botones .btn { height: 38px; font-weight: 600; }
  .cc-otros-botones .btn-guardar { background: var(--cc-azul); border-color: var(--cc-azul); color: #fff; }
  .cc-otros-botones .btn-guardar:hover { background: #16305c; color: #fff; }
  .cc-otros .cc-tabla thead th { text-align: left; }
  .cc-otros .cc-tabla td:first-child, .cc-otros .cc-tabla th:first-child { padding-left: 24px; }
  .cc-estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
  .cc-estado.activo { background: #e7f6ee; color: #2f855a; }
  .cc-estado.inactivo { background: #f1f3f6; color: #8a94a6; }
  .cc-btn-editar { white-space: nowrap; font-size: 12px; font-weight: 600; border: 1px solid var(--cc-borde); background: #fff; border-radius: 8px; padding: 4px 10px; color: #1f2937; }
  .cc-btn-editar:hover { border-color: var(--cc-azul); color: var(--cc-azul); }

  @media (max-width: 767.98px) {
    .cc-otros-form { padding-left: 16px; padding-right: 16px; }
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
  <div class="cc-pagina bloqueado" id="ccPagina">
    <!-- Al entrar todo está bloqueado (solo lectura): así no se cambia un precio por accidente -->
    <div class="cc-barra-edicion" id="barraEdicion">
      <span class="cc-modo" id="textoModo"><i class="fas fa-lock"></i> Edición bloqueada: solo puede ver los precios y requisitos.</span>
      <button type="button" class="btn cc-btn-modo" id="btnModoEdicion"><i class="fas fa-pen"></i> Activar edición</button>
    </div>

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
        <span><b><i class="fas fa-graduation-cap" style="font-size:10px;"></i></b> Marque <strong>Titulación</strong> si ese grado lo exige para titularse (se guarda al marcar)</span>
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

    <!-- Otros certificados: el sistema no los genera; en Registro de documentos solo se
         registra su pago. Aquí el ADM mantiene la lista y su precio. -->
    <div class="cc-card cc-otros" id="cardOtrosCertificados">
      <div class="cc-cab">
        <div>
          <h3><i class="fas fa-tags"></i> Otros certificados (sin documento)</h3>
          <p>Se registra solo el pago en Registro de documentos y salen en el recibo de certificados.</p>
        </div>
        <div class="cc-resumen" id="resumenOtros"></div>
      </div>
      <div class="cc-otros-form">
        <input type="hidden" id="sdc-id" value="">
        <div class="cc-otros-nombre">
          <label for="sdc-nombre">Nombre del certificado</label>
          <input type="text" class="form-control" id="sdc-nombre" maxlength="150" placeholder="Ej: PRO BIBLIOTECA">
        </div>
        <div class="cc-otros-monto">
          <label for="sdc-monto">Precio</label>
          <div class="input-group cc-precio">
            <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
            <input type="number" class="form-control" id="sdc-monto" min="0" step="0.01" placeholder="0.00">
          </div>
        </div>
        <div class="cc-otros-activo" id="sdc-activo-grupo" style="display:none;">
          <label class="mb-0"><input type="checkbox" id="sdc-activo" checked> Activo</label>
        </div>
        <div class="cc-otros-botones">
          <button type="button" class="btn btn-outline-secondary" id="sdc-cancelar" style="display:none;">Cancelar</button>
          <button type="button" class="btn btn-guardar" id="sdc-guardar"><i class="fas fa-plus"></i> Agregar</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table cc-tabla" id="tabla-sd-catalogo">
          <thead><tr><th>Certificado</th><th>Precio</th><th>Estado</th><th></th></tr></thead>
          <tbody><tr><td colspan="4" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr></tbody>
        </table>
      </div>
      <div class="cc-pie">
        <div class="cc-pie-info"><i class="fas fa-info-circle"></i> El precio se sugiere al registrar el pago y se puede ajustar. Uno inactivo ya no se ofrece, pero lo registrado se conserva.</div>
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
    // Certificados exigidos para la titulación: { clave: { GRADO: true } }. Clave: el tipo
    // generado o 'SD:<id>' para un certificado sin documento. Se guardan al marcarlos.
    var requisitosTit = {};
    var gradosTit = {};

    function casillaTitulacion(clave, grado) {
        var marcado = !!(requisitosTit[clave] && requisitosTit[clave][grado]);
        return $('<label class="cc-tit" title="Se exige para que el estudiante de este grado pueda titularse"></label>')
            .toggleClass('is-exigido', marcado)
            .append($('<input type="checkbox" class="req-titulacion">').attr({ 'data-clave': clave, 'data-grado': grado }).prop('checked', marcado))
            .append(' Titulación');
    }

    function cargarRequisitosTit() {
        return $.post('ajax/certificado.ajax.php', { accion: 'requisitosTitulacion' }, function (resp) {
            requisitosTit = (resp && resp.requisitos) || {};
            if (Array.isArray(requisitosTit)) requisitosTit = {};
            gradosTit = (resp && resp.grados) || {};
        }, 'json');
    }

    // Guarda una casilla (marcada o no). Devuelve la promesa; si falla, la casilla vuelve atrás.
    function guardarTitulacion($c, exigir) {
        var clave = $c.data('clave'), grado = $c.data('grado');
        $c.prop('checked', exigir).closest('.cc-tit').toggleClass('is-exigido', exigir);
        return $.post('ajax/certificado.ajax.php', { accion: 'guardarRequisitoTitulacion', clave: clave, grado: grado, exigir: exigir ? 1 : '' }, null, 'json')
            .then(function (resp) {
                if (!resp.success) return $.Deferred().reject(resp.mensaje).promise();
                requisitosTit[clave] = requisitosTit[clave] || {};
                if (exigir) requisitosTit[clave][grado] = true; else delete requisitosTit[clave][grado];
                actualizarBotonesFila();
                return resp;
            })
            .fail(function () {
                $c.prop('checked', !exigir).closest('.cc-tit').toggleClass('is-exigido', !exigir);
                actualizarBotonesFila();
            });
    }

    function avisoGuardado(titulo) {
        Swal.fire({ type: 'success', title: titulo, toast: true, position: 'top-end', timer: 1600, showConfirmButton: false });
    }
    function avisoError(mensaje) {
        Swal.fire({ type: 'error', title: 'No se guardó', text: typeof mensaje === 'string' ? mensaje : 'No se pudo conectar con el servidor' });
    }

    // Marcar / desmarcar una: se guarda en el momento (no espera al botón Guardar de los precios)
    $(document).on('change', '.req-titulacion', function () {
        guardarTitulacion($(this), $(this).is(':checked'))
            .done(function (resp) { avisoGuardado(resp.mensaje); })
            .fail(avisoError);
    });

    // ===== Activar / bloquear edición (al entrar, bloqueado) =====
    var modoEdicion = false;
    function aplicarModoEdicion() {
        $('#ccPagina').toggleClass('bloqueado', !modoEdicion);
        $('.costo-certificado, .req-titulacion, #sdc-nombre, #sdc-monto, #sdc-activo').prop('disabled', !modoEdicion);
        $('#textoModo').html(modoEdicion
            ? '<i class="fas fa-pen"></i> Edición activa: los cambios de precio se guardan con Guardar; las casillas de titulación, al marcarlas.'
            : '<i class="fas fa-lock"></i> Edición bloqueada: solo puede ver los precios y requisitos.');
        $('#btnModoEdicion').html(modoEdicion ? '<i class="fas fa-lock"></i> Bloquear edición' : '<i class="fas fa-pen"></i> Activar edición');
    }
    function bloquear() {
        modoEdicion = false;
        limpiarOtro();
        aplicarModoEdicion();
    }
    $('#btnModoEdicion').on('click', function () {
        if (!modoEdicion) {
            modoEdicion = true;
            aplicarModoEdicion();
            return;
        }
        // Precios cambiados sin guardar: se avisa antes de bloquear
        var cambiados = $('.costo-certificado').filter(function () { return $(this).val() !== String($(this).data('original')); }).length;
        if (!cambiados) { bloquear(); return; }
        Swal.fire({
            type: 'warning',
            title: 'Hay cambios sin guardar',
            text: cambiados + (cambiados === 1 ? ' precio cambiado no se guardó.' : ' precios cambiados no se guardaron.') + ' ¿Descartarlos y bloquear?',
            showCancelButton: true,
            confirmButtonText: 'Descartar y bloquear',
            cancelButtonText: 'Seguir editando'
        }).then(function (r) {
            if (!r.value) return;
            $('#btnDescartarCostos').trigger('click');
            bloquear();
        });
    });

    // "Titulación en todos los grados": marca toda la fila; si ya estaban todas, la desmarca
    function casillasDeFila(clave) {
        return $('.req-titulacion').filter(function () { return String($(this).data('clave')) === String(clave); });
    }
    function actualizarBotonesFila() {
        $('.cc-tit-fila').each(function () {
            var $casillas = casillasDeFila($(this).data('clave'));
            var todas = $casillas.length && $casillas.filter(':checked').length === $casillas.length;
            $(this).toggleClass('is-todas', !!todas)
                .html(todas ? '<i class="fas fa-times-circle"></i> Quitar titulación en todos' : '<i class="fas fa-graduation-cap"></i> Titulación en todos los grados');
        });
    }
    function botonFila(clave) {
        return $('<button type="button" class="btn btn-link btn-sm cc-tit-fila"></button>').attr('data-clave', clave);
    }
    $(document).on('click', '.cc-tit-fila', function () {
        var $casillas = casillasDeFila($(this).data('clave'));
        var exigir = $casillas.filter(':checked').length !== $casillas.length;
        var $cambian = $casillas.filter(function () { return $(this).is(':checked') !== exigir; });
        $.when.apply($, $cambian.map(function () { return guardarTitulacion($(this), exigir); }).get())
            .done(function () { avisoGuardado(exigir ? 'Se exige en todos los grados' : 'Ya no se exige en ningún grado'); })
            .fail(avisoError);
    });

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
                        .html('<i class="fas fa-copy"></i> Mismo precio en todos los grados'))
                    .append(botonFila(tipo));
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
                    ).append(casillaTitulacion(tipo, grado)));
                });
                $cuerpo.append($fila);
            });

            actualizarBotonesFila();
            aplicarModoEdicion();   // los campos recién dibujados respetan el bloqueo

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

    // ===== Otros certificados (sin documento): lista y precio =====
    var otros = [];
    function esc(t) { return $('<div>').text(t == null ? '' : String(t)).html(); }

    function cargarOtros() {
        $.post('ajax/certificado.ajax.php', { accion: 'catalogoSinDocumento', todos: 1 }, function (resp) {
            otros = (resp && resp.certificados) || [];
            var activos = otros.filter(function (c) { return Number(c.Activo) === 1; }).length;
            $('#resumenOtros').html('<span class="cc-chip"><i class="fas fa-check-circle"></i> ' + activos + (activos === 1 ? ' activo' : ' activos') + '</span>' +
                (otros.some(function (c) { return Number(c.Activo) === 1 && !(Number(c.Monto) > 0); })
                    ? '<span class="cc-chip is-falta"><i class="fas fa-exclamation-circle"></i> Hay certificados sin precio</span>' : ''));
            // Una columna por grado con la casilla "Titulación"
            var clavesGrado = Object.keys(gradosTit);
            $('#tabla-sd-catalogo thead tr').html('<th>Certificado</th><th>Precio</th>' +
                clavesGrado.map(function (g) { return '<th class="text-center">' + esc(gradosTit[g]) + '<small>Titulación</small></th>'; }).join('') +
                '<th>Estado</th><th></th>');
            var $cuerpo = $('#tabla-sd-catalogo tbody').empty();
            if (!otros.length) {
                $cuerpo.html('<tr><td colspan="' + (4 + clavesGrado.length) + '" class="text-center text-muted py-4">Todavía no hay otros certificados.</td></tr>');
            }
            otros.forEach(function (c) {
                var $fila = $('<tr></tr>')
                    .append($('<td></td>').append($('<strong></strong>').text(c.Nombre)).append(botonFila('SD:' + c.IdCertificadoSD)))
                    .append('<td class="text-nowrap">' + (Number(c.Monto) > 0 ? formatoBs(c.Monto) : '<span class="text-muted">Sin precio</span>') + '</td>');
                clavesGrado.forEach(function (g) { $fila.append($('<td class="text-center"></td>').append(casillaTitulacion('SD:' + c.IdCertificadoSD, g))); });
                $fila.append('<td><span class="cc-estado ' + (Number(c.Activo) ? 'activo">Activo' : 'inactivo">Inactivo') + '</span></td>')
                    .append('<td class="text-right pr-4"><button type="button" class="cc-btn-editar" data-editar-sd="' + c.IdCertificadoSD + '"><i class="fas fa-pen"></i> Editar</button></td>');
                $cuerpo.append($fila);
            });
            actualizarBotonesFila();
            aplicarModoEdicion();   // los campos recién dibujados respetan el bloqueo
        }, 'json');
    }

    function limpiarOtro() {
        $('#sdc-id, #sdc-nombre, #sdc-monto').val('');
        $('#sdc-activo').prop('checked', true);
        $('#sdc-activo-grupo, #sdc-cancelar').hide();
        $('#sdc-guardar').html('<i class="fas fa-plus"></i> Agregar');
    }

    $('#tabla-sd-catalogo').on('click', '[data-editar-sd]', function () {
        var id = String($(this).data('editar-sd'));
        var c = otros.filter(function (x) { return String(x.IdCertificadoSD) === id; })[0];
        if (!c) return;
        $('#sdc-id').val(c.IdCertificadoSD);
        $('#sdc-nombre').val(c.Nombre).focus();
        $('#sdc-monto').val(Number(c.Monto).toFixed(2));
        $('#sdc-activo').prop('checked', Number(c.Activo) === 1);
        $('#sdc-activo-grupo, #sdc-cancelar').show();
        $('#sdc-guardar').html('<i class="fas fa-save"></i> Guardar');
    });
    $('#sdc-cancelar').on('click', limpiarOtro);

    $('#sdc-guardar').on('click', function () {
        if (!$('#sdc-nombre').val().trim()) { Swal.fire({ type: 'warning', title: 'Escriba el nombre del certificado' }); return; }
        $.post('ajax/certificado.ajax.php', {
            accion: 'guardarSinDocumento', id: $('#sdc-id').val(), nombre: $('#sdc-nombre').val(),
            monto: $('#sdc-monto').val() || '0', activo: $('#sdc-activo').is(':checked') ? 1 : ''
        }, function (resp) {
            if (!resp.success) { Swal.fire({ type: 'error', title: 'No se guardó', text: resp.mensaje }); return; }
            Swal.fire({ type: 'success', title: resp.mensaje, timer: 1300, showConfirmButton: false });
            limpiarOtro();
            cargarOtros();
        }, 'json');
    });

    // Primero qué exige cada grado (las casillas se dibujan con las tablas)
    cargarRequisitosTit().always(function () {
        cargarCostos();
        cargarOtros();
    });
});
</script>

</body>
