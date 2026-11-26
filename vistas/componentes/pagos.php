<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
require_once 'modelos/pagos.modelo.php';

$pgProgramas = PagosModelo::ProgramasFiltro();
$pgSedes = array_values(array_unique(array_filter(array_column($pgProgramas, 'Sede'))));
sort($pgSedes);
?>

<style>
/* =========================================================
   PAGOS RECIBIDOS (prefijo pg-): todo lo cobrado en una sola pantalla.
   Un buscador, botones de periodo y de concepto, totales arriba y la tabla.
   Cada cambio vuelve a consultar solo (no hay botón "Consultar").
   Datos: ajax/pagos.ajax.php → modelos/pagos.modelo.php
   ========================================================= */
.pg {
    --pg-primario: #1e3c72;
    --pg-suave: #eef2f9;
    --pg-texto: #1f2937;
    --pg-texto-2: #64748b;
    --pg-borde: #e3e7ef;
    --pg-matricula: #1d4ed8;
    --pg-cuotas: #0f766e;
    --pg-clinica: #7c3aed;
    --pg-certificado: #b45309;
    max-width: 1240px;
    margin: 0 auto;
    color: var(--pg-texto);
}
.pg-card { background: #fff; border: 1px solid var(--pg-borde); border-radius: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, .05); margin-bottom: 16px; }
.pg-card-body { padding: 18px 20px; }

/* ---------- Buscador ---------- */
.pg-buscar { position: relative; }
.pg-buscar i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--pg-texto-2); font-size: 16px; }
.pg-buscar input { height: 50px; padding-left: 46px; padding-right: 44px; font-size: 15px; border-radius: 12px; border: 1px solid #d8dde8; }
.pg-buscar input:focus { border-color: var(--pg-primario); box-shadow: 0 0 0 3px rgba(30, 60, 114, .12); }
.pg-buscar .pg-limpiar-busqueda { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: 0; background: none;
    color: var(--pg-texto-2); width: 34px; height: 34px; border-radius: 8px; display: none; }
.pg-buscar.con-texto .pg-limpiar-busqueda { display: block; }

/* ---------- Botones de periodo y concepto ---------- */
.pg-fila { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; }
.pg-fila-titulo { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--pg-texto-2); min-width: 78px; }
.pg-chip { border: 1px solid #d8dde8; background: #fff; color: var(--pg-texto); border-radius: 999px; padding: 6px 14px;
    font-size: 13px; font-weight: 600; line-height: 1.3; cursor: pointer; }
