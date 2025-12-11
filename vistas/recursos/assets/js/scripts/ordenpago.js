/**
 * Script para Orden de Pago - Búsqueda de Estudiantes
 */

console.log('=== Script Orden de Pago Cargado ===');

$(document).ready(function() {
    // Inicializar Select2 con búsqueda forzada
    $('.select2-estudiantes').select2({
        placeholder: 'Escriba para buscar estudiante por nombre o CI...',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0, // IMPORTANTE: Fuerza a mostrar el buscador siempre
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function() {
                return "Escriba para buscar";
            }
        }
    });

    // Cargar estudiantes al iniciar
    cargarEstudiantes();

    // Event listener para cambio en el select
    $('#select_estudiante').on('change', function() {
        const estudianteID = $(this).val();

        if (estudianteID) {
            cargarDatosEstudiante(estudianteID);
        } else {
            limpiarFormulario();
        }
    });

    // Botón limpiar
    $('#btn_limpiar_busqueda').on('click', function() {
        limpiarFormulario();
    });

    // Event listeners para botones de la tabla (delegados)
    $(document).on('click', '.btn-adicionar-datos', function() {
        const pagoID = $(this).data('pago-id');
        const moduloNombre = $(this).data('modulo-nombre');
        const programaNombre = $(this).data('programa-nombre');
        const monto = $(this).data('monto');
        const version = $(this).data('version');
        const numeroTramite = $(this).data('numero-tramite');
        abrirModalAdicionarDatos(pagoID, moduloNombre, programaNombre, monto, version, numeroTramite);
    });

    $(document).on('click', '.btn-imprimir-orden', function() {
        const pagoID = $(this).data('pago-id');
        const moduloNombre = $(this).data('modulo-nombre');
        imprimirOrdenPago(pagoID, moduloNombre);
    });

    // Botón imprimir PDF del modal
    $('#btn_imprimir_pdf').on('click', function() {
        generarPDFOrdenPago();
    });
});

/**
 * Cargar lista de estudiantes en el select
 */
function cargarEstudiantes() {
    console.log('Cargando lista de estudiantes...');

    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'cargarEstudiantes'
        },
        success: function(response) {
            console.log('Estudiantes cargados:', response);

            if (response.success && response.estudiantes) {
                const select = $('#select_estudiante');
                select.html('<option value="">-- Seleccione un estudiante --</option>');

                response.estudiantes.forEach(function(est) {
                    const nombreCompleto = `${est.Apaterno} ${est.Amaterno || ''} ${est.Nombre}`.trim();
                    let ci = est.Ci;
                    if (est.Complemento) ci += '-' + est.Complemento;
                    if (est.Exp) ci += ' ' + est.Exp;

                    const texto = `${nombreCompleto} - CI: ${ci}`;
                    select.append(new Option(texto, est.EstudianteID));
                });

                // Reinicializar Select2
                select.trigger('change.select2');
            } else {
                swal({
                    title: 'Error',
                    text: 'No se pudieron cargar los estudiantes',
                    icon: 'error',
                    button: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar estudiantes:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudo cargar la lista de estudiantes',
                icon: 'error',
                button: 'Aceptar'
            });
        }
    });
}

/**
 * Cargar datos del estudiante seleccionado
 */
function cargarDatosEstudiante(estudianteID) {
    console.log('Cargando datos del estudiante ID:', estudianteID);

    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerDatosEstudiante',
            estudianteID: estudianteID
        },
        success: function(response) {
            console.log('Datos del estudiante:', response);

            if (response.success && response.estudiante) {
                mostrarDatosEstudiante(response.estudiante);
                cargarDetallePagos(estudianteID);
            } else {
                swal({
                    title: 'Error',
                    text: response.mensaje || 'No se pudieron cargar los datos',
                    icon: 'error',
                    button: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar datos:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudieron cargar los datos del estudiante',
                icon: 'error',
                button: 'Aceptar'
            });
        }
    });
}

/**
 * Mostrar datos básicos del estudiante
 */
