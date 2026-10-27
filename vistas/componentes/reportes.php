<?php
$Validar = new FuncionesControladores();
$Validar -> ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

$sedesDisponibles = ReportesModelos::ListaSedes();
$programas = ReportesModelos::ListaProgramas();
$cuentas = ReportesModelos::ListaCuentas();

/**
 * Par de filtros Sede → Programa de una tarjeta (la sede acota los programas).
 * Con $programaObligatorio no hay "Todos los programas" y hay que elegir uno.
 */
function rpFiltroSedePrograma($id, $sedes, $programas, $programaObligatorio = false)
{
    $html = '<div class="rp-campos">';
    $html .= '<div class="rp-campo"><label for="sede_' . $id . '">Sede</label>'
        . '<select name="sede" id="sede_' . $id . '" class="form-control select2-uniforme select-programa-por-sede" data-target="#programa_' . $id . '">'
        . '<option value="">Todas</option>';
    foreach ($sedes as $s) {
        $html .= '<option value="' . htmlspecialchars($s) . '">' . htmlspecialchars($s) . '</option>';
    }
    $html .= '</select></div>';
    $html .= '<div class="rp-campo rp-campo-ancho"><label for="programa_' . $id . '">Programa</label>'
        // Sin "required": con Select2 el select queda oculto y el navegador no puede avisar;
        // si falta, el PDF lo indica
        . '<select name="programa_id" id="programa_' . $id . '" class="form-control select2-uniforme">'
        . '<option value="">' . ($programaObligatorio ? 'Seleccione un programa' : 'Todos') . '</option>';
    foreach ($programas as $p) {
        $html .= '<option value="' . (int)$p['ProgramaID'] . '" data-sede="' . htmlspecialchars($p['Sede']) . '">'
            . htmlspecialchars($p['NombrePrograma']) . ' (' . htmlspecialchars($p['Sede']) . ')</option>';
    }
    $html .= '</select></div></div>';
    return $html;
}

function rpFiltroFechas($id, $ayuda = '')
{
    return '<div class="rp-campos">'
        . '<div class="rp-campo"><label for="desde_' . $id . '">Desde</label><input type="date" name="fecha_desde" id="desde_' . $id . '" class="form-control"></div>'
        . '<div class="rp-campo"><label for="hasta_' . $id . '">Hasta</label><input type="date" name="fecha_hasta" id="hasta_' . $id . '" class="form-control"></div>'
        . '</div>' . ($ayuda ? '<p class="rp-ayuda">' . $ayuda . '</p>' : '');
}

function rpFiltroCuenta($id, $cuentas)
{
    $html = '<div class="rp-campos">'
        . '<div class="rp-campo rp-campo-ancho"><label for="cuenta_' . $id . '">Cuenta</label>'
        . '<select name="numero_cuenta" id="cuenta_' . $id . '" class="form-control select2-uniforme"><option value="">Todas las cuentas</option>';
    foreach ($cuentas as $c) {
        $html .= '<option value="' . htmlspecialchars($c['NumeroCuenta']) . '">'
            . htmlspecialchars($c['Banco'] . ' - N° ' . $c['NumeroCuenta'] . ' (' . $c['Tipos'] . ')') . '</option>';
    }
    $html .= '</select></div>'
        . '<div class="rp-campo"><label for="concepto_' . $id . '">Concepto</label>'
        . '<select name="concepto" id="concepto_' . $id . '" class="form-control"><option value="">Todos</option>'
        . '<option value="MATRICULA">Matrícula</option><option value="CUOTAS">Cuotas</option><option value="CLINICA">Clínica</option></select></div>'
        . '</div>';
    return $html;
}

// Tarjeta de un reporte: el formulario abre el PDF en una pestaña nueva
function rpTarjeta($tipo, $icono, $titulo, $descripcion, $filtros = '')
{
    return '<div class="rp-card"><form action="ajax/reportes.ajax.php" method="POST" target="_blank" class="rp-form">'
        . '<input type="hidden" name="tipo_reporte" value="' . $tipo . '">'
        . '<div class="rp-card-cab"><span class="rp-icono"><i class="fas ' . $icono . '"></i></span>'
        . '<div><h4>' . $titulo . '</h4><p>' . $descripcion . '</p></div></div>'
        . ($filtros ? '<div class="rp-card-filtros">' . $filtros . '</div>' : '<div class="rp-card-filtros rp-sin-filtros">Sin filtros: incluye a todos.</div>')
        . '<div class="rp-card-pie"><button type="submit" class="btn rp-btn"><i class="fas fa-file-pdf"></i> Generar PDF</button></div>'
        . '</form></div>';
}
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

