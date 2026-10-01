<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<style>
.card-certificado {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.card-certificado h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e3c72;
    margin-bottom: 20px;
}

.dato-solo-lectura {
    background: #f8f9fa;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    padding: 10px 14px;
    font-weight: 600;
    color: #464457;
    min-height: 42px;
}

.item-checklist {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 2px solid #e8ecef;
}

.item-checklist.emitido {
    border-color: #b7e0c4;
    background: #f2faf5;
}

.item-checklist.pendiente {
    border-color: #f0d9b5;
    background: #fffaf2;
}

.item-checklist .icono {
    font-size: 26px;
    width: 32px;
    text-align: center;
}

.item-checklist.emitido .icono { color: #1e7145; }
.item-checklist.pendiente .icono { color: #b7791f; }

.item-checklist .titulo-doc {
    font-weight: 700;
    color: #333;
}

.item-checklist .detalle-doc {
    font-size: 12px;
    color: #74788d;
}

.badge-estado-doc {
    margin-left: auto;
    padding: 5px 12px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    color: white;
}

.badge-estado-doc.emitido { background: #1e7145; }
.badge-estado-doc.pendiente { background: #b7791f; }

.btn-guardar-trabajo {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.tabla-log {
    font-size: 12px;
}

.tabla-log thead {
    background: linear-gradient(135deg, #52616e 0%, #37424a 100%);
}

.tabla-log thead th {
    color: white;
    font-weight: 600;
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
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
                                        <h4>QUÉ CERTIFICADOS YA SE EMITIERON Y CUÁLES FALTAN</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container">

                            <div class="card-certificado">
                                <h3><i class="fas fa-id-card"></i> Buscar Estudiante por Carnet (CI)</h3>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><strong>N° de Carnet</strong></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="input-buscar-ci" placeholder="Ej: 7456178" inputmode="numeric" autocomplete="off">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" id="btn-buscar-ci">
                                                        <i class="fas fa-search"></i> Buscar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7" id="col-resultado-busqueda" style="display:none;">
                                        <div class="form-group">
                                            <label><strong>Resultado</strong></label>
                                            <select class="form-control" id="select-inscripcion" style="width: 100%;">
                                                <option value="">-- Seleccione --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="alerta-sin-resultado" class="alert alert-warning" style="display:none;">
                                    <i class="fas fa-exclamation-triangle"></i> No se encontró ningún estudiante activo con ese carnet.
                                </div>
                                <div class="row" id="bloque-datos" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Nombre</strong></label>
                                            <div class="dato-solo-lectura" id="dato-nombre"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Programa</strong></label>
                                            <div class="dato-solo-lectura" id="dato-programa"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-checklist" style="display:none;">
                                <h3><i class="fas fa-clipboard-check"></i> Documentos del Estudiante</h3>
                                <div id="contenedor-checklist"></div>
                            </div>

                            <div class="card-certificado" id="card-trabajo" style="display:none;">
                                <h3><i class="fas fa-edit"></i> Trabajo de Grado Registrado</h3>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><strong>Título del Trabajo</strong></label>
                                            <textarea class="form-control" id="edit-titulo" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Calificación Numeral</strong></label>
                                            <input type="number" class="form-control" id="edit-calificacion" min="0" max="100">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label><strong>Tribunal (uno por línea, ej: "PRESIDENTE: NOMBRE")</strong></label>
                                            <textarea class="form-control" id="edit-tribunal" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-guardar-trabajo" id="btn-guardar-trabajo">
                                    <i class="fas fa-save"></i> Guardar cambios
                                </button>
                            </div>

                            <div class="card-certificado">
                                <h3><i class="fas fa-history"></i> Últimos Documentos Emitidos</h3>
                                <div class="table-responsive">
                                    <table class="table table-hover tabla-log">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo de Documento</th>
                                                <th>Estudiante</th>
                                                <th>N° Vaucher</th>
                                                <th>Emitido por</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla-log-body"></tbody>
                                    </table>
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

        function cargarLog(ci) {
            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'listarDocumentosEmitidos', ci: ci || '' },
                dataType: 'json',
                success: function(data) {
                    var $tbody = $('#tabla-log-body');
                    $tbody.empty();

                    if (!data.success || !data.documentos || data.documentos.length === 0) {
                        $tbody.append('<tr><td colspan="5" class="text-center text-muted">Sin documentos emitidos todavía</td></tr>');
                        return;
                    }

                    data.documentos.forEach(function(doc) {
                        var etiquetaTipo = data.tipos[doc.TipoDocumento] || doc.TipoDocumento;
                        $tbody.append(
                            '<tr>' +
                            '<td>' + doc.FechaEmision + '</td>' +
                            '<td>' + etiquetaTipo + '</td>' +
                            '<td>' + (doc.NombreEstudiante || '-') + '</td>' +
                            '<td>' + (doc.NumeroVaucher || '-') + '</td>' +
                            '<td>' + (doc.UsuarioEmisor || '-') + '</td>' +
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
                Swal.fire({ icon: 'warning', title: 'Ingrese un carnet', text: 'Escriba el número de carnet del estudiante' });
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
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
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

                    Object.keys(data.tipos).forEach(function(clave) {
                        var etiqueta = data.tipos[clave];
                        var emitido = data.estado[clave];

                        var html = '<div class="item-checklist ' + (emitido ? 'emitido' : 'pendiente') + '">' +
                            '<div class="icono"><i class="fas ' + (emitido ? 'fa-check-circle' : 'fa-clock') + '"></i></div>' +
                            '<div>' +
                                '<div class="titulo-doc">' + etiqueta + '</div>' +
                                '<div class="detalle-doc">' +
                                    (emitido
                                        ? 'Emitido el ' + emitido.FechaEmision + (emitido.NumeroVaucher ? ' · Vaucher: ' + emitido.NumeroVaucher : '')
                                        : 'Todavía no se ha generado') +
                                '</div>' +
                            '</div>' +
                            '<span class="badge-estado-doc ' + (emitido ? 'emitido' : 'pendiente') + '">' +
                                (emitido ? 'EMITIDO' : 'PENDIENTE') +
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
                Swal.fire({ icon: 'warning', title: 'Falta el título' });
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
                        Swal.fire({ icon: 'success', title: 'Guardado', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'No se pudo guardar' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'No se pudo guardar' });
                }
            });
        });
    });
    </script>
</body>
