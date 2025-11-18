/**
 * Sistema de Inscripciones con Plan de Pagos
 * Compatible con PHP 8 - AJAX/jQuery
 * @author Sistema Posgrado FCS
 */

$(document).ready(function() {

    console.log('Script de inscripción cargado correctamente');

    // ========================================
    // 1. CARGAR PROGRAMAS POR GRADO ACADÉMICO
    // ========================================

    $('#gradoAcademico').on('change', function() {
        const gradoAcademico = $(this).val();
        const $selectPrograma = $('#programa');

        console.log('Grado académico seleccionado:', gradoAcademico);

        // Limpiar select de programa
        $selectPrograma.html('<option value="" disabled selected>Cargando programas...</option>');

        // Limpiar sección de detalles
        $('#detalle-programa').hide();
        $('#plan-pagos-preview').hide();

        if (!gradoAcademico) {
            $selectPrograma.html('<option value="" disabled selected>Seleccione un programa</option>');
            return;
        }

        console.log('Enviando petición AJAX para obtener programas...');

        // Petición AJAX para obtener programas
        $.ajax({
            url: 'controladores/inscripcion.controlador.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'obtenerProgramas',
                gradoAcademico: gradoAcademico
            },
            beforeSend: function() {
                console.log('Petición enviada con datos:', {
                    action: 'obtenerProgramas',
                    gradoAcademico: gradoAcademico
                });
            },
            success: function(response) {
                console.log('Respuesta recibida:', response);

                if (response.success && response.data && response.data.length > 0) {
                    let options = '<option value="" disabled selected>Seleccione un programa</option>';

                    response.data.forEach(function(programa) {
                        options += `<option value="${programa.ProgramaID}"
                                           data-modulos="${programa.Modulos}"
                                           data-costo="${programa.Costo}"
                                           data-sede="${programa.Sede}">
                                        ${programa.NombrePrograma} - ${programa.Sede} (Bs. ${formatearMoneda(programa.Costo)})
                                    </option>`;
                    });

                    $selectPrograma.html(options);
                    console.log('Se cargaron', response.data.length, 'programas');
                } else {
                    $selectPrograma.html('<option value="" disabled selected>No hay programas disponibles</option>');
                    mostrarNotificacion('info', response.message || 'No se encontraron programas para este grado académico');
                    console.warn('No se encontraron programas:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar programas:');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Text:', xhr.responseText);
                console.error('Status Code:', xhr.status);

                $selectPrograma.html('<option value="" disabled selected>Error al cargar programas</option>');

                // Mostrar error más detallado
                let errorMsg = 'Error al cargar programas.';
                if (xhr.status === 404) {
                    errorMsg = 'No se encontró el controlador de inscripción.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Error del servidor. Revise los logs de PHP.';
                } else if (xhr.responseText) {
                    errorMsg = 'Error: ' + xhr.responseText.substring(0, 100);
                }

                mostrarNotificacion('error', errorMsg);
            }
        });
    });

    // ========================================
    // 2. MOSTRAR DETALLES DEL PROGRAMA SELECCIONADO
    // ========================================

    $('#programa').on('change', function() {
        const programaID = $(this).val();

        console.log('Programa seleccionado:', programaID);

        if (!programaID) {
            $('#detalle-programa').hide();
            return;
        }

        console.log('Enviando petición AJAX para obtener detalles del programa...');

        // Petición AJAX para obtener detalles del programa
        $.ajax({
            url: 'controladores/inscripcion.controlador.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'obtenerDetallePrograma',
                programaID: programaID
            },
            beforeSend: function() {
                console.log('Petición enviada con datos:', {
                    action: 'obtenerDetallePrograma',
                    programaID: programaID
                });
            },
            success: function(response) {
                console.log('Respuesta de detalles recibida:', response);

                if (response.success && response.data) {
                    const programa = response.data;

                    // Mostrar detalles del programa
                    $('#detalle-nombre-programa').text(programa.NombrePrograma);
                    $('#detalle-codigo').text(programa.Codigo);
                    $('#detalle-duracion').text(programa.DuracionMeses + ' meses');
                    $('#detalle-modulos').text(programa.Modulos + ' módulos');
                    $('#detalle-costo-total').text('Bs. ' + formatearMoneda(programa.Costo));
                    $('#detalle-sede').text(programa.Sede);
                    $('#detalle-inicio').text(formatearFecha(programa.FechaInicio));
                    $('#detalle-tipo').text(programa.Detalle);

                    // Autocompletar campos
                    $('input[name="costoTotal"]').val(programa.Costo);
                    $('input[name="numModulos"]').val(programa.Modulos).attr('readonly', true);

                    // Calcular costo por módulo
                    calcularCostoPorModulo();

                    $('#detalle-programa').slideDown();
                } else {
                    mostrarNotificacion('error', response.message || 'No se pudieron cargar los detalles del programa');
                    console.error('Error al obtener detalles:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar detalles:');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Text:', xhr.responseText);
                mostrarNotificacion('error', 'Error al cargar detalles del programa');
            }
        });
    });

    // ========================================
    // 3. CALCULAR COSTO POR MÓDULO
    // ========================================

    function calcularCostoPorModulo() {
        const costoTotal = parseFloat($('input[name="costoTotal"]').val()) || 0;
        const pagoInicial = parseFloat($('input[name="pagoMatricula"]').val()) || 0;
        const numModulos = parseInt($('input[name="numModulos"]').val()) || 1;

        const montoModulos = costoTotal - pagoInicial;
        const costoPorModulo = montoModulos / numModulos;

        // Actualizar campo de pago de módulos
        $('input[name="pagoModulos"]').val(montoModulos.toFixed(2));
        $('#costo-por-modulo').text('Bs. ' + formatearMoneda(costoPorModulo.toFixed(2)));

        // Generar preview del plan de pagos
        generarPlanPagosPreview(numModulos, costoPorModulo);
    }

    // Recalcular cuando cambie el pago inicial
    $('input[name="pagoMatricula"]').on('input', calcularCostoPorModulo);

    // ========================================
    // 4. GENERAR PREVIEW DEL PLAN DE PAGOS
    // ========================================

    function generarPlanPagosPreview(numModulos, costoPorModulo) {
        const tbody = $('#tabla-plan-pagos tbody');
        tbody.empty();

        for (let i = 1; i <= numModulos; i++) {
            // Calcular mes de vencimiento
            const fechaVencimiento = new Date();
            fechaVencimiento.setMonth(fechaVencimiento.getMonth() + i);

            // Ajustar último módulo por redondeos
            let monto = costoPorModulo;
            if (i === numModulos) {
                const montoModulos = parseFloat($('input[name="pagoModulos"]').val());
                monto = montoModulos - (costoPorModulo * (numModulos - 1));
            }

            const row = `
                <tr>
                    <td class="text-center">${i}</td>
                    <td>MÓDULO ${numeroARomano(i)}</td>
                    <td class="text-right">Bs. ${formatearMoneda(monto.toFixed(2))}</td>
                    <td class="text-center">${formatearFecha(fechaVencimiento.toISOString().split('T')[0])}</td>
                    <td class="text-center"><span class="badge badge-warning">PENDIENTE</span></td>
                </tr>
            `;
            tbody.append(row);
        }

        $('#plan-pagos-preview').slideDown();
    }

    // ========================================
    // 5. ENVIAR FORMULARIO DE INSCRIPCIÓN
    // ========================================

    $('#formMatriculacion').on('submit', function(e) {
        e.preventDefault();

        console.log('Formulario de matriculación enviado');

        // Validar que se haya seleccionado estudiante
        const estudianteID = $('select[name="idcliente"]').val();
        if (!estudianteID || estudianteID === '' || estudianteID === 'Buscar estudiante por cédula de identidad') {
            mostrarNotificacion('warning', 'Por favor seleccione un estudiante');
            return;
        }

        // Validar que se haya seleccionado programa
        const programaID = $('#programa').val();
        if (!programaID || programaID === '') {
            mostrarNotificacion('warning', 'Por favor seleccione un programa');
            return;
        }

        // Validar formulario HTML5
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            mostrarNotificacion('warning', 'Por favor complete todos los campos requeridos');
            return;
        }

        // Validar montos
        const pagoMatricula = parseFloat($('input[name="pagoMatricula"]').val());
        const pagoModulos = parseFloat($('input[name="pagoModulos"]').val());
        const numModulos = parseInt($('input[name="numModulos"]').val());

        if (isNaN(pagoMatricula) || pagoMatricula < 0) {
            mostrarNotificacion('warning', 'El pago de matrícula no es válido');
            return;
        }

        if (isNaN(numModulos) || numModulos <= 0) {
            mostrarNotificacion('warning', 'El número de módulos no es válido');
            return;
        }

        // Preparar datos
        const datos = {
            action: 'registrarInscripcion',
            estudianteID: estudianteID,
            programaID: programaID,
            pagoInicial: pagoMatricula,
            montoModulos: pagoModulos,
            cantidadModulos: numModulos
        };

        console.log('Enviando datos de inscripción:', datos);

        // Deshabilitar botón de envío
        const $btnSubmit = $(this).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');

        // Enviar petición AJAX
        $.ajax({
            url: 'controladores/inscripcion.controlador.php',
            type: 'POST',
            dataType: 'json',
            data: datos,
            success: function(response) {
                console.log('Respuesta de inscripción:', response);

                if (response.success) {
                    // Éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Inscripción Exitosa!',
                        html: `
                            <div class="text-left">
                                <p>La inscripción ha sido registrada correctamente en la tabla <strong>estudianteprograma</strong>.</p>
                                <hr>
                                <p><strong>ID Inscripción:</strong> ${response.data.idInscripcion}</p>
                                <p><strong>Estudiante ID:</strong> ${response.data.estudianteID}</p>
                                <p><strong>Programa ID:</strong> ${response.data.programaID}</p>
                                <p><strong>Fecha Inscripción:</strong> ${response.data.fechaInscripcion}</p>
                                <p><strong>Plan de Pagos ID:</strong> ${response.data.planPagoID}</p>
                                <hr>
                                <p class="text-success">✓ Se ha generado automáticamente el plan de pagos con ${datos.cantidadModulos} cuotas.</p>
                            </div>
                        `,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        // Redirigir o limpiar formulario
                        window.location.href = 'matriculas';
                    });
                } else {
                    // Error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar inscripción',
                        text: response.message || 'Error desconocido al registrar inscripción',
                        confirmButtonText: 'Aceptar'
                    });
                    $btnSubmit.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar Matriculación');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                console.error('Response:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    confirmButtonText: 'Aceptar'
                });
                $btnSubmit.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar Matriculación');
            }
        });
    });

    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    function formatearMoneda(numero) {
        return parseFloat(numero).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatearFecha(fecha) {
        const date = new Date(fecha + 'T00:00:00');
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('es-ES', opciones);
    }

    function numeroARomano(num) {
        const romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X',
                         'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'];
        return romanos[num - 1] || num;
    }

    function mostrarNotificacion(tipo, mensaje) {
        const iconos = {
            success: 'success',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: iconos[tipo] || 'info',
            title: mensaje
        });
    }

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    // Ocultar secciones al cargar
    $('#detalle-programa').hide();
    $('#plan-pagos-preview').hide();

    console.log('Todos los eventos han sido configurados correctamente');
});
