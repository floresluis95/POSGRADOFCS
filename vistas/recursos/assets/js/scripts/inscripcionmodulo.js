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

        // Limpiar formulario de pago de cuota
        $('input[name="numeroVaucher"]').val('');
        $('#fmodulo').val('');
        $('#montoCuota').val('');

        // Mostrar loader en el resumen de pago
        $('#resumenPagoPrograma').html('<div class="text-center p-2"><i class="fa fa-spinner fa-spin"></i> Cargando resumen...</div>');
        $('#cuotaPendienteInfo').html('');
        $('#tablaCuotasBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        // Establecer fecha actual
        const hoy = new Date().toISOString().split('T')[0];
        $('#fechaPago').val(hoy);

        // Volver siempre a la pestaña "Cuotas del Programa" al abrir
        $('#tab-pago-modulos-link').tab('show');

        // Mostrar modal
        $('#modalInscribirModulo').modal('show');

        // Cargar cuotas del programa y orden de pago DESPUÉS de mostrar el modal
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

            if (typeof window.cargarOrdenPagoModulos === 'function') {
                window.cargarOrdenPagoModulos(contexto);
            }
            cargarCuotasPrograma(programaID);
        }, 300);
    });

    // ========================================
    // CARGAR RESUMEN Y HISTORIAL DE CUOTAS DEL PROGRAMA
    // (las cuotas ya no están ligadas a módulos: monto y número son libres)
    // ========================================
    function cargarCuotasPrograma(programaID) {
        const idinscripcion = $('#idinscripcion').val();

        if (!idinscripcion) {
            $('#resumenPagoPrograma').html('<div class="alert alert-danger mb-0">No se pudo obtener el ID de inscripción.</div>');
            return;
        }

        $('#btnGuardarPagoCuota').prop('disabled', true);

        $.when(
            $.ajax({
                url: 'ajax/pagomodulo.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerResumenCuotas', idinscripcion: idinscripcion, programaID: programaID },
                dataType: 'json'
            }),
            $.ajax({
                url: 'ajax/ordenpago.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerOrdenPendienteCuotaPrograma', idinscripcion: idinscripcion },
                dataType: 'json'
            })
        ).done(function(respResumen, respOrden) {
            const resp = respResumen[0];
            if (!resp || !resp.success) {
                $('#resumenPagoPrograma').html('<div class="alert alert-danger mb-0">No se pudo cargar el resumen de pago</div>');
                return;
            }

            const r = resp.resumen;
            const ordenResp = respOrden[0];
            const orden = (ordenResp && ordenResp.success) ? ordenResp.orden : null;

            renderResumenPagoPrograma(r);
            renderCuotaPendienteInfo(r, orden);
            renderHistorialCuotas(resp.cuotas || [], r.numeroCuotasPlan);
        }).fail(function() {
            $('#resumenPagoPrograma').html('<div class="alert alert-danger mb-0">No se pudo cargar el resumen de pago</div>');
        });
    }

    function renderResumenPagoPrograma(r) {
        if (!r.tienePlan) {
            $('#resumenPagoPrograma').html(
                '<div class="alert alert-warning mb-0"><i class="fa fa-exclamation-triangle"></i> ' +
                'Este estudiante todavía no tiene un Plan de Pago del Programa (normalmente porque eligió Plan Grupal ' +
                'en Pre-Registro). Use el botón "Plan Grupal" en la barra superior para armarle uno.</div>'
            );
            return;
        }

        const badgeCompletado = r.completado
            ? '<span class="badge badge-success ml-2"><i class="fa fa-check-circle"></i> COMPLETADO</span>' : '';

        // Progreso de cuotas: solo tiene sentido en Plan Regular con número de cuotas definido
        let filaCuotas = '';
        if (!r.cuotaUnica && r.numeroCuotasPlan) {
            filaCuotas = '<div class="row text-center mt-2">' +
                '<div class="col-md-12"><small class="text-muted d-block">Progreso de cuotas</small>' +
                '<strong>' + r.cuotasPagadasCount + ' de ' + r.numeroCuotasPlan + ' cuotas pagadas</strong></div>' +
            '</div>';
        }

        $('#resumenPagoPrograma').html(
            '<div class="row text-center">' +
                '<div class="col-md-4"><small class="text-muted d-block">Costo del programa</small>' +
                    '<strong>Bs. ' + parseFloat(r.costoTotal).toFixed(2) + '</strong></div>' +
                '<div class="col-md-4"><small class="text-muted d-block">Pagado</small>' +
                    '<strong class="text-success">Bs. ' + parseFloat(r.totalPagado).toFixed(2) + '</strong></div>' +
                '<div class="col-md-4"><small class="text-muted d-block">Saldo pendiente</small>' +
                    '<strong class="text-danger">Bs. ' + parseFloat(r.saldoPendiente).toFixed(2) + '</strong>' + badgeCompletado + '</div>' +
            '</div>' + filaCuotas
        );
    }

    function renderCuotaPendienteInfo(r, orden) {
        $('#montoCuota').val('');
        $('#btnGuardarPagoCuota').html('<i class="fa fa-save"></i> Guardar Pago');

        if (!r.tienePlan) {
            $('#cuotaPendienteInfo').html('');
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        if (r.completado) {
            $('#cuotaPendienteInfo').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> El programa ya está pagado en su totalidad.</div>');
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        if (!orden) {
            $('#cuotaPendienteInfo').html(
                '<div class="alert alert-light border"><i class="fa fa-lock"></i> No hay una orden de pago pendiente. ' +
                '<button type="button" class="btn btn-link btn-sm p-0 align-baseline btn-ir-orden-pago">Generarla ahora</button> en la pestaña "Orden de Pago".</div>'
            );
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        $('#montoCuota').val(orden.MontoTotal);
        $('#cuotaPendienteInfo').html(
            '<div class="alert alert-info"><i class="fa fa-file-invoice"></i> Orden de pago N° ' + orden.NumeroOrden +
            ' pendiente por <strong>Bs. ' + parseFloat(orden.MontoTotal).toFixed(2) + '</strong>. Registre el voucher para confirmarla.</div>'
        );
        $('#btnGuardarPagoCuota').prop('disabled', false);
    }

    function renderHistorialCuotas(cuotas, numeroCuotasPlan) {
        if (!cuotas || cuotas.length === 0) {
            $('#tablaCuotasBody').html('<tr><td colspan="5" class="text-center text-muted py-3">Sin cuotas registradas</td></tr>');
            return;
        }

        const badgeEstado = { PAGADO: 'badge-success', PENDIENTE: 'badge-warning', RECHAZADO: 'badge-danger' };

        let html = '';
        cuotas.forEach(function(c) {
            const etiquetaCuota = (c.NumeroCuota && numeroCuotasPlan)
                ? (c.NumeroCuota + ' de ' + numeroCuotasPlan)
                : (c.NumeroCuota || '-');

            html += '<tr>' +
                '<td class="text-center">' + etiquetaCuota + '</td>' +
                '<td class="text-center">Bs. ' + parseFloat(c.montocuota).toFixed(2) + '</td>' +
                '<td class="text-center">' + (c.fechapago || '-') + '</td>' +
                '<td>' + (c.nvaucher || '-') + '</td>' +
                '<td class="text-center"><span class="badge ' + (badgeEstado[c.Estado] || 'badge-secondary') + '">' + c.Estado + '</span></td>' +
            '</tr>';
        });
        $('#tablaCuotasBody').html(html);
    }

    // Botón "Generarla ahora" cuando no hay orden pendiente: salta a la pestaña "Orden de Pago"
    $(document).on('click', '.btn-ir-orden-pago', function() {
        $('#tab-orden-pago-link').tab('show');
    });

    // Envío del pago de la cuota (con voucher)
    $('#formPagoModulo').on('submit', function(e) {
        e.preventDefault();

        const idinscripcion = $('#idinscripcion').val();
        const programaID = $('#programaID').val();
        const $btn = $('#btnGuardarPagoCuota');

        const formData = new FormData(this);
        formData.append('accion', 'registrarPagoModulo');
        formData.append('idinscripcion', idinscripcion);

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/pagomodulo.ajax.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.estado === 'exitoso') {
                    Swal.fire({ icon: 'success', title: 'Pago registrado', text: resp.mensaje, timer: 1800, showConfirmButton: false })
                        .then(function() {
                            cargarCuotasPrograma(programaID);
                        });
                } else {
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje });
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Pago');
            }
        });
    });

    // Exponer para que ordenpagomodulos.js refresque el resumen tras generar una orden
    window.cargarCuotasPrograma = cargarCuotasPrograma;

    // ========================================
    // MARCAR / REVERTIR ABANDONO DE UNA INSCRIPCIÓN
    // No borra ni toca el historial de pagos (pagoprograma/pagomodulo); solo cambia
    // estudianteprograma.Estado, lo que la saca (o la vuelve a meter) de la lista de
    // "Activos" en Matriculados y permite identificarla en el reporte de pagos.
    // ========================================
    $(document).on('click', '.btn-marcar-abandono', function(e) {
        e.preventDefault();

        const idinscripcion = $(this).data('idinscripcion');
        const nombre = $(this).data('estudiante-nombre');
        const nuevoEstado = $(this).data('nuevo-estado');
        const esAbandono = nuevoEstado === 'ABANDONO';

        Swal.fire({
            icon: 'warning',
            title: esAbandono ? '¿Marcar como Abandono?' : '¿Reactivar estudiante?',
            html: esAbandono
                ? '<strong>' + nombre + '</strong> dejará de aparecer como activo en Matriculados. Su historial de pagos no se modifica.'
                : '<strong>' + nombre + '</strong> volverá a aparecer como activo en Matriculados.',
            showCancelButton: true,
            confirmButtonText: esAbandono ? 'Sí, marcar abandono' : 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: 'ajax/inscripcionmodulo.ajax.php',
                type: 'POST',
                data: {
                    accion: 'actualizarEstadoInscripcion',
                    idinscripcion: idinscripcion,
                    nuevoEstado: nuevoEstado
                },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: resp.mensaje, timer: 1600, showConfirmButton: false });
                        if (typeof window.refrescarTablaMatriculados === 'function') {
                            window.refrescarTablaMatriculados();
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo actualizar el estado' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado' });
                }
            });
        });
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
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    response.forEach((modulo, index) => {
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
