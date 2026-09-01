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
    // ABRIR MODAL UNIFICADO: PLAN DE PAGO + PAGO DE MÓDULOS
    // ========================================
    $(document).on('click', '.btn-gestionar-pagos', function(e) {
        e.preventDefault();

        const estudianteID = $(this).data('estudiante-id');
        const estudianteNombre = $(this).data('estudiante-nombre');
        const estudianteCI = $(this).data('estudiante-ci');
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');
        const costoPrograma = $(this).data('costo-programa');
        const version = $(this).data('version');
        const numeroTramite = $(this).data('numero-tramite');
        const idinscripcion = $(this).data('idinscripcion');

        console.log('Abriendo modal de gestión de pagos para:', estudianteNombre);
        console.log('ID Inscripción:', idinscripcion);

        // Llenar información del estudiante
        $('#infoEstudianteNombre').text(estudianteNombre);
        $('#infoProgramaNombre').text(programaNombre);
        $('#estudianteID').val(estudianteID);
        $('#programaID').val(programaID);
        $('#idinscripcion').val(idinscripcion);

        // Limpiar formulario de pago múltiple
        $('input[name="numeroVaucher"]').val('');
        $('#fmodulo').val('');

        // Limpiar selección de módulos (manejado por matriculados-modulos-multiple.js)
        if (typeof modulosSeleccionados !== 'undefined') {
            modulosSeleccionados = [];
        }
        $('.modulo-card').removeClass('seleccionado');
        $('.modulo-checkbox').prop('checked', false);
        $('#modulosSeleccionadosInfo').hide();
        $('#contenedorCostos').hide();

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

        // Volver siempre a la pestaña "Plan de Pago" al abrir
        $('#tab-plan-pago-link').tab('show');

        // Mostrar modal
        $('#modalInscribirModulo').modal('show');

        // Cargar plan de pago, módulos disponibles y orden de pago DESPUÉS de mostrar el modal
        setTimeout(function() {
            const contexto = {
                idInscripcion: idinscripcion,
                estudianteID: estudianteID,
                estudianteNombre: estudianteNombre,
                estudianteCi: estudianteCI,
                programaID: programaID,
                programaNombre: programaNombre,
                costoPrograma: parseFloat(costoPrograma) || 0,
                version: version,
                numeroTramite: numeroTramite
            };

            if (typeof window.abrirPlanPagoPrograma === 'function') {
                window.abrirPlanPagoPrograma(contexto);
            }
            if (typeof window.cargarOrdenPagoModulos === 'function') {
                window.cargarOrdenPagoModulos(contexto);
            }
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

        $.when(
            $.ajax({
                url: 'ajax/modulo.ajax.php',
                method: 'POST',
                data: {
                    programaID: programaID,
                    idinscripcion: idinscripcion
                },
                dataType: 'json'
            }),
            $.ajax({
                url: 'ajax/ordenpago.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerOrdenesModulosPendientes', idinscripcion: idinscripcion },
                dataType: 'json'
            })
        ).done(function(respModulos, respOrdenes) {
                const response = respModulos[0];
                const ordenesResp = respOrdenes[0];
                const ordenes = (ordenesResp && ordenesResp.success) ? (ordenesResp.ordenes || []) : [];

                // Mapa ModuloID -> orden de pago pendiente que ya lo incluye. El pago de un
                // módulo solo puede registrarse si ya tiene una orden generada (pestaña
                // "Orden de Pago"), con el mismo formato que la orden de pago de matrícula.
                const ordenPorModulo = {};
                ordenes.forEach(function(orden) {
                    (orden.ListaModulos || '').split(',').forEach(function(idMod) {
                        idMod = idMod.trim();
                        if (idMod) ordenPorModulo[idMod] = orden;
                    });
                });

                console.log('✅ Respuesta AJAX recibida:', response);
                console.log('📊 Total de módulos:', response ? response.length : 0);

                if (response && response.length > 0) {
                    let tarjetasHTML = '';
                    let contadorPagados = 0;
                    let contadorPendientes = 0;

                    response.forEach(function(modulo) {
                        const esPagado = parseInt(modulo.Pagado) === 1;
                        const orden = ordenPorModulo[String(modulo.ModuloID)];
                        const tieneOrden = !esPagado && !!orden;
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

                        // Checkbox solo para módulos pendientes que ya tienen orden de pago emitida
                        const checkbox = tieneOrden ? `
                            <input type="checkbox" class="modulo-checkbox"
                                   style="position: absolute; top: 10px; left: 10px; width: 20px; height: 20px; cursor: pointer; z-index: 10;">
                        ` : '';

                        // Módulo pendiente sin orden de pago aún: no se puede seleccionar para
                        // pagar, hay que generar primero la orden en la pestaña "Orden de Pago"
                        let avisoSinOrden = '';
                        if (!esPagado && !tieneOrden) {
                            avisoSinOrden = `
                                <div class="modulo-card-pago-info">
                                    <p class="mb-0"><i class="fa fa-lock"></i> Requiere orden de pago.
                                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline btn-ir-orden-pago">Generarla ahora</button></p>
                                </div>
                            `;
                        } else if (!esPagado && tieneOrden) {
                            avisoSinOrden = `
                                <div class="modulo-card-pago-info">
                                    <p class="mb-0"><i class="fa fa-file-invoice"></i> Orden de pago N° ${orden.NumeroOrden} emitida</p>
                                </div>
                            `;
                        }

                        // Si es el último módulo pendiente, su costo puede haberse ajustado
                        // para completar exactamente el total del programa (falta o sobra por redondeo)
                        let avisoAjuste = '';
                        if (!esPagado && modulo.AjustadoAlTotal) {
                            avisoAjuste = `
                                <div class="modulo-card-pago-info">
                                    <p class="mb-0"><i class="fa fa-info-circle"></i> Último módulo: monto ajustado a
                                    Bs. ${parseFloat(modulo.Costo || 0).toFixed(2)} para completar el total del programa
                                    (costo registrado: Bs. ${parseFloat(modulo.CostoOriginal || 0).toFixed(2)})</p>
                                </div>
                            `;
                        }

                        tarjetasHTML += `
                            <div class="modulo-card ${claseEstado} ${tieneOrden ? 'seleccionable' : ''}"
                                 data-moduloid="${modulo.ModuloID}"
                                 data-nombre="${modulo.NombreModulo}"
                                 data-codigo="${modulo.Codigo}"
                                 data-costo="${modulo.Costo || 0}"
                                 data-pagado="${modulo.Pagado}">
                                ${checkbox}
                                <div class="modulo-card-header">
                                    <span class="modulo-card-codigo">${modulo.Codigo}</span>
                                    <span class="badge ${esPagado ? 'badge-success' : 'badge-danger'}">${textoEstado}</span>
                                </div>

                                <div class="modulo-card-titulo">${modulo.NombreModulo}</div>

                                ${modulo.NombreDocente ? `
                                    <div class="modulo-card-docente">
                                        <i class="fa fa-user"></i> ${modulo.NombreDocente}
                                    </div>
                                ` : ''}

                                ${infoPago}
                                ${avisoSinOrden}
                                ${avisoAjuste}
                            </div>
                        `;
                    });

                    console.log('🎨 Generando HTML de tarjetas...');
                    console.log('HTML length:', tarjetasHTML.length);

                    $('#contenedorModulos').html(tarjetasHTML);

                    // Mostrar resumen
                    console.log(`📈 RESUMEN: Total módulos: ${response.length}, Pagados: ${contadorPagados}, Pendientes: ${contadorPendientes}`);

                    console.log('✅ Tarjetas cargadas correctamente');
                    console.log('ℹ️ Los eventos de selección múltiple son manejados por matriculados-modulos-multiple.js');

                    // Mensaje si todo está pagado
                    if (contadorPendientes === 0) {
                        $('#contenedorModulos').append(`
                            <div class="col-12">
                                <div class="alert alert-success text-center mt-3" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.15) 0%, rgba(40, 167, 69, 0.05) 100%); border: 2px solid #28a745;">
                                    <i class="fa fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h4 class="text-success mb-3">¡Todos los módulos están pagados!</h4>
                                    <p class="mb-2"><i class="fa fa-info-circle"></i> Este estudiante ha completado el pago de todos los módulos del programa.</p>
                                    <hr style="border-color: #28a745;">
                                    <p class="mb-0"><strong><i class="fa fa-graduation-cap"></i> Total de módulos pagados:</strong> ${contadorPagados}</p>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fa fa-lightbulb-o"></i> El estudiante puede ver sus módulos inscritos en la opción "Ver Módulos Inscritos"
                                    </small>
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
        }).fail(function(xhr, status, error) {
                console.error('❌ ERROR AJAX:', error);
                console.error('Status:', status);

                $('#contenedorModulos').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle fa-2x mb-2"></i>
                        <h5>Error al cargar módulos</h5>
                        <p>No se pudieron cargar los módulos del programa.</p>
                        <small class="d-block mt-2">Error: ${error}</small>
                    </div>
                `);

                console.error('No se pudieron cargar los módulos del programa. Error:', error);
        });
    }

    // Botón "Generarla ahora" dentro de una tarjeta de módulo sin orden: salta a la
    // pestaña "Orden de Pago" para que el usuario emita la orden antes de poder pagar.
    $(document).on('click', '.btn-ir-orden-pago', function() {
        $('#tab-orden-pago-link').tab('show');
    });

    // Exponer para que planpago.js pueda refrescar los costos de módulo
    // tras guardar/editar el plan de pago (el descuento afecta el costo mostrado)
    window.cargarModulosDisponibles = cargarModulosDisponibles;

    // ========================================
    // NOTA: La selección y validación de módulos múltiples
    // está manejada por matriculados-modulos-multiple.js
    // ========================================

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
        const idInscripcion = $(this).data('idinscripcion');
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');

        console.log('Cargando módulos inscritos para estudiante:', estudianteID);
        console.log('Inscripción:', idInscripcion, 'Programa:', programaID);

        // Actualizar título del modal con nombre del estudiante y programa
        $('#modulosEstudianteNombre').text(estudianteNombre + ' - ' + programaNombre);

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
                estudianteID: estudianteID,
                idInscripcion: idInscripcion,
                programaID: programaID
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
                                        <th style="color: white;">DOCENTE</th>
                                        <th class="text-center" style="color: white;">CALIFICACIÓN</th>
                                        <th class="text-center" style="color: white;">ESTADO</th>
                                        <th class="text-center" style="color: white;">COSTO</th>
                                        <th class="text-center" style="color: white;">N° VOUCHER</th>
                                        <th class="text-center" style="color: white;">FECHA PAGO</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    response.forEach((modulo, index) => {
                        const fechaFormateada = modulo.FechaInscripcion ?
                            new Date(modulo.FechaInscripcion).toLocaleDateString('es-BO') : 'N/A';

                        // Información del docente
                        let docenteInfo = modulo.NombreDocente || 'Sin asignar';
                        if (modulo.EspecialidadDocente) {
                            docenteInfo += `<br><small class="text-muted">${modulo.EspecialidadDocente}</small>`;
                        }

                        // Calificación
                        let notaDisplay = modulo.Nota !== null && modulo.Nota !== undefined
                            ? `<strong class="h5">${modulo.Nota}</strong>`
                            : '<span class="text-muted">-</span>';

                        // Badge de estado según aprobación
                        let estadoBadge = '';
                        if (modulo.EstadoAprobacion === 'APROBADO') {
                            estadoBadge = '<span class="badge badge-success badge-lg"><i class="fa fa-check-circle"></i> APROBADO</span>';
                        } else if (modulo.EstadoAprobacion === 'REPROBADO') {
                            estadoBadge = '<span class="badge badge-danger badge-lg"><i class="fa fa-times-circle"></i> REPROBADO</span>';
                        } else {
                            estadoBadge = '<span class="badge badge-secondary badge-lg"><i class="fa fa-clock-o"></i> SIN CALIFICACIÓN</span>';
                        }

                        tabla += `
                            <tr>
                                <td class="text-center"><strong>${index + 1}</strong></td>
                                <td><strong>${modulo.NombreModulo || 'N/A'}</strong></td>
                                <td class="text-center"><span class="badge badge-primary">${modulo.Codigo || 'N/A'}</span></td>
                                <td>${docenteInfo}</td>
                                <td class="text-center">${notaDisplay}</td>
                                <td class="text-center">${estadoBadge}</td>
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
                        <div class="alert alert-info mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <i class="fa fa-list"></i>
                                    Total de módulos inscritos: <strong>${response.length}</strong>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="badge badge-success mr-2"><i class="fa fa-check"></i> Aprobado: >= 51</span>
                                    <span class="badge badge-danger"><i class="fa fa-times"></i> Reprobado: < 51</span>
                                </div>
                            </div>
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