<style>
/* =========================================================
   REPORTES PDF (prefijo rp-): categorías en pestañas y una
   tarjeta por reporte con sus filtros.
   ========================================================= */
.rp {
    --rp-primario: #1e3c72;
    --rp-primario-suave: #eef2f9;
    --rp-texto: #1f2937;
    --rp-texto-2: #64748b;
    --rp-borde: #e2e8f0;
    color: var(--rp-texto);
}
.rp-intro { background: #fff; border: 1px solid var(--rp-borde); border-radius: 8px; padding: 14px 18px; margin-bottom: 16px; }
.rp-intro p { margin: 0; color: var(--rp-texto-2); font-size: 13px; }
.rp-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; border-bottom: 2px solid var(--rp-borde); }
.rp-tab { background: none; border: 0; border-bottom: 3px solid transparent; margin-bottom: -2px; padding: 10px 16px;
    font-weight: 600; font-size: 13px; color: var(--rp-texto-2); cursor: pointer; }
.rp-tab:hover { color: var(--rp-primario); }
.rp-tab.activo { color: var(--rp-primario); border-bottom-color: var(--rp-primario); }
.rp-tab .rp-n { display: inline-block; min-width: 20px; padding: 0 6px; margin-left: 6px; border-radius: 10px;
    background: var(--rp-primario-suave); color: var(--rp-primario); font-size: 11px; }
