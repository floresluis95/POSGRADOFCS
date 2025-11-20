/**
 * Sistema de Inscripción a Módulos
 * Maneja la inscripción de estudiantes a módulos específicos
 */

$(document).ready(function() {
    console.log('Script de inscripción a módulos cargado correctamente');

    // Inicializar DataTable
    $('#tablaMatriculados').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[8, 'desc']], // Ordenar por fecha descendente
        pageLength: 25
    });

    // ========================================
    // ABRIR MODAL DE INSCRIPCIÓN A MÓDULO
    // ========================================
    $(document).on('click', '.btn-inscribir-modulo', function() {
        const estudianteID = $(this).data('estudiante-id');
        const estudianteNombre = $(this).data('estudiante-nombre');
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');

        console.log('Abriendo modal para:', estudianteNombre);

        // Llenar información del estudiante
        $('#infoEstudianteNombre').text(estudianteNombre);
        $('#infoProgramaNombre').text(programaNombre);
        $('#estudianteID').val(estudianteID);
        $('#programaID').val(programaID);

        // Limpiar formulario
        $('#moduloID').html('<option value="">Cargando módulos...</option>');
        $('#costoModulo').val('');
        $('input[name="numeroVaucherModulo"]').val('');
        $('#detallesModulo').hide();

        // Establecer fecha actual
        const hoy = new Date().toISOString().split('T')[0];
        $('#fechaInscripcionModulo').val(hoy);

        // Cargar módulos del programa
        cargarModulosPorPrograma(programaID);

        // Mostrar modal
        $('#modalInscribirModulo').modal('show');
    });

    // ========================================
    // CARGAR MÓDULOS POR PROGRAMA (AJAX)
    // ========================================
    function cargarModulosPorPrograma(programaID) {
        $.ajax({
            url: 'ajax/modulo.ajax.php',
            method: 'POST',
            data: {
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                console.log('Módulos cargados:', response);

                let options = '<option value="">Seleccione un módulo...</option>';

                if (response && response.length > 0) {
                    response.forEach(function(modulo) {
                        options += `<option value="${modulo.ModuloID}"
                                           data-codigo="${modulo.Codigo}"
                                           data-creditos="${modulo.Creditos}"
                                           data-horas-teoricas="${modulo.HorasTeoricas}"
                                           data-horas-practicas="${modulo.HorasPracticas}"
                                           data-costo="${modulo.Costo}">
                                        ${modulo.Codigo} - ${modulo.NombreModulo}
                                    </option>`;
                    });
                } else {
                    options = '<option value="">No hay módulos disponibles para este programa</option>';
                }

                $('#moduloID').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar módulos:', error);
                $('#moduloID').html('<option value="">Error al cargar módulos</option>');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los módulos del programa'
                });
            }
        });
    }

    // ========================================
    // MOSTRAR DETALLES DEL MÓDULO SELECCIONADO
    // ========================================
    $('#moduloID').on('change', function() {
        const selectedOption = $(this).find('option:selected');

        if ($(this).val() === '') {
            $('#detallesModulo').hide();
            $('#costoModulo').val('');
            return;
        }

        const codigo = selectedOption.data('codigo');
        const creditos = selectedOption.data('creditos');
        const horasTeo = selectedOption.data('horas-teoricas');
        const horasPrac = selectedOption.data('horas-practicas');
        const costo = selectedOption.data('costo');

        // Mostrar detalles
        $('#detalle-codigo').text(codigo);
        $('#detalle-creditos').text(creditos);
        $('#detalle-horas-teoricas').text(horasTeo);
        $('#detalle-horas-practicas').text(horasPrac);
        $('#detalle-costo-modulo').text('Bs. ' + parseFloat(costo).toFixed(2));

        // Auto-completar costo
        $('#costoModulo').val(parseFloat(costo).toFixed(2));

        // Mostrar el panel de detalles
        $('#detallesModulo').slideDown();
    });

    // ========================================
    // VALIDACIÓN DEL FORMULARIO
    // ========================================
    $('#formInscripcionModulo').on('submit', function(e) {
        // Validar módulo seleccionado
        const moduloID = $('#moduloID').val();
        if (!moduloID || moduloID === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un módulo',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar costo
        const costo = parseFloat($('#costoModulo').val());
        if (!costo || costo <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor ingrese un costo válido',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar voucher
        const voucher = $('input[name="numeroVaucherModulo"]').val();
        if (!voucher || voucher.trim() === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor ingrese el número de voucher',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar fecha
        const fecha = $('#fechaInscripcionModulo').val();
        if (!fecha || fecha === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione la fecha de inscripción',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Mostrar mensaje de procesamiento
        Swal.fire({
            title: 'Procesando...',
            text: 'Registrando inscripción al módulo, por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        return true;
    });

    console.log('Sistema de inscripción a módulos configurado correctamente');
});
