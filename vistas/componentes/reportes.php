<?php
$Validar = new FuncionesControladores();
$Validar -> ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

$sedesDisponibles = ReportesModelos::ListaSedes();
$programas = ReportesModelos::ListaProgramas();
$cuentas = ReportesModelos::ListaCuentas();
$gradosDisponibles = ReportesModelos::ListaGrados();
$gestiones = ReportesModelos::ListaGestiones();

/**
 * Par de filtros Sede → Programa de una tarjeta (la sede acota los programas).
 * Con $programaObligatorio no hay "Todos los programas" y hay que elegir uno.
 */
function rpFiltroSedePrograma($id, $sedes, $programas, $programaObligatorio = false)
{
    return '<div class="rp-campos">' . rpCampoSede($id, $sedes) . rpCampoPrograma($id, $programas, $programaObligatorio) . '</div>';
}

function rpCampoSede($id, $sedes)
{
    $html = '<div class="rp-campo"><label for="sede_' . $id . '">Sede</label>'
        . '<select name="sede" id="sede_' . $id . '" class="form-control select2-uniforme"><option value="">Todas</option>';
    foreach ($sedes as $s) {
        $html .= '<option value="' . htmlspecialchars($s) . '">' . htmlspecialchars($s) . '</option>';
    }
    return $html . '</select></div>';
}

/**
 * Combo de programas. Cada opción lleva su sede, grado y gestión: al elegir cualquiera
 * de esos filtros en la misma tarjeta, el combo muestra solo los programas que coinciden.
 */
function rpCampoPrograma($id, $programas, $programaObligatorio = false)
{
    // Sin "required": con Select2 el select queda oculto y el navegador no puede avisar;
    // si falta, el PDF lo indica
    $html = '<div class="rp-campo rp-campo-ancho"><label for="programa_' . $id . '">Programa</label>'
        . '<select name="programa_id" id="programa_' . $id . '" class="form-control select2-uniforme">'
        . '<option value="">' . ($programaObligatorio ? 'Seleccione un programa' : 'Todos') . '</option>';
    foreach ($programas as $p) {
        $gestion = !empty($p['FechaInicio']) ? (int)substr($p['FechaInicio'], 0, 4) : '';
        $html .= '<option value="' . (int)$p['ProgramaID'] . '" data-sede="' . htmlspecialchars($p['Sede'])
            . '" data-grado="' . htmlspecialchars($p['GradoAcademico']) . '" data-gestion="' . $gestion . '">'
            . htmlspecialchars($p['NombrePrograma']) . ' (' . htmlspecialchars($p['Sede']) . ($gestion ? ' · ' . $gestion : '') . ')</option>';
    }
    return $html . '</select></div>';
}

/**
 * Filtros de la Lista general y del Personalizado, en el orden en que se piensa:
 * primero QUIÉNES (situación), luego DÓNDE y CUÁNDO (sede, grado, gestión) y al final
 * el programa, que ya viene acotado por los anteriores.
 */
