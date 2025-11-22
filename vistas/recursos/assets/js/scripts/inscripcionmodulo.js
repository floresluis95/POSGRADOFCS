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
    // ABRIR MODAL DE INSCRIPCIÓN A MÓDULO (PAGO)
    // ========================================
    $(document).on('click', '.btn-inscribir-modulo', function(e) {
        e.preventDefault();

        const estudianteID = $(this).data('estudiante-id');
        const estudianteNombre = $(this).data('estudiante-nombre');
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');
        const idinscripcion = $(this).data('idinscripcion');

        console.log('Abriendo modal de pago de módulo para:', estudianteNombre);
        console.log('ID Inscripción:', idinscripcion);

        // Llenar información del estudiante
        $('#infoEstudianteNombre').text(estudianteNombre);
        $('#infoProgramaNombre').text(programaNombre);
        $('#estudianteID').val(estudianteID);
        $('#programaID').val(programaID);
        $('#idinscripcion').val(idinscripcion);

        // Limpiar formulario
        $('#moduloSeleccionado').val(''); // Campo hidden
        $('#costoModulo').val('');
        $('input[name="numeroVaucher"]').val('');
        $('#fmodulo').val('');
        $('#moduloSeleccionadoInfo').hide();

        // Mostrar loader en contenedor de módulos
        $('#contenedorModulos').html(`
            <div class="text-center p-4">
                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Cargando módulos...</p>
            </div>
        `);

        // Establecer fecha actual
        const hoy = new Date().toISOString().split('T')[0];
        $('#fechaPago').val(hoy);

        // Mostrar modal
        $('#modalInscribirModulo').modal('show');

        // Cargar módulos disponibles del programa DESPUÉS de mostrar el modal
        setTimeout(function() {
            cargarModulosDisponibles(programaID);
        }, 300);
    });

    // ========================================
    // CARGAR MÓDULOS CON ESTADO DE PAGO (TARJETAS)
    // ========================================
    function cargarModulosDisponibles(programaID) {
        const idinscripcion = $('#idinscripcion').val();

        console.log('=== CARGAR MÓDULOS ===');
        console.log('ProgramaID:', programaID);
        console.log('idinscripcion:', idinscripcion);

        if (!idinscripcion) {
            console.error('❌ ERROR: No se encontró ID de inscripción');
            $('#contenedorModulos').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle fa-2x mb-2"></i>
                    <h5>Error</h5>
                    <p>No se pudo obtener el ID de inscripción.</p>
                </div>
            `);
            return;
        }

        console.log('📡 Enviando petición AJAX...');

        $.ajax({
            url: 'ajax/modulo.ajax.php',
            method: 'POST',
            data: {
                programaID: programaID,
                idinscripcion: idinscripcion
            },
            dataType: 'json',
            success: function(response) {
                console.log('✅ Respuesta AJAX recibida:', response);
                console.log('📊 Total de módulos:', response ? response.length : 0);

                if (response && response.length > 0) {
                    let tarjetasHTML = '';
                    let contadorPagados = 0;
                    let contadorPendientes = 0;

                    response.forEach(function(modulo) {
                        const esPagado = parseInt(modulo.Pagado) === 1;
                        const claseEstado = esPagado ? 'pagado' : 'pendiente';
                        const textoEstado = esPagado ? 'PAGADO' : 'PENDIENTE';

                        if (esPagado) {
                            contadorPagados++;
                        } else {
                            contadorPendientes++;
                        }

                        // Información de pago (solo si está pagado)
                        let infoPago = '';
                        if (esPagado && modulo.FechaPago) {
                            const fechaPago = new Date(modulo.FechaPago).toLocaleDateString('es-BO');
                            infoPago = `
                                <div class="modulo-card-pago-info">
                                    <p><strong>Pagado:</strong> ${fechaPago}</p>
                                    <p><strong>Monto:</strong> Bs. ${parseFloat(modulo.CostoPagado || 0).toFixed(2)}</p>
                                    ${modulo.NumeroVaucher ? `<p><strong>Voucher:</strong> ${modulo.NumeroVaucher}</p>` : ''}
                                </div>
                            `;
                        }

                        tarjetasHTML += `
                            <div class="modulo-card ${claseEstado}"
                                 data-nombre="${modulo.NombreModulo}"
                                 data-codigo="${modulo.Codigo}"
                                 data-creditos="${modulo.Creditos}"
                                 data-horas-teoricas="${modulo.HorasTeoricas}"
                                 data-horas-practicas="${modulo.HorasPracticas}"
                                 data-costo="${modulo.Costo}"
                                 data-pagado="${modulo.Pagado}">

                                <div class="modulo-card-header">
                                    <span class="modulo-card-codigo">${modulo.Codigo}</span>
                                </div>

                                <div class="modulo-card-titulo">${modulo.NombreModulo}</div>

                                <div class="modulo-card-info">
                                    <div class="modulo-card-info-item">
                                        <span class="modulo-card-info-label">Créditos</span>
                                        <span class="modulo-card-info-valor">${modulo.Creditos}</span>
                                    </div>
                                    <div class="modulo-card-info-item">
                                        <span class="modulo-card-info-label">Horas</span>
                                        <span class="modulo-card-info-valor">${parseInt(modulo.HorasTeoricas) + parseInt(modulo.HorasPracticas)}</span>
                                    </div>
                                </div>

                                <div class="modulo-card-costo">Bs. ${parseFloat(modulo.Costo).toFixed(2)}</div>

                                <div class="modulo-card-estado">${textoEstado}</div>

                                ${infoPago}
                            </div>
                        `;
                    });

                    console.log('🎨 Generando HTML de tarjetas...');
                    console.log('HTML length:', tarjetasHTML.length);

                    $('#contenedorModulos').html(tarjetasHTML);

                    // Mostrar resumen
                    console.log(`📈 RESUMEN: Total módulos: ${response.length}, Pagados: ${contadorPagados}, Pendientes: ${contadorPendientes}`);

                    // Agregar event listeners a las tarjetas pendientes
                    console.log('🖱️ Agregando eventos click a tarjetas pendientes...');
                    $('.modulo-card.pendiente').on('click', function() {
                        console.log('Click en tarjeta pendiente');
                        seleccionarModulo($(this));
                    });

                    console.log('✅ Tarjetas cargadas correctamente');

                    // Mensaje si todo está pagado
                    if (contadorPendientes === 0) {
                        $('#contenedorModulos').append(`
                            <div class="col-12">
                                <div class="alert alert-success text-center mt-3">
                                    <i class="fa fa-check-circle fa-2x mb-2"></i>
                                    <h5>¡Todos los módulos están pagados!</h5>
                                    <p>Este estudiante ha completado el pago de todos los módulos del programa.</p>
                                </div>
                            </div>
                        `);
                    }

                } else {
                    $('#contenedorModulos').html(`
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                                <h5>No hay módulos disponibles</h5>
                                <p>Este programa no tiene módulos registrados.</p>
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ ERROR AJAX:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);

                $('#contenedorModulos').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle fa-2x mb-2"></i>
                        <h5>Error al cargar módulos</h5>
                        <p>No se pudieron cargar los módulos del programa.</p>
                        <small class="d-block mt-2">Error: ${error}</small>
                    </div>
                `);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los módulos del programa. Revisa la consola para más detalles.'
                });
            }
        });
    }

    // ========================================
    // SELECCIONAR MÓDULO (AL HACER CLIC EN TARJETA ROJA)
    // ========================================
    function seleccionarModulo(card) {
        // Remover selección anterior
        $('.modulo-card').removeClass('seleccionado');

        // Marcar como seleccionado
        card.addClass('seleccionado');

        // Obtener datos del módulo
        const nombre = card.data('nombre');
        const codigo = card.data('codigo');
        const creditos = card.data('creditos');
        const horasTeo = card.data('horas-teoricas');
        const horasPrac = card.data('horas-practicas');
        const costo = card.data('costo');

        console.log('Módulo seleccionado:', nombre);

        // Actualizar campo hidden
        $('#moduloSeleccionado').val(nombre);

        // Mostrar información del módulo seleccionado
        $('#detalle-codigo').text(codigo);
        $('#detalle-nombre').text(nombre);
        $('#detalle-creditos').text(creditos);
        $('#detalle-horas-teoricas').text(horasTeo);
        $('#detalle-horas-practicas').text(horasPrac);
        $('#detalle-costo-modulo').text('Bs. ' + parseFloat(costo).toFixed(2));

        // Auto-completar el costo
        $('#costoModulo').val(parseFloat(costo).toFixed(2));

        // Mostrar el panel de información
        $('#moduloSeleccionadoInfo').slideDown();

        // Hacer scroll suave hacia el formulario de pago
        $('html, body').animate({
            scrollTop: $('#moduloSeleccionadoInfo').offset().top - 100
        }, 500);
    }

    // ========================================
    // EVENTO OBSOLETO - AHORA SE USA TARJETAS EN LUGAR DE SELECT
    // ========================================
    // La selección de módulos ahora se hace mediante tarjetas (cajoncitos)
    // Ver función seleccionarModulo() más arriba

    // ========================================
    // VALIDACIÓN DEL FORMULARIO DE PAGO DE MÓDULO
    // ========================================
    $('#formPagoModulo').on('submit', function(e) {
        // Validar módulo seleccionado
        const moduloSeleccionado = $('#moduloSeleccionado').val();
        if (!moduloSeleccionado || moduloSeleccionado === '') {
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

        // Validar fecha
        const fecha = $('#fechaPago').val();
        if (!fecha || fecha === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione la fecha de pago',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Validar inscripción ID
        const idinscripcion = $('#idinscripcion').val();
        if (!idinscripcion) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el ID de inscripción',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        console.log('Formulario válido - Enviando datos de pago...');
        console.log('Módulo:', moduloSeleccionado);
        console.log('Costo:', costo);
        console.log('Fecha:', fecha);
        console.log('ID Inscripción:', idinscripcion);

        // Mostrar mensaje de procesamiento
        Swal.fire({
            title: 'Procesando...',
            text: 'Registrando pago del módulo, por favor espere',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        return true;
    });

    // ========================================
    // VER DETALLES DEL ESTUDIANTE MATRICULADO
    // ========================================
    $(document).on('click', '.btn-ver-detalles', function(e) {
        e.preventDefault();

        const estudianteNombre = $(this).data('estudiante-nombre');
        const estudianteCI = $(this).data('estudiante-ci');
        const programaNombre = $(this).data('programa-nombre');
        const grado = $(this).data('grado');
        const codigo = $(this).data('codigo');
        const costo = $(this).data('costo');
        const voucher = $(this).data('voucher');
        const fecha = $(this).data('fecha');

        // Llenar información en el modal
        $('#detalleEstudianteNombre').text(estudianteNombre);
        $('#detalleEstudianteCI').text(estudianteCI);
        $('#detalleProgramaNombre').text(programaNombre);
        $('#detalleGrado').text(grado);
        $('#detalleCodigo').text(codigo);
        $('#detalleCosto').text('Bs. ' + costo);
        $('#detalleVoucher').text(voucher);
        $('#detalleFecha').text(fecha);

        // Mostrar modal
        $('#modalVerDetalles').modal('show');
    });

    // ========================================
    // VER MÓDULOS INSCRITOS
    // ========================================
    $(document).on('click', '.btn-ver-modulos', function(e) {
        e.preventDefault();

        const estudianteID = $(this).data('estudiante-id');
        const estudianteNombre = $(this).data('estudiante-nombre');

        console.log('Cargando módulos inscritos para estudiante:', estudianteID);

        // Actualizar título del modal
        $('#modulosEstudianteNombre').text(estudianteNombre);

        // Mostrar modal con loader
        $('#contenidoModulos').html(`
            <div class="text-center">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <p class="mt-3">Cargando módulos...</p>
            </div>
        `);
        $('#modalVerModulos').modal('show');

        // Cargar módulos inscritos vía AJAX
        $.ajax({
            url: 'ajax/inscripcionmodulo.ajax.php',
            method: 'POST',
            data: {
                accion: 'obtenerModulosInscritos',
                estudianteID: estudianteID
            },
            dataType: 'json',
            success: function(response) {
                console.log('Módulos inscritos:', response);

                if (response && response.length > 0) {
                    let tabla = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                                    <tr>
                                        <th class="text-center" style="color: white;">#</th>
                                        <th style="color: white;">MÓDULO</th>
                                        <th class="text-center" style="color: white;">CÓDIGO</th>
                                        <th class="text-center" style="color: white;">CRÉDITOS</th>
                                        <th class="text-center" style="color: white;">COSTO</th>
                                        <th class="text-center" style="color: white;">N° VOUCHER</th>
                                        <th class="text-center" style="color: white;">FECHA INSCRIPCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    response.forEach((modulo, index) => {
                        const fechaFormateada = modulo.FechaInscripcion ?
                            new Date(modulo.FechaInscripcion).toLocaleDateString('es-BO') : 'N/A';

                        tabla += `
                            <tr>
                                <td class="text-center"><strong>${index + 1}</strong></td>
                                <td>${modulo.NombreModulo || 'N/A'}</td>
                                <td class="text-center"><span class="badge badge-primary">${modulo.Codigo || 'N/A'}</span></td>
                                <td class="text-center">${modulo.Creditos || 'N/A'}</td>
                                <td class="text-center"><strong>Bs. ${parseFloat(modulo.Costo || 0).toFixed(2)}</strong></td>
                                <td class="text-center">${modulo.NumeroVaucher || 'N/A'}</td>
                                <td class="text-center">${fechaFormateada}</td>
                            </tr>
                        `;
                    });

                    tabla += `
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-success mt-3">
                            <i class="fa fa-check-circle"></i>
                            Total de módulos inscritos: <strong>${response.length}</strong>
                        </div>
                    `;

                    $('#contenidoModulos').html(tabla);
                } else {
                    $('#contenidoModulos').html(`
                        <div class="alert alert-warning text-center">
                            <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                            <h5>No hay módulos inscritos</h5>
                            <p>Este estudiante aún no está inscrito en ningún módulo.</p>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar módulos inscritos:', error);
                $('#contenidoModulos').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle fa-3x mb-3"></i>
                        <h5>Error al cargar módulos</h5>
                        <p>No se pudieron cargar los módulos inscritos. Por favor, intente nuevamente.</p>
                    </div>
                `);
            }
        });
    });

    console.log('Sistema de inscripción a módulos configurado correctamente');
});
