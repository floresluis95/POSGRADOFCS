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
        abrirModalAdicionarDatos(pagoID, moduloNombre, programaNombre, monto);
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
 * Mostrar tabla de detalle de pagos
 */
function mostrarTablaPagos(modulos) {
    let totalPagado = 0;

    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th>PROGRAMA</th>
                        <th style="width: 120px;">CÓDIGO</th>
                        <th>NOMBRE DEL MÓDULO</th>
                        <th style="width: 130px; text-align: right;">COSTO (Bs.)</th>
                        <th style="width: 130px; text-align: center;">FECHA PAGO</th>
                        <th style="width: 120px; text-align: center;">ESTADO</th>
                        <th style="width: 150px; text-align: center;">ADICIONAR DATOS</th>
                        <th style="width: 150px; text-align: center;">ORDEN DE PAGO</th>
                    </tr>
                </thead>
                <tbody>
    `;

    modulos.forEach(function(modulo, index) {
        const estadoBadge = modulo.Estado === 'PAGADO'
            ? '<span class="badge-pagado"><i class="fa fa-check-circle"></i> PAGADO</span>'
            : '<span class="badge-pendiente"><i class="fa fa-clock"></i> PENDIENTE</span>';

        const fechaPago = modulo.fechapago ? formatearFecha(modulo.fechapago) : '-';
        const costo = parseFloat(modulo.costomodulo || 0);

        if (modulo.Estado === 'PAGADO') {
            totalPagado += costo;
        }

        html += `
            <tr style="transition: background 0.3s;">
                <td class="text-center" style="font-weight: 700; color: #667eea;">${index + 1}</td>
                <td style="color: #464E5F;">
                    <strong>${modulo.GradoAcademico || ''}</strong><br>
                    <small style="color: #B5B5C3;">${modulo.NombrePrograma || '-'}</small>
                </td>
                <td>
                    <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 15px; font-size: 11px; font-weight: 700;">
                        ${modulo.codigomodulo || '-'}
                    </span>
                </td>
                <td style="color: #464E5F; font-weight: 500;">${modulo.nombremodulo || '-'}</td>
                <td class="text-right" style="font-size: 16px; font-weight: 700; color: #11998e;">Bs. ${costo.toFixed(2)}</td>
                <td class="text-center" style="color: #B5B5C3;">${fechaPago}</td>
                <td class="text-center">${estadoBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info btn-adicionar-datos"
                            data-pago-id="${modulo.Idpagomodulo}"
                            data-modulo-nombre="${modulo.nombremodulo}"
                            data-programa-nombre="${modulo.GradoAcademico} - ${modulo.NombrePrograma}"
                            data-monto="${costo}"
                            style="border-radius: 20px; padding: 6px 15px;">
                        <i class="fa fa-edit"></i> Adicionar
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary btn-imprimir-orden"
                            data-pago-id="${modulo.Idpagomodulo}"
                            data-modulo-nombre="${modulo.nombremodulo}"
                            style="border-radius: 20px; padding: 6px 15px;">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
                <tfoot style="background: #f8f9fa;">
                    <tr>
                        <td colspan="4" class="text-right" style="padding: 1rem; font-weight: 700; font-size: 16px; color: #464E5F;">
                            TOTAL PAGADO:
                        </td>
                        <td class="text-right" style="padding: 1rem; font-weight: 700; font-size: 20px; color: #11998e;">
                            Bs. ${totalPagado.toFixed(2)}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-3" style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
            <div class="row">
                <div class="col-md-4">
                    <strong style="color: #B5B5C3;">Total de Registros:</strong>
                    <h5 style="color: #464E5F; margin: 0.5rem 0;">${modulos.length}</h5>
                </div>
                <div class="col-md-4">
                    <strong style="color: #B5B5C3;">Módulos Pagados:</strong>
                    <h5 style="color: #28a745; margin: 0.5rem 0;">${modulos.filter(m => m.Estado === 'PAGADO').length}</h5>
                </div>
                <div class="col-md-4">
                    <strong style="color: #B5B5C3;">Módulos Pendientes:</strong>
                    <h5 style="color: #ffc107; margin: 0.5rem 0;">${modulos.filter(m => m.Estado !== 'PAGADO').length}</h5>
                </div>
            </div>
        </div>
    `;

    $('#contenido_tabla_pagos').html(html);
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
function abrirModalAdicionarDatos(pagoID, moduloNombre, programaNombre, monto) {
    console.log('Abrir modal adicionar datos - Pago ID:', pagoID, 'Módulo:', moduloNombre, 'Programa:', programaNombre, 'Monto:', monto);

    // Limpiar formulario
    $('#form_adicionar_datos')[0].reset();

    // Establecer datos en el modal
    $('#modal_pago_id').val(pagoID);
    $('#modal_modulo_nombre').text(moduloNombre);
    $('#modal_programa_nombre').text(programaNombre);

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
    // Validar campos obligatorios
    const version = $('#version').val();
    const cuentaAuxiliar = $('#cuenta_auxiliar').val();
    const nombreFactura = $('#nombre_factura').val();
    const nitCiFactura = $('#nit_ci_factura').val();
    const responsable = $('#responsable').val();

    if (!version || !cuentaAuxiliar || !nombreFactura || !nitCiFactura || !responsable) {
        swal({
            title: 'Campos incompletos',
            text: 'Por favor, complete todos los campos obligatorios marcados con *',
            icon: 'warning',
            button: 'Aceptar'
        });
        return;
    }

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
        cuentaAuxiliar: cuentaAuxiliar,
        nombreFactura: nombreFactura,
        nitCiFactura: nitCiFactura,
        responsable: responsable,
        firma: $('#firma').val()
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
