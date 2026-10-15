/**
 * Exportar una lista a Excel (.xlsx real, no CSV) en el navegador.
 *
 * Usa JSZip, que ya viene dentro de datatables.bundle.js (cargado en plantilla.php).
 * Arma un libro con una hoja: título, subtítulo (filtros), encabezados en negrita con
 * fondo, filas y, si se pasa, una fila de totales. Los montos quedan como números
 * (con formato #,##0.00) para que en Excel se puedan sumar y filtrar.
 *
 *   window.exportarExcel({
 *       archivo: 'ordenes-de-pago',            // sin extensión
 *       hoja: 'Órdenes',                        // nombre de la hoja (máx. 31)
 *       titulo: 'ÓRDENES DE PAGO',
 *       subtitulo: 'Tipo: Cuota · 01/10/2026 a 15/10/2026',
 *       columnas: [{ titulo: 'N°', ancho: 6, tipo: 'numero' }, { titulo: 'Monto', ancho: 14, tipo: 'moneda' }, ...],
 *       filas: [[1, 'ORD-...', 1000], ...],
 *       totales: ['', 'Total', 1000]            // opcional, una celda por columna
 *   });
 */
(function () {
    'use strict';

    function xml(texto) {
        return String(texto == null ? '' : texto)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            // Caracteres de control no válidos en XML
            .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g, '');
    }

    // 0 → A, 25 → Z, 26 → AA
    function letraColumna(i) {
        let s = '';
        i += 1;
        while (i > 0) {
            const r = (i - 1) % 26;
            s = String.fromCharCode(65 + r) + s;
            i = Math.floor((i - 1) / 26);
        }
        return s;
    }

    // Estilos (índices de cellXfs en styles.xml)
    const ESTILO = { normal: 0, titulo: 1, subtitulo: 2, encabezado: 3, moneda: 4, total: 5, totalMoneda: 6, entero: 7 };

    function celda(ref, valor, tipo, estilo) {
        if (valor === null || valor === undefined || valor === '') {
            return estilo ? '<c r="' + ref + '" s="' + estilo + '"/>' : '';
        }
        const esNumero = (tipo === 'numero' || tipo === 'moneda') && !isNaN(parseFloat(valor)) && isFinite(valor);
        if (esNumero) {
            return '<c r="' + ref + '" s="' + (estilo || 0) + '"><v>' + Number(valor) + '</v></c>';
        }
        return '<c r="' + ref + '" t="inlineStr" s="' + (estilo || 0) + '"><is><t xml:space="preserve">' + xml(valor) + '</t></is></c>';
    }

    function hojaXml(op) {
        const columnas = op.columnas;
        const ultima = letraColumna(columnas.length - 1);
        let filaNum = 0;
        const filas = [];
        const combinadas = [];

        filaNum++;
        filas.push('<row r="' + filaNum + '" ht="20" customHeight="1">' + celda('A' + filaNum, op.titulo || '', 'texto', ESTILO.titulo) + '</row>');
        combinadas.push('A' + filaNum + ':' + ultima + filaNum);
        if (op.subtitulo) {
            filaNum++;
            filas.push('<row r="' + filaNum + '">' + celda('A' + filaNum, op.subtitulo, 'texto', ESTILO.subtitulo) + '</row>');
            combinadas.push('A' + filaNum + ':' + ultima + filaNum);
        }
        filaNum++; // fila en blanco

        filaNum++;
        const filaEncabezado = filaNum;
        filas.push('<row r="' + filaNum + '">' + columnas.map(function (c, i) {
            return celda(letraColumna(i) + filaNum, c.titulo, 'texto', ESTILO.encabezado);
        }).join('') + '</row>');

        op.filas.forEach(function (fila) {
            filaNum++;
            filas.push('<row r="' + filaNum + '">' + fila.map(function (valor, i) {
                const tipo = columnas[i].tipo || 'texto';
                const estilo = tipo === 'moneda' ? ESTILO.moneda : (tipo === 'numero' ? ESTILO.entero : ESTILO.normal);
                return celda(letraColumna(i) + filaNum, valor, tipo, estilo);
            }).join('') + '</row>');
        });

        if (op.totales) {
            filaNum++;
            filas.push('<row r="' + filaNum + '">' + op.totales.map(function (valor, i) {
                const tipo = columnas[i].tipo || 'texto';
                return celda(letraColumna(i) + filaNum, valor, tipo, tipo === 'moneda' ? ESTILO.totalMoneda : ESTILO.total);
            }).join('') + '</row>');
        }

        const anchos = '<cols>' + columnas.map(function (c, i) {
            return '<col min="' + (i + 1) + '" max="' + (i + 1) + '" width="' + (c.ancho || 14) + '" customWidth="1"/>';
        }).join('') + '</cols>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ' +
            'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' +
            // Encabezado fijo al desplazarse
            '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' + filaEncabezado + '" topLeftCell="A' + (filaEncabezado + 1) +
                '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>' +
            anchos +
            '<sheetData>' + filas.join('') + '</sheetData>' +
            (op.filas.length ? '<autoFilter ref="A' + filaEncabezado + ':' + ultima + (filaEncabezado + op.filas.length) + '"/>' : '') +
            '<mergeCells count="' + combinadas.length + '">' + combinadas.map(function (r) { return '<mergeCell ref="' + r + '"/>'; }).join('') + '</mergeCells>' +
            '</worksheet>';
    }

    const ESTILOS_XML =
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' +
        '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>' +
        '<fonts count="4">' +
            '<font><sz val="10"/><name val="Arial"/></font>' +
            '<font><b/><sz val="13"/><color rgb="FF1E3C72"/><name val="Arial"/></font>' +
            '<font><i/><sz val="9"/><color rgb="FF555555"/><name val="Arial"/></font>' +
            '<font><b/><sz val="10"/><name val="Arial"/></font>' +
        '</fonts>' +
        '<fills count="4">' +
            '<fill><patternFill patternType="none"/></fill>' +
            '<fill><patternFill patternType="gray125"/></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FFEEF2F9"/></patternFill></fill>' +
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/></patternFill></fill>' +
        '</fills>' +
        '<borders count="2">' +
            '<border><left/><right/><top/><bottom/><diagonal/></border>' +
            '<border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right>' +
                '<top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border>' +
        '</borders>' +
        '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' +
        '<cellXfs count="8">' +
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>' +                       // 0 normal
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' +                         // 1 título
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>' +                         // 2 subtítulo
            '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>' + // 3 encabezado
            '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' +     // 4 moneda
            '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>' + // 5 total
            '<xf numFmtId="164" fontId="3" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>' + // 6 total moneda
            '<xf numFmtId="1" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>' +      // 7 entero
        '</cellXfs>' +
        '</styleSheet>';

    window.exportarExcel = function (op) {
        const JSZip = window.JSZip;
        if (!JSZip) {
            alert('No se pudo cargar el generador de Excel. Recargue la página e intente de nuevo.');
            return Promise.reject(new Error('JSZip no disponible'));
        }
        const hoja = xml(String(op.hoja || 'Hoja1').replace(/[\\\/\?\*\[\]:]/g, ' ').slice(0, 31));
        const zip = new JSZip();
        zip.file('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' +
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' +
            '<Default Extension="xml" ContentType="application/xml"/>' +
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' +
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' +
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' +
            '</Types>');
        zip.file('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' +
            '</Relationships>');
        zip.file('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' +
            '<sheets><sheet name="' + hoja + '" sheetId="1" r:id="rId1"/></sheets>' +
            '</workbook>');
        zip.file('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' +
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' +
            '</Relationships>');
        zip.file('xl/styles.xml', ESTILOS_XML);
        zip.file('xl/worksheets/sheet1.xml', hojaXml(op));

        return zip.generateAsync({ type: 'blob', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
            .then(function (blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = (op.archivo || 'exportacion') + '.xlsx';
                document.body.appendChild(a);
                a.click();
                setTimeout(function () { URL.revokeObjectURL(url); a.remove(); }, 1000);
            });
    };
})();
