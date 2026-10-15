/**
 * Pestaña "Gráficos" del Reporte General (reportemodulos.php).
 *
 * Gráficos en HTML/CSS (sin librería) para la toma de decisiones:
 *  - Órdenes por cobrar: vencidas, por vencer, al día y matrículas por validar.
 *  - Cobrado por mes: matrícula, cuotas y clínicas apiladas.
 *  - Cartera por programa: cobrado frente a lo que falta cobrar.
 *  - Inscripciones por mes.
 *  - Planes de pago elegidos y descuentos otorgados.
 *
 * Colores: posiciones 1-3 de la paleta categórica validada (azul, naranja, aqua) para
 * las series; el resto en tinta y grises. Los textos nunca van en el color de la serie.
 * Datos: ajax/reportemodulos.ajax.php (obtenerDatosGraficos y obtenerReportePagosPrograma).
 */
(function($) {
    'use strict';

    const SERIES = [
        { clave: 'matricula', nombre: 'Matrícula', clase: 'is-s1' },
        { clave: 'cuotas', nombre: 'Cuotas', clase: 'is-s2' },
        { clave: 'clinicas', nombre: 'Clínicas', clase: 'is-s3' }
    ];
    const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    const MAX_PROGRAMAS = 8;

    let cargado = false;

    // ---------- Formatos ----------
    function bs(valor) {
        return 'Bs. ' + (parseFloat(valor) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    // Ejes: 0 / 500 / 1,5K / 12K / 1,2M
    function compacto(valor) {
        const v = Math.abs(valor);
        if (v >= 1e6) return (valor / 1e6).toLocaleString('es-BO', { maximumFractionDigits: 1 }) + 'M';
        if (v >= 1e3) return (valor / 1e3).toLocaleString('es-BO', { maximumFractionDigits: 1 }) + 'K';
        return String(Math.round(valor));
    }
    // También escapa comillas: el HTML del tooltip viaja dentro de un atributo
    function esc(t) {
        return $('<div>').text(t == null ? '' : String(t)).html().replace(/"/g, '&quot;');
    }
    // "2026-10" → "oct 26" (corto, para el eje) y "octubre 2026" (tooltip)
    function mesCorto(mes) {
        const p = mes.split('-');
        return MESES[parseInt(p[1], 10) - 1] + ' ' + p[0].slice(2);
    }
    function mesLargo(mes) {
        const p = mes.split('-');
        const largo = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, 1).toLocaleDateString('es-BO', { month: 'long' });
        return largo.charAt(0).toUpperCase() + largo.slice(1) + ' ' + p[0];
    }

    // Marcas del eje Y redondeadas (pasos de 1, 2 o 5 × 10^n), unas 4 líneas
    // Con enteros (conteos) el paso es al menos 1: con pocos inscritos el eje mostraba "0, 1, 1"
    function escalaY(maximo, enteros) {
        if (maximo <= 0) return { tope: 1, marcas: [0] };
        const crudo = maximo / 4;
        const base = Math.pow(10, Math.floor(Math.log10(crudo)));
        let paso = [1, 2, 5, 10].map(function(f) { return f * base; }).find(function(p) { return p >= crudo; });
        if (enteros) paso = Math.max(1, paso);
        const tope = Math.ceil(maximo / paso) * paso;
        const marcas = [];
        for (let v = 0; v <= tope + paso / 2; v += paso) marcas.push(v);
        return { tope: tope, marcas: marcas };
    }

    // ---------- Tooltip compartido ----------
    function tooltip() {
        let $t = $('#rgTooltip');
        // Clase "rp" para que tome los colores de las series (variables CSS del reporte)
        if (!$t.length) $t = $('<div id="rgTooltip" class="rp rg-tooltip" role="tooltip"></div>').appendTo('body');
        return $t;
    }
    $(document).on('mousemove', '[data-rg-tip]', function(e) {
        const $t = tooltip().html($(this).attr('data-rg-tip')).show();
        const ancho = $t.outerWidth();
        const izquierda = e.clientX + 14 + ancho > window.innerWidth ? e.clientX - ancho - 14 : e.clientX + 14;
        $t.css({ left: izquierda, top: e.clientY + 14 });
    });
    $(document).on('mouseleave', '[data-rg-tip]', function() {
        tooltip().hide();
    });

    function filaTip(clase, etiqueta, valor) {
        return '<div class="rg-tip-fila">' + (clase ? '<i class="rg-llave ' + clase + '"></i>' : '') +
            '<span>' + etiqueta + '</span><b>' + valor + '</b></div>';
    }

    // ---------- Columnas (apiladas o de una serie) ----------
    function graficoColumnas(meses, series, opciones) {
        const totales = meses.map(function(m) {
            return series.reduce(function(s, serie) { return s + (m[serie.clave] || 0); }, 0);
        });
        const escala = escalaY(Math.max.apply(null, totales), opciones.enteros);
        const etiquetar = opciones.etiquetar || function() { return false; };
        // Rótulos del eje X que caben: hasta 12 a todo el ancho, 6 en medio panel
        const saltoEje = Math.max(1, Math.ceil(meses.length / (opciones.rotulosEje || 12)));

        let html = '<div class="rg-columnas" style="--rg-n:' + meses.length + '">';
        html += '<div class="rg-eje-y">' + escala.marcas.slice().reverse().map(function(v) {
            return '<span style="bottom:' + (v / escala.tope * 100) + '%">' + compacto(v) + '</span>';
        }).join('') + '</div>';
        html += '<div class="rg-plano">' + escala.marcas.map(function(v) {
            return '<i class="rg-grilla" style="bottom:' + (v / escala.tope * 100) + '%"></i>';
        }).join('');

        meses.forEach(function(m, i) {
            const total = totales[i];
            const tip = '<div class="rg-tip-titulo">' + mesLargo(m.mes) + '</div>' +
                series.map(function(s) {
                    return filaTip(series.length > 1 ? s.clase : '', s.nombre, opciones.formato(m[s.clave] || 0));
                }).join('') +
                (series.length > 1 ? filaTip('', 'Total', opciones.formato(total)) : '');

            html += '<div class="rg-col" data-rg-tip="' + esc(tip) + '">';
            html += '<div class="rg-pila" style="height:' + (total / escala.tope * 100) + '%">';
            // De arriba hacia abajo: la última serie queda arriba
            series.slice().reverse().forEach(function(s) {
                const v = m[s.clave] || 0;
                if (v > 0) html += '<span class="rg-seg ' + s.clase + '" style="flex-grow:' + v + '"></span>';
            });
            html += '</div>';
            if (total > 0 && etiquetar(i, total)) {
                html += '<span class="rg-col-valor" style="bottom:calc(' + (total / escala.tope * 100) + '% + 4px)">' + opciones.etiqueta(total) + '</span>';
            }
            // Se rotula contando desde el último mes (el actual siempre lleva su nombre)
            html += '<span class="rg-col-eje">' + ((meses.length - 1 - i) % saltoEje === 0 ? mesCorto(m.mes) : '') + '</span>';
            html += '</div>';
        });
        html += '</div></div>';
        return html;
    }

    // ---------- Bloques ----------
    function renderOrdenes(o) {
        const tile = function(clase, icono, etiqueta, dato, nota) {
            return '<div class="rg-tile ' + clase + '">' +
                '<div class="rg-tile-cab"><i class="fa ' + icono + '"></i> ' + etiqueta + '</div>' +
                '<div class="rg-tile-valor">' + dato.cantidad + '</div>' +
                '<div class="rg-tile-sub">' + (dato.cantidad ? bs(dato.monto) : 'Ninguna') + (nota ? ' &middot; ' + nota : '') + '</div>' +
            '</div>';
        };
        $('#rgOrdenes').html(
            tile('is-critico', 'fa-exclamation-circle', 'Cuotas vencidas', o.vencidas, '') +
            tile('is-alerta', 'fa-clock', 'Vencen en 7 días', o.proximas, '') +
            tile('is-bien', 'fa-check-circle', 'Pendientes al día', o.aldia, '') +
            tile('', 'fa-user-clock', 'Matrículas por validar', o.matricula, 'pre-registros') +
            // Ya pagadas, falta registrar el voucher: no cuentan como cobrado
            tile('', 'fa-search-dollar', 'Voucher por validar', o.extraviadas || { cantidad: 0, monto: 0 }, 'falta el código')
        );
    }

    function renderCobradoMes(meses) {
        const totalSerie = {};
        SERIES.forEach(function(s) {
            totalSerie[s.clave] = meses.reduce(function(t, m) { return t + (m[s.clave] || 0); }, 0);
        });
        const total = SERIES.reduce(function(t, s) { return t + totalSerie[s.clave]; }, 0);

        $('#rgCobradoTotal').text(bs(total));
        // Leyenda con el total de cada serie en el periodo (la identidad no depende solo del color)
        $('#rgCobradoLeyenda').html(SERIES.map(function(s) {
            return '<span><i class="rg-llave ' + s.clase + '"></i>' + s.nombre + ' <b>' + bs(totalSerie[s.clave]) + '</b></span>';
        }).join(''));

        if (total <= 0) {
            $('#rgCobradoMes').html('<div class="rg-vacio">Sin cobros en el periodo</div>');
        } else {
            // Rótulo solo en el mes más alto y en el actual
            const totales = meses.map(function(m) { return m.matricula + m.cuotas + m.clinicas; });
            const maximo = Math.max.apply(null, totales);
            const iMax = totales.indexOf(maximo);
            $('#rgCobradoMes').html(graficoColumnas(meses, SERIES, {
                formato: bs,
                etiqueta: compacto,
                etiquetar: function(i) { return i === iMax || i === meses.length - 1; }
            }));
        }

        // Vista de tabla (los mismos datos, para leer cifras exactas o copiar)
        $('#rgCobradoTabla').html(
            '<table class="table rp-tabla mb-0"><thead><tr><th>Mes</th>' +
            SERIES.map(function(s) { return '<th class="text-right">' + s.nombre + '</th>'; }).join('') +
            '<th class="text-right">Total</th></tr></thead><tbody>' +
            meses.slice().reverse().map(function(m) {
                return '<tr><td>' + mesLargo(m.mes) + '</td>' +
                    SERIES.map(function(s) { return '<td class="text-right">' + bs(m[s.clave]) + '</td>'; }).join('') +
                    '<td class="text-right"><b>' + bs(m.matricula + m.cuotas + m.clinicas) + '</b></td></tr>';
            }).join('') +
            '</tbody></table>'
        );
    }

    function renderInscripciones(meses) {
        const total = meses.reduce(function(t, m) { return t + m.inscritos; }, 0);
        $('#rgInscritosTotal').text(total + (total === 1 ? ' inscripción' : ' inscripciones'));
        if (total <= 0) {
            $('#rgInscritosMes').html('<div class="rg-vacio">Sin inscripciones en el periodo</div>');
            return;
        }
        const valores = meses.map(function(m) { return m.inscritos; });
        const iMax = valores.indexOf(Math.max.apply(null, valores));
        $('#rgInscritosMes').html(graficoColumnas(meses, [{ clave: 'inscritos', nombre: 'Inscripciones', clase: 'is-s1' }], {
            formato: function(v) { return String(v); },
            etiqueta: function(v) { return String(v); },
            enteros: true,
            rotulosEje: 6,
            etiquetar: function(i) { return i === iMax || i === meses.length - 1; }
        }));
    }

    function renderCartera(programas) {
        const filas = programas
            .map(function(p) {
                return {
                    nombre: p.NombrePrograma,
                    codigo: p.Codigo,
                    programado: parseFloat(p.CostoTotalProgramado) || 0,
                    cobrado: parseFloat(p.TotalPagado) || 0,
                    saldo: parseFloat(p.SaldoPendiente) || 0,
                    inscritos: parseInt(p.TotalInscritos, 10) || 0
                };
            })
            .filter(function(f) { return f.programado > 0; })
            .sort(function(a, b) { return b.saldo - a.saldo; });

        if (!filas.length) {
            $('#rgCartera').html('<div class="rg-vacio">Sin programas con estudiantes inscritos</div>');
            $('#rgCarteraTotal').text('');
            return;
        }

        const totalSaldo = filas.reduce(function(t, f) { return t + f.saldo; }, 0);
        $('#rgCarteraTotal').text(bs(totalSaldo) + ' por cobrar');
        const maximo = Math.max.apply(null, filas.map(function(f) { return f.programado; }));

        let html = '<div class="rg-barras">';
        filas.slice(0, MAX_PROGRAMAS).forEach(function(f) {
            const pct = Math.round(f.cobrado / f.programado * 100);
            const tip = '<div class="rg-tip-titulo">' + esc(f.nombre) + '</div>' +
                filaTip('is-s1', 'Cobrado', bs(f.cobrado) + ' (' + pct + '%)') +
                filaTip('is-pendiente', 'Por cobrar', bs(f.saldo)) +
                filaTip('', 'Programado', bs(f.programado)) +
                filaTip('', 'Inscritos', f.inscritos);
            html += '<div class="rg-barra-fila" data-rg-tip="' + esc(tip) + '">' +
                '<div class="rg-barra-nombre" title="' + esc(f.nombre) + '">' + esc(f.nombre) + '</div>' +
                '<div class="rg-barra-pista">' +
                    '<div class="rg-barra-total" style="width:' + (f.programado / maximo * 100) + '%">' +
                        (f.cobrado > 0 ? '<span class="rg-seg is-s1" style="flex-grow:' + f.cobrado + '"></span>' : '') +
                        (f.saldo > 0 ? '<span class="rg-seg is-pendiente" style="flex-grow:' + f.saldo + '"></span>' : '') +
                    '</div>' +
                '</div>' +
                '<div class="rg-barra-valor">' + (f.saldo > 0 ? bs(f.saldo) : '<span class="rp-muted">Pagado</span>') + '</div>' +
            '</div>';
        });
        html += '</div>';
        if (filas.length > MAX_PROGRAMAS) {
            html += '<div class="rg-nota">Y ' + (filas.length - MAX_PROGRAMAS) + ' programa(s) más con menor saldo; ver la pestaña Pagos por programa.</div>';
        }
        $('#rgCartera').html(html);
    }

    function renderPlanes(programas) {
        const suma = function(campo) {
            return programas.reduce(function(t, p) { return t + (parseFloat(p[campo]) || 0); }, 0);
        };
        const planes = [
            { nombre: 'Regular', valor: suma('CountRegular') },
            { nombre: 'Contado', valor: suma('CountDescuento') },
            { nombre: 'Grupal', valor: suma('CountGrupal') },
            { nombre: 'Sin plan', valor: suma('CountSinPlan') }
        ];
        const total = planes.reduce(function(t, p) { return t + p.valor; }, 0);
        const conDescuento = suma('CountConDescuento');
        const montoDescuentos = suma('MontoTotalDescuentos');

        $('#rgPlanesNota').html(
            '<b>' + bs(montoDescuentos) + '</b> en descuentos otorgados a ' + conDescuento +
            (conDescuento === 1 ? ' estudiante' : ' estudiantes')
        );
        if (total <= 0) {
            $('#rgPlanes').html('<div class="rg-vacio">Sin estudiantes inscritos</div>');
            return;
        }
        const maximo = Math.max.apply(null, planes.map(function(p) { return p.valor; }));
        $('#rgPlanes').html('<div class="rg-barras">' + planes.map(function(p) {
            const pct = Math.round(p.valor / total * 100);
            return '<div class="rg-barra-fila is-corta" data-rg-tip="' + esc(filaTip('', p.nombre, p.valor + ' (' + pct + '%)')) + '">' +
                '<div class="rg-barra-nombre">' + p.nombre + '</div>' +
                '<div class="rg-barra-pista">' +
                    (p.valor > 0 ? '<div class="rg-barra-total" style="width:' + (p.valor / maximo * 100) + '%"><span class="rg-seg is-s1" style="flex-grow:1"></span></div>' : '') +
                '</div>' +
                '<div class="rg-barra-valor">' + p.valor + ' <span class="rp-muted">&middot; ' + pct + '%</span></div>' +
            '</div>';
        }).join('') + '</div>');
    }

    // ---------- Carga ----------
    function cargarGraficos() {
        const $ = window.jQuery;
        const filtros = {
            sede: $('#rgSede').val() || '',
            grado: $('#rgGrado').val() || '',
            programaID: $('#rgPrograma').val() || ''
        };
        const meses = $('#rgPeriodo').val() || '12';
        $('#rgCobradoMes, #rgInscritosMes, #rgCartera, #rgPlanes').html('<div class="rg-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');

        $.when(
            $.ajax({ url: 'ajax/reportemodulos.ajax.php', method: 'POST', dataType: 'json',
                     data: $.extend({ accion: 'obtenerDatosGraficos', meses: meses }, filtros) }),
            $.ajax({ url: 'ajax/reportemodulos.ajax.php', method: 'POST', dataType: 'json',
                     data: $.extend({ accion: 'obtenerReportePagosPrograma' }, filtros) })
        ).done(function(respGraficos, respPagos) {
            const datos = respGraficos[0] || {};
            const programas = Array.isArray(respPagos[0]) ? respPagos[0] : [];
            renderOrdenes(datos.ordenes || {});
            renderCobradoMes(datos.meses || []);
            renderInscripciones(datos.meses || []);
            renderCartera(programas);
            renderPlanes(programas);
        }).fail(function() {
            $('#rgCobradoMes, #rgInscritosMes, #rgCartera, #rgPlanes').html('<div class="rg-vacio">No se pudieron cargar los datos</div>');
        });
    }

    $(function() {
        // La página incluye jQuery más de una vez: los eventos que disparan Select2 y
        // Bootstrap van por la copia vigente al terminar de cargar, no por la de arriba.
        const $ = window.jQuery;
        if (!$('#rpTabGraficos').length) return;

        // Filtros: sedes y grados salen de los programas activos; la lista de programas
        // se acota con la sede y el grado elegidos
        let programasFiltro = [];
        const unicos = function(campo) {
            return programasFiltro.map(function(p) { return p[campo]; })
                .filter(function(v, i, lista) { return v && lista.indexOf(v) === i; })
                .sort();
        };
        function llenarProgramas() {
            const sede = $('#rgSede').val() || '';
            const grado = $('#rgGrado').val() || '';
            const elegido = $('#rgPrograma').val() || '';
            const $sel = $('#rgPrograma').empty().append($('<option>').val('').text('Todos los programas'));
            let sigue = false;
            programasFiltro.forEach(function(p) {
                if ((sede && p.Sede !== sede) || (grado && p.GradoAcademico !== grado)) return;
                const version = p.Version && String(p.Version).toUpperCase() !== 'V-1' ? ' (' + p.Version + ')' : '';
                $sel.append($('<option>').val(p.ProgramaID).text(p.NombrePrograma + version));
                if (String(p.ProgramaID) === elegido) sigue = true;
            });
            // Si el programa elegido ya no está en la lista, vuelve a "Todos"
            $sel.val(sigue ? elegido : '').trigger('change.select2');
        }
        $.ajax({
            url: 'ajax/reportemodulos.ajax.php', method: 'POST', dataType: 'json',
            data: { accion: 'obtenerOpcionesFiltros' },
            success: function(programas) {
                programasFiltro = Array.isArray(programas) ? programas : [];
                unicos('Sede').forEach(function(s) { $('#rgSede').append($('<option>').val(s).text(s)); });
                unicos('GradoAcademico').forEach(function(g) { $('#rgGrado').append($('<option>').val(g).text(g)); });
                llenarProgramas();
            }
        });

        // Se carga al abrir la pestaña por primera vez (no retrasa la carga de la página).
        // También con "click": la vista vuelve a incluir jQuery y el evento de Bootstrap
        // puede dispararse desde la otra copia.
        $('#rpTabGraficosLink').on('click shown.bs.tab', function() {
            if (!cargado) {
                cargado = true;
                cargarGraficos();
            }
        });
        $('#rgSede, #rgGrado').on('change', function() {
            llenarProgramas();
            cargarGraficos();
        });
        $('#rgPrograma, #rgPeriodo').on('change', cargarGraficos);
        $('#rgVerTabla').on('click', function() {
            const $tabla = $('#rgCobradoTabla').toggle();
            $(this).text($tabla.is(':visible') ? 'Ocultar datos' : 'Ver datos');
        });

        // Enlace directo a la pestaña: reportemodulos#graficos
        if (window.location.hash === '#graficos') {
            document.getElementById('rpTabGraficosLink').click();
        }
    });
})(jQuery);
