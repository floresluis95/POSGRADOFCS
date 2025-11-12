/**
 * Sistema de Inscripciones con Plan de Pagos
 * Compatible con PHP 8 - AJAX/jQuery
 * @author Sistema Posgrado FCS
 */

$(document).ready(function() {

    // ========================================
    // 1. CARGAR PROGRAMAS POR GRADO ACADÉMICO
    // ========================================

    $('#gradoAcademico').on('change', function() {
        const gradoAcademico = $(this).val();
        const $selectPrograma = $('#programa');

        // Limpiar select de programa
        $selectPrograma.html('<option value="" disabled selected>Cargando programas...</option>');

        // Limpiar sección de detalles
        $('#detalle-programa').hide();
        $('#plan-pagos-preview').hide();

        if (!gradoAcademico) {
            $selectPrograma.html('<option value="" disabled selected>Seleccione un programa</option>');
            return;
        }

        // Petición AJAX para obtener programas
        $.ajax({
            url: 'controladores/inscripcion.controlador.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'obtenerProgramas',
                gradoAcademico: gradoAcademico
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
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
                } else {
                    $selectPrograma.html('<option value="" disabled selected>No hay programas disponibles</option>');
                    mostrarNotificacion('info', 'No se encontraron programas para este grado académico');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar programas:', error);
                $selectPrograma.html('<option value="" disabled selected>Error al cargar programas</option>');
                mostrarNotificacion('error', 'Error al cargar programas. Intente nuevamente.');
            }
        });
    });

    // ========================================
    // 2. MOSTRAR DETALLES DEL PROGRAMA SELECCIONADO
    // ========================================

    $('#programa').on('change', function() {
        const programaID = $(this).val();

        if (!programaID) {
            $('#detalle-programa').hide();
            return;
        }

        // Petición AJAX para obtener detalles del programa
        $.ajax({
            url: 'controladores/inscripcion.controlador.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'obtenerDetallePrograma',
                programaID: programaID
            },
            success: function(response) {
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
                    mostrarNotificacion('error', 'No se pudieron cargar los detalles del programa');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar detalles:', error);
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

        // Validar que se haya seleccionado estudiante
        const estudianteID = $('select[name="idcliente"]').val();
        if (!estudianteID) {
            mostrarNotificacion('warning', 'Por favor seleccione un estudiante');
            return;
        }

        // Validar que se haya seleccionado programa
        const programaID = $('#programa').val();
        if (!programaID) {
            mostrarNotificacion('warning', 'Por favor seleccione un programa');
            return;
        }

        // Validar formulario HTML5
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }

        // Preparar datos
        const datos = {
            action: 'registrarInscripcion',
            estudianteID: estudianteID,
            programaID: programaID,
            pagoInicial: parseFloat($('input[name="pagoMatricula"]').val()),
            montoModulos: parseFloat($('input[name="pagoModulos"]').val()),
            cantidadModulos: parseInt($('input[name="numModulos"]').val())
        };

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
                if (response.success) {
                    // Éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Inscripción Exitosa!',
                        html: `
                            <p>La inscripción ha sido registrada correctamente.</p>
                            <p><strong>ID Inscripción:</strong> ${response.data.idInscripcion}</p>
                            <p><strong>Plan de Pagos:</strong> ${response.data.planPagoID}</p>
                            <p>Se ha generado automáticamente el plan de pagos con ${datos.cantidadModulos} cuotas.</p>
                        `,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Redirigir o limpiar formulario
                        window.location.href = 'matriculas';
                    });
                } else {
                    // Error
                    mostrarNotificacion('error', response.message || 'Error al registrar inscripción');
                    $btnSubmit.prop('disabled', false).html('<i class="bi bi-save"></i> Guardar Matriculación');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                mostrarNotificacion('error', 'Error de conexión. Intente nuevamente.');
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

});