function mostrarDatosEstudiante(estudiante) {
    console.log('Mostrando datos del estudiante:', estudiante);

    // Ocultar mensaje inicial y mostrar área de datos
    $('#mensaje_inicial').fadeOut();
    $('#area_datos_estudiante').fadeIn();

    // Nombre en el header
    const nombreCompleto = `${estudiante.Nombre} ${estudiante.Apaterno} ${estudiante.Amaterno || ''}`.trim();
    $('#nombre_estudiante_header').text(nombreCompleto);

    // CI con complemento y expedido
    let ciCompleto = estudiante.Ci;
    if (estudiante.Complemento) {
        ciCompleto += '-' + estudiante.Complemento;
    }
    if (estudiante.Exp) {
        ciCompleto += ' ' + estudiante.Exp;
    }
    $('#dato_ci').text(ciCompleto);

    // Otros datos
    $('#dato_correo').text(estudiante.Correo || '-');
    $('#dato_celular').text(estudiante.Celular || '-');
    $('#dato_profesion').text(estudiante.NombreProfesion || '-');
}

/**
 * Cargar detalle de pagos del estudiante
 */
function cargarDetallePagos(estudianteID) {
    console.log('Cargando detalle de pagos del estudiante ID:', estudianteID);

    $('#contenido_tabla_pagos').html(`
        <div class="text-center p-4">
            <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
            <p class="mt-3" style="color: #667eea; font-weight: 600;">Cargando detalle de pagos...</p>
        </div>
    `);

    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerModulosPagados',
            estudianteID: estudianteID
        },
        success: function(response) {
            console.log('Detalle de pagos:', response);

            if (response.success && response.modulos && response.modulos.length > 0) {
                mostrarTablaPagos(response.modulos);
            } else {
                $('#contenido_tabla_pagos').html(`
                    <div class="alert alert-warning text-center">
                        <i class="fa fa-exclamation-triangle fa-2x"></i>
                        <h5 class="mt-3">No hay pagos registrados</h5>
                        <p>Este estudiante no tiene módulos pagados registrados en el sistema</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar pagos:', error);
            $('#contenido_tabla_pagos').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle fa-2x"></i>
                    <h5 class="mt-3">Error al cargar pagos</h5>
                    <p>No se pudo obtener el detalle de pagos. Intente nuevamente.</p>
                </div>
            `);
        }
    });
}

/**
 * Mostrar tabla de detalle de pagos agrupada por programa
 */
