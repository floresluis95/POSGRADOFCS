/**
 * Orden de Pago de Módulos
 * Pestaña "Orden de Pago" del modal unificado de Gestión de Pagos del Programa (Matriculados).
 * Se emite ANTES de pagar: el estudiante selecciona módulos pendientes, se genera un
 * comprobante (PDF) con el monto a cancelar, y recién después se registra el pago (con
 * voucher) en la pestaña "Pago de Módulos". Al registrarse ese pago, la orden pendiente
 * correspondiente se marca automáticamente como confirmada (ver modulopagos_core.php).
 *
 * El PDF usa el MISMO formato oficial (UTO - Coordinación de Posgrado) que la orden de
 * pago de matrícula/preregistro: se genera enviando los datos por POST a
 * vistas/componentes/generar-orden-pago-pdf.php (igual que hace ordenpago.js).
 */

$(document).ready(function() {

    // idInscripcion, estudianteID, estudianteNombre, estudianteCi, programaID,
    // programaNombre, version, numeroTramite
    let opContexto = {};

    // Ordenes pendientes cargadas actualmente, indexadas por IdOrdenPago (para reimprimir)
    let opOrdenesPorId = {};

    // Total de módulos ACTIVOS del programa actual (pagados + pendientes), para mostrar
    // en el PDF la cuota como "IV/XII" (módulo IV de XII en total)
    let opTotalModulos = 0;

    // ========================================
    // CARGAR PESTAÑA: módulos pendientes de esta inscripción + órdenes ya emitidas
    // Invocado desde inscripcionmodulo.js al abrir el modal unificado de Gestión de Pagos
    // ========================================
    window.cargarOrdenPagoModulos = function(datos) {
        opContexto = datos;

        $('#ordenPagoContenido').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>');

        $.when(
            $.ajax({
                url: 'ajax/modulo.ajax.php',
                method: 'POST',
                data: { programaID: opContexto.programaID, idinscripcion: opContexto.idInscripcion },
                dataType: 'json'
            }),
            $.ajax({
                url: 'ajax/ordenpago.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerOrdenesModulosPendientes', idinscripcion: opContexto.idInscripcion },
                dataType: 'json'
            })
        ).done(function(respModulos, respOrdenes) {
            const todosLosModulos = respModulos[0] || [];
            const pendientes = todosLosModulos.filter(function(m) { return parseInt(m.Pagado) !== 1; });
            opTotalModulos = todosLosModulos.length;

            const ordenesResp = respOrdenes[0];
            const ordenes = (ordenesResp && ordenesResp.success) ? (ordenesResp.ordenes || []) : [];

            opOrdenesPorId = {};
            ordenes.forEach(function(orden) { opOrdenesPorId[orden.IdOrdenPago] = orden; });

            // Mapa ModuloID -> orden pendiente que ya lo incluye
            const ordenPorModulo = {};
            ordenes.forEach(function(orden) {
                (orden.ListaModulos || '').split(',').forEach(function(idMod) {
                    idMod = idMod.trim();
                    if (idMod) ordenPorModulo[idMod] = orden;
                });
            });

            renderOrdenPago(pendientes, ordenPorModulo);
        }).fail(function() {
            $('#ordenPagoContenido').html('<div class="alert alert-danger">No se pudieron cargar los módulos pendientes</div>');
        });
    };

    function renderOrdenPago(pendientes, ordenPorModulo) {
        if (pendientes.length === 0) {
            $('#ordenPagoContenido').html(
                '<div class="alert alert-success text-center">' +
                    '<i class="fa fa-check-circle fa-2x mb-2"></i>' +
                    '<h6>Sin módulos pendientes</h6>' +
                    '<p class="mb-0">Este estudiante no tiene módulos pendientes de pago en este programa.</p>' +
                '</div>'
            );
            return;
        }

        let filas = '';
        pendientes.forEach(function(m) {
            const orden = ordenPorModulo[String(m.ModuloID)];
            const costo = parseFloat(m.Costo || 0);

            filas += '<tr>' +
                '<td class="text-center">' +
                    (orden
                        ? '<i class="fa fa-clock-o text-muted" title="Ya tiene una orden de pago pendiente"></i>'
                        : '<input type="checkbox" class="op-check" value="' + m.ModuloID + '" data-codigo="' + m.Codigo + '" data-nombre="' + (m.NombreModulo || '').replace(/"/g, '&quot;') + '" data-costo="' + costo + '">') +
                '</td>' +
                '<td><span class="badge badge-primary">' + (m.Codigo || '') + '</span> ' + (m.NombreModulo || '') + '</td>' +
                '<td class="text-center">Bs. ' + costo.toFixed(2) + '</td>' +
                '<td class="text-center">' +
                    (orden
                        ? '<span class="badge badge-warning" title="Generada el ' + orden.FechaGeneracion + '">N° ' + orden.NumeroOrden + '</span> ' +
                          '<button type="button" class="btn btn-xs btn-outline-secondary btn-reimprimir-orden-pendiente" data-id="' + orden.IdOrdenPago + '"><i class="fa fa-print"></i></button>'
                        : '<span class="text-muted">Sin orden</span>') +
                '</td>' +
            '</tr>';
        });

        const html =
            '<div class="table-responsive mb-3">' +
                '<table class="table table-sm table-bordered table-hover mb-0">' +
                    '<thead class="thead-light">' +
                        '<tr>' +
                            '<th class="text-center" style="width:36px;"></th>' +
                            '<th>Módulo</th>' +
                            '<th class="text-center">Costo</th>' +
                            '<th class="text-center">Orden</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody id="opListaModulos">' + filas + '</tbody>' +
                '</table>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Nombre / Razón Social (Factura)</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNombreFactura" value="' + (opContexto.estudianteNombre || '') + '">' +
                '</div>' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">NIT / C.I.</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNitCiFactura" value="' + (opContexto.estudianteCi || '') + '">' +
                '</div>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Responsable *</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opResponsable" placeholder="Nombre de quien genera la orden">' +
                '</div>' +
                '<div class="col-lg-6 text-right">' +
                    '<label class="small font-weight-bold mb-1 d-block">&nbsp;</label>' +
                    'Total a pagar: <strong>Bs. <span id="opTotalSeleccionado">0.00</span></strong>' +
                '</div>' +
            '</div>' +
            '<div class="text-right mt-2">' +
                '<button type="button" class="btn btn-sm btn-success" id="opBtnGenerarOrden" disabled>' +
                    '<i class="fa fa-file-invoice"></i> Generar Orden de Pago' +
                '</button>' +
            '</div>';

        $('#ordenPagoContenido').html(html);
        actualizarTotalOP();
    }

    $(document).on('change', '.op-check', actualizarTotalOP);

    function actualizarTotalOP() {
        const seleccionados = $('.op-check:checked');
        let total = 0;
        seleccionados.each(function() {
            total += parseFloat($(this).data('costo')) || 0;
        });
        $('#opTotalSeleccionado').text(total.toFixed(2));
        $('#opBtnGenerarOrden').prop('disabled', seleccionados.length === 0);
    }

    $(document).on('click', '#opBtnGenerarOrden', function() {
        const seleccionados = $('.op-check:checked');
        const responsable = ($('#opResponsable').val() || '').trim();
        const nombreFactura = ($('#opNombreFactura').val() || '').trim();
        const nitCiFactura = ($('#opNitCiFactura').val() || '').trim();

        if (seleccionados.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione al menos un módulo pendiente' });
            return;
        }
        if (!responsable) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Indique el responsable que genera la orden' });
            return;
        }

        const listaModulos = seleccionados.map(function() { return $(this).val(); }).get().join(',');

        let montoTotal = 0;
        const detalleModulos = [];
        seleccionados.each(function() {
            const costo = parseFloat($(this).data('costo')) || 0;
            montoTotal += costo;
            detalleModulos.push({
                ModuloID: $(this).val(),
                Codigo: $(this).data('codigo'),
                Nombre: $(this).data('nombre'),
                Costo: costo
            });
        });

        const $btn = $('#opBtnGenerarOrden');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarOrdenPagoModulosPendientes',
                estudianteID: opContexto.estudianteID,
                idinscripcion: opContexto.idInscripcion,
                programaID: opContexto.programaID,
                listaModulos: listaModulos,
                detalleModulos: JSON.stringify(detalleModulos),
                montoTotal: montoTotal,
                responsable: responsable,
                nombreFactura: nombreFactura,
                nitCiFactura: nitCiFactura
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Orden generada', text: 'N° ' + resp.numeroOrden, timer: 1800, showConfirmButton: false });
                    imprimirOrdenPagoOficial({
                        estudianteID: opContexto.estudianteID,
                        programaNombre: opContexto.programaNombre,
                        version: opContexto.version,
                        numeroTramite: opContexto.numeroTramite,
                        montoTotal: montoTotal,
                        nombreFactura: nombreFactura,
                        nitCiFactura: nitCiFactura,
                        detalleModulos: detalleModulos,
                        totalModulos: opTotalModulos
                    });
                    window.cargarOrdenPagoModulos(opContexto);
                    // El módulo recién ordenado ya puede seleccionarse en "Pago de Módulos"
                    if (typeof window.cargarModulosDisponibles === 'function') {
                        window.cargarModulosDisponibles(opContexto.programaID);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden de pago' });
                    $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar Orden de Pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar la orden de pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar Orden de Pago');
            }
        });
    });

    $(document).on('click', '.btn-reimprimir-orden-pendiente', function() {
        const idOrdenPago = $(this).data('id');
        const orden = opOrdenesPorId[idOrdenPago];
        if (!orden) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontraron los datos de la orden' });
            return;
        }

        let detalleModulos = [];
        try {
            const detalleJson = JSON.parse(orden.Observaciones || '[]');
            if (Array.isArray(detalleJson)) detalleModulos = detalleJson;
        } catch (e) {
            detalleModulos = [];
        }

        imprimirOrdenPagoOficial({
            estudianteID: opContexto.estudianteID,
            programaNombre: opContexto.programaNombre,
            version: opContexto.version,
            numeroTramite: opContexto.numeroTramite,
            montoTotal: orden.MontoTotal,
            nombreFactura: orden.NombreFactura,
            nitCiFactura: orden.NitCiFactura,
            detalleModulos: detalleModulos,
            totalModulos: opTotalModulos
        });
    });

    /**
     * Genera el PDF de la orden de pago de módulos con el mismo formato oficial
     * (UTO - Coordinación de Posgrado, doble copia) que la orden de pago de matrícula:
     * envía los datos por POST a generar-orden-pago-pdf.php, igual que ordenpago.js.
     * @param {Object} params estudianteID, programaNombre, version, numeroTramite,
     *        montoTotal, nombreFactura, nitCiFactura, totalModulos,
     *        detalleModulos (array {Codigo, Nombre})
     */
    function imprimirOrdenPagoOficial(params) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'obtenerDatosEstudiante', estudianteID: params.estudianteID },
            success: function(resp) {
                const est = (resp && resp.success && resp.estudiante) ? resp.estudiante : {};

                let ciCompleto = est.Ci || '';
                if (est.Complemento) ciCompleto += '-' + est.Complemento;
                if (est.Exp) ciCompleto += ' ' + est.Exp;

                // Cada módulo se identifica por su cuota (posición/total del programa),
                // p.ej. "CUOTA IV/XII", en vez de "código - nombre" (que suelen ser
                // iguales, como "MODULO IV - MODULO IV").
                const listaModulosTexto = formatoModulosParaPdf(params.detalleModulos, params.totalModulos);

                const montoTotal = parseFloat(params.montoTotal) || 0;

                const datos = {
                    apaterno: est.Apaterno || '',
                    amaterno: est.Amaterno || '',
                    nombres: est.Nombre || '',
                    correo: est.Correo || '',
                    ci: ciCompleto,
                    celular: est.Celular || '',
                    programa: (params.programaNombre || '') + (listaModulosTexto ? ('\nMódulos: ' + listaModulosTexto) : ''),
                    montoNumeral: 'Bs. ' + montoTotal.toFixed(2),
                    montoLiteral: numeroALetras(montoTotal),
                    version: params.version || 'V-1',
                    numeroTramite: params.numeroTramite || '',
                    nombreFactura: params.nombreFactura || '',
                    nitCiFactura: params.nitCiFactura || ''
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'vistas/componentes/generar-orden-pago-pdf.php';
                form.target = '_blank';

                for (const key in datos) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = datos[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener los datos del estudiante para la orden de pago' });
            }
        });
    }

    /**
     * Texto de módulos para el PDF, identificando cada uno por su cuota (posición/total
     * del programa), p.ej. "CUOTA IV/XII", en vez de "código - nombre" (que en la mayoría
     * de los módulos son iguales, ej. "MODULO IV - MODULO IV", y salían repetidos).
     * @param {Array} detalleModulos [{Codigo, Nombre}, ...]
     * @param {number} totalModulos total de módulos activos del programa
     * @returns {string}
     */
    function formatoModulosParaPdf(detalleModulos, totalModulos) {
        const totalRomano = toRoman(totalModulos);
        return (detalleModulos || []).map(function(m) {
            const codigo = m.Codigo || '';
            const nombre = m.Nombre || '';
            const romanPos = romanDeCodigo(codigo);
            const cuota = 'CUOTA ' + (romanPos || codigo) + (totalRomano ? ('/' + totalRomano) : '');
            return (nombre && nombre !== codigo) ? (cuota + ' - ' + nombre) : cuota;
        }).join('; ');
    }

    /**
     * Extrae el número romano de un código de módulo con formato "MODULO <romano>"
     * (ej. "MODULO IV" -> "IV"). Si no calza el formato, devuelve el código tal cual.
     */
    function romanDeCodigo(codigo) {
        const m = /^MODULO\s+([IVXLCDM]+)$/i.exec((codigo || '').trim());
        return m ? m[1].toUpperCase() : '';
    }

    /**
     * Convertir un entero a número romano (1..3999).
     */
    function toRoman(num) {
        num = parseInt(num, 10);
        if (!num || num <= 0) return '';
        const valores = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1];
        const simbolos = ['M', 'CM', 'D', 'CD', 'C', 'XC', 'L', 'XL', 'X', 'IX', 'V', 'IV', 'I'];
        let resultado = '';
        for (let i = 0; i < valores.length; i++) {
            while (num >= valores[i]) {
                resultado += simbolos[i];
                num -= valores[i];
            }
        }
        return resultado;
    }

    /**
     * Convertir número a letras (español - bolivianos). Copia de la función usada en
     * ordenpago.js (no está disponible en esta vista porque ordenpago.js no se carga aquí).
     */
    function numeroALetras(numero) {
        const unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        const decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        const especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        const centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        function convertirGrupo(n) {
            if (n === 0) return '';
            if (n === 100) return 'CIEN';

            let output = '';

            if (n >= 100) {
                output += centenas[Math.floor(n / 100)] + ' ';
                n %= 100;
            }

            if (n >= 10 && n < 20) {
                output += especiales[n - 10];
            } else {
                if (n >= 20) {
                    output += decenas[Math.floor(n / 10)];
                    n %= 10;
                    if (n > 0) output += ' Y ';
                }
                if (n > 0 && n < 10) {
                    output += unidades[n];
                }
            }

            return output.trim();
        }

        if (numero === 0) return 'CERO BOLIVIANOS CON 00/100';

        const partes = numero.toFixed(2).split('.');
        const entero = parseInt(partes[0]);
        const centavos = partes[1];

        let resultado = '';

        if (entero >= 1000000) {
            const millones = Math.floor(entero / 1000000);
            if (millones === 1) {
                resultado += 'UN MILLÓN ';
            } else {
                resultado += convertirGrupo(millones) + ' MILLONES ';
            }
        }

        const miles = Math.floor((entero % 1000000) / 1000);
        if (miles > 0) {
            if (miles === 1) {
                resultado += 'MIL ';
            } else {
                resultado += convertirGrupo(miles) + ' MIL ';
            }
        }

        const unidadesNum = entero % 1000;
        if (unidadesNum > 0) {
            resultado += convertirGrupo(unidadesNum) + ' ';
        }

        resultado += 'BOLIVIANOS CON ' + centavos + '/100';

        return resultado.trim();
    }

});
