<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left
kt-aside--fixed kt-page--loading">

<!-- Estilos de la vista: van antes del contenido para que no se vea sin estilo al cargar -->
<style>
/* =========================================================
   INSCRIPCIÓN (prefijo in-): validar el voucher de matrícula de un
   pre-registro. Mismo lenguaje visual que Programas: tarjetas blancas,
   azul institucional y botones del formato unificado del sistema.
   ========================================================= */
.in-contenedor {
    --in-primario: #1e3c72;
    --in-primario-suave: #eef2f9;
    --in-texto: #1f2937;
    --in-texto-2: #64748b;
    --in-borde: #e3e7ef;
    max-width: 860px;
    margin: 0 auto;
}

/* ---------- Pasos ---------- */
.in-pasos {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
}
.in-paso {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--in-texto-2);
}
.in-paso strong { display: block; font-size: 13.5px; color: var(--in-texto-2); }
.in-paso small { display: block; font-size: 12px; color: #94a3b8; }
.in-paso-num {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 700;
    font-size: 14px;
    background: #fff;
    border: 2px solid #cbd5e1;
    color: #94a3b8;
}
.in-paso.active .in-paso-num {
    background: var(--in-primario);
    border-color: var(--in-primario);
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .3);
}
.in-paso.active strong { color: var(--in-primario); }
.in-paso.active small { color: var(--in-texto-2); }
.in-paso-linea { flex: 1; height: 2px; background: #d8dde8; border-radius: 2px; }

/* ---------- Tarjeta de búsqueda ---------- */
.in-card {
    background: #fff;
    border: 1px solid var(--in-borde);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
}
.in-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid var(--in-borde);
}
.in-card-head h3 { font-size: 16px; font-weight: 700; margin: 0; color: var(--in-texto); }
.in-card-head h3 i { color: var(--in-primario); margin-right: 4px; }
.in-card-head p { font-size: 12.5px; color: var(--in-texto-2); margin: 3px 0 0; }
.in-card-head .btn-abrir-panel { white-space: nowrap; padding: 8px 14px; }
.in-card-body { padding: 22px 24px 20px; }

.in-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--in-texto);
    margin-bottom: 6px;
}

.in-buscador { position: relative; display: flex; gap: 10px; }
.in-buscador-icono {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}
.in-buscador .form-control {
    height: 46px;
    padding-left: 40px;
    border: 1px solid #d8dde8;
    border-radius: 10px;
    font-size: 14px;
}
.in-buscador .form-control:focus {
    border-color: var(--in-primario);
    box-shadow: 0 0 0 3px rgba(30, 60, 114, .12);
}
.in-buscador .in-btn-primario { height: 46px; padding: 0 22px; }

.in-ayuda {
    margin: 12px 0 0;
    padding: 10px 14px;
    background: var(--in-primario-suave);
    border-radius: 10px;
    font-size: 12.5px;
    color: #334155;
}
.in-ayuda i { color: var(--in-primario); margin-right: 4px; }

