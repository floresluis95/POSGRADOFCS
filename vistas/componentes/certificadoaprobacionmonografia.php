<?php
$certTitulo = 'CERTIFICADO DE APROBACIÓN PROGRAMA DE POSGRADO';
$certSubtitulo = 'FORMATO PRE-IMPRESO DE POSGRADO CENTRAL';
include __DIR__ . '/modulos/certificado-encabezado.php';
?>

                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> Estudiante</h3>
                                <div class="certificado-cuerpo">
                                    <div id="buscador-certificado"></div>
                                    <select id="select-inscripcion" class="d-none" aria-hidden="true"><option value="">-- Seleccione --</option></select>
                                </div>
                            </div>

                            <div class="card-certificado" id="cert-vacio">
                                <div class="cert-vacio">
                                    <i class="fas fa-user-graduate"></i>
                                    <p>Busque al estudiante por su C.I. o nombre.</p>
                                </div>
                            </div>

                            <!-- Paso 2: el trabajo de grado que se certifica -->
                            <div class="card-certificado" id="card-monografia" style="display:none;">
                                <h3><span class="paso-numero">2</span> Trabajo de grado</h3>
                                <div class="certificado-cuerpo">

                                <div class="form-group">
                                    <label for="input-titulo">Título del trabajo de grado</label>
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
                                            Se usa también en Medio Magnético y en el Cronograma de defensas.
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
                                    <label>Miembros del tribunal</label>
                                    <small class="form-text text-muted mb-2">Seleccione al docente en la lista; si no está, escriba el nombre a mano: al generar el certificado queda registrado y aparece en la lista la próxima vez.</small>
                                    <div id="contenedor-tribunal"></div>
                                    <button type="button" class="btn-agregar-miembro" id="btn-agregar-miembro">
                                        <i class="fas fa-plus"></i> Agregar miembro del tribunal
                                    </button>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="input-entrega">Entrega del informe final</label>
                                            <div class="d-flex" style="gap:6px;">
                                                <input type="date" class="form-control" id="input-entrega" title="Fecha de entrega">
                                                <input type="time" class="form-control" id="input-hora-entrega" title="Hora de entrega" style="max-width:130px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="input-calificacion">Calificación numeral</label>
                                            <input type="number" class="form-control" id="input-calificacion" min="0" max="100" placeholder="Ej: 92">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Calificación literal</label>
                                            <div class="dato-solo-lectura" id="dato-literal">-</div>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>

                            <!-- Paso 3: pago; aquí se genera -->
                            <div class="card-certificado" id="bloque-datos" style="display:none;">
                                <h3><span class="paso-numero">3</span> Pago</h3>
                                <div class="certificado-cuerpo">
                                    <div class="cert-fijos">
                                        <div><span>Programa</span><div class="dato-solo-lectura" id="dato-programa"></div></div>
                                        <div><span>Grado académico</span><div class="dato-solo-lectura" id="dato-grado"></div></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 form-group mb-md-0">
                                            <label for="input-vaucher">N° de recibo de caja o voucher<span class="cert-requerido">*</span></label>
                                            <input type="text" class="form-control" id="input-vaucher" data-voucher-partes="alfanumerico">
                                        </div>
                                        <div class="col-md-3 form-group mb-0">
                                            <label for="input-monto">Monto pagado (Bs.)<span class="cert-requerido">*</span></label>
                                            <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="certificado-acciones cert-acciones">
                                        <button type="button" class="cert-demo" id="btn-documento-completo"
                                            title="Imprime todo el certificado (recuadros, membrete, firmas y escudo) en hoja oficio en blanco. No se registra.">
                                            <i class="fas fa-file-alt"></i> Ver formato completo (demostración)
                                        </button>
                                        <button type="button" class="btn-generar-certificado" id="btn-generar-certificado">
                                            <i class="fas fa-eye"></i> Revisar y generar
                                        </button>
                                    </div>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-aprobacion-monografia-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="programa" id="hidden-programa">
        <input type="hidden" name="curso" id="hidden-curso">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
        <input type="hidden" name="titulo" id="hidden-titulo">
        <input type="hidden" name="tribunal" id="hidden-tribunal">
        <input type="hidden" name="entrega" id="hidden-entrega">
        <input type="hidden" name="horaEntrega" id="hidden-hora-entrega">
        <input type="hidden" name="calificacion" id="hidden-calificacion">
    </form>

    <script>
    $(document).ready(function() {

        // ---- Conversión de número a letras (español, 0-100) para la vista previa ----
        function numeroALetrasJS(numero) {
            var entero = parseInt(numero, 10);
            if (isNaN(entero)) return '';

            var unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
            var decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
            var especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
            var veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

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
                    '<div class="caja-docente-tribunal"><select class="form-control select-docente-tribunal"></select></div>' +
                    '<input type="text" class="form-control input-nombre-tribunal" placeholder="Nombre completo">' +
                    '<button type="button" class="btn-quitar-miembro" title="Quitar"><i class="fas fa-times"></i></button>' +
                '</div>'
            );
            $fila.find('.input-rol-tribunal').val(rol || '');
            $fila.find('.input-nombre-tribunal').val(nombre || '');
            llenarSelectTribunal($fila.find('.select-docente-tribunal'), nombre);
            // Elegir a alguien de la lista pone su nombre; escribir a mano vuelve a "Otro"
            $fila.find('.select-docente-tribunal').on('change', function() {
                if (this.value) $fila.find('.input-nombre-tribunal').val(this.value);
                else $fila.find('.input-nombre-tribunal').val('').trigger('focus');
                bloquearElegidos();
            });
            $fila.find('.input-nombre-tribunal').on('input', function() {
                marcarEnSelect($fila.find('.select-docente-tribunal'), this.value);
                bloquearElegidos();
            });
            $fila.find('.btn-quitar-miembro').on('click', function() {
                if ($('#contenedor-tribunal .fila-tribunal').length > 1) {
                    $fila.remove();
                    bloquearElegidos();
                }
            });
            $('#contenedor-tribunal').append($fila);
            // Select con buscador: se escribe parte del nombre y se filtra la lista
            $fila.find('.select-docente-tribunal').select2({
                width: '100%',
                language: { noResults: function() { return 'No está en la lista: elija "Otro" y escríbalo a mano'; } }
            });
            bloquearElegidos();
        }

        // Una persona no puede estar dos veces en el tribunal (presidente y vocal, etc.).
        // Se compara sin tildes, mayúsculas ni el grado del inicio ("M.Sc. Dr.", "Esp. Lic.").
        function claveMiembro(nombre) {
            return String(nombre || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
                .replace(/^(\s*\S+\.)+\s*/, '').replace(/\s+/g, ' ').trim();
        }
        // Quien ya está en una fila aparece deshabilitado en el select de las demás
        function bloquearElegidos() {
            var $filas = $('#contenedor-tribunal .fila-tribunal');
            $filas.each(function() {
                var $propia = $(this);
                var otros = {};
                $filas.not($propia).each(function() {
                    var clave = claveMiembro($(this).find('.input-nombre-tribunal').val());
                    if (clave) otros[clave] = true;
                });
                $propia.find('.select-docente-tribunal option').each(function() {
                    this.disabled = !!this.value && !!otros[claveMiembro(this.value)];
                });
            });
        }

        // Lista para elegir el tribunal: docentes y personas de tribunales anteriores
        var personasTribunal = [];
        function llenarSelectTribunal($select, nombre) {
            $select.empty().append('<option value="">-- Otro (escribir a mano) --</option>');
            [['Docente', 'Docentes'], ['Tribunal anterior', 'Tribunales anteriores']].forEach(function(grupo) {
                var $grupo = $('<optgroup></optgroup>').attr('label', grupo[1]);
                personasTribunal.filter(function(p) { return p.origen === grupo[0]; }).forEach(function(p) {
                    $('<option></option>').val(p.nombre).text(p.nombre).appendTo($grupo);
                });
                if ($grupo.children().length) $select.append($grupo);
            });
            marcarEnSelect($select, nombre);
        }
        function marcarEnSelect($select, nombre) {
            var buscado = String(nombre || '').trim().toLowerCase();
            var $opcion = $select.find('option').filter(function() { return buscado && this.value.toLowerCase() === buscado; }).first();
            $select.val($opcion.length ? $opcion.val() : '').trigger('change.select2'); // solo refresca lo que se ve
        }
        $.post('ajax/certificado.ajax.php', { accion: 'sugerenciasTribunal' }, function(data) {
            personasTribunal = (data && data.personas) || [];
            $('#contenedor-tribunal .fila-tribunal').each(function() {
                llenarSelectTribunal($(this).find('.select-docente-tribunal'), $(this).find('.input-nombre-tribunal').val());
            });
            bloquearElegidos();
        }, 'json');

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
                    : 'Se usa también en Medio Magnético y en el Cronograma de defensas.');
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

        // ---- Estudiante: buscador por C.I. o nombre ----
        Certificado.buscador({
            contenedor: '#buscador-certificado',
            tipo: 'APROBACION_MONOGRAFIA',
            precios: window.PRECIOS_CERTIFICADOS,
            opcion: function(insc) {
                return $('<option></option>')
                    .val(insc.EstudianteID)
                    .attr('data-programa-id', insc.ProgramaID)
                    .attr('data-nombre', [insc.Apaterno, insc.Amaterno, insc.Nombre].filter(Boolean).join(' ').toLocaleUpperCase('es'))
                    .attr('data-programa', insc.NombrePrograma)
                    .attr('data-grado', insc.GradoAcademico || '');
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

            $('#dato-programa').text($opt.data('programa'));
            $('#dato-grado').text(textoGrado($opt.data('grado')));
            $('#bloque-datos').show();
            $('#card-monografia').show();
            cargarTrabajoGrado(estudianteID, $opt.data('programa-id'));
        });

        // ---- Generar PDF ----
        // Valida y copia los datos al formulario oculto; null si falta algo. En la
        // demostración (documento completo) no se piden el recibo ni el monto.
        function prepararFormulario(demostracion) {
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
                return null;
            }
            if (!titulo) {
                Swal.fire({ type: 'warning', title: 'Falta el título', text: 'Ingrese el título del trabajo de grado' });
                return null;
            }
            titulo = titulo.replace(/\s+/g, ' '); // sin espacios dobles
            if (!vaucher && !demostracion) {
                Swal.fire({ type: 'warning', title: 'Falta el N° de recibo', text: 'Ingrese el número de recibo de caja' });
                return null;
            }
            if (!calificacion) {
                Swal.fire({ type: 'warning', title: 'Falta la calificación', text: 'Ingrese la calificación numeral' });
                return null;
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
                return null;
            }
            var vistos = {}, repetido = null;
            $('#contenedor-tribunal .input-nombre-tribunal').each(function() {
                var clave = claveMiembro(this.value);
                if (clave && vistos[clave] && !repetido) repetido = this.value.trim();
                if (clave) vistos[clave] = true;
            });
            if (repetido) {
                Swal.fire({ type: 'warning', title: 'Miembro repetido en el tribunal', text: repetido + ' está dos veces. Cada miembro del tribunal debe ser una persona distinta.' });
                return null;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-nombre').val(nombre);
            $('#hidden-programa').val(programa);
            $('#hidden-curso').val(curso);
            $('#hidden-vaucher').val(vaucher);
            var monto = ($('#input-monto').val() || '').trim();
            if (!demostracion && (monto === '' || isNaN(monto) || parseFloat(monto) < 0)) {
                Swal.fire({ type: 'warning', title: 'Falta el monto', text: 'Ingrese el monto pagado por el certificado (Bs.)' });
                return null;
            }
            $('#hidden-monto').val(demostracion ? '' : monto);
            $('#hidden-titulo').val(titulo);
            $('#hidden-tribunal').val(tribunal.join('|'));
            $('#hidden-entrega').val(entrega);
            $('#hidden-hora-entrega').val(entrega ? $('#input-hora-entrega').val() : '');
            $('#hidden-calificacion').val(calificacion);

            return { nombre: nombre, programa: programa, curso: curso, vaucher: vaucher, monto: monto,
                titulo: titulo, tribunal: tribunal, entrega: entrega, calificacion: calificacion };
        }

        $('#btn-generar-certificado').on('click', function() {
            var d = prepararFormulario(false);
            if (!d) return;

            // Antes de imprimir: datos, revisión ortográfica y vista previa
            Certificado.confirmar({
                titulo: 'Certificado de Aprobación Programa de Posgrado',
                form: '#form-generar-certificado',
                datos: [
                    ['Estudiante', d.nombre],
                    ['Programa', String(d.programa).toLocaleUpperCase('es')],
                    ['Grado académico', d.curso],
                    ['N° de recibo o voucher', d.vaucher],
                    ['Monto pagado', Certificado.formatoBs(d.monto)],
                    ['Título', d.titulo],
                    ['Tribunal', d.tribunal.join(' · ')],
                    ['Entrega del informe final', d.entrega ? d.entrega.split('-').reverse().join('/') + ($('#input-hora-entrega').val() ? ' ' + $('#input-hora-entrega').val() : '') : ''],
                    ['Calificación', d.calificacion + ' (' + numeroALetrasJS(d.calificacion) + ')']
                ],
                ortografia: [{ texto: String(d.programa).toLocaleUpperCase('es') }, { texto: d.titulo }]
            });
        });

        // Demostración: todo el certificado (recuadros, membrete, firmas, escudo) en hoja
        // oficio, con los datos puestos. No se registra ni guarda el trabajo de grado.
        $('#btn-documento-completo').on('click', function() {
            if (!prepararFormulario(true)) return;
            var $form = $('#form-generar-certificado');
            var $completo = $('<input type="hidden" name="completo" value="1">').appendTo($form);
            $form.attr('target', '_blank')[0].submit();
            $completo.remove();
        });
    });
    </script>
</body>