.rp-tab:focus { outline: none; }
.rp-tab:focus-visible { outline: 2px solid var(--rp-primario); outline-offset: 2px; }
.rp-panel { display: none; }
.rp-panel.activo { display: block; }
.rp-panel-desc { color: var(--rp-texto-2); font-size: 13px; margin: 0 0 12px; }
.rp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.rp-card { background: #fff; border: 1px solid var(--rp-borde); border-radius: 8px; display: flex; }
.rp-form { display: flex; flex-direction: column; width: 100%; }
.rp-card-cab { display: flex; gap: 12px; padding: 16px 16px 10px; }
.rp-card-cab h4 { margin: 0 0 4px; font-size: 15px; font-weight: 700; color: var(--rp-primario); }
.rp-card-cab p { margin: 0; font-size: 12.5px; color: var(--rp-texto-2); line-height: 1.45; }
.rp-icono { flex: 0 0 38px; height: 38px; border-radius: 8px; background: var(--rp-primario-suave); color: var(--rp-primario);
    display: flex; align-items: center; justify-content: center; font-size: 16px; }
.rp-card-filtros { padding: 4px 16px 0; flex: 1; }
.rp-sin-filtros { font-size: 12px; color: var(--rp-texto-2); font-style: italic; }
.rp-campos { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
.rp-campo { flex: 1 1 120px; min-width: 0; }
.rp-campo-ancho { flex: 2 1 180px; }
.rp-campo label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: var(--rp-texto-2); margin-bottom: 3px; }
.rp-campo .form-control { height: 34px; padding: 4px 10px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; }
.rp-campo .form-control:focus { border-color: var(--rp-primario); box-shadow: 0 0 0 2px rgba(30, 60, 114, .15); }
.rp-check { display: flex; align-items: center; gap: 6px; font-size: 13px; margin-bottom: 10px; cursor: pointer; }
.rp-ayuda { font-size: 11.5px; color: var(--rp-texto-2); margin: -4px 0 10px; }
.rp-card-pie { padding: 0 16px 16px; }
.rp-btn { width: 100%; background: var(--rp-primario); color: #fff; border: 0; border-radius: 6px; padding: 9px; font-weight: 600; }
.rp-btn:hover, .rp-btn:focus { background: #16305c; color: #fff; }
.rp .select2-container .select2-selection--single { height: 34px; border: 1px solid #cbd5e1; border-radius: 6px; }
.rp .select2-container .select2-selection__rendered { line-height: 32px; font-size: 13px; }
@media (max-width: 575px) {
    .rp-grid { grid-template-columns: 1fr; }
    .rp-tab { padding: 8px 10px; }
}
</style>

  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
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
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>GENERACIÓN DE REPORTES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>REPORTES PDF</h3>
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

<div class="app-content content kt-container">
  <div class="content-wrapper">
    <div class="content-body rp">

      <div class="rp-intro">
        <p><i class="fas fa-info-circle"></i> Elija una categoría, ajuste los filtros y pulse <strong>Generar PDF</strong>: el reporte se abre en una pestaña nueva con los datos al momento. Los filtros vacíos incluyen todo.</p>
      </div>

      <div class="rp-tabs" role="tablist">
        <button type="button" class="rp-tab activo" data-panel="rp-estudiantes" role="tab"><i class="fas fa-user-graduate"></i> Por estudiante <span class="rp-n">3</span></button>
        <button type="button" class="rp-tab" data-panel="rp-programas" role="tab"><i class="fas fa-graduation-cap"></i> Por programa <span class="rp-n">5</span></button>
        <button type="button" class="rp-tab" data-panel="rp-cuentas" role="tab"><i class="fas fa-university"></i> Por cuenta <span class="rp-n">4</span></button>
      </div>

      <!-- POR ESTUDIANTE -->
      <section class="rp-panel activo" id="rp-estudiantes" role="tabpanel">
        <p class="rp-panel-desc">Listas de estudiantes y la situación de pagos de cada uno.</p>
        <div class="rp-grid">
          <?php
          echo rpTarjeta('estado_cuenta', 'fa-file-invoice-dollar', 'Estado de cuenta',
              'De un estudiante: matrícula, cuotas y clínicas de cada programa, con lo pagado y el saldo pendiente.',
              '<div class="rp-campos"><div class="rp-campo rp-campo-ancho"><label for="ci_estado">C.I. del estudiante</label>'
              . '<input type="text" name="ci" id="ci_estado" class="form-control" placeholder="Ej.: 7456178" inputmode="numeric" required></div></div>');
          echo rpTarjeta('estudiantes', 'fa-users', 'Lista general de estudiantes',
              'Todos los estudiantes activos con C.I., profesión y celular.');
          echo rpTarjeta('por_programa', 'fa-list-ol', 'Estudiantes por programa',
              'Inscritos de cada programa con su fecha de inscripción y datos de contacto.',
              rpFiltroSedePrograma('por_programa', $sedesDisponibles, $programas));
          ?>
        </div>
      </section>

      <!-- POR PROGRAMA -->
      <section class="rp-panel" id="rp-programas" role="tabpanel">
        <p class="rp-panel-desc">Cobranza, avance académico y notas de cada programa.</p>
        <div class="rp-grid">
          <?php
          echo rpTarjeta('saldos', 'fa-balance-scale', 'Saldos por estudiante',
              'Costo, pagado y saldo de cada estudiante, programa por programa. Sirve para el seguimiento de cobranza.',
              rpFiltroSedePrograma('saldos', $sedesDisponibles, $programas)
              . '<label class="rp-check"><input type="checkbox" name="solo_con_saldo" value="1"> Solo estudiantes con saldo pendiente</label>');
          $opcionesGrado = '';
          foreach (ReportesModelos::ListaGrados() as $g) {
              $opcionesGrado .= '<option value="' . htmlspecialchars($g) . '">' . htmlspecialchars($g) . '</option>';
          }
          echo rpTarjeta('abandonos', 'fa-user-times', 'Abandonos',
              'Lo que representaban los estudiantes que abandonaron: costo, lo que pagaron y lo que quedó sin cobrar. Resumen por sede, grado y programa, y detalle por estudiante con el módulo y el motivo.',
              rpFiltroSedePrograma('abandonos', $sedesDisponibles, $programas)
              . '<div class="rp-campos">'
              . '<div class="rp-campo"><label for="grado_abandonos">Grado</label><select name="grado" id="grado_abandonos" class="form-control"><option value="">Todos</option>' . $opcionesGrado . '</select></div>'
              . '<div class="rp-campo"><label for="ci_abandonos">C.I. del estudiante</label><input type="text" name="ci" id="ci_abandonos" class="form-control" placeholder="Opcional" inputmode="numeric"></div>'
              . '</div>');
          echo rpTarjeta('notas_programa', 'fa-table', 'Consolidado de notas',
              'Notas de todos los estudiantes en todos los módulos de un programa, con promedio y módulos aprobados.',
              rpFiltroSedePrograma('notas', $sedesDisponibles, $programas, true));
          echo rpTarjeta('ejecutivo', 'fa-chart-line', 'Reporte ejecutivo',
              'Matrícula, % de cobranza, % de aprobación, módulos validados y docentes. Resalta los programas con alertas.',
              rpFiltroSedePrograma('ejecutivo', $sedesDisponibles, $programas));
          echo rpTarjeta('modulos', 'fa-book', 'Módulos',
              'Estado de cada módulo, docente asignado y cuántos aprobaron o reprobaron.',
              rpFiltroSedePrograma('modulos', $sedesDisponibles, $programas));
          ?>
        </div>
      </section>

      <!-- POR CUENTA -->
      <section class="rp-panel" id="rp-cuentas" role="tabpanel">
        <p class="rp-panel-desc">Lo que ingresó a cada cuenta bancaria. Las fechas son las de pago del voucher.</p>
        <div class="rp-grid">
          <?php
          echo rpTarjeta('recaudacion', 'fa-money-check-alt', 'Recaudación por cuenta',
              'Total recaudado y por cobrar en cada cuenta, con subtotales de matrícula, cuotas y clínica.',
              rpFiltroSedePrograma('recaudacion', $sedesDisponibles, $programas)
              . rpFiltroFechas('recaudacion', 'Las fechas filtran solo lo recaudado; lo por cobrar es lo pendiente a hoy.'));
          echo rpTarjeta('cuentas_programa', 'fa-landmark', 'Cuentas del programa',
              'Las cuentas de cada programa (matrícula, cuotas y clínica) con lo recaudado y por cobrar, y la lista de pagos que entró a cada una.',
              rpFiltroSedePrograma('cuentas_programa', $sedesDisponibles, $programas)
              . rpFiltroFechas('cuentas_programa', 'Las fechas filtran solo lo recaudado; lo por cobrar es lo pendiente a hoy.'));
          echo rpTarjeta('movimientos', 'fa-exchange-alt', 'Movimientos por cuenta',
              'Cada pago recibido, uno por uno: fecha, estudiante, programa, voucher y monto. Útil para conciliar con el extracto del banco.',
              rpFiltroCuenta('movimientos', $cuentas)
              . rpFiltroSedePrograma('movimientos', $sedesDisponibles, $programas)
              . rpFiltroFechas('movimientos'));
          echo rpTarjeta('ingresos_mes', 'fa-calendar-alt', 'Ingresos por mes',
              'Lo recaudado mes a mes, separado en matrícula, cuotas y clínica, con el promedio mensual.',
              rpFiltroCuenta('ingresos', $cuentas)
              . rpFiltroSedePrograma('ingresos', $sedesDisponibles, $programas)
              . rpFiltroFechas('ingresos'));
          ?>
        </div>
      </section>

    </div>
  </div>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterResumidoControlador();
?>

<script>
// Mostrar fecha actual
function actualizarFecha() {
    const opciones = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    document.getElementById('lafecha').textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
}
actualizarFecha();
setInterval(actualizarFecha, 60000);

// Pestañas de categorías; se recuerda la última elegida en este navegador
(function () {
    const tabs = document.querySelectorAll('.rp-tab');
    function mostrar(idPanel) {
        tabs.forEach(function (t) { t.classList.toggle('activo', t.dataset.panel === idPanel); });
        document.querySelectorAll('.rp-panel').forEach(function (p) { p.classList.toggle('activo', p.id === idPanel); });
    }
    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            mostrar(t.dataset.panel);
            try { localStorage.setItem('reportes.categoria', t.dataset.panel); } catch (e) {}
        });
    });
    try {
        const guardada = localStorage.getItem('reportes.categoria');
        if (guardada && document.getElementById(guardada)) mostrar(guardada);
    } catch (e) {}
})();

