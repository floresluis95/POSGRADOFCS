<?php
	$Validar = new FuncionesControladores();
	$Validar -> ValidarSessionControlador();
	date_default_timezone_set("America/La_Paz");
require_once 'controladores/inscripcionmodulo.controlador.php';
?>

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
    /* =========================================================
       Modal "Gestión de Pagos del Programa" (prefijo gp-)
       Todo queda acotado a #modalInscribirModulo para no afectar otras vistas.
       ========================================================= */
    #modalInscribirModulo {
        --gp-primario: #1e3c72;
        --gp-primario-suave: #eef2f9;
        --gp-texto: #1f2937;
        --gp-texto-2: #6b7280;
        --gp-borde: #e5e7eb;
        --gp-fondo: #f7f8fa;
        --gp-exito: #15803d;
        --gp-exito-suave: #ecfdf3;
        --gp-alerta: #b45309;
        --gp-alerta-suave: #fffbeb;
        --gp-peligro: #b91c1c;
    }
    #modalInscribirModulo .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        color: var(--gp-texto);
    }

    /* Encabezado */
    #modalInscribirModulo .gp-header {
        background: var(--gp-primario);
        color: #fff;
        border: 0;
        padding: 18px 24px;
        align-items: flex-start;
    }
    #modalInscribirModulo .gp-eyebrow {
        display: block;
        font-size: 11px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .65);
        margin-bottom: 2px;
    }
    #modalInscribirModulo .gp-header .modal-title {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.3;
    }
    #modalInscribirModulo .gp-header-sub {
        font-size: 13px;
        color: rgba(255, 255, 255, .8);
        margin-top: 2px;
    }
    #modalInscribirModulo .gp-header .close {
        color: #fff;
        opacity: .7;
        text-shadow: none;
        margin: -6px -8px -6px auto;
    }
    #modalInscribirModulo .gp-header .close:hover { opacity: 1; }

    /* Resumen + pestañas (zona fija bajo el encabezado) */
    #modalInscribirModulo .gp-top {
        background: #fff;
        border-bottom: 1px solid var(--gp-borde);
        padding: 16px 24px 0;
    }
    #modalInscribirModulo .gp-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    #modalInscribirModulo .gp-stat {
        background: var(--gp-fondo);
        border-radius: 8px;
        padding: 10px 14px;
    }
    #modalInscribirModulo .gp-stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gp-texto-2);
    }
    #modalInscribirModulo .gp-stat-valor {
        font-size: 18px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    #modalInscribirModulo .gp-stat-valor small { font-size: 12px; font-weight: 500; color: var(--gp-texto-2); }
    #modalInscribirModulo .gp-stat.is-pagado .gp-stat-valor { color: var(--gp-exito); }
    #modalInscribirModulo .gp-stat.is-saldo .gp-stat-valor { color: var(--gp-primario); }
    #modalInscribirModulo .gp-progreso {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        font-size: 12px;
        color: var(--gp-texto-2);
    }
    #modalInscribirModulo .gp-progreso .progress {
        flex: 1;
        height: 6px;
        border-radius: 3px;
        background: var(--gp-borde);
    }
    #modalInscribirModulo .gp-progreso .progress-bar { background: var(--gp-primario); }
    #modalInscribirModulo .gp-progreso .progress-bar.is-completo { background: var(--gp-exito); }
    #modalInscribirModulo .gp-aviso {
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        background: var(--gp-alerta-suave);
        color: var(--gp-alerta);
    }

    #modalInscribirModulo .gp-tabs {
        margin-top: 14px;
        border: 0;
        gap: 4px;
    }
    #modalInscribirModulo .gp-tabs .nav-link {
        border: 0;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 500;
        color: var(--gp-texto-2);
        background: transparent;
    }
    #modalInscribirModulo .gp-tabs .nav-link i { margin-right: 6px; }
    #modalInscribirModulo .gp-tabs .nav-link:hover { color: var(--gp-texto); }
    #modalInscribirModulo .gp-tabs .nav-link.active {
        color: var(--gp-primario);
        border-bottom-color: var(--gp-primario);
        font-weight: 600;
    }

    /* Cuerpo */
    #modalInscribirModulo .modal-body {
        background: var(--gp-fondo);
        padding: 20px 24px;
    }
    #modalInscribirModulo .gp-cargando {
        text-align: center;
        color: var(--gp-texto-2);
        padding: 24px 0;
        font-size: 13px;
    }
    #modalInscribirModulo .gp-section {
        background: #fff;
        border: 1px solid var(--gp-borde);
        border-radius: 10px;
        padding: 18px 20px;
        margin-bottom: 16px;
    }
    #modalInscribirModulo .tab-pane > :last-child { margin-bottom: 0; }
    #modalInscribirModulo .gp-section-flush { padding: 0; overflow: hidden; }
    #modalInscribirModulo .gp-section-flush .gp-section-title { padding: 14px 20px 10px; margin: 0; }
    #modalInscribirModulo .gp-section-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gp-texto-2);
        margin-bottom: 14px;
    }
    #modalInscribirModulo .gp-section-title i { margin-right: 6px; }
    #modalInscribirModulo .gp-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--gp-texto);
        margin-bottom: 4px;
    }
    #modalInscribirModulo .gp-req { color: var(--gp-peligro); }
    #modalInscribirModulo .gp-ayuda { font-size: 12px; color: var(--gp-texto-2); margin-top: 4px; }
    #modalInscribirModulo .form-control {
        border-color: var(--gp-borde);
        border-radius: 6px;
        font-size: 13px;
    }
    #modalInscribirModulo .form-control:focus {
        border-color: var(--gp-primario);
        box-shadow: 0 0 0 3px rgba(30, 60, 114, .12);
    }
    #modalInscribirModulo .gp-file { font-size: 12px; }
    #modalInscribirModulo .gp-file::file-selector-button {
        border: 1px solid var(--gp-borde);
        background: #fff;
        border-radius: 6px;
        padding: 6px 12px;
        margin-right: 10px;
        cursor: pointer;
    }
    #modalInscribirModulo .gp-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        padding-top: 4px;
    }

    /* Monto a generar */
    #modalInscribirModulo .gp-monto {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    /* Módulo(s) que cubre la cuota (ordenpagomodulos.js): casillas en dos columnas */
    #modalInscribirModulo .op-modulos-cuota { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 12px; max-height: 260px; overflow-y: auto; }
    #modalInscribirModulo .op-modulo { display: flex; align-items: flex-start; gap: 8px; margin: 0; padding: 7px 10px; border: 1px solid var(--gp-borde); border-radius: 8px; font-size: 12.5px; cursor: pointer; background: #fff; }
    #modalInscribirModulo .op-modulo input { margin-top: 2px; width: 15px; height: 15px; accent-color: #1e3c72; flex-shrink: 0; }
    #modalInscribirModulo .op-modulo:has(input:checked:not(:disabled)) { background: #eef3fb; border-color: #c9d6ee; }
    #modalInscribirModulo .op-modulo.is-cubierto { background: #f6f7f9; color: #6b7280; cursor: default; }
    @media (max-width: 575.98px) { #modalInscribirModulo .op-modulos-cuota { grid-template-columns: 1fr; } }
    #modalInscribirModulo .gp-monto-input {
        display: flex;
        align-items: center;
        border: 1px solid var(--gp-borde);
        border-radius: 8px;
        padding: 4px 12px;
        background: #fff;
    }
    #modalInscribirModulo .gp-monto-input:focus-within {
        border-color: var(--gp-primario);
        box-shadow: 0 0 0 3px rgba(30, 60, 114, .12);
    }
    #modalInscribirModulo .gp-monto-input span { font-weight: 600; color: var(--gp-texto-2); margin-right: 6px; }
    #modalInscribirModulo .gp-monto-input input {
        border: 0;
        outline: 0;
        width: 150px;
        font-size: 22px;
        font-weight: 600;
        color: var(--gp-primario);
        text-align: right;
        font-variant-numeric: tabular-nums;
        background: transparent;
    }
    #modalInscribirModulo .gp-monto-fijo {
        font-size: 22px;
        font-weight: 600;
        color: var(--gp-primario);
        font-variant-numeric: tabular-nums;
    }

    /* Orden pendiente */
    #modalInscribirModulo .gp-orden {
        border-left: 3px solid #f59e0b;
    }
    #modalInscribirModulo .gp-orden-fila {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    #modalInscribirModulo .gp-orden-num { font-weight: 600; }
    #modalInscribirModulo .gp-orden-meta { font-size: 12px; color: var(--gp-texto-2); margin-top: 2px; }
    #modalInscribirModulo .gp-orden-monto {
        font-size: 22px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }
    #modalInscribirModulo .gp-orden .gp-actions {
        justify-content: flex-start;
        border-top: 1px solid var(--gp-borde);
        margin-top: 14px;
        padding-top: 14px;
    }
    #modalInscribirModulo .gp-estado-vacio {
        text-align: center;
        padding: 28px 16px;
        color: var(--gp-texto-2);
    }
    #modalInscribirModulo .gp-estado-vacio i { font-size: 28px; margin-bottom: 10px; display: block; }
    #modalInscribirModulo .gp-estado-vacio h6 { color: var(--gp-texto); font-weight: 600; margin-bottom: 4px; }
    #modalInscribirModulo .gp-estado-vacio.is-exito i { color: var(--gp-exito); }
    #modalInscribirModulo .gp-estado-vacio.is-alerta i { color: #f59e0b; }

    /* Chips de estado */
    #modalInscribirModulo .gp-chip {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        background: var(--gp-fondo);
        color: var(--gp-texto-2);
    }
    #modalInscribirModulo .gp-chip.is-pendiente { background: var(--gp-alerta-suave); color: var(--gp-alerta); }
    #modalInscribirModulo .gp-chip.is-pagado { background: var(--gp-exito-suave); color: var(--gp-exito); }
    #modalInscribirModulo .gp-chip.is-rechazado { background: #fef2f2; color: var(--gp-peligro); }

    /* Mensajes dentro de las pestañas (orden pendiente, sin orden, etc.) */
    #modalInscribirModulo .gp-nota {
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        margin-bottom: 16px;
        background: var(--gp-primario-suave);
        color: var(--gp-primario);
    }
    #modalInscribirModulo .gp-nota.is-neutra { background: #fff; border: 1px dashed var(--gp-borde); color: var(--gp-texto-2); }
    #modalInscribirModulo .gp-nota.is-exito { background: var(--gp-exito-suave); color: var(--gp-exito); }

    /* Tablas */
    #modalInscribirModulo .gp-table { font-size: 13px; }
    #modalInscribirModulo .gp-table thead th {
        background: var(--gp-fondo);
        border-top: 1px solid var(--gp-borde);
        border-bottom: 1px solid var(--gp-borde);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--gp-texto-2);
        padding: 8px 12px;
    }
    #modalInscribirModulo .gp-table td {
        border-top: 1px solid var(--gp-borde);
        padding: 10px 12px;
        vertical-align: middle;
    }
    #modalInscribirModulo .gp-table th:first-child,
    #modalInscribirModulo .gp-table td:first-child { padding-left: 20px; }
    #modalInscribirModulo .gp-table th:last-child,
    #modalInscribirModulo .gp-table td:last-child { padding-right: 20px; }
    #modalInscribirModulo .gp-table .text-right { font-variant-numeric: tabular-nums; }
    #modalInscribirModulo .gp-table td { white-space: nowrap; }
    #modalInscribirModulo .gp-vacio { text-align: center; color: var(--gp-texto-2); padding: 20px !important; white-space: normal; }

    /* Botones y enlaces con el color institucional (el tema usa otro primario) */
    #modalInscribirModulo a:not(.btn):not(.nav-link) { color: var(--gp-primario); }
    #modalInscribirModulo .btn-link { color: var(--gp-primario); }
    #modalInscribirModulo .btn-link.text-danger,
    #modalInscribirModulo .text-danger { color: var(--gp-peligro) !important; }
    #modalInscribirModulo .btn-primary {
        background: var(--gp-primario);
        border-color: var(--gp-primario);
        color: #fff;
    }
    #modalInscribirModulo .btn-primary:hover:not(:disabled),
    #modalInscribirModulo .btn-primary:focus {
        background: #16305c;
        border-color: #16305c;
        color: #fff;
    }
    #modalInscribirModulo .btn-primary:disabled { opacity: .45; }
    #modalInscribirModulo .btn-outline-secondary {
        border-color: var(--gp-borde);
        color: var(--gp-texto);
        background: #fff;
    }
    #modalInscribirModulo .btn-outline-secondary:hover { background: var(--gp-fondo); color: var(--gp-texto); }
    #modalInscribirModulo .gp-footer .btn-light { border: 1px solid var(--gp-borde); background: #fff; }

    #modalInscribirModulo .gp-footer {
        background: #fff;
        border-top: 1px solid var(--gp-borde);
        padding: 12px 24px;
    }

    @media (max-width: 575.98px) {
        #modalInscribirModulo .gp-header,
        #modalInscribirModulo .gp-top,
        #modalInscribirModulo .modal-body { padding-left: 16px; padding-right: 16px; }
        #modalInscribirModulo .gp-stats { grid-template-columns: 1fr; gap: 8px; }
        #modalInscribirModulo .gp-tabs { flex-wrap: nowrap; overflow-x: auto; }
        #modalInscribirModulo .gp-tabs .nav-link { white-space: nowrap; }
    }

    /* =========================================================
       PÁGINA DE MATRICULADOS (prefijo mt-): filtros y lista.
       Mismo lenguaje visual que Programas, Reporte de Notas y Estudiantes.
       Las filas las arma InscripcionModuloControladores (badges con estilo
       en línea: por eso algunos ajustes llevan !important).
       ========================================================= */
    .mt {
        --mt-primario: #1e3c72;
        --mt-primario-suave: #eef2f9;
        --mt-texto: #1f2937;
        --mt-texto-2: #64748b;
        --mt-borde: #e3e7ef;
    }

    .mt-card {
        background: #fff;
        border: 1px solid var(--mt-borde);
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
        overflow: hidden;
        margin-bottom: 18px;
    }
    .mt-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px 16px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--mt-borde);
    }
    .mt-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--mt-texto); }
    .mt-card-head h3 i { color: var(--mt-primario); margin-right: 4px; }
    .mt-card-head p { font-size: 12.5px; color: var(--mt-texto-2); margin: 3px 0 0; }
    .mt-card-head .btn-abrir-panel { height: 40px; display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; }
    .mt-card-body { padding: 20px 24px; }

    .mt-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--mt-texto);
        margin-bottom: 6px;
    }
    .mt-label i { color: var(--mt-primario); width: 14px; }
    .mt .btn-abrir-panel.btn-block { height: 38px; display: flex; align-items: center; justify-content: center; gap: 6px; }

    /* ---------- Tabla ---------- */
    .mt-tabla {
        margin-bottom: 0;
        font-size: 13px;
        border: 1px solid var(--mt-borde);
        border-radius: 10px;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
    }
    .mt-tabla thead th {
        background: #f8fafc;
        color: var(--mt-texto-2);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-top: 0;
        border-bottom: 1px solid var(--mt-borde);
        vertical-align: middle;
        padding: 12px 10px;
    }
    .mt-tabla td {
        vertical-align: middle;
        border-color: #eef1f6;
        color: var(--mt-texto);
        padding: 11px 10px;
    }
    .mt-tabla tbody tr { transition: background-color .15s ease; }
    .mt-tabla tbody tr:hover { background-color: #f8fafc; }

    /* Etiquetas de la fila (tipo de pago, estado): pastillas suaves */
    .mt-tabla .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px !important;
        border-radius: 999px;
        font-size: 11px !important;
        font-weight: 600;
    }
    .mt-tabla .badge-success { background: #e7f6ee; color: #2f855a; }
    .mt-tabla .badge-warning { background: #fff6e0; color: #b7791f; }
    .mt-tabla .badge-danger  { background: #fdecec; color: #c53030; }
    .mt-tabla .badge-info,
    .mt-tabla .badge-primary { background: var(--mt-primario-suave); color: var(--mt-primario); }

    /* Menú de acciones de cada fila */
    .dropdown-menu {
        border: 1px solid var(--mt-borde, #e3e7ef);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        padding: 6px;
    }
    .dropdown-item {
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 13px;
    }
    .dropdown-item:hover { background-color: #eef2f9; color: #1e3c72; }
    .dropdown-item i { width: 20px; margin-right: 8px; color: #1e3c72; }

    .btn-gestionar-pagos { white-space: nowrap; }

    /* ---------- Ventanas secundarias (detalles, módulos, foto, plan grupal) ---------- */
    .mt-modal-head {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-bottom: 0;
    }
    .mt-modal-head .modal-title,
    .mt-modal-head .modal-title i { color: #fff !important; font-weight: 700; }
    .mt-modal-head .close,
    .mt-modal-head .close span { color: #fff !important; opacity: .9; text-shadow: none; }
    #modalVerDetalles .modal-content,
    #modalVerModulos .modal-content,
    #modalFotoVoucher .modal-content,
    #modalPlanGrupal .modal-content { border: 0; border-radius: 14px; overflow: hidden; }

    .modal-header.bg-primary {
        background-color: #1e3c72 !important;
    }

    #detallesModulo {
        background-color: #f8f9fa;
        border-left: 4px solid #1e3c72;
    }

    /* Modal Ver Detalles: cabecera del estudiante y secciones Contacto / Programa / Matrícula */
    #modalVerDetalles .alert-info {
        background: #eef2f9;
        border: 0;
        border-left: 4px solid #1e3c72;
        border-radius: 10px;
        color: #1f2937;
    }
    /* Etiquetas del detalle (estado, tipo de pago): pastillas suaves como en la tabla */
    #modalVerDetalles .badge { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    #modalVerDetalles .badge-success { background: #e7f6ee; color: #2f855a; }
    #modalVerDetalles .badge-warning { background: #fff6e0; color: #b7791f; }
    #modalVerDetalles .badge-danger  { background: #fdecec; color: #c53030; }

    #modalVerDetalles .detalle-seccion {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #1e3c72;
        border-bottom: 1px solid #e3e7ef;
        padding-bottom: 6px;
        margin: 16px 0 10px;
    }
    #modalVerDetalles .detalle-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #64748b;
        margin-bottom: 0;
    }
    #modalVerDetalles .detalle-valor {
        margin-bottom: 10px;
        color: #1f2937;
    }

    /* Plan grupal: lista de estudiantes con el mismo encabezado claro */
    #modalPlanGrupal .table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    /* Badge para mostrar información */
    .badge-custom {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        .mt-card-head { flex-direction: column; align-items: flex-start; }
        .mt-card-head, .mt-card-body { padding-left: 16px; padding-right: 16px; }
    }

    /* ====================================
       ESTILOS PARA TARJETAS DE MÓDULOS
       ==================================== */
    .modulos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .modulo-card {
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .modulo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Tarjeta de módulo PAGADO (verde) */
    .modulo-card.pagado {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #f1f9f4 100%);
        cursor: default;
    }

    .modulo-card.pagado::before {
        content: "✓";
        position: absolute;
        top: 8px;
        right: 8px;
        background: #28a745;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }

    .modulo-card.pagado:hover {
        transform: none;
        cursor: not-allowed;
    }

    /* Tarjeta de módulo PENDIENTE (rojo) */
    .modulo-card.pendiente {
        border-color: #dc3545;
        background: linear-gradient(135deg, #f8d7da 0%, #fff5f5 100%);
    }

    .modulo-card.pendiente::before {
        content: "!";
        position: absolute;
        top: 8px;
        right: 8px;
        background: #dc3545;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }

    .modulo-card.pendiente:hover {
        border-color: #bd2130;
        background: linear-gradient(135deg, #f5c6cb 0%, #ffe0e3 100%);
    }

    /* Tarjeta seleccionada */
    .modulo-card.seleccionado {
        border-color: #007bff;
        background: linear-gradient(135deg, #cce5ff 0%, #e7f3ff 100%);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.3);
    }

    .modulo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .modulo-card-codigo {
        background: #1e3c72;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }

    .modulo-card-titulo {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin: 8px 0;
        padding-right: 30px;
        line-height: 1.2;
        min-height: 32px;
    }

    .modulo-card-docente {
        font-size: 11px;
        color: #666;
        margin-top: 6px;
        padding: 4px 0;
    }

    .modulo-card-docente i {
        color: #1e3c72;
        margin-right: 4px;
    }

    /* Información de pago (solo en tarjetas pagadas) */
    .modulo-card-pago-info {
        margin-top: 8px;
        padding: 6px;
        background: rgba(40, 167, 69, 0.1);
        border-radius: 4px;
        font-size: 10px;
    }

    .modulo-card-pago-info p {
        margin: 2px 0;
        color: #155724;
    }

    /* Scroll personalizado */
    .modulos-grid::-webkit-scrollbar {
        width: 8px;
    }

    .modulos-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .modulos-grid::-webkit-scrollbar-thumb {
        background: #1e3c72;
        border-radius: 10px;
    }

    .modulos-grid::-webkit-scrollbar-thumb:hover {
        background: #5568d3;
    }

    /* Responsive para tarjetas */
    @media (max-width: 768px) {
        .modulos-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .modulo-card-titulo {
            font-size: 14px;
        }
    }

    /* Checkbox en tarjetas */
    .modulo-checkbox {
        pointer-events: none;
    }

    .modulo-card.seleccionable {
        cursor: pointer;
    }

    /* Estilos para Select2 */
    .select2-container {
        width: 100% !important;
        display: block !important;
    }

    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        /* El bundle del tema le pone padding vertical propio a este mismo
           elemento; hay que anular los cuatro lados (no solo left/right)
           para que no se siga sumando y el texto no se salga de la caja. */
        padding: 0 28px 0 12px !important;
        color: #495057 !important;
        font-size: 14px !important;
        display: block !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        /* El bundle del tema posiciona esta flecha con "top: 50%" sin
           transform, así que un top fijo la deja corrida hacia arriba;
           top+translateY(-50%) la centra sin importar el alto real de la caja. */
        top: 50% !important;
        transform: translateY(-50%) !important;
        height: auto !important;
        right: 5px !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1e3c72 !important;
        color: white !important;
    }
    /* =========================================================
       REQUISITOS DE MATRICULACIÓN (prefijo rq- y .mt-requisitos)
       ========================================================= */
    /* Celda de la tabla: pastilla que abre la ventana de requisitos */
    .mt-requisitos {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .mt-requisitos.is-completo { background: #e7f6ee; color: #2f855a; }
    .mt-requisitos.is-pendiente { background: #fff6e0; color: #b7791f; }
    .mt-requisitos:hover { border-color: currentColor; box-shadow: 0 2px 6px rgba(15, 23, 42, .1); }

    #modalRequisitos .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    .rq-barra { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .rq-contador { font-size: 13px; color: #b7791f; }
    .rq-contador.is-completo { color: #2f855a; }
    .rq-lista { display: flex; flex-direction: column; gap: 8px; }
    .rq-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
        align-items: center;
        gap: 6px 14px;
        padding: 10px 14px;
        border: 1px solid #e3e7ef;
        border-radius: 12px;
        background: #fff;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .rq-item.is-entregado { background: #f3faf6; border-color: #c6e9d4; }
    .rq-check { display: flex; align-items: center; gap: 10px; margin: 0; cursor: pointer; }
    .rq-check input { position: absolute; opacity: 0; pointer-events: none; }
    .rq-caja {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: transparent;
        font-size: 11px;
        transition: all .15s ease;
    }
    .rq-check input:checked + .rq-caja { background: #2f855a; border-color: #2f855a; color: #fff; }
    .rq-check input:focus-visible + .rq-caja { box-shadow: 0 0 0 3px rgba(30, 60, 114, .2); }
    .rq-nombre { font-size: 13.5px; font-weight: 600; color: #1f2937; }
    .rq-nombre small { color: #64748b; font-weight: 500; }
    .rq-datos { display: flex; gap: 8px; }
    .rq-datos .rq-fecha { max-width: 160px; }
    .rq-datos .form-control { height: 36px; font-size: 13px; border-radius: 8px; border: 1px solid #d8dde8; }
    .rq-registro { grid-column: 1 / -1; font-size: 11.5px; color: #64748b; padding-left: 32px; }
    .rq-ayuda { margin: 14px 0 0; padding: 10px 14px; background: #eef2f9; border-radius: 10px; font-size: 12.5px; color: #334155; }
    .rq-ayuda i { color: #1e3c72; margin-right: 4px; }
    .rq-vacio { text-align: center; padding: 24px; color: #64748b; }

    @media (max-width: 767.98px) {
        .rq-item { grid-template-columns: 1fr; }
        .rq-datos { flex-direction: column; }
        .rq-datos .rq-fecha { max-width: none; }
    }

</style>
  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="panel">
        <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
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

	<?php
			$NavBar = new FuncionesControladores();
			$NavBar -> NavBarControlador();
		?>
        <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
		<?php
			$Sidebar = new FuncionesControladores();
			$Sidebar -> SidebarControlador();
		?>

<!-- Content -->
<div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

    <!-- Subheader -->
    <div class="kt-subheader kt-grid__item" id="kt_subheader">
      <div class="kt-container">
        <div class="kt-subheader__main">
          <h2>MATRICULADOS</h2>
          <span class="kt-subheader__separator kt-hidden"></span>
          <div class="kt-subheader__breadcrumbs">
            <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
            <span class="kt-subheader__breadcrumbs-separator"></span>
            <a href="#" class="kt-subheader__breadcrumbs-link">
              <h3>ESTUDIANTES MATRICULADOS</h3>
            </a>
          </div>
        </div>
        <div class="kt-subheader__toolbar">
          <div class="kt-subheader__wrapper">
            <div id="lafecha" style="font-size:13pt"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">
      <div class="mt">

        <!-- Filtros -->
        <div class="mt-card">
            <div class="mt-card-head">
                <div>
                    <h3><i class="fa fa-filter"></i> Filtrar matriculados</h3>
                    <p>Acote la lista por sede, programa o estado de la inscripción.</p>
                </div>
            </div>
            <div class="mt-card-body">
                <div class="row align-items-end">
                    <div class="col-xl-2 col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <label class="mt-label" for="selectSedeFiltro"><i class="fa fa-map-marker-alt"></i> Sede</label>
                        <select class="form-control select2-uniforme" id="selectSedeFiltro" style="width: 100%;">
                            <option value="">Todas las sedes...</option>
                            <?php
                            require_once 'modelos/programa.modelo.php';
                            $sedesMatriculados = ProgramasModelos::ListarSedesModelo();
                            foreach ($sedesMatriculados as $sedeOpcion) {
                                $sedeValor = htmlspecialchars($sedeOpcion, ENT_QUOTES);
                                echo '<option value="' . $sedeValor . '">' . $sedeValor . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                        <label class="mt-label" for="selectProgramaFiltro"><i class="fa fa-university"></i> Programa</label>
                        <select class="form-control select2-uniforme" id="selectProgramaFiltro" style="width: 100%;">
                            <option value="">Todos los programas...</option>
                            <?php
                            $programas = ProgramasModelos::ListaProgramaModelo();
                            foreach ($programas as $prog) {
                                $programaID = htmlspecialchars($prog['ProgramaID'] ?? '', ENT_QUOTES);
                                $nombrePrograma = htmlspecialchars($prog['NombrePrograma'] ?? '', ENT_QUOTES);
                                $codigo = htmlspecialchars($prog['Codigo'] ?? '', ENT_QUOTES);
                                $grado = htmlspecialchars($prog['GradoAcademico'] ?? '', ENT_QUOTES);

                                echo '<option value="' . $programaID . '">';
                                echo $nombrePrograma . ' - ' . $grado . ' (' . $codigo . ')';
                                echo '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-xl-0">
                        <label class="mt-label" for="selectEstadoFiltro"><i class="fa fa-user-check"></i> Estado</label>
                        <select class="form-control select2-uniforme" id="selectEstadoFiltro" style="width: 100%;">
                            <option value="ACTIVO" selected>Activos</option>
                            <option value="ABANDONO">Abandonos</option>
                            <option value="TODOS">Todos</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 mb-3 mb-md-0">
                        <label class="mt-label" for="selectRequisitosFiltro"><i class="fa fa-clipboard-check"></i> Requisitos</label>
                        <select class="form-control select2-uniforme" id="selectRequisitosFiltro" style="width: 100%;">
                            <option value="" selected>Todos</option>
                            <option value="INCOMPLETOS">Incompletos</option>
                            <option value="COMPLETOS">Completos</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <button type="button" class="btn btn-abrir-panel btn-block" id="btnLimpiarFiltro">
                            <i class="fa fa-times"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Estudiantes Matriculados -->
        <div class="mt-card">
            <div class="mt-card-head">
                <div>
                    <h3><i class="fa fa-user-graduate"></i> Estudiantes matriculados</h3>
                    <p>Pulse <strong>Pagos</strong> para gestionar la matrícula, las cuotas y las clínicas de cada estudiante.</p>
                </div>
                <button type="button" class="btn btn-abrir-panel" id="btnAbrirPlanGrupal">
                    <i class="fa fa-users"></i> Plan grupal
                </button>
            </div>

            <div class="mt-card-body">
                <div class="table-responsive">
                    <table class="table mt-tabla" id="tablaMatriculados">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Estudiante</th>
                                <th>Programa</th>
                                <th class="text-center" style="width: 100px;">Sede</th>
                                <th class="text-center" style="width: 150px;">Pago</th>
                                <th class="text-center" style="width: 110px;">Requisitos</th>
                                <th class="text-center" style="width: 110px;">Fecha inscripción</th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMatriculadosBody">
                            <?php
                                $listar = new InscripcionModuloControladores();
                                $listar->ListarEstudiantesMatriculadosControlador();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

      </div>
    </div>
</div>

<!-- Modal Ver Detalles del Estudiante Matriculado -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1" role="dialog" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header mt-modal-head">
                <h5 class="modal-title text-white" id="modalVerDetallesLabel">
                    <i class="fa fa-user-circle"></i> Detalles del Estudiante Matriculado
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- La tabla solo identifica al estudiante (nombre, CI, programa, sede,
                 tipo de pago, fecha). Aquí va lo que NO está en la tabla: contacto,
                 datos administrativos del programa y el desglose de la matrícula. -->
            <div class="modal-body">
                <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0"><i class="fa fa-user"></i> <strong id="detalleEstudianteNombre"></strong></h5>
                        <small><i class="fa fa-id-card"></i> CI: <span id="detalleEstudianteCI"></span></small>
                    </div>
                    <span id="detalleEstado" class="badge"></span>
                </div>

                <h6 class="detalle-seccion"><i class="fa fa-address-book"></i> Contacto</h6>
                <div class="row">
                    <div class="col-md-4"><label class="detalle-label">Celular</label><p id="detalleCelular" class="detalle-valor"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Correo</label><p id="detalleCorreo" class="detalle-valor text-break"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Profesión</label><p id="detalleProfesion" class="detalle-valor"></p></div>
                </div>

                <h6 class="detalle-seccion"><i class="fa fa-graduation-cap"></i> Programa</h6>
                <div class="row">
                    <div class="col-md-12"><p id="detalleProgramaNombre" class="detalle-valor font-weight-bold"></p></div>
                    <div class="col-md-3"><label class="detalle-label">Código</label><p id="detalleCodigo" class="detalle-valor"></p></div>
                    <div class="col-md-3"><label class="detalle-label">Versión</label><p id="detalleVersion" class="detalle-valor"></p></div>
                    <div class="col-md-3"><label class="detalle-label">Sede</label><p id="detalleSede" class="detalle-valor"></p></div>
                    <div class="col-md-3"><label class="detalle-label">N° Trámite</label><p id="detalleTramite" class="detalle-valor"></p></div>
                </div>

                <h6 class="detalle-seccion"><i class="fa fa-money"></i> Matrícula</h6>
                <div class="row">
                    <div class="col-md-4"><label class="detalle-label">Fecha de inscripción</label><p id="detalleFecha" class="detalle-valor"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Tipo de pago</label><p id="detalleTipoPago" class="detalle-valor"></p></div>
                    <div class="col-md-4"><label class="detalle-label">N° Voucher</label><p id="detalleVoucher" class="detalle-valor"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Costo de matrícula</label><p id="detalleCosto" class="detalle-valor text-primary font-weight-bold"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Descuento</label><p id="detalleDescuento" class="detalle-valor"></p></div>
                    <div class="col-md-4"><label class="detalle-label">Monto pagado</label><p id="detalleMontoPagado" class="detalle-valor font-weight-bold"></p></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Módulos Inscritos -->
<div class="modal fade" id="modalVerModulos" tabindex="-1" role="dialog" aria-labelledby="modalVerModulosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header mt-modal-head">
                <h5 class="modal-title text-white" id="modalVerModulosLabel">
                    <i class="fa fa-list"></i> Módulos Inscritos - <span id="modulosEstudianteNombre"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="contenidoModulos">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Cargando módulos...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal unificado: Plan de Pago del Programa + Registrar Pago de Módulo -->
<div class="modal fade" id="modalInscribirModulo" tabindex="-1" role="dialog" aria-labelledby="modalInscribirModuloLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <!-- Encabezado: estudiante y programa (fijo, sin importar la pestaña) -->
            <div class="modal-header gp-header">
                <div class="gp-header-info">
                    <span class="gp-eyebrow">Gestión de pagos del programa</span>
                    <h5 class="modal-title" id="modalInscribirModuloLabel"><span id="infoEstudianteNombre"></span></h5>
                    <div class="gp-header-sub"><i class="fa fa-graduation-cap"></i> <span id="infoProgramaNombre"></span></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Resumen del programa (costo / pagado / saldo) y pestañas: fijos arriba del contenido -->
            <div class="gp-top">
                <div id="resumenPagoPrograma">
                    <div class="gp-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando resumen...</div>
                </div>

                <!-- Pestañas: Orden de Pago (paso inicial) / Cuotas / Clínicas -->
                <ul class="nav gp-tabs" id="tabsGestionPagos" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-orden-pago-link" data-toggle="tab" href="#tab-orden-pago" role="tab">
                            <i class="fa fa-file-invoice"></i> Orden de pago
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-pago-modulos-link" data-toggle="tab" href="#tab-pago-modulos" role="tab">
                            <i class="fa fa-receipt"></i> Cuotas del programa
                        </a>
                    </li>
                    <!-- Solo se muestra si el programa tiene una cuenta CLINICA activa (ordenpagoclinica.js) -->
                    <li class="nav-item" id="tab-clinica-item" style="display:none;">
                        <a class="nav-link" id="tab-clinica-link" data-toggle="tab" href="#tab-clinica" role="tab">
                            <i class="fa fa-stethoscope"></i> Clínicas
                        </a>
                    </li>
                </ul>
            </div>

            <div class="modal-body">

                <input type="hidden" name="estudianteID" id="estudianteID">
                <input type="hidden" name="programaID" id="programaID">
                <input type="hidden" name="idinscripcion" id="idinscripcion">

                <div class="tab-content">

                    <!-- Pestaña: Orden de Pago (se emite ANTES de pagar, para que el estudiante pague y luego suba el voucher) -->
                    <div class="tab-pane fade show active" id="tab-orden-pago" role="tabpanel">
                        <div id="ordenPagoContenido">
                            <div class="gp-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>
                        </div>
                    </div>

                    <!-- Pestaña: Cuotas del Programa (antes "Pago de Módulos"; las cuotas ya no
                         están ligadas a módulos, su número y monto son libres) -->
                    <div class="tab-pane fade" id="tab-pago-modulos" role="tabpanel">

                        <div id="cuotaPendienteInfo"></div>

                        <div class="gp-section">
                            <div class="gp-section-title"><i class="fa fa-upload"></i> Registrar voucher de pago</div>
                            <form id="formPagoModulo" enctype="multipart/form-data">
                                <input type="hidden" name="montoCuota" id="montoCuota">

                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label class="gp-label" for="fechaPago">Fecha de pago <span class="gp-req">*</span></label>
                                        <input type="date" class="form-control" name="fechaPago" id="fechaPago" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="gp-label" for="numeroVaucher">N° de voucher / comprobante <span class="gp-req">*</span></label>
                                        <input type="text" class="form-control" name="numeroVaucher" id="numeroVaucher"
                                               placeholder="Ej: 123456" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="gp-label" for="fmodulo">Foto o archivo del voucher <span class="gp-req">*</span></label>
                                        <input type="file" class="form-control-file gp-file" name="fmodulo" id="fmodulo"
                                               accept="image/*,.pdf" required>
                                        <div id="previewFotoVoucher" class="mt-2"></div>
                                    </div>
                                </div>

                                <div class="gp-actions">
                                    <button type="submit" id="btnGuardarPagoCuota" class="btn btn-primary" disabled>
                                        <i class="fa fa-save"></i> Guardar pago
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="gp-section gp-section-flush">
                            <div class="gp-section-title"><i class="fa fa-history"></i> Historial de cuotas</div>
                            <div class="table-responsive">
                                <table class="table gp-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:70px;">N°</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-center">Fecha</th>
                                            <th>Voucher</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCuotasBody">
                                        <tr><td colspan="5" class="gp-vacio">Sin cuotas registradas</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña: Clínicas (orden de pago de monto libre a la cuenta CLINICA del
                         programa; se confirma registrando su voucher). Contenido en ordenpagoclinica.js -->
                    <div class="tab-pane fade" id="tab-clinica" role="tabpanel">
                        <div class="gp-nota is-neutra">
                            <i class="fa fa-info-circle"></i>
                            <span>Los pagos de clínica son independientes: no se suman al costo, a lo pagado ni al saldo de matrícula y cuotas del programa.</span>
                        </div>

                        <div id="clinicaContenido"></div>

                        <div class="gp-section gp-section-flush">
                            <div class="gp-section-title d-flex justify-content-between">
                                <span><i class="fa fa-history"></i> Historial de clínicas</span>
                                <span id="clinicaTotalPagado"></span>
                            </div>
                            <div class="table-responsive">
                                <table class="table gp-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>N° Orden</th>
                                            <th class="text-right">Monto</th>
                                            <th class="text-center">Fecha pago</th>
                                            <th>Voucher</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center" style="width:60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaClinicasBody">
                                        <tr><td colspan="6" class="gp-vacio">Sin clínicas registradas</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer gp-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Foto del voucher (vista previa antes de guardar, o del historial de
     cuotas ya registradas). Tamaño de imagen fijo por CSS para que todas se vean
     igual sin importar su resolución original. -->
<div class="modal fade" id="modalFotoVoucher" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header mt-modal-head">
                <h5 class="modal-title"><i class="fa fa-image"></i> Foto del voucher</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imagenVoucherGrande" src="" alt="Foto del voucher" style="width:500px; max-width:100%; height:500px; object-fit:contain; border-radius:10px; background:#f8f9fa;">
            </div>
        </div>
    </div>
</div>

<!-- Modal: Plan Grupal (seleccionar a los estudiantes que entran en el plan) -->
<div class="modal fade" id="modalPlanGrupal" tabindex="-1" role="dialog" aria-labelledby="modalPlanGrupalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header mt-modal-head">
                <h5 class="modal-title text-white" id="modalPlanGrupalLabel">
                    <i class="fa fa-users"></i> Plan Grupal - Seleccionar Estudiantes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <small class="text-muted d-block mb-3">
                    <i class="fa fa-info-circle"></i> Seleccione a los estudiantes matriculados que cancelarán el programa
                    juntos bajo este plan grupal. Solo se listan estudiantes que aún no tienen un plan de pago del programa registrado.
                </small>

                <div class="row">
                    <div class="col-lg-6 form-group">
                        <label class="small font-weight-bold mb-1">Filtrar por Programa</label>
                        <select class="form-control select2-plan-grupal" id="pgFiltroPrograma">
                            <option value="">Todos los programas...</option>
                            <?php foreach ($programas as $prog): ?>
                                <option value="<?php echo (int)$prog['ProgramaID']; ?>">
                                    <?php echo htmlspecialchars($prog['NombrePrograma'] . ' - ' . $prog['GradoAcademico']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6 form-group">
                        <label class="small font-weight-bold mb-1">% Descuento Grupal</label>
                        <input type="number" class="form-control form-control-sm" id="pgPorcentajeDescuento" min="0" max="100" step="0.01" value="0">
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width:36px;"></th>
                                <th>Estudiante</th>
                                <th>CI</th>
                                <th>Programa</th>
                                <th class="text-center">Costo Programa</th>
                            </tr>
                        </thead>
                        <tbody id="pgListaEstudiantes">
                            <tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>

                <small class="d-block text-right mt-2">
                    Seleccionados: <strong id="pgContadorSeleccionados">0</strong> &nbsp;|&nbsp;
                    Monto por estudiante: <strong>Bs. <span id="pgMontoPorEstudiante">0.00</span></strong>
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="pgBtnCrearPlan"><i class="fa fa-save"></i> Crear Plan Grupal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Requisitos de matriculación (requisitos.js) -->
<div class="modal fade" id="modalRequisitos" tabindex="-1" role="dialog" aria-labelledby="modalRequisitosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header mt-modal-head">
                <div>
                    <small style="display:block;font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#fff;opacity:.75;">Requisitos de matriculación</small>
                    <h5 class="modal-title" id="modalRequisitosLabel"><i class="fa fa-clipboard-check"></i> <span id="rqEstudiante"></span></h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="rq-barra">
                    <span id="rqContador" class="rq-contador"></span>
                    <button type="button" class="btn btn-abrir-panel btn-sm" id="rqMarcarTodos"><i class="fa fa-check-double"></i> Marcar todos</button>
                </div>
                <div id="rqLista" class="rq-lista"></div>
                <p class="rq-ayuda"><i class="fa fa-info-circle"></i> Marque los documentos que el estudiante ya entregó. Si falta alguno, la inscripción sigue vigente: la tabla solo lo marca como pendiente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="rqGuardar"><i class="fa fa-save"></i> Guardar requisitos</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="vistas/recursos/assets/js/scripts/inscripcionmodulo.js?v=<?php echo time(); ?>"></script>
<script src="vistas/recursos/assets/js/scripts/planpago.js?v=<?php echo time(); ?>"></script>
<script src="vistas/recursos/assets/js/scripts/ordenpagomodulos.js?v=<?php echo time(); ?>"></script>
<script src="vistas/recursos/assets/js/scripts/ordenpagoclinica.js?v=<?php echo time(); ?>"></script>
<script src="vistas/recursos/assets/js/scripts/requisitos.js?v=<?php echo time(); ?>"></script>


<!-- Script para filtro por programa -->
<script>
$(document).ready(function() {
    console.log('=== FILTRO DE MATRICULADOS POR PROGRAMA ===');

    // Select2 dentro del modal "Plan Grupal": requiere dropdownParent para
    // que el desplegable no quede recortado/oculto detrás del modal.
    $('.select2-plan-grupal').select2({
        width: '100%',
        dropdownParent: $('#modalPlanGrupal')
    });

    // Inicializar Select2 (mismo tamaño/estilo en Sede, Programa y Estado)
    $('.select2-uniforme').select2({
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Función para cargar estudiantes filtrados por programa, sede y/o estado
    function cargarEstudiantes(programaID, sede, estado) {
        console.log('>>> Cargando estudiantes. ProgramaID:', programaID || 'TODOS', '| Sede:', sede || 'TODAS', '| Estado:', estado || 'ACTIVO');

        // Destruir DataTable si existe
        if ($.fn.DataTable.isDataTable('#tablaMatriculados')) {
            $('#tablaMatriculados').DataTable().destroy();
        }

        // Mostrar mensaje de carga
        $('#tablaMatriculadosBody').html('<tr><td colspan="8" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>');

        // Petición AJAX
        $.ajax({
            url: 'ajax/inscripcionmodulo.ajax.php',
            type: 'POST',
            data: {
                accion: 'cargarTablaMatriculados',
                programaIDFiltro: programaID || '',
                sedeFiltro: sede || '',
                estadoFiltro: estado || 'ACTIVO',
                requisitosFiltro: $('#selectRequisitosFiltro').val() || ''
            },
            success: function(response) {
                console.log('✓ Estudiantes cargados correctamente');
                $('#tablaMatriculadosBody').html(response);

                // Verificar si hay datos reales (sin colspan)
                const $firstRow = $('#tablaMatriculadosBody tr:first');
                const hasColspan = $firstRow.find('td[colspan]').length > 0;

                if (!hasColspan) {
                    // Inicializar DataTable solo si hay datos
                    try {
                        $('#tablaMatriculados').DataTable({
                            language: {
                                processing: "Procesando...",
                                search: "Buscar:",
                                lengthMenu: "Mostrar _MENU_ registros",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ estudiantes",
                                infoEmpty: "Mostrando 0 a 0 de 0 estudiantes",
                                infoFiltered: "(filtrado de _MAX_ estudiantes totales)",
                                infoPostFix: "",
                                loadingRecords: "Cargando...",
                                zeroRecords: "No se encontraron estudiantes",
                                emptyTable: "No hay estudiantes matriculados",
                                paginate: {
                                    first: "Primero",
                                    previous: "Anterior",
                                    next: "Siguiente",
                                    last: "Último"
                                }
                            },
                            order: [[0, 'asc']],
                            pageLength: 25,
                            responsive: true
                        });
                        console.log('✓ DataTable inicializado');
                    } catch (error) {
                        console.error('✗ Error al inicializar DataTable:', error);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('✗ Error al cargar estudiantes:', error);
                $('#tablaMatriculadosBody').html('<tr><td colspan="8" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error al cargar los estudiantes</td></tr>');
            }
        });
    }

    // Evento al cambiar el programa, la sede o el estado (se combinan los 3 filtros)
    $('#selectProgramaFiltro').on('change', function() {
        const programaID = $(this).val();
        const sede = $('#selectSedeFiltro').val();
        const estado = $('#selectEstadoFiltro').val();
        console.log('>>> Programa seleccionado:', programaID);
        cargarEstudiantes(programaID, sede, estado);
    });

    $('#selectSedeFiltro').on('change', function() {
        const sede = $(this).val();
        const programaID = $('#selectProgramaFiltro').val();
        const estado = $('#selectEstadoFiltro').val();
        console.log('>>> Sede seleccionada:', sede);
        cargarEstudiantes(programaID, sede, estado);
    });

    $('#selectEstadoFiltro').on('change', function() {
        const estado = $(this).val();
        const programaID = $('#selectProgramaFiltro').val();
        const sede = $('#selectSedeFiltro').val();
        console.log('>>> Estado seleccionado:', estado);
        cargarEstudiantes(programaID, sede, estado);
    });

    // Evento al limpiar filtro
    $('#btnLimpiarFiltro').on('click', function() {
        console.log('>>> Limpiando filtro');
        // trigger('change') en los tres: con Select2 un .val() plano no
        // actualiza el texto visible del combo.
        $('#selectSedeFiltro').val('').trigger('change');
        $('#selectEstadoFiltro').val('ACTIVO').trigger('change');
        $('#selectRequisitosFiltro').val('').trigger('change.select2');
        $('#selectProgramaFiltro').val('').trigger('change');
    });

    $('#selectRequisitosFiltro').on('change', function() {
        window.refrescarTablaMatriculados();
    });

    // Expuesto globalmente para que inscripcionmodulo.js pueda refrescar la tabla
    // tras marcar/revertir un abandono, sin perder los filtros actuales.
    window.refrescarTablaMatriculados = function() {
        cargarEstudiantes(
            $('#selectProgramaFiltro').val(),
            $('#selectSedeFiltro').val(),
            $('#selectEstadoFiltro').val()
        );
    };

    console.log('=== FILTRO INICIALIZADO ===');
});
</script>
