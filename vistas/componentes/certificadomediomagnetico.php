<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

require_once 'modelos/certificado.modelo.php';
?>

<link rel="stylesheet" href="vistas/recursos/assets/css/certificados.css?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/css/certificados.css'); ?>">

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
                                    <h2>CERTIFICADO DE MEDIO MAGNÉTICO</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>CARTA DE CERTIFICACIÓN (CON MEMBRETE PROPIO)</h4>
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
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Tratamiento</strong></label>
                                                <select class="form-control" id="input-tratamiento">
                                                    <option value="El Sr.">El Sr.</option>
                                                    <option value="La Sra.">La Sra.</option>
                                                    <option value="La Srta.">La Srta.</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <label><strong>Nombre</strong></label>
                                                <div class="dato-solo-lectura" id="dato-nombre"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Curso realizado (Grado + Programa)</strong></label>
                                                <div class="dato-solo-lectura" id="dato-curso"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-carta" style="display:none;">
                                <h3><span class="paso-numero">2</span> <i class="fas fa-file-alt"></i> Datos de la Certificación</h3>
                                <div class="certificado-cuerpo">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Tipo de trabajo entregado</strong></label>
                                            <select class="form-control" id="input-tipotrabajo">
                                                <option value="MEDIO FISICO Y DIGITAL DEL TRABAJO DE TESIS">Medio físico y digital del Trabajo de Tesis</option>
                                                <option value="MEDIO FISICO Y DIGITAL DEL TRABAJO MONOGRAFICO">Medio físico y digital del Trabajo Monográfico</option>
                                                <option value="MEDIO FISICO Y DIGITAL DEL TRABAJO DE GRADO">Medio físico y digital del Trabajo de Grado</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>N° de Recibo / Vaucher</strong></label>
                                            <input type="text" class="form-control" id="input-vaucher" placeholder="Número de recibo de caja">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><strong>Título del Trabajo</strong></label>
                                    <textarea class="form-control" id="input-titulo" rows="3" placeholder="Escriba el título completo del trabajo"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Fecha de la carta</strong></label>
                                            <input type="date" class="form-control" id="input-fecha">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Coordinador de Posgrado (nombre y grado)</strong></label>
                                            <input type="text" class="form-control" id="input-coordinador" value="M. Sc. Dr. Milton Mamani Morales">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Director de Posgrado (nombre y grado)</strong></label>
                                            <input type="text" class="form-control" id="input-director" value="M. Sc. Ing. Carlos Antezana Garcia">
                                        </div>
                                    </div>
                                </div>

                                <div class="certificado-acciones">
                                    <button type="button" class="btn-generar-certificado" id="btn-generar-certificado">
                                        <i class="fas fa-file-pdf"></i> Generar Carta PDF
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

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-medio-magnetico-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="tratamiento" id="hidden-tratamiento">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="curso" id="hidden-curso">
        <input type="hidden" name="tipotrabajo" id="hidden-tipotrabajo">
        <input type="hidden" name="titulo" id="hidden-titulo">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="fecha" id="hidden-fecha">
        <input type="hidden" name="coordinador" id="hidden-coordinador">
        <input type="hidden" name="director" id="hidden-director">
    </form>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script>
    $(document).ready(function() {
        var hoy = new Date();
        $('#input-fecha').val(hoy.toISOString().split('T')[0]);

        function limpiarResultadoBusqueda() {
            $('#select-inscripcion').empty().append('<option value="">-- Seleccione --</option>');
            $('#col-resultado-busqueda').hide();
            $('#bloque-datos').hide();
            $('#card-carta').hide();
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
                            .attr('data-curso', insc.GradoAcademico + ' EN ' + insc.NombrePrograma)
                            .text(etiqueta);

                        $select.append($option);
                    });

                    $('#col-resultado-busqueda').show();

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

            if (!estudianteID) {
                $('#bloque-datos').hide();
                $('#card-carta').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-curso').text($opt.data('curso'));
            $('#bloque-datos').show();
            $('#card-carta').show();

            // Reiniciar campos del trabajo antes de intentar autocompletar
            $('#input-titulo').val('');
            $('#alerta-titulo-automatico').remove();

            if (!programaID) {
                return;
            }

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerTrabajoGrado', estudianteID: estudianteID, programaID: programaID },
                dataType: 'json',
                success: function(data) {
                    if (data.success && data.trabajo) {
                        $('#input-titulo').val(data.trabajo.TituloTrabajo);
                        if (data.trabajo.TipoTrabajo) {
                            $('#input-tipotrabajo').val(data.trabajo.TipoTrabajo);
                        }
                        $('#input-titulo').before(
                            '<div class="alert alert-success py-2" id="alerta-titulo-automatico">' +
                            '<i class="fas fa-check-circle"></i> Título completado automáticamente desde el trabajo de grado registrado (Certificado de Aprobación de Monografía). Puede editarlo si es necesario.' +
                            '</div>'
                        );
                    }
                }
            });
        });

        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var nombre = $opt.data('nombre');
            var curso = $opt.data('curso');
            var titulo = $('#input-titulo').val().trim();
            var vaucher = $('#input-vaucher').val().trim();
            var fecha = $('#input-fecha').val();
            var coordinador = $('#input-coordinador').val().trim();
            var director = $('#input-director').val().trim();

            if (!nombre) {
                Swal.fire({ type: 'warning', title: 'Falta el estudiante', text: 'Busque y seleccione un estudiante' });
                return;
            }
            if (!titulo) {
                Swal.fire({ type: 'warning', title: 'Falta el título', text: 'Ingrese el título del trabajo' });
                return;
            }
            if (!vaucher) {
                Swal.fire({ type: 'warning', title: 'Falta el N° de recibo', text: 'Ingrese el número de recibo/vaucher' });
                return;
            }
            if (!fecha) {
                Swal.fire({ type: 'warning', title: 'Falta la fecha', text: 'Seleccione la fecha de la carta' });
                return;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-tratamiento').val($('#input-tratamiento').val());
            $('#hidden-nombre').val(nombre);
            $('#hidden-curso').val(curso);
            $('#hidden-tipotrabajo').val($('#input-tipotrabajo').val());
            $('#hidden-titulo').val(titulo);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-fecha').val(fecha);
            $('#hidden-coordinador').val(coordinador);
            $('#hidden-director').val(director);

            $('#form-generar-certificado').submit();
        });
    });
    </script>
</body>