// Select2 (la vista se incluye antes que jQuery en plantilla.php: esperar a que cargue)
document.addEventListener('DOMContentLoaded', function() {
    $('.select2-uniforme').select2({ width: '100%' });
});

// Cascada Sede -> Programa: al elegir una sede, el combo de Programa de esa misma
// tarjeta solo muestra los programas de esa sede. Con Select2 no basta con ocultar
// <option> por CSS, hay que quitarlas/ponerlas del DOM.
document.addEventListener('DOMContentLoaded', function() {
    $('.select-programa-por-sede').each(function() {
        const $programa = $($(this).data('target'));
        const cache = [];
        $programa.find('option[value!=""]').each(function() {
            cache.push({
                value: $(this).val(),
                sede: $(this).data('sede') || '',
                // .html() conserva el escape que ya aplicó el servidor;
                // .text() lo decodificaría y reinyectarlo sería un riesgo XSS.
                html: $(this).html()
            });
        });
        $(this).data('programasCache', cache);
        $programa.data('textoVacio', $programa.find('option[value=""]').text());
    });

    $('.select-programa-por-sede').on('change', function() {
        const sede = $(this).val();
        const $programa = $($(this).data('target'));
        const programaSeleccionado = $programa.val();
        const cache = $(this).data('programasCache') || [];

        let opciones = '<option value="">' + $('<div>').text($programa.data('textoVacio')).html() + '</option>';
        cache.forEach(function(p) {
            if (!sede || p.sede === sede) {
                opciones += '<option value="' + p.value + '" data-sede="' + p.sede + '">' + p.html + '</option>';
            }
        });
        $programa.html(opciones);

        if (programaSeleccionado && $programa.find('option[value="' + programaSeleccionado + '"]').length) {
            $programa.val(programaSeleccionado);
        }
        $programa.trigger('change');
    });
});
</script>

</body>
