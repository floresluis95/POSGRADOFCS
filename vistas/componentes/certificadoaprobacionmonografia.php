<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

require_once 'modelos/certificado.modelo.php';
?>

<link rel="stylesheet" href="vistas/recursos/assets/css/certificados.css">

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
                                    <h2>CERTIFICADO DE APROBACIÓN DE MONOGRAFÍA</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>SOBRE FORMATO PRE-IMPRESO DE POSGRADO CENTRAL</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container certificado-container">

                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> <i class="fas fa-id-card"></i> Buscar Estudiante por Carnet (CI)</h3>
                                <div class="certificado-cuerpo">
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

                                <div id="bloque-datos" style="display:none;">
                                    <div class="certificado-subtitulo">Datos del estudiante</div>
                                    <div class="row">
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
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Curso</strong></label>
                                                <input type="text" class="form-control" id="input-curso" placeholder="Curso / versión / gestión">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>N° de Recibo de Caja (Vaucher)</strong></label>
                                                <input type="text" class="form-control" id="input-vaucher" placeholder="Número de recibo de caja">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-monografia" style="display:none;">
                                <h3><span class="paso-numero">2</span> <i class="fas fa-file-alt"></i> Datos del Trabajo Monográfico</h3>
                                <div class="certificado-cuerpo">

                                <div class="form-group">
                                    <label><strong>Título del Trabajo Monográfico</strong></label>
                                    <textarea class="form-control" id="input-titulo" rows="3" placeholder="Escriba el título completo del trabajo monográfico"></textarea>
                                </div>

                                <div class="form-group">
                                    <label><strong>Miembros del Tribunal</strong></label>
                                    <div id="contenedor-tribunal"></div>
                                    <button type="button" class="btn-agregar-miembro" id="btn-agregar-miembro">
                                        <i class="fas fa-plus"></i> Agregar miembro del tribunal
                                    </button>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Entrega de Informe Final</strong></label>
                                            <input type="date" class="form-control" id="input-entrega">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Calificación Numeral</strong></label>
                                            <input type="number" class="form-control" id="input-calificacion" min="0" max="100" placeholder="Ej: 92">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Calificación Literal</strong></label>
                                            <div class="dato-solo-lectura" id="dato-literal">-</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="certificado-acciones">
                                    <button type="button" class="btn-generar-certificado" id="btn-generar-certificado">
                                        <i class="fas fa-file-pdf"></i> Generar Certificado PDF
                                    </button>
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

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-aprobacion-monografia-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="programa" id="hidden-programa">
        <input type="hidden" name="curso" id="hidden-curso">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="titulo" id="hidden-titulo">
        <input type="hidden" name="tribunal" id="hidden-tribunal">
        <input type="hidden" name="entrega" id="hidden-entrega">
        <input type="hidden" name="calificacion" id="hidden-calificacion">
    </form>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script>
    $(document).ready(function() {

        // ---- Conversión de número a letras (español, 0-100) para la vista previa ----
        function numeroALetrasJS(numero) {
            var entero = parseInt(numero, 10);
            if (isNaN(entero)) return '';

            var unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
            var decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
            var especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
            var veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

            var literal;
            if (entero === 0) {
                literal = 'CERO';
            } else if (entero === 100) {
                literal = 'CIEN';
            } else if (entero > 100) {
                literal = 'CIENTO ';
                var resto = entero - 100;
                if (resto >= 10 && resto < 20) {
                    literal += especiales[resto - 10];
                } else if (resto >= 20 && resto < 30) {
                    literal += veintenas[resto - 20];
                } else if (resto >= 30) {
                    var dec = Math.floor(resto / 10);
                    var uni = resto % 10;
                    literal += decenas[dec];
                    if (uni > 0) literal += ' Y ' + unidades[uni];
                } else {
                    literal += unidades[resto];
                }
            } else if (entero >= 10 && entero < 20) {
                literal = especiales[entero - 10];
            } else if (entero >= 20 && entero < 30) {
                literal = veintenas[entero - 20];
            } else {
                var d = Math.floor(entero / 10);
                var u = entero % 10;
                literal = decenas[d];
                if (u > 0) {
                    if (d > 0) literal += ' Y ';
                    literal += unidades[u];
                }
            }
            return literal.trim();
        }

        $('#input-calificacion').on('input', function() {
            var val = $(this).val();
            $('#dato-literal').text(val === '' ? '-' : numeroALetrasJS(val));
        });

        // ---- Miembros del tribunal (dinámico) ----
        function agregarFilaTribunal(rol, nombre) {
            var $fila = $(
                '<div class="fila-tribunal">' +
                    '<input type="text" class="form-control input-rol-tribunal" placeholder="Rol (Ej: Presidente, Tribunal)" style="max-width:220px;">' +
                    '<input type="text" class="form-control input-nombre-tribunal" placeholder="Nombre completo">' +
                    '<button type="button" class="btn-quitar-miembro" title="Quitar"><i class="fas fa-times"></i></button>' +
                '</div>'
            );
            $fila.find('.input-rol-tribunal').val(rol || '');
            $fila.find('.input-nombre-tribunal').val(nombre || '');
            $fila.find('.btn-quitar-miembro').on('click', function() {
                if ($('#contenedor-tribunal .fila-tribunal').length > 1) {
                    $fila.remove();
                }
            });
            $('#contenedor-tribunal').append($fila);
        }

        agregarFilaTribunal('PRESIDENTE', '');
        agregarFilaTribunal('TRIBUNAL', '');

        $('#btn-agregar-miembro').on('click', function() {
            agregarFilaTribunal('', '');
        });

        // ---- Búsqueda por CI ----
        function limpiarResultadoBusqueda() {
            $('#select-inscripcion').empty().append('<option value="">-- Seleccione --</option>');
            $('#col-resultado-busqueda').hide();
            $('#bloque-datos').hide();
            $('#card-monografia').hide();
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

            if (!estudianteID) {
                $('#bloque-datos').hide();
                $('#card-monografia').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-programa').text($opt.data('programa'));
            $('#bloque-datos').show();
            $('#card-monografia').show();
        });

        // ---- Generar PDF ----
        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var nombre = $opt.data('nombre');
            var programa = $opt.data('programa');
            var curso = $('#input-curso').val().trim();
            var vaucher = $('#input-vaucher').val().trim();
            var titulo = $('#input-titulo').val().trim();
            var entrega = $('#input-entrega').val();
            var calificacion = $('#input-calificacion').val();

            if (!nombre) {
                Swal.fire({ icon: 'warning', title: 'Falta el estudiante', text: 'Busque y seleccione un estudiante' });
                return;
            }
            if (!titulo) {
                Swal.fire({ icon: 'warning', title: 'Falta el título', text: 'Ingrese el título del trabajo monográfico' });
                return;
            }
            if (!vaucher) {
                Swal.fire({ icon: 'warning', title: 'Falta el N° de recibo', text: 'Ingrese el número de recibo de caja' });
                return;
            }
            if (!calificacion) {
                Swal.fire({ icon: 'warning', title: 'Falta la calificación', text: 'Ingrese la calificación numeral' });
                return;
            }

            var tribunal = [];
            $('#contenedor-tribunal .fila-tribunal').each(function() {
                var rol = $(this).find('.input-rol-tribunal').val().trim();
                var nombreMiembro = $(this).find('.input-nombre-tribunal').val().trim();
                if (nombreMiembro) {
                    tribunal.push((rol ? rol + ': ' : '') + nombreMiembro);
                }
            });

            if (tribunal.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Falta el tribunal', text: 'Registre al menos un miembro del tribunal' });
                return;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-nombre').val(nombre);
            $('#hidden-programa').val(programa);
            $('#hidden-curso').val(curso);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-titulo').val(titulo);
            $('#hidden-tribunal').val(tribunal.join('|'));
            $('#hidden-entrega').val(entrega);
            $('#hidden-calificacion').val(calificacion);

            $('#form-generar-certificado').submit();
        });
    });
    </script>
</body>
