<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
require_once 'modelos/certificado.modelo.php'; // tipos de certificado del filtro
?>

<style>
/* =========================================================
   REGISTRO DE DOCUMENTOS (prefijo rd-). Dos pestañas:
   - Por estudiante: se busca un estudiante y se ven TODAS sus emisiones
     (también varias del mismo certificado), su resumen y su trabajo de grado.
   - Reporte de pagos: certificados emitidos en un periodo, con total e impresión.
   Al entrar no se lista nada: la tabla aparece recién al buscar o consultar.
   ========================================================= */
/* Colores de la pantalla y de la ventana de edición (que está fuera de .rd) */
.rd, .rd-ventana-fondo {
    --rd-primario: #1e3c72;
    --rd-primario-suave: #eef2f9;
    --rd-texto: #1f2937;
    --rd-texto-2: #64748b;
    --rd-borde: #e3e7ef;
    --rd-ok: #2f855a;
    --rd-ok-suave: #e7f6ee;
    --rd-espera: #b7791f;
    --rd-espera-suave: #fff6e0;
}
.rd {
    max-width: 1100px;
    margin: 0 auto;
}

/* ---------- Pestañas y botón "Emitir certificado" ---------- */
.rd-barra { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.rd-tabs { display: inline-flex; background: #fff; border: 1px solid var(--rd-borde); border-radius: 8px; padding: 4px; gap: 4px; }
.rd-tab {
    display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 18px;
    border: 0; border-radius: 6px; background: transparent; color: var(--rd-texto-2);
    font-size: 13px; font-weight: 600; cursor: pointer;
}
.rd-tab:hover { color: var(--rd-primario); }
.rd-tab.activa { background: var(--rd-primario); color: #fff; }
.rd-panel[hidden] { display: none; }

.rd-ir-certificado { position: relative; }
.rd-ir-certificado summary { list-style: none; }
.rd-ir-certificado summary::-webkit-details-marker { display: none; }
.rd-btn-ir {
    display: inline-flex; align-items: center; gap: 8px; height: 46px; padding: 0 18px;
    border: 1px solid var(--rd-primario); border-radius: 6px; color: var(--rd-primario);
    font-weight: 600; font-size: 13px; cursor: pointer; user-select: none; background: #fff;
}
.rd-btn-ir:hover, .rd-ir-certificado[open] .rd-btn-ir { background: var(--rd-primario); color: #fff; }
.rd-ir-flecha { font-size: 10px; transition: transform .15s; }
.rd-ir-certificado[open] .rd-ir-flecha { transform: rotate(180deg); }
.rd-ir-menu {
    position: absolute; right: 0; top: calc(100% + 6px); z-index: 30; min-width: 300px; padding: 6px;
    background: #fff; border: 1px solid var(--rd-borde); border-radius: 6px; box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
}
.rd-ir-menu a { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 6px; color: var(--rd-texto); font-size: 13px; text-decoration: none; }
.rd-ir-menu a:hover { background: var(--rd-primario-suave); color: var(--rd-primario); text-decoration: none; }
.rd-ir-menu a i { color: var(--rd-primario); width: 16px; }

/* ---------- Tarjetas ---------- */
.rd-card {
    background: #fff;
    border: 1px solid var(--rd-borde);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    margin-bottom: 16px;
}
.rd-card-head {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
    gap: 10px 16px; padding: 16px 22px; border-bottom: 1px solid var(--rd-borde);
}
.rd-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--rd-texto); }
.rd-card-head h3 i { color: var(--rd-primario); margin-right: 6px; }
.rd-card-body { padding: 20px 22px; }

.rd-label { display: block; font-size: 12px; font-weight: 600; color: var(--rd-texto); margin-bottom: 6px; }
.rd .form-control, .rd-ventana .form-control { border: 1px solid #d8dde8; border-radius: 6px; }
.rd .form-control:focus, .rd-ventana .form-control:focus { border-color: var(--rd-primario); box-shadow: 0 0 0 3px rgba(30, 60, 114, .12); }

.rd-btn-primario {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 46px; padding: 0 22px;
    background: var(--rd-primario); border: 1px solid var(--rd-primario); color: #fff;
    border-radius: 6px; font-weight: 600; white-space: nowrap;
}
.rd-btn-primario:hover, .rd-btn-primario:focus { background: #16305c; border-color: #16305c; color: #fff; box-shadow: 0 4px 12px rgba(30, 60, 114, .25); }
.rd-btn-secundario {
    display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 16px;
    background: #fff; border: 1px solid var(--rd-borde); color: var(--rd-texto); border-radius: 6px; font-weight: 600; font-size: 13px; white-space: nowrap;
}
.rd-btn-secundario:hover { border-color: var(--rd-primario); color: var(--rd-primario); }

/* ---------- Buscador ---------- */
.rd-buscar { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.rd-buscar-campo { position: relative; flex: 1 1 320px; }
.rd-buscar-campo i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
.rd-buscar-campo .form-control { height: 46px; padding-left: 42px; font-size: 14px; }
.rd-buscar-programa { flex: 1 1 100%; }
.rd-buscar-programa select.form-control { height: 46px; }
.rd-aviso { margin: 12px 0 0; padding: 10px 14px; background: #fff8e6; border: 1px solid #f1d58a; border-radius: 6px; font-size: 13px; color: #6b4e0e; }
.rd-aviso i { color: #d69e2e; margin-right: 6px; }

/* ---------- Estado vacío ---------- */
.rd-vacio { text-align: center; padding: 48px 20px; color: var(--rd-texto-2); }
.rd-vacio i { display: block; font-size: 34px; color: #c3cad8; margin-bottom: 12px; }
.rd-vacio p { margin: 0; font-size: 14px; }

/* ---------- Ficha del estudiante y resumen por certificado ---------- */
.rd-alumno { display: flex; align-items: center; gap: 14px; padding: 18px 22px; }
.rd-avatar {
    width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
    background: var(--rd-primario-suave); color: var(--rd-primario); font-weight: 700; font-size: 15px;
}
.rd-alumno-nombre { font-size: 16px; font-weight: 700; color: var(--rd-texto); line-height: 1.25; }
.rd-alumno-sub { font-size: 12.5px; color: var(--rd-texto-2); margin-top: 2px; }
/* Certificados para la titulación: lista de control (emitido / pendiente) */
.rd-titulacion { padding: 16px 22px 18px; border-top: 1px solid var(--rd-borde); }
.rd-tit-cabeza { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.rd-tit-titulo { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--rd-texto-2); }
.rd-tit-cuenta { font-size: 13px; font-weight: 700; color: var(--rd-texto); white-space: nowrap; }
.rd-tit-barra { height: 4px; background: #e9edf3; border-radius: 2px; overflow: hidden; margin-bottom: 12px; }
.rd-tit-barra span { display: block; height: 100%; background: var(--rd-primario); }
.rd-titulacion.completo .rd-tit-barra span { background: var(--rd-ok); }
.rd-tit-lista { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2px 24px; margin: 0; padding: 0; list-style: none; }
/* En mayúsculas: el catálogo mezcla "Medio Magnético" con "PRO BIBLIOTECA..." */
.rd-tit-lista li { display: flex; align-items: baseline; gap: 8px; padding: 5px 0; font-size: 12px; color: var(--rd-texto); border-bottom: 1px solid #f1f4f8; text-transform: uppercase; }
.rd-tit-lista li i { width: 14px; flex-shrink: 0; text-align: center; }
.rd-tit-lista .rd-tit-hecho i { color: var(--rd-ok); }
.rd-tit-lista .rd-tit-falta { color: var(--rd-texto-2); }
.rd-tit-lista .rd-tit-falta i { color: #c3cad8; }
.rd-tit-ok { margin: 10px 0 0; font-size: 12.5px; color: var(--rd-ok); font-weight: 600; }
@media (max-width: 767.98px) { .rd-tit-lista { grid-template-columns: 1fr; } }

/* ---------- Tablas ---------- */
.rd-tabla-marco { border: 1px solid var(--rd-borde); border-radius: 6px; overflow: hidden; }
.tabla-log { margin: 0; font-size: 13px; }
.tabla-log thead th {
    background: #f8fafc; color: var(--rd-texto-2); font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .4px; border-top: 0; border-bottom: 1px solid var(--rd-borde); white-space: nowrap;
}
.tabla-log td { vertical-align: middle; border-color: #eef1f6; color: var(--rd-texto); }
.tabla-log .rd-monto { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-weight: 600; }
.tabla-log tfoot td { background: #f7f8fa; font-weight: 700; border-top: 2px solid #e2e6ee; }
.tabla-log .rd-sin-filas td { text-align: center; color: var(--rd-texto-2); padding: 34px 12px; }
.rd-tipo { font-weight: 600; color: var(--rd-texto); }
.rd-estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.rd-estado.pendiente { background: var(--rd-espera-suave); color: var(--rd-espera); }
.rd-estado.aprobado { background: var(--rd-ok-suave); color: var(--rd-ok); }
.rd-estado.registrado { background: #eef1f5; color: #475569; }

/* Acciones de cada fila */
.rd-acciones { display: flex; justify-content: flex-end; gap: 6px; }
.rd-btn-mini {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px; height: 30px; padding: 0 10px; border-radius: 8px;
    font-size: 12px; font-weight: 600; border: 1px solid var(--rd-borde); background: #fff; color: var(--rd-texto); cursor: pointer; white-space: nowrap;
}
.rd-btn-mini:hover { border-color: var(--rd-primario); color: var(--rd-primario); }
.rd-btn-mini.icono { width: 30px; padding: 0; }
.rd-btn-mini.aprobar { border-color: var(--rd-ok); color: var(--rd-ok); }
.rd-btn-mini.aprobar:hover { background: var(--rd-ok); color: #fff; }
.rd-btn-mini.borrar:hover { border-color: #c53030; background: #c53030; color: #fff; }
.rd-btn-mini[disabled] { opacity: .5; cursor: not-allowed; }

/* Recién generado (al llegar desde la pantalla del certificado): un destello al aparecer */
.tabla-log tr.rd-recien td { animation: rd-destello 2.4s ease-out; }
@keyframes rd-destello { 0%, 35% { background: #fff3c4; } 100% { background: transparent; } }

/* Casillas del recibo */
.tabla-log .rd-col-check { width: 36px; text-align: center; padding-left: 10px; padding-right: 4px; }
.tabla-log .rd-col-check input { width: 16px; height: 16px; cursor: pointer; accent-color: var(--rd-primario); vertical-align: middle; }
/* Últimos emitidos / Anteriores */
.rd-subtabs { display: flex; gap: 22px; margin-bottom: 14px; border-bottom: 1px solid var(--rd-borde); }
.rd-subtab {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 2px; margin-bottom: -1px;
    border: 0; border-bottom: 2px solid transparent; background: none;
    font-size: 13px; font-weight: 600; color: var(--rd-texto-2); cursor: pointer;
}
.rd-subtab:hover { color: var(--rd-primario); }
.rd-subtab.activa { color: var(--rd-primario); border-bottom-color: var(--rd-primario); }
.rd-subtab-n { min-width: 20px; padding: 1px 6px; border-radius: 10px; background: #eef1f5; color: var(--rd-texto-2); font-size: 11px; font-weight: 700; text-align: center; }
.rd-subtab.activa .rd-subtab-n { background: var(--rd-primario-suave); color: var(--rd-primario); }
.rd-tabla-marco[hidden] { display: none; }
.rd-contador-recibo { font-weight: 700; }

/* ---------- Reporte de pagos: filtros ---------- */
/* Dos filas: Sede · Grado · Programa / Certificado · Desde · Hasta · botones */
.rd-filtros { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 12px; align-items: end; margin-bottom: 16px; }
.rd-filtro { min-width: 0; }
.rd-filtro .form-control { height: 42px; }
.rd-f-sede, .rd-f-grado { grid-column: span 3; }
.rd-f-programa { grid-column: span 6; }
.rd-f-tipo { grid-column: span 4; }
.rd-f-fecha { grid-column: span 2; }
.rd-filtro-botones { grid-column: span 4; display: flex; justify-content: flex-end; gap: 8px; }
.rd-filtro-botones .rd-btn-primario, .rd-filtro-botones .rd-btn-secundario { height: 42px; }
@media (max-width: 991.98px) {
    .rd-f-sede, .rd-f-grado, .rd-f-tipo, .rd-f-fecha { grid-column: span 6; }
    .rd-f-programa, .rd-filtro-botones { grid-column: span 12; }
}
@media (max-width: 575.98px) {
    .rd-filtros > .rd-filtro { grid-column: span 12; }
    .rd-filtro-botones .btn { flex: 1; }
}

/* Selects con buscador (select2): misma altura y bordes que los demás campos; el texto
   largo (nombre del programa) se corta con "…" en vez de salirse de la caja */
/* (el tema también estiliza select2: por eso los !important) */
.rd-filtros .select2-container { width: 100% !important; display: block !important; }
.rd-filtros .select2-container--default .select2-selection--single {
    height: 42px !important; border: 1px solid #d8dde8 !important; border-radius: 6px !important;
    display: flex !important; align-items: center !important; background: #fff !important; padding: 0 !important;
}
.rd-filtros .select2-container--default .select2-selection--single .select2-selection__rendered {
    padding: 0 30px 0 12px !important; line-height: 40px !important; font-size: 13.5px !important; color: var(--rd-texto) !important;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block !important; width: 100%;
}
.rd-filtros .select2-container--default .select2-selection--single .select2-selection__arrow { top: 50% !important; transform: translateY(-50%); height: auto !important; right: 6px !important; }
.rd-filtros .select2-container--focus .select2-selection--single,
.rd-filtros .select2-container--open .select2-selection--single { border-color: #1e3c72 !important; box-shadow: 0 0 0 3px rgba(30, 60, 114, .12); }
.rd-select2-lista { border: 1px solid #d8dde8 !important; border-radius: 6px !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .12); overflow: hidden; }
.rd-select2-lista .select2-search__field { border: 1px solid #d8dde8 !important; border-radius: 8px; padding: 6px 10px !important; }
.rd-select2-lista .select2-results__option { font-size: 13px; padding: 7px 12px; white-space: normal; }
.rd-select2-lista .select2-results__option--highlighted[aria-selected] { background: #1e3c72 !important; color: #fff !important; }
.rd-select2-lista .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted) { background: #eef2f9 !important; color: #1e3c72 !important; }

/* ---------- Trabajo de grado (plegable) ---------- */
.rd-trabajo > summary {
    list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 16px 22px; cursor: pointer; font-size: 15px; font-weight: 700; color: var(--rd-texto);
}
.rd-trabajo > summary::-webkit-details-marker { display: none; }
.rd-trabajo > summary i.fa-file-signature { color: var(--rd-primario); margin-right: 6px; }
.rd-trabajo > summary .rd-plegar { font-size: 12px; color: var(--rd-texto-2); transition: transform .15s; }
.rd-trabajo[open] > summary { border-bottom: 1px solid var(--rd-borde); }
.rd-trabajo[open] > summary .rd-plegar { transform: rotate(180deg); }
.rd-pie { display: flex; justify-content: flex-end; padding: 14px 22px; background: #f8fafc; border-top: 1px solid var(--rd-borde); }
.rd-pie .rd-btn-primario { height: 42px; }

/* ---------- Ventana para reimprimir (propia, sin depender de Bootstrap) ---------- */
.rd-ventana-fondo { position: fixed; inset: 0; background: rgba(15, 23, 42, .45); z-index: 1060; display: none; align-items: flex-start; justify-content: center; padding: 40px 16px; overflow-y: auto; }
.rd-ventana-fondo.abierta { display: flex; }
.rd-ventana { background: #fff; border-radius: 8px; width: 100%; max-width: 620px; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
.rd-ventana-cabeza { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 18px 22px; border-bottom: 1px solid var(--rd-borde); }
.rd-ventana-cabeza h4 { margin: 0; font-size: 16px; font-weight: 700; color: var(--rd-texto); }
.rd-ventana-cerrar { background: none; border: none; font-size: 22px; line-height: 1; color: #94a3b8; cursor: pointer; }
.rd-ventana-cuerpo { padding: 18px 22px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
.rd-ventana-cuerpo .rd-campo-ancho { grid-column: 1 / -1; }
.rd-ventana-cuerpo .form-control { font-size: 13.5px; }
.rd-ventana-aviso { grid-column: 1 / -1; margin: 0; padding: 8px 12px; border-radius: 8px; background: #fff8e6; color: #6b4e0e; font-size: 12.5px; }
.rd-ventana-info { background: #eef2f9; color: #334155; }
.rd-ventana-cabeza .rd-ventana-sub { margin: 3px 0 0; font-size: 12.5px; color: var(--rd-texto-2); }
.rd-req { color: #c53030; margin-left: 2px; }
#ventana-sd .voucher-partes { max-width: 300px; }
.rd-ventana-pie { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 22px; border-top: 1px solid var(--rd-borde); background: #f8fafc; border-radius: 0 0 8px 8px; }
.rd-ventana-pie .rd-btn-primario { height: 40px; }

@media (max-width: 767.98px) {
    .rd-barra { align-items: stretch; }
    .rd-tabs { width: 100%; }
    .rd-tab { flex: 1; justify-content: center; padding: 0 10px; }
    .rd-ir-certificado, .rd-btn-ir { width: 100%; justify-content: center; }
    .rd-ir-menu { left: 0; right: 0; min-width: 0; }
    .rd-buscar .rd-btn-primario { flex: 1 1 100%; }
    .rd-ventana-cuerpo { grid-template-columns: 1fr; }
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="panel">
        <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler">
        <i class="flaticon-more-1"></i>
      </button>
    </div>
  </div>

    <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

                <?php
                $NavBar = new FuncionesControladores();
                $NavBar->NavBarControlador();
                ?>
                <button class="kt-aside-close" id="kt_aside_close_btn"><i class="la la-close"></i></button>
                <?php
                $Sidebar = new FuncionesControladores();
                $Sidebar->SidebarControlador();
                ?>

                <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                    <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <!-- Subheader -->
                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2>REGISTRO DE DOCUMENTOS</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>CERTIFICADOS EMITIDOS</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                          <div class="rd">

                            <!-- Pestañas y acceso a las pantallas de cada certificado -->
                            <div class="rd-barra">
                                <div class="rd-tabs" role="tablist">
                                    <button type="button" class="rd-tab activa" role="tab" id="tab-estudiante" data-panel="#panel-estudiante" aria-selected="true">
                                        <i class="fas fa-user-graduate"></i> Por estudiante
                                    </button>
                                    <button type="button" class="rd-tab" role="tab" id="tab-reporte" data-panel="#panel-reporte" aria-selected="false">
                                        <i class="fas fa-chart-bar"></i> Reporte de pagos
                                    </button>
                                </div>
                                <!-- Menú propio con <details>: esta página no depende de los plugins de Bootstrap -->
                                <details class="rd-ir-certificado">
                                    <summary class="rd-btn-ir"><i class="fas fa-plus"></i> Emitir certificado <i class="fas fa-chevron-down rd-ir-flecha"></i></summary>
                                    <div class="rd-ir-menu">
                                        <?php
                                        // Pantalla de cada certificado (los nombres salen de TiposDocumento)
                                        $paginasCertificado = [
                                            'CALIFICACIONES' => 'certificadocalificaciones',
                                            'HISTORIAL_ACADEMICO' => 'certificadohistorialacademico',
                                            'APROBACION_MONOGRAFIA' => 'certificadoaprobacionmonografia',
                                            'MEDIO_MAGNETICO' => 'certificadomediomagnetico',
                                            'REVISION' => 'certificadorevision',
                                        ];
                                        foreach (CertificadoModelo::TiposDocumento() as $claveTipo => $nombreTipo):
                                            if (!isset($paginasCertificado[$claveTipo])) continue; ?>
                                        <a href="<?php echo $paginasCertificado[$claveTipo]; ?>"><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($nombreTipo); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            </div>

                            <!-- ===== Pestaña: por estudiante ===== -->
                            <section class="rd-panel" id="panel-estudiante" role="tabpanel" aria-labelledby="tab-estudiante">
                                <div class="rd-card">
                                    <div class="rd-card-body">
                                        <div class="rd-buscar">
                                            <div class="rd-buscar-campo">
                                                <i class="fas fa-search"></i>
                                                <input type="text" class="form-control" id="input-buscar-ci" placeholder="C.I. o nombre del estudiante" autocomplete="off" aria-label="C.I. o nombre del estudiante">
                                            </div>
                                            <button type="button" class="btn rd-btn-primario" id="btn-buscar-ci"><i class="fas fa-search"></i> Buscar</button>
                                            <div class="rd-buscar-programa" id="col-resultado-busqueda" style="display:none;">
                                                <label class="rd-label" for="select-inscripcion">Estudiante y programa</label>
                                                <select class="form-control" id="select-inscripcion"></select>
                                            </div>
                                        </div>
                                        <div id="alerta-sin-resultado" class="rd-aviso" style="display:none;">
                                            <i class="fas fa-exclamation-triangle"></i> No se encontró ningún estudiante activo.
                                        </div>
                                    </div>
                                </div>

                                <div class="rd-card" id="rd-vacio-estudiante">
                                    <div class="rd-vacio">
                                        <i class="fas fa-folder-open"></i>
                                        <p>Busque un estudiante para ver sus certificados.</p>
                                    </div>
                                </div>

                                <div id="rd-estudiante" style="display:none;">
                                    <!-- Ficha y resumen: cuántos de cada certificado se emitieron -->
                                    <div class="rd-card">
                                        <div class="rd-alumno">
                                            <div class="rd-avatar" id="dato-iniciales"></div>
                                            <div>
                                                <div class="rd-alumno-nombre" id="dato-nombre"></div>
                                                <div class="rd-alumno-sub" id="dato-programa"></div>
                                            </div>
                                        </div>
                                        <!-- Certificados que exige su grado para titularse (se marcan en Costos de certificados);
                                             lo emitido en sí está en la tabla de abajo -->

                                        <div class="rd-titulacion" id="rd-titulacion" style="display:none;"></div>
                                    </div>

                                    <!-- Todas las emisiones del estudiante en el programa -->
                                    <div class="rd-card">
                                        <div class="rd-card-head">
                                            <h3><i class="fas fa-file-alt"></i> Certificados emitidos</h3>
                                            <div class="d-flex flex-wrap align-items-center" id="rd-exportar-estudiante" style="gap:8px;">
                                                <button type="button" class="btn rd-btn-secundario" id="btn-registrar-sd" title="Certificado que el sistema no genera: solo se registra el pago"><i class="fas fa-plus"></i> Registrar otro certificado</button>
                                                <button type="button" class="btn rd-btn-secundario" id="btn-imprimir-estudiante" title="Recibo con los pagos marcados en la tabla"><i class="fas fa-receipt"></i> Imprimir recibo <span class="rd-contador-recibo" id="contador-recibo"></span></button>
                                            </div>
                                        </div>
                                        <div class="rd-card-body">
                                            <!-- El último de cada certificado y, aparte, los que se emitieron antes -->
                                            <div class="rd-subtabs" role="tablist">
                                                <button type="button" class="rd-subtab activa" role="tab" id="subtab-ultimos" data-subpanel="#subpanel-ultimos" aria-selected="true">
                                                    Últimos emitidos <span class="rd-subtab-n" id="n-ultimos"></span>
                                                </button>
                                                <button type="button" class="rd-subtab" role="tab" id="subtab-anteriores" data-subpanel="#subpanel-anteriores" aria-selected="false">
                                                    Anteriores <span class="rd-subtab-n" id="n-anteriores"></span>
                                                </button>
                                            </div>
                                            <div class="rd-tabla-marco table-responsive" id="subpanel-ultimos">
                                                <table class="table table-hover tabla-log" id="tabla-estudiante" data-exportar="Certificados del estudiante" data-exportar-botones="#rd-exportar-estudiante">
                                                    <thead>
                                                        <tr>
                                                            <th class="rd-col-check" data-no-exportar><input type="checkbox" id="check-recibo-todos" title="Pagos que van en el recibo (vienen marcados los del último día)" aria-label="Marcar todos los pagos para el recibo"></th>
                                                            <th>Fecha</th>
                                                            <th>Certificado</th>
                                                            <th>N° voucher</th>
                                                            <th class="text-right">Monto</th>
                                                            <th>Estado</th>
                                                            <th class="text-right">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tabla-estudiante-body"></tbody>
                                                    <tfoot id="tabla-estudiante-pie"></tfoot>
                                                </table>
                                            </div>
                                            <div class="rd-tabla-marco table-responsive" id="subpanel-anteriores" hidden>
                                                <table class="table table-hover tabla-log" id="tabla-anteriores">
                                                    <thead>
                                                        <tr>
                                                            <th class="rd-col-check"><input type="checkbox" id="check-recibo-anteriores" title="Pagos que van en el recibo" aria-label="Marcar todos los anteriores para el recibo"></th>
                                                            <th>Fecha</th>
                                                            <th>Certificado</th>
                                                            <th>N° voucher</th>
                                                            <th class="text-right">Monto</th>
                                                            <th>Estado</th>
                                                            <th class="text-right">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tabla-anteriores-body"></tbody>
                                                    <tfoot id="tabla-anteriores-pie"></tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Trabajo de grado (plegado: se abre solo para corregirlo) -->
                                    <details class="rd-card rd-trabajo" id="card-trabajo" style="display:none;">
                                        <summary>
                                            <span><i class="fas fa-file-signature"></i> Trabajo de grado</span>
                                            <i class="fas fa-chevron-down rd-plegar"></i>
                                        </summary>
                                        <div class="rd-card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="rd-label" for="edit-titulo">Título</label>
                                                        <textarea class="form-control" id="edit-titulo" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-md-0">
                                                        <label class="rd-label" for="edit-calificacion">Calificación</label>
                                                        <input type="number" class="form-control" id="edit-calificacion" min="0" max="100" style="height:46px;">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group mb-0">
                                                        <label class="rd-label" for="edit-tribunal">Tribunal <span class="text-muted font-weight-normal">(uno por línea)</span></label>
                                                        <textarea class="form-control" id="edit-tribunal" rows="3" placeholder="PRESIDENTE: NOMBRE"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rd-pie">
                                            <button type="button" class="btn rd-btn-primario" id="btn-guardar-trabajo"><i class="fas fa-save"></i> Guardar cambios</button>
                                        </div>
                                    </details>
                                </div>
                            </section>

                            <!-- ===== Pestaña: reporte de pagos ===== -->
                            <section class="rd-panel" id="panel-reporte" role="tabpanel" aria-labelledby="tab-reporte" hidden>
                                <div class="rd-card">
                                    <div class="rd-card-head">
                                        <h3><i class="fas fa-chart-bar"></i> Pagos de certificados</h3>
                                        <div class="d-flex flex-wrap align-items-center" id="rd-exportar" style="gap:8px;">
                                            <button type="button" class="btn rd-btn-secundario" id="btn-imprimir-pagos"><i class="fas fa-print"></i> Imprimir reporte</button>
                                        </div>
                                    </div>
                                    <div class="rd-card-body">
                                        <?php
                                        // Programas con su sede y grado: al elegir sede o grado la lista de
                                        // programas se reduce a los que corresponden
                                        $programasFiltro = CertificadoModelo::ProgramasFiltroModelo();
                                        $sedesFiltro = array_values(array_unique(array_filter(array_column($programasFiltro, 'Sede'))));
                                        sort($sedesFiltro);
                                        ?>
                                        <div class="rd-filtros">
                                            <div class="rd-filtro rd-f-sede">
                                                <label class="rd-label" for="filtro-sede">Sede</label>
                                                <select class="form-control rd-select-buscador" id="filtro-sede">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($sedesFiltro as $sede): ?>
                                                    <option value="<?php echo htmlspecialchars($sede); ?>"><?php echo htmlspecialchars($sede); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="rd-filtro rd-f-grado">
                                                <label class="rd-label" for="filtro-grado">Grado académico</label>
                                                <select class="form-control rd-select-buscador" id="filtro-grado">
                                                    <option value="">Todos</option>
                                                    <?php foreach (CertificadoModelo::GradosAcademicos() as $claveGrado => $nombreGrado): ?>
                                                    <option value="<?php echo htmlspecialchars($claveGrado); ?>"><?php echo htmlspecialchars($nombreGrado); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="rd-filtro rd-f-programa">
                                                <label class="rd-label" for="filtro-programa">Programa</label>
                                                <select class="form-control rd-select-buscador" id="filtro-programa">
                                                    <option value="">Todos</option>
                                                    <?php foreach ($programasFiltro as $prog): ?>
                                                    <option value="<?php echo (int)$prog['ProgramaID']; ?>" data-sede="<?php echo htmlspecialchars($prog['Sede'] ?? ''); ?>" data-grado="<?php echo htmlspecialchars($prog['GradoAcademico'] ?? ''); ?>"><?php echo htmlspecialchars($prog['NombrePrograma'] . ($prog['Codigo'] ? ' · ' . $prog['Codigo'] : '')); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="rd-filtro rd-f-tipo">
                                                <label class="rd-label" for="filtro-tipo">Certificado</label>
                                                <select class="form-control rd-select-buscador" id="filtro-tipo">
                                                    <option value="">Todos</option>
                                                    <?php foreach (CertificadoModelo::TiposParaListado() as $claveTipo => $nombreTipo): ?>
                                                    <option value="<?php echo htmlspecialchars($claveTipo); ?>"><?php echo htmlspecialchars($nombreTipo); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="rd-filtro rd-f-fecha">
                                                <label class="rd-label" for="filtro-desde">Desde</label>
                                                <input type="date" class="form-control" id="filtro-desde">
                                            </div>
                                            <div class="rd-filtro rd-f-fecha">
                                                <label class="rd-label" for="filtro-hasta">Hasta</label>
                                                <input type="date" class="form-control" id="filtro-hasta">
                                            </div>
                                            <div class="rd-filtro rd-filtro-botones">
                                                <button type="button" class="btn rd-btn-primario" id="btn-filtrar-pagos"><i class="fas fa-search"></i> Consultar</button>
                                                <button type="button" class="btn rd-btn-secundario" id="btn-limpiar-pagos">Limpiar</button>
                                            </div>
                                        </div>
                                        <div class="rd-tabla-marco table-responsive">
                                            <table class="table table-hover tabla-log" id="tabla-log" data-exportar="Pagos de certificados" data-exportar-botones="#rd-exportar">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Certificado</th>
                                                        <th>Estudiante</th>
                                                        <th>N° voucher</th>
                                                        <th class="text-right">Monto</th>
                                                        <th>Estado</th>
                                                        <th class="text-right">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabla-log-body">
                                                    <tr class="rd-sin-filas"><td colspan="7">Elija el periodo y pulse <strong>Consultar</strong>.</td></tr>
                                                </tbody>
                                                <tfoot id="tabla-log-pie"></tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>

                          </div>
                        </div>

                        <!-- Ventana: editar los datos guardados de un documento (guardar, o guardar e imprimir) -->
                        <div class="rd-ventana-fondo" id="ventana-reimprimir" role="dialog" aria-modal="true" aria-labelledby="ventana-reimprimir-titulo">
                            <div class="rd-ventana">
                                <div class="rd-ventana-cabeza">
                                    <h4 id="ventana-reimprimir-titulo">Editar documento</h4>
                                    <button type="button" class="rd-ventana-cerrar" data-cerrar-ventana aria-label="Cerrar">&times;</button>
                                </div>
                                <form id="form-reimprimir" method="POST" target="_blank">
                                    <div class="rd-ventana-cuerpo" id="campos-reimprimir"></div>
                                    <div class="rd-ventana-pie">
                                        <button type="button" class="btn rd-btn-secundario mr-auto" data-cerrar-ventana>Cancelar</button>
                                        <button type="button" class="btn rd-btn-secundario" id="btn-guardar-imprimir"><i class="fas fa-print"></i> Guardar e imprimir</button>
                                        <button type="button" class="btn rd-btn-primario" id="btn-guardar-documento"><i class="fas fa-save"></i> Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Ventana: registrar un certificado que el sistema no genera (solo el pago) -->
                        <div class="rd-ventana-fondo" id="ventana-sd" role="dialog" aria-modal="true" aria-labelledby="ventana-sd-titulo">
                            <div class="rd-ventana">
                                <div class="rd-ventana-cabeza">
                                    <div>
                                        <h4 id="ventana-sd-titulo">Registrar otro certificado</h4>
                                        <p class="rd-ventana-sub" id="ventana-sd-sub"></p>
                                    </div>
                                    <button type="button" class="rd-ventana-cerrar" data-cerrar-sd aria-label="Cerrar">&times;</button>
                                </div>
                                <div class="rd-ventana-cuerpo">
                                    <div class="rd-campo-ancho">
                                        <label class="rd-label" for="sd-certificado">Certificado<span class="rd-req">*</span></label>
                                        <select class="form-control" id="sd-certificado"></select>
                                    </div>
                                    <div class="rd-campo-ancho">
                                        <label class="rd-label" for="sd-voucher">N° de voucher<span class="rd-req">*</span></label>
                                        <input type="text" class="form-control" id="sd-voucher" data-voucher-partes="alfanumerico">
                                    </div>
                                    <div>
                                        <label class="rd-label" for="sd-fecha-pago">Fecha de pago del voucher<span class="rd-req">*</span></label>
                                        <input type="date" class="form-control" id="sd-fecha-pago" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div>
                                        <label class="rd-label" for="sd-monto">Monto pagado (Bs.)</label>
                                        <input type="number" class="form-control" id="sd-monto" min="0" step="0.01" placeholder="0.00">
                                    </div>
                                    <p class="rd-ventana-aviso rd-ventana-info"><i class="fas fa-info-circle"></i> Solo se registra el pago; no se genera ningún documento.</p>
                                </div>
                                <div class="rd-ventana-pie">
                                    <button type="button" class="btn rd-btn-secundario mr-auto" data-cerrar-sd>Cancelar</button>
                                    <button type="button" class="btn rd-btn-primario" id="btn-guardar-sd"><i class="fas fa-save"></i> Registrar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Imprimir tal como está guardado (sin abrir la ventana de edición) -->
                        <form id="form-imprimir-directo" method="POST" class="d-none"></form>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterResumidoControlador();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <!-- select2 otra vez, sobre el jQuery de esta página (el de la plantilla queda reemplazado) -->
    <script src="vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js"></script>
    <script>
    $(document).ready(function() {
        var idTrabajoActual = null;
        var TIPOS = <?php echo json_encode(CertificadoModelo::TiposParaListado(), JSON_UNESCAPED_UNICODE); ?>;

        // Escapar texto que viene de la BD antes de insertarlo como HTML
        function esc(texto) {
            return $('<div>').text(texto == null ? '' : String(texto)).html();
        }
        function formatoBs(monto) {
            return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function tieneMonto(doc) {
            return doc.Monto !== null && doc.Monto !== undefined && doc.Monto !== '';
        }
        // "2026-10-15 12:30:00" → "15/10/2026 12:30"
        function fechaHora(valor) {
            var p = String(valor || '').match(/^(\d{4})-(\d{2})-(\d{2})(?: (\d{2}):(\d{2}))?/);
            return p ? p[3] + '/' + p[2] + '/' + p[1] + (p[4] ? ' ' + p[4] + ':' + p[5] : '') : (valor || '-');
        }

        // ===== Pestañas =====
        $('.rd-tab').on('click', function() {
            $('.rd-tab').removeClass('activa').attr('aria-selected', 'false');
            $(this).addClass('activa').attr('aria-selected', 'true');
            $('.rd-panel').attr('hidden', true);
            $($(this).data('panel')).removeAttr('hidden');
        });

        // ===== Celdas comunes a las dos tablas =====
        // Certificado sin documento: solo se registró el pago (no se imprime ni lo aprueba el decano)
        function esSinDocumento(doc) { return doc.TipoDocumento === 'SIN_DOCUMENTO'; }
        // Nombre que se muestra: el del certificado sin documento, o el del tipo
        function nombreDoc(doc) { return doc.NombreCertificado || TIPOS[doc.TipoDocumento] || doc.TipoDocumento; }
        // "Último" por tipo; los sin documento, por su nombre (Pro Biblioteca aparte de los demás)
        function claveUltimo(doc) { return esSinDocumento(doc) ? 'SD:' + doc.NombreCertificado : doc.TipoDocumento; }
        // Fecha de registro y, si se anotó, la fecha en que se pagó el voucher
        function htmlFechas(doc) {
            return esc(fechaHora(doc.FechaEmision)) +
                (doc.FechaPago ? '<div class="text-muted small">Pagó: ' + esc(fechaHora(doc.FechaPago)) + '</div>' : '');
        }

        // Aprobación del decano (solo control: no bloquea otros trámites)
        function htmlEstado(doc) {
            if (esSinDocumento(doc)) return '<span class="rd-estado registrado" title="Solo se registró el pago">Registrado</span>';
            return doc.Estado === 'APROBADO'
                ? '<span class="rd-estado aprobado" title="' + esc(doc.FechaAprobacion ? 'Aprobado el ' + fechaHora(doc.FechaAprobacion) : '') + '">Aprobado</span>'
                : '<span class="rd-estado pendiente">Por aprobar</span>';
        }

        // Editar (corregir y guardar los datos), Imprimir (tal como está guardado), Aprobar /
        // Quitar aprobación y Borrar (solo mientras el decano no lo apruebe)
        function htmlAcciones(doc) {
            var tieneDatos = Number(doc.TieneDatos) === 1;
            if (esSinDocumento(doc)) {
                // No hay documento: solo corregir el registro o borrarlo
                return '<div class="rd-acciones">' +
                    '<button type="button" class="rd-btn-mini" data-editar="' + doc.IdDocumento + '" title="Corregir voucher, monto o fecha de pago"><i class="fas fa-pen"></i> Editar</button>' +
                    '<button type="button" class="rd-btn-mini icono borrar" data-borrar-documento="' + doc.IdDocumento + '"' +
                    ' data-voucher="' + esc(doc.NumeroVaucher) + '" data-estudiante="' + esc(doc.NombreEstudiante) + '"' +
                    ' title="Borrar" aria-label="Borrar"><i class="fas fa-trash-alt"></i></button></div>';
            }
            var sinDatos = ' disabled title="Se generó antes de que el sistema guardara los datos: vuelva a generarlo desde su pantalla"';
            var html = '<button type="button" class="rd-btn-mini" data-editar="' + doc.IdDocumento + '"' +
                    (tieneDatos ? ' title="Corregir los datos del certificado"' : sinDatos) + '><i class="fas fa-pen"></i> Editar</button>' +
                '<button type="button" class="rd-btn-mini icono" data-imprimir="' + doc.IdDocumento + '"' +
                    (tieneDatos ? ' title="Imprimir" aria-label="Imprimir"' : sinDatos) + '><i class="fas fa-print"></i></button>';
            if (doc.Estado === 'APROBADO') {
                html += '<button type="button" class="rd-btn-mini icono" data-quitar-aprobacion="' + doc.IdDocumento + '" title="Quitar aprobación" aria-label="Quitar aprobación"><i class="fas fa-undo"></i></button>';
            } else {
                html += '<button type="button" class="rd-btn-mini aprobar" data-aprobar="' + doc.IdDocumento + '" title="Marcar como aprobado por el decano"><i class="fas fa-check"></i> Aprobar</button>' +
                    '<button type="button" class="rd-btn-mini icono borrar" data-borrar-documento="' + doc.IdDocumento + '"' +
                    ' data-voucher="' + esc(doc.NumeroVaucher) + '" data-estudiante="' + esc(doc.NombreEstudiante) + '"' +
                    ' title="Borrar" aria-label="Borrar"><i class="fas fa-trash-alt"></i></button>';
            }
            return '<div class="rd-acciones">' + html + '</div>';
        }

        function htmlMonto(doc) {
            return tieneMonto(doc) ? formatoBs(doc.Monto) : '<span class="text-muted font-weight-normal">-</span>';
        }

        // Fila de total de una tabla (columna del monto en la posición indicada)
        function htmlTotal(docs, colsAntes, colsDespues) {
            var total = 0, sinMonto = 0;
            docs.forEach(function(doc) { if (tieneMonto(doc)) total += parseFloat(doc.Monto) || 0; else sinMonto++; });
            return '<tr><td colspan="' + colsAntes + '">Total · ' + docs.length + ' documento(s)' +
                (sinMonto ? ' <span class="text-muted font-weight-normal">(' + sinMonto + ' sin monto)</span>' : '') + '</td>' +
                '<td class="rd-monto">' + formatoBs(total) + '</td>' + (colsDespues ? '<td colspan="' + colsDespues + '"></td>' : '') + '</tr>';
        }

        // Recibo de pago de certificados (mismo formato que el recibo de pago del programa)
        function abrirRecibo(parametros) {
            window.open('vistas/componentes/recibo-pago-certificados.php?' + $.param(parametros), '_blank');
        }

        // Vuelve a cargar lo que esté a la vista (después de aprobar, borrar o reimprimir)
        function refrescar() {
            if (estudianteActual) cargarEstudiante();
            if (reporteConsultado) cargarReporte();
        }

        // ===== Pestaña "Por estudiante" =====
        var estudianteActual = null;   // { estudianteID, programaID, nombre, ci, programa }
        var docsEstudiante = [];
        var docResaltado = null;       // IdDocumento recién generado (llegando desde un certificado)

        function limpiarEstudiante() {
            estudianteActual = null;
            docsEstudiante = [];
            idTrabajoActual = null;
            $('#rd-estudiante').hide();
            mostrarSubtab('#subtab-ultimos');
            $('#card-trabajo').hide().removeAttr('open');
            $('#rd-vacio-estudiante').show();
        }

        // seleccionar: "estudianteID-programaID" para dejar elegida esa inscripción (al llegar
        // desde un certificado recién generado)
        function buscarPorCI(seleccionar) {
            var termino = $('#input-buscar-ci').val().trim();
            $('#alerta-sin-resultado').hide();
            $('#col-resultado-busqueda').hide();
            $('#select-inscripcion').empty();
            limpiarEstudiante();

            if (!termino) {
                Swal.fire({ type: 'warning', title: 'Escriba el C.I. o el nombre del estudiante' });
                return;
            }

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'buscarEstudiantes', termino: termino },
                dataType: 'json',
                success: function(data) {
                    if (!data.success) {
                        Swal.fire({ type: 'warning', title: data.mensaje || 'No se pudo buscar' });
                        return;
                    }
                    if (!data.resultados || !data.resultados.length) {
                        $('#alerta-sin-resultado').show();
                        return;
                    }

                    // Cada opción es una inscripción (un estudiante puede tener varios programas)
                    var $select = $('#select-inscripcion');
                    if (data.resultados.length > 1) $select.append('<option value="">Elija el estudiante y el programa</option>');
                    data.resultados.forEach(function(insc) {
                        var nombre = [insc.Apaterno, insc.Amaterno, insc.Nombre].filter(Boolean).join(' ');
                        var ci = insc.Ci + (insc.Complemento ? '-' + insc.Complemento : '');
                        $('<option></option>')
                            .val(insc.EstudianteID + '-' + insc.ProgramaID)
                            .data('insc', { estudianteID: insc.EstudianteID, programaID: insc.ProgramaID, nombre: nombre, ci: ci,
                                programa: insc.NombrePrograma, grado: insc.GradoLegible || insc.GradoAcademico })
                            .text(nombre + ' · C.I. ' + ci + ' · ' + insc.NombrePrograma)
                            .appendTo($select);
                    });

                    if (seleccionar && $select.find('option[value="' + seleccionar + '"]').length) {
                        $select.val(seleccionar).trigger('change');
                        $('#col-resultado-busqueda').toggle(data.resultados.length > 1);
                    } else if (data.resultados.length === 1) {
                        $select.trigger('change');
                    } else {
                        $('#col-resultado-busqueda').show();
                    }
                },
                error: function() {
                    Swal.fire({ type: 'error', title: 'No se pudo realizar la búsqueda' });
                }
            });
        }

        // Una búsqueda hecha a mano ya no resalta el documento recién generado
        $('#btn-buscar-ci').on('click', function() {
            docResaltado = null;
            buscarPorCI();
        });
        $('#input-buscar-ci').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                docResaltado = null;
                buscarPorCI();
            }
        });

        $('#select-inscripcion').on('change', function() {
            var insc = $(this).find('option:selected').data('insc');
            limpiarEstudiante();
            if (!insc) return;
            estudianteActual = insc;
            // En la dirección, para que al recargar se vuelva a abrir este estudiante
            history.replaceState(null, '', window.location.pathname + '?ci=' + encodeURIComponent(insc.ci) +
                '&inscripcion=' + insc.estudianteID + '-' + insc.programaID);
            cargarEstudiante();
        });

        function cargarEstudiante() {
            var insc = estudianteActual;
            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'estadoDocumentos', estudianteID: insc.estudianteID, programaID: insc.programaID },
                dataType: 'json',
                success: function(data) {
                    if (!data.success || estudianteActual !== insc) return;
                    docsEstudiante = data.documentos || [];

                    // Ficha
                    var partes = insc.nombre.split(' ').filter(Boolean);
                    $('#dato-iniciales').text(((partes[0] || '')[0] || '') + ((partes[2] || partes[1] || '')[0] || ''));
                    $('#dato-nombre').text(insc.nombre);
                    $('#dato-programa').text('C.I. ' + insc.ci + ' · ' + insc.programa + (insc.grado ? ' · ' + insc.grado : ''));

                    // Para la titulación: los que exige su grado (Costos de certificados), emitidos
                    // primero y pendientes después, con el avance
                    var tit = data.titulacion || { total: 0 };
                    if (!tit.total) {
                        $('#rd-titulacion').hide().empty();
                    } else {
                        var orden = tit.certificados.slice().sort(function(a, b) { return (b.pagado ? 1 : 0) - (a.pagado ? 1 : 0); });
                        $('#rd-titulacion').toggleClass('completo', !!tit.completo).html(
                            '<div class="rd-tit-cabeza"><span class="rd-tit-titulo">Certificados para la titulación</span>' +
                            '<span class="rd-tit-cuenta">' + tit.cumplidos + ' de ' + tit.total + '</span></div>' +
                            '<div class="rd-tit-barra"><span style="width:' + Math.round(100 * tit.cumplidos / tit.total) + '%"></span></div>' +
                            '<ul class="rd-tit-lista">' + orden.map(function(c) {
                                return c.pagado
                                    ? '<li class="rd-tit-hecho" title="Emitido' + (c.fecha ? ' el ' + esc(fechaHora(c.fecha)) : '') + '"><i class="fas fa-check"></i> ' + esc(c.nombre) + '</li>'
                                    : '<li class="rd-tit-falta" title="Pendiente' + (c.precio ? ' · ' + esc(formatoBs(c.precio)) : '') + '"><i class="far fa-circle"></i> ' + esc(c.nombre) + '</li>';
                            }).join('') + '</ul>' +
                            (tit.completo ? '<p class="rd-tit-ok"><i class="fas fa-check-circle"></i> Tiene todos los certificados exigidos</p>' : '')
                        ).show();
                    }

                    // "Últimos emitidos": el más reciente de cada certificado (sale de los datos: pasa
                    // a otro solo cuando se genera uno nuevo de ese tipo). "Anteriores": los demás.
                    // La lista viene de la más reciente a la más antigua.
                    var ultimoPorTipo = {};
                    docsEstudiante.forEach(function(doc) {
                        if (!ultimoPorTipo[claveUltimo(doc)]) ultimoPorTipo[claveUltimo(doc)] = Number(doc.IdDocumento);
                    });
                    var ultimos = docsEstudiante.filter(function(doc) { return ultimoPorTipo[claveUltimo(doc)] === Number(doc.IdDocumento); });
                    var anteriores = docsEstudiante.filter(function(doc) { return ultimos.indexOf(doc) === -1; });
                    // Para el recibo vienen marcados los pagos del último día con emisiones (en las dos tablas)
                    var ultimoDia = docsEstudiante.length ? String(docsEstudiante[0].FechaEmision || '').substring(0, 10) : '';

                    function filaDocumento(doc, ultimo) {
                        var id = Number(doc.IdDocumento);
                        var tipo = esc(doc.TipoDocumento);
                        var clases = (ultimo ? 'rd-ultimo' : '') + (id === docResaltado ? ' rd-recien' : '');
                        return '<tr data-id="' + id + '" data-tipo="' + tipo + '"' + (clases.trim() ? ' class="' + clases.trim() + '"' : '') + '>' +
                            '<td class="rd-col-check"><input type="checkbox" class="rd-check-recibo" value="' + id + '"' +
                                (String(doc.FechaEmision || '').substring(0, 10) === ultimoDia ? ' checked' : '') +
                                ' aria-label="Incluir en el recibo"></td>' +
                            '<td class="text-nowrap">' + htmlFechas(doc) + '</td>' +
                            '<td><span class="rd-tipo" data-tipo="' + tipo + '">' + esc(nombreDoc(doc)) + '</span></td>' +
                            '<td class="text-nowrap">' + esc(doc.NumeroVaucher || '-') + '</td>' +
                            '<td class="rd-monto">' + htmlMonto(doc) + '</td>' +
                            '<td>' + htmlEstado(doc) + '</td>' +
                            '<td>' + htmlAcciones(doc) + '</td>' +
                            '</tr>';
                    }
                    function llenarTabla(cuerpo, pie, docs, ultimo, sinFilas) {
                        $(cuerpo).html(docs.length
                            ? docs.map(function(doc) { return filaDocumento(doc, ultimo); }).join('')
                            : '<tr class="rd-sin-filas"><td colspan="7">' + sinFilas + '</td></tr>');
                        $(pie).html(docs.length ? htmlTotal(docs, 4, 2) : '');
                    }
                    llenarTabla('#tabla-estudiante-body', '#tabla-estudiante-pie', ultimos, true,
                        'Todavía no se le emitió ningún certificado en este programa.');
                    llenarTabla('#tabla-anteriores-body', '#tabla-anteriores-pie', anteriores, false,
                        'No hay certificados anteriores.');
                    $('#n-ultimos').text(ultimos.length);
                    $('#n-anteriores').text(anteriores.length);
                    // El documento recién generado se muestra en su pestaña; si no, se queda la que estaba
                    if ($('#tabla-anteriores-body tr.rd-recien').length) mostrarSubtab('#subtab-anteriores');
                    else if ($('#tabla-estudiante-body tr.rd-recien').length) mostrarSubtab('#subtab-ultimos');
                    actualizarContadorRecibo();

                    // Trabajo de grado
                    if (data.trabajo) {
                        idTrabajoActual = data.trabajo.IdTrabajo;
                        $('#edit-titulo').val(data.trabajo.TituloTrabajo);
                        $('#edit-calificacion').val(data.trabajo.CalificacionNumeral);
                        $('#edit-tribunal').val((data.trabajo.Tribunal || '').split('|').join('\n'));
                        $('#card-trabajo').show();
                    } else {
                        idTrabajoActual = null;
                        $('#card-trabajo').hide();
                    }

                    $('#rd-vacio-estudiante').hide();
                    $('#rd-estudiante').show();

                    // Llevar la vista al documento recién generado; el destello es una sola vez
                    // (la marca "Último" es la que queda)
                    var fila = document.querySelector('#rd-estudiante tr.rd-recien');
                    if (fila) {
                        docResaltado = null;
                        fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        }

        // ===== Recibo con los pagos marcados =====
        function idsRecibo() {
            return $('.rd-check-recibo:checked').map(function() { return this.value; }).get();
        }
        // El contador suma las dos tablas; la casilla de cada encabezado marca solo su tabla
        function actualizarContadorRecibo() {
            var marcados = idsRecibo().length;
            $('#contador-recibo').text(marcados ? '(' + marcados + ')' : '');
            [['#check-recibo-todos', '#tabla-estudiante'], ['#check-recibo-anteriores', '#tabla-anteriores']].forEach(function(par) {
                var total = $(par[1] + ' .rd-check-recibo').length;
                var enTabla = $(par[1] + ' .rd-check-recibo:checked').length;
                $(par[0]).prop({ checked: total > 0 && enTabla === total, indeterminate: enTabla > 0 && enTabla < total });
            });
        }
        $(document).on('change', '.rd-check-recibo', actualizarContadorRecibo);
        $('#check-recibo-todos, #check-recibo-anteriores').on('change', function() {
            $(this).closest('table').find('.rd-check-recibo').prop('checked', this.checked);
            actualizarContadorRecibo();
        });

        // Pestañas "Últimos emitidos" / "Anteriores"
        function mostrarSubtab(boton) {
            $('.rd-subtab').removeClass('activa').attr('aria-selected', 'false');
            $(boton).addClass('activa').attr('aria-selected', 'true');
            $('#subpanel-ultimos, #subpanel-anteriores').attr('hidden', true);
            $($(boton).data('subpanel')).removeAttr('hidden');
        }
        $('.rd-subtab').on('click', function() { mostrarSubtab(this); });

        $('#btn-imprimir-estudiante').on('click', function() {
            if (!estudianteActual) return;
            var ids = idsRecibo();
            if (!ids.length) {
                Swal.fire({ type: 'info', title: 'Marque al menos un pago', text: 'El recibo sale con los pagos marcados en la tabla.' });
                return;
            }
            abrirRecibo({ estudianteID: estudianteActual.estudianteID, programaID: estudianteActual.programaID, ids: ids.join(',') });
        });

        $('#btn-guardar-trabajo').on('click', function() {
            if (!idTrabajoActual) return;

            var titulo = $('#edit-titulo').val().trim();
            var calificacion = $('#edit-calificacion').val();
            var tribunal = $('#edit-tribunal').val().split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; }).join('|');

            if (!titulo) {
                Swal.fire({ type: 'warning', title: 'Falta el título' });
                return;
            }

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: {
                    accion: 'actualizarTrabajoGrado',
                    idTrabajo: idTrabajoActual,
                    titulo: titulo,
                    calificacion: calificacion,
                    tribunal: tribunal
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        Swal.fire({ type: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ type: 'error', title: 'No se pudo guardar' });
                    }
                },
                error: function() {
                    Swal.fire({ type: 'error', title: 'No se pudo guardar' });
                }
            });
        });

        // ===== Pestaña "Reporte de pagos": vacía hasta pulsar Consultar =====
        var reporteConsultado = false;
        var docsReporte = [];
        var filtrosConsultados = null; // los de la última consulta: el reporte impreso es lo que se ve

        function filtrosReporte() {
            return {
                sede: $('#filtro-sede').val() || '',
                grado: $('#filtro-grado').val() || '',
                programaID: $('#filtro-programa').val() || '',
                tipo: $('#filtro-tipo').val() || '',
                desde: $('#filtro-desde').val() || '',
                hasta: $('#filtro-hasta').val() || ''
            };
        }

        // Selects con buscador
        $('.rd-select-buscador').select2({
            width: '100%',
            dropdownCssClass: 'rd-select2-lista',
            language: { noResults: function() { return 'Sin resultados'; } }
        });

        // Al elegir sede o grado, en Programa quedan solo los que corresponden
        var opcionesPrograma = $('#filtro-programa option').not('[value=""]').clone();
        function filtrarProgramas() {
            var sede = $('#filtro-sede').val();
            var grado = $('#filtro-grado').val();
            var $programa = $('#filtro-programa');
            var elegido = $programa.val();
            $programa.find('option').not('[value=""]').remove();
            opcionesPrograma.filter(function() {
                return (!sede || this.getAttribute('data-sede') === sede) && (!grado || this.getAttribute('data-grado') === grado);
            }).clone().appendTo($programa);
            $programa.val($programa.find('option[value="' + elegido + '"]').length ? elegido : '').trigger('change.select2');
        }
        $('#filtro-sede, #filtro-grado').on('change', filtrarProgramas);

        function cargarReporte() {
            reporteConsultado = true;
            var f = filtrosReporte();
            filtrosConsultados = f;
            var hayFiltros = f.desde || f.hasta || f.programaID || f.tipo;

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: $.extend({ accion: 'listarDocumentosEmitidos' }, f),
                dataType: 'json',
                success: function(data) {
                    var $tbody = $('#tabla-log-body').empty();
                    $('#tabla-log-pie').empty();
                    docsReporte = (data && data.success && data.documentos) || [];

                    if (!docsReporte.length) {
                        $tbody.html('<tr class="rd-sin-filas"><td colspan="7">' +
                            (hayFiltros ? 'Sin documentos emitidos con estos filtros.' : 'Sin documentos emitidos todavía.') + '</td></tr>');
                        return;
                    }

                    docsReporte.forEach(function(doc) {
                        $tbody.append(
                            '<tr>' +
                            // Quién lo emitió va bajo la fecha (una columna menos: la tabla entra a lo ancho)
                            '<td class="text-nowrap">' + htmlFechas(doc) +
                                (doc.UsuarioEmisor ? '<div class="text-muted small">por ' + esc(doc.UsuarioEmisor) + '</div>' : '') + '</td>' +
                            '<td><span class="rd-tipo" data-tipo="' + esc(doc.TipoDocumento) + '">' + esc(nombreDoc(doc)) + '</span></td>' +
                            '<td>' + esc(doc.NombreFicha || doc.NombreEstudiante || '-') + (doc.Ci ? '<div class="text-muted small">C.I. ' + esc(doc.Ci) + '</div>' : '') + '</td>' +
                            '<td class="text-nowrap">' + esc(doc.NumeroVaucher || '-') + '</td>' +
                            '<td class="rd-monto">' + htmlMonto(doc) + '</td>' +
                            '<td>' + htmlEstado(doc) + '</td>' +
                            '<td>' + htmlAcciones(doc) + '</td>' +
                            '</tr>'
                        );
                    });
                    $('#tabla-log-pie').html(htmlTotal(docsReporte, 4, 2));
                }
            });
        }

        $('#btn-filtrar-pagos').on('click', cargarReporte);
        $('#btn-limpiar-pagos').on('click', function() {
            $('#filtro-desde, #filtro-hasta').val('');
            $('#filtro-sede, #filtro-grado').val('');
            filtrarProgramas();
            $('#filtro-programa, #filtro-tipo, #filtro-sede, #filtro-grado').val('').trigger('change.select2');
            reporteConsultado = false;
            filtrosConsultados = null;
            docsReporte = [];
            $('#tabla-log-pie').empty();
            $('#tabla-log-body').html('<tr class="rd-sin-filas"><td colspan="7">Elija el periodo y pulse <strong>Consultar</strong>.</td></tr>');
        });
        $('#btn-imprimir-pagos').on('click', function() {
            if (!filtrosConsultados || !docsReporte.length) {
                Swal.fire({ type: 'info', title: 'Primero consulte', text: 'Elija el periodo, el programa o el certificado y pulse Consultar.' });
                return;
            }
            abrirRecibo(filtrosConsultados);
        });

        // ===== Reimprimir con los datos guardados (editables) =====
        var ETIQUETAS_CAMPO = {
            nombre: 'Nombre del estudiante', programa: 'Programa', nivel: 'Nivel', anio: 'Año',
            vaucher: 'N° de voucher', monto: 'Monto pagado (Bs.)', fecha: 'Fecha del documento', curso: 'Curso',
            titulo: 'Título del trabajo', tribunal: 'Tribunal (uno por línea)', entrega: 'Fecha de entrega',
            horaEntrega: 'Hora de entrega', calificacion: 'Calificación', tratamiento: 'Tratamiento',
            tipotrabajo: 'Tipo de trabajo', coordinador: 'Coordinador(a)', director: 'Director(a)',
            fechaPago: 'Fecha de pago del voucher'
        };
        var CAMPOS_FECHA = ['fecha', 'entrega', 'fechaPago'];
        var CAMPOS_ANCHOS = ['nombre', 'programa', 'titulo', 'tribunal'];

        function abrirVentana() { $('#ventana-reimprimir').addClass('abierta'); }
        function cerrarVentana() { $('#ventana-reimprimir').removeClass('abierta'); }

        $(document).on('click', '[data-cerrar-ventana]', cerrarVentana);
        $('#ventana-reimprimir').on('click', function(e) { if (e.target === this) cerrarVentana(); });
        $(document).on('keydown', function(e) { if (e.key === 'Escape') cerrarVentana(); });

        var idEditando = null;

        // ===== Editar: la ventana con los datos guardados, que se corrigen y se guardan =====
        $(document).on('click', '[data-editar]', function() {
            var id = $(this).data('editar');
            var tipoTexto = $(this).closest('tr').find('.rd-tipo').first().text();
            $.post('ajax/certificado.ajax.php', { accion: 'obtenerDocumento', idDocumento: id }, function(data) {
                if (!data.success || !data.documento.datos) {
                    Swal.fire({ type: 'error', title: 'No se puede editar', text: data.mensaje || 'El documento no tiene datos guardados.' });
                    return;
                }
                var doc = data.documento;
                idEditando = doc.idDocumento;
                var $campos = $('#campos-reimprimir').empty();
                $('#ventana-reimprimir-titulo').text('Editar: ' + (tipoTexto || 'documento'));
                // Sin documento (solo se registró el pago) no hay PDF: solo "Guardar cambios"
                $('#btn-guardar-imprimir').toggle(!!doc.rutaPdf);
                if (!doc.rutaPdf) doc.estado = '';  // tampoco tiene aprobación del decano

                if (doc.estado === 'APROBADO') {
                    $campos.append('<p class="rd-ventana-aviso"><i class="fas fa-info-circle"></i> Ya está aprobado: si cambia algún dato volverá a quedar <strong>por aprobar</strong>.</p>');
                }

                Object.keys(doc.datos).forEach(function(clave) {
                    var valor = doc.datos[clave];
                    // estudianteID, programaID y los demás datos internos no se muestran ni se editan
                    if (!ETIQUETAS_CAMPO[clave]) return;
                    var idCampo = 'reimp-' + clave;
                    var $grupo = $('<div></div>').toggleClass('rd-campo-ancho', CAMPOS_ANCHOS.indexOf(clave) !== -1);
                    $grupo.append($('<label class="rd-label"></label>').attr('for', idCampo).text(ETIQUETAS_CAMPO[clave]));
                    var $control;
                    if (clave === 'titulo' || clave === 'tribunal') {
                        $control = $('<textarea class="form-control" rows="3"></textarea>')
                            .val(clave === 'tribunal' ? String(valor).split('|').join('\n') : valor);
                    } else {
                        var tipo = 'text';
                        if (CAMPOS_FECHA.indexOf(clave) !== -1 && /^\d{4}-\d{2}-\d{2}$/.test(valor)) tipo = 'date';
                        else if (clave === 'horaEntrega' && /^\d{2}:\d{2}/.test(valor)) tipo = 'time';
                        else if (clave === 'calificacion') tipo = 'number';
                        $control = $('<input class="form-control">').attr('type', tipo).val(valor);
                    }
                    $control.attr({ id: idCampo, 'data-campo': clave });
                    $grupo.append($control);
                    $campos.append($grupo);
                });
                abrirVentana();
            }, 'json');
        });

        // Valores de la ventana con su nombre original (el tribunal vuelve a ir separado
        // por "|", como lo envía su pantalla)
        function valoresVentana() {
            var datos = {};
            $('#campos-reimprimir [data-campo]').each(function() {
                var clave = $(this).data('campo');
                var valor = $(this).val();
                if (clave === 'tribunal') {
                    valor = valor.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; }).join('|');
                }
                datos[clave] = valor;
            });
            return datos;
        }

        // Guarda lo corregido; despues(data) corre si se guardó bien
        function guardarDocumento(despues, alFallar) {
            $.post('ajax/certificado.ajax.php', { accion: 'editarDocumento', idDocumento: idEditando, datos: valoresVentana() }, function(data) {
                if (!data.success) {
                    if (alFallar) alFallar();
                    Swal.fire({ type: 'error', title: 'No se guardó', text: data.mensaje || 'Revise los datos' });
                    return;
                }
                cerrarVentana();
                refrescar();
                despues(data);
            }, 'json').fail(function() {
                if (alFallar) alFallar();
                Swal.fire({ type: 'error', title: 'No se pudo guardar' });
            });
        }

        $('#form-reimprimir').on('submit', function(e) { e.preventDefault(); });

        $('#btn-guardar-documento').on('click', function() {
            guardarDocumento(function(data) {
                Swal.fire({ type: data.cambio ? 'success' : 'info', title: data.mensaje, timer: 1600, showConfirmButton: false });
            });
        });

        // Guardar e imprimir: la pestaña del PDF se abre en el mismo clic (si se abriera
        // después de la respuesta, el navegador la bloquearía) y se llena al guardar
        $('#btn-guardar-imprimir').on('click', function() {
            var id = idEditando;
            var destino = abrirPestanaPdf();
            guardarDocumento(function() { imprimirGuardado(id, destino); }, function() { if (destino.ventana) destino.ventana.close(); });
        });

        // ===== Imprimir tal como está guardado =====
        function abrirPestanaPdf() {
            var nombre = 'rd_pdf_' + Date.now();
            return { nombre: nombre, ventana: window.open('', nombre) };
        }

        function imprimirGuardado(id, destino) {
            $.post('ajax/certificado.ajax.php', { accion: 'obtenerDocumento', idDocumento: id }, function(data) {
                if (!data.success || !data.documento.datos || !data.documento.rutaPdf) {
                    if (destino.ventana) destino.ventana.close();
                    Swal.fire({ type: 'error', title: 'No se puede imprimir', text: data.mensaje || 'El documento no tiene datos guardados.' });
                    return;
                }
                var $form = $('#form-imprimir-directo').empty().attr({ action: data.documento.rutaPdf, target: destino.nombre });
                $form.append($('<input type="hidden" name="idDocumento">').val(data.documento.idDocumento));
                Object.keys(data.documento.datos).forEach(function(clave) {
                    $form.append($('<input type="hidden">').attr('name', clave).val(data.documento.datos[clave]));
                });
                $form[0].submit();
            }, 'json');
        }

        $(document).on('click', '[data-imprimir]', function() {
            imprimirGuardado($(this).data('imprimir'), abrirPestanaPdf());
        });

        // ===== Aprobación del decano =====
        function cambiarEstado(id, aprobar) {
            $.post('ajax/certificado.ajax.php', { accion: aprobar ? 'aprobarDocumento' : 'quitarAprobacion', idDocumento: id }, function(data) {
                if (data.success) {
                    Swal.fire({ type: 'success', title: data.mensaje, timer: 1400, showConfirmButton: false });
                    refrescar();
                } else {
                    Swal.fire({ type: 'error', title: data.mensaje || 'No se pudo cambiar el estado' });
                }
            }, 'json');
        }

        $(document).on('click', '[data-aprobar]', function() {
            var id = $(this).data('aprobar');
            Swal.fire({
                type: 'question',
                title: '¿El decano aprobó este documento?',
                showCancelButton: true,
                confirmButtonText: 'Sí, aprobado',
                cancelButtonText: 'Cancelar'
            }).then(function(r) { if (r.value) cambiarEstado(id, true); });
        });

        $(document).on('click', '[data-quitar-aprobacion]', function() {
            var id = $(this).data('quitar-aprobacion');
            Swal.fire({
                type: 'warning',
                title: '¿Quitar la aprobación?',
                text: 'El documento volverá a quedar por aprobar.',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then(function(r) { if (r.value) cambiarEstado(id, false); });
        });

        // ===== Certificados sin documento: se registra solo el pago, en la inscripción abierta =====
        var catalogoSD = [];
        function cargarCatalogoSD(todos) {
            return $.post('ajax/certificado.ajax.php', { accion: 'catalogoSinDocumento', todos: todos ? 1 : '' }, function(data) {
                catalogoSD = (data && data.certificados) || [];
            }, 'json');
        }

        $('#btn-registrar-sd').on('click', function() {
            if (!estudianteActual) return;
            cargarCatalogoSD(false).done(function() {
                if (!catalogoSD.length) {
                    Swal.fire({ type: 'info', title: 'No hay certificados en la lista', text: 'El Administrador los agrega en Costos de certificados.' });
                    return;
                }
                var $sel = $('#sd-certificado').empty();
                if (catalogoSD.length > 1) $sel.append('<option value="">Elija el certificado</option>');
                catalogoSD.forEach(function(c) { $sel.append($('<option>').val(c.IdCertificadoSD).text(c.Nombre).attr('data-monto', c.Monto)); });
                $sel.trigger('change');
                // Cada registro queda en el programa abierto (un estudiante puede estar en dos)
                $('#ventana-sd-sub').text(estudianteActual.nombre + ' · ' + estudianteActual.programa + (estudianteActual.grado ? ' · ' + estudianteActual.grado : ''));
                $('#sd-voucher, #sd-voucher_largo, #sd-voucher_corto').val('');
                $('#sd-fecha-pago').val(new Date().toLocaleDateString('en-CA'));
                $('#ventana-sd').addClass('abierta');
            });
        });
        $('#sd-certificado').on('change', function() {
            var monto = parseFloat($(this).find('option:selected').attr('data-monto'));
            $('#sd-monto').val(monto > 0 ? monto.toFixed(2) : '');
        });
        $(document).on('click', '[data-cerrar-sd]', function() { $('#ventana-sd').removeClass('abierta'); });
        $('#ventana-sd').on('click', function(e) { if (e.target === this) $(this).removeClass('abierta'); });

        $('#btn-guardar-sd').on('click', function() {
            var datos = {
                accion: 'registrarSinDocumento',
                estudianteID: estudianteActual.estudianteID,
                programaID: estudianteActual.programaID,
                idCertificadoSD: $('#sd-certificado').val(),
                vaucher: $('#sd-voucher').val().trim(),
                monto: $('#sd-monto').val(),
                fechaPago: $('#sd-fecha-pago').val()
            };
            if (!datos.idCertificadoSD) { Swal.fire({ type: 'warning', title: 'Elija el certificado' }); return; }
            if (!datos.vaucher) { Swal.fire({ type: 'warning', title: 'Falta el N° de voucher' }); return; }
            if (!datos.fechaPago) { Swal.fire({ type: 'warning', title: 'Falta la fecha de pago del voucher' }); return; }
            var $btn = $(this).prop('disabled', true);
            $.post('ajax/certificado.ajax.php', datos, function(data) {
                if (!data.success) {
                    Swal.fire({ type: 'error', title: 'No se registró', text: data.mensaje || 'Revise los datos' });
                    return;
                }
                $('#ventana-sd').removeClass('abierta');
                Swal.fire({ type: 'success', title: data.mensaje, timer: 1400, showConfirmButton: false });
                docResaltado = data.idDocumento;  // la fila nueva destella y queda como "Último"
                refrescar();
            }, 'json').always(function() { $btn.prop('disabled', false); });
        });

        // ===== Llegando desde un certificado recién generado (registrodocumentos?emision=marca) =====
        // El PDF se registra en la otra pestaña: se pregunta por el documento hasta que esté
        // guardado; luego se abre su estudiante y se resalta la fila.
        var parametrosUrl = new URLSearchParams(window.location.search);
        var emision = parametrosUrl.get('emision');
        if (emision) {
            var intentos = 0;
            (function buscarEmision() {
                $.post('ajax/certificado.ajax.php', { accion: 'documentoPorEmision', token: emision }, function(data) {
                    if (data.success) {
                        var doc = data.documento;
                        docResaltado = doc.idDocumento;
                        $('#input-buscar-ci').val(doc.ci || '');
                        // La dirección queda con el estudiante: al recargar se vuelve a abrir
                        // (y su último documento sigue marcado)
                        history.replaceState(null, '', window.location.pathname + '?ci=' + encodeURIComponent(doc.ci || '') +
                            '&inscripcion=' + doc.estudianteID + '-' + doc.programaID);
                        buscarPorCI(doc.estudianteID + '-' + doc.programaID);
                    } else if (++intentos < 15) {
                        setTimeout(buscarEmision, 800);
                    } else {
                        Swal.fire({ type: 'info', title: 'El certificado se abrió en otra pestaña', text: 'Si no aparece aquí, búsquelo por el C.I. del estudiante.' });
                    }
                }, 'json');
            })();
        } else if (parametrosUrl.get('ci')) {
            // Recarga de la página: vuelve a abrir el mismo estudiante y programa
            $('#input-buscar-ci').val(parametrosUrl.get('ci'));
            buscarPorCI(parametrosUrl.get('inscripcion') || undefined);
        }

        // ===== Borrar un documento que aún no aprobó el decano =====
        $(document).on('click', '[data-borrar-documento]', function() {
            var $btn = $(this);
            var id = $btn.data('borrar-documento');
            var detalle = [$btn.attr('data-estudiante'), $btn.attr('data-voucher') ? 'voucher ' + $btn.attr('data-voucher') : '']
                .filter(Boolean).join(' · ');
            Swal.fire({
                type: 'warning',
                title: '¿Borrar este documento?',
                html: (detalle ? '<b>' + esc(detalle) + '</b><br>' : '') + 'Se quitará del registro de pagos. No se puede deshacer.',
                showCancelButton: true,
                confirmButtonColor: '#c53030',
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar'
            }).then(function(r) {
                if (!r.value) return;
                $.post('ajax/certificado.ajax.php', { accion: 'eliminarDocumento', idDocumento: id }, function(data) {
                    if (data.success) {
                        Swal.fire({ type: 'success', title: data.mensaje, timer: 1400, showConfirmButton: false });
                    } else {
                        Swal.fire({ type: 'error', title: data.mensaje || 'No se pudo borrar el documento' });
                    }
                    refrescar();
                }, 'json');
            });
        });
    });

    // El menú "Emitir certificado" se cierra al hacer clic fuera de él
    document.addEventListener('click', function(e) {
        var menu = document.querySelector('.rd-ir-certificado[open]');
        if (menu && !menu.contains(e.target)) menu.removeAttribute('open');
    });
    </script>
</body>
