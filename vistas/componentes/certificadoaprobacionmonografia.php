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
                                    <h2>CERTIFICADO DE APROBACIÓN PROGRAMA DE POSGRADO</h2>
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
                                                <label><strong>Grado académico</strong></label>
                                                <div class="dato-solo-lectura" id="dato-grado"></div>
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
                                <h3><span class="paso-numero">2</span> <i class="fas fa-file-alt"></i> Datos del Trabajo de Grado</h3>
                                <div class="certificado-cuerpo">

                                <div class="form-group">
                                    <label><strong>Título del Trabajo de Grado</strong> <small class="text-muted">(se escribe en MAYÚSCULAS)</small></label>
                                    <textarea class="form-control" id="input-titulo" rows="3" spellcheck="true" lang="es"
                                              placeholder="ESCRIBA EL TÍTULO COMPLETO DEL TRABAJO DE GRADO"></textarea>

                                    <!-- Revisión ortográfica del título (LanguageTool): marca las palabras
                                         dudosas y ofrece las correcciones con un clic. -->
                                    <div id="revision-titulo" class="revision-titulo" style="display:none;">
                                        <div class="revision-estado" id="revision-estado"></div>
                                        <div class="revision-vista" id="revision-vista"></div>
                                        <ul class="revision-lista" id="revision-lista"></ul>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap mt-2" style="gap:10px;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-guardar-titulo">
                                            <i class="fas fa-save"></i> Guardar título
                                        </button>
                                        <small class="text-muted" id="estado-titulo-guardado">
                                            El título guardado se usa en Medio Magnético, Registro de Documentos y el Cronograma de defensas.
                                        </small>
                                    </div>
                                </div>

                                <style>
                                .revision-titulo { margin-top: 8px; padding: 10px 12px; border: 1px solid #e3e7ef; border-radius: 8px; background: #fafbfd; font-size: 13px; }
                                .revision-estado { font-weight: 600; margin-bottom: 6px; }
                                .revision-estado.ok { color: #2f855a; }
                                .revision-estado.error { color: #b45309; }
                                .revision-estado.info { color: #6b7280; font-weight: 500; }
                                .revision-vista { font-weight: 600; letter-spacing: .2px; line-height: 1.7; color: #1f2937; }
                                .revision-vista mark { background: #fde68a; color: #92400e; padding: 0 2px; border-radius: 3px; border-bottom: 2px solid #d97706; }
                                .revision-lista { margin: 6px 0 0; padding-left: 18px; }
                                .revision-lista li { margin: 3px 0; }
                                .revision-sugerencia { display: inline-block; margin: 0 4px 2px 0; padding: 1px 8px; border: 1px solid #1e3c72; border-radius: 12px; color: #1e3c72; background: #fff; cursor: pointer; font-size: 12px; font-weight: 600; }
                                .revision-sugerencia:hover { background: #1e3c72; color: #fff; }
                                </style>

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
                                            <div class="d-flex" style="gap:6px;">
                                                <input type="date" class="form-control" id="input-entrega" title="Fecha de entrega">
                                                <input type="time" class="form-control" id="input-hora-entrega" title="Hora de entrega" style="max-width:130px;">
                                            </div>
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
        <input type="hidden" name="horaEntrega" id="hidden-hora-entrega">
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

        // Grado académico del programa tal como se imprime (la BD guarda MAESTRIA sin tilde)
        function textoGrado(grado) {
            grado = String(grado || '').toLocaleUpperCase('es').trim();
            return grado === 'MAESTRIA' ? 'MAESTRÍA' : grado;
        }

        // ---- Título: siempre en MAYÚSCULAS y con revisión ortográfica ----
        var erroresTitulo = [];      // [{palabra, inicio, largo, mensaje, sugerencias[]}]
        var temporizadorRevision = null;
        var revisionEnCurso = null;

        function escaparHtml(texto) {
            return $('<div>').text(texto == null ? '' : String(texto)).html();
        }

        // Pasa a mayúsculas mientras se escribe, sin mover el cursor
        $('#input-titulo').on('input', function() {
            var inicio = this.selectionStart, fin = this.selectionEnd;
            var mayus = this.value.toLocaleUpperCase('es');
            if (mayus !== this.value) {
                this.value = mayus;
                this.setSelectionRange(inicio, fin);
            }
            $('#estado-titulo-guardado').text('Hay cambios sin guardar en el título.').addClass('text-warning');
            clearTimeout(temporizadorRevision);
            temporizadorRevision = setTimeout(revisarTitulo, 900);
        });

        // Revisión con LanguageTool (servicio gratuito en línea): detecta palabras mal
        // escritas y tildes faltantes (EVALUACION -> EVALUACIÓN). Solo avisa, no bloquea.
        function revisarTitulo() {
            var texto = $('#input-titulo').val();
            if (revisionEnCurso) revisionEnCurso.abort();
            erroresTitulo = [];

            if (!texto.trim()) {
                $('#revision-titulo').hide();
                return;
            }

            $('#revision-titulo').show();
            $('#revision-estado').attr('class', 'revision-estado info').html('<i class="fas fa-spinner fa-spin"></i> Revisando ortografía...');

            revisionEnCurso = $.ajax({
                url: 'https://api.languagetool.org/v2/check',
                method: 'POST',
                dataType: 'json',
                timeout: 15000,
                data: { text: texto, language: 'es' },
                success: function(resp) {
                    if ($('#input-titulo').val() !== texto) return; // se siguió escribiendo
                    (resp.matches || []).forEach(function(m) {
                        var palabra = texto.substr(m.offset, m.length);
                        var sugerencias = (m.replacements || []).map(function(r) {
                            return r.value.toLocaleUpperCase('es');
                        }).filter(function(s, i, lista) {
                            return s !== palabra && lista.indexOf(s) === i; // sin repetidas ni la misma palabra
                        }).slice(0, 4);
                        // Si la única "corrección" es la misma palabra (p. ej. mayúscula de un
                        // nombre propio), no es un error para un título en mayúsculas.
                        if (m.rule && m.rule.issueType === 'misspelling' && !sugerencias.length) return;
                        erroresTitulo.push({ palabra: palabra, inicio: m.offset, largo: m.length, mensaje: m.message, sugerencias: sugerencias });
                    });
                    mostrarRevision(texto);
                },
                error: function(xhr, estado) {
                    if (estado === 'abort') return;
                    $('#revision-estado').attr('class', 'revision-estado info')
                        .html('<i class="fas fa-wifi"></i> No se pudo revisar la ortografía (sin conexión a internet). Revise el título a mano.');
                    $('#revision-vista, #revision-lista').empty();
                }
            });
        }

        function mostrarRevision(texto) {
            if (!erroresTitulo.length) {
                $('#revision-estado').attr('class', 'revision-estado ok').html('<i class="fas fa-check-circle"></i> No se encontraron errores de ortografía.');
                $('#revision-vista, #revision-lista').empty();
                return;
            }

            $('#revision-estado').attr('class', 'revision-estado error').html(
                '<i class="fas fa-exclamation-triangle"></i> ' + erroresTitulo.length +
                (erroresTitulo.length === 1 ? ' posible error' : ' posibles errores') + ' de ortografía:'
            );

            // Título con las palabras dudosas resaltadas
            var html = '', pos = 0;
            erroresTitulo.forEach(function(e) {
                html += escaparHtml(texto.substring(pos, e.inicio)) + '<mark>' + escaparHtml(e.palabra) + '</mark>';
                pos = e.inicio + e.largo;
            });
            $('#revision-vista').html(html + escaparHtml(texto.substring(pos)));

            // Lista con sugerencias que se aplican con un clic
            var lista = '';
            erroresTitulo.forEach(function(e, i) {
                lista += '<li><strong>' + escaparHtml(e.palabra) + '</strong>' +
                    (e.sugerencias.length
                        ? ' &rarr; ' + e.sugerencias.map(function(s) {
                            return '<button type="button" class="revision-sugerencia" data-i="' + i + '" data-valor="' + escaparHtml(s) + '">' + escaparHtml(s) + '</button>';
                          }).join('')
                        : ' <small class="text-muted">' + escaparHtml(e.mensaje) + '</small>') +
                    '</li>';
            });
            $('#revision-lista').html(lista);
        }

        $('#revision-lista').on('click', '.revision-sugerencia', function() {
            var e = erroresTitulo[$(this).data('i')];
            var texto = $('#input-titulo').val();
            if (!e || texto.substr(e.inicio, e.largo) !== e.palabra) return;
            $('#input-titulo').val(texto.substring(0, e.inicio) + $(this).data('valor') + texto.substring(e.inicio + e.largo)).trigger('input');
            clearTimeout(temporizadorRevision);
            revisarTitulo();
        });

        // Carga lo ya registrado del trabajo de grado (título, tribunal, fechas)
        function cargarTrabajoGrado(estudianteID, programaID) {
            $.post('ajax/certificado.ajax.php', { accion: 'obtenerTrabajoGrado', estudianteID: estudianteID, programaID: programaID }, function(data) {
                var t = data && data.trabajo;
                // Primero se limpia lo del estudiante anterior
                $('#input-entrega').val('');
                $('#input-hora-entrega').val('');
                $('#input-calificacion').val('').trigger('input');
                $('#contenedor-tribunal').empty();
                agregarFilaTribunal('PRESIDENTE', '');
                agregarFilaTribunal('TRIBUNAL', '');
                $('#input-titulo').val(t ? t.TituloTrabajo : '');
                $('#estado-titulo-guardado').removeClass('text-warning').text(t && t.TituloTrabajo
                    ? 'Título ya registrado. Si lo cambia, presione "Guardar título".'
                    : 'El título guardado se usa en Medio Magnético, Registro de Documentos y el Cronograma de defensas.');
                if (t) {
                    if (t.FechaEntrega) $('#input-entrega').val(t.FechaEntrega);
                    if (t.HoraEntrega) $('#input-hora-entrega').val(String(t.HoraEntrega).substring(0, 5));
                    if (t.CalificacionNumeral !== null && t.CalificacionNumeral !== '') {
                        $('#input-calificacion').val(parseInt(t.CalificacionNumeral, 10)).trigger('input');
                    }
                    var miembros = (t.Tribunal || '').split('|').filter(function(m) { return m.trim(); });
                    if (miembros.length) {
                        $('#contenedor-tribunal').empty();
                        miembros.forEach(function(m) {
                            var partes = m.split(':');
                            if (partes.length > 1) agregarFilaTribunal(partes.shift().trim(), partes.join(':').trim());
                            else agregarFilaTribunal('', m.trim());
                        });
                    }
                }
                revisarTitulo();
            }, 'json');
        }

        $('#btn-guardar-titulo').on('click', function() {
            var titulo = $('#input-titulo').val().trim();
            var estudianteID = $('#select-inscripcion').val();
            var programaID = $('#select-inscripcion').find('option:selected').data('programa-id');
            if (!estudianteID || !titulo) {
                Swal.fire({ type: 'warning', title: 'Falta el título', text: 'Seleccione el estudiante y escriba el título' });
                return;
            }
            var $btn = $(this).prop('disabled', true);
            $.post('ajax/certificado.ajax.php', { accion: 'guardarTitulo', estudianteID: estudianteID, programaID: programaID, titulo: titulo }, function(resp) {
                if (resp && resp.success) {
                    $('#input-titulo').val(resp.titulo);
                    $('#estado-titulo-guardado').removeClass('text-warning').text('Título guardado. Ya se refleja en los demás documentos y en el cronograma de defensas.');
                    Swal.fire({ type: 'success', title: 'Título guardado', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ type: 'warning', title: 'Atención', text: (resp && resp.mensaje) || 'No se pudo guardar el título' });
                }
            }, 'json').fail(function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo guardar el título' });
            }).always(function() {
                $btn.prop('disabled', false);
            });
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
                            .attr('data-grado', insc.GradoAcademico || '')
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

            if (!estudianteID) {
                $('#bloque-datos').hide();
                $('#card-monografia').hide();
                return;
            }

            $('#dato-nombre').text($opt.data('nombre'));
            $('#dato-programa').text($opt.data('programa'));
            $('#dato-grado').text(textoGrado($opt.data('grado')));
            $('#bloque-datos').show();
            $('#card-monografia').show();
            cargarTrabajoGrado(estudianteID, $opt.data('programa-id'));
        });

        // ---- Generar PDF ----
        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var nombre = $opt.data('nombre');
            var programa = $opt.data('programa');
            // En el certificado, el espacio de "curso" lleva el grado académico del programa
            var curso = textoGrado($opt.data('grado'));
            var vaucher = $('#input-vaucher').val().trim();
            var titulo = $('#input-titulo').val().trim().toLocaleUpperCase('es');
            var entrega = $('#input-entrega').val();
            var calificacion = $('#input-calificacion').val();

            if (!nombre) {
                Swal.fire({ type: 'warning', title: 'Falta el estudiante', text: 'Busque y seleccione un estudiante' });
                return;
            }
            if (!titulo) {
                Swal.fire({ type: 'warning', title: 'Falta el título', text: 'Ingrese el título del trabajo de grado' });
                return;
            }
            titulo = titulo.replace(/\s+/g, ' '); // sin espacios dobles
            if (!vaucher) {
                Swal.fire({ type: 'warning', title: 'Falta el N° de recibo', text: 'Ingrese el número de recibo de caja' });
                return;
            }
            if (!calificacion) {
                Swal.fire({ type: 'warning', title: 'Falta la calificación', text: 'Ingrese la calificación numeral' });
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
                Swal.fire({ type: 'warning', title: 'Falta el tribunal', text: 'Registre al menos un miembro del tribunal' });
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
            $('#hidden-hora-entrega').val(entrega ? $('#input-hora-entrega').val() : '');
            $('#hidden-calificacion').val(calificacion);

            // Si la revisión marcó posibles errores, se pregunta antes de imprimir
            if (erroresTitulo.length) {
                Swal.fire({
                    type: 'warning',
                    title: 'El título tiene ' + erroresTitulo.length + (erroresTitulo.length === 1 ? ' posible error' : ' posibles errores'),
                    text: 'Palabras marcadas: ' + erroresTitulo.map(function(e) { return e.palabra; }).join(', ') + '. ¿Generar el certificado de todas formas?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, generar',
                    cancelButtonText: 'Revisar título'
                }).then(function(r) {
                    if (r.value) $('#form-generar-certificado').submit();
                });
                return;
            }
            $('#form-generar-certificado').submit();
        });
    });
    </script>
</body>
