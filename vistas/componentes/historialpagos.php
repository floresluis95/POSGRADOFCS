<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
/* =========================================================
   HISTORIAL DE PAGOS (prefijo hp-): matrícula y cuotas de un estudiante.
   Mismo lenguaje visual que Inscripción y Programas: tarjetas blancas,
   azul institucional y botones del formato unificado del sistema.
   ========================================================= */
.hp {
    --hp-primario: #1e3c72;
    --hp-primario-suave: #eef2f9;
    --hp-texto: #1f2937;
    --hp-texto-2: #64748b;
    --hp-borde: #e3e7ef;
    max-width: 1100px;
    margin: 0 auto;
}

.hp-card {
    background: #fff;
    border: 1px solid var(--hp-borde);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
    margin-bottom: 18px;
}
.hp-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--hp-borde);
}
.hp-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--hp-texto); }
.hp-card-head h3 i { color: var(--hp-primario); margin-right: 4px; }
.hp-card-head p { font-size: 12.5px; color: var(--hp-texto-2); margin: 3px 0 0; }
.hp-card-body { padding: 22px 24px 20px; }

/* ---------- Buscador ---------- */
.hp-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--hp-texto);
    margin-bottom: 6px;
}
/* Buscador compacto: ancho acotado, campo y botón de 38 px, esquinas sobrias */
.hp-buscador { position: relative; display: flex; gap: 8px; max-width: 560px; }
.hp-buscador-icono {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 13px;
    pointer-events: none;
}
.hp-buscador .form-control {
    height: 38px;
    padding-left: 34px;
    border: 1px solid #cfd5e1;
    border-radius: 6px;
    font-size: 13px;
}
.hp-buscador .form-control:focus {
    border-color: var(--hp-primario);
    box-shadow: 0 0 0 2px rgba(30, 60, 114, .12);
}
.hp-buscador .hp-btn-primario { height: 38px; padding: 0 16px; border-radius: 6px; font-size: 13px; }
.hp-ayuda {
    margin: 6px 0 0;
    font-size: 11.5px;
    color: var(--hp-texto-2);
}
.hp-ayuda i { color: var(--hp-primario); margin-right: 4px; }
/* Tarjeta del buscador más baja que las de resultados */
.hp-card-busqueda .hp-card-head { padding: 12px 20px; }
.hp-card-busqueda .hp-card-head h3 { font-size: 14px; }
.hp-card-busqueda .hp-card-body { padding: 14px 20px 16px; }

