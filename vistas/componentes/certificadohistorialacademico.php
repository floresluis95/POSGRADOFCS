<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

require_once 'modelos/certificado.modelo.php';
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

.tabla-certificado {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.tabla-certificado thead {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.tabla-certificado thead th {
    color: white;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
}

.btn-generar-certificado {
    background: linear-gradient(135deg, #d9a941 0%, #b7791f 100%);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-generar-certificado:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(183, 121, 31, 0.4);
    color: white;
}

.btn-generar-certificado:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
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
                                    <h2>HISTORIAL ACADÉMICO</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>SOBRE FORMATO PRE-IMPRESO DE POSGRADO CENTRAL (HOJA OFICIO HORIZONTAL)</h4>
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
                                            <label><strong>Nivel</strong></label>
                                            <div class="dato-solo-lectura" id="dato-nivel"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><strong>Programa</strong></label>
                                            <div class="dato-solo-lectura" id="dato-programa"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>N° de Vaucher</strong></label>
                                            <input type="text" class="form-control" id="input-vaucher" placeholder="Número de vaucher de pago">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Fecha del historial</strong></label>
                                            <input type="date" class="form-control" id="input-fecha">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-modulos" style="display:none;">
                                <h3><i class="fas fa-list-ol"></i> Módulos y Calificaciones</h3>
                                <div class="table-responsive">
                                    <table class="table tabla-certificado" id="tabla-modulos-certificado">
                                        <thead>
                                            <tr>
                                                <th>Sigla</th>
                                                <th>Módulo</th>
                                                <th class="text-center">Nota</th>
                                                <th class="text-center">Fecha Aprob.</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div id="alerta-sin-modulos" class="alert alert-warning" style="display:none;">
                                    <i class="fas fa-exclamation-triangle"></i> Este estudiante no tiene calificaciones registradas en el programa seleccionado.
                                </div>
                                <div class="alert alert-info" id="alerta-mas-de-12" style="display:none;">
                                    <i class="fas fa-info-circle"></i> Este programa tiene más de 12 módulos: el historial se generará en varias hojas.
                                </div>
                                <div class="text-right mt-3">
                                    <button type="button" class="btn-generar-certificado" id="btn-generar-certificado" disabled>
                                        <i class="fas fa-file-pdf"></i> Generar Historial PDF
                                    </button>
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

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-historial-academico-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="programa" id="hidden-programa">
        <input type="hidden" name="nivel" id="hidden-nivel">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="fecha" id="hidden-fecha">
    </form>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script>
    $(document).ready(function() {
        var hoy = new Date();
        var hoyISO = hoy.toISOString().split('T')[0];
        $('#input-fecha').val(hoyISO);

        var modulosActuales = [];

        function limpiarResultadoBusqueda() {
            $('#select-inscripcion').empty().append('<option value="">-- Seleccione --</option>');
            $('#col-resultado-busqueda').hide();
            $('#bloque-datos').hide();
            $('#card-modulos').hide();
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
                        var nombreCompleto = (insc.Nombre + ' ' + insc.Apaterno + ' ' + insc.Amaterno).trim();
                        var ciCompleto = insc.Ci + (insc.Complemento ? '-' + insc.Complemento : '');
                        var etiqueta = nombreCompleto + ' (' + ciCompleto + ') - ' + insc.NombrePrograma + ' - ' + insc.GradoAcademico;

                        var $option = $('<option></option>')
                            .val(insc.EstudianteID)
                            .attr('data-programa-id', insc.ProgramaID)
                            .attr('data-nombre', nombreCompleto)
                            .attr('data-programa', insc.NombrePrograma)
                            .attr('data-nivel', insc.GradoAcademico)
                            .text(etiqueta);

                        $select.append($option);
                    });

                    $('#col-resultado-busqueda').show();

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

            if (!estudianteID || !programaID) {
                $('#bloque-datos').hide();
                $('#card-modulos').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-programa').text($opt.data('programa'));
            $('#dato-nivel').text($opt.data('nivel'));
            $('#bloque-datos').show();

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: {
                    accion: 'modulosCalificados',
                    estudianteID: estudianteID,
                    programaID: programaID
                },
                dataType: 'json',
                success: function(data) {
                    $('#card-modulos').show();
                    var $tbody = $('#tabla-modulos-certificado tbody');
                    $tbody.empty();

                    if (!data.success || !data.modulos || data.modulos.length === 0) {
                        modulosActuales = [];
                        $('#alerta-sin-modulos').show();
                        $('#alerta-mas-de-12').hide();
                        $('#btn-generar-certificado').prop('disabled', true);
                        return;
                    }

                    $('#alerta-sin-modulos').hide();
                    modulosActuales = data.modulos;
                    $('#alerta-mas-de-12').toggle(modulosActuales.length > 12);

                    data.modulos.forEach(function(mod) {
                        var fecha = mod.FechaRegistro ? mod.FechaRegistro.split('-').reverse().join('/') : '-';
                        $tbody.append(
                            '<tr>' +
                            '<td>' + mod.codigomodulo + '</td>' +
                            '<td>' + mod.nombremodulo + '</td>' +
                            '<td class="text-center">' + parseInt(mod.Nota) + '</td>' +
                            '<td class="text-center">' + fecha + '</td>' +
                            '</tr>'
                        );
                    });

                    $('#btn-generar-certificado').prop('disabled', false);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener las calificaciones' });
                }
            });
        });

        $('#btn-generar-certificado').on('click', function() {
            var vaucher = $('#input-vaucher').val().trim();
            var fecha = $('#input-fecha').val();

            if (!vaucher) {
                Swal.fire({ icon: 'warning', title: 'Falta el N° de Vaucher', text: 'Ingrese el número de vaucher antes de generar el historial' });
                return;
            }
            if (!fecha) {
                Swal.fire({ icon: 'warning', title: 'Falta la fecha', text: 'Seleccione la fecha del historial' });
                return;
            }
            if (!modulosActuales.length) {
                Swal.fire({ icon: 'warning', title: 'Sin calificaciones', text: 'El estudiante no tiene módulos calificados en este programa' });
                return;
            }

            var $opt = $('#select-inscripcion').find('option:selected');

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-nombre').val($opt.data('nombre'));
            $('#hidden-programa').val($opt.data('programa'));
            $('#hidden-nivel').val($opt.data('nivel'));
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-fecha').val(fecha);

            $('#form-generar-certificado').submit();
        });
    });
    </script>
</body>
