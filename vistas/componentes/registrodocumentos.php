<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
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

@media (max-width: 767.98px) {
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

                            <!-- Registro de emisiones -->
                            <div class="rd-card">
                                <div class="rd-card-head">
                                    <div>
                                        <h3><i class="fas fa-history"></i> Últimos documentos emitidos</h3>
                                        <p id="subtitulo-log">Emisiones más recientes de todo el sistema.</p>
                                    </div>
                                </div>
                                <div class="rd-card-body">
                                    <div class="rd-tabla-marco table-responsive">
                                        <table class="table table-hover tabla-log">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Tipo de documento</th>
                                                    <th>Estudiante</th>
                                                    <th>N° voucher</th>
                                                    <th>Emitido por</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabla-log-body"></tbody>
                                        </table>
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

        function cargarLog(ci) {
            $('#subtitulo-log').text(ci ? 'Documentos emitidos al estudiante con C.I. ' + ci + '.' : 'Emisiones más recientes de todo el sistema.');

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'listarDocumentosEmitidos', ci: ci || '' },
                dataType: 'json',
                success: function(data) {
                    var $tbody = $('#tabla-log-body');
                    $tbody.empty();

                    if (!data.success || !data.documentos || data.documentos.length === 0) {
                        $tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">Sin documentos emitidos todavía</td></tr>');
                        return;
                    }

                    data.documentos.forEach(function(doc) {
                        var etiquetaTipo = data.tipos[doc.TipoDocumento] || doc.TipoDocumento;
                        $tbody.append(
                            '<tr>' +
                            '<td class="text-nowrap">' + esc(doc.FechaEmision) + '</td>' +
                            '<td><span class="rd-tipo">' + esc(etiquetaTipo) + '</span></td>' +
                            '<td>' + esc(doc.NombreEstudiante || '-') + '</td>' +
                            '<td>' + esc(doc.NumeroVaucher || '-') + '</td>' +
                            '<td>' + esc(doc.UsuarioEmisor || '-') + '</td>' +
                            '</tr>'
                        );
                    });
                }
            });
        }

        cargarLog();

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
                    $('#contador-documentos').html('<strong>' + emitidos + ' de ' + claves.length + '</strong> emitidos');

                    claves.forEach(function(clave) {
                        var etiqueta = data.tipos[clave];
                        var emitido = data.estado[clave];

                        var html = '<div class="item-checklist ' + (emitido ? 'emitido' : 'pendiente') + '">' +
                            '<div class="icono"><i class="fas ' + (emitido ? 'fa-check' : 'fa-clock') + '"></i></div>' +
                            '<div>' +
                                '<div class="titulo-doc">' + esc(etiqueta) + '</div>' +
                                '<div class="detalle-doc">' +
                                    (emitido
                                        ? 'Emitido el ' + esc(emitido.FechaEmision) + (emitido.NumeroVaucher ? ' · Voucher: ' + esc(emitido.NumeroVaucher) : '')
                                        : 'Todavía no se ha generado') +
                                '</div>' +
                            '</div>' +
                            '<span class="badge-estado-doc ' + (emitido ? 'emitido' : 'pendiente') + '">' +
                                (emitido ? 'Emitido' : 'Pendiente') +
                            '</span>' +
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
    });
    </script>
</body>
