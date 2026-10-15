<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
require_once 'modelos/certificado.modelo.php'; // tipos de certificado del filtro
?>

<style>
/* =========================================================
   REGISTRO DE DOCUMENTOS (prefijo rd-): qué certificados ya se emitieron
   a un estudiante y cuáles faltan, su trabajo de grado y el registro de
   emisiones. Mismo lenguaje visual que Inscripción e Historial de Pagos.
   ========================================================= */
.rd {
    --rd-primario: #1e3c72;
    --rd-primario-suave: #eef2f9;
    --rd-texto: #1f2937;
    --rd-texto-2: #64748b;
    --rd-borde: #e3e7ef;
    max-width: 1100px;
    margin: 0 auto;
}

.rd-card {
    background: #fff;
    border: 1px solid var(--rd-borde);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    margin-bottom: 18px;
}
.rd-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px 16px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--rd-borde);
}
.rd-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--rd-texto); }
.rd-card-head h3 i { color: var(--rd-primario); margin-right: 4px; }
.rd-card-head p { font-size: 12.5px; color: var(--rd-texto-2); margin: 3px 0 0; }
.rd-card-body { padding: 22px 24px; }

.rd-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--rd-texto);
    margin-bottom: 6px;
}
.rd .form-control { border: 1px solid #d8dde8; border-radius: 10px; }
.rd .form-control:focus {
    border-color: var(--rd-primario);
    box-shadow: 0 0 0 3px rgba(30, 60, 114, .12);
}
.rd select.form-control { height: 46px; }

/* ---------- Historial de pagos: filtros, monto y total ---------- */
.rd-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 16px; }
.rd-filtro { flex: 0 1 170px; min-width: 150px; }
.rd-filtro .form-control { height: 42px; }
.rd-filtro-tipo { flex: 1 1 220px; }
.rd-filtro-botones { flex: 0 0 auto; display: flex; gap: 8px; }
.rd-filtro-botones .btn { height: 42px; }
.tabla-log .rd-monto { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-weight: 600; }
.tabla-log tfoot td { background: #f7f8fa; font-weight: 700; border-top: 2px solid #e2e6ee; }

/* ---------- Buscador ---------- */
.rd-buscador { position: relative; display: flex; gap: 10px; }
.rd-buscador-icono {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}
.rd-buscador .form-control { height: 46px; padding-left: 40px; font-size: 14px; }
.rd-btn-primario {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 46px;
    padding: 0 22px;
    background: #1e3c72;
    border: 1px solid #1e3c72;
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.rd-btn-primario:hover, .rd-btn-primario:focus {
    background: #16305c;
    border-color: #16305c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .25);
}
.rd-aviso {
    margin: 14px 0 0;
    padding: 10px 14px;
    background: #fff8e6;
    border: 1px solid #f1d58a;
    border-left: 4px solid #d69e2e;
    border-radius: 10px;
    font-size: 12.5px;
    color: #6b4e0e;
}
.rd-aviso i { color: #d69e2e; margin-right: 4px; }

/* ---------- Ficha del estudiante ---------- */
.rd-ficha {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px;
}
.rd-ficha-item {
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid var(--rd-borde);
    border-radius: 10px;
}
.rd-ficha-item > span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: var(--rd-texto-2); }
.rd-ficha-item > span i { color: #94a3b8; margin-right: 3px; }
.rd-ficha-item strong { display: block; margin-top: 3px; font-size: 14px; color: var(--rd-texto); }

/* ---------- Documentos del estudiante ---------- */
.rd-contador { font-size: 12.5px; color: var(--rd-texto-2); }
.rd-contador strong { color: var(--rd-primario); }
.rd-documentos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.item-checklist {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1px solid var(--rd-borde);
    background: #fff;
}
.item-checklist .icono {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
.item-checklist.emitido .icono { background: #e7f6ee; color: #2f855a; }
.item-checklist.pendiente .icono { background: #fff6e0; color: #b7791f; }
.item-checklist .titulo-doc { font-weight: 700; font-size: 13.5px; color: var(--rd-texto); }
.item-checklist .detalle-doc { font-size: 12px; color: var(--rd-texto-2); margin-top: 2px; }
.badge-estado-doc {
    margin-left: auto;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.badge-estado-doc.emitido { background: #e7f6ee; color: #2f855a; }
.badge-estado-doc.pendiente { background: #fff6e0; color: #b7791f; }

/* ---------- Trabajo de grado ---------- */
.rd-pie {
    display: flex;
    justify-content: flex-end;
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid var(--rd-borde);
}
.rd-pie .rd-btn-primario { height: 42px; }

/* ---------- Registro de emisiones ---------- */
.rd-tabla-marco { border: 1px solid var(--rd-borde); border-radius: 10px; overflow: hidden; }
.tabla-log { margin: 0; font-size: 13px; }
.tabla-log thead th {
    background: #f8fafc;
    color: var(--rd-texto-2);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-top: 0;
    border-bottom: 1px solid var(--rd-borde);
    white-space: nowrap;
}
.tabla-log td { vertical-align: middle; border-color: #eef1f6; color: var(--rd-texto); }
.rd-tipo { display: inline-block; padding: 3px 10px; border-radius: 999px; background: var(--rd-primario-suave); color: var(--rd-primario); font-size: 11.5px; font-weight: 600; }

/* ---------- Aprobación del decano y reimpresión ---------- */
.rd-estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.rd-estado.pendiente { background: #fff6e0; color: #b7791f; }
.rd-estado.aprobado { background: #e7f6ee; color: #2f855a; }
.rd-acciones { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.rd-btn-mini {
    display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 8px;
    font-size: 12px; font-weight: 600; border: 1px solid var(--rd-borde); background: #fff; color: var(--rd-texto); cursor: pointer;
}
.rd-btn-mini:hover { border-color: #1e3c72; color: #1e3c72; }
.rd-btn-mini.aprobar { border-color: #2f855a; color: #2f855a; }
.rd-btn-mini.aprobar:hover { background: #2f855a; color: #fff; }
.rd-btn-mini[disabled] { opacity: .5; cursor: not-allowed; }
.item-checklist { flex-wrap: wrap; }
.item-checklist .rd-doc-texto { flex: 1; min-width: 0; }
.tabla-log td.rd-celda-acciones { white-space: nowrap; }

/* Ventana para reimprimir con los datos guardados (propia, sin depender de Bootstrap) */
.rd-ventana-fondo { position: fixed; inset: 0; background: rgba(15, 23, 42, .45); z-index: 1060; display: none; align-items: flex-start; justify-content: center; padding: 40px 16px; overflow-y: auto; }
.rd-ventana-fondo.abierta { display: flex; }
.rd-ventana { background: #fff; border-radius: 14px; width: 100%; max-width: 620px; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
.rd-ventana-cabeza { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 18px 22px; border-bottom: 1px solid var(--rd-borde); }
.rd-ventana-cabeza h4 { margin: 0; font-size: 16px; font-weight: 700; color: var(--rd-texto); }
.rd-ventana-cabeza p { margin: 2px 0 0; font-size: 12.5px; color: var(--rd-texto-2); }
.rd-ventana-cerrar { background: none; border: none; font-size: 22px; line-height: 1; color: #94a3b8; cursor: pointer; }
.rd-ventana-cuerpo { padding: 18px 22px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 14px; }
.rd-ventana-cuerpo .rd-campo-ancho { grid-column: 1 / -1; }
.rd-ventana-cuerpo .form-control { font-size: 13.5px; }
.rd-ventana-aviso { grid-column: 1 / -1; margin: 0; padding: 8px 12px; border-radius: 8px; background: #fff8e6; color: #6b4e0e; font-size: 12.5px; }
.rd-ventana-pie { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 22px; border-top: 1px solid var(--rd-borde); background: #f8fafc; border-radius: 0 0 14px 14px; }
.rd-ventana-pie .rd-btn-primario { height: 40px; }
@media (max-width: 767.98px) {
    .rd-ventana-cuerpo { grid-template-columns: 1fr; }
    .rd-buscador { flex-direction: column; }
    .rd-buscador-icono { top: 23px; }
    .rd-ficha, .rd-documentos { grid-template-columns: 1fr; }
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
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
                                        <h4>CERTIFICADOS EMITIDOS Y PENDIENTES</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                          <div class="rd">

                            <!-- Buscar estudiante -->
                            <div class="rd-card">
                                <div class="rd-card-head">
                                    <div>
                                        <h3><i class="fas fa-search"></i> Buscar estudiante</h3>
                                        <p>Vea qué certificados ya se le emitieron, cuáles faltan y su trabajo de grado.</p>
                                    </div>
                                </div>
                                <div class="rd-card-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label class="rd-label" for="input-buscar-ci">N° de carnet (C.I.)</label>
                                            <div class="rd-buscador">
                                                <i class="fas fa-id-card rd-buscador-icono"></i>
                                                <input type="text" class="form-control" id="input-buscar-ci" placeholder="Ej: 7456178" inputmode="numeric" autocomplete="off">
                                                <button type="button" class="btn rd-btn-primario" id="btn-buscar-ci">
                                                    <i class="fas fa-search"></i> Buscar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-7 mt-3 mt-md-0" id="col-resultado-busqueda" style="display:none;">
                                            <label class="rd-label" for="select-inscripcion">Programa del estudiante</label>
                                            <select class="form-control" id="select-inscripcion" style="width: 100%;">
                                                <option value="">-- Seleccione --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="alerta-sin-resultado" class="rd-aviso" style="display:none;">
                                        <i class="fas fa-exclamation-triangle"></i> No se encontró ningún estudiante activo con ese carnet.
                                    </div>
                                    <div class="rd-ficha" id="bloque-datos" style="display:none;">
                                        <div class="rd-ficha-item">
                                            <span><i class="fas fa-user-graduate"></i> Estudiante</span>
                                            <strong id="dato-nombre"></strong>
                                        </div>
                                        <div class="rd-ficha-item">
                                            <span><i class="fas fa-university"></i> Programa</span>
                                            <strong id="dato-programa"></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documentos del estudiante -->
                            <div class="rd-card" id="card-checklist" style="display:none;">
                                <div class="rd-card-head">
                                    <div>
                                        <h3><i class="fas fa-clipboard-check"></i> Documentos del estudiante</h3>
                                        <p>Certificados del programa seleccionado.</p>
                                    </div>
                                    <span class="rd-contador" id="contador-documentos"></span>
                                </div>
                                <div class="rd-card-body">
                                    <div class="rd-documentos" id="contenedor-checklist"></div>
                                </div>
                            </div>

                            <!-- Trabajo de grado -->
                            <div class="rd-card" id="card-trabajo" style="display:none;">
                                <div class="rd-card-head">
                                    <div>
                                        <h3><i class="fas fa-file-signature"></i> Trabajo de grado registrado</h3>
                                        <p>Corrija el título, la calificación o el tribunal si hubo un error al registrarlo.</p>
                                    </div>
                                </div>
                                <div class="rd-card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="rd-label" for="edit-titulo">Título del trabajo</label>
                                                <textarea class="form-control" id="edit-titulo" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-md-0">
                                                <label class="rd-label" for="edit-calificacion">Calificación numeral</label>
                                                <input type="number" class="form-control" id="edit-calificacion" min="0" max="100" style="height:46px;">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-0">
                                                <label class="rd-label" for="edit-tribunal">Tribunal <span class="text-muted font-weight-normal">(uno por línea, ej: "PRESIDENTE: NOMBRE")</span></label>
                                                <textarea class="form-control" id="edit-tribunal" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="rd-pie">
                                    <button type="button" class="btn rd-btn-primario" id="btn-guardar-trabajo">
                                        <i class="fas fa-save"></i> Guardar cambios
                                    </button>
                                </div>
                            </div>

                            <!-- Registro de emisiones e historial de pagos de certificados -->
                            <div class="rd-card" id="card-historial-pagos">
                                <div class="rd-card-head">
                                    <div>
                                        <h3><i class="fas fa-history"></i> Documentos emitidos y pagos</h3>
                                        <p id="subtitulo-log">Emisiones más recientes de todo el sistema.</p>
                                    </div>
                                    <button type="button" class="btn rd-btn-primario" id="btn-imprimir-pagos">
                                        <i class="fas fa-print"></i> Imprimir detalle
                                    </button>
                                </div>
                                <div class="rd-card-body">
                                    <!-- Filtros del historial de pagos (con el C.I. buscado arriba, si hay uno) -->
                                    <div class="rd-filtros">
                                        <div class="rd-filtro">
                                            <label class="rd-label" for="filtro-desde">Desde</label>
                                            <input type="date" class="form-control" id="filtro-desde">
                                        </div>
                                        <div class="rd-filtro">
                                            <label class="rd-label" for="filtro-hasta">Hasta</label>
                                            <input type="date" class="form-control" id="filtro-hasta">
                                        </div>
                                        <div class="rd-filtro rd-filtro-tipo">
                                            <label class="rd-label" for="filtro-tipo">Tipo de certificado</label>
                                            <select class="form-control" id="filtro-tipo">
                                                <option value="">Todos</option>
                                                <?php foreach (CertificadoModelo::TiposDocumento() as $claveTipo => $nombreTipo): ?>
                                                <option value="<?php echo htmlspecialchars($claveTipo); ?>"><?php echo htmlspecialchars($nombreTipo); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="rd-filtro rd-filtro-botones">
                                            <button type="button" class="btn rd-btn-primario" id="btn-filtrar-pagos"><i class="fas fa-filter"></i> Filtrar</button>
                                            <button type="button" class="btn btn-outline-secondary" id="btn-limpiar-pagos">Limpiar</button>
                                        </div>
                                    </div>
                                    <div class="rd-tabla-marco table-responsive">
                                        <table class="table table-hover tabla-log" id="tabla-log">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Tipo de documento</th>
                                                    <th>Estudiante</th>
                                                    <th>N° voucher</th>
                                                    <th class="text-right">Monto</th>
                                                    <th>Emitido por</th>
                                                    <th>Aprobación del decano</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-log-body"></tbody>
                                            <tfoot id="tabla-log-pie"></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                          </div>
                        </div>

                        <!-- Ventana: reimprimir un documento con sus datos guardados (editables) -->
                        <div class="rd-ventana-fondo" id="ventana-reimprimir" role="dialog" aria-modal="true" aria-labelledby="ventana-reimprimir-titulo">
                            <div class="rd-ventana">
                                <div class="rd-ventana-cabeza">
                                    <div>
                                        <h4 id="ventana-reimprimir-titulo">Reimprimir documento</h4>
                                        <p id="ventana-reimprimir-sub">Revise o corrija los datos antes de imprimir.</p>
                                    </div>
                                    <button type="button" class="rd-ventana-cerrar" data-cerrar-ventana aria-label="Cerrar">&times;</button>
                                </div>
                                <form id="form-reimprimir" method="POST" target="_blank">
                                    <div class="rd-ventana-cuerpo" id="campos-reimprimir"></div>
                                    <div class="rd-ventana-pie">
                                        <button type="button" class="btn btn-outline-secondary" data-cerrar-ventana>Cancelar</button>
                                        <button type="submit" class="btn rd-btn-primario"><i class="fas fa-print"></i> Imprimir</button>
                                    </div>
                                </form>
                            </div>
                        </div>

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
    <script>
    $(document).ready(function() {
        var idTrabajoActual = null;

        // Escapar texto que viene de la BD antes de insertarlo como HTML
        function esc(texto) {
            return $('<div>').text(texto == null ? '' : String(texto)).html();
        }

        // Aprobación del decano (solo control: no bloquea otros trámites)
        function htmlEstado(doc) {
            return doc.Estado === 'APROBADO'
                ? '<span class="rd-estado aprobado" title="' + esc(doc.FechaAprobacion ? 'Aprobado el ' + doc.FechaAprobacion : '') + '">Aprobado</span>'
                : '<span class="rd-estado pendiente">Por aprobar</span>';
        }

        // Botones Imprimir (con los datos guardados) y Aprobar / Quitar aprobación
        function htmlAcciones(doc) {
            var tieneDatos = doc.TieneDatos !== undefined ? Number(doc.TieneDatos) === 1 : !!doc.Datos;
            var imprimir = '<button type="button" class="rd-btn-mini" data-reimprimir="' + doc.IdDocumento + '"' +
                (tieneDatos ? '' : ' disabled title="Se generó antes de que el sistema guardara los datos: vuelva a generarlo desde su pantalla"') +
                '><i class="fas fa-print"></i> Imprimir</button>';
            var estado = doc.Estado === 'APROBADO'
                ? '<button type="button" class="rd-btn-mini" data-quitar-aprobacion="' + doc.IdDocumento + '"><i class="fas fa-undo"></i> Quitar aprobación</button>'
                : '<button type="button" class="rd-btn-mini aprobar" data-aprobar="' + doc.IdDocumento + '"><i class="fas fa-check"></i> Aprobado por el decano</button>';
            return imprimir + estado;
        }

        // Vuelve a cargar la lista y los documentos del estudiante elegido
        function refrescar() {
            cargarLog($('#input-buscar-ci').val().trim());
            if ($('#select-inscripcion').val()) $('#select-inscripcion').trigger('change');
        }

        // ===== Historial de pagos de certificados: filtros, monto, total e impresión =====
        var ciLog = '';
        var ultimoLog = { documentos: [], tipos: {} };

        function formatoBs(monto) {
            return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        // "2026-10-15 12:30:00" → "15/10/2026 12:30"
        function fechaHora(valor) {
            var p = String(valor || '').match(/^(\d{4})-(\d{2})-(\d{2})(?: (\d{2}):(\d{2}))?/);
            return p ? p[3] + '/' + p[2] + '/' + p[1] + (p[4] ? ' ' + p[4] + ':' + p[5] : '') : (valor || '-');
        }
        function filtrosLog() {
            return {
                desde: $('#filtro-desde').val() || '',
                hasta: $('#filtro-hasta').val() || '',
                tipo: $('#filtro-tipo').val() || ''
            };
        }
        // Texto del periodo y filtros, para el subtítulo y la impresión
        function descripcionFiltros() {
            var f = filtrosLog();
            var partes = [];
            if (ciLog) partes.push('C.I. ' + ciLog);
            if (f.tipo) partes.push($('#filtro-tipo option:selected').text());
            if (f.desde || f.hasta) {
                partes.push((f.desde ? 'desde ' + fechaHora(f.desde) : '') + (f.desde && f.hasta ? ' ' : '') + (f.hasta ? 'hasta ' + fechaHora(f.hasta) : ''));
            }
            return partes.join(' · ');
        }

        function cargarLog(ci) {
            if (ci !== undefined) ciLog = ci || '';
            var f = filtrosLog();
            var hayFiltros = ciLog || f.desde || f.hasta || f.tipo;
            $('#subtitulo-log').text(hayFiltros ? 'Filtrado: ' + descripcionFiltros() + '.' : 'Emisiones más recientes de todo el sistema.');

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: $.extend({ accion: 'listarDocumentosEmitidos', ci: ciLog }, f),
                dataType: 'json',
                success: function(data) {
                    var $tbody = $('#tabla-log-body');
                    $tbody.empty();
                    $('#tabla-log-pie').empty();
                    ultimoLog = { documentos: (data && data.documentos) || [], tipos: (data && data.tipos) || {} };

                    if (!data.success || !data.documentos || data.documentos.length === 0) {
                        $tbody.append('<tr><td colspan="8" class="text-center text-muted py-4">' +
                            (hayFiltros ? 'Sin documentos emitidos con estos filtros' : 'Sin documentos emitidos todavía') + '</td></tr>');
                        return;
                    }

                    var total = 0;
                    var sinMonto = 0;
                    data.documentos.forEach(function(doc) {
                        var etiquetaTipo = data.tipos[doc.TipoDocumento] || doc.TipoDocumento;
                        if (doc.Monto !== null && doc.Monto !== undefined && doc.Monto !== '') {
                            total += parseFloat(doc.Monto) || 0;
                        } else {
                            sinMonto++;
                        }
                        $tbody.append(
                            '<tr>' +
                            '<td class="text-nowrap">' + esc(fechaHora(doc.FechaEmision)) + '</td>' +
                            '<td><span class="rd-tipo">' + esc(etiquetaTipo) + '</span></td>' +
                            '<td>' + esc(doc.NombreEstudiante || '-') + (doc.Ci ? '<div class="text-muted small">C.I. ' + esc(doc.Ci) + '</div>' : '') + '</td>' +
                            '<td>' + esc(doc.NumeroVaucher || '-') + '</td>' +
                            '<td class="rd-monto">' + (doc.Monto !== null && doc.Monto !== undefined && doc.Monto !== '' ? formatoBs(doc.Monto) : '<span class="text-muted font-weight-normal">-</span>') + '</td>' +
                            '<td>' + esc(doc.UsuarioEmisor || '-') + '</td>' +
                            '<td>' + htmlEstado(doc) + '</td>' +
                            '<td class="rd-celda-acciones"><div class="rd-acciones" style="margin:0;">' + htmlAcciones(doc) + '</div></td>' +
                            '</tr>'
                        );
                    });

                    $('#tabla-log-pie').html(
                        '<tr><td colspan="4">Total · ' + data.documentos.length + ' documento(s)' +
                            (sinMonto ? ' <span class="text-muted font-weight-normal">(' + sinMonto + ' sin monto registrado)</span>' : '') + '</td>' +
                        '<td class="rd-monto">' + formatoBs(total) + '</td><td colspan="3"></td></tr>'
                    );
                }
            });
        }

        cargarLog();

        $('#btn-filtrar-pagos').on('click', function() { cargarLog(); });
        $('#filtro-tipo').on('change', function() { cargarLog(); });
        $('#btn-limpiar-pagos').on('click', function() {
            $('#filtro-desde, #filtro-hasta, #filtro-tipo').val('');
            cargarLog();
        });

        // Imprimir el detalle de pagos con los filtros actuales: cabecera institucional,
        // tabla (sin acciones) y total, en hoja carta horizontal
        $('#btn-imprimir-pagos').on('click', function() {
            var docs = ultimoLog.documentos;
            if (!docs.length) {
                Swal.fire({ type: 'info', title: 'Sin datos', text: 'No hay documentos para imprimir con estos filtros' });
                return;
            }
            var total = 0;
            var filas = docs.map(function(doc, i) {
                var conMonto = doc.Monto !== null && doc.Monto !== undefined && doc.Monto !== '';
                if (conMonto) total += parseFloat(doc.Monto) || 0;
                return '<tr>' +
                    '<td class="c">' + (i + 1) + '</td>' +
                    '<td>' + esc(fechaHora(doc.FechaEmision)) + '</td>' +
                    '<td>' + esc(doc.NombreEstudiante || '-') + '</td>' +
                    '<td>' + esc(doc.Ci || '-') + '</td>' +
                    '<td>' + esc(ultimoLog.tipos[doc.TipoDocumento] || doc.TipoDocumento) + '</td>' +
                    '<td>' + esc(doc.NombrePrograma || '-') + '</td>' +
                    '<td>' + esc(doc.NumeroVaucher || '-') + '</td>' +
                    '<td class="r">' + (conMonto ? formatoBs(doc.Monto) : '-') + '</td>' +
                '</tr>';
            }).join('');

            var base = document.baseURI;
            var ahora = new Date();
            var dos = function(n) { return String(n).padStart(2, '0'); };
            var emitido = dos(ahora.getDate()) + '/' + dos(ahora.getMonth() + 1) + '/' + ahora.getFullYear() + ' ' + dos(ahora.getHours()) + ':' + dos(ahora.getMinutes());
            var filtros = descripcionFiltros();
            var v = window.open('', '_blank');
            v.document.write(
                '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Detalle de pagos de certificados</title>' +
                '<style>' +
                '@page { size: letter landscape; margin: 12mm; }' +
                'body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #111; margin: 0; }' +
                '.cab { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #1e3c72; padding-bottom: 8px; margin-bottom: 10px; }' +
                '.cab img { height: 54px; } .cab .t { flex: 1; text-align: center; }' +
                '.cab h1 { font-size: 14px; margin: 0; } .cab h2 { font-size: 11px; margin: 2px 0 0; font-weight: normal; }' +
                'h3 { font-size: 13px; margin: 10px 0 2px; letter-spacing: .5px; } .meta { color: #555; margin-bottom: 8px; }' +
                'table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #bbb; padding: 4px 6px; text-align: left; }' +
                'th { background: #eef2f9; font-size: 9.5px; text-transform: uppercase; } .r { text-align: right; white-space: nowrap; } .c { text-align: center; }' +
                'tfoot td { font-weight: bold; background: #f3f4f6; } .pie { margin-top: 10px; color: #777; font-size: 9px; }' +
                '</style></head><body>' +
                '<div class="cab"><img src="' + new URL('extensiones/imagenespdf/logouto.png', base).href + '">' +
                    '<div class="t"><h1>UNIVERSIDAD TÉCNICA DE ORURO</h1><h2>FACULTAD DE CIENCIAS DE LA SALUD · COORDINACIÓN DE POSGRADO - ODONTOLOGÍA</h2></div>' +
                    '<img src="' + new URL('extensiones/imagenespdf/logofcs.png', base).href + '"></div>' +
                '<h3>DETALLE DE PAGOS DE CERTIFICADOS</h3>' +
                '<div class="meta">' + (filtros ? esc(filtros) + ' · ' : '') + docs.length + ' documento(s) · Emitido el ' + emitido + '</div>' +
                '<table><thead><tr><th class="c">N°</th><th>Fecha</th><th>Estudiante</th><th>C.I.</th><th>Certificado</th><th>Programa</th><th>N° voucher</th><th class="r">Monto</th></tr></thead>' +
                '<tbody>' + filas + '</tbody>' +
                '<tfoot><tr><td colspan="7">Total</td><td class="r">' + formatoBs(total) + '</td></tr></tfoot></table>' +
                '<div class="pie">Sistema de Gestión Académica · Posgrado FCS</div>' +
                '<script>window.onload = function () { window.print(); };<\/script>' +
                '</body></html>'
            );
            v.document.close();
        });

        function limpiarResultadoBusqueda() {
            $('#select-inscripcion').empty().append('<option value="">-- Seleccione --</option>');
            $('#col-resultado-busqueda').hide();
            $('#bloque-datos').hide();
            $('#card-checklist').hide();
            $('#card-trabajo').hide();
        }

        function buscarPorCI() {
            var ci = $('#input-buscar-ci').val().trim();
            $('#alerta-sin-resultado').hide();
            limpiarResultadoBusqueda();

            if (!ci) {
                Swal.fire({ type: 'warning', title: 'Ingrese un carnet', text: 'Escriba el número de carnet del estudiante' });
                return;
            }

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'buscarPorCI', ci: ci },
                dataType: 'json',
                success: function(data) {
                    if (!data.success || !data.resultados || data.resultados.length === 0) {
                        $('#alerta-sin-resultado').show();
                        return;
                    }

                    var $select = $('#select-inscripcion');
                    $select.empty();

                    if (data.resultados.length > 1) {
                        $select.append('<option value="">-- Seleccione --</option>');
                    }

                    data.resultados.forEach(function(insc) {
                        var nombreCompleto = (insc.Apaterno + ' ' + insc.Amaterno + ' ' + insc.Nombre).trim();
                        var ciCompleto = insc.Ci + (insc.Complemento ? '-' + insc.Complemento : '');
                        var etiqueta = nombreCompleto + ' (' + ciCompleto + ') - ' + insc.NombrePrograma + ' - ' + insc.GradoAcademico;

                        var $option = $('<option></option>')
                            .val(insc.EstudianteID)
                            .attr('data-programa-id', insc.ProgramaID)
                            .attr('data-nombre', nombreCompleto)
                            .attr('data-programa', insc.NombrePrograma)
                            .text(etiqueta);

                        $select.append($option);
                    });

                    $('#col-resultado-busqueda').show();
                    cargarLog(ci);

                    if (data.resultados.length === 1) {
                        $select.val(data.resultados[0].EstudianteID).trigger('change');
                    }
                },
                error: function() {
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
                }
            });
        }

        $('#btn-buscar-ci').on('click', buscarPorCI);
        $('#input-buscar-ci').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarPorCI();
            }
        });

        $('#select-inscripcion').on('change', function() {
            var $opt = $(this).find('option:selected');
            var estudianteID = $(this).val();
            var programaID = $opt.data('programa-id');

            $('#card-trabajo').hide();
            idTrabajoActual = null;

            if (!estudianteID || !programaID) {
                $('#bloque-datos').hide();
                $('#card-checklist').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-programa').text($opt.data('programa'));
            $('#bloque-datos').show();

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'estadoDocumentos', estudianteID: estudianteID, programaID: programaID },
                dataType: 'json',
                success: function(data) {
                    if (!data.success) return;

                    $('#card-checklist').show();
                    var $contenedor = $('#contenedor-checklist');
                    $contenedor.empty();

                    var claves = Object.keys(data.tipos);
                    var emitidos = claves.filter(function(clave) { return data.estado[clave]; }).length;
                    var aprobados = claves.filter(function(clave) { return data.estado[clave] && data.estado[clave].Estado === 'APROBADO'; }).length;
                    $('#contador-documentos').html('<strong>' + emitidos + ' de ' + claves.length + '</strong> emitidos · <strong>' + aprobados + '</strong> aprobados');

                    claves.forEach(function(clave) {
                        var etiqueta = data.tipos[clave];
                        var emitido = data.estado[clave];

                        var html = '<div class="item-checklist ' + (emitido ? 'emitido' : 'pendiente') + '">' +
                            '<div class="icono"><i class="fas ' + (emitido ? 'fa-check' : 'fa-clock') + '"></i></div>' +
                            '<div class="rd-doc-texto">' +
                                '<div class="titulo-doc">' + esc(etiqueta) + '</div>' +
                                '<div class="detalle-doc">' +
                                    (emitido
                                        ? 'Emitido el ' + esc(emitido.FechaEmision) + (emitido.NumeroVaucher ? ' · Voucher: ' + esc(emitido.NumeroVaucher) : '')
                                        : 'Todavía no se ha generado') +
                                '</div>' +
                                (emitido ? '<div class="rd-acciones">' + htmlAcciones(emitido) + '</div>' : '') +
                            '</div>' +
                            (emitido ? htmlEstado(emitido) : '<span class="badge-estado-doc pendiente">Sin generar</span>') +
                        '</div>';

                        $contenedor.append(html);
                    });

                    if (data.trabajo) {
                        idTrabajoActual = data.trabajo.IdTrabajo;
                        $('#edit-titulo').val(data.trabajo.TituloTrabajo);
                        $('#edit-calificacion').val(data.trabajo.CalificacionNumeral);
                        $('#edit-tribunal').val((data.trabajo.Tribunal || '').split('|').join('\n'));
                        $('#card-trabajo').show();
                    }
                }
            });
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
        // ===== Reimprimir con los datos guardados (editables) =====
        var ETIQUETAS_CAMPO = {
            nombre: 'Nombre del estudiante', programa: 'Programa', nivel: 'Nivel', anio: 'Año',
            vaucher: 'N° de voucher', monto: 'Monto pagado (Bs.)', fecha: 'Fecha del documento', curso: 'Curso',
            titulo: 'Título del trabajo', tribunal: 'Tribunal (uno por línea)', entrega: 'Fecha de entrega',
            horaEntrega: 'Hora de entrega', calificacion: 'Calificación', tratamiento: 'Tratamiento',
            tipotrabajo: 'Tipo de trabajo', coordinador: 'Coordinador(a)', director: 'Director(a)'
        };
        var CAMPOS_FECHA = ['fecha', 'entrega'];
        var CAMPOS_ANCHOS = ['nombre', 'programa', 'titulo', 'tribunal'];

        function abrirVentana() { $('#ventana-reimprimir').addClass('abierta'); }
        function cerrarVentana() { $('#ventana-reimprimir').removeClass('abierta'); }

        $(document).on('click', '[data-cerrar-ventana]', cerrarVentana);
        $('#ventana-reimprimir').on('click', function(e) { if (e.target === this) cerrarVentana(); });
        $(document).on('keydown', function(e) { if (e.key === 'Escape') cerrarVentana(); });

        $(document).on('click', '[data-reimprimir]', function() {
            var id = $(this).data('reimprimir');
            var tipoTexto = $(this).closest('tr, .item-checklist').find('.rd-tipo, .titulo-doc').first().text();
            $.post('ajax/certificado.ajax.php', { accion: 'obtenerDocumento', idDocumento: id }, function(data) {
                if (!data.success || !data.documento.datos || !data.documento.rutaPdf) {
                    Swal.fire({ type: 'error', title: 'No se puede reimprimir', text: data.mensaje || 'El documento no tiene datos guardados.' });
                    return;
                }
                var doc = data.documento;
                var $campos = $('#campos-reimprimir').empty();
                $('#form-reimprimir').attr('action', doc.rutaPdf);
                $('#ventana-reimprimir-titulo').text('Reimprimir: ' + (tipoTexto || 'documento'));

                if (doc.estado === 'APROBADO') {
                    $campos.append('<p class="rd-ventana-aviso"><i class="fas fa-info-circle"></i> Este documento ya fue aprobado por el decano. Si cambia algún dato, volverá a quedar <strong>por aprobar</strong>.</p>');
                }

                $campos.append($('<input type="hidden" name="idDocumento">').val(doc.idDocumento));
                Object.keys(doc.datos).forEach(function(clave) {
                    var valor = doc.datos[clave];
                    // estudianteID, programaID y cualquier otro dato interno van ocultos
                    if (!ETIQUETAS_CAMPO[clave]) {
                        $campos.append($('<input type="hidden">').attr('name', clave).val(valor));
                        return;
                    }
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

        // Al imprimir, los campos editables pasan al formulario con su nombre original
        // (el tribunal vuelve a ir separado por "|", como lo envía su pantalla)
        $('#form-reimprimir').on('submit', function() {
            var $form = $(this);
            $form.find('input[data-copia]').remove();
            $form.find('[data-campo]').each(function() {
                var clave = $(this).data('campo');
                var valor = $(this).val();
                if (clave === 'tribunal') {
                    valor = valor.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l; }).join('|');
                }
                $form.append($('<input type="hidden" data-copia>').attr('name', clave).val(valor));
            });
            cerrarVentana();
            // El estado puede cambiar si se corrigió un dato de un documento aprobado
            setTimeout(refrescar, 1500);
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
                text: 'Se marcará como aprobado para continuar con el trámite.',
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
    });
    </script>
</body>