function rpFiltrosEstudiantes($id, $sedes, $programas, $grados, $gestiones)
{
    $opciones = function ($valores, $todos) {
        $html = '<option value="">' . $todos . '</option>';
        foreach ($valores as $v) $html .= '<option value="' . htmlspecialchars($v) . '">' . htmlspecialchars($v) . '</option>';
        return $html;
    };
    return '<div class="rp-campos">'
        . '<div class="rp-campo"><label for="situacion_' . $id . '">Situación</label><select name="situacion" id="situacion_' . $id . '" class="form-control">'
        . '<option value="">Activos y abandonos</option><option value="ACTIVO">Solo activos</option><option value="ABANDONO">Solo abandonos</option>'
        . '<option value="SIN_INSCRIPCION">Sin ningún programa</option></select></div>'
        . rpCampoSede($id, $sedes)
        . '</div><div class="rp-campos">'
        . '<div class="rp-campo"><label for="grado_' . $id . '">Grado</label><select name="grado" id="grado_' . $id . '" class="form-control">' . $opciones($grados, 'Todos') . '</select></div>'
        . '<div class="rp-campo"><label for="gestion_' . $id . '" title="Año en que empezó el programa">Gestión (año)</label><select name="gestion" id="gestion_' . $id . '" class="form-control">' . $opciones($gestiones, 'Todas') . '</select></div>'
        . '</div><div class="rp-campos">' . rpCampoPrograma($id, $programas) . '</div>'
        . '<p class="rp-ayuda rp-aviso-sin" hidden><i class="fas fa-info-circle"></i> "Sin ningún programa": estudiantes registrados que aún no se inscribieron; los filtros de programa no se usan.</p>';
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

// Pie con el contador en vivo ("Saldrán N estudiantes") y el botón Limpiar filtros
function rpPieContador()
{
    return '<div class="rp-pie-info"><span class="rp-contador" aria-live="polite"></span>'
        . '<button type="button" class="rp-limpiar"><i class="fas fa-undo-alt"></i> Limpiar filtros</button></div>';
}

// Tarjeta de un reporte: el formulario abre el PDF en una pestaña nueva.
// Con $contador, muestra cuántos estudiantes saldrán antes de generar.
function rpTarjeta($tipo, $icono, $titulo, $descripcion, $filtros = '', $contador = false)
{
    return '<div class="rp-card"><form action="ajax/reportes.ajax.php" method="POST" target="_blank" class="rp-form' . ($contador ? ' rp-contable' : '') . '">'
        . '<input type="hidden" name="tipo_reporte" value="' . $tipo . '">'
        . '<div class="rp-card-cab"><span class="rp-icono"><i class="fas ' . $icono . '"></i></span>'
        . '<div><h4>' . $titulo . '</h4><p>' . $descripcion . '</p></div></div>'
        . ($filtros ? '<div class="rp-card-filtros">' . $filtros . '</div>' : '<div class="rp-card-filtros rp-sin-filtros">Sin filtros: incluye a todos.</div>')
        . '<div class="rp-card-pie">' . ($contador ? rpPieContador() : '') . '<button type="submit" class="btn rp-btn"><i class="fas fa-file-pdf"></i> Generar PDF</button></div>'
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
/* Contador en vivo y Limpiar filtros */
.rp-pie-info { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 6px 12px; margin-bottom: 10px; min-height: 20px; }
.rp-contador { font-size: 12.5px; color: var(--rp-texto); }
.rp-contador i { color: var(--rp-primario); margin-right: 3px; }
.rp-contador .rp-detalle { color: var(--rp-texto-2); }
.rp-contador.rp-cero, .rp-contador.rp-cero i { color: #b45309; font-weight: 600; }
.rp-limpiar, .rp-enlace { background: none; border: 0; padding: 0; color: var(--rp-primario); font-size: 12px; font-weight: 600; cursor: pointer; }
.rp-limpiar:hover, .rp-enlace:hover { text-decoration: underline; }
.rp-limpiar:focus-visible, .rp-enlace:focus-visible { outline: 2px solid var(--rp-primario); outline-offset: 2px; }
.rp-btn:disabled { background: #94a3b8; cursor: not-allowed; }
.rp-apagado { opacity: .5; }
.rp-campo .form-control:disabled { background: #f1f5f9; }
/* Reporte personalizado */
.rp-paso { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%;
    background: var(--rp-primario); color: #fff; font-size: 11px; margin-right: 4px; }
.rp-pz-todas { float: right; font-weight: 400; }
.rp-mas { margin: 0 0 10px; }
.rp-mas summary { cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--rp-primario); margin-bottom: 10px; }
.rp-mas summary small { font-weight: 400; color: var(--rp-texto-2); }
.rp-pz { display: block; }
.rp-pz-bloque { padding: 14px 16px 4px; border-bottom: 1px solid var(--rp-borde); }
.rp-pz-bloque h5 { margin: 0 0 10px; font-size: 13px; font-weight: 700; color: var(--rp-primario); }
.rp-pz-bloque h5 small { font-weight: 600; color: var(--rp-texto-2); margin-left: 6px; }
.rp-pz .rp-card-pie { padding-top: 14px; }
.rp-pz .rp-btn { width: auto; min-width: 220px; padding: 9px 24px; }
.rp-pz-plantillas { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 8px; }
.rp-chip { background: var(--rp-primario-suave); color: var(--rp-primario); border: 1px solid #cdd8ec; border-radius: 16px;
    padding: 4px 12px; font-size: 12.5px; font-weight: 600; }
.rp-chip:hover, .rp-chip:focus { background: var(--rp-primario); color: #fff; }
.rp-chip-peligro:not(:disabled):hover { background: #b91c1c; border-color: #b91c1c; }
.rp-chip:disabled { opacity: .5; cursor: not-allowed; }
.rp-pz-sep { flex: 0 0 1px; height: 22px; background: var(--rp-borde); margin: 0 4px; }
.rp-pz-guardadas { width: auto; min-width: 200px; height: 30px !important; padding: 2px 8px !important; font-size: 12.5px !important; border-radius: 6px; }
.rp-pz-columnas { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; margin-bottom: 8px; }
.rp-pz-grupo { border: 1px solid var(--rp-borde); border-radius: 6px; padding: 6px 10px 2px; margin: 0; }
.rp-pz-grupo legend { width: auto; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    color: var(--rp-texto-2); padding: 0 4px; margin: 0; }
.rp-pz-grupo .rp-check { margin-bottom: 4px; }
@media (max-width: 575px) {
    .rp-grid { grid-template-columns: 1fr; }
    .rp-pz .rp-btn { width: 100%; }
    .rp-tab { padding: 8px 10px; }
}
</style>

  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
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
        <button type="button" class="rp-tab" data-panel="rp-personalizado" role="tab"><i class="fas fa-sliders-h"></i> Personalizado</button>
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
              'Cada estudiante con su programa, sede y gestión. Los que abandonaron salen sombreados en rojo, con el módulo desde el que abandonaron.',
              rpFiltrosEstudiantes('estudiantes', $sedesDisponibles, $programas, $gradosDisponibles, $gestiones)
              . '<div class="rp-campos"><div class="rp-campo"><label for="orden_estudiantes">Presentación</label><select name="orden" id="orden_estudiantes" class="form-control">'
              . '<option value="apellido">Una sola lista, por apellido</option><option value="programa">Separado por programa</option></select></div></div>',
              true);
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

      <!-- PERSONALIZADO: el usuario arma su propio reporte (solo lectura, no cambia la BD) -->
      <section class="rp-panel" id="rp-personalizado" role="tabpanel">
        <p class="rp-panel-desc">Arme su propio reporte de estudiantes en 3 pasos. Si no sabe por dónde empezar, elija una plantilla: marca las columnas por usted.</p>
        <div class="rp-card rp-pz">
          <form action="ajax/reportes.ajax.php" method="POST" target="_blank" class="rp-form rp-contable" id="rpPersonalizado">
            <input type="hidden" name="tipo_reporte" value="personalizado">

            <div class="rp-pz-bloque">
              <h5><i class="fas fa-magic"></i> Empezar desde una plantilla <small>(opcional)</small></h5>
              <div class="rp-pz-plantillas">
                <button type="button" class="btn rp-chip" data-plantilla="contactos" title="C.I., nombre, celular, teléfono, correo, programa y situación">Contactos</button>
                <button type="button" class="btn rp-chip" data-plantilla="cobranza" title="Plan, cuotas, costo, pagado y saldo; ordenado por saldo">Cobranza</button>
                <button type="button" class="btn rp-chip" data-plantilla="academico" title="Módulos aprobados y promedio">Avance académico</button>
                <button type="button" class="btn rp-chip" data-plantilla="abandonos" title="Solo abandonos, con módulo y motivo">Abandonos</button>
                <button type="button" class="btn rp-chip" data-plantilla="formacion" title="Profesión, universidad y egreso">Formación</button>
                <span class="rp-pz-sep"></span>
                <select id="rpPzGuardadas" class="form-control rp-pz-guardadas" aria-label="Mis plantillas guardadas"><option value="">Mis plantillas guardadas…</option></select>
                <button type="button" class="btn rp-chip" id="rpPzGuardar" title="Guarda filtros, columnas y presentación con un nombre"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" class="btn rp-chip rp-chip-peligro" id="rpPzBorrar" disabled><i class="fas fa-trash-alt"></i> Borrar</button>
              </div>
              <p class="rp-ayuda">Las plantillas que guarde quedan solo en este navegador (no en la base de datos).</p>
            </div>

            <div class="rp-pz-bloque">
              <h5><span class="rp-paso">1</span> ¿Qué estudiantes?</h5>
              <?php
              echo rpFiltrosEstudiantes('pz', $sedesDisponibles, $programas, $gradosDisponibles, $gestiones);
              $opProfesion = '';
              foreach (ReportesModelos::ListaProfesionesConEstudiantes() as $p) {
                  $opProfesion .= '<option value="' . (int)$p['IdProfesion'] . '">' . htmlspecialchars($p['NombreProfesion']) . '</option>';
              }
              $opUniversidad = '';
              foreach (ReportesModelos::ListaUniversidadesConEstudiantes() as $u) {
                  $opUniversidad .= '<option value="' . (int)$u['IdUniversidad'] . '">' . htmlspecialchars($u['NombreUniversidad']) . '</option>';
              }
              ?>
              <details class="rp-mas" id="rpPzMas">
                <summary>Más filtros <small>(profesión, universidad, fecha de inscripción, saldo)</small></summary>
                <?php
                echo '<div class="rp-campos">'
                    . '<div class="rp-campo rp-campo-ancho"><label for="profesion_pz">Profesión</label><select name="profesion_id" id="profesion_pz" class="form-control select2-uniforme"><option value="">Todas</option>' . $opProfesion . '</select></div>'
                    . '<div class="rp-campo rp-campo-ancho"><label for="universidad_pz">Universidad</label><select name="universidad_id" id="universidad_pz" class="form-control select2-uniforme"><option value="">Todas</option>' . $opUniversidad . '</select></div>'
                    . '</div>';
                echo '<div class="rp-campos">'
                    . '<div class="rp-campo"><label for="desde_pz">Inscritos desde</label><input type="date" name="fecha_desde" id="desde_pz" class="form-control"></div>'
                    . '<div class="rp-campo"><label for="hasta_pz">Inscritos hasta</label><input type="date" name="fecha_hasta" id="hasta_pz" class="form-control"></div>'
                    . '</div>';
                ?>
                <label class="rp-check"><input type="checkbox" name="solo_con_saldo" value="1"> Solo los que deben (activos con saldo pendiente)</label>
              </details>
            </div>

            <div class="rp-pz-bloque">
              <h5><span class="rp-paso">2</span> ¿Qué datos mostrar? <small id="rpPzContador"></small>
                <span class="rp-pz-todas"><button type="button" class="rp-enlace" id="rpPzTodas">Marcar todas</button> · <button type="button" class="rp-enlace" id="rpPzNinguna">Quitar todas</button></span></h5>
              <div class="rp-pz-columnas">
                <?php
                $porGrupo = [];
                foreach (ReportesControladores::ColumnasPersonalizado() as $clave => $c) {
                    $porGrupo[$c[3]][$clave] = $c[0];
                }
                foreach ($porGrupo as $grupo => $cols) {
                    echo '<fieldset class="rp-pz-grupo"><legend>' . htmlspecialchars($grupo) . '</legend>';
                    foreach ($cols as $clave => $titulo) {
                        echo '<label class="rp-check"><input type="checkbox" name="columnas[]" value="' . htmlspecialchars($clave) . '"> ' . htmlspecialchars($titulo) . '</label>';
                    }
                    echo '</fieldset>';
                }
                ?>
              </div>
              <p class="rp-ayuda">Las columnas salen en este mismo orden. Con muchas, la hoja se pone horizontal y la letra se achica. Pagos: mismo cálculo que Saldos por estudiante (sin clínica).</p>
            </div>

            <div class="rp-pz-bloque">
              <h5><span class="rp-paso">3</span> ¿Cómo presentarlo?</h5>
              <div class="rp-campos"><div class="rp-campo rp-campo-ancho"><label for="titulo_pz">Título del reporte</label>
                <input type="text" name="titulo" id="titulo_pz" class="form-control" maxlength="90" placeholder="REPORTE PERSONALIZADO DE ESTUDIANTES"></div></div>
              <div class="rp-campos">
                <div class="rp-campo"><label for="agrupar_pz">Separar por</label><select name="agrupar" id="agrupar_pz" class="form-control">
                  <?php foreach (ReportesControladores::AgrupacionesPersonalizado() as $v => $t) echo '<option value="' . $v . '">' . ($v === '' ? 'No separar (una sola lista)' : $t) . '</option>'; ?>
                </select></div>
                <div class="rp-campo"><label for="orden_pz">Ordenar por</label><select name="orden_personalizado" id="orden_pz" class="form-control">
                  <?php foreach (ReportesControladores::OrdenesPersonalizado() as $v => $t) echo '<option value="' . $v . '">' . $t . '</option>'; ?>
                </select></div>
                <div class="rp-campo"><label for="orientacion_pz">Hoja</label><select name="orientacion" id="orientacion_pz" class="form-control">
                  <option value="">Automática (según columnas)</option><option value="P">Vertical</option><option value="L">Horizontal</option>
                </select></div>
              </div>
              <label class="rp-check"><input type="checkbox" name="marcar_abandonos" value="1" checked> Sombrear en rojo a los que abandonaron</label>
            </div>

            <div class="rp-card-pie"><?php echo rpPieContador(); ?><button type="submit" class="btn rp-btn"><i class="fas fa-file-pdf"></i> Generar PDF</button></div>
          </form>
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

// Reporte personalizado: plantillas de columnas, plantillas guardadas (solo en este
// navegador, nunca en la BD), contador de columnas y aviso si no eligió ninguna.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rpPersonalizado');
    if (!form) return;
    const CLAVE = 'reportes.personalizado.plantillas';
    const casillas = function () { return form.querySelectorAll('input[name="columnas[]"]'); };
    const contador = document.getElementById('rpPzContador');
    const guardadas = document.getElementById('rpPzGuardadas');
    const borrar = document.getElementById('rpPzBorrar');

    const PLANTILLAS = {
        contactos: { columnas: ['ci', 'nombre', 'celular', 'telefono', 'correo', 'programa', 'situacion'], agrupar: 'programa', orden: 'apellido' },
        cobranza:  { columnas: ['ci', 'nombre', 'celular', 'programa', 'plan', 'cuotas', 'costo', 'pagado', 'saldo'], agrupar: 'programa', orden: 'saldo' },
        academico: { columnas: ['ci', 'nombre', 'programa', 'gestion', 'aprobados', 'promedio', 'situacion'], agrupar: 'programa', orden: 'apellido' },
        abandonos: { columnas: ['ci', 'nombre', 'celular', 'programa', 'gestion', 'abandono_modulo', 'abandono_motivo', 'pagado', 'saldo'], agrupar: 'programa', orden: 'apellido', situacion: 'ABANDONO' },
        formacion: { columnas: ['ci', 'nombre', 'profesion', 'universidad', 'egreso', 'programa'], agrupar: 'profesion', orden: 'apellido' }
    };

    function contar() {
        const n = form.querySelectorAll('input[name="columnas[]"]:checked').length;
        contador.textContent = n ? '(' + n + ' elegida' + (n === 1 ? '' : 's') + ')' : '(ninguna elegida)';
    }
    function setSelect(id, valor) {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = valor || '';
        if (el.value !== (valor || '')) el.value = '';
        if (window.jQuery) jQuery(el).trigger('change'); else el.dispatchEvent(new Event('change'));
    }
    // Configuración actual del formulario (lo que se guarda como plantilla)
    function leer() {
        return {
            columnas: Array.from(form.querySelectorAll('input[name="columnas[]"]:checked')).map(function (c) { return c.value; }),
            titulo: form.titulo.value,
            agrupar: form.agrupar.value, orden: form.orden_personalizado.value, orientacion: form.orientacion.value,
            marcar: form.marcar_abandonos.checked, soloConSaldo: form.solo_con_saldo.checked,
            sede: form.sede.value, programa: form.programa_id.value, grado: form.grado.value, gestion: form.gestion.value,
            situacion: form.situacion.value, profesion: form.profesion_id.value, universidad: form.universidad_id.value
        };
    }
    function aplicar(p) {
        casillas().forEach(function (c) { c.checked = (p.columnas || []).indexOf(c.value) !== -1; });
        if ('titulo' in p) form.titulo.value = p.titulo || '';
        setSelect('agrupar_pz', p.agrupar);
        setSelect('orden_pz', p.orden || 'apellido');
        setSelect('orientacion_pz', p.orientacion);
        form.marcar_abandonos.checked = p.marcar !== false;
        form.solo_con_saldo.checked = !!p.soloConSaldo;
        setSelect('sede_pz', p.sede);         // primero la sede: arma la lista de programas
        setSelect('programa_pz', p.programa);
        setSelect('grado_pz', p.grado);
        setSelect('gestion_pz', p.gestion);
        setSelect('situacion_pz', p.situacion);
        setSelect('profesion_pz', p.profesion);
        setSelect('universidad_pz', p.universidad);
        // Si la plantilla usa algún filtro de "Más filtros", se abre para que se vea
        if (p.profesion || p.universidad || p.soloConSaldo) document.getElementById('rpPzMas').open = true;
        contar();
    }
    document.getElementById('rpPzTodas').addEventListener('click', function () {
        casillas().forEach(function (c) { c.checked = true; });
        contar();
    });
    document.getElementById('rpPzNinguna').addEventListener('click', function () {
        casillas().forEach(function (c) { c.checked = false; });
        contar();
    });
    function cargarGuardadas() {
        let lista = {};
        try { lista = JSON.parse(localStorage.getItem(CLAVE) || '{}') || {}; } catch (e) { lista = {}; }
        return lista;
    }
    function pintarGuardadas(seleccion) {
        const lista = cargarGuardadas();
        guardadas.innerHTML = '<option value="">Mis plantillas guardadas…</option>';
        Object.keys(lista).sort().forEach(function (nombre) {
            const o = document.createElement('option');
            o.value = nombre;
            o.textContent = nombre;
            guardadas.appendChild(o);
        });
        guardadas.value = seleccion && lista[seleccion] ? seleccion : '';
        borrar.disabled = !guardadas.value;
    }

    form.querySelectorAll('[data-plantilla]').forEach(function (b) {
        b.addEventListener('click', function () {
            const p = Object.assign({ situacion: '', soloConSaldo: false }, PLANTILLAS[b.dataset.plantilla]);
            // Las plantillas rápidas cambian columnas y presentación; los filtros de sede/programa se respetan
            const actual = leer();
            aplicar(Object.assign({}, actual, p, { titulo: actual.titulo }));
        });
    });
    guardadas.addEventListener('change', function () {
        borrar.disabled = !guardadas.value;
        const p = cargarGuardadas()[guardadas.value];
        if (p) aplicar(p);
    });
    document.getElementById('rpPzGuardar').addEventListener('click', function () {
        const config = leer();
        if (!config.columnas.length) { alerta('Elija al menos una columna antes de guardar.'); return; }
        const nombre = (window.prompt('Nombre de la plantilla:', guardadas.value || '') || '').trim().slice(0, 60);
        if (!nombre) return;
        const lista = cargarGuardadas();
        lista[nombre] = config;
        try {
            localStorage.setItem(CLAVE, JSON.stringify(lista));
        } catch (e) {
            alerta('Este navegador no permite guardar plantillas (modo privado o almacenamiento bloqueado).');
            return;
        }
        pintarGuardadas(nombre);
    });
    borrar.addEventListener('click', function () {
        const nombre = guardadas.value;
        if (!nombre || !window.confirm('¿Borrar la plantilla "' + nombre + '"?')) return;
        const lista = cargarGuardadas();
        delete lista[nombre];
        try { localStorage.setItem(CLAVE, JSON.stringify(lista)); } catch (e) {}
        pintarGuardadas('');
    });
    function alerta(texto) {
        if (window.Swal && Swal.fire) {
            const op = { title: 'Reporte personalizado', text: texto };
            op[parseInt(Swal.version, 10) < 9 ? 'type' : 'icon'] = 'info'; // SweetAlert2 v8 usa "type"
            Swal.fire(op);
        } else {
            window.alert(texto);
        }
    }

    form.addEventListener('change', function (e) { if (e.target.name === 'columnas[]') contar(); });
    form.addEventListener('submit', function (e) {
        if (!form.querySelector('input[name="columnas[]"]:checked')) {
            e.preventDefault();
            alerta('Elija al menos una columna para el reporte.');
        }
    });

    // Al abrir: la plantilla "Contactos" como punto de partida
    aplicar(Object.assign({ marcar: true }, PLANTILLAS.contactos));
    pintarGuardadas('');
});

// Select2 (la vista se incluye antes que jQuery en plantilla.php: esperar a que cargue)
document.addEventListener('DOMContentLoaded', function() {
    $('.select2-uniforme').select2({ width: '100%' });
});

// Cascada de filtros → Programa: en cada tarjeta, el combo de Programa solo muestra los
// programas que coinciden con la Sede, el Grado y la Gestión elegidos en esa misma
// tarjeta. Con Select2 no basta con ocultar <option> por CSS: se quitan/ponen del DOM
// (copias de las opciones originales, así no se reinyecta texto sin escapar).
document.addEventListener('DOMContentLoaded', function() {
    $('.rp-form').each(function() {
        const $form = $(this);
        const $programa = $form.find('select[name="programa_id"]');
        if (!$programa.length) return;
        const $vacia = $programa.find('option[value=""]').first().clone();
        const textoVacio = $vacia.text();
        const $opciones = $programa.find('option[value!=""]').clone();
        const filtros = ['sede', 'grado', 'gestion'];

        $form.on('change', 'select[name="sede"], select[name="grado"], select[name="gestion"]', function() {
            const elegidos = {};
            filtros.forEach(function(n) { elegidos[n] = String($form.find('select[name="' + n + '"]').val() || ''); });
            const anterior = $programa.val();
            const $visibles = $opciones.filter(function() {
                const $o = $(this);
                return filtros.every(function(n) { return !elegidos[n] || String($o.data(n)) === elegidos[n]; });
            }).clone();
            $vacia.text($visibles.length ? textoVacio : 'Ningún programa con esos filtros');
            $programa.empty().append($vacia.clone(), $visibles);
            $programa.val($visibles.filter('[value="' + anterior + '"]').length ? anterior : '');
            $programa.trigger('change');
        });
    });
});

// Lista general y Personalizado: contador en vivo, Limpiar filtros y filtros que no aplican
document.addEventListener('DOMContentLoaded', function() {
    $('.rp-contable').each(function() {
        const form = this;
        const $form = $(form);
        const $contador = $form.find('.rp-contador');
        const $boton = $form.find('button[type="submit"]');
        let temporizador = null;
        let ultima = 0;

        // "Sin ningún programa" deja sin efecto los filtros del programa; "Solo abandonos",
        // el de saldo (el saldo de un abandono no se cobra). Se desactivan para que se note.
        function ajustarDisponibles() {
            const situacion = $form.find('select[name="situacion"]').val();
            const sinPrograma = situacion === 'SIN_INSCRIPCION';
            $form.find('select[name="sede"], select[name="grado"], select[name="gestion"], select[name="programa_id"], input[name="fecha_desde"], input[name="fecha_hasta"]')
                .prop('disabled', sinPrograma);
            $form.find('input[name="solo_con_saldo"]').prop('disabled', sinPrograma || situacion === 'ABANDONO')
                .closest('label').toggleClass('rp-apagado', sinPrograma || situacion === 'ABANDONO');
            $form.find('.rp-aviso-sin').prop('hidden', !sinPrograma);
        }

        // Cuántos saldrán en el PDF (mismo filtrado que el PDF). Por GET para que, si se
        // corta internet, no salga el aviso de "datos que no se sabe si se guardaron".
        function contar() {
            clearTimeout(temporizador);
            temporizador = setTimeout(function() {
                const datos = new URLSearchParams(new FormData(form));
                datos.set('tipo_reporte', 'contar_estudiantes');
                datos.delete('columnas[]');
                const pedido = ++ultima;
                $contador.removeClass('rp-cero').html('<i class="fas fa-spinner fa-spin"></i> Contando…');
                fetch('ajax/reportes.ajax.php?' + datos.toString(), { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(c) {
                        if (pedido !== ultima) return; // llegó la respuesta de un cambio anterior
                        if (!c.registros) {
                            $contador.addClass('rp-cero').html('<i class="fas fa-exclamation-circle"></i> Ningún estudiante coincide con estos filtros');
                            $boton.prop('disabled', true).attr('title', 'Cambie o limpie los filtros');
                            return;
                        }
                        const partes = [];
                        if (c.activos) partes.push(c.activos + ' activo' + (c.activos === 1 ? '' : 's'));
                        if (c.abandonos) partes.push(c.abandonos + ' abandono' + (c.abandonos === 1 ? '' : 's'));
                        $contador.html('<i class="fas fa-users"></i> Saldrán <strong>' + c.estudiantes + '</strong> estudiante' + (c.estudiantes === 1 ? '' : 's')
                            + (c.registros !== c.estudiantes ? ' en ' + c.registros + ' inscripciones' : '')
                            + (partes.length ? ' <span class="rp-detalle">(' + partes.join(', ') + ')</span>' : ''));
                        $boton.prop('disabled', false).removeAttr('title');
                    })
                    .catch(function() {
                        if (pedido !== ultima) return;
                        $contador.text(''); // sin conexión: no se bloquea el botón
                        $boton.prop('disabled', false).removeAttr('title');
                    });
            }, 350);
        }

        const FILTROS = '[name="situacion"], [name="sede"], [name="grado"], [name="gestion"], [name="programa_id"], [name="profesion_id"], '
            + '[name="universidad_id"], [name="fecha_desde"], [name="fecha_hasta"], [name="solo_con_saldo"]';
        $form.on('change', FILTROS, function() {
            ajustarDisponibles();
            contar();
        });
        $form.find('.rp-limpiar').on('click', function() {
            $form.find('select[name="situacion"], select[name="sede"], select[name="grado"], select[name="gestion"], select[name="profesion_id"], select[name="universidad_id"]')
                .val('').trigger('change');
            $form.find('select[name="programa_id"]').val('').trigger('change');
            $form.find('input[name="fecha_desde"], input[name="fecha_hasta"]').val('');
            $form.find('input[name="solo_con_saldo"]').prop('checked', false);
            ajustarDisponibles();
            contar();
        });

        ajustarDisponibles();
        contar();
    });
});
</script>

</body>