.pg-chip.activo { background: var(--pg-primario); border-color: var(--pg-primario); color: #fff; }
.pg-chip:focus { outline: none; }
.pg-chip:focus-visible { outline: 2px solid var(--pg-primario); outline-offset: 2px; }
.pg-fechas { display: none; align-items: center; gap: 6px; }
.pg-fechas.visible { display: inline-flex; }
.pg-fechas input { height: 34px; border-radius: 8px; border: 1px solid #d8dde8; padding: 0 8px; font-size: 13px; }
.pg-mas { margin-left: auto; border: 0; background: none; color: var(--pg-primario); font-weight: 600; font-size: 13px; padding: 6px 4px; }
.pg-mas-filtros { display: none; grid-template-columns: 1fr 2fr; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--pg-borde); }
.pg-mas-filtros.visible { display: grid; }
.pg-mas-filtros label { font-size: 12px; font-weight: 600; color: var(--pg-texto-2); margin-bottom: 4px; }
.pg-mas-filtros select { height: 40px; border-radius: 8px; }

/* ---------- Totales ---------- */
.pg-totales { display: grid; grid-template-columns: 1.4fr repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.pg-total { background: #fff; border: 1px solid var(--pg-borde); border-radius: 14px; padding: 14px 16px; text-align: left; position: relative; cursor: pointer; }
.pg-total:focus { outline: none; }
.pg-total:focus-visible { outline: 2px solid var(--pg-primario); outline-offset: 2px; }
.pg-total::before { content: ''; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px; border-radius: 0 4px 4px 0; background: var(--pg-color, var(--pg-primario)); }
.pg-total span { display: block; font-size: 12px; font-weight: 700; color: var(--pg-texto-2); text-transform: uppercase; letter-spacing: .3px; }
.pg-total strong { display: block; font-size: 20px; font-weight: 700; margin-top: 4px; white-space: nowrap; font-variant-numeric: tabular-nums; }
.pg-total small { color: var(--pg-texto-2); font-size: 12px; }
.pg-total.principal { background: var(--pg-primario); border-color: var(--pg-primario); color: #fff; cursor: default; }
.pg-total.principal::before { display: none; }
.pg-total.principal span, .pg-total.principal small { color: rgba(255, 255, 255, .8); }
.pg-total.principal strong { font-size: 26px; }
.pg-total.elegido { box-shadow: 0 0 0 2px var(--pg-color); }
.pg-total[data-concepto="MATRICULA"] { --pg-color: var(--pg-matricula); }
.pg-total[data-concepto="CUOTAS"] { --pg-color: var(--pg-cuotas); }
.pg-total[data-concepto="CLINICA"] { --pg-color: var(--pg-clinica); }
.pg-total[data-concepto="CERTIFICADO"] { --pg-color: var(--pg-certificado); }

/* ---------- Tabla ---------- */
.pg-tabla-cab { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 20px; border-bottom: 1px solid var(--pg-borde); }
.pg-tabla-cab h3 { margin: 0; font-size: 15px; font-weight: 700; }
.pg-tabla-cab p { margin: 2px 0 0; font-size: 12.5px; color: var(--pg-texto-2); }
.pg-tabla { margin: 0; font-size: 13px; }
.pg-tabla thead th { background: #f8fafc; color: var(--pg-texto-2); font-size: 11.5px; text-transform: uppercase; letter-spacing: .3px; border-top: 0; border-bottom: 1px solid var(--pg-borde); white-space: nowrap; }
.pg-tabla td { vertical-align: middle; border-top: 1px solid #f0f2f7; }
.pg-tabla .pg-monto { text-align: right; font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
.pg-tabla tfoot td { background: #f8fafc; font-weight: 700; border-top: 2px solid var(--pg-borde); }
.pg-sub { display: block; color: var(--pg-texto-2); font-size: 12px; }
.pg-concepto { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; color: #fff; background: var(--pg-color); white-space: nowrap; }
.pg-concepto[data-c="MATRICULA"] { --pg-color: var(--pg-matricula); }
.pg-concepto[data-c="CUOTAS"] { --pg-color: var(--pg-cuotas); }
.pg-concepto[data-c="CLINICA"] { --pg-color: var(--pg-clinica); }
.pg-concepto[data-c="CERTIFICADO"] { --pg-color: var(--pg-certificado); }
.pg-vacio td { text-align: center; padding: 36px 20px !important; color: var(--pg-texto-2); }
.pg-vacio i { display: block; font-size: 28px; color: #c7cdd8; margin-bottom: 8px; }
.pg-aviso { margin: 12px 20px 0; padding: 10px 14px; border-radius: 10px; background: #fff8e6; color: #8a6116; font-size: 13px; }

@media (max-width: 991.98px) {
    .pg-totales { grid-template-columns: repeat(2, 1fr); }
    .pg-total.principal { grid-column: span 2; }
}
@media (max-width: 575.98px) {
    .pg-mas-filtros { grid-template-columns: 1fr; }
    .pg-fila-titulo { width: 100%; }
    .pg-mas { margin-left: 0; }
}
</style>

  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="panel"><img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" /></a>
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
      <?php $NavBar = new FuncionesControladores(); $NavBar->NavBarControlador(); ?>
      <button class="kt-aside-close" id="kt_aside_close_btn"><i class="la la-close"></i></button>
      <?php $Sidebar = new FuncionesControladores(); $Sidebar->SidebarControlador(); ?>

      <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

          <div class="kt-subheader kt-grid__item" id="kt_subheader">
            <div class="kt-container">
              <div class="kt-subheader__main">
                <h2>PAGOS RECIBIDOS</h2>
                <span class="kt-subheader__separator kt-hidden"></span>
                <div class="kt-subheader__breadcrumbs">
                  <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                  <span class="kt-subheader__breadcrumbs-separator"></span>
                  <h4>MATRÍCULA · CUOTAS · CLÍNICAS · CERTIFICADOS</h4>
                </div>
              </div>
              <div class="kt-subheader__toolbar"><div class="kt-subheader__wrapper"><div id="lafecha" style="font-size:13pt"></div></div></div>
            </div>
          </div>

          <div class="kt-container kt-grid__item kt-grid__item--fluid">
            <div class="pg">

              <!-- Filtros -->
              <div class="pg-card">
                <div class="pg-card-body">
                  <div class="pg-buscar" id="pgBuscarCaja">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" class="form-control" id="pgEstudiante" maxlength="80" autocomplete="off"
                           placeholder="Buscar estudiante por nombre, apellido o C.I. (opcional)" aria-label="Buscar estudiante">
                    <button type="button" class="pg-limpiar-busqueda" id="pgLimpiarBusqueda" title="Borrar búsqueda" aria-label="Borrar búsqueda"><i class="fas fa-times" style="position:static;transform:none;"></i></button>
                  </div>

                  <div class="pg-fila" role="group" aria-label="Periodo">
                    <span class="pg-fila-titulo">Periodo</span>
                    <button type="button" class="pg-chip" data-periodo="hoy">Hoy</button>
                    <button type="button" class="pg-chip" data-periodo="semana">Esta semana</button>
                    <button type="button" class="pg-chip activo" data-periodo="mes">Este mes</button>
                    <button type="button" class="pg-chip" data-periodo="mesAnterior">Mes anterior</button>
                    <button type="button" class="pg-chip" data-periodo="anio">Este año</button>
                    <button type="button" class="pg-chip" data-periodo="todo">Todo</button>
                    <button type="button" class="pg-chip" data-periodo="rango"><i class="far fa-calendar-alt"></i> Elegir fechas</button>
                    <span class="pg-fechas" id="pgFechas">
                      <input type="date" id="pgDesde" aria-label="Desde"> <span>a</span> <input type="date" id="pgHasta" aria-label="Hasta">
                    </span>
                  </div>

                  <div class="pg-fila" role="group" aria-label="Concepto">
                    <span class="pg-fila-titulo">Concepto</span>
                    <button type="button" class="pg-chip activo" data-concepto="">Todos</button>
                    <button type="button" class="pg-chip" data-concepto="MATRICULA">Matrícula</button>
                    <button type="button" class="pg-chip" data-concepto="CUOTAS">Cuotas</button>
                    <button type="button" class="pg-chip" data-concepto="CLINICA">Clínicas</button>
                    <button type="button" class="pg-chip" data-concepto="CERTIFICADO">Certificados</button>
                    <button type="button" class="pg-mas" id="pgMas" aria-expanded="false"><i class="fas fa-sliders-h"></i> Sede y programa</button>
                  </div>

                  <div class="pg-mas-filtros" id="pgMasFiltros">
                    <div>
                      <label for="pgSede">Sede</label>
                      <select class="form-control" id="pgSede">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($pgSedes as $sede): ?>
                        <option value="<?php echo htmlspecialchars($sede); ?>"><?php echo htmlspecialchars($sede); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div>
                      <label for="pgPrograma">Programa</label>
                      <select class="form-control" id="pgPrograma">
                        <option value="">Todos los programas</option>
                        <?php foreach ($pgProgramas as $prog): ?>
                        <option value="<?php echo (int)$prog['ProgramaID']; ?>" data-sede="<?php echo htmlspecialchars($prog['Sede'] ?? ''); ?>">
                          <?php echo htmlspecialchars(($prog['Codigo'] ? $prog['Codigo'] . ' · ' : '') . $prog['NombrePrograma']); ?>
                        </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Totales: el grande es el total; los otros filtran por concepto al pulsarlos -->
              <div class="pg-totales">
                <div class="pg-total principal">
                  <span>Total cobrado</span>
                  <strong id="pgTotal">Bs. 0,00</strong>
                  <small id="pgTotalN">0 pagos</small>
                </div>
                <button type="button" class="pg-total" data-concepto="MATRICULA" title="Ver solo matrícula"><span>Matrícula</span><strong data-total>Bs. 0,00</strong><small data-n>0 pagos</small></button>
                <button type="button" class="pg-total" data-concepto="CUOTAS" title="Ver solo cuotas"><span>Cuotas</span><strong data-total>Bs. 0,00</strong><small data-n>0 pagos</small></button>
                <button type="button" class="pg-total" data-concepto="CLINICA" title="Ver solo clínicas"><span>Clínicas</span><strong data-total>Bs. 0,00</strong><small data-n>0 pagos</small></button>
                <button type="button" class="pg-total" data-concepto="CERTIFICADO" title="Ver solo certificados"><span>Certificados</span><strong data-total>Bs. 0,00</strong><small data-n>0 pagos</small></button>
              </div>

              <!-- Lista -->
              <div class="pg-card">
                <div class="pg-tabla-cab">
                  <div>
                    <h3 id="pgTitulo">Pagos de este mes</h3>
                    <p id="pgResumen">Cargando…</p>
                  </div>
                  <div id="pgExportar"></div>
                </div>
                <div id="pgAviso"></div>
                <div class="table-responsive">
                  <table class="table pg-tabla" id="pgTabla" data-exportar="Pagos recibidos" data-exportar-botones="#pgExportar">
                    <thead>
                      <tr>
                        <th>Fecha</th>
                        <th>Estudiante</th>
                        <th>Concepto</th>
                        <th>Programa</th>
                        <th>N° voucher</th>
                        <th>Cuenta</th>
                        <th class="text-right">Monto (Bs.)</th>
                      </tr>
                    </thead>
                    <tbody id="pgCuerpo">
                      <tr class="pg-vacio"><td colspan="7"><i class="fas fa-spinner fa-spin"></i> Cargando pagos…</td></tr>
                    </tbody>
                    <tfoot id="pgPie"></tfoot>
                  </table>
                </div>
              </div>

            </div>
          </div>

          <?php $Footer = new FuncionesControladores(); $Footer->FooterResumidoControlador(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// jQuery y SweetAlert los carga la plantilla después de esta vista: se espera a que esté todo
document.addEventListener('DOMContentLoaded', function () {
    var $ = window.jQuery;

    var NOMBRES = { MATRICULA: 'Matrícula', CUOTAS: 'Cuotas', CLINICA: 'Clínicas', CERTIFICADO: 'Certificados' };
    // En cada fila es un solo pago: en singular
    var NOMBRE_FILA = { MATRICULA: 'Matrícula', CUOTAS: 'Cuota', CLINICA: 'Clínica', CERTIFICADO: 'Certificado' };
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    var estado = { periodo: 'mes', concepto: '' };
    var consultaActual = 0;   // para descartar respuestas viejas si se cambia rápido de filtro
    var esperaBusqueda = null;

    function esc(t) {
        return $('<div>').text(t == null ? '' : String(t)).html().replace(/"/g, '&quot;');
    }
    function bs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function numero(monto) {
        return (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function ymd(d) { return d.toLocaleDateString('en-CA'); }
    function fechaCorta(valor) {
        var p = String(valor || '').slice(0, 10).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : '-';
    }
    function nPagos(n) { return n + (n === 1 ? ' pago' : ' pagos'); }

    // Periodo elegido → desde/hasta (AAAA-MM-DD) y el título de la lista
    function rangoPeriodo() {
        var hoy = new Date();
        var a = hoy.getFullYear(), m = hoy.getMonth();
        switch (estado.periodo) {
            case 'hoy':
                return { desde: ymd(hoy), hasta: ymd(hoy), titulo: 'Pagos de hoy' };
            case 'semana': {
                var lunes = new Date(hoy);
                lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
                return { desde: ymd(lunes), hasta: ymd(hoy), titulo: 'Pagos de esta semana' };
            }
            case 'mesAnterior':
                return { desde: ymd(new Date(a, m - 1, 1)), hasta: ymd(new Date(a, m, 0)),
                    titulo: 'Pagos de ' + MESES[(m + 11) % 12] + ' ' + (m === 0 ? a - 1 : a) };
            case 'anio':
                return { desde: a + '-01-01', hasta: ymd(hoy), titulo: 'Pagos de ' + a };
            case 'todo':
                return { desde: '', hasta: '', titulo: 'Todos los pagos' };
            case 'rango': {
                var d = $('#pgDesde').val(), h = $('#pgHasta').val();
                var t = d && h ? 'Pagos del ' + fechaCorta(d) + ' al ' + fechaCorta(h)
                    : d ? 'Pagos desde el ' + fechaCorta(d) : h ? 'Pagos hasta el ' + fechaCorta(h) : 'Todos los pagos';
                return { desde: d, hasta: h, titulo: t };
            }
            default:
                return { desde: ymd(new Date(a, m, 1)), hasta: ymd(hoy), titulo: 'Pagos de ' + MESES[m] + ' ' + a };
        }
    }

    function consultar() {
        var rango = rangoPeriodo();
        var estudiante = $.trim($('#pgEstudiante').val());
        // Con 1 o 2 letras saldría medio sistema: se espera a que escriba más (el C.I. va con cualquier largo)
        if (estudiante && !/^\d+$/.test(estudiante) && estudiante.replace(/\s/g, '').length < 3) return;

        var titulo = rango.titulo + (estado.concepto ? ' · ' + NOMBRES[estado.concepto] : '');
        $('#pgTitulo').text(titulo);
        $('#pgTabla').attr('data-exportar', titulo + (estudiante ? ' · ' + estudiante.toUpperCase() : ''));
        $('#pgResumen').text('Cargando…');

        var id = ++consultaActual;
        $.ajax({
            url: 'ajax/pagos.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'listar',
                desde: rango.desde,
                hasta: rango.hasta,
                estudiante: estudiante,
                sede: $('#pgSede').val() || '',
                programaID: $('#pgPrograma').val() || ''
                // El concepto se filtra aquí: así las tarjetas muestran los 4 totales siempre
            }
        }).done(function (r) {
            if (id !== consultaActual) return;
            if (!r || !r.success) {
                mostrarError((r && r.mensaje) || 'No se pudieron cargar los pagos');
                return;
            }
            pintar(r.pagos || [], r.recortado, r.limite, estudiante, r.totales);
        }).fail(function () {
            if (id === consultaActual) mostrarError('No se pudo conectar con el servidor');
        });
    }

    function mostrarError(mensaje) {
        $('#pgResumen').text('');
        $('#pgPie').empty();
        $('#pgCuerpo').html('<tr class="pg-vacio"><td colspan="7"><i class="fas fa-exclamation-triangle"></i>' + esc(mensaje) + '</td></tr>');
    }

    function pintar(pagos, recortado, limite, estudiante, totalesServidor) {
        // Totales por concepto de TODO lo consultado (sin el filtro de concepto). Los calcula
        // el servidor: con más de <limite> pagos la lista se recorta, pero los totales no
        var totales = { MATRICULA: [0, 0], CUOTAS: [0, 0], CLINICA: [0, 0], CERTIFICADO: [0, 0] };
        if (totalesServidor) {
            Object.keys(totales).forEach(function (c) {
                if (totalesServidor[c]) totales[c] = [parseFloat(totalesServidor[c].monto) || 0, parseInt(totalesServidor[c].cantidad, 10) || 0];
            });
        } else {
            pagos.forEach(function (p) {
                if (!totales[p.Concepto]) return;
                totales[p.Concepto][0] += parseFloat(p.Monto) || 0;
                totales[p.Concepto][1]++;
            });
        }
        $('.pg-total[data-concepto]').each(function () {
            var t = totales[$(this).data('concepto')];
            $(this).find('[data-total]').text(bs(t[0]));
            $(this).find('[data-n]').text(nPagos(t[1]));
        });

        var lista = estado.concepto ? pagos.filter(function (p) { return p.Concepto === estado.concepto; }) : pagos;
        var conceptos = estado.concepto ? [estado.concepto] : Object.keys(totales);
        var total = conceptos.reduce(function (s, c) { return s + totales[c][0]; }, 0);
        var cantidad = conceptos.reduce(function (s, c) { return s + totales[c][1]; }, 0);
        // Total de las filas de la tabla (si la lista se recortó, es menos que el total)
        var totalLista = lista.reduce(function (s, p) { return s + (parseFloat(p.Monto) || 0); }, 0);
        $('#pgTotal').text(bs(total));
        $('#pgTotalN').text(nPagos(cantidad) + (estado.concepto ? ' de ' + NOMBRES[estado.concepto].toLowerCase() : ''));
        $('#pgResumen').text(nPagos(lista.length) + (estudiante ? ' · búsqueda: "' + estudiante + '"' : '') + ' · del más reciente al más antiguo');

        $('#pgAviso').html(recortado
            ? '<div class="pg-aviso"><i class="fas fa-info-circle"></i> Se muestran los ' + limite + ' pagos más recientes. Elija un periodo más corto o busque al estudiante para ver el resto.</div>'
            : '');

        if (!lista.length) {
            $('#pgPie').empty();
            $('#pgCuerpo').html('<tr class="pg-vacio"><td colspan="7"><i class="fas fa-receipt"></i>' +
                (estudiante ? 'No hay pagos de "' + esc(estudiante) + '" en este periodo.' : 'No hay pagos en este periodo.') +
                (estado.periodo !== 'todo' ? '<div class="mt-2"><button type="button" class="pg-chip" data-ver-todo>Ver todos los periodos</button></div>' : '') +
                '</td></tr>');
            return;
        }

        var html = '';
        lista.forEach(function (p) {
            var ci = p.Ci ? 'C.I. ' + p.Ci + (p.Complemento ? '-' + p.Complemento : '') + (p.Exp ? ' ' + p.Exp : '') : '';
            var cuenta = p.NumeroCuenta ? esc(p.NumeroCuenta) + (p.Banco ? '<span class="pg-sub">' + esc(p.Banco) + '</span>' : '') : '<span class="text-muted">-</span>';
            html += '<tr>' +
                '<td class="text-nowrap">' + fechaCorta(p.Fecha) + '</td>' +
                '<td><strong>' + esc(p.Estudiante) + '</strong>' + (ci ? '<span class="pg-sub">' + esc(ci) + '</span>' : '') + '</td>' +
                '<td><span class="pg-concepto" data-c="' + esc(p.Concepto) + '">' + esc(NOMBRE_FILA[p.Concepto] || p.Concepto) + '</span>' +
                    (p.Detalle ? '<span class="pg-sub">' + esc(p.Detalle) + '</span>' : '') + '</td>' +
                '<td>' + esc(p.NombrePrograma) + (p.CodigoPrograma ? '<span class="pg-sub">' + esc(p.CodigoPrograma) + '</span>' : '') + '</td>' +
                '<td class="text-nowrap">' + esc(p.Voucher || '-') + '</td>' +
                '<td>' + cuenta + '</td>' +
                '<td class="pg-monto">' + numero(p.Monto) + '</td>' +
                '</tr>';
        });
        $('#pgCuerpo').html(html);
        $('#pgPie').html('<tr><td colspan="6" class="text-right">' + (recortado ? 'Total de los ' + nPagos(lista.length) + ' mostrados' : 'Total (' + nPagos(lista.length) + ')') +
            '</td><td class="pg-monto">' + numero(totalLista) + '</td></tr>');
    }

    // ----- Eventos -----
    $('[data-periodo]').on('click', function () {
        estado.periodo = $(this).data('periodo');
        $('[data-periodo]').removeClass('activo');
        $(this).addClass('activo');
        $('#pgFechas').toggleClass('visible', estado.periodo === 'rango');
        if (estado.periodo === 'rango' && !$('#pgDesde').val()) {
            $('#pgDesde').focus();
            return;   // consulta al elegir la fecha
        }
        consultar();
    });
    $('#pgDesde, #pgHasta').on('change', consultar);

    function elegirConcepto(concepto) {
        estado.concepto = concepto;
        $('.pg-chip[data-concepto]').removeClass('activo').filter('[data-concepto="' + concepto + '"]').addClass('activo');
        $('.pg-total[data-concepto]').removeClass('elegido');
        if (concepto) $('.pg-total[data-concepto="' + concepto + '"]').addClass('elegido');
        consultar();
    }
    $('.pg-chip[data-concepto]').on('click', function () { elegirConcepto($(this).data('concepto')); });
    // Pulsar una tarjeta filtra por ese concepto; pulsarla otra vez vuelve a "Todos"
    $('.pg-total[data-concepto]').on('click', function () {
        var c = $(this).data('concepto');
        elegirConcepto(estado.concepto === c ? '' : c);
    });

    $('#pgEstudiante').on('input', function () {
        $('#pgBuscarCaja').toggleClass('con-texto', !!this.value);
        clearTimeout(esperaBusqueda);
        esperaBusqueda = setTimeout(consultar, 400);
    }).on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(esperaBusqueda); consultar(); }
    });
    $('#pgLimpiarBusqueda').on('click', function () {
        $('#pgEstudiante').val('').trigger('input').focus();
    });

    $('#pgMas').on('click', function () {
        var abierto = !$('#pgMasFiltros').hasClass('visible');
        $('#pgMasFiltros').toggleClass('visible', abierto);
        $(this).attr('aria-expanded', abierto);
    });
    // Al elegir sede, el programa solo muestra los de esa sede
    $('#pgSede').on('change', function () {
        var sede = $(this).val();
        $('#pgPrograma option[data-sede]').each(function () {
            this.hidden = !!sede && $(this).data('sede') !== sede;
        });
        if ($('#pgPrograma option:selected').prop('hidden')) $('#pgPrograma').val('');
        consultar();
    });
    $('#pgPrograma').on('change', consultar);

    $(document).on('click', '[data-ver-todo]', function () {
        $('[data-periodo="todo"]').trigger('click');
    });

    // Fecha del encabezado
    var hoyTexto = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    $('#lafecha').text(hoyTexto.charAt(0).toUpperCase() + hoyTexto.slice(1));

    consultar();
    $('#pgEstudiante').focus();
});
</script>