/* Botón principal de la vista (Buscar, Seleccionar): azul institucional */
.in-btn-primario {
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
.in-btn-primario:hover, .in-btn-primario:focus {
    background: #16305c;
    border-color: #16305c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .25);
}

/* ---------- Ventanas (resultados y validación) ---------- */
.in-modal-head {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    border-bottom: 0;
    align-items: flex-start;
}
.in-modal-head small {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .6px;
    opacity: .75;
}
.in-modal-head .modal-title,
.in-modal-head .modal-title i { color: #fff !important; font-weight: 700; opacity: 1; }
.in-modal-head .close { color: #fff; opacity: .85; text-shadow: none; }
.in-modal-head .close:hover { opacity: 1; }

/* Lista de resultados */
.in-resultados { display: flex; flex-direction: column; gap: 10px; }
.in-resultado {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 14px;
    border: 1px solid #e3e7ef;
    border-radius: 12px;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.in-resultado:hover { border-color: #b9c6dd; box-shadow: 0 4px 12px rgba(15, 23, 42, .06); }
.in-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef2f9;
    color: #1e3c72;
    font-weight: 700;
    font-size: 13px;
}
.in-resultado-datos { flex: 1; min-width: 0; }
.in-resultado-datos strong { display: block; color: #1f2937; font-size: 14px; }
.in-resultado-datos small { display: block; color: #64748b; font-size: 12px; margin-top: 2px; }
.in-resultado-datos small i { width: 14px; color: #94a3b8; }
.in-resultado .in-btn-primario { height: 36px; padding: 0 14px; font-size: 12.5px; }

.in-vacio { text-align: center; padding: 26px 10px; color: #64748b; }
.in-vacio i { display: block; font-size: 28px; color: #c7cdd8; margin-bottom: 10px; }
.in-vacio strong { display: block; color: #1f2937; margin-bottom: 4px; }
.in-vacio span { font-size: 12.5px; }

/* #formValidarVoucher envuelve el modal-body y el modal-footer (ver comentario
   junto a su apertura en el HTML). Bootstrap necesita que ambos sean hijos
   directos de .modal-content para que el layout flex de modal-dialog-scrollable
   funcione (.modal-body con flex:1 1 auto + overflow-y:auto); si quedan dentro
   de un <form> normal, ese flex no se aplica y el contenido se corta sin poder
   desplazarse. display:contents saca al form del árbol de cajas sin quitarlo
   del DOM, así el scroll interno vuelve a funcionar y el submit sigue igual. */
#formValidarVoucher {
    display: contents;
}

/* Validar voucher: estudiante, datos a comparar, voucher y requisitos */
.vv-estudiante {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
}
.vv-nombre { font-size: 17px; font-weight: 700; color: #1f2937; line-height: 1.3; }
.vv-sub { font-size: 12.5px; color: #64748b; margin-top: 2px; }
.vv-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    padding: 4px 10px;
    border-radius: 999px;
    background: #eef2fa;
    color: #1e3c72;
    font-size: 12px;
    font-weight: 600;
}

/* Monto y cuenta: grandes, para mirarlos junto al voucher físico */
.vv-comparar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: #dbe3f0;
    border: 1px solid #dbe3f0;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 14px;
}
.vv-dato { background: #f5f8fd; padding: 12px 16px; display: flex; flex-direction: column; }
.vv-etiqueta { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
.vv-valor { font-size: 22px; font-weight: 700; color: #1e3c72; letter-spacing: .5px; line-height: 1.25; }
.vv-cuenta-datos { font-size: 11px; color: #64748b; }

.vv-exonerada {
    margin: 0 0 14px;
    padding: 8px 12px;
    border-radius: 10px;
    background: #f3faf6;
    color: #2f855a;
    font-size: 13px;
    font-weight: 600;
}
.vv-exonerada i { margin-right: 4px; }

.vv-bloque { padding-top: 12px; border-top: 1px solid #edf0f5; margin-bottom: 12px; }

/* Foto: miniatura + botones Archivo / Cámara */
.vv-foto { display: flex; align-items: center; gap: 10px; }
.vv-foto-caja {
    position: relative;
    flex: 0 0 62px;
    height: 62px;
    margin: 0;
    border: 1px dashed #c7cdd8;
    border-radius: 8px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a0aec0;
    font-size: 20px;
    cursor: pointer;
    overflow: hidden;
}
.vv-foto-caja #voucherFotoPreview { position: absolute; inset: 0; }
.vv-foto-caja img { width: 100%; height: 100%; object-fit: cover; }
.vv-foto-acciones { min-width: 0; }
.vv-foto-botones { display: flex; gap: 6px; flex-wrap: wrap; }
.vv-foto-botones .btn { font-size: 12.5px; }
.vv-foto-botones .captura-foto-boton { margin-top: 0 !important; }
.vv-foto-nombre { display: block; margin-top: 4px; font-size: 11.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.vv-contador { font-weight: 500; color: #64748b; margin-left: 4px; }

.vv-pie { flex-wrap: nowrap; }
.vv-aviso { font-size: 12px; color: #8a6d1d; }
.vv-aviso i { color: #d69e2e; margin-right: 3px; }

@media (max-width: 575.98px) {
    .vv-estudiante { flex-direction: column; }
    .vv-comparar { grid-template-columns: 1fr; }
    .vv-pie { flex-wrap: wrap; }
}

.filtro-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 5px;
}

/* Requisitos entregados: casillas en dos columnas */
.in-requisitos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 14px; }
.in-requisito {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 8px 10px;
    border: 1px solid #e3e7ef;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #1f2937;
    cursor: pointer;
}
.in-requisito input { width: 16px; height: 16px; accent-color: #2f855a; }
.in-requisito:has(input:checked) { background: #f3faf6; border-color: #c6e9d4; }
.in-requisito-auto { cursor: default; }
.in-link { background: none; border: 0; padding: 0; color: #1e3c72; font-size: 12px; font-weight: 600; text-transform: none; letter-spacing: 0; cursor: pointer; }
.in-link:hover { text-decoration: underline; }
@media (max-width: 575.98px) { .in-requisitos { grid-template-columns: 1fr; } }

#modalValidarVoucher .form-control-sm { height: 38px; border-radius: 8px; font-size: 13px; }

/* Resumen que se confirma antes de inscribir (swal): con scroll propio para que
   los botones no queden fuera de la pantalla */
.in-swal-resumen { width: 560px; max-width: 94vw; }
.in-swal-resumen .swal-title { font-size: 20px; padding-top: 18px; }
.in-swal-resumen .swal-content { max-height: 62vh; overflow-y: auto; margin-top: 12px; }
.in-swal-resumen .swal-button--confirm { background: #1e3c72; }
.in-swal-resumen .swal-button--confirm:not([disabled]):hover { background: #16305c; }
.in-resumen { width: 100%; font-size: 13px; text-align: left; border-collapse: collapse; }
.in-resumen th, .in-resumen td { padding: 6px 8px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
.in-resumen th { width: 38%; color: #64748b; font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: .3px; }
.in-resumen td { color: #1e293b; }
.in-resumen-pendiente { color: #b45309; }
.in-resumen-foto { display: block; max-width: 100%; max-height: 160px; margin: 12px auto 0; border: 1px solid #e2e8f0; border-radius: 8px; }


@media (max-width: 767.98px) {
    .in-card-head { flex-direction: column; align-items: flex-start; }
    .in-buscador { flex-direction: column; }
    .in-buscador-icono { top: 23px; }
    .in-paso small { display: none; }
    .in-resultado { flex-wrap: wrap; }
    .in-resultado .in-btn-primario { width: 100%; }
}
</style>

  <!-- Header Mobile -->
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
  <!-- End Header Mobile -->

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background-color: #EAEAEA; font-size:12pt;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

        <?php
          $NavBar = new FuncionesControladores();
          $NavBar->NavBarControlador();
        ?>

        <button class="kt-aside-close" id="kt_aside_close_btn">
          <i class="la la-close"></i>
        </button>

        <?php
          $Sidebar = new FuncionesControladores();
          $Sidebar->SidebarControlador();
        ?>

        <!-- Cuerpo principal -->
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>MATRICULACIÓN</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>VALIDAR VOUCHER DE MATRÍCULA</h3>
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

            <!-- Contenido principal -->
            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="in-contenedor">

                <!-- Indicador de pasos (#pasoBuscar / #pasoValidar: el JS alterna .active) -->
                <div class="in-pasos">
                  <div class="in-paso active" id="pasoBuscar">
                    <span class="in-paso-num">1</span>
                    <div>
                      <strong>Buscar estudiante</strong>
                      <small>Con pre-registro pendiente</small>
                    </div>
                  </div>
                  <div class="in-paso-linea"></div>
                  <div class="in-paso" id="pasoValidar">
                    <span class="in-paso-num">2</span>
                    <div>
                      <strong>Validar voucher</strong>
                      <small>Inscripción formal al programa</small>
                    </div>
                  </div>
                </div>

                <!-- PASO 1: BUSCAR ESTUDIANTE CON PREREGISTRO PENDIENTE -->
                <div class="in-card">
                  <div class="in-card-head">
                    <div>
                      <h3><i class="fa fa-search"></i> Buscar estudiante</h3>
                      <p>Busque al estudiante que ya tiene su orden de pago de matrícula generada en Pre-Registro.</p>
                    </div>
                    <a href="preregistro" class="btn btn-abrir-panel">
                      <i class="fa fa-user-plus"></i> Nuevo pre-registro
                    </a>
                  </div>
                  <div class="in-card-body">
                    <label class="in-label" for="buscarTermino">C.I., nombre o apellido</label>
                    <div class="in-buscador">
                      <i class="fa fa-id-card in-buscador-icono"></i>
                      <input type="text" id="buscarTermino" class="form-control"
                             placeholder="Ej: 1234567 o Pérez" maxlength="60" autocomplete="off">
                      <button type="button" id="btnBuscarPreregistro" class="btn in-btn-primario">
                        <i class="fa fa-search"></i> Buscar
                      </button>
                    </div>
                    <p class="in-ayuda">
                      <i class="fa fa-info-circle"></i> Solo aparecen estudiantes con una orden de pago de matrícula <strong>pendiente</strong>.
                      Al validar su voucher quedan inscritos formalmente en el programa.
                    </p>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer->FooterResumidoControlador();
        ?>
      </div>
    </div>
  </div>

  <!-- ============================================ -->
  <!-- MODAL PASO 2: VALIDAR VOUCHER DE MATRÍCULA    -->
  <!-- (antes un panel inline; ahora modal, mismo contenido e ids). Se coloca
       fuera de #kt_wrapper, al mismo nivel que ModalResultadosBusqueda: dentro
       de #kt_wrapper el tema aplica transform a contenedores del layout, lo
       que rompe el position:fixed de un modal anidado ahí adentro. -->
  <!-- ============================================ -->
  <div class="modal fade" id="modalValidarVoucher" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header in-modal-head">
          <div>
            <small>Paso 2 de 2</small>
            <h5 class="modal-title"><i class="fa fa-check-circle"></i> Validar voucher de matrícula</h5>
          </div>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- Formulario de validación del voucher: envuelve todo el cuerpo del modal
             (igual que en los demás modales de esta app), aunque el submit arma su
             propio FormData a mano, no depende de la posición de los campos. -->
        <!-- novalidate: los campos obligatorios los revisa el submit (la foto va en un
             input oculto y el navegador no podría señalarlo). -->
        <form id="formValidarVoucher" enctype="multipart/form-data" novalidate>

          <div class="modal-body">
            <div id="panelDetalle" style="display:none;">

              <input type="hidden" id="detalleIdOrdenPago">

              <!-- Solo lo necesario para contrastar con el voucher físico: quién
                   pagó, cuánto y a qué cuenta. -->

              <!-- Estudiante, programa y plan elegido en Pre-Registro -->
              <div class="vv-estudiante">
                <div class="vv-estudiante-datos">
                  <div class="vv-nombre" id="detalleNombre"></div>
                  <div class="vv-sub">
                    C.I. <span id="detalleCi"></span> &middot;
                    <span id="detallePrograma"></span> (<span id="detalleCodigo"></span>)
                  </div>
                </div>
                <div id="planPropuestaInfo"></div>
              </div>

              <!-- Lo que debe coincidir con el voucher -->
              <div class="vv-comparar">
                <div class="vv-dato">
                  <span class="vv-etiqueta">Monto</span>
                  <span class="vv-valor">Bs. <span id="detalleMonto">0.00</span></span>
                </div>
                <div class="vv-dato">
                  <span class="vv-etiqueta">Cuenta</span>
                  <span class="vv-valor" id="detalleCuentaNumero">-</span>
                  <small class="vv-cuenta-datos" id="detalleCuentaDatos"></small>
                </div>
              </div>

              <!-- Matrícula exonerada en Pre-Registro (Bs. 0): no se pide voucher -->
              <div class="vv-exonerada" id="avisoMatriculaExonerada" style="display:none;">
                <i class="fa fa-check-circle"></i> Matrícula exonerada: no requiere voucher
              </div>

              <!-- Voucher -->
              <div class="vv-bloque">
                <div class="row" id="filaCamposVoucher">
                  <div class="col-md-4 form-group mb-2" id="grupoVoucherNumero">
                    <label class="filtro-label" for="voucherNumero">N° de voucher</label>
                    <input type="text" class="form-control form-control-sm" id="voucherNumero" maxlength="25" required>
                  </div>
                  <div class="col-md-3 form-group mb-2">
                    <label class="filtro-label" for="voucherFecha">Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="voucherFecha" required
                           max="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div class="col-md-5 form-group mb-2" id="grupoVoucherFoto">
                    <label class="filtro-label">Foto</label>
                    <div class="vv-foto">
                      <label class="vv-foto-caja" for="voucherFoto" title="JPG, PNG, GIF o WEBP (máx. 5 MB)">
                        <i class="fa fa-image"></i>
                        <span id="voucherFotoPreview" style="display:none;"><img id="voucherFotoPreviewImg" src="" alt="Voucher"></span>
                      </label>
                      <div class="vv-foto-acciones">
                        <div class="vv-foto-botones">
                          <label class="btn btn-sm btn-outline-secondary mb-0" for="voucherFoto"><i class="fa fa-upload"></i> Archivo</label>
                          <input type="file" class="d-none" id="voucherFoto" accept="image/*" data-captura-foto required>
                        </div>
                        <small class="vv-foto-nombre" id="voucherFotoLabel">Sin foto</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- REQUISITOS ENTREGADOS (catálogo cargado por ajax/requisito.ajax.php).
                   Lo que no se marque queda pendiente en Matriculados > Requisitos;
                   no impide inscribir. -->
              <div class="vv-bloque mb-0" id="seccionRequisitos" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="filtro-label mb-0" title="Lo que falte se registra después en Matriculados">
                    Requisitos entregados <span class="vv-contador" id="contadorRequisitos"></span>
                  </span>
                  <button type="button" class="in-link" id="btnMarcarTodosRequisitos">Marcar todos</button>
                </div>
                <div class="in-requisitos" id="listaRequisitosInscripcion"></div>
              </div>

            </div>
          </div>

          <div class="modal-footer vv-pie">
            <small class="vv-aviso mr-auto" id="avisoValidacion">
              <i class="fa fa-exclamation-triangle"></i> Inscribe al estudiante; no se puede deshacer.
            </small>
            <button type="button" id="btnBuscarOtro" class="btn btn-outline-secondary">
              <i class="fa fa-arrow-left"></i> Volver
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-check-circle"></i> Inscribir
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Modal: Resultados de la búsqueda -->
  <div class="modal fade" id="ModalResultadosBusqueda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header in-modal-head">
          <div>
            <small>Paso 1 de 2</small>
            <h5 class="modal-title"><i class="fa fa-search"></i> Resultados de la búsqueda</h5>
          </div>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="resultadosBusquedaBody" style="max-height: 60vh; overflow-y: auto;">
          <!-- Se llena por JS -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="vistas/recursos/sweetalert.min.js"></script>

  <script>
  function iniciarInscripcion() {

      function actualizarFecha() {
          const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          const fecha = new Date().toLocaleDateString('es-ES', opciones);
          $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
      }
      actualizarFecha();
      setInterval(actualizarFecha, 60000);

      // ============================================
      // BÚSQUEDA DE PREREGISTROS PENDIENTES
      // ============================================
      function buscarPreregistros() {
          const termino = $('#buscarTermino').val().trim();

          if (termino.length < 2) {
              swal('Atención', 'Ingrese al menos 2 caracteres para buscar', 'warning');
              return;
          }

          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: { accion: 'buscarPreregistrosPendientes', termino: termino },
              dataType: 'json',
              success: function(resp) {
                  if (!resp.success) {
                      swal('Atención', resp.mensaje || 'No se pudo realizar la búsqueda', 'warning');
                      return;
                  }
                  renderResultadosBusqueda(resp.resultados || []);
                  $('#ModalResultadosBusqueda').modal('show');
              },
              error: function() {
                  swal('Error', 'No se pudo realizar la búsqueda', 'error');
              }
          });
      }

      function escaparHtml(texto) {
          return $('<div>').text(texto == null ? '' : String(texto)).html();
      }

      function renderResultadosBusqueda(lista) {
          if (!lista || lista.length === 0) {
              $('#resultadosBusquedaBody').html(
                  '<div class="in-vacio"><i class="fa fa-user-slash"></i><strong>Sin resultados</strong>' +
                  '<span>No hay pre-registros pendientes con ese criterio. Revise el C.I. o el apellido.</span></div>'
              );
              return;
          }

          let html = '<div class="in-resultados">';
          lista.forEach(function(pr) {
              const nombreCompleto = ((pr.Apaterno || '') + ' ' + (pr.Amaterno || '') + ' ' + (pr.Nombre || '')).trim();
              const iniciales = ((pr.Apaterno || '').charAt(0) + (pr.Nombre || '').charAt(0)).toUpperCase();
              html += '' +
                  '<div class="in-resultado">' +
                      '<span class="in-avatar">' + escaparHtml(iniciales) + '</span>' +
                      '<div class="in-resultado-datos">' +
                          '<strong>' + escaparHtml(nombreCompleto) + '</strong>' +
                          '<small><i class="fa fa-id-card"></i> ' + escaparHtml(pr.Ci || '') + '</small>' +
                          '<small><i class="fa fa-graduation-cap"></i> ' + escaparHtml(pr.NombrePrograma || '') + ' (' + escaparHtml(pr.CodigoPrograma || '') + ')</small>' +
                      '</div>' +
                      '<button type="button" class="btn in-btn-primario btn-seleccionar-preregistro" data-id="' + pr.IdOrdenPago + '">' +
                          'Seleccionar <i class="fa fa-arrow-right"></i>' +
                      '</button>' +
                  '</div>';
          });
          html += '</div>';

          $('#resultadosBusquedaBody').html(html);
      }

      // ============================================
      // REQUISITOS DE MATRICULACIÓN: casillas del modal de validación. Se cargan al
      // elegir el preregistro, con los requisitos del grado académico de su programa
      // (en doctorado se piden más documentos).
      // ============================================
      function cargarRequisitosInscripcion(programaID) {
      $('#seccionRequisitos').hide();
      $('#listaRequisitosInscripcion').empty();
      $.ajax({
          url: 'ajax/requisito.ajax.php',
          type: 'POST',
          dataType: 'json',
          data: { accion: 'catalogo', programaID: programaID },
          success: function(resp) {
              if (!resp.success || !resp.requisitos || !resp.requisitos.length) return;
              let html = '';
              resp.requisitos.forEach(function(r) {
                  // Los automáticos (ficha, documento de compromiso) los marca el sistema al validar
                  const auto = parseInt(r.Automatico) === 1;
                  html += '<label class="in-requisito' + (auto ? ' in-requisito-auto' : '') + '"' + (auto ? ' title="Se marca automáticamente al validar el voucher"' : '') + '>' +
                          '<input type="checkbox" class="chk-requisito" value="' + parseInt(r.IdRequisito) + '"' + (auto ? ' checked disabled' : '') + '>' +
                          '<span>' + escaparHtml(r.Nombre) + (auto ? ' <small class="text-muted">(automático)</small>' : '') + '</span></label>';
              });
              $('#listaRequisitosInscripcion').html(html);
              actualizarContadorRequisitos();
              $('#seccionRequisitos').show();
          }
      });
      }

      $('#btnMarcarTodosRequisitos').on('click', function() {
          $('#listaRequisitosInscripcion .chk-requisito').prop('checked', true);
          actualizarContadorRequisitos();
      });

      $('#btnBuscarPreregistro').on('click', buscarPreregistros);
      $('#buscarTermino').on('keypress', function(e) {
          if (e.which === 13) {
              e.preventDefault();
              buscarPreregistros();
          }
      });

      // ============================================
      // SELECCIONAR UN PREREGISTRO (muestra el panel de validación)
      // ============================================
      $(document).on('click', '.btn-seleccionar-preregistro', function() {
          seleccionarPreregistro($(this).data('id'));
      });

      // Desde Pre-Registro (botón "Validar voucher"): inscripcion?orden=ID abre la
      // validación de esa solicitud directamente, sin buscarla.
      const ordenDesdeUrl = parseInt(new URLSearchParams(window.location.search).get('orden'), 10);
      if (ordenDesdeUrl > 0) {
          seleccionarPreregistro(ordenDesdeUrl);
          // Se quita de la URL para que al recargar no se vuelva a abrir
          window.history.replaceState(null, '', window.location.pathname);
      }

      // C.I. del preregistro abierto: al inscribir se abre Matriculados filtrado por él
      let ciSeleccionado = '';

      function seleccionarPreregistro(id) {
          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: { accion: 'obtenerPreregistro', idOrdenPago: id },
              dataType: 'json',
              success: function(resp) {
                  if (!resp.success) {
                      swal('Error', resp.mensaje || 'No se pudo cargar el preregistro', 'error');
                      return;
                  }

                  const p = resp.preregistro;
                  const nombreCompleto = ((p.Apaterno || '') + ' ' + (p.Amaterno || '') + ' ' + (p.Nombre || '')).trim();

                  $('#detalleIdOrdenPago').val(p.IdOrdenPago);
                  ciSeleccionado = p.Ci || '';
                  $('#detalleNombre').text(nombreCompleto);
                  $('#detalleCi').text(p.CiCompleto || '');
                  $('#detallePrograma').text(p.NombrePrograma || '');
                  $('#detalleCodigo').text(p.CodigoPrograma || '');
                  $('#detalleMonto').text(parseFloat(p.MontoFinal).toFixed(2));

                  // Cuenta de depósito (la elegida en Pre-Registro o la principal de
                  // matrícula del programa; ver ObtenerPreregistroModelo)
                  if (p.CuentaNumero) {
                      $('#detalleCuentaNumero').text(p.CuentaNumero);
                      $('#detalleCuentaDatos').text(
                          [p.CuentaBanco, p.CuentaTipo].filter(Boolean).join(' - ')
                      );
                  } else {
                      $('#detalleCuentaNumero').text('Sin cuenta registrada');
                      $('#detalleCuentaDatos').text('Registre la cuenta de matrícula del programa en Programas > Cuentas.');
                  }

                  // Reiniciar el formulario de validación
                  $('#formValidarVoucher')[0].reset();
                  cargarRequisitosInscripcion(p.ProgramaID);
                  $('#voucherFecha').val(new Date().toISOString().split('T')[0]);
                  $('#voucherFotoLabel').text('Sin foto');
                  $('#voucherFotoPreview').hide();
                  $('#voucherFotoPreviewImg').attr('src', '');

                  // Matrícula exonerada (Bs. 0) en Pre-Registro: no se pide voucher
                  const montoFinal = parseFloat(p.MontoFinal) || 0;
                  mostrarCamposVoucher(montoFinal <= 0 && parseInt(p.PagoCompleto, 10) !== 1);

                  // El plan de pago del programa y su % de descuento ya se definieron en
                  // Pre-Registro; aquí solo se muestran como referencia. Quedan definitivos
                  // al validar el voucher (en los tres planes).
                  const planTipo = p.PlanPropuestoTipo || 'REGULAR';
                  const planPorcentaje = parseFloat(p.PlanPropuestoPorcentaje) || 0;
                  const nombresPlan = { REGULAR: 'Regular', CONTADO: 'al Contado', GRUPAL: 'Grupal' };

                  $('#planPropuestaInfo').html(
                      '<span class="vv-chip" title="Plan elegido en Pre-Registro; queda definitivo al inscribir">' +
                      '<i class="fa fa-tags"></i> Plan ' + nombresPlan[planTipo] +
                      (planPorcentaje > 0 ? ' &middot; ' + parseFloat(planPorcentaje.toFixed(2)) + '% programa' : '') +
                      (planPorcentaje < 0 ? ' &middot; aumento de ' + parseFloat((-planPorcentaje).toFixed(2)) + '% programa' : '') +
                      (parseInt(p.PlanPropuestoNumeroCuotas, 10) > 0
                          ? ' &middot; ' + parseInt(p.PlanPropuestoNumeroCuotas, 10) + (parseInt(p.PlanPropuestoNumeroCuotas, 10) === 1 ? ' cuota' : ' cuotas')
                          : '') +
                      '</span>'
                  );

                  $('#ModalResultadosBusqueda').modal('hide');
                  $('#panelDetalle').slideDown();
                  $('#pasoBuscar').removeClass('active');
                  $('#pasoValidar').addClass('active');
                  // Nota: ya no se anima el scroll de la página hacia #panelDetalle
                  // (quedaba dentro de un modal oculto en este punto, así que
                  // .offset() daba {top:0} y el intento de mover el scroll del
                  // body mientras Bootstrap lo bloquea para abrir el modal es lo
                  // que generaba el aviso de "scroll-linked effect" y trababa el
                  // scroll). El modal maneja su propio scroll interno.
              },
              error: function() {
                  swal('Error', 'No se pudo cargar el preregistro', 'error');
              }
          });
      }

      // Volver a la búsqueda
      $('#btnBuscarOtro').on('click', function() {
          $('#panelDetalle').slideUp();
          $('#pasoValidar').removeClass('active');
          $('#pasoBuscar').addClass('active');
          $('#buscarTermino').val('').focus();
      });

      // Vista previa de la foto del voucher
      $('#voucherFoto').on('change', function() {
          const file = this.files[0];
          const fileName = file ? file.name : 'Sin foto';
          $('#voucherFotoLabel').text(fileName);

          if (file && file.type.startsWith('image/')) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  $('#voucherFotoPreviewImg').attr('src', e.target.result);
                  $('#voucherFotoPreview').show();
              };
              reader.readAsDataURL(file);
          } else {
              $('#voucherFotoPreview').hide();
          }
      });

      // ============================================
      // PLAN DE PAGO DEL PROGRAMA (posgrado, aparte de la matrícula)
      // Se define en Pre-Registro; el backend lo confirma automáticamente en la misma
      // transacción en que se valida el voucher de matrícula (ver ValidarVoucherMatriculaModelo).
      // ============================================

      // ============================================
      // MATRÍCULA EXONERADA EN PRE-REGISTRO: oculta los campos de voucher (no hay nada que
      // validar). Lo decide la orden, no se elige aquí; el servidor aplica la misma regla.
      // ============================================
      let matriculaExonerada = false;
      function mostrarCamposVoucher(exonerada) {
          matriculaExonerada = exonerada;
          const sinPago = exonerada;

          // Exonerada: no hay monto ni cuenta que comparar; solo se muestra el aviso
          $('#avisoMatriculaExonerada').toggle(sinPago);
          $('.vv-comparar').toggle(!sinPago);
          $('#grupoVoucherNumero, #grupoVoucherFoto').toggle(!sinPago);
          $('#voucherNumero').prop('required', !sinPago);
          $('#voucherFoto').prop('required', !sinPago);
      }

      // Requisitos marcados / total, junto al título
      function actualizarContadorRequisitos() {
          const $chk = $('#listaRequisitosInscripcion .chk-requisito');
          $('#contadorRequisitos').text($chk.length ? '(' + $chk.filter(':checked').length + ' de ' + $chk.length + ')' : '');
      }
      $(document).on('change', '#listaRequisitosInscripcion .chk-requisito', actualizarContadorRequisitos);

      // ============================================
      // VALIDAR EL VOUCHER (inscribe formalmente al estudiante)
      // ============================================
      $('#formValidarVoucher').on('submit', function(e) {
          e.preventDefault();

          const idOrdenPago = $('#detalleIdOrdenPago').val();
          const sinPago = matriculaExonerada;
          const numeroVoucher = sinPago ? '' : $('#voucherNumero').val().trim();
          const fechaInscripcion = $('#voucherFecha').val();

          if (!sinPago && !numeroVoucher) {
              swal('Atención', 'Ingrese el código de voucher', 'warning');
              return;
          }
          if (!fechaInscripcion) {
              swal('Atención', 'Seleccione la fecha de inscripción', 'warning');
              return;
          }

          const archivoFoto = $('#voucherFoto')[0].files[0];
          if (!sinPago && !archivoFoto) {
              swal('Atención', 'Debe subir la foto del voucher', 'warning');
              return;
          }

          const btnSubmit = $(this).find('button[type="submit"]');

          // Antes de guardar se muestra el detalle de lo que se va a registrar
          // (la inscripción no se puede deshacer)
          swal({
              title: '¿Inscribir al estudiante?',
              content: resumenInscripcion(sinPago, numeroVoucher, fechaInscripcion),
              className: 'in-swal-resumen',
              closeOnClickOutside: false,
              buttons: {
                  cancel: { text: 'Revisar', value: false, visible: true },
                  confirm: { text: 'Sí, inscribir', value: true }
              }
          }).then(function(confirmado) {
              if (confirmado) {
                  enviarInscripcion(idOrdenPago, numeroVoucher, fechaInscripcion, sinPago, archivoFoto, btnSubmit);
              }
          });
      });

      // Detalle que se muestra para confirmar: lo cargado del preregistro y lo
      // llenado en el formulario
      function resumenInscripcion(sinPago, numeroVoucher, fechaInscripcion) {
          const fila = function(etiqueta, valorHtml) {
              return '<tr><th>' + etiqueta + '</th><td>' + valorHtml + '</td></tr>';
          };
          const partesFecha = fechaInscripcion.split('-');
          const cuenta = escaparHtml($('#detalleCuentaNumero').text()) +
              ($('#detalleCuentaDatos').text() ? ' <small>(' + escaparHtml($('#detalleCuentaDatos').text()) + ')</small>' : '');

          let html = '<table class="in-resumen">' +
              fila('Estudiante', escaparHtml($('#detalleNombre').text())) +
              fila('C.I.', escaparHtml($('#detalleCi').text())) +
              fila('Programa', escaparHtml($('#detallePrograma').text()) + ' (' + escaparHtml($('#detalleCodigo').text()) + ')') +
              fila('Plan', escaparHtml($('#planPropuestaInfo').text().trim()));

          if (sinPago) {
              html += fila('Matrícula', 'Exonerada (sin voucher)');
          } else {
              html += fila('Monto', 'Bs. ' + escaparHtml($('#detalleMonto').text())) +
                      fila('Cuenta', cuenta) +
                      fila('N° de voucher', '<strong>' + escaparHtml(numeroVoucher) + '</strong>');
          }
          html += fila('Fecha', escaparHtml(partesFecha[2] + '/' + partesFecha[1] + '/' + partesFecha[0]));

          const $requisitos = $('#listaRequisitosInscripcion .chk-requisito');
          if ($requisitos.length) {
              const nombreRequisito = function(chk) {
                  return escaparHtml($(chk).closest('label').find('span').clone().children('small').remove().end().text().trim());
              };
              const entregados = $requisitos.filter(':checked').map(function() { return nombreRequisito(this); }).get();
              const pendientes = $requisitos.not(':checked').map(function() { return nombreRequisito(this); }).get();
              html += fila('Requisitos entregados', entregados.length ? entregados.join('<br>') : '<em>Ninguno</em>');
              if (pendientes.length) {
                  html += fila('Quedan pendientes', '<span class="in-resumen-pendiente">' + pendientes.join('<br>') + '</span>');
              }
          }
          html += '</table>';

          if (!sinPago && $('#voucherFotoPreviewImg').attr('src')) {
              html += '<img class="in-resumen-foto" src="' + $('#voucherFotoPreviewImg').attr('src') + '" alt="Voucher">';
          }

          const contenedor = document.createElement('div');
          contenedor.innerHTML = html;
          return contenedor;
      }

      function enviarInscripcion(idOrdenPago, numeroVoucher, fechaInscripcion, sinPago, archivoFoto, btnSubmit) {
          const formData = new FormData();
          formData.append('accion', 'validarVoucherMatricula');
          formData.append('idOrdenPago', idOrdenPago);
          formData.append('numeroVoucher', numeroVoucher);
          formData.append('fechaInscripcion', fechaInscripcion);

          // Requisitos: se envían todos (marcados y no marcados) con la fecha de inscripción
          $('#listaRequisitosInscripcion .chk-requisito').each(function() {
              formData.append('requisitos[' + this.value + '][entregado]', this.checked ? '1' : '0');
              formData.append('requisitos[' + this.value + '][fecha]', this.checked ? fechaInscripcion : '');
          });

          if (!sinPago) {
              formData.append('fotoVoucher', archivoFoto);
          }

          btnSubmit.prop('disabled', true);

          $.ajax({
              url: 'ajax/ordenpago.ajax.php',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(resp) {
                  if (resp.success) {
                      // El botón queda deshabilitado: se pasa a Matriculados, filtrado
                      // por el C.I. del estudiante recién inscrito
                      swal({
                          title: '¡Estudiante inscrito!',
                          text: 'Quedó inscrito formalmente en el programa y su plan de pago quedó CONFIRMADO. Abriendo Matriculados...',
                          icon: 'success',
                          buttons: false,
                          closeOnClickOutside: false,
                          timer: 2000
                      }).then(function() {
                          window.location.href = 'matriculados' + (ciSeleccionado ? '?buscar=' + encodeURIComponent(ciSeleccionado) : '');
                      });
                  } else {
                      btnSubmit.prop('disabled', false);
                      swal('Error', resp.mensaje || 'No se pudo validar el voucher', 'error');
                  }
              },
              error: function() {
                  btnSubmit.prop('disabled', false);
                  swal('Error', 'No se pudo validar el voucher', 'error');
              }
          });
      }

      // ============================================
      // MODAL "VALIDAR VOUCHER DE MATRÍCULA": puramente de presentación. Todo
      // el código de arriba sigue igual (busca, valida y hace slideDown()/
      // slideUp() sobre #panelDetalle exactamente como antes); este bloque
      // solo abre/cierra el modal que ahora lo contiene, enganchándose a los
      // mismos eventos AJAX ya existentes (no duplica ninguna validación ni
      // toca los manejadores de arriba).
      // ============================================
      $(document).ajaxSuccess(function(event, jqXHR, settings, respuesta) {
          // Se cargó el detalle de un preregistro seleccionado: abrir el modal.
          // Si el modal de resultados todavía se está cerrando (el código de
          // arriba ya llamó a su .modal('hide')), se espera a que termine esa
          // transición antes de abrir el siguiente: mostrar un modal mientras
          // otro todavía se está ocultando es lo que deja el scroll bloqueado
          // en Bootstrap 4 (backdrop duplicado).
          if (typeof settings.data === 'string' && settings.data.indexOf('accion=obtenerPreregistro') !== -1 && respuesta && respuesta.success) {
              if ($('#ModalResultadosBusqueda').hasClass('show')) {
                  $('#ModalResultadosBusqueda').one('hidden.bs.modal', function() {
                      $('#modalValidarVoucher').modal('show');
                  });
              } else {
                  $('#modalValidarVoucher').modal('show');
              }
          }
          // Se validó el voucher correctamente: cerrar el modal (el mensaje de
          // éxito y la limpieza del panel los sigue manejando el código de arriba).
          if (settings.data instanceof FormData && settings.data.get('accion') === 'validarVoucherMatricula' && respuesta && respuesta.success) {
              $('#modalValidarVoucher').modal('hide');
          }
      });

      $('#btnBuscarOtro').on('click', function() {
          $('#modalValidarVoucher').modal('hide');
      });

      console.log('=== INSCRIPCIÓN (VALIDAR VOUCHER) INICIALIZADO ===');
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', iniciarInscripcion);
  } else {
      iniciarInscripcion();
  }
  </script>


</body>
</html>