function mostrarTablaPagos(modulos) {
    // Agrupar módulos por programa
    const programas = {};
    modulos.forEach(modulo => {
        const key = modulo.ProgramaID;
        if (!programas[key]) {
            programas[key] = {
                programaID: modulo.ProgramaID,
                nombrePrograma: modulo.NombrePrograma,
                gradoAcademico: modulo.GradoAcademico,
                codigoPrograma: modulo.CodigoPrograma,
                version: modulo.Version,
                numeroTramite: modulo.NumeroTramite,
                idinscripcion: modulo.idinscripcion,
                modulos: []
            };
        }
        programas[key].modulos.push(modulo);
    });

    let html = '';

    // Generar HTML para cada programa
    Object.values(programas).forEach(programa => {
        let totalPrograma = 0;
        let modulosConOrden = 0;
        let modulosSinOrden = 0;

        html += `
            <div class="programa-card" style="margin-bottom: 2rem; border-left: 4px solid #667eea; background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 style="color: #464E5F; margin: 0; font-weight: 700;">
                            <span class="badge-programa" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 15px; font-size: 11px;">
                                ${programa.codigoPrograma}
                            </span>
                            ${programa.gradoAcademico}
                        </h4>
                        <p style="color: #B5B5C3; margin: 0.5rem 0 0 0;">${programa.nombrePrograma}</p>
                        <small style="color: #999;">
                            <i class="fa fa-info-circle"></i> Versión: ${programa.version || 'N/A'} |
                            N° Trámite: ${programa.numeroTramite || 'N/A'}
                        </small>
                    </div>
                    <div class="text-right">
                        <button class="btn btn-success btn-generar-orden-multiple"
                                data-programa-id="${programa.programaID}"
                                data-idinscripcion="${programa.idinscripcion}"
                                data-programa-nombre="${programa.gradoAcademico} - ${programa.nombrePrograma}"
                                data-version="${programa.version || ''}"
                                data-numero-tramite="${programa.numeroTramite || ''}"
                                style="border-radius: 20px; padding: 8px 20px;">
                            <i class="fa fa-file-invoice"></i> Generar Orden de Pago
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="margin-bottom: 0;">
                        <thead style="background: #f8f9fa; color: #464E5F;">
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" class="check-all-programa" data-programa-id="${programa.programaID}">
                                </th>
                                <th style="width: 50px;">#</th>
                                <th style="width: 100px;">CÓDIGO</th>
                                <th>MÓDULO</th>
                                <th style="width: 120px; text-align: right;">COSTO</th>
                                <th style="width: 110px; text-align: center;">FECHA PAGO</th>
                                <th style="width: 100px; text-align: center;">ESTADO</th>
                                <th style="width: 180px; text-align: center;">ORDEN GENERADA</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        programa.modulos.forEach((modulo, index) => {
            const costo = parseFloat(modulo.costomodulo || 0);
            totalPrograma += costo;

            const tieneOrden = modulo.OrdenPagoID != null;
            if (tieneOrden) {
                modulosConOrden++;
            } else {
                modulosSinOrden++;
            }

            const estadoBadge = modulo.Estado === 'PAGADO'
                ? '<span class="badge-pagado"><i class="fa fa-check-circle"></i> PAGADO</span>'
                : '<span class="badge-pendiente"><i class="fa fa-clock"></i> PENDIENTE</span>';

            const fechaPago = modulo.fechapago ? formatearFecha(modulo.fechapago) : '-';

            const ordenInfo = tieneOrden
                ? `<span class="badge badge-success" style="font-size: 10px;">
                       <i class="fa fa-check"></i> ${modulo.NumeroOrden}
                   </span><br>
                   <small style="font-size: 9px; color: #666;">
                       ${formatearFecha(modulo.FechaOrdenGenerada)}
                   </small>`
                : '<span class="badge badge-warning" style="font-size: 10px;"><i class="fa fa-exclamation-triangle"></i> Sin orden</span>';

            const checkboxDisabled = tieneOrden ? 'disabled' : '';
            const rowStyle = tieneOrden ? 'background: #f0f9ff;' : '';

            html += `
                <tr style="${rowStyle}">
                    <td class="text-center">
                        <input type="checkbox"
                               class="check-modulo"
                               data-programa-id="${programa.programaID}"
                               data-pago-id="${modulo.Idpagomodulo}"
                               data-modulo-nombre="${modulo.nombremodulo}"
                               data-costo="${costo}"
                               ${checkboxDisabled}>
                    </td>
                    <td class="text-center">${index + 1}</td>
                    <td><span style="background: #667eea; color: white; padding: 4px 8px; border-radius: 10px; font-size: 10px;">${modulo.codigomodulo}</span></td>
                    <td style="font-weight: 500;">${modulo.nombremodulo}</td>
                    <td class="text-right" style="font-weight: 700; color: #11998e;">Bs. ${costo.toFixed(2)}</td>
                    <td class="text-center">${fechaPago}</td>
                    <td class="text-center">${estadoBadge}</td>
                    <td class="text-center">${ordenInfo}</td>
                </tr>
            `;
        });

        html += `
                        </tbody>
                        <tfoot style="background: #f8f9fa;">
                            <tr>
                                <td colspan="4" class="text-right" style="padding: 0.75rem; font-weight: 700;">TOTAL:</td>
                                <td class="text-right" style="padding: 0.75rem; font-weight: 700; color: #11998e; font-size: 18px;">
                                    Bs. ${totalPrograma.toFixed(2)}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-2" style="background: #f8f9fa; padding: 0.75rem; border-radius: 5px;">
                    <small>
                        <i class="fa fa-info-circle text-info"></i>
                        <strong>${modulosConOrden}</strong> módulo(s) con orden generada |
                        <strong>${modulosSinOrden}</strong> módulo(s) sin orden
                    </small>
                </div>
            </div>
        `;
    });

    $('#contenido_tabla_pagos').html(html);

    // Agregar event listeners para checkboxes
    agregarEventListenersCheckboxes();
}

/**
 * Agregar event listeners para checkboxes
 */
function agregarEventListenersCheckboxes() {
    // Checkbox "Seleccionar Todos" por programa
    $(document).on('change', '.check-all-programa', function() {
        const programaID = $(this).data('programa-id');
        const isChecked = $(this).prop('checked');

        $(`.check-modulo[data-programa-id="${programaID}"]:not(:disabled)`).prop('checked', isChecked);
    });

    // Verificar si todos están seleccionados cuando se marca uno individual
    $(document).on('change', '.check-modulo', function() {
        const programaID = $(this).data('programa-id');
        const totalCheckboxes = $(`.check-modulo[data-programa-id="${programaID}"]:not(:disabled)`).length;
        const checkedCheckboxes = $(`.check-modulo[data-programa-id="${programaID}"]:checked`).length;

        $(`.check-all-programa[data-programa-id="${programaID}"]`).prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Botón generar orden múltiple
    $(document).on('click', '.btn-generar-orden-multiple', function() {
        const programaID = $(this).data('programa-id');
        const idinscripcion = $(this).data('idinscripcion');
        const programaNombre = $(this).data('programa-nombre');
        const version = $(this).data('version');
        const numeroTramite = $(this).data('numero-tramite');

        // Obtener módulos seleccionados
        const modulosSeleccionados = [];
        let montoTotal = 0;

        $(`.check-modulo[data-programa-id="${programaID}"]:checked`).each(function() {
            const pagoID = $(this).data('pago-id');
            const moduloNombre = $(this).data('modulo-nombre');
            const costo = parseFloat($(this).data('costo'));

            modulosSeleccionados.push({
                Idpagomodulo: pagoID,
                nombremodulo: moduloNombre,
                costo: costo
            });

            montoTotal += costo;
        });

        if (modulosSeleccionados.length === 0) {
            swal({
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un módulo para generar la orden de pago',
                icon: 'warning',
                button: 'Aceptar'
            });
            return;
        }

        // Abrir modal con datos de múltiples módulos
        abrirModalOrdenMultiple(programaID, idinscripcion, programaNombre, version, numeroTramite, modulosSeleccionados, montoTotal);
    });
}

/**
 * Abrir modal para orden de pago con múltiples módulos
 */
function abrirModalOrdenMultiple(programaID, idinscripcion, programaNombre, version, numeroTramite, modulos, montoTotal) {
    console.log('Generar orden para', modulos.length, 'módulos');

    // Limpiar formulario
    $('#form_adicionar_datos')[0].reset();

    // Establecer datos en el modal
    $('#modal_programa_nombre').text(programaNombre);
    $('#version').val(version || 'V-1');
    $('#numero_tramite').val(numeroTramite || '');

    // Crear lista de módulos
    let listaModulos = '<ul style="margin: 0; padding-left: 20px;">';
    modulos.forEach(modulo => {
        listaModulos += `<li>${modulo.nombremodulo} - Bs. ${modulo.costo.toFixed(2)}</li>`;
    });
    listaModulos += '</ul>';
    $('#modal_modulo_nombre').html(listaModulos);

    // Monto total
    $('#modal_monto_numeral').text('Bs. ' + montoTotal.toFixed(2));
    $('#modal_monto_literal').text(numeroALetras(montoTotal));

    // Obtener datos del estudiante
    const estudianteID = $('#select_estudiante').val();

    if (estudianteID) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'obtenerDatosEstudiante',
                estudianteID: estudianteID
            },
            success: function(response) {
                if (response.success && response.estudiante) {
                    const est = response.estudiante;

                    $('#modal_estudiante_apaterno').text(est.Apaterno || '-');
                    $('#modal_estudiante_amaterno').text(est.Amaterno || '-');
                    $('#modal_estudiante_nombres').text(est.Nombre || '-');
                    $('#modal_estudiante_correo').text(est.Correo || '-');

                    let ciCompleto = est.Ci;
                    if (est.Complemento) ciCompleto += '-' + est.Complemento;
                    if (est.Exp) ciCompleto += ' ' + est.Exp;
                    $('#modal_estudiante_ci').text(ciCompleto);

                    $('#modal_estudiante_celular').text(est.Celular || '-');
                }
            }
        });
    }

    // Guardar datos para el PDF
    $('#modal_pago_id').val(JSON.stringify({
        estudianteID: estudianteID,
        programaID: programaID,
        idinscripcion: idinscripcion,
        modulos: modulos,
        montoTotal: montoTotal
    }));

    // Abrir modal
    $('#modalAdicionarDatos').modal('show');
}

/**
 * Limpiar formulario y resultados
 */
function limpiarFormulario() {
    $('#select_estudiante').val('').trigger('change');
    $('#area_datos_estudiante').fadeOut();
    $('#mensaje_inicial').fadeIn();

    // Limpiar campos
    $('#nombre_estudiante_header').text('Estudiante');
    $('#dato_ci, #dato_correo, #dato_celular, #dato_profesion').text('-');
    $('#contenido_tabla_pagos').html('');
}

/**
 * Formatear fecha de YYYY-MM-DD a DD/MM/YYYY
 */
function formatearFecha(fecha) {
    if (!fecha) return '-';

    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();

    return `${dia}/${mes}/${anio}`;
}

/**
 * Convertir número a letras (español - bolivianos)
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

        // Centenas
        if (n >= 100) {
            output += centenas[Math.floor(n / 100)] + ' ';
            n %= 100;
        }

        // Decenas y unidades
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

    // Millones
    if (entero >= 1000000) {
        const millones = Math.floor(entero / 1000000);
        if (millones === 1) {
            resultado += 'UN MILLÓN ';
        } else {
            resultado += convertirGrupo(millones) + ' MILLONES ';
        }
    }

    // Miles
    const miles = Math.floor((entero % 1000000) / 1000);
    if (miles > 0) {
        if (miles === 1) {
            resultado += 'MIL ';
        } else {
            resultado += convertirGrupo(miles) + ' MIL ';
        }
    }

    // Unidades
    const unidadesNum = entero % 1000;
    if (unidadesNum > 0) {
        resultado += convertirGrupo(unidadesNum) + ' ';
    }

    resultado += 'BOLIVIANOS CON ' + centavos + '/100';

    return resultado.trim();
}

/**
 * Abrir modal para adicionar datos
 */
function abrirModalAdicionarDatos(pagoID, moduloNombre, programaNombre, monto, version, numeroTramite) {
    console.log('Abrir modal adicionar datos - Pago ID:', pagoID, 'Módulo:', moduloNombre, 'Programa:', programaNombre, 'Monto:', monto, 'Version:', version, 'Numero Tramite:', numeroTramite);

    // Limpiar formulario
    $('#form_adicionar_datos')[0].reset();

    // Establecer datos en el modal
    $('#modal_pago_id').val(pagoID);
    $('#modal_modulo_nombre').text(moduloNombre);
    $('#modal_programa_nombre').text(programaNombre);

    // Establecer Version y Numero de Tramite
    $('#version').val(version || 'V-1');
    $('#numero_tramite').val(numeroTramite || '');

    // Monto en numeral
    const montoNumeral = parseFloat(monto || 0);
    $('#modal_monto_numeral').text('Bs. ' + montoNumeral.toFixed(2));

    // Monto en literal
    const montoLiteral = numeroALetras(montoNumeral);
    $('#modal_monto_literal').text(montoLiteral);

    // Obtener datos del estudiante seleccionado
    const estudianteID = $('#select_estudiante').val();

    if (estudianteID) {
        // Cargar datos del estudiante en el modal
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'obtenerDatosEstudiante',
                estudianteID: estudianteID
            },
            success: function(response) {
                if (response.success && response.estudiante) {
                    const est = response.estudiante;

                    // Llenar datos del estudiante en el modal
                    $('#modal_estudiante_apaterno').text(est.Apaterno || '-');
                    $('#modal_estudiante_amaterno').text(est.Amaterno || '-');
                    $('#modal_estudiante_nombres').text(est.Nombre || '-');
                    $('#modal_estudiante_correo').text(est.Correo || '-');

                    // CI con complemento y expedido
                    let ciCompleto = est.Ci;
                    if (est.Complemento) ciCompleto += '-' + est.Complemento;
                    if (est.Exp) ciCompleto += ' ' + est.Exp;
                    $('#modal_estudiante_ci').text(ciCompleto);

                    $('#modal_estudiante_celular').text(est.Celular || '-');
                }
            }
        });
    }

    // Abrir el modal
    $('#modalAdicionarDatos').modal('show');
}

/**
 * Imprimir orden de pago
 */
function imprimirOrdenPago(pagoID, moduloNombre) {
    console.log('Imprimir orden de pago - Pago ID:', pagoID, 'Módulo:', moduloNombre);

    // Abrir ventana de impresión
    const urlImpresion = `vistas/componentes/imprimir-orden-pago.php?id=${pagoID}`;
    window.open(urlImpresion, '_blank', 'width=800,height=600');
}

/**
 * Generar PDF de Orden de Pago
 */
function generarPDFOrdenPago() {
    console.log('=== GENERAR PDF ORDEN DE PAGO ===');

    // Validar campos obligatorios
    const version = $('#version').val();
    const numeroTramite = $('#numero_tramite').val();
    const nombreFactura = $('#nombre_factura').val();
    const nitCiFactura = $('#nit_ci_factura').val();
    const responsable = $('#responsable').val();

    console.log('Validación de campos:', {
        version: version,
        numeroTramite: numeroTramite,
        nombreFactura: nombreFactura,
        nitCiFactura: nitCiFactura,
        responsable: responsable
    });

    if (!version || !numeroTramite || !nombreFactura || !nitCiFactura || !responsable) {
        console.warn('Campos incompletos detectados');
        swal({
            title: 'Campos incompletos',
            text: 'Por favor, complete todos los campos obligatorios marcados con *',
            icon: 'warning',
            button: 'Aceptar'
        });
        return;
    }

    // Verificar si es orden múltiple o simple
    const pagoIdValue = $('#modal_pago_id').val();
    console.log('Valor de modal_pago_id:', pagoIdValue);

    let esOrdenMultiple = false;
    let datosOrdenMultiple = null;

    try {
        datosOrdenMultiple = JSON.parse(pagoIdValue);
        esOrdenMultiple = datosOrdenMultiple && datosOrdenMultiple.modulos && datosOrdenMultiple.modulos.length > 0;
        console.log('Es orden múltiple:', esOrdenMultiple);
        console.log('Datos parseados:', datosOrdenMultiple);
    } catch (e) {
        // Es un ID simple (no JSON)
        esOrdenMultiple = false;
        console.log('Es orden simple (no JSON)');
    }

    if (esOrdenMultiple) {
        console.log('Generando orden de pago múltiple...');
        // Generar orden de pago múltiple
        generarOrdenMultiple(datosOrdenMultiple, version, numeroTramite, nombreFactura, nitCiFactura, responsable);
    } else {
        console.log('Generando orden de pago simple...');
        // Generar orden de pago simple (un solo módulo)
        generarOrdenSimple(pagoIdValue, version, numeroTramite, nombreFactura, nitCiFactura, responsable);
    }
}

/**
 * Generar orden de pago con múltiples módulos
 */
function generarOrdenMultiple(datosOrden, version, numeroTramite, nombreFactura, nitCiFactura, responsable) {
    console.log('=== GENERAR ORDEN MÚLTIPLE ===');
    console.log('Datos de la orden:', datosOrden);

    // Preparar lista de IDs de pagos módulo
    const listaPagosModulo = datosOrden.modulos.map(m => m.Idpagomodulo).join(',');
    console.log('Lista de IDs de pago módulo:', listaPagosModulo);

    console.log('Enviando AJAX para registrar orden...');

    // Primero registrar la orden en la base de datos
    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'registrarOrdenPago',
            estudianteID: datosOrden.estudianteID,
            idinscripcion: datosOrden.idinscripcion,
            programaID: datosOrden.programaID,
            listaPagosModulo: listaPagosModulo,
            montoTotal: datosOrden.montoTotal,
            responsable: responsable,
            nombreFactura: nombreFactura,
            nitCiFactura: nitCiFactura
        },
        success: function(response) {
            console.log('Respuesta del servidor:', response);

            if (response.success) {
                console.log('Orden registrada:', response.numeroOrden);

                // Preparar datos para el PDF
                const datos = {
                    // Datos del estudiante
                    apaterno: $('#modal_estudiante_apaterno').text(),
                    amaterno: $('#modal_estudiante_amaterno').text(),
                    nombres: $('#modal_estudiante_nombres').text(),
                    correo: $('#modal_estudiante_correo').text(),
                    ci: $('#modal_estudiante_ci').text(),
                    celular: $('#modal_estudiante_celular').text(),

                    // Datos del comprobante
                    programa: $('#modal_programa_nombre').text(),
                    modulo: 'Pago de módulos del programa',
                    montoNumeral: $('#modal_monto_numeral').text(),
                    montoLiteral: $('#modal_monto_literal').text(),

                    // Datos del formulario
                    version: version,
                    numeroTramite: numeroTramite,
                    cuentaAuxiliar: numeroTramite,
                    nombreFactura: nombreFactura,
                    nitCiFactura: nitCiFactura,
                    responsable: responsable,
                    firma: $('#firma').val() || '',

                    // Datos de la orden múltiple
                    esMultiple: 'true',
                    modulosJSON: JSON.stringify(datosOrden.modulos),
                    numeroOrden: response.numeroOrden
                };

                // Crear formulario temporal para enviar por POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'vistas/componentes/generar-orden-pago-pdf.php';
                form.target = '_blank';

                // Agregar campos al formulario
                for (const key in datos) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = datos[key];
                    form.appendChild(input);
                }

                // Enviar formulario
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

                // Cerrar modal y actualizar vista
                setTimeout(function() {
                    $('#modalAdicionarDatos').modal('hide');

                    swal({
                        title: 'Orden generada',
                        text: 'La orden de pago se ha generado correctamente',
                        icon: 'success',
                        button: 'Aceptar'
                    }).then(() => {
                        // Recargar los datos del estudiante para actualizar la vista
                        const estudianteID = $('#select_estudiante').val();
                        if (estudianteID) {
                            cargarDetallePagos(estudianteID);
                        }
                    });
                }, 1000);

            } else {
                swal({
                    title: 'Error',
                    text: response.mensaje || 'No se pudo registrar la orden de pago',
                    icon: 'error',
                    button: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al registrar orden:', error);
            console.error('Respuesta completa:', xhr.responseText);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudo registrar la orden de pago. Intente nuevamente.',
                icon: 'error',
                button: 'Aceptar'
            });
        }
    });
}

/**
 * Generar orden de pago simple (un solo módulo)
 */
function generarOrdenSimple(pagoID, version, numeroTramite, nombreFactura, nitCiFactura, responsable) {
    // Recopilar todos los datos del formulario
    const datos = {
        // Datos del estudiante
        apaterno: $('#modal_estudiante_apaterno').text(),
        amaterno: $('#modal_estudiante_amaterno').text(),
        nombres: $('#modal_estudiante_nombres').text(),
        correo: $('#modal_estudiante_correo').text(),
        ci: $('#modal_estudiante_ci').text(),
        celular: $('#modal_estudiante_celular').text(),

        // Datos del comprobante
        programa: $('#modal_programa_nombre').text(),
        modulo: $('#modal_modulo_nombre').text(),
        montoNumeral: $('#modal_monto_numeral').text(),
        montoLiteral: $('#modal_monto_literal').text(),

        // Datos del formulario
        version: version,
        numeroTramite: numeroTramite,
        cuentaAuxiliar: numeroTramite, // La cuenta auxiliar ES el número de trámite
        nombreFactura: nombreFactura,
        nitCiFactura: nitCiFactura,
        responsable: responsable,
        firma: $('#firma').val() || '',

        // Indicar que es simple
        esMultiple: 'false'
    };

    // Crear formulario temporal para enviar por POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'vistas/componentes/generar-orden-pago-pdf.php';
    form.target = '_blank';

    // Agregar campos al formulario
    for (const key in datos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = datos[key];
        form.appendChild(input);
    }

    // Enviar formulario
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Cerrar modal
    setTimeout(function() {
        $('#modalAdicionarDatos').modal('hide');
    }, 500);
}
