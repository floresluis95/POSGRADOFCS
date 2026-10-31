/**
 * Exportar tablas a Excel o PDF con dos botones pequeños ("Excel | PDF").
 *
 * 1) Cualquier <table data-exportar="Título del reporte"> recibe, arriba a la derecha,
 *    el grupo de botones. Se exporta lo que se ve en la tabla: si es un DataTable, todas
 *    las filas que cumplen la búsqueda (no solo la página actual); si no, las filas
 *    visibles. Las columnas de acciones (Acción, Acciones, Opciones, Foto) y las marcadas
 *    con <th data-no-exportar> no se exportan. Las columnas de montos (encabezado con
 *    Monto, Bs., Total, Costo, Saldo o Pagado) salen como números y con su total.
 *
 *    Atributos opcionales:
 *      data-exportar-archivo="matriculados"     nombre del archivo (sin extensión)
 *      data-exportar-botones="#contenedor"      dónde poner los botones (por defecto, arriba de la tabla)
 *
 *    Si la tabla se vuelve a dibujar por JavaScript, los botones siguen funcionando: los
 *    datos se leen recién al hacer clic.
 *
 * 2) window.exportarPdf(op): mismo objeto que window.exportarExcel (exportar-excel.js),
 *    para pantallas que arman su propia lista (p. ej. Órdenes de pago).
 *
 * El PDF se arma con pdfMake, que ya viene en datatables.bundle.js.
 */
