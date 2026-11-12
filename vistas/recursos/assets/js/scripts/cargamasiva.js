/**
 * Carga masiva desde Excel (pantalla cargamasiva, solo ADM).
 *
 * El .xlsx se ARMA y se LEE aquí, en el navegador, con JSZip (viene en datatables.bundle.js):
 * el servidor no necesita librerías de Excel ni la extensión zip de PHP.
 *
 * Plantilla (una por programa):
 *   - "Estudiantes": título en la fila 1, encabezados en la fila 2, datos desde la fila 3.
 *     Columnas fijas del estudiante, una columna de nota por cada módulo del programa y las
 *     del trabajo de grado (opcionales). Listas desplegables para Expedido, Profesión y Universidad.
 *   - "Instrucciones".
 *   - "Listas" y "_sistema" (ocultas): valores de las listas y, en _sistema, el programa y a
 *     qué dato corresponde cada encabezado. Al leer, las columnas se reconocen por su
 *     encabezado, así que da igual si el usuario cambia su orden.
 */
(function () {
    'use strict';

    var AJAX = 'ajax/cargamasiva.ajax.php';
    var FILAS_CON_FORMATO = 500;
    var MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    // Columnas fijas. tipo: texto | fecha | nota ; grupo: obligatorio | opcional | nota | tesis
    var FIJAS = [
        { k: 'ci', t: 'C.I. *', w: 12, g: 'obligatorio' },
        { k: 'complemento', t: 'Complemento', w: 9, g: 'opcional' },
        { k: 'exp', t: 'Expedido *', w: 10, g: 'obligatorio', lista: 'A' },
        { k: 'nombre', t: 'Nombres *', w: 22, g: 'obligatorio' },
        { k: 'apaterno', t: 'Apellido paterno *', w: 18, g: 'obligatorio' },
        { k: 'amaterno', t: 'Apellido materno', w: 18, g: 'opcional' },
        { k: 'fechaNacimiento', t: 'Fecha de nacimiento', w: 13, g: 'opcional', tipo: 'fecha' },
        { k: 'correo', t: 'Correo', w: 26, g: 'opcional' },
        { k: 'celular', t: 'Celular', w: 11, g: 'opcional' },
        { k: 'profesion', t: 'Profesión *', w: 28, g: 'obligatorio', lista: 'B' },
        { k: 'universidad', t: 'Universidad de egreso', w: 32, g: 'opcional', lista: 'C' },
        { k: 'fechaEgreso', t: 'Fecha de egreso', w: 13, g: 'opcional', tipo: 'fecha' },
        { k: 'fechaInscripcion', t: 'Fecha de inscripción *', w: 13, g: 'obligatorio', tipo: 'fecha' }
    ];
    var TESIS = [
        { k: 'titulo', t: 'Título del trabajo de grado', w: 45, g: 'tesis' },
        { k: 'fechaEntrega', t: 'Fecha de entrega del trabajo', w: 13, g: 'tesis', tipo: 'fecha' },
        { k: 'fechaAprobacion', t: 'Fecha de aprobación del trabajo', w: 13, g: 'tesis', tipo: 'fecha' },
        { k: 'calificacionTesis', t: 'Calificación del trabajo', w: 11, g: 'tesis', tipo: 'nota' },
        { k: 'presidente', t: 'Tribunal: Presidente', w: 28, g: 'tesis' },
        { k: 'tribunal1', t: 'Tribunal: Miembro 1', w: 28, g: 'tesis' },
        { k: 'tribunal2', t: 'Tribunal: Miembro 2', w: 28, g: 'tesis' },
        { k: 'secretaria', t: 'Tribunal: Secretario(a)', w: 28, g: 'tesis' }
    ];
    // Pagos (opcionales): total del programa, matrícula y, por cada cuota, monto, fecha y voucher.
    // Claves "pago:..." → fila.pagos (ver CargaMasivaModelo::AnalizarPagos)
    function columnasPagos(costo, cuotas) {
        var costoTexto = Number(costo || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var cols = [
            { k: 'pago:total', t: 'Total a pagar del programa Bs. (vacío = ' + costoTexto + ')', w: 16, g: 'pago', tipo: 'monto' },
            { k: 'pago:matricula', t: 'Matrícula Bs.', w: 12, g: 'pago', tipo: 'monto' },
            { k: 'pago:voucherMatricula', t: 'Voucher matrícula', w: 14, g: 'pago' }
        ];
        for (var n = 1; n <= cuotas; n++) {
            cols.push({ k: 'pago:cuota' + n + 'monto', t: 'Cuota ' + n + ' Bs.', w: 12, g: 'pago', tipo: 'monto' });
            cols.push({ k: 'pago:cuota' + n + 'fecha', t: 'Cuota ' + n + ' fecha de pago', w: 13, g: 'pago', tipo: 'fecha' });
            cols.push({ k: 'pago:cuota' + n + 'voucher', t: 'Cuota ' + n + ' voucher', w: 14, g: 'pago' });
        }
        return cols;
    }
    var ESTILO_ENCABEZADO = { obligatorio: 1, opcional: 2, nota: 3, tesis: 4, pago: 12 };

    // ------------------------------------------------------------------ utilidades
    function xml(texto) {
        return String(texto == null ? '' : texto)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '');
    }
    function letra(i) { // 0 → A
        var s = '';
        i += 1;
        while (i > 0) { var r = (i - 1) % 26; s = String.fromCharCode(65 + r) + s; i = Math.floor((i - 1) / 26); }
        return s;
    }
    function indiceDeLetras(letras) { // "A" → 0
        var n = 0;
        for (var i = 0; i < letras.length; i++) n = n * 26 + (letras.charCodeAt(i) - 64);
        return n - 1;
    }
    function celdaTexto(ref, texto, estilo) {
        return '<c r="' + ref + '" t="inlineStr"' + (estilo ? ' s="' + estilo + '"' : '') + '><is><t xml:space="preserve">' + xml(texto) + '</t></is></c>';
    }
    function comparable(t) {
        return String(t || '').toUpperCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/\s+/g, ' ').trim();
    }
    function escaparHtml(t) { return $('<div>').text(t == null ? '' : String(t)).html(); }

    // ------------------------------------------------------------------ plantilla
    function hojaXml(filas, opciones) {
        opciones = opciones || {};
        var s = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        s += '<sheetViews><sheetView workbookViewId="0"' + (opciones.activa ? ' tabSelected="1"' : '') + '>' + (opciones.congelar || '') + '</sheetView></sheetViews>';
        s += '<sheetFormatPr defaultRowHeight="15"/>';
        if (opciones.cols) s += '<cols>' + opciones.cols + '</cols>';
        s += '<sheetData>' + filas + '</sheetData>';
        if (opciones.combinar) s += '<mergeCells count="1"><mergeCell ref="' + opciones.combinar + '"/></mergeCells>';
        if (opciones.validaciones) s += opciones.validaciones;
        s += '<pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/></worksheet>';
        return s;
    }

    function estilosXml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' +
            '<numFmts count="1"><numFmt numFmtId="164" formatCode="dd/mm/yyyy"/></numFmts>' +
            '<fonts count="4"><font><sz val="11"/><name val="Calibri"/></font>' +
            '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>' +
            '<font><b/><sz val="14"/><color rgb="FF1F3A5F"/><name val="Calibri"/></font>' +
            '<font><b/><sz val="11"/><color rgb="FF1F3A5F"/><name val="Calibri"/></font></fonts>' +
            '<fills count="8"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FF1F3A5F"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FF5B7FA6"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FFB8892B"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FF2F5D3A"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF8E1"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FF595959"/></patternFill></fill></fills>' +
            '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>' +
            '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right>' +
            '<top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom><diagonal/></border></borders>' +
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' +
            '<cellXfs count="13">' +
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' +                                                                 // 0
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' + // 1 obligatorio
            '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' + // 2 opcional
            '<xf numFmtId="0" fontId="1" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' + // 3 nota
            '<xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' + // 4 tesis
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' +                                                 // 5 título
            '<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' +                       // 6 texto
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' +                      // 7 fecha
            '<xf numFmtId="0" fontId="0" fillId="6" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>' +                                // 8 nota (dato)
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>' + // 9 instrucciones
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>' +                                                 // 10 subtítulo
            '<xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' +                        // 11 monto (#,##0.00)
            '<xf numFmtId="0" fontId="1" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' + // 12 pagos
            '</cellXfs></styleSheet>';
    }

    function armarPlantilla(datos) {
        var programa = datos.programa;
        var columnas = FIJAS.slice();
        datos.modulos.forEach(function (m, i) {
            var nombre = (m.codigomodulo ? m.codigomodulo + ' · ' : '') + m.nombremodulo;
            columnas.push({ k: 'nota:' + m.Idmodulo, t: 'Nota M' + (i + 1) + ': ' + nombre, w: 16, g: 'nota', tipo: 'nota' });
        });
        columnas = columnas.concat(columnasPagos(programa.Costo, datos.cuotas || 1), TESIS);

        var ultima = letra(columnas.length - 1);
        var nombrePrograma = programa.NombrePrograma + (programa.Version ? ' (' + programa.Version + ')' : '');

        // Hoja Estudiantes
        var cols = '';
        var encabezados = '';
        columnas.forEach(function (c, i) {
            var estiloDato = c.tipo === 'fecha' ? 7 : (c.tipo === 'nota' ? 8 : (c.tipo === 'monto' ? 11 : 6));
            cols += '<col min="' + (i + 1) + '" max="' + (i + 1) + '" width="' + c.w + '" style="' + estiloDato + '" customWidth="1"/>';
            encabezados += celdaTexto(letra(i) + '2', c.t, ESTILO_ENCABEZADO[c.g]);
        });
        var filas = '<row r="1" ht="24" customHeight="1">' + celdaTexto('A1', 'CARGA MASIVA · ' + nombrePrograma + ' · Datos desde la fila 3 (vea la hoja Instrucciones)', 5) + '</row>' +
            '<row r="2" ht="48" customHeight="1">' + encabezados + '</row>';

        var hasta = FILAS_CON_FORMATO + 2;
        var val = [];
        var listas = { A: datos.expedidos, B: datos.profesiones, C: datos.universidades };
        columnas.forEach(function (c, i) {
            var rango = letra(i) + '3:' + letra(i) + hasta;
            if (c.lista && listas[c.lista].length) {
                val.push('<dataValidation type="list" allowBlank="1" showErrorMessage="1" errorTitle="Valor no válido" error="Elija un valor de la lista" sqref="' + rango + '">' +
                    '<formula1>Listas!$' + c.lista + '$2:$' + c.lista + '$' + (listas[c.lista].length + 1) + '</formula1></dataValidation>');
            } else if (c.tipo === 'nota') {
                val.push('<dataValidation type="decimal" operator="between" allowBlank="1" showErrorMessage="1" errorTitle="Nota no válida" error="La nota va de 0 a 100" sqref="' + rango + '">' +
                    '<formula1>0</formula1><formula2>100</formula2></dataValidation>');
            } else if (c.tipo === 'monto') {
                val.push('<dataValidation type="decimal" operator="greaterThanOrEqual" allowBlank="1" showErrorMessage="1" errorTitle="Monto no válido" error="Escriba el monto en Bs. (solo números)" sqref="' + rango + '">' +
                    '<formula1>0</formula1></dataValidation>');
            }
        });
        var hojaEstudiantes = hojaXml(filas, {
            activa: true,
            cols: cols,
            combinar: 'A1:' + letra(Math.min(columnas.length, 13) - 1) + '1',
            congelar: '<pane xSplit="6" ySplit="2" topLeftCell="G3" activePane="bottomRight" state="frozen"/><selection pane="bottomRight" activeCell="A3" sqref="A3"/>',
            validaciones: '<dataValidations count="' + val.length + '">' + val.join('') + '</dataValidations>'
        });

        // Hoja Instrucciones
        var lineas = [
            ['CÓMO LLENAR ESTE EXCEL', 5],
            [nombrePrograma + ' · ' + datos.modulos.length + ' módulos', 10],
            ['', 0],
            ['1. Llene la hoja "Estudiantes" desde la fila 3: una fila por estudiante. No cambie los encabezados de la fila 2 ni borre las hojas ocultas.', 9],
            ['2. Columnas azul oscuro (con *): obligatorias. Azul claro: opcionales. Doradas: notas de cada módulo (de 0 a 100; vacío = sin nota). Grises: pagos (opcionales). Verdes: trabajo de grado (opcional).', 9],
            ['3. Fechas en formato dd/mm/aaaa (por ejemplo 15/03/2024).', 9],
            ['4. Expedido, Profesión y Universidad se eligen de la lista desplegable. Si falta una profesión o universidad, agréguela primero en el sistema y descargue la plantilla otra vez.', 9],
            ['5. Si el C.I. ya está registrado, se usa ese estudiante y NO se cambian sus datos (el apellido debe coincidir). Las notas o el trabajo de grado que ya tenga en el sistema tampoco se reemplazan.', 9],
            ['6. La inscripción se registra como CARGA HISTÓRICA (sin órdenes de pago), para poder emitir los certificados.', 9],
            ['   PAGOS (columnas grises): llénelos para que el saldo y los reportes de costos salgan correctos. "Total a pagar del programa": lo que el estudiante debía pagar con su descuento (vacío = costo del programa, ' + Number(programa.Costo || 0).toFixed(2) + ' Bs.). Por cada cuota pagada: monto en Bs., fecha de pago (vacía = fecha de inscripción) y N° de voucher (opcional; si lo escribe, no puede estar usado en el sistema). Las cuotas no pueden sumar más que el total; lo que falte queda como saldo. Sin pagos, el estudiante figura con saldo igual al costo del programa.', 9],
            ['   Si el estudiante ya se cargó antes sin pagos, puede subir el Excel otra vez con los pagos: se agregan solo a las inscripciones de carga histórica que todavía no tienen pagos.', 9],
            ['7. Trabajo de grado: si llena alguna columna verde, el título y la fecha de aprobación son obligatorios. Cada miembro del tribunal debe ser una persona distinta.', 9],
            ['8. Suba el archivo en Carga masiva: primero se REVISA (no guarda nada) y usted ve qué filas tienen errores; luego registra solo las filas correctas.', 9],
            ['', 0],
            ['Máximo 500 estudiantes por archivo.', 10]
        ];
        var filasInstr = lineas.map(function (l, i) {
            return '<row r="' + (i + 1) + '">' + (l[0] ? celdaTexto('A' + (i + 1), l[0], l[1]) : '') + '</row>';
        }).join('');
        var hojaInstrucciones = hojaXml(filasInstr, { cols: '<col min="1" max="1" width="130" customWidth="1"/>' });

        // Hoja Listas (oculta)
        var maxLista = Math.max(datos.expedidos.length, datos.profesiones.length, datos.universidades.length);
        var filasListas = '<row r="1">' + celdaTexto('A1', 'Expedido') + celdaTexto('B1', 'Profesión') + celdaTexto('C1', 'Universidad') + '</row>';
        for (var i = 0; i < maxLista; i++) {
            filasListas += '<row r="' + (i + 2) + '">' +
                (datos.expedidos[i] ? celdaTexto('A' + (i + 2), datos.expedidos[i]) : '') +
                (datos.profesiones[i] ? celdaTexto('B' + (i + 2), datos.profesiones[i]) : '') +
                (datos.universidades[i] ? celdaTexto('C' + (i + 2), datos.universidades[i]) : '') + '</row>';
        }
        var hojaListas = hojaXml(filasListas);

        // Hoja _sistema (oculta): programa y encabezado → dato
        var filasSistema = '<row r="1">' + celdaTexto('A1', 'PROGRAMA') + celdaTexto('B1', String(programa.ProgramaID)) + '</row>';
        columnas.forEach(function (c, i) {
            filasSistema += '<row r="' + (i + 2) + '">' + celdaTexto('A' + (i + 2), c.t) + celdaTexto('B' + (i + 2), c.k) + '</row>';
        });
        var hojaSistema = hojaXml(filasSistema);

        var zip = new JSZip();
        zip.file('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' +
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' +
            '<Default Extension="xml" ContentType="application/xml"/>' +
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' +
            [1, 2, 3, 4].map(function (n) { return '<Override PartName="/xl/worksheets/sheet' + n + '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'; }).join('') +
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        zip.file('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        zip.file('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' +
            '<bookViews><workbookView activeTab="0"/></bookViews><sheets>' +
            '<sheet name="Estudiantes" sheetId="1" r:id="rId1"/><sheet name="Instrucciones" sheetId="2" r:id="rId2"/>' +
            '<sheet name="Listas" sheetId="3" state="hidden" r:id="rId3"/><sheet name="_sistema" sheetId="4" state="hidden" r:id="rId4"/>' +
            '</sheets></workbook>');
        zip.file('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
            [1, 2, 3, 4].map(function (n) { return '<Relationship Id="rId' + n + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' + n + '.xml"/>'; }).join('') +
            '<Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        zip.file('xl/styles.xml', estilosXml());
        zip.file('xl/worksheets/sheet1.xml', hojaEstudiantes);
        zip.file('xl/worksheets/sheet2.xml', hojaInstrucciones);
        zip.file('xl/worksheets/sheet3.xml', hojaListas);
        zip.file('xl/worksheets/sheet4.xml', hojaSistema);

        var nombreArchivo = 'carga-masiva-' + comparable(programa.NombrePrograma).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 50) +
            (programa.Version ? '-' + programa.Version.toLowerCase() : '');
        return zip.generateAsync({ type: 'blob', mimeType: MIME_XLSX }).then(function (blob) {
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = nombreArchivo + '.xlsx';
            document.body.appendChild(a);
            a.click();
            setTimeout(function () { URL.revokeObjectURL(url); a.remove(); }, 1000);
        });
    }

    // ------------------------------------------------------------------ lectura del Excel
    function parsear(texto) { return new DOMParser().parseFromString(texto, 'application/xml'); }
    function etiquetas(nodo, nombre) { return nodo.getElementsByTagNameNS('*', nombre); }
    function textoDe(nodo) {
        var ts = etiquetas(nodo, 't');
        var s = '';
        for (var i = 0; i < ts.length; i++) s += ts[i].textContent;
        return s;
    }

    /* Devuelve { nombreHoja: [[fila][col] = texto] } (filas y columnas desde 0) */
    function leerLibro(archivo) {
        return JSZip.loadAsync(archivo).then(function (zip) {
            var leer = function (ruta) { var f = zip.file(ruta); return f ? f.async('string') : Promise.resolve(null); };
            return Promise.all([leer('xl/workbook.xml'), leer('xl/_rels/workbook.xml.rels'), leer('xl/sharedStrings.xml')]).then(function (r) {
                if (!r[0]) throw new Error('El archivo no es un Excel .xlsx válido');
                var libro = parsear(r[0]);
                var rels = parsear(r[1] || '<x/>');
                var compartidos = [];
                if (r[2]) {
                    var sis = etiquetas(parsear(r[2]), 'si');
                    for (var i = 0; i < sis.length; i++) compartidos.push(textoDe(sis[i]));
                }
                var destinos = {};
                var relLista = etiquetas(rels, 'Relationship');
                for (var j = 0; j < relLista.length; j++) {
                    destinos[relLista[j].getAttribute('Id')] = relLista[j].getAttribute('Target');
                }
                var hojas = etiquetas(libro, 'sheet');
                var tareas = [];
                for (var k = 0; k < hojas.length; k++) {
                    (function (hoja) {
                        var rid = hoja.getAttribute('r:id') || hoja.getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                        var destino = destinos[rid] || '';
                        var ruta = destino.charAt(0) === '/' ? destino.slice(1) : 'xl/' + destino.replace(/^\.\//, '');
                        tareas.push(leer(ruta).then(function (contenido) {
                            return [hoja.getAttribute('name'), contenido ? leerHoja(parsear(contenido), compartidos) : []];
                        }));
                    })(hojas[k]);
                }
                return Promise.all(tareas).then(function (pares) {
                    var resultado = {};
                    pares.forEach(function (p) { resultado[p[0]] = p[1]; });
                    return resultado;
                });
            });
        });
    }

    function leerHoja(doc, compartidos) {
        var matriz = [];
        var celdas = etiquetas(doc, 'c');
        for (var i = 0; i < celdas.length; i++) {
            var c = celdas[i];
            var m = /^([A-Z]+)(\d+)$/.exec(c.getAttribute('r') || '');
            if (!m) continue;
            var col = indiceDeLetras(m[1]);
            var fila = parseInt(m[2], 10) - 1;
            var tipo = c.getAttribute('t');
            var vNodo = etiquetas(c, 'v')[0];
            var valor;
            if (tipo === 's') valor = vNodo ? (compartidos[parseInt(vNodo.textContent, 10)] || '') : '';
            else if (tipo === 'inlineStr') valor = textoDe(c);
            else valor = vNodo ? vNodo.textContent : '';
            (matriz[fila] = matriz[fila] || [])[col] = valor;
        }
        return matriz;
    }

    /* Número de serie de Excel (45366) → "15/03/2024"; el resto queda igual */
    function fechaDeExcel(valor) {
        var v = String(valor).trim();
        if (/^\d+(\.\d+)?$/.test(v) && parseFloat(v) > 59) {
            var d = new Date(Date.UTC(1899, 11, 30) + Math.round(parseFloat(v)) * 86400000);
            return ('0' + d.getUTCDate()).slice(-2) + '/' + ('0' + (d.getUTCMonth() + 1)).slice(-2) + '/' + d.getUTCFullYear();
        }
        return v;
    }
    /* 85.5000000001 (decimales de Excel) → "85.5" */
    function numeroDeExcel(valor) {
        var v = String(valor).trim();
        return /^-?\d+(\.\d+)?(E[-+]?\d+)?$/i.test(v) ? String(Math.round(parseFloat(v) * 100) / 100) : v;
    }

    /* Hojas del libro → { programaID, filas } con las filas en el formato del servidor */
    function filasDelLibro(hojas) {
        var sistema = hojas._sistema;
        if (!sistema || !sistema[0] || sistema[0][0] !== 'PROGRAMA') {
            throw new Error('Este Excel no es una plantilla del sistema. Descargue la plantilla del programa (paso 1) y llene esa.');
        }
        var programaID = parseInt(sistema[0][1], 10);
        var claves = {};
        for (var i = 1; i < sistema.length; i++) {
            if (sistema[i] && sistema[i][0]) claves[comparable(sistema[i][0])] = sistema[i][1];
        }
        var hoja = hojas.Estudiantes;
        if (!hoja) throw new Error('No se encontró la hoja "Estudiantes"');

        // Fila de encabezados: la que más coincide con los de la plantilla (normalmente la 2)
        var filaEnc = -1, mejor = 0;
        for (var f = 0; f < Math.min(hoja.length, 6); f++) {
            var aciertos = (hoja[f] || []).filter(function (t) { return t && claves[comparable(t)]; }).length;
            if (aciertos > mejor) { mejor = aciertos; filaEnc = f; }
        }
        if (filaEnc < 0) throw new Error('No se encontraron los encabezados de la plantilla en la hoja "Estudiantes"');
        var mapa = []; // col → clave
        (hoja[filaEnc] || []).forEach(function (t, col) { if (t && claves[comparable(t)]) mapa[col] = claves[comparable(t)]; });
        if (mapa.indexOf('ci') < 0) throw new Error('Falta la columna "C.I. *" en la hoja "Estudiantes"');

        var fechas = {}, notas = {};
        FIJAS.concat(TESIS).forEach(function (c) { if (c.tipo === 'fecha') fechas[c.k] = 1; if (c.tipo === 'nota') notas[c.k] = 1; });

        var filas = [];
        for (var r = filaEnc + 1; r < hoja.length; r++) {
            var celdas = hoja[r];
            if (!celdas) continue;
            var fila = { fila: r + 1, notas: {}, pagos: {} };
            var conDatos = false;
            mapa.forEach(function (clave, col) {
                var v = celdas[col];
                if (v == null || String(v).trim() === '') return;
                conDatos = true;
                if (clave.indexOf('nota:') === 0) fila.notas[clave.slice(5)] = numeroDeExcel(v);
                else if (clave.indexOf('pago:') === 0) {
                    var campo = clave.slice(5);
                    fila.pagos[campo] = /fecha$/.test(campo) ? fechaDeExcel(v)
                        : (/voucher/i.test(campo) ? numeroDeExcel(v).replace(/\.0+$/, '') : numeroDeExcel(v));
                }
                else if (fechas[clave]) fila[clave] = fechaDeExcel(v);
                else if (notas[clave]) fila[clave] = numeroDeExcel(v);
                else if (clave === 'ci' || clave === 'celular') fila[clave] = numeroDeExcel(v).replace(/\.0+$/, '');
                else fila[clave] = String(v).trim();
            });
            if (conDatos) filas.push(fila);
        }
        return { programaID: programaID, filas: filas };
    }

    // ------------------------------------------------------------------ pantalla
    var lectura = null; // { programaID, filas, archivo }

    function mostrarResultado(resp, registrado) {
        var res = resp.resumen;
        var $r = $('#cm-resultado').show();
        var programa = resp.programa.NombrePrograma + (resp.programa.Version ? ' (' + resp.programa.Version + ')' : '');
        var html = '<div class="cm-programa"><i class="fas fa-graduation-cap"></i> ' + escaparHtml(programa) + ' · archivo <b>' + escaparHtml(lectura.archivo) + '</b></div>';
        html += '<div class="cm-cifras">' +
            '<div class="cm-cifra"><b>' + res.total + '</b><span>filas en el Excel</span></div>' +
            (registrado
                ? '<div class="cm-cifra ok"><b>' + res.registradas + '</b><span>registradas</span></div>' +
                  '<div class="cm-cifra"><b>' + res.estudiantesNuevos + '</b><span>estudiantes nuevos</span></div>' +
                  '<div class="cm-cifra"><b>' + res.notas + '</b><span>notas</span></div>' +
                  '<div class="cm-cifra"><b>' + res.trabajos + '</b><span>trabajos de grado</span></div>' +
                  '<div class="cm-cifra"><b>' + (res.pagos || 0) + '</b><span>con pagos</span></div>'
                : '<div class="cm-cifra ok"><b>' + res.validas + '</b><span>listas para registrar</span></div>') +
            '<div class="cm-cifra ' + (res.conError ? 'error' : '') + '"><b>' + res.conError + '</b><span>con errores' + (registrado ? ' (no se registraron)' : '') + '</span></div>' +
            '</div>';

        if (registrado) {
            html += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Se registraron ' + res.registradas + ' estudiantes. Ya puede buscarlos en los certificados.' +
                (res.conError ? ' Corrija en el Excel las filas con error y súbalo otra vez: las que ya se registraron no se duplican.' : '') + '</div>';
        } else if (!res.validas) {
            html += '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Ninguna fila se puede registrar. Corrija los errores en el Excel y súbalo otra vez.</div>';
        } else {
            html += '<div class="cm-confirmar"><span>' + (res.conError ? 'Las filas con error <b>no</b> se registrarán; puede corregirlas y subirlas después.' : 'Todas las filas están correctas.') + '</span>' +
                '<button type="button" class="btn btn-success" id="cm-registrar"><i class="fas fa-database"></i> Registrar ' + res.validas + ' estudiante' + (res.validas === 1 ? '' : 's') + '</button></div>';
        }

        html += '<div class="cm-filtro"><label><input type="checkbox" id="cm-solo-errores"' + (res.conError ? '' : ' disabled') + '> Ver solo las filas con error</label>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" id="cm-informe"><i class="fas fa-file-excel"></i> Descargar informe</button></div>';
        html += '<div class="cm-tabla-envoltura"><table class="cm-tabla"><thead><tr><th>Fila</th><th>C.I.</th><th>Estudiante</th><th>Qué se hace</th><th>Observaciones</th></tr></thead><tbody>';
        resp.filas.forEach(function (f) {
            var estado = f.errores.length ? 'error' : (f.avisos.length ? 'aviso' : 'ok');
            html += '<tr class="cm-' + estado + '">' +
                '<td>' + f.fila + '</td><td>' + escaparHtml(f.ci) + '</td><td>' + escaparHtml(f.nombre) + '</td>' +
                '<td>' + (f.errores.length ? '<span class="cm-badge error">NO SE REGISTRA</span>' : (registrado && f.registrada ? '<span class="cm-badge ok">REGISTRADA</span> ' : '') + escaparHtml(f.acciones.join(' · '))) + '</td>' +
                '<td>' + f.errores.map(function (e) { return '<div class="cm-msg error"><i class="fas fa-times-circle"></i> ' + escaparHtml(e) + '</div>'; }).join('') +
                f.avisos.map(function (a) { return '<div class="cm-msg aviso"><i class="fas fa-exclamation-triangle"></i> ' + escaparHtml(a) + '</div>'; }).join('') + '</td></tr>';
        });
        html += '</tbody></table></div>';
        $r.html(html);
        $r.data('respuesta', resp).data('registrado', registrado);
        $('html, body').animate({ scrollTop: $r.offset().top - 90 }, 300);
    }

    function enviar(accion) {
        return $.ajax({
            url: AJAX, method: 'POST', dataType: 'json', timeout: 300000,
            data: { accion: accion, programaID: lectura.programaID, filas: JSON.stringify(lectura.filas) }
        });
    }

    $(function () {
        var $programa = $('#cm-programa');

        $programa.on('change', function () {
            var $o = $(this).find(':selected');
            $('#cm-info-programa').html($o.val()
                ? '<i class="fas fa-info-circle"></i> ' + $o.data('modulos') + ' módulos (una columna de nota por módulo) · ' + $o.data('inscritos') + ' inscritos actualmente'
                : '');
            $('#cm-descargar').prop('disabled', !$o.val() || !$o.data('modulos'));
            if ($o.val() && !$o.data('modulos')) {
                $('#cm-info-programa').html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Este programa no tiene módulos: créelos primero en Módulos.</span>');
            }
        });

        $('#cm-descargar').on('click', function () {
            var $b = $(this).prop('disabled', true);
            if (typeof JSZip === 'undefined') {
                Swal.fire('Error', 'No se cargó el componente de Excel. Recargue la página.', 'error');
                $b.prop('disabled', false);
                return;
            }
            $.post(AJAX, { accion: 'plantilla', programaID: $programa.val() }, null, 'json').done(function (resp) {
                if (!resp || !resp.success) {
                    Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo armar la plantilla', 'warning');
                    return;
                }
                armarPlantilla(resp).catch(function () { Swal.fire('Error', 'No se pudo generar el Excel', 'error'); });
            }).fail(function () {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }).always(function () { $b.prop('disabled', false); });
        });

        $('#cm-archivo').on('change', function () {
            var archivo = this.files[0];
            $('#cm-nombre-archivo').text(archivo ? archivo.name : 'Ningún archivo elegido');
            $('#cm-revisar').prop('disabled', !archivo);
            $('#cm-resultado').hide().empty();
        });

        $('#cm-revisar').on('click', function () {
            var archivo = $('#cm-archivo')[0].files[0];
            if (!archivo) return;
            if (!/\.xlsx$/i.test(archivo.name)) {
                Swal.fire('Atención', 'El archivo debe ser .xlsx (Excel). Si lo guardó como .xls o .csv, ábralo y use "Guardar como" > Libro de Excel (.xlsx).', 'warning');
                return;
            }
            var $b = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Revisando...');
            leerLibro(archivo).then(function (hojas) {
                lectura = filasDelLibro(hojas);
                lectura.archivo = archivo.name;
                if (!lectura.filas.length) throw new Error('La hoja "Estudiantes" no tiene filas con datos (se llenan desde la fila 3)');
                return enviar('validar').then(function (resp) {
                    if (!resp || !resp.success) throw new Error((resp && resp.mensaje) || 'No se pudo revisar el Excel');
                    mostrarResultado(resp, false);
                }, function (xhr) {
                    throw new Error((xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo conectar con el servidor');
                });
            }).catch(function (e) {
                Swal.fire('No se pudo leer el Excel', e.message || String(e), 'warning');
            }).then(function () {
                $b.prop('disabled', false).html('<i class="fas fa-search"></i> Revisar Excel');
            });
        });

        $(document).on('click', '#cm-registrar', function () {
            var resp = $('#cm-resultado').data('respuesta');
            Swal.fire({
                type: 'question',
                title: '¿Registrar ' + resp.resumen.validas + ' estudiante' + (resp.resumen.validas === 1 ? '' : 's') + '?',
                html: 'Se crearán los estudiantes nuevos, sus inscripciones como <b>carga histórica</b>, las notas, los pagos y los trabajos de grado.<br><small>Las filas con error no se registran.</small>',
                showCancelButton: true, confirmButtonText: 'Sí, registrar', cancelButtonText: 'Cancelar'
            }).then(function (r) {
                if (!r.value) return;
                var $b = $('#cm-registrar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');
                enviar('importar').done(function (resp2) {
                    if (!resp2 || !resp2.success) {
                        Swal.fire('Atención', (resp2 && resp2.mensaje) || 'No se pudo registrar', 'warning');
                        $b.prop('disabled', false);
                        return;
                    }
                    mostrarResultado(resp2, true);
                    Swal.fire({ type: 'success', title: resp2.resumen.registradas + ' estudiantes registrados', timer: 2200, showConfirmButton: false });
                    $('#cm-archivo').val('');
                    $('#cm-nombre-archivo').text('Ningún archivo elegido');
                    $('#cm-revisar').prop('disabled', true);
                }).fail(function (xhr) {
                    Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo conectar con el servidor', 'error');
                    $b.prop('disabled', false);
                });
            });
        });

        $(document).on('change', '#cm-solo-errores', function () {
            $('.cm-tabla tbody tr').not('.cm-error').toggle(!this.checked);
        });

        $(document).on('click', '#cm-informe', function () {
            var resp = $('#cm-resultado').data('respuesta');
            var registrado = $('#cm-resultado').data('registrado');
            window.exportarExcel({
                archivo: 'informe-carga-masiva',
                hoja: 'Informe',
                titulo: 'CARGA MASIVA · ' + resp.programa.NombrePrograma,
                subtitulo: (registrado ? 'Resultado del registro' : 'Revisión (sin registrar)') + ' · ' + lectura.archivo,
                columnas: [{ titulo: 'Fila', ancho: 6, tipo: 'numero' }, { titulo: 'C.I.', ancho: 12 }, { titulo: 'Estudiante', ancho: 35 },
                    { titulo: 'Estado', ancho: 18 }, { titulo: 'Qué se hace', ancho: 50 }, { titulo: 'Observaciones', ancho: 80 }],
                filas: resp.filas.map(function (f) {
                    return [f.fila, f.ci, f.nombre,
                        f.errores.length ? 'CON ERROR' : (registrado ? (f.registrada ? 'REGISTRADA' : '') : 'CORRECTA'),
                        f.acciones.join(' · '), f.errores.concat(f.avisos).join(' | ')];
                })
            });
        });
    });

    // Para las pruebas
    window.CargaMasiva = { filasDelLibro: filasDelLibro, leerLibro: leerLibro };
})();
