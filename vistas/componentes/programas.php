<?php
    $Validar = new FuncionesControladores();
    $Validar->ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right 
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed 
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
  /* Select2 (filtros de Grado académico y Sede): mismo alto/borde que
     .form-control de Bootstrap. El bundle del tema le pone a
     .select2-selection__rendered un padding vertical propio que, si no se
     anula en los 4 lados, sigue sumandose y el texto se sale de la caja;
     display:flex + align-items:center evita depender de un line-height
     calculado a mano. */
  .select2-container--default .select2-selection--single {
    height: calc(1.5em + .75rem + 2px) !important;
    display: flex !important;
    align-items: center;
    padding: 0 !important;
    border: 1px solid #ced4da !important;
    border-radius: .25rem !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    padding: 0 20px 0 .75rem !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    /* El bundle del tema posiciona esta flecha con "top: 50%" sin transform,
       así que queda corrida hacia arriba; top+translateY(-50%) la centra
       sin importar el alto real de la caja. */
    top: 50% !important;
    transform: translateY(-50%) !important;
    height: auto !important;
  }


  /* Botones mejorados */
  .btn {
    transition: all 0.3s ease;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  /* Badges de estado */
  .badge {
    font-weight: 500;
    letter-spacing: 0.5px;
  }

  /* Filtros de búsqueda */
  .form-control {
    border-radius: 5px;
    border: 1px solid #ddd;
  }

  .form-control:focus {
    border-color: #1e3c72;
    box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.25);
  }

  /* Estilos para input type="date" */
  input[type="date"] {
    background-color: #ffffff !important;
    color: #495057 !important;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    cursor: pointer;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer !important;
    filter: invert(0.5);
    font-size: 18px;
    padding: 5px;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
    opacity: 1 !important;
  }

  input[type="date"]::-webkit-datetime-edit {
    color: #495057 !important;
    background-color: transparent !important;
    padding: 2px;
    cursor: pointer !important;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-datetime-edit-text {
    color: #495057 !important;
    padding: 0 4px;
    cursor: pointer !important;
    pointer-events: auto !important;
  }

  input[type="date"]::-webkit-datetime-edit-month-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-month-field:hover,
  input[type="date"]::-webkit-datetime-edit-month-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  input[type="date"]::-webkit-datetime-edit-day-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-day-field:hover,
  input[type="date"]::-webkit-datetime-edit-day-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  input[type="date"]::-webkit-datetime-edit-year-field {
    color: #495057 !important;
    font-weight: 600;
    cursor: pointer !important;
    pointer-events: auto !important;
    background-color: transparent;
    padding: 2px 4px;
  }

  input[type="date"]::-webkit-datetime-edit-year-field:hover,
  input[type="date"]::-webkit-datetime-edit-year-field:focus {
    background-color: #e9ecef;
    border-radius: 3px;
  }

  /* Para Firefox */
  input[type="date"]::-moz-placeholder {
    color: #6c757d !important;
  }

  /* Cuando el input está vacío */
  input[type="date"]:invalid {
    color: #6c757d;
  }

  /* Asegurar que el modal no bloquee el date picker */
  .modal-body input[type="date"] {
    z-index: 1;
    position: relative;
  }

  /* Portlet mejorado */
  .kt-portlet {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  }

  .kt-portlet__head {
    border-radius: 10px 10px 0 0;
  }

  /* Animaciones */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }


  /* Responsive */
  @media (max-width: 768px) {
    #tabladetalle {
      font-size: 11px;
    }

    .btn-sm {
      padding: 4px 8px;
      font-size: 10px;
    }
  }

  /* =========================================================
     PROGRAMAS (prefijo pg-): franja de estados + tarjeta con filtros y tabla.
     Misma paleta que el resto del sistema (azul institucional); los estados
     usan color + ícono + texto (nunca solo color).
     ========================================================= */
  .pg {
    --pg-azul: #2a5298;
    --pg-primario: #1e3c72;
    --pg-primario-suave: #eef2f9;
    --pg-texto: #1f2937;
    --pg-texto-2: #6b7280;
    --pg-borde: #e5e7eb;
    --pg-fondo: #f7f8fa;
    --pg-verde: #15803d;
    --pg-verde-suave: #ecfdf3;
    --pg-ambar: #b45309;
    --pg-ambar-suave: #fffbeb;
    --pg-rojo: #b91c1c;
    --pg-rojo-suave: #fef2f2;
    --pg-gris: #475569;
    --pg-gris-suave: #f1f5f9;
    color: var(--pg-texto);
    padding-bottom: 24px;
  }

  /* Franja de indicadores */
  .pg-resumen { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
  .pg-res {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid var(--pg-borde);
    border-top: 3px solid var(--pg-acento, var(--pg-azul));
    border-radius: 12px;
    padding: 14px 16px;
  }
  .pg-res::after {
    content: '';
    position: absolute;
    right: -22px;
    bottom: -22px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--pg-acento, var(--pg-azul));
    opacity: .07;
  }
  .pg-res.is-azul { --pg-acento: var(--pg-azul); }
  .pg-res.is-en-curso { --pg-acento: #0d9488; }
  .pg-res.is-por-iniciar { --pg-acento: #c2410c; }
  .pg-res.is-concluido { --pg-acento: var(--pg-gris); }
  .pg-res-icono {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--pg-acento);
    color: #fff;
    font-size: 16px;
    box-shadow: 0 6px 14px -6px var(--pg-acento);
  }
  .pg-res strong { display: block; font-size: 22px; font-weight: 700; line-height: 1.1; }
  .pg-res small { display: block; font-size: 12.5px; font-weight: 600; color: var(--pg-texto); }

  /* Tarjeta principal */
  .pg-card { background: #fff; border: 1px solid var(--pg-borde); border-radius: 12px; overflow: hidden; }
  .pg-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 16px 20px;
    border-bottom: 1px solid var(--pg-borde);
  }
  .pg-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--pg-texto); }
  .pg-card-head p { font-size: 12.5px; color: var(--pg-texto-2); margin: 2px 0 0; }

  /* Botones */
  .pg-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
    border-radius: 8px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background .2s, box-shadow .2s;
  }
  .pg-btn-primario { background: var(--pg-primario); color: #fff; }
  .pg-btn-primario:hover { background: #16305c; box-shadow: 0 6px 16px rgba(30, 60, 114, .25); }
  .pg-btn-secundario { background: var(--pg-primario-suave); color: var(--pg-primario); }
  .pg-btn-secundario:hover { background: #dfe6f3; }
  .pg-btn-link { background: none; color: var(--pg-texto-2); padding: 9px 8px; }
  .pg-btn-link:hover { color: var(--pg-rojo); }

  /* Filtros en una fila */
  .pg-filtros {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    padding: 14px 20px;
    background: var(--pg-fondo);
    border-bottom: 1px solid var(--pg-borde);
  }
  .pg-filtro { flex: 0 1 180px; min-width: 150px; }
  .pg-filtro-fecha { flex-basis: 150px; }
  .pg-filtro label { display: block; font-size: 11.5px; font-weight: 600; color: var(--pg-texto-2); margin-bottom: 4px; }
  .pg .form-control { height: 38px; border: 1px solid var(--pg-borde); border-radius: 8px; font-size: 13px; }
  .pg-filtro-botones { display: flex; gap: 4px; }
  .pg-buscar { position: relative; flex: 1 1 220px; margin-left: auto; }
  .pg-buscar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--pg-texto-2); font-size: 12px; }
  .pg-buscar .form-control { padding-left: 32px; background: #fff; }

  /* Tabla */
  .pg-tabla { font-size: 13px; color: var(--pg-texto); }
  .pg-tabla thead th {
    background: #fff;
    border-top: 0;
    border-bottom: 1px solid var(--pg-borde);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--pg-texto-2);
    padding: 10px 14px;
    white-space: nowrap;
  }
  .pg-tabla td { border-top: 1px solid var(--pg-borde); padding: 12px 14px; vertical-align: middle; }
  .pg-tabla tbody tr:hover td { background: #fafbfd; }
  .pg-tabla th:first-child, .pg-tabla td:first-child { padding-left: 20px; }
  .pg-tabla th:last-child, .pg-tabla td:last-child { padding-right: 20px; }
  .pg-nombre { font-weight: 700; line-height: 1.3; }
  .pg-meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px; }
  .pg-codigo, .pg-sigla {
    font-size: 11px;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 6px;
    background: var(--pg-gris-suave);
    color: var(--pg-gris);
    font-variant-numeric: tabular-nums;
  }
  .pg-sigla { background: var(--pg-primario-suave); color: var(--pg-primario); }
  .pg-fecha strong, .pg-costo strong { display: block; font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .pg-fecha small, .pg-costo small { display: block; font-size: 11.5px; color: var(--pg-texto-2); white-space: nowrap; }
  .pg-costo { text-align: right; }

  /* Estado: color + ícono + texto */
  .pg-estado {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
    white-space: nowrap;
  }
  .pg-estado.is-en-curso { background: #ecfdf5; color: #0f766e; }
  .pg-estado.is-por-iniciar { background: #fff7ed; color: #c2410c; }
  .pg-estado.is-concluido { background: var(--pg-gris-suave); color: var(--pg-gris); }
  .pg-estado.is-inactivo { background: var(--pg-rojo-suave); color: var(--pg-rojo); }

  /* Acciones: un solo grupo de botones discretos */
  .pg-acciones { text-align: right; white-space: nowrap; }
  .pg-accion {
    width: 32px;
    height: 32px;
    border: 1px solid var(--pg-borde);
    border-radius: 8px;
    background: #fff;
    color: var(--pg-primario);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    margin-left: 4px;
    transition: background .15s, color .15s, border-color .15s;
  }
  .pg-accion:hover { background: var(--pg-primario); border-color: var(--pg-primario); color: #fff; }
  .pg-accion.is-peligro { color: var(--pg-rojo); }
  .pg-accion.is-peligro:hover { background: var(--pg-rojo); border-color: var(--pg-rojo); color: #fff; }
  .pg-accion.is-exito { color: var(--pg-verde); }
  .pg-accion.is-exito:hover { background: var(--pg-verde); border-color: var(--pg-verde); color: #fff; }

  .pg-vacio { text-align: center; padding: 40px 20px !important; color: var(--pg-texto-2); }
  .pg-vacio i { display: block; font-size: 28px; color: #c7cdd8; margin-bottom: 10px; }
  .pg-vacio strong { display: block; color: var(--pg-texto); }
  .pg-vacio span { font-size: 12.5px; }

  .pg-leyenda {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 18px;
    padding: 12px 20px;
    border-top: 1px solid var(--pg-borde);
    font-size: 12px;
    color: var(--pg-texto-2);
  }
  .pg-leyenda i { color: var(--pg-primario); margin-right: 4px; }

  @media (max-width: 991.98px) {
    .pg-resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pg-buscar { margin-left: 0; flex-basis: 100%; }
  }
  @media (max-width: 575.98px) {
    .pg-filtro, .pg-filtro-fecha { flex-basis: 100%; }
  }

  /* =========================================================
     VENTANAS DE PROGRAMAS (prefijo pgm-): nuevo, editar, detalle,
     activar y dar de baja. Mismo lenguaje visual que la página.
     ========================================================= */
  .pgm {
    --pgm-primario: #1e3c72;
    --pgm-primario-2: #2a5298;
    --pgm-primario-suave: #eef2f9;
    --pgm-texto: #1f2937;
    --pgm-texto-2: #6b7280;
    --pgm-borde: #e5e7eb;
    --pgm-fondo: #f7f8fa;
    --pgm-verde: #15803d;
    --pgm-rojo: #b91c1c;
  }
  .pgm .modal-content { border: 0; border-radius: 14px; overflow: hidden; color: var(--pgm-texto); box-shadow: 0 24px 48px rgba(15, 23, 42, .25); }
  .pgm .modal-sm-md { max-width: 440px; }

  /* Encabezado */
  .pgm .pgm-header {
    background:
      radial-gradient(circle at 92% 0%, rgba(255, 255, 255, .14) 0, transparent 45%),
      linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border: 0;
    padding: 18px 24px;
    align-items: flex-start;
  }
  .pgm-eyebrow { display: block; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: rgba(255, 255, 255, .7); }
  .pgm .modal-content .pgm-header .modal-title { color: #fff; font-size: 18px; font-weight: 700; line-height: 1.3; margin-top: 2px; }
  .pgm .pgm-header .close { color: #fff; opacity: .75; text-shadow: none; margin: -6px -8px -6px auto; }
  .pgm .pgm-header .close:hover { opacity: 1; }
  .pgm .modal-content .pgm-header .close span,
  .pgm .modal-content .pgm-header .close::before,
  .pgm .modal-content .pgm-header .close i { color: #fff !important; }
  #DetallePrograma .modal-dialog { max-width: 580px; }
  .pgm-chips { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
  .pgm-chip { font-size: 11px; font-weight: 700; letter-spacing: .03em; padding: 3px 10px; border-radius: 999px; background: #fff; color: var(--pgm-primario); }
  .pgm-chip.is-claro { background: rgba(255, 255, 255, .16); color: #fff; }
  .pgm-chip:empty { display: none; }

  /* Cuerpo y formularios */
  .pgm .pgm-body { padding: 20px 24px 8px; }
  .pgm-seccion-titulo {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--pgm-texto-2);
    margin: 4px 0 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--pgm-borde);
  }
  .pgm-seccion-titulo i { color: var(--pgm-primario-2); margin-right: 6px; }
  .pgm-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 14px 16px; margin-bottom: 18px; }
  .pgm-col-2 { grid-column: span 2; }
  .pgm-col-4 { grid-column: span 4; }
  .pgm-col-6 { grid-column: span 6; }
  .pgm-col-12 { grid-column: span 12; }
  .pgm-campo label { display: block; font-size: 12.5px; font-weight: 600; color: var(--pgm-texto); margin-bottom: 5px; }
  .pgm-req { color: var(--pgm-rojo); }
  .pgm .form-control {
    height: 40px;
    border: 1px solid var(--pgm-borde);
    border-radius: 8px;
    font-size: 13.5px;
    color: var(--pgm-texto);
    transition: border-color .2s, box-shadow .2s;
  }
  .pgm textarea.form-control { height: auto; min-height: 80px; resize: vertical; }
  .pgm .form-control:focus { border-color: var(--pgm-primario-2); box-shadow: 0 0 0 3px rgba(42, 82, 152, .14); }
  .pgm-prefijo, .pgm-sufijo { display: flex; align-items: stretch; }
  .pgm-prefijo span, .pgm-sufijo span {
    display: flex;
    align-items: center;
    padding: 0 12px;
    background: var(--pgm-fondo);
    border: 1px solid var(--pgm-borde);
    color: var(--pgm-texto-2);
    font-size: 12.5px;
    font-weight: 600;
  }
  .pgm-prefijo span { border-radius: 8px 0 0 8px; border-right: 0; }
  .pgm-prefijo .form-control { border-radius: 0 8px 8px 0; }
  .pgm-sufijo span { border-radius: 0 8px 8px 0; border-left: 0; }
  .pgm-sufijo .form-control { border-radius: 8px 0 0 8px; }
  .pgm-ayuda { font-size: 12px; color: var(--pgm-texto-2); margin-top: -4px; }
  .pgm-ayuda i { color: var(--pgm-primario-2); }

  /* Pie y botones */
  .pgm .pgm-footer { border-top: 1px solid var(--pgm-borde); padding: 12px 24px; background: #fff; }
  .pgm-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, box-shadow .2s;
  }
  .pgm-btn-primario { background: var(--pgm-primario); color: #fff; }
  .pgm-btn-primario:hover { background: #16305c; box-shadow: 0 6px 16px rgba(30, 60, 114, .25); }
  .pgm-btn-claro { background: #fff; border-color: var(--pgm-borde); color: var(--pgm-texto); }
  .pgm-btn-claro:hover { background: var(--pgm-fondo); }
  .pgm-btn-peligro { background: var(--pgm-rojo); color: #fff; }
  .pgm-btn-peligro:hover { background: #991b1b; }
  .pgm-btn-exito { background: var(--pgm-verde); color: #fff; }
  .pgm-btn-exito:hover { background: #166534; }

  /* Detalle */
  .pgm-datos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 12px; }
  .pgm-dato { display: flex; gap: 10px; align-items: center; padding: 10px 12px; border: 1px solid var(--pgm-borde); border-radius: 10px; }
  .pgm-dato i {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--pgm-primario-suave);
    color: var(--pgm-primario);
    font-size: 13px;
  }
  .pgm-dato small, .pgm-costos small { display: block; font-size: 11px; color: var(--pgm-texto-2); }
  .pgm-dato strong { font-size: 13.5px; }
  .pgm-costos {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    padding: 12px 14px;
    border-radius: 10px;
    background: var(--pgm-primario-suave);
    margin-bottom: 16px;
  }
  .pgm-costos strong { font-size: 17px; color: var(--pgm-primario); font-variant-numeric: tabular-nums; }
  .pgm-texto { font-size: 13.5px; line-height: 1.6; color: var(--pgm-texto); white-space: pre-line; margin-bottom: 14px; }

  /* Confirmaciones (activar / dar de baja) */
  .pgm .pgm-confirmar { text-align: center; padding: 28px 28px 18px; }
  .pgm-confirmar-icono {
    width: 58px;
    height: 58px;
    margin: 0 auto 14px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
  }
  .pgm-confirmar-icono.is-peligro { background: var(--pgm-rojo); box-shadow: 0 10px 24px -8px var(--pgm-rojo); }
  .pgm-confirmar-icono.is-exito { background: var(--pgm-verde); box-shadow: 0 10px 24px -8px var(--pgm-verde); }
  .pgm-confirmar h5 { font-size: 18px; font-weight: 700; color: var(--pgm-texto); margin: 0 0 6px; }
  .pgm-confirmar p { font-size: 13.5px; color: var(--pgm-texto-2); margin: 0; }
  .pgm-confirmar .pgm-confirmar-nombre { font-weight: 700; color: var(--pgm-primario); margin-bottom: 8px; }
  .pgm-confirmar-nombre:empty { display: none; }
  .pgm .pgm-confirmar + .pgm-footer { justify-content: center; }

  @media (max-width: 767.98px) {
    .pgm-col-2, .pgm-col-4, .pgm-col-6 { grid-column: span 6; }
    .pgm-datos, .pgm-costos { grid-template-columns: 1fr; }
  }
  @media (max-width: 479.98px) {
    .pgm-col-2, .pgm-col-4, .pgm-col-6 { grid-column: span 12; }
  }

  /* ===== Estilo formal: un solo tono institucional (azul marino + grises) ===== */
  .pg-res { border-top: 1px solid var(--pg-borde); }
  .pg-res::after { display: none; }
  .pg-res .pg-res-icono { background: var(--pg-primario-suave); color: var(--pg-primario); box-shadow: none; }
  /* Estados: tonos apagados; solo "Inactivo" conserva un rojo suave (alerta) */
  .pg-estado.is-en-curso { background: var(--pg-primario-suave); color: var(--pg-primario); }
  .pg-estado.is-por-iniciar { background: #f1f5f9; color: #334155; }
  .pg-estado.is-concluido { background: #f1f5f9; color: #64748b; }
  .pg-estado.is-inactivo { background: #fef2f2; color: #991b1b; }
  .pg-accion.is-exito { color: var(--pg-primario); }
  /* Ventanas: encabezado liso, confirmaciones con ícono sobrio */
  .pgm .pgm-header { background: #1e3c72; }
  .pgm-confirmar-icono.is-peligro, .pgm-confirmar-icono.is-exito { box-shadow: none; }
  .pgm-confirmar-icono.is-peligro { background: #fef2f2; color: #991b1b; }
  .pgm-confirmar-icono.is-exito { background: var(--pgm-primario-suave); color: var(--pgm-primario); }
  .pgm-btn-exito { background: var(--pgm-primario); }
  .pgm-btn-exito:hover { background: #16305c; }

  /* ===== Selectores (select2): alto uniforme, flecha y "×" centradas, texto largo recortado ===== */
  .pg .select2-container--default .select2-selection--single {
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    position: relative;
    padding: 0 !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 8px !important;
    background: #fff !important;
  }
  .pg .select2-container--default .select2-selection--single .select2-selection__rendered {
    flex: 1;
    line-height: 36px !important;
    padding: 0 56px 0 12px !important;
    font-size: 13px !important;
    color: #1f2937 !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .pg .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9aa3b2 !important; }
  .pg .select2-container--default .select2-selection--single .select2-selection__arrow:before,
  .pg .select2-container--default .select2-selection--single .select2-selection__arrow b { color: #6b7280 !important; border-top-color: #6b7280 !important; }
  .pg .select2-container--default .select2-selection--single .select2-selection__arrow {
    position: absolute !important;
    top: 50% !important;
    right: 8px !important;
    transform: translateY(-50%) !important;
    width: 20px !important;
    height: 20px !important;
    line-height: 20px !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    color: #6b7280;
  }
  .pg .select2-container--default .select2-selection--single .select2-selection__clear {
    position: absolute !important;
    top: 50% !important;
    right: 30px !important;
    transform: translateY(-50%);
    margin: 0 !important;
    padding: 0 4px !important;
    height: auto !important;
    line-height: 1 !important;
    font-size: 15px;
    color: #9aa3b2;
    background: transparent;
    border: 0;
    z-index: 1;
  }
  .pg .select2-container--default .select2-selection--single .select2-selection__clear:hover { color: #991b1b; }
  .pg .select2-container--focus .select2-selection--single,
  .pg .select2-container--open .select2-selection--single {
    border-color: #1e3c72 !important;
    box-shadow: 0 0 0 3px rgba(30, 60, 114, .12);
  }
  /* Lista desplegable (se agrega al final del <body>, fuera del contenedor) */
  .select2-container--open .select2-dropdown {
    border: 1px solid #e5e7eb !important;
    border-radius: 8px !important;
    overflow: hidden;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
  }
  .select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #e5e7eb !important;
    border-radius: 6px !important;
    height: 34px;
  }
  .select2-results__option { font-size: 13px; padding: 8px 12px !important; }
  .select2-container--default .select2-results__option--highlighted[aria-selected],
  .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background: #1e3c72 !important;
    color: #fff !important;
  }
  .select2-container--default .select2-results__option[aria-selected=true],
  .select2-container--default .select2-results__option--selected {
    background: #eef2f9;
    color: #1e3c72;
  }
</style>

  <!-- Header móvil -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
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
  <!-- end:: Header Mobile -->

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

            <!-- begin:: Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>PROGRAMAS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h4>DETALLE DE PROGRAMAS HABILITADOS</h4>
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

            <!-- begin:: Content -->
            <?php
              // Filtros de búsqueda (vienen por GET; se escapan al volver a mostrarlos)
              $grado = !empty($_GET['grado']) ? $_GET['grado'] : null;
              $fechaInicio = !empty($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
              $fechaFin = !empty($_GET['fechaFin']) ? $_GET['fechaFin'] : null;
              $sede = !empty($_GET['sede']) ? $_GET['sede'] : null;
              $hayFiltros = $grado || $fechaInicio || $fechaFin || $sede;
              $resumenProgramas = ProgramasControladores::ResumenEstadosProgramasControlador();
            ?>
            <div class="kt-container pg">

              <!-- Indicadores por estado -->
              <div class="pg-resumen">
                <div class="pg-res is-azul">
                  <span class="pg-res-icono"><i class="fa fa-graduation-cap"></i></span>
                  <div><strong><?php echo (int)$resumenProgramas['total']; ?></strong><small>Programas vigentes</small></div>
                </div>
                <div class="pg-res is-en-curso">
                  <span class="pg-res-icono"><i class="fa fa-play-circle"></i></span>
                  <div><strong><?php echo (int)$resumenProgramas['en-curso']; ?></strong><small>En curso</small></div>
                </div>
                <div class="pg-res is-por-iniciar">
                  <span class="pg-res-icono"><i class="fa fa-hourglass-start"></i></span>
                  <div><strong><?php echo (int)$resumenProgramas['por-iniciar']; ?></strong><small>Por iniciar</small></div>
                </div>
                <div class="pg-res is-concluido">
                  <span class="pg-res-icono"><i class="fa fa-flag-checkered"></i></span>
                  <div><strong><?php echo (int)$resumenProgramas['concluido']; ?></strong><small>Concluidos</small></div>
                </div>
              </div>

              <div class="pg-card">
                <!-- Cabecera: título + nuevo programa -->
                <div class="pg-card-head">
                  <div>
                    <h3>Programas de posgrado</h3>
                    <p>Administra los programas, sus cuentas bancarias y su estado.</p>
                  </div>
                  <button type="button" class="pg-btn pg-btn-primario" data-toggle="modal" data-target="#nprograma">
                    <i class="fa fa-plus"></i> Nuevo programa
                  </button>
                </div>

                <!-- Filtros en una sola fila -->
                <form id="formBusquedaProgramas" method="GET" class="pg-filtros" onsubmit="return false;">
                  <div class="pg-filtro">
                    <label for="filtroGrado">Grado</label>
                    <select class="form-control select2-uniforme" id="filtroGrado" name="grado">
                      <option value="">Todos los grados</option>
                      <option value="DIPLOMADO">Diplomado</option>
                      <option value="ESPECIALIDAD">Especialidad</option>
                      <option value="MAESTRIA">Maestría</option>
                      <option value="DOCTORADO">Doctorado</option>
                    </select>
                  </div>
                  <div class="pg-filtro">
                    <label for="filtroSede">Sede</label>
                    <select class="form-control select2-uniforme" id="filtroSede" name="sede">
                      <option value="">Todas las sedes</option>
                      <?php foreach (ProgramasModelos::ListarSedesModelo() as $sedeFiltroOpcion): ?>
                        <option value="<?php echo htmlspecialchars($sedeFiltroOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars($sedeFiltroOpcion); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="pg-filtro pg-filtro-fecha">
                    <label for="filtroFechaInicio">Inicio desde</label>
                    <input type="date" class="form-control" id="filtroFechaInicio" name="fechaInicio">
                  </div>
                  <div class="pg-filtro pg-filtro-fecha">
                    <label for="filtroFechaFin">Inicio hasta</label>
                    <input type="date" class="form-control" id="filtroFechaFin" name="fechaFin">
                  </div>
                  <div class="pg-filtro-botones">
                    <button type="button" id="btnBuscar" class="pg-btn pg-btn-secundario"><i class="fa fa-filter"></i> Filtrar</button>
                    <?php if ($hayFiltros): ?>
                      <button type="button" id="btnLimpiar" class="pg-btn pg-btn-link"><i class="fa fa-times"></i> Limpiar</button>
                    <?php else: ?>
                      <button type="button" id="btnLimpiar" class="pg-btn pg-btn-link" style="display:none">Limpiar</button>
                    <?php endif; ?>
                  </div>
                  <div class="pg-buscar">
                    <i class="fa fa-search"></i>
                    <input type="text" class="form-control" id="buscadorTablaProgramas" placeholder="Buscar por nombre, sigla, código..." aria-label="Buscar en la tabla">
                  </div>
                </form>

                <!-- Tabla -->
                <div class="table-responsive">
                  <table class="table pg-tabla mb-0" id="tabladetalle">
                    <thead>
                      <tr>
                        <th>Programa</th>
                        <th>Grado</th>
                        <th>Inicio</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th class="text-right">Costo</th>
                        <th class="text-right">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="tablaProgramasBody">
                      <?php
                        $listaprogramas = new ProgramasControladores();
                        $listaprogramas->ListaProgramaControlador($grado, $fechaInicio, $fechaFin, $sede);
                      ?>
                    </tbody>
                  </table>
                </div>

                <div class="pg-leyenda">
                  <span><i class="fa fa-eye"></i> Detalle</span>
                  <span><i class="fa fa-university"></i> Cuentas bancarias</span>
                  <span><i class="fa fa-pen"></i> Editar</span>
                  <span><i class="fa fa-arrow-down"></i> Dar de baja</span>
                </div>
              </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="vistas/recursos/sweetalert.min.js"></script>
          <script>
          $(document).on('click', '#btnDetallePrograma', function() {
              var idprograma = $(this).attr("idcod");

              $.ajax({
                  url: "ajax/programa.ajax.php",
                  method: "POST",
                  data: { idprograma: idprograma },
                  dataType: "json",
                  success: function(respuesta) {
                      // Montos con separador de miles (12.500,00) y fecha dd/mm/aaaa
                      var moneda = function(valor) {
                          var n = parseFloat(valor);
                          return isNaN(n) ? '—' : n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                      };
                      var fecha = (respuesta.FechaInicio || '').split('-');
                      var fechaTexto = fecha.length === 3 ? fecha[2] + '/' + fecha[1] + '/' + fecha[0] : (respuesta.FechaInicio || '—');

                      $("#detalleNombre").text(respuesta.NombrePrograma);
                      $("#detalleCodigo").text(respuesta.Codigo || '');
                      $("#detalleSigla").text(respuesta.Sigla || '').toggle(!!respuesta.Sigla);
                      $("#detalleGrado").text(respuesta.GradoAcademico);
                      $("#detalleDuracion").text(respuesta.DuracionMeses);
                      $("#detalleModulos").text(respuesta.Modulos);
                      $("#detalleFecha").text(fechaTexto);
                      $("#detalleSede").text(respuesta.Sede);
                      $("#detalleCosto").text(moneda(respuesta.Costo));
                      $("#detalleCostoMatricula").text(respuesta.CostoMatricula ? moneda(respuesta.CostoMatricula) : 'No especificado');
                      $("#detalleDetalle").text(respuesta.Detalle || 'Sin detalle registrado.');

                      // Abrir modal si no se abre automáticamente
                      $("#DetallePrograma").modal("show");
                  }
              });
          });
          </script>
            <!-- end:: Content -->

            <!-- Modal Registrar nuevo programa -->
            <?php
              // Sedes para los formularios de programa: las ya registradas + las ciudades habituales
              $sedesFormularioPrograma = array_values(array_unique(array_merge(
                  ['ORURO', 'LA PAZ', 'COCHABAMBA'],
                  ProgramasModelos::ListarSedesModelo()
              )));
            ?>
            <div class="modal fade pgm" id="nprograma" tabindex="-1" role="dialog" aria-labelledby="tituloNuevoPrograma" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header pgm-header">
                    <div>
                      <span class="pgm-eyebrow">Programas de posgrado</span>
                      <h5 class="modal-title" id="tituloNuevoPrograma">Nuevo programa</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                  </div>

                  <form method="post">
                    <div class="modal-body pgm-body">
                      <div class="pgm-seccion-titulo"><i class="fa fa-graduation-cap"></i> Datos generales</div>
                      <div class="pgm-grid">
                        <div class="pgm-campo pgm-col-12">
                          <label for="NombreProgramaNuevo">Nombre del programa <span class="pgm-req">*</span></label>
                          <input type="text" class="form-control" id="NombreProgramaNuevo" name="NombrePrograma" autocomplete="off" required placeholder="Ej: Maestría en Ortodoncia">
                        </div>
                        <div class="pgm-campo pgm-col-4">
                          <label for="GradoAcademicoNuevo">Grado académico <span class="pgm-req">*</span></label>
                          <select class="form-control" id="GradoAcademicoNuevo" name="GradoAcademico" required>
                            <option value="" disabled selected>Elija el grado</option>
                            <option value="DIPLOMADO">DIPLOMADO</option>
                            <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                            <option value="MAESTRIA">MAESTRIA</option>
                            <option value="DOCTORADO">DOCTORADO</option>
                          </select>
                        </div>
                        <div class="pgm-campo pgm-col-4">
                          <label for="SedeNuevo">Sede <span class="pgm-req">*</span></label>
                          <select class="form-control" id="SedeNuevo" name="Sede" required>
                            <option value="" disabled selected>Elija la sede</option>
                            <?php foreach ($sedesFormularioPrograma as $sedeOpcion): ?>
                              <option value="<?php echo htmlspecialchars($sedeOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars($sedeOpcion); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="pgm-campo pgm-col-2">
                          <label for="SiglaNuevo">Sigla</label>
                          <input type="text" class="form-control" id="SiglaNuevo" name="Sigla" autocomplete="off" maxlength="20" placeholder="Ej: ODO-01" style="text-transform: uppercase;">
                        </div>
                        <div class="pgm-campo pgm-col-2">
                          <label for="VersionNuevo">Versión</label>
                          <select class="form-control" id="VersionNuevo" name="Version">
                            <?php for ($v = 1; $v <= 10; $v++): ?>
                              <option value="V-<?php echo $v; ?>"<?php echo $v === 1 ? ' selected' : ''; ?>>V-<?php echo $v; ?></option>
                            <?php endfor; ?>
                          </select>
                        </div>
                        <div class="pgm-col-12 pgm-ayuda"><i class="fa fa-info-circle"></i> La sigla es un identificador corto: se muestra en el listado y en la planilla de calificaciones.</div>
                      </div>

                      <div class="pgm-seccion-titulo"><i class="fa fa-calendar-alt"></i> Estructura y costos</div>
                      <div class="pgm-grid">
                        <div class="pgm-campo pgm-col-4">
                          <label for="FechaInicioNuevo">Fecha de inicio <span class="pgm-req">*</span></label>
                          <input type="date" class="form-control" id="FechaInicioNuevo" name="FechaInicio" required>
                        </div>
                        <div class="pgm-campo pgm-col-4">
                          <label for="DuracionNuevo">Duración <span class="pgm-req">*</span></label>
                          <div class="pgm-sufijo"><input type="number" class="form-control" id="DuracionNuevo" name="DuracionMeses" min="1" required placeholder="Ej: 12"><span>meses</span></div>
                        </div>
                        <div class="pgm-campo pgm-col-4">
                          <label for="ModulosNuevo">Módulos <span class="pgm-req">*</span></label>
                          <input type="number" class="form-control" id="ModulosNuevo" name="Modulos" min="1" required placeholder="Ej: 6">
                        </div>
                        <div class="pgm-campo pgm-col-6">
                          <label for="CostoNuevo">Costo del programa (sin matrícula) <span class="pgm-req">*</span></label>
                          <div class="pgm-prefijo"><span>Bs.</span><input type="number" class="form-control" id="CostoNuevo" name="Costo" step="0.01" min="0" required></div>
                        </div>
                        <div class="pgm-campo pgm-col-6">
                          <label for="CostoMatriculaNuevo">Costo de la matrícula <span class="pgm-req">*</span></label>
                          <div class="pgm-prefijo"><span>Bs.</span><input type="number" class="form-control" id="CostoMatriculaNuevo" name="CostoMatricula" step="0.01" min="0" required></div>
                        </div>
                        <div class="pgm-campo pgm-col-12">
                          <label for="DetalleNuevo">Detalle</label>
                          <textarea class="form-control" id="DetalleNuevo" name="Detalle" rows="3" placeholder="Modalidad, horarios u otra información del programa"></textarea>
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer pgm-footer">
                      <button type="button" class="pgm-btn pgm-btn-claro" data-dismiss="modal">Cancelar</button>
                      <button type="submit" class="pgm-btn pgm-btn-primario"><i class="fa fa-save"></i> Registrar programa</button>
                      <?php
                        $registroprograma = new ProgramasControladores();
                        $registroprograma->RegistrarProgramaControlador();
                      ?>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- end:: Modal -->

            <!-- Modal Gestionar cuentas (por programa) -->
            <div class="modal fade" id="gestionarCuentas" tabindex="-1" role="dialog" aria-labelledby="gestionarCuentasLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="gestionarCuentasLabel">Administrar cuentas — <span id="cuentasProgramaNombre"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>

                  <div class="modal-body">
                    <p class="text-muted" style="font-size: 12px;">
                      <i class="fa fa-info-circle"></i> Un programa puede tener varias cuentas por tipo
                      (ej. Matrícula, Cuotas, Clínicas). La cuenta marcada como <strong>Principal</strong> de
                      cada tipo es la que se imprime automáticamente en las órdenes de pago.
                    </p>

                    <input type="hidden" id="cuentasProgramaID">

                    <form id="formRegistrarCuentaPrograma" class="form-row align-items-end mb-3">
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Tipo de cuenta</label>
                        <input type="text" class="form-control" id="inputTipoCuenta" name="TipoCuenta" list="listaTiposCuenta" placeholder="Ej: MATRICULA" required>
                        <datalist id="listaTiposCuenta"></datalist>
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Número de cuenta</label>
                        <input type="text" class="form-control" id="inputNumeroCuenta" name="NumeroCuenta" placeholder="Número de cuenta" required>
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="mb-1">Banco</label>
                        <input type="text" class="form-control" id="inputBancoCuenta" name="Banco" placeholder="Banco (opcional)">
                      </div>
                      <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Agregar cuenta</button>
                      </div>
                    </form>

                    <table class="table table-bordered table-sm">
                      <thead>
                        <tr>
                          <th>Tipo</th>
                          <th>N° Cuenta</th>
                          <th>Banco</th>
                          <th class="text-center">Principal</th>
                          <th class="text-center">Estado</th>
                        </tr>
                      </thead>
                      <tbody id="tablaCuentasBody">
                      </tbody>
                    </table>
                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- end:: Modal -->

            <?php
              $Footer = new FuncionesControladores();
              $Footer->FooterResumidoControlador();
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>


</body>
<!-- Modal Detalle Programa -->
<div class="modal fade pgm" id="DetallePrograma" tabindex="-1" aria-labelledby="detalleProgramaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header pgm-header">
        <div>
          <span class="pgm-eyebrow">Detalle del programa</span>
          <h5 class="modal-title" id="detalleProgramaLabel"><span id="detalleNombre"></span></h5>
          <div class="pgm-chips">
            <span class="pgm-chip" id="detalleGrado"></span>
            <span class="pgm-chip is-claro" id="detalleCodigo"></span>
            <span class="pgm-chip is-claro" id="detalleSigla"></span>
          </div>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>

      <div class="modal-body pgm-body">
        <div class="pgm-datos">
          <div class="pgm-dato"><i class="fa fa-calendar-alt"></i><div><small>Fecha de inicio</small><strong id="detalleFecha"></strong></div></div>
          <div class="pgm-dato"><i class="fa fa-map-marker-alt"></i><div><small>Sede</small><strong id="detalleSede"></strong></div></div>
          <div class="pgm-dato"><i class="fa fa-hourglass-half"></i><div><small>Duración</small><strong><span id="detalleDuracion"></span> meses</strong></div></div>
          <div class="pgm-dato"><i class="fa fa-layer-group"></i><div><small>Módulos</small><strong><span id="detalleModulos"></span> módulos</strong></div></div>
        </div>
        <div class="pgm-costos">
          <div><small>Costo del programa</small><strong>Bs. <span id="detalleCosto"></span></strong></div>
          <div><small>Costo de la matrícula</small><strong>Bs. <span id="detalleCostoMatricula"></span></strong></div>
        </div>
        <div class="pgm-seccion-titulo">Detalle</div>
        <p class="pgm-texto" id="detalleDetalle"></p>
      </div>

      <div class="modal-footer pgm-footer">
        <button type="button" class="pgm-btn pgm-btn-claro" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Activar programa -->
<div class="modal fade pgm" id="modalsubir" tabindex="-1" role="dialog" aria-labelledby="tituloActivarPrograma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-md" role="document">
    <div class="modal-content">
      <form class="kt-form" method="POST">
        <div class="modal-body pgm-confirmar">
          <input type="hidden" id="idProgramaSubir" name="idProgramaSubir">
          <span class="pgm-confirmar-icono is-exito"><i class="fa fa-arrow-up"></i></span>
          <h5 id="tituloActivarPrograma">¿Activar este programa?</h5>
          <p class="pgm-confirmar-nombre" id="activarNombrePrograma"></p>
          <p>El programa volverá a estar disponible para matricular estudiantes.</p>
        </div>
        <div class="modal-footer pgm-footer">
          <button type="button" class="pgm-btn pgm-btn-claro" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="pgm-btn pgm-btn-exito" id="confirmarSubir"><i class="fa fa-arrow-up"></i> Activar</button>
        </div>
        <?php
          $estado = new ProgramaEstadoControlador();
          $estado->SubirProgramaControlador();
        ?>
      </form>
    </div>
  </div>
</div>

<!-- Dar de baja -->
<div class="modal fade pgm" id="modalBorrar" tabindex="-1" role="dialog" aria-labelledby="tituloBajaPrograma" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-md" role="document">
    <div class="modal-content">
      <form class="kt-form" method="POST">
        <div class="modal-body pgm-confirmar">
          <input type="hidden" id="idProgramaBaja" name="idProgramaBaja">
          <span class="pgm-confirmar-icono is-peligro"><i class="fa fa-arrow-down"></i></span>
          <h5 id="tituloBajaPrograma">¿Dar de baja este programa?</h5>
          <p class="pgm-confirmar-nombre" id="bajaNombrePrograma"></p>
          <p>El programa dejará de estar disponible para matricular y saldrá de la lista de programas vigentes.</p>
        </div>
        <div class="modal-footer pgm-footer">
          <button type="button" class="pgm-btn pgm-btn-claro" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="pgm-btn pgm-btn-peligro" id="confirmarBaja"><i class="fa fa-arrow-down"></i> Dar de baja</button>
        </div>
        <?php
          $estadopr = new ProgramaEstadoControlador();
          $estadopr->BajarProgramaControlador();
        ?>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar Programa -->
<div class="modal fade pgm" id="modalEditarPrograma" tabindex="-1" role="dialog" aria-labelledby="tituloEditarPrograma" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header pgm-header">
        <div>
          <span class="pgm-eyebrow">Programas de posgrado</span>
          <h5 class="modal-title" id="tituloEditarPrograma">Editar programa</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>

      <form method="post">
        <input type="hidden" id="ProgramaIDEditar" name="ProgramaIDEditar">
        <div class="modal-body pgm-body">
          <div class="pgm-seccion-titulo"><i class="fa fa-graduation-cap"></i> Datos generales</div>
          <div class="pgm-grid">
            <div class="pgm-campo pgm-col-12">
              <label for="NombreProgramaEditar">Nombre del programa <span class="pgm-req">*</span></label>
              <input type="text" class="form-control" id="NombreProgramaEditar" name="NombreProgramaEditar" autocomplete="off" required>
            </div>
            <div class="pgm-campo pgm-col-4">
              <label for="GradoAcademicoEditar">Grado académico <span class="pgm-req">*</span></label>
              <select class="form-control" id="GradoAcademicoEditar" name="GradoAcademicoEditar" required>
                <option value="" disabled>Elija el grado</option>
                <option value="DIPLOMADO">DIPLOMADO</option>
                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                <option value="MAESTRIA">MAESTRIA</option>
                <option value="DOCTORADO">DOCTORADO</option>
              </select>
            </div>
            <div class="pgm-campo pgm-col-4">
              <label for="SedeEditar">Sede <span class="pgm-req">*</span></label>
              <select class="form-control" id="SedeEditar" name="SedeEditar" required>
                <option value="" disabled>Elija la sede</option>
                <?php foreach ($sedesFormularioPrograma as $sedeOpcion): ?>
                  <option value="<?php echo htmlspecialchars($sedeOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars($sedeOpcion); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="pgm-campo pgm-col-2">
              <label for="SiglaEditar">Sigla</label>
              <input type="text" class="form-control" id="SiglaEditar" name="SiglaEditar" autocomplete="off" maxlength="20" placeholder="Ej: ODO-01" style="text-transform: uppercase;">
            </div>
            <div class="pgm-campo pgm-col-2">
              <label for="VersionEditar">Versión</label>
              <select class="form-control" id="VersionEditar" name="VersionEditar">
                <?php for ($v = 1; $v <= 10; $v++): ?>
                  <option value="V-<?php echo $v; ?>">V-<?php echo $v; ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <div class="pgm-seccion-titulo"><i class="fa fa-calendar-alt"></i> Estructura y costos</div>
          <div class="pgm-grid">
            <div class="pgm-campo pgm-col-4">
              <label for="FechaInicioEditar">Fecha de inicio <span class="pgm-req">*</span></label>
              <input type="date" class="form-control" id="FechaInicioEditar" name="FechaInicioEditar" required>
            </div>
            <div class="pgm-campo pgm-col-4">
              <label for="DuracionMesesEditar">Duración <span class="pgm-req">*</span></label>
              <div class="pgm-sufijo"><input type="number" class="form-control" id="DuracionMesesEditar" name="DuracionMesesEditar" min="1" required><span>meses</span></div>
            </div>
            <div class="pgm-campo pgm-col-4">
              <label for="ModulosEditar">Módulos <span class="pgm-req">*</span></label>
              <input type="number" class="form-control" id="ModulosEditar" name="ModulosEditar" min="1" required>
            </div>
            <div class="pgm-campo pgm-col-6">
              <label for="CostoEditar">Costo del programa <span class="pgm-req">*</span></label>
              <div class="pgm-prefijo"><span>Bs.</span><input type="number" class="form-control" id="CostoEditar" name="CostoEditar" step="0.01" min="0" required></div>
            </div>
            <div class="pgm-campo pgm-col-6">
              <label for="CostoMatriculaEditar">Costo de la matrícula <span class="pgm-req">*</span></label>
              <div class="pgm-prefijo"><span>Bs.</span><input type="number" class="form-control" id="CostoMatriculaEditar" name="CostoMatriculaEditar" step="0.01" min="0" required></div>
            </div>
            <div class="pgm-campo pgm-col-12">
              <label for="DetalleEditar">Detalle</label>
              <textarea class="form-control" id="DetalleEditar" name="DetalleEditar" rows="3" placeholder="Modalidad, horarios u otra información del programa"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer pgm-footer">
          <button type="button" class="pgm-btn pgm-btn-claro" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="pgm-btn pgm-btn-primario"><i class="fa fa-save"></i> Guardar cambios</button>
          <?php
            $actualizarPrograma = new ProgramasControladores();
            $actualizarPrograma->ActualizarProgramaControlador();
          ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// JavaScript para búsqueda de programas
$(document).ready(function() {
    // Botón Buscar
    $('#btnBuscar').click(function() {
        var grado = $('#filtroGrado').val();
        var sede = $('#filtroSede').val();
        var fechaInicio = $('#filtroFechaInicio').val();
        var fechaFin = $('#filtroFechaFin').val();

        // Construir URL con parámetros - USAR index.php?action=
        var url = 'index.php?action=programas';
        var params = [];

        if (grado && grado !== '') {
            params.push('grado=' + encodeURIComponent(grado));
        }
        if (sede && sede !== '') {
            params.push('sede=' + encodeURIComponent(sede));
        }
        if (fechaInicio && fechaInicio !== '') {
            params.push('fechaInicio=' + encodeURIComponent(fechaInicio));
        }
        if (fechaFin && fechaFin !== '') {
            params.push('fechaFin=' + encodeURIComponent(fechaFin));
        }

        if (params.length > 0) {
            url += '&' + params.join('&');
        }

        window.location.href = url;
    });

    // Buscador de texto libre sobre la tabla ya cargada (filtra por cualquier patrón, en cualquier columna)
    $('#buscadorTablaProgramas').on('keyup', function() {
        var patron = $(this).val().trim().toLowerCase();
        $('#tablaProgramasBody tr').each(function() {
            var textoFila = $(this).text().toLowerCase();
            $(this).toggle(textoFila.indexOf(patron) !== -1);
        });
    });

    // Permitir buscar con Enter
    $('#filtroGrado, #filtroSede, #filtroFechaInicio, #filtroFechaFin').keypress(function(e) {
        if (e.which == 13) { // Enter key
            $('#btnBuscar').click();
            return false;
        }
    });

    // Botón Limpiar
    $('#btnLimpiar').click(function() {
        $('#filtroGrado').val('');
        $('#filtroSede').val('');
        $('#filtroFechaInicio').val('');
        $('#filtroFechaFin').val('');
        window.location.href = 'index.php?action=programas';
    });

    // Mantener valores de filtros después de búsqueda
    var urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('grado')) {
        $('#filtroGrado').val(urlParams.get('grado'));
    }
    if (urlParams.has('sede')) {
        $('#filtroSede').val(urlParams.get('sede'));
    }
    if (urlParams.has('fechaInicio')) {
        $('#filtroFechaInicio').val(urlParams.get('fechaInicio'));
    }
    if (urlParams.has('fechaFin')) {
        $('#filtroFechaFin').val(urlParams.get('fechaFin'));
    }

    // Si la página se recargó después de una acción dentro de un modal (ej: registrar/dar de baja
    // una cuenta), reabrir ese mismo modal en vez de dejar al usuario en la lista principal
    if (urlParams.has('panel')) {
        var panelAReabrir = '#' + urlParams.get('panel');
        if ($(panelAReabrir).length) {
            $(panelAReabrir).modal('show');
        }
    }

    // Select2 (mismo tamaño/estilo en Grado académico y Sede). Se inicializa
    // después de restaurar los valores desde la URL para que ya se muestren
    // seleccionados desde el primer render.
    $('.select2-uniforme').select2({ width: '100%' });
});

// JavaScript para cargar datos en el modal de edición
$(document).on('click', '.btnEditarPrograma', function() {
    var idprograma = $(this).data('idprograma');
    var nombre = $(this).data('nombre');
    var grado = $(this).data('grado');
    var sigla = $(this).data('sigla');
    var duracion = $(this).data('duracion');
    var modulos = $(this).data('modulos');
    var fecha = $(this).data('fecha');
    var sede = $(this).data('sede');
    var costo = $(this).data('costo');
    var costomatricula = $(this).data('costomatricula');
    var detalle = $(this).data('detalle');
    var version = $(this).data('version');

    // Formatear costos a 2 decimales
    var costoFormateado = parseFloat(costo).toFixed(2);
    var costoMatriculaFormateado = costomatricula ? parseFloat(costomatricula).toFixed(2) : '';

    $('#ProgramaIDEditar').val(idprograma);
    $('#NombreProgramaEditar').val(nombre);
    $('#GradoAcademicoEditar').val(grado);
    $('#SiglaEditar').val(sigla || '');
    $('#DuracionMesesEditar').val(duracion);
    $('#ModulosEditar').val(modulos);
    $('#FechaInicioEditar').val(fecha);
    $('#SedeEditar').val(sede);
    $('#CostoEditar').val(costoFormateado);
    $('#CostoMatriculaEditar').val(costoMatriculaFormateado);
    $('#DetalleEditar').val(detalle);
    $('#VersionEditar').val(version || 'V-1');
});

// JavaScript para modal de subir programa
$(document).on('click', '.btnSubir', function() {
    var idprograma = $(this).data('idprograma');
    $('#idProgramaSubir').val(idprograma);
    $('#activarNombrePrograma').text($(this).data('nombre') || '');
});

// JavaScript para modal de dar de baja
$(document).on('click', '.btnDarBaja', function() {
    var idprograma = $(this).data('idprograma');
    $('#idProgramaBaja').val(idprograma);
    $('#bajaNombrePrograma').text($(this).data('nombre') || '');
});

// Manejo correcto de modales de Bootstrap apilados (Gestionar cuentas se abre
// sobre Nuevo programa). Sin esto, al cerrar el modal hijo Bootstrap quita
// "modal-open" del body aunque el padre siga abierto y la pantalla queda bloqueada.
$(document).on('show.bs.modal', '.modal', function() {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
        $('.modal-backdrop').not('.modal-stack-fixed').css('z-index', zIndex - 1).addClass('modal-stack-fixed');
    }, 0);
});
$(document).on('hidden.bs.modal', '.modal', function() {
    if ($('.modal:visible').length) {
        $('body').addClass('modal-open');
    }
});

// Gestión de cuentas POR PROGRAMA: un programa puede tener varias cuentas
// clasificadas por tipo (Matrícula, Cuotas, Clínicas...). Todo vía AJAX,
// para no recargar la página ni romper el modal apilado.
function refrescarTablaCuentasPrograma() {
    var programaID = $('#cuentasProgramaID').val();
    if (!programaID) { return; }

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'listarHtml', ProgramaID: programaID },
        success: function(html) {
            $('#tablaCuentasBody').html(html);
        }
    });
}

function refrescarTiposCuentaDatalist() {
    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'listarTiposHtml' },
        success: function(html) {
            $('#listaTiposCuenta').html(html);
        }
    });
}

// Abrir el modal de cuentas desde el botón de la fila del programa
$(document).on('click', '.btnAbrirCuentasPrograma', function() {
    var programaID = $(this).data('idprograma');
    var nombrePrograma = $(this).data('nombreprograma');

    $('#cuentasProgramaID').val(programaID);
    $('#cuentasProgramaNombre').text(nombrePrograma);
    $('#formRegistrarCuentaPrograma')[0].reset();

    refrescarTablaCuentasPrograma();
    refrescarTiposCuentaDatalist();
});

$('#formRegistrarCuentaPrograma').on('submit', function(e) {
    e.preventDefault();

    var programaID = $('#cuentasProgramaID').val();
    var tipoCuenta = $('#inputTipoCuenta').val();
    var numeroCuenta = $('#inputNumeroCuenta').val();
    var banco = $('#inputBancoCuenta').val();

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'registrar', ProgramaID: programaID, TipoCuenta: tipoCuenta, NumeroCuenta: numeroCuenta, Banco: banco },
        dataType: 'json',
        success: function(respuesta) {
            if (respuesta.estado === 'exitoso') {
                $('#formRegistrarCuentaPrograma')[0].reset();
                refrescarTablaCuentasPrograma();
                refrescarTiposCuentaDatalist();
            }
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});

$(document).on('click', '.btnCambiarEstadoCuentaPrograma', function() {
    var idcuenta = $(this).data('idcuenta');
    var nuevoestado = $(this).data('nuevoestado');

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'cambiarEstado', ProgramaCuentaID: idcuenta, NuevoEstado: nuevoestado },
        dataType: 'json',
        success: function(respuesta) {
            refrescarTablaCuentasPrograma();
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});

$(document).on('click', '.btnMarcarPrincipalCuenta', function() {
    var idcuenta = $(this).data('idcuenta');

    $.ajax({
        url: 'ajax/programacuenta.ajax.php',
        method: 'POST',
        data: { accion: 'marcarPrincipal', ProgramaCuentaID: idcuenta },
        dataType: 'json',
        success: function(respuesta) {
            refrescarTablaCuentasPrograma();
            swal(respuesta.estado === 'exitoso' ? 'EXITOSO!' : 'ERROR!', respuesta.mensaje, respuesta.estado === 'exitoso' ? 'success' : 'error');
        },
        error: function() {
            swal('ERROR!', 'No se pudo conectar con el servidor', 'error');
        }
    });
});
</script>

