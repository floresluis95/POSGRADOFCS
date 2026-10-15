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
                                    <h2>CERTIFICADO DE CALIFICACIONES</h2>
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
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Nivel</strong></label>
                                                <div class="dato-solo-lectura" id="dato-nivel"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Año</strong></label>
                                                <div class="dato-solo-lectura" id="dato-anio"></div>
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
                                                <input type="text" class="form-control" id="input-vaucher" placeholder="Número de vaucher de pago" data-voucher-partes>
                                            </div>
                                        </div>
                                        <!-- Monto pagado: llega con el precio del certificado (Autoridades) y se puede cambiar -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="input-monto"><strong>Monto pagado (Bs.)</strong></label>
                                                <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00"
                                                       value="<?php $preciosCert = CertificadoModelo::PreciosCertificados(); echo $preciosCert['CALIFICACIONES'] !== null ? number_format($preciosCert['CALIFICACIONES'], 2, '.', '') : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Fecha del certificado</strong></label>
                                                <input type="date" class="form-control" id="input-fecha">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-modulos" style="display:none;">
                                <h3><span class="paso-numero">2</span> <i class="fas fa-list-ol"></i> Módulos y Calificaciones</h3>
                                <div class="certificado-cuerpo">
                                <div class="table-responsive">
                                    <table class="table tabla-certificado" id="tabla-modulos-certificado">
                                        <thead>
                                            <tr>
                                                <th>Sigla</th>
                                                <th>Módulo</th>
                                                <th class="text-center">Nota</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div id="alerta-sin-modulos" class="alert alert-warning" style="display:none;">
                                    <i class="fas fa-exclamation-triangle"></i> Este estudiante no tiene calificaciones registradas en el programa seleccionado.
                                </div>
                                <div class="certificado-acciones">
                                    <button type="button" class="btn-generar-certificado" id="btn-generar-certificado" disabled>
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

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-calificaciones-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="programa" id="hidden-programa">
        <input type="hidden" name="nivel" id="hidden-nivel">
        <input type="hidden" name="anio" id="hidden-anio">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
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
                        var nombreCompleto = (insc.Nombre + ' ' + insc.Apaterno + ' ' + insc.Amaterno).trim();
                        var ciCompleto = insc.Ci + (insc.Complemento ? '-' + insc.Complemento : '');
                        var partesCodigo = insc.Codigo.split('-');
                        var anioPrograma = partesCodigo[partesCodigo.length - 1].trim();
                        var etiqueta = nombreCompleto + ' (' + ciCompleto + ') - ' + insc.NombrePrograma + ' - ' + insc.GradoAcademico;

                        var $option = $('<option></option>')
                            .val(insc.EstudianteID)
                            .attr('data-programa-id', insc.ProgramaID)
                            .attr('data-nombre', nombreCompleto)
                            .attr('data-programa', insc.NombrePrograma)
                            .attr('data-nivel', insc.GradoAcademico)
                            .attr('data-anio', anioPrograma)
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

            if (!estudianteID || !programaID) {
                $('#bloque-datos').hide();
                $('#card-modulos').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-programa').text($opt.data('programa'));
            $('#dato-nivel').text($opt.data('nivel'));
            $('#dato-anio').text($opt.data('anio'));
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
                        $('#btn-generar-certificado').prop('disabled', true);
                        return;
                    }

                    $('#alerta-sin-modulos').hide();
                    modulosActuales = data.modulos;

                    data.modulos.forEach(function(mod) {
                        $tbody.append(
                            '<tr>' +
                            '<td>' + $('<div>').text(mod.codigomodulo || '').html() + '</td>' +
                            '<td>' + $('<div>').text(mod.nombremodulo || '').html() + '</td>' +
                            '<td class="text-center">' + parseInt(mod.Nota) + '</td>' +
                            '</tr>'
                        );
                    });

                    $('#btn-generar-certificado').prop('disabled', false);
                },
                error: function() {
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudieron obtener las calificaciones' });
                }
            });
        });

        $('#btn-generar-certificado').on('click', function() {
            var vaucher = $('#input-vaucher').val().trim();
            var fecha = $('#input-fecha').val();

            if (!vaucher) {
                Swal.fire({ type: 'warning', title: 'Falta el N° de Vaucher', text: 'Ingrese el número de vaucher antes de generar el certificado' });
                return;
            }
            if (!fecha) {
                Swal.fire({ type: 'warning', title: 'Falta la fecha', text: 'Seleccione la fecha del certificado' });
                return;
            }
            if (!modulosActuales.length) {
                Swal.fire({ type: 'warning', title: 'Sin calificaciones', text: 'El estudiante no tiene módulos calificados en este programa' });
                return;
            }

            var $opt = $('#select-inscripcion').find('option:selected');

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-nombre').val($opt.data('nombre'));
            $('#hidden-programa').val($opt.data('programa'));
            $('#hidden-nivel').val($opt.data('nivel'));
            $('#hidden-anio').val($opt.data('anio'));
            $('#hidden-vaucher').val(vaucher);
            var monto = ($('#input-monto').val() || '').trim();
            if (monto === '' || isNaN(monto) || parseFloat(monto) < 0) {
                Swal.fire({ type: 'warning', title: 'Falta el monto', text: 'Ingrese el monto pagado por el certificado (Bs.)' });
                return;
            }
            $('#hidden-monto').val(monto);
            $('#hidden-fecha').val(fecha);

            $('#form-generar-certificado').submit();
        });
    });
    </script>
</body>