(function ($) {
    'use strict';

    const COLUMNAS_SIN_EXPORTAR = /^(acci[oó]n(es)?|opciones|foto|ver|seleccionar)$/i;
    const COLUMNAS_MONTO = /(monto|bs\.?|total|costo|saldo|pagado|importe)/i;

    function dos(n) { return String(n).padStart(2, '0'); }

    function hoyTexto() {
        const h = new Date();
        return dos(h.getDate()) + '/' + dos(h.getMonth() + 1) + '/' + h.getFullYear();
    }

    function hoyArchivo() {
        const h = new Date();
        return h.getFullYear() + dos(h.getMonth() + 1) + dos(h.getDate());
    }

    function avisar(texto) {
        if (window.Swal && window.Swal.fire) {
            window.Swal.fire({ type: 'info', title: 'Sin datos', text: texto });
        } else if (window.swal) {
            window.swal('Sin datos', texto, 'info');
        } else {
            alert(texto);
        }
    }

    // "Bs. 1.234,56" / "1,234.56" / "1234.5" → 1234.56; null si no es un monto
    function leerMonto(texto) {
        let t = String(texto || '').replace(/bs\.?/ig, '').replace(/\s/g, '');
        if (!/^-?[\d.,]+$/.test(t) || !/\d/.test(t)) return null;
        const coma = t.lastIndexOf(',');
        const punto = t.lastIndexOf('.');
        if (coma >= 0 && punto >= 0) {
            // El último separador es el de los decimales
            t = coma > punto ? t.replace(/\./g, '').replace(',', '.') : t.replace(/,/g, '');
        } else if (coma >= 0) {
            t = /,\d{1,2}$/.test(t) ? t.replace(/\./g, '').replace(',', '.') : t.replace(/,/g, '');
        } else if (punto >= 0 && /^\d{1,3}(\.\d{3})+$/.test(t)) {
            t = t.replace(/\./g, ''); // 1.234 (miles, formato es-BO)
        }
        const n = parseFloat(t);
        return isNaN(n) ? null : Math.round(n * 100) / 100;
    }

    // Texto de una celda sin botones ni campos (lo que se lee en pantalla)
    function textoCelda(celda) {
        if (celda == null) return '';
        const div = document.createElement('div');
        if (typeof celda === 'string' || typeof celda === 'number') {
            div.innerHTML = String(celda);
        } else {
            div.appendChild(celda.cloneNode(true));
        }
        div.querySelectorAll('button, .btn, input, select, script, style, .no-exportar, .sr-only').forEach(function (n) { n.remove(); });
        // Separar bloques (líneas, badges) con un espacio
        div.querySelectorAll('br, div, p, li, small, span.badge').forEach(function (n) { n.insertAdjacentText('beforebegin', ' '); });
        return (div.textContent || '').replace(/\s+/g, ' ').trim();
    }

    // Columnas y filas de una tabla, tal como se ven
    function datosDeTabla(tabla) {
        const $tabla = $(tabla);
        const encabezados = $tabla.find('thead tr').last().children('th, td').toArray();
        const indices = [];
        const columnas = [];
        encabezados.forEach(function (th, i) {
            const titulo = textoCelda(th);
            if (!titulo || th.hasAttribute('data-no-exportar') || COLUMNAS_SIN_EXPORTAR.test(titulo)) return;
            indices.push(i);
            columnas.push({ titulo: titulo, tipo: COLUMNAS_MONTO.test(titulo) ? 'moneda' : 'texto' });
        });

        let filasDom = [];
        const esDataTable = $.fn.dataTable && $.fn.dataTable.isDataTable && $.fn.dataTable.isDataTable(tabla);
        if (esDataTable) {
            const api = $tabla.DataTable();
            const nodos = api.rows({ search: 'applied', order: 'applied' }).nodes().toArray();
            const datos = api.rows({ search: 'applied', order: 'applied' }).data().toArray();
            filasDom = nodos.map(function (nodo, i) {
                if (nodo) return Array.prototype.slice.call(nodo.cells);
                const d = datos[i];
                return Array.isArray(d) ? d : Object.keys(d || {}).map(function (k) { return d[k]; });
            });
        } else {
            $tabla.children('tbody').children('tr').each(function () {
                if (this.style.display === 'none' || $(this).is('.no-exportar')) return;
                const celdas = Array.prototype.slice.call(this.cells);
                // Fila de "sin datos" / "cargando" (una sola celda que ocupa todo)
                if (celdas.length === 1 && celdas[0].colSpan > 1) return;
                filasDom.push(celdas);
            });
        }

        const filas = filasDom.map(function (celdas) {
            return indices.map(function (i, j) {
                const texto = textoCelda(celdas[i]);
                if (columnas[j].tipo === 'moneda') {
                    const monto = leerMonto(texto);
                    return monto === null ? texto : monto;
                }
                return texto;
            });
        });

        // Una columna de "montos" que no trae números (p. ej. "Monto pagado: SI") pasa a texto
        columnas.forEach(function (c, j) {
            if (c.tipo === 'moneda' && filas.length && !filas.some(function (f) { return typeof f[j] === 'number'; })) {
                c.tipo = 'texto';
            }
        });
        columnas.forEach(function (c, j) {
            const largo = Math.max(c.titulo.length, ...filas.slice(0, 200).map(function (f) { return String(f[j] == null ? '' : f[j]).length; }));
            c.ancho = Math.min(50, Math.max(8, largo + 2));
        });

        let totales = null;
        if (columnas.some(function (c) { return c.tipo === 'moneda'; })) {
            totales = columnas.map(function (c, j) {
                if (c.tipo !== 'moneda') return '';
                return Math.round(filas.reduce(function (s, f) { return s + (typeof f[j] === 'number' ? f[j] : 0); }, 0) * 100) / 100;
            });
            const primeraTexto = columnas.findIndex(function (c) { return c.tipo !== 'moneda'; });
            if (primeraTexto >= 0) totales[primeraTexto] = 'Total';
        }

        let busqueda = '';
        if (esDataTable) busqueda = $tabla.DataTable().search() || '';

        return { columnas: columnas, filas: filas, totales: totales, busqueda: busqueda };
    }

    function opcionesDeTabla(tabla) {
        const titulo = tabla.getAttribute('data-exportar') || 'Reporte';
        const datos = datosDeTabla(tabla);
        const archivo = (tabla.getAttribute('data-exportar-archivo') ||
            titulo.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')) +
            '-' + hoyArchivo();
        return {
            archivo: archivo,
            hoja: titulo,
            titulo: titulo.toUpperCase() + ' · POSGRADO FCS',
            subtitulo: (datos.busqueda ? 'Búsqueda: ' + datos.busqueda + ' · ' : '') +
                datos.filas.length + ' registro(s) · Exportado el ' + hoyTexto(),
            columnas: datos.columnas,
            filas: datos.filas,
            totales: datos.totales
        };
    }

    // ------------------------------------------------------------------
    // PDF (pdfMake)
    // ------------------------------------------------------------------
    function formatoMoneda(n) {
        return Number(n).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    window.exportarPdf = function (op) {
        const pdfMake = window.pdfMake;
        if (!pdfMake || !pdfMake.createPdf) {
            alert('No se pudo cargar el generador de PDF. Recargue la página e intente de nuevo.');
            return;
        }
        const columnas = op.columnas;
        const horizontal = columnas.length > 6;
        // Carta: 612 x 792 pt; márgenes de 28 pt
        const anchoUtil = (horizontal ? 792 : 612) - 56;
        const pesos = columnas.map(function (c) { return Math.max(4, Math.min(40, c.ancho || 14)); });
        const sumaPesos = pesos.reduce(function (a, b) { return a + b; }, 0);
        const anchos = pesos.map(function (p) { return Math.floor(anchoUtil * p / sumaPesos) - 8; });

        const valor = function (v, c) {
            if (v === null || v === undefined || v === '') return '';
            if (c.tipo === 'moneda' && typeof v === 'number') return formatoMoneda(v);
            return String(v);
        };
        const alineacion = function (c) { return (c.tipo === 'moneda' || c.tipo === 'numero') ? 'right' : 'left'; };

        const cuerpo = [columnas.map(function (c) {
            return { text: c.titulo, style: 'encabezado', alignment: alineacion(c) };
        })];
        op.filas.forEach(function (fila) {
            cuerpo.push(fila.map(function (v, i) { return { text: valor(v, columnas[i]), alignment: alineacion(columnas[i]) }; }));
        });
        if (op.totales) {
            cuerpo.push(op.totales.map(function (v, i) {
                return { text: valor(v, columnas[i]), style: 'total', alignment: alineacion(columnas[i]) };
            }));
        }

        const doc = {
            pageSize: 'LETTER',
            pageOrientation: horizontal ? 'landscape' : 'portrait',
            pageMargins: [28, 34, 28, 34],
            info: { title: op.titulo || 'Reporte', author: 'Posgrado FCS - UTO' },
            content: [
                { text: op.titulo || '', style: 'titulo' },
                op.subtitulo ? { text: op.subtitulo, style: 'subtitulo' } : '',
                {
                    table: { headerRows: 1, widths: anchos, body: cuerpo, dontBreakRows: true },
                    layout: {
                        hLineWidth: function () { return 0.5; },
                        vLineWidth: function () { return 0.5; },
                        hLineColor: function () { return '#d1d5db'; },
                        vLineColor: function () { return '#d1d5db'; },
                        fillColor: function (fila) {
                            if (fila === 0) return '#eef2f9';
                            if (op.totales && fila === cuerpo.length - 1) return '#f3f4f6';
                            return fila % 2 === 0 ? '#fafbfc' : null;
                        },
                        paddingLeft: function () { return 4; },
                        paddingRight: function () { return 4; },
                        paddingTop: function () { return 3; },
                        paddingBottom: function () { return 3; }
                    }
                }
            ],
            footer: function (pagina, total) {
                return {
                    columns: [
                        { text: (window.INSTITUCION ? window.INSTITUCION.nombreSistema + ' · ' + window.INSTITUCION.universidad : 'Posgrado'), alignment: 'left' },
                        { text: 'Página ' + pagina + ' de ' + total, alignment: 'right' }
                    ],
                    margin: [28, 8, 28, 0], fontSize: 7, color: '#6b7280'
                };
            },
            defaultStyle: { fontSize: columnas.length > 9 ? 7 : 8, color: '#1f2937' },
            styles: {
                titulo: { fontSize: 12, bold: true, color: '#1e3c72', margin: [0, 0, 0, 2] },
                subtitulo: { fontSize: 8, italics: true, color: '#555555', margin: [0, 0, 0, 8] },
                encabezado: { bold: true, color: '#1e3c72' },
                total: { bold: true }
            }
        };
        pdfMake.createPdf(doc).download((op.archivo || 'reporte') + '.pdf');
    };

    // ------------------------------------------------------------------
    // Botones junto a las tablas con data-exportar
    // ------------------------------------------------------------------
    function estilos() {
        if (document.getElementById('exportar-tabla-css')) return;
        const css = document.createElement('style');
        css.id = 'exportar-tabla-css';
        css.textContent =
            '.exportar-barra { display: flex; justify-content: flex-end; margin: 0 0 6px; }' +
            '.exportar-barra .btn { font-size: 12px; padding: 3px 10px; line-height: 1.4; background: #fff; border-color: #d5dae3; color: #374151; }' +
            '.exportar-barra .btn:hover { background: #f3f5f9; color: #111827; }' +
            '.exportar-barra .btn i { margin-right: 4px; }' +
            '.exportar-barra .fa-file-excel { color: #1d6f42; }' +
            '.exportar-barra .fa-file-pdf { color: #c0392b; }' +
            '.exportar-en-linea { display: inline-flex; margin: 0; }';
        document.head.appendChild(css);
    }

    function grupoBotones() {
        return $('<div class="btn-group btn-group-sm" role="group" aria-label="Exportar">' +
            '<button type="button" class="btn exportar-excel" title="Exportar a Excel lo que muestra la tabla"><i class="fa fa-file-excel"></i>Excel</button>' +
            '<button type="button" class="btn exportar-pdf" title="Exportar a PDF lo que muestra la tabla"><i class="fa fa-file-pdf"></i>PDF</button>' +
        '</div>');
    }

    function exportar(tabla, formato) {
        const op = opcionesDeTabla(tabla);
        if (!op.columnas.length || !op.filas.length) {
            avisar('La tabla no tiene filas para exportar.');
            return;
        }
        if (formato === 'pdf') {
            window.exportarPdf(op);
        } else if (window.exportarExcel) {
            window.exportarExcel(op);
        }
    }

    function preparar(tabla) {
        if (tabla.dataset.exportarListo) return;
        tabla.dataset.exportarListo = '1';
        estilos();

        const $grupo = grupoBotones();
        $grupo.on('click', '.exportar-excel', function () { exportar(tabla, 'excel'); });
        $grupo.on('click', '.exportar-pdf', function () { exportar(tabla, 'pdf'); });

        const destino = tabla.getAttribute('data-exportar-botones');
        if (destino && $(destino).length) {
            $(destino).first().append($('<div class="exportar-barra exportar-en-linea"></div>').append($grupo));
            return;
        }
        // Arriba de la tabla: fuera del contenedor con scroll y del envoltorio de DataTables
        const $tabla = $(tabla);
        let $ancla = $tabla.closest('.dataTables_wrapper');
        if (!$ancla.length) $ancla = $tabla.closest('.table-responsive');
        if (!$ancla.length) $ancla = $tabla;
        $ancla.before($('<div class="exportar-barra"></div>').append($grupo));
    }

    function prepararTodas(raiz) {
        $(raiz).find('table[data-exportar]').addBack('table[data-exportar]').each(function () { preparar(this); });
    }

    $(function () {
        estilos(); // también para pantallas que ponen sus propios botones (.exportar-barra)
        prepararTodas(document);
        // Tablas que aparecen después (modales, pestañas armadas por JavaScript). Se espera
        // un momento para que DataTables termine de envolver la tabla.
        new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                cambio.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType === 1 && (nodo.matches('table[data-exportar]') || nodo.querySelector('table[data-exportar]'))) {
                        setTimeout(function () { prepararTodas(nodo); }, 0);
                    }
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    });

    window.ExportarTabla = { datos: opcionesDeTabla, exportar: exportar };
})(jQuery);