/* Botón principal de la vista (Buscar, Ver historial): azul institucional */
.hp-btn-primario {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    background: #1e3c72;
    border: 1px solid #1e3c72;
    color: #fff;
    border-radius: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.hp-btn-primario:hover, .hp-btn-primario:focus {
    background: #16305c;
    border-color: #16305c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .25);
}

/* ---------- Estado vacío ---------- */
.hp-vacio { text-align: center; padding: 42px 20px; color: var(--hp-texto-2); }
.hp-vacio i { display: block; font-size: 34px; color: #c7cdd8; margin-bottom: 12px; }
.hp-vacio strong { display: block; color: var(--hp-texto); font-size: 15px; margin-bottom: 4px; }
.hp-vacio span { font-size: 13px; }

/* ---------- Lista de coincidencias ---------- */
.hp-resultados { display: flex; flex-direction: column; gap: 10px; }
.hp-resultado {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border: 1px solid var(--hp-borde);
    border-radius: 12px;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.hp-resultado:hover { border-color: #b9c6dd; box-shadow: 0 4px 12px rgba(15, 23, 42, .06); }
.hp-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--hp-primario-suave);
    color: var(--hp-primario);
    font-weight: 700;
    font-size: 13px;
}
.hp-resultado-datos { flex: 1; min-width: 0; }
.hp-resultado-datos strong { display: block; color: var(--hp-texto); font-size: 14px; }
.hp-resultado-datos small { color: var(--hp-texto-2); font-size: 12px; margin-right: 14px; }
.hp-resultado-datos small i { color: #94a3b8; margin-right: 3px; }
.hp-resultado .hp-btn-primario { height: 36px; padding: 0 14px; font-size: 12.5px; }

/* ---------- Ficha del estudiante ---------- */
.hp-estudiante { display: flex; align-items: center; gap: 16px; padding: 18px 24px; }
.hp-estudiante .hp-avatar { width: 54px; height: 54px; font-size: 17px; }
.hp-estudiante h4 { margin: 0 0 6px; font-size: 18px; font-weight: 700; color: var(--hp-texto); }
.hp-datos { display: flex; flex-wrap: wrap; gap: 6px 18px; font-size: 12.5px; color: var(--hp-texto-2); }
.hp-datos i { color: #94a3b8; margin-right: 4px; }
/* Ir a Matriculados con el estudiante: a la derecha de la ficha */
.hp-btn-ir-matriculados {
    margin-left: auto; flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px;
    height: 34px; padding: 0 14px; border-radius: 6px; font-size: 12.5px; font-weight: 600;
    background: #fff; color: #1e3c72; border: 1px solid #1e3c72; white-space: nowrap;
}
.hp-btn-ir-matriculados:hover, .hp-btn-ir-matriculados:focus { background: #1e3c72; color: #fff; text-decoration: none; }

/* ---------- Programa ---------- */
.hp-programa-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px 16px;
    padding: 16px 24px;
    border-bottom: 1px solid var(--hp-borde);
    border-left: 4px solid var(--hp-primario);
}
.hp-programa-head h5 { margin: 0; font-size: 15px; font-weight: 700; color: var(--hp-texto); }
.hp-programa-head h5 i { color: var(--hp-primario); margin-right: 6px; }
.hp-programa-head small { display: block; margin-top: 3px; font-size: 12px; color: var(--hp-texto-2); }

.hp-resumen {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 18px 24px 4px;
}
.hp-resumen-item {
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid var(--hp-borde);
    border-radius: 10px;
}
.hp-resumen-item span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; color: var(--hp-texto-2); }
.hp-resumen-item strong { display: block; margin-top: 2px; font-size: 18px; color: var(--hp-primario); }
.hp-resumen-item small { font-size: 12px; color: var(--hp-texto-2); }

.hp-seccion { padding: 16px 24px 4px; }
.hp-seccion:last-child { padding-bottom: 22px; }
.hp-seccion-titulo {
    font-size: 12px;
    font-weight: 700;
    color: var(--hp-primario);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 10px;
}
.hp-seccion-titulo i { margin-right: 4px; }
.hp-seccion-titulo span { color: var(--hp-texto-2); font-weight: 500; text-transform: none; letter-spacing: 0; }

.hp-tabla { margin: 0; font-size: 13px; }
.hp-tabla thead th {
    background: #f8fafc;
    color: var(--hp-texto-2);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    border-top: 0;
    border-bottom: 1px solid var(--hp-borde);
    white-space: nowrap;
}
.hp-tabla td { vertical-align: middle; border-color: #eef1f6; color: var(--hp-texto); }
.hp-tabla-marco { border: 1px solid var(--hp-borde); border-radius: 10px; overflow: hidden; }
.hp-nota { font-size: 12.5px; color: var(--hp-texto-2); padding: 12px 14px; background: #f8fafc; border-radius: 10px; }

/* Etiquetas de estado y de plan */
.hp-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
    white-space: nowrap;
}
.hp-chip.is-ok { background: #e7f6ee; color: #2f855a; }
.hp-chip.is-pendiente { background: #fff6e0; color: #b7791f; }
.hp-chip.is-rechazado { background: #fdecec; color: #c53030; }
.hp-chip.is-plan { background: var(--hp-primario-suave); color: var(--hp-primario); }

.foto-voucher-thumb {
    width: 46px;
    height: 46px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--hp-borde, #e3e7ef);
    cursor: zoom-in;
    transition: transform .15s ease;
}
.foto-voucher-thumb:hover { transform: scale(1.06); }

/* ---------- Ventanas ---------- */
.hp-modal-head {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-bottom: 0;
    align-items: flex-start;
}
.hp-modal-head small {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #fff;
    opacity: .75;
}
.hp-modal-head .modal-title,
.hp-modal-head .modal-title i { color: #fff !important; font-weight: 700; opacity: 1; }
.hp-modal-head .close,
.hp-modal-head .close span { color: #fff !important; opacity: .9; text-shadow: none; }
.hp-modal-head .close:hover { opacity: 1; }
#modalEditarPagoHistorial .form-control { height: 40px; border-radius: 8px; }
#modalEditarPagoHistorial .custom-file-label { border-radius: 8px; }
#modalEditarPagoHistorial .custom-file-label::after { content: "Examinar"; }
.hp-voucher-numero { display: inline-block; padding: 6px 14px; background: var(--hp-primario-suave, #eef2f9); border-radius: 8px; color: #1e3c72; }

@media (max-width: 767.98px) {
    .hp-card-head { flex-direction: column; align-items: flex-start; }
    .hp-buscador { flex-direction: column; }
    .hp-buscador-icono { top: 23px; }
    .hp-resultado { flex-wrap: wrap; }
    .hp-resultado .hp-btn-primario { width: 100%; }
    .hp-resumen { grid-template-columns: 1fr; }
    .hp-estudiante { align-items: flex-start; flex-wrap: wrap; }
    .hp-btn-ir-matriculados { margin-left: 0; width: 100%; justify-content: center; }
}
</style>

  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
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
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>HISTORIAL DE PAGOS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>CONSULTA POR ESTUDIANTE</h3>
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

            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="hp">

                <!-- Buscador unificado de estudiante -->
                <div class="hp-card hp-card-busqueda">
                  <div class="hp-card-head">
                    <div>
                      <h3><i class="fa fa-search"></i> Consulta de historial de pagos</h3>
                      <p>Matrícula, cuotas y clínicas del estudiante en cada programa.</p>
                    </div>
                  </div>
                  <div class="hp-card-body">
                    <label class="hp-label" for="valorBusquedaHistorial">Carnet de identidad o nombre del estudiante</label>
                    <div class="hp-buscador">
                      <i class="fa fa-id-card hp-buscador-icono"></i>
                      <input type="text" id="valorBusquedaHistorial" class="form-control"
                             placeholder="Ej.: 7456178 o Pérez Gómez" maxlength="150" autocomplete="off">
                      <button type="button" id="btnBuscarHistorial" class="btn hp-btn-primario">
                        <i class="fa fa-search"></i> Buscar
                      </button>
                    </div>
                    <p class="hp-ayuda">
                      <i class="fa fa-info-circle"></i> C.I. sin complemento ni expedido, o nombre y/o apellidos.
                    </p>
                  </div>
                </div>

                <!-- Estado vacío / sin búsqueda todavía -->
                <div id="historialSinBuscar" class="hp-card">
                  <div class="hp-vacio">
                    <i class="fa fa-history"></i>
                    <strong>Aún no hay ninguna consulta</strong>
                    <span>Busque un estudiante para ver su historial de pagos.</span>
                  </div>
                </div>

                <!-- Resultado: lista de coincidencias (búsqueda por nombre con varios resultados) -->
                <div id="historialListaResultados" class="hp-card" style="display:none;">
                  <div class="hp-card-head">
                    <div>
                      <h3><i class="fa fa-users"></i> Seleccione un estudiante</h3>
                      <p id="historialListaResultadosResumen"></p>
                    </div>
                  </div>
                  <div class="hp-card-body">
                    <div class="hp-resultados" id="historialListaResultadosBody"></div>
                  </div>
                </div>

                <!-- Resultado: datos del estudiante -->
                <div id="historialResultado" style="display:none;">

                  <div class="hp-card">
                    <div class="hp-estudiante">
                      <span class="hp-avatar" id="historialEstudianteIniciales"></span>
                      <div>
                        <h4 id="historialEstudianteNombre"></h4>
                        <div class="hp-datos" id="historialEstudianteDatos"></div>
                      </div>
                      <!-- Matriculados filtrado por el C.I. del estudiante (matriculados?buscar=CI) -->
                      <a href="matriculados" class="btn hp-btn-ir-matriculados" id="historialIrMatriculados" title="Abrir Matriculados filtrado por este estudiante">
                        <i class="fa fa-user-graduate"></i> Ver en Matriculados
                      </a>
                    </div>
                  </div>

                  <div id="historialProgramas"></div>

                </div>

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

<!-- Modal: Foto del voucher (matrícula o cuota, según a quién se invoque) -->
<div class="modal fade" id="modalFotoHistorialPago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header hp-modal-head">
        <h5 class="modal-title"><i class="fa fa-image"></i> Foto del voucher</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-3" id="infoVoucherHistorialPago" style="display:none;">
          <span class="text-muted small d-block mb-1">N° de voucher</span>
          <strong id="numeroVoucherHistorialPagoGrande" class="hp-voucher-numero"></strong>
        </p>
        <img id="imagenHistorialPagoGrande" src="" alt="Foto del voucher"
             style="max-width:100%; max-height:70vh; border-radius:10px; background:#f8f9fa;">
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar pago (matrícula o cuota, según data-tipo del botón que lo abrió).
     Corrige errores de captura: fecha, N° de voucher, monto y, opcionalmente, la
     foto (si no se selecciona una nueva, se conserva la actual). No cambia el
     Estado del pago ni vuelve a validar/confirmar nada. -->
<div class="modal fade" id="modalEditarPagoHistorial" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header hp-modal-head">
        <div>
          <small>Corregir datos del pago</small>
          <h5 class="modal-title" id="tituloEditarPagoHistorial"><i class="fa fa-pencil-alt"></i> Editar pago</h5>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formEditarPagoHistorial" enctype="multipart/form-data">
        <input type="hidden" id="editPagoTipo" name="tipo">
        <input type="hidden" id="editPagoId">
        <div class="modal-body">
          <div class="form-group">
            <label class="hp-label" for="editPagoVoucher">N° de voucher <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="editPagoVoucher" maxlength="100" required data-voucher-partes>
          </div>
          <!-- Primero la fecha de la orden (se generó antes) y después la del pago, igual
               que las columnas F. orden y F. pago. La de la orden se oculta si el pago no tiene orden -->
          <div class="row">
            <div class="col-md-6 form-group" id="editPagoFechaOrdenGrupo">
              <label class="hp-label" for="editPagoFechaOrden">Fecha de la orden de pago</label>
              <input type="date" class="form-control" id="editPagoFechaOrden" max="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6 form-group">
              <label class="hp-label" for="editPagoFecha">Fecha de pago <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="editPagoFecha" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="hp-label" for="editPagoMonto">Monto (Bs.) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" class="form-control" id="editPagoMonto" required>
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="hp-label" for="editPagoFoto">Foto del voucher</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="editPagoFoto" accept="image/*" data-captura-foto>
              <label class="custom-file-label" for="editPagoFoto" id="editPagoFotoLabel">Conservar la foto actual</label>
            </div>
            <small class="form-text text-muted">Opcional: solo si desea reemplazar la foto actual (máx. 5MB).</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function iniciarHistorialPagos() {

    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    const etiquetasTipoPlan = {
        REGULAR: 'Plan Regular',
        DESCUENTO: 'Plan al Contado',
        GRUPAL: 'Plan Grupal'
    };

    const chipEstadoCuota = {
        PAGADO: 'is-ok',
        PENDIENTE: 'is-pendiente',
        RECHAZADO: 'is-rechazado',
        ANULADO: ''
    };

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        // "2026-10-13" o "2026-10-13 17:39:30" (fecha de la orden) → "13/10/2026"
        const partes = String(fecha).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!partes) return fecha;
        return partes[3] + '/' + partes[2] + '/' + partes[1];
    }

    function formatoBs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Escapar un valor para insertarlo en HTML armado por concatenación de
    // strings (texto o atributos como data-voucher), evitando que comillas u
    // otros caracteres de los datos rompan el markup.
    function escaparAtributo(valor) {
        const div = document.createElement('div');
        div.textContent = valor == null ? '' : String(valor);
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function iniciales(apaterno, nombre) {
        return ((apaterno || '').charAt(0) + (nombre || '').charAt(0)).toUpperCase();
    }

    function botonEditar(tipo, id, fecha, voucher, monto, titulo, fechaOrden) {
        return '<button type="button" class="btn btn-sm btn-warning btnEditarPagoHistorial" title="' + escaparAtributo(titulo) + '" ' +
                   'data-tipo="' + tipo + '" data-id="' + id + '" ' +
                   'data-fecha-orden="' + escaparAtributo(String(fechaOrden || '').slice(0, 10)) + '" ' +
                   'data-fecha="' + escaparAtributo(fecha || '') + '" data-voucher="' + escaparAtributo(voucher || '') + '" ' +
                   'data-monto="' + (parseFloat(monto) || 0).toFixed(2) + '" ' +
                   'data-titulo="' + escaparAtributo(titulo) + '">' +
                   '<i class="fa fa-pencil-alt"></i>' +
               '</button>';
    }

    // Se cambia al guardar una edición: obliga al navegador a pedir de nuevo las
    // fotos (si se reemplazó la foto, la URL es la misma y mostraba la anterior)
    let versionFotos = Date.now();

    function miniaturaFoto(tieneFoto, urlBase, voucher, alt) {
        urlBase += '&v=' + versionFotos;
        return tieneFoto
            ? '<img src="' + urlBase + '&thumb=1" class="foto-voucher-thumb" alt="' + alt + '" ' +
              'data-url="' + urlBase + '" data-voucher="' + escaparAtributo(voucher || '-') + '">'
            : '<span class="text-muted small">Sin foto</span>';
    }

    function renderPrograma(programa) {
        const tipoPlan = programa.TipoPlan
            ? '<span class="hp-chip is-plan"><i class="fa fa-list-ol"></i> ' + escaparAtributo(etiquetasTipoPlan[programa.TipoPlan] || programa.TipoPlan) + '</span>'
            : '<span class="hp-chip">Sin plan definido</span>';

        const cuotas = programa.Cuotas || [];
        const cuotasPagadas = cuotas.filter(function(c) { return c.Estado === 'PAGADO'; });
        const totalCuotasPagadas = cuotasPagadas.reduce(function(suma, c) { return suma + (parseFloat(c.Monto) || 0); }, 0);
        const matriculaExonerada = programa.EstadoInscripcion && parseFloat(programa.costomatricula) === 0 && !programa.nvauchermatricula;

        // Pagadas y pendientes juntas, en orden correlativo por N° de cuota (1, 2, 3...)
        const filas = [];
        const numero = function(n) { return parseInt(n, 10) || 9999; };
        if (cuotas.length > 0) {
            cuotas.forEach(function(c) {
                filas.push({ n: numero(c.NumeroCuota), html: '<tr>' +
                    '<td class="text-center"><strong>' + escaparAtributo(c.NumeroCuota || '-') + '</strong></td>' +
                    '<td class="text-right">' + formatoBs(c.Monto) + '</td>' +
                    '<td class="text-center">' + formatearFecha(c.FechaOrden) + '</td>' +
                    '<td class="text-center">' + formatearFecha(c.FechaPago) + '</td>' +
                    '<td>' + escaparAtributo(c.NumeroVoucher || '-') + '</td>' +
                    '<td class="text-center"><span class="hp-chip ' + (chipEstadoCuota[c.Estado] || '') + '">' + escaparAtributo(c.Estado) + '</span></td>' +
                    '<td class="text-center">' + miniaturaFoto(c.TieneFoto === 'SI', 'ajax/obtener-voucher-imagen.php?id=' + c.Idpagomodulo, c.NumeroVoucher, 'Foto voucher cuota') + '</td>' +
                    '<td class="text-center">' + botonEditar('cuota', c.Idpagomodulo, c.FechaPago, c.NumeroVoucher, c.Monto,
                        'Editar cuota' + (c.NumeroCuota ? ' N° ' + c.NumeroCuota : ''), c.FechaOrden) + '</td>' +
                '</tr>' });
            });
        }
        // Cuotas con orden de pago pero sin voucher validado: "Pendiente" (o voucher extraviado)
        (programa.CuotasPorPagar || []).forEach(function(o) {
            const extraviado = o.Estado === 'EXTRAVIADO';
            filas.push({ n: numero(o.NumeroCuota), html: '<tr class="hp-fila-pendiente">' +
                '<td class="text-center"><strong>' + escaparAtributo(o.NumeroCuota || '-') + '</strong></td>' +
                '<td class="text-right">' + formatoBs(o.MontoTotal) + '</td>' +
                '<td class="text-center">' + formatearFecha(o.FechaGeneracion) + '</td>' +
                '<td class="text-center">-</td>' +
                '<td>' + (extraviado
                    ? '<span class="text-muted small" title="' + escaparAtributo(o.MotivoExtraviado || '') + '">Por validar: ' + escaparAtributo(o.MotivoExtraviado || '') + '</span>'
                    : '<span class="text-muted small">Orden ' + escaparAtributo(o.NumeroOrden) + (o.FechaVencimiento ? ' · vence ' + formatearFecha(o.FechaVencimiento) : '') + '</span>') + '</td>' +
                '<td class="text-center"><span class="hp-chip is-pendiente">' + (extraviado ? 'VOUCHER POR VALIDAR' : 'PENDIENTE') + '</span></td>' +
                '<td class="text-center"><span class="text-muted small">-</span></td>' +
                '<td class="text-center"><span class="text-muted small">Sin validar</span></td>' +
            '</tr>' });
        });
        let filasCuotas = filas
            .sort(function(a, b) { return a.n - b.n; })
            .map(function(f) { return f.html; }).join('');
        if (!filasCuotas) {
            filasCuotas = '<tr><td colspan="8" class="text-center text-muted py-3">Sin cuotas registradas</td></tr>';
        }

        // Clínicas: solo si el estudiante tiene alguna (no todos los programas las manejan).
        // La orden confirmada = pago registrado; la foto/PDF sale de obtener-voucher-clinica.php
        const clinicas = programa.Clinicas || [];
        const totalClinicas = clinicas
            .filter(function(cl) { return cl.Estado === 'CONFIRMADO'; })
            .reduce(function(suma, cl) { return suma + (parseFloat(cl.MontoTotal) || 0); }, 0);
        let seccionClinicas = '';
        if (clinicas.length > 0) {
            let filasClinicas = '';
            clinicas.forEach(function(cl) {
                const pagada = cl.Estado === 'CONFIRMADO';
                const urlFoto = 'ajax/obtener-voucher-clinica.php?id=' + cl.IdPagoClinica;
                let foto = '<span class="text-muted small">Sin foto</span>';
                if (cl.IdPagoClinica && cl.TieneFoto == 1) {
                    foto = cl.EsPdf == 1
                        ? '<a href="' + urlFoto + '" target="_blank" title="Ver voucher (PDF)"><i class="fa fa-file-pdf fa-lg"></i></a>'
                        : miniaturaFoto(true, urlFoto, cl.NumeroVoucher, 'Foto voucher clínica');
                }
                filasClinicas += '<tr>' +
                    '<td>' + escaparAtributo(cl.NumeroOrden) + '</td>' +
                    '<td class="text-right">' + formatoBs(cl.MontoTotal) + '</td>' +
                    '<td class="text-center">' + formatearFecha(cl.FechaGeneracion) + '</td>' +
                    '<td class="text-center">' + formatearFecha(cl.FechaPago) + '</td>' +
                    '<td>' + escaparAtributo(cl.NumeroVoucher || '-') + '</td>' +
                    '<td class="text-center"><span class="hp-chip ' + (chipEstadoCuota[pagada ? 'PAGADO' : cl.Estado] || '') + '">' + (pagada ? 'PAGADO' : (cl.Estado === 'EXTRAVIADO' ? 'VOUCHER POR VALIDAR' : escaparAtributo(cl.Estado))) + '</span></td>' +
                    '<td class="text-center">' + foto + '</td>' +
                    '<td class="text-center">' + (pagada && cl.IdPagoClinica
                        ? botonEditar('clinica', cl.IdPagoClinica, cl.FechaPago, cl.NumeroVoucher, cl.MontoTotal,
                            'Editar clínica ' + (cl.NumeroOrden || ''), cl.FechaGeneracion)
                        : '<span class="text-muted small">Sin validar</span>') + '</td>' +
                '</tr>';
            });
            seccionClinicas =
                '<div class="hp-seccion">' +
                    '<div class="hp-seccion-titulo"><i class="fa fa-stethoscope"></i> Clínicas' +
                        ' <span>(aparte del saldo del programa · pagado ' + formatoBs(totalClinicas) + ')</span>' +
                    '</div>' +
                    '<div class="hp-tabla-marco table-responsive">' +
                        '<table class="table hp-tabla">' +
                            '<thead><tr>' +
                                '<th>N° orden</th>' +
                                '<th class="text-right">Monto</th>' +
                                '<th class="text-center" title="Fecha en que se generó la orden de pago">F. orden</th>' +
                                '<th class="text-center">F. pago</th>' +
                                '<th>Voucher</th>' +
                                '<th class="text-center">Estado</th>' +
                                '<th class="text-center">Foto</th>' +
                                '<th class="text-center" style="width:90px;">Acción</th>' +
                            '</tr></thead>' +
                            '<tbody>' + filasClinicas + '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>';
        }

        const seccionMatricula = matriculaExonerada
            ? '<div class="hp-nota"><i class="fa fa-info-circle"></i> Sin voucher de matrícula: se inscribió sin pago de matrícula y el programa completo se cobra por cuotas.</div>'
            : '<div class="hp-tabla-marco table-responsive">' +
                '<table class="table hp-tabla">' +
                    '<thead><tr>' +
                        '<th class="text-right">Monto</th>' +
                        '<th class="text-center" title="Fecha en que se generó la orden de pago">F. orden</th>' +
                        '<th class="text-center">F. pago</th>' +
                        '<th>N° voucher</th>' +
                        '<th class="text-center">Foto</th>' +
                        '<th class="text-center" style="width:90px;">Acción</th>' +
                    '</tr></thead>' +
                    '<tbody><tr>' +
                        '<td class="text-right"><strong>' + formatoBs(programa.costomatricula) + '</strong></td>' +
                        '<td class="text-center">' + formatearFecha(programa.FechaOrdenMatricula) + '</td>' +
                        '<td class="text-center">' + formatearFecha(programa.FechaInscripcion) + '</td>' +
                        '<td>' + escaparAtributo(programa.nvauchermatricula || '-') + '</td>' +
                        '<td class="text-center">' + miniaturaFoto(programa.TieneFotoMatricula === 'SI', 'ajax/obtener-foto-matricula.php?idInscripcion=' + programa.idInscripcion, programa.nvauchermatricula, 'Foto voucher matrícula') + '</td>' +
                        '<td class="text-center">' + botonEditar('matricula', programa.idInscripcion, programa.FechaInscripcion, programa.nvauchermatricula, programa.costomatricula, 'Editar matrícula', programa.FechaOrdenMatricula) + '</td>' +
                    '</tr></tbody>' +
                '</table>' +
              '</div>';

        return '<div class="hp-card">' +
            '<div class="hp-programa-head">' +
                '<div>' +
                    '<h5><i class="fa fa-graduation-cap"></i>' + escaparAtributo(programa.NombrePrograma) + '</h5>' +
                    '<small>' + escaparAtributo(programa.GradoAcademico || '') + '</small>' +
                '</div>' +
                tipoPlan +
            '</div>' +

            '<div class="hp-resumen">' +
                '<div class="hp-resumen-item"><span>Matrícula</span><strong>' + (matriculaExonerada ? 'Sin pago' : formatoBs(programa.costomatricula)) + '</strong>' +
                    '<small>' + (matriculaExonerada ? 'Inscrito sin matrícula' : 'Pagada el ' + formatearFecha(programa.FechaInscripcion)) + '</small></div>' +
                '<div class="hp-resumen-item"><span>Cuotas pagadas</span><strong>' + cuotasPagadas.length +
                    (programa.NumeroCuotasPlan ? ' <small>de ' + escaparAtributo(programa.NumeroCuotasPlan) + '</small>' : '') + '</strong>' +
                    '<small>' + cuotas.length + ' registrada(s)' +
                        ((programa.CuotasPorPagar || []).length ? ' · ' + programa.CuotasPorPagar.length + ' pendiente(s) de validar' : '') + '</small></div>' +
                '<div class="hp-resumen-item"><span>Total pagado en cuotas</span><strong>' + formatoBs(totalCuotasPagadas) + '</strong>' +
                    '<small>Solo cuotas en estado PAGADO</small></div>' +
            '</div>' +

            '<div class="hp-seccion">' +
                '<div class="hp-seccion-titulo"><i class="fa fa-file-invoice"></i> Matrícula</div>' +
                seccionMatricula +
            '</div>' +

            '<div class="hp-seccion">' +
                '<div class="hp-seccion-titulo"><i class="fa fa-history"></i> Cuotas del programa' +
                    (programa.NumeroCuotasPlan ? ' <span>(plan: ' + escaparAtributo(programa.NumeroCuotasPlan) + ' cuotas)</span>' : '') +
                '</div>' +
                '<div class="hp-tabla-marco table-responsive">' +
                    '<table class="table hp-tabla">' +
                        '<thead><tr>' +
                            '<th class="text-center" style="width:60px;">N°</th>' +
                            '<th class="text-right">Monto</th>' +
                            '<th class="text-center" title="Fecha en que se generó la orden de pago">F. orden</th>' +
                            '<th class="text-center">F. pago</th>' +
                            '<th>Voucher</th>' +
                            '<th class="text-center">Estado</th>' +
                            '<th class="text-center">Foto</th>' +
                            '<th class="text-center" style="width:90px;">Acción</th>' +
                        '</tr></thead>' +
                        '<tbody>' + filasCuotas + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>' +
            seccionClinicas +
        '</div>';
    }

    // Último criterio usado, para poder refrescar el historial tras editar un
    // pago sin importar si se llegó por C.I., por nombre (único) o eligiendo
    // de la lista de coincidencias.
    let ultimaBusqueda = null;

    function ciCompletoDe(est) {
        let ci = est.Ci;
        if (est.Complemento) ci += '-' + est.Complemento;
        return ci + ' ' + (est.Exp || '');
    }

    function mostrarResultadoEstudiante(resp) {
        const est = resp.estudiante;
        const nombreCompleto = est.Apaterno + ' ' + est.Amaterno + ' ' + est.Nombre;

        $('#historialEstudianteIniciales').text(iniciales(est.Apaterno, est.Nombre));
        $('#historialIrMatriculados').attr('href', 'matriculados?buscar=' + encodeURIComponent(est.Ci || ''));
        $('#historialEstudianteNombre').text(nombreCompleto);
        $('#historialEstudianteDatos').html(
            '<span><i class="fa fa-id-card"></i>C.I. ' + escaparAtributo(ciCompletoDe(est)) + '</span>' +
            (est.Correo ? '<span><i class="fa fa-envelope"></i>' + escaparAtributo(est.Correo) + '</span>' : '') +
            (est.Celular ? '<span><i class="fa fa-mobile-alt"></i>' + escaparAtributo(est.Celular) + '</span>' : '') +
            '<span><i class="fa fa-graduation-cap"></i>' + ((resp.programas || []).length) + ' programa(s)</span>'
        );

        if (!resp.programas || resp.programas.length === 0) {
            $('#historialProgramas').html(
                '<div class="hp-card"><div class="hp-vacio"><i class="fa fa-folder-open"></i>' +
                '<strong>Sin inscripciones</strong><span>Este estudiante no tiene inscripciones registradas.</span></div></div>'
            );
        } else {
            let html = '';
            resp.programas.forEach(function(p) {
                html += renderPrograma(p);
            });
            $('#historialProgramas').html(html);
        }

        $('#historialListaResultados').hide();
        $('#historialSinBuscar').hide();
        $('#historialResultado').show();
    }

    function refrescarUltimaBusqueda() {
        if (!ultimaBusqueda) return;
        if (ultimaBusqueda.tipo === 'ci') {
            buscarPorCi(ultimaBusqueda.ci);
        } else {
            buscarPorEstudianteId(ultimaBusqueda.estudianteId);
        }
    }

    const htmlBotonBuscar = '<i class="fa fa-search"></i> Buscar';

    // ciGuardado: al refrescar tras editar se repite la misma búsqueda aunque el
    // campo de búsqueda haya cambiado
    function buscarPorCi(ciGuardado) {
        const ci = typeof ciGuardado === 'string' ? ciGuardado : $('#valorBusquedaHistorial').val().trim();

        if (!ci) {
            Swal.fire({ type: 'warning', title: 'Atención', text: 'Ingrese un C.I. para buscar' });
            return;
        }

        const $btn = $('#btnBuscarHistorial');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorCi', ci: ci },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html(htmlBotonBuscar);

                if (!resp.success) {
                    $('#historialResultado').hide();
                    $('#historialListaResultados').hide();
                    $('#historialSinBuscar').show();
                    Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró al estudiante' });
                    return;
                }

                ultimaBusqueda = { tipo: 'ci', ci: ci };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                $btn.prop('disabled', false).html(htmlBotonBuscar);
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    // Un solo campo de búsqueda con varios parámetros posibles: si lo escrito
    // son solo dígitos se busca por C.I., si trae letras se busca por nombre
    // y/o apellidos. Así no hace falta un selector de criterio aparte.
    function ejecutarBusquedaHistorial() {
        const valor = $('#valorBusquedaHistorial').val().trim();

        if (!valor) {
            Swal.fire({ type: 'warning', title: 'Atención', text: 'Ingrese un C.I. o un nombre para buscar' });
            return;
        }

        if (/^\d+$/.test(valor)) {
            buscarPorCi();
        } else {
            buscarPorNombre();
        }
    }

    function renderListaCoincidencias(estudiantes) {
        let html = '';
        estudiantes.forEach(function(est) {
            html += '<div class="hp-resultado">' +
                '<span class="hp-avatar">' + escaparAtributo(iniciales(est.Apaterno, est.Nombre)) + '</span>' +
                '<div class="hp-resultado-datos">' +
                    '<strong>' + escaparAtributo(est.Apaterno + ' ' + est.Amaterno + ' ' + est.Nombre) + '</strong>' +
                    '<small><i class="fa fa-id-card"></i>' + escaparAtributo(ciCompletoDe(est)) + '</small>' +
                    (est.Correo ? '<small><i class="fa fa-envelope"></i>' + escaparAtributo(est.Correo) + '</small>' : '') +
                '</div>' +
                '<button type="button" class="btn hp-btn-primario btnVerHistorialEstudiante" data-id="' + est.EstudianteID + '">' +
                    'Ver historial <i class="fa fa-arrow-right"></i>' +
                '</button>' +
            '</div>';
        });
        $('#historialListaResultadosResumen').text(estudiantes.length + ' estudiantes coinciden con la búsqueda.');
        $('#historialListaResultadosBody').html(html);
        $('#historialResultado').hide();
        $('#historialSinBuscar').hide();
        $('#historialListaResultados').show();
    }

    function buscarPorEstudianteId(estudianteId) {
        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorEstudianteId', estudianteId: estudianteId },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró al estudiante' });
                    return;
                }

                ultimaBusqueda = { tipo: 'estudianteId', estudianteId: estudianteId };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    function buscarPorNombre() {
        const nombre = $('#valorBusquedaHistorial').val().trim();

        if (nombre.length < 3) {
            Swal.fire({ type: 'warning', title: 'Atención', text: 'Ingrese al menos 3 caracteres para buscar' });
            return;
        }

        const $btn = $('#btnBuscarHistorial');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorNombre', nombre: nombre },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html(htmlBotonBuscar);

                if (!resp.success) {
                    $('#historialResultado').hide();
                    $('#historialListaResultados').hide();
                    $('#historialSinBuscar').show();
                    Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró ningún estudiante' });
                    return;
                }

                if (resp.tipo === 'lista') {
                    renderListaCoincidencias(resp.estudiantes);
                    return;
                }

                ultimaBusqueda = { tipo: 'estudianteId', estudianteId: resp.estudiante.EstudianteID };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                $btn.prop('disabled', false).html(htmlBotonBuscar);
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    $('#btnBuscarHistorial').on('click', ejecutarBusquedaHistorial);
    $('#valorBusquedaHistorial').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            ejecutarBusquedaHistorial();
        }
    });

    $(document).on('click', '.btnVerHistorialEstudiante', function() {
        buscarPorEstudianteId($(this).data('id'));
    });

    // Ver la foto en grande: se dispara desde las miniaturas (data-url/data-voucher),
    // mostrando también el N° de voucher arriba de la imagen para que coincida con
    // el dato que ya se ve en la fila.
    $(document).on('click', '.foto-voucher-thumb', function() {
        const $img = $(this);
        const numeroVoucher = $img.data('voucher');

        $('#imagenHistorialPagoGrande').attr('src', $img.data('url'));

        if (numeroVoucher) {
            $('#numeroVoucherHistorialPagoGrande').text(numeroVoucher);
            $('#infoVoucherHistorialPago').show();
        } else {
            $('#infoVoucherHistorialPago').hide();
        }

        $('#modalFotoHistorialPago').modal('show');
    });

    // ============================================
    // EDITAR PAGO (matrícula o cuota): corrige fecha, N° de voucher, monto y,
    // opcionalmente, la foto. Al guardar, se vuelve a buscar el mismo estudiante
    // para refrescar el historial con los datos corregidos.
    // ============================================
    $(document).on('click', '.btnEditarPagoHistorial', function() {
        const $btn = $(this);

        $('#tituloEditarPagoHistorial').html('<i class="fa fa-pencil-alt"></i> ' + escaparAtributo($btn.data('titulo')));
        $('#editPagoTipo').val($btn.data('tipo'));
        $('#editPagoId').val($btn.data('id'));
        $('#editPagoVoucher').val($btn.data('voucher'));
        $('#editPagoFecha').val($btn.data('fecha'));
        $('#editPagoMonto').val($btn.data('monto'));
        // Fecha de la orden: solo si el pago tiene orden de pago (los antiguos pueden no tenerla)
        const fechaOrden = String($btn.attr('data-fecha-orden') || '');
        $('#editPagoFechaOrden').val(fechaOrden);
        $('#editPagoFechaOrdenGrupo').toggle(fechaOrden !== '');
        // Valores al abrir: el resumen antes de guardar marca lo que cambió
        $('#formEditarPagoHistorial').data('original', {
            titulo: String($btn.data('titulo') || ''),
            voucher: String($btn.data('voucher') || ''),
            fechaOrden: fechaOrden,
            fecha: String($btn.data('fecha') || ''),
            monto: parseFloat($btn.data('monto')) || 0
        });
        $('#editPagoFoto').val('').attr('accept', $btn.data('tipo') === 'clinica' ? 'image/*,application/pdf' : 'image/*');
        $('#editPagoFotoLabel').text('Conservar la foto actual');

        $('#modalEditarPagoHistorial').modal('show');
    });

    $('#editPagoFoto').on('change', function() {
        $('#editPagoFotoLabel').text(this.files[0] ? this.files[0].name : 'Conservar la foto actual');
    });

    const htmlBotonGuardar = '<i class="fa fa-save"></i> Guardar cambios';

    // Antes de guardar: resumen de lo que se va a guardar; lo que cambió se muestra
    // como "antes → ahora" para revisarlo
    $('#formEditarPagoHistorial').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const original = $(form).data('original') || {};
        const conFechaOrden = $('#editPagoFechaOrdenGrupo').css('display') !== 'none';
        const nuevo = {
            voucher: $('#editPagoVoucher').val().trim(),
            fechaOrden: $('#editPagoFechaOrden').val(),
            fecha: $('#editPagoFecha').val(),
            monto: parseFloat($('#editPagoMonto').val()) || 0
        };
        const hoy = $('#editPagoFecha').attr('max');
        if (conFechaOrden && (!nuevo.fechaOrden || nuevo.fechaOrden > hoy)) {
            Swal.fire({ type: 'warning', title: 'Revise la fecha de la orden', text: 'Elija una fecha que no sea posterior a hoy.' });
            return;
        }

        const valor = function(texto, antes, cambio) {
            return cambio
                ? '<span style="color:#9ca3af;text-decoration:line-through;">' + escaparAtributo(antes || '-') + '</span> &rarr; <strong style="color:#b45309;">' + escaparAtributo(texto) + '</strong>'
                : escaparAtributo(texto);
        };
        const fila = function(etiqueta, contenido) {
            return '<tr><th style="padding:6px 10px 6px 0;color:#6b7280;font-weight:500;white-space:nowrap;vertical-align:top;">' + etiqueta + '</th>' +
                '<td style="padding:6px 0;border-bottom:1px solid #edf0f4;">' + contenido + '</td></tr>';
        };
        const archivoFoto = $('#editPagoFoto')[0].files[0];
        let cambios = 0;
        const marcar = function(cambio) { if (cambio) cambios++; return cambio; };

        let filas = fila('N° de voucher', valor(nuevo.voucher, original.voucher, marcar(nuevo.voucher !== original.voucher)));
        if (conFechaOrden) {
            filas += fila('Fecha de la orden', valor(formatearFecha(nuevo.fechaOrden), formatearFecha(original.fechaOrden), marcar(nuevo.fechaOrden !== original.fechaOrden)));
        }
        filas += fila('Fecha de pago', valor(formatearFecha(nuevo.fecha), formatearFecha(original.fecha), marcar(nuevo.fecha !== original.fecha)));
        filas += fila('Monto', valor(formatoBs(nuevo.monto), formatoBs(original.monto), marcar(Math.abs(nuevo.monto - original.monto) > 0.004)));
        marcar(!!archivoFoto);
        filas += fila('Foto del voucher', archivoFoto
            ? '<strong style="color:#b45309;">Se reemplaza</strong> por ' + escaparAtributo(archivoFoto.name)
            : 'Se conserva la actual');

        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Revise los cambios',
            html: '<div style="font-size:13px;color:#6b7280;margin-bottom:8px;">' + escaparAtributo(original.titulo || 'Pago') + '</div>' +
                '<table style="width:100%;text-align:left;font-size:14px;border-collapse:collapse;">' + filas + '</table>' +
                (cambios === 0 ? '<div style="margin-top:10px;font-size:13px;color:#6b7280;">No cambió ningún dato.</div>' : ''),
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Revisar',
            reverseButtons: true
        }).then(function(resultado) {
            if (resultado.value) enviarEdicionPago(form);
        });
    });

    function enviarEdicionPago(form) {
        const tipo = $('#editPagoTipo').val();
        const id = $('#editPagoId').val();
        const acciones = {
            matricula: ['actualizarMatricula', 'idInscripcion'],
            cuota: ['actualizarCuota', 'idPagoModulo'],
            clinica: ['actualizarClinica', 'idPagoClinica']
        };
        const accion = acciones[tipo] || acciones.cuota;

        const formData = new FormData();
        formData.append('accion', accion[0]);
        formData.append(accion[1], id);
        formData.append('numeroVoucher', $('#editPagoVoucher').val().trim());
        formData.append('fechaPago', $('#editPagoFecha').val());
        formData.append('monto', $('#editPagoMonto').val());
        if ($('#editPagoFechaOrdenGrupo').css('display') !== 'none') {
            formData.append('fechaOrden', $('#editPagoFechaOrden').val());
        }

        const archivoFoto = $('#editPagoFoto')[0].files[0];
        if (archivoFoto) {
            formData.append('fotoVoucher', archivoFoto);
        }

        const $btnSubmit = $(form).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                $btnSubmit.prop('disabled', false).html(htmlBotonGuardar);

                if (!resp.success) {
                    Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo guardar el cambio' });
                    return;
                }

                // Refrescar de inmediato (no al cerrar el aviso), con fotos nuevas
                $('#modalEditarPagoHistorial').modal('hide');
                versionFotos = Date.now();
                refrescarUltimaBusqueda();
                Swal.fire({ type: 'success', title: 'Guardado', text: resp.mensaje, timer: 1800, showConfirmButton: false });
            },
            error: function() {
                $btnSubmit.prop('disabled', false).html(htmlBotonGuardar);
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo guardar el cambio' });
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarHistorialPagos);
} else {
    iniciarHistorialPagos();
}
</script>
