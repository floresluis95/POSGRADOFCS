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
        order: [[0, 'asc']], // El modelo ya entrega las filas por fecha de inscripción descendente
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
        $('#previewFotoVoucher').html('');

        // Mostrar loader en el resumen de pago
        $('#resumenPagoPrograma').html('<div class="gp-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando resumen...</div>');
        $('#cuotaPendienteInfo').html('');
        $('#tablaCuotasBody').html('<tr><td colspan="5" class="gp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        // Establecer fecha actual
        const hoy = new Date().toISOString().split('T')[0];
        $('#fechaPago').val(hoy);

        // Volver siempre a la pestaña "Orden de Pago" al abrir (es el primer paso del flujo)
        $('#tab-orden-pago-link').tab('show');

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
            if (typeof window.cargarClinica === 'function') {
                window.cargarClinica(contexto);
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
            $('#resumenPagoPrograma').html('<div class="gp-aviso">No se pudo obtener el ID de inscripción.</div>');
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
                $('#resumenPagoPrograma').html('<div class="gp-aviso">No se pudo cargar el resumen de pago</div>');
                return;
            }

            const r = resp.resumen;
            const ordenResp = respOrden[0];
            const orden = (ordenResp && ordenResp.success) ? ordenResp.orden : null;

            renderResumenPagoPrograma(r);
            renderCuotaPendienteInfo(r, orden);
            renderHistorialCuotas(resp.cuotas || [], r.numeroCuotasPlan);
        }).fail(function() {
            $('#resumenPagoPrograma').html('<div class="gp-aviso">No se pudo cargar el resumen de pago</div>');
        });
    }

    // Formato de moneda con separador de miles: Bs. 12,500.00
    function formatoBs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    window.formatoBs = formatoBs;

    // Chip de estado (PAGADO / PENDIENTE / RECHAZADO / CONFIRMADO...) con estilos del modal
    window.chipEstadoPago = function(estado) {
        const clases = { PAGADO: 'is-pagado', CONFIRMADO: 'is-pagado', PENDIENTE: 'is-pendiente', RECHAZADO: 'is-rechazado', VENCIDO: 'is-rechazado' };
        const texto = String(estado || '-');
        return '<span class="gp-chip ' + (clases[texto] || '') + '">' + texto.charAt(0) + texto.slice(1).toLowerCase() + '</span>';
    };

    function renderResumenPagoPrograma(r) {
        if (!r.tienePlan) {
            $('#resumenPagoPrograma').html(
                '<div class="gp-aviso"><i class="fa fa-exclamation-triangle"></i> ' +
                'Sin plan de pago del programa (normalmente porque eligió Plan Grupal en Pre-Registro). ' +
                'Use el botón "Plan Grupal" de la barra superior para armarle uno.</div>'
            );
            return;
        }

        const costoTotal = parseFloat(r.costoTotal) || 0;
        const totalPagado = parseFloat(r.totalPagado) || 0;
        const saldoPendiente = parseFloat(r.saldoPendiente) || 0;
        const porcentaje = costoTotal > 0 ? Math.min(100, Math.round((totalPagado / costoTotal) * 100)) : 0;

        // Progreso de cuotas: solo tiene sentido en Plan Regular con número de cuotas definido
        const etiquetaCuotas = (!r.cuotaUnica && r.numeroCuotasPlan)
            ? r.cuotasPagadasCount + ' de ' + r.numeroCuotasPlan + ' cuotas'
            : '';

        $('#resumenPagoPrograma').html(
            '<div class="gp-stats">' +
                '<div class="gp-stat">' +
                    '<div class="gp-stat-label">Costo del programa</div>' +
                    '<div class="gp-stat-valor">' + formatoBs(costoTotal) + '</div>' +
                '</div>' +
                '<div class="gp-stat is-pagado">' +
                    '<div class="gp-stat-label">Pagado</div>' +
                    '<div class="gp-stat-valor">' + formatoBs(totalPagado) + '</div>' +
                '</div>' +
                '<div class="gp-stat is-saldo">' +
                    '<div class="gp-stat-label">Saldo pendiente</div>' +
                    '<div class="gp-stat-valor">' + (r.completado ? '<span style="color:var(--gp-exito)"><i class="fa fa-check-circle"></i> Completado</span>' : formatoBs(saldoPendiente)) + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="gp-progreso">' +
                '<div class="progress">' +
                    '<div class="progress-bar' + (r.completado ? ' is-completo' : '') + '" role="progressbar" ' +
                        'style="width: ' + porcentaje + '%;" aria-valuenow="' + porcentaje + '" aria-valuemin="0" aria-valuemax="100"></div>' +
                '</div>' +
                '<span>' + porcentaje + '% pagado' + (etiquetaCuotas ? ' &middot; ' + etiquetaCuotas : '') + '</span>' +
            '</div>'
        );
    }

    function renderCuotaPendienteInfo(r, orden) {
        $('#montoCuota').val('');
        $('#btnGuardarPagoCuota').html('<i class="fa fa-save"></i> Guardar pago');

        if (!r.tienePlan) {
            $('#cuotaPendienteInfo').html('');
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        if (r.completado) {
            $('#cuotaPendienteInfo').html('<div class="gp-nota is-exito"><i class="fa fa-check-circle"></i> El programa ya está pagado en su totalidad.</div>');
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        if (!orden) {
            $('#cuotaPendienteInfo').html(
                '<div class="gp-nota is-neutra"><i class="fa fa-lock"></i> <span>No hay una orden de pago pendiente. ' +
                '<button type="button" class="btn btn-link btn-sm p-0 align-baseline btn-ir-orden-pago">Generarla ahora</button> en la pestaña "Orden de pago".</span></div>'
            );
            $('#btnGuardarPagoCuota').prop('disabled', true);
            return;
        }

        $('#montoCuota').val(orden.MontoTotal);
        $('#cuotaPendienteInfo').html(
            '<div class="gp-nota"><i class="fa fa-file-invoice"></i> <span>Orden N° ' + orden.NumeroOrden +
            ' pendiente por <strong>' + formatoBs(orden.MontoTotal) + '</strong>. Registre el voucher para confirmarla.</span></div>'
        );
        $('#btnGuardarPagoCuota').prop('disabled', false);
    }

    function renderHistorialCuotas(cuotas, numeroCuotasPlan) {
        if (!cuotas || cuotas.length === 0) {
            $('#tablaCuotasBody').html('<tr><td colspan="5" class="gp-vacio">Sin cuotas registradas</td></tr>');
            return;
        }

        let html = '';
        cuotas.forEach(function(c) {
            const etiquetaCuota = (c.NumeroCuota && numeroCuotasPlan)
                ? (c.NumeroCuota + ' de ' + numeroCuotasPlan)
                : (c.NumeroCuota || '-');

            const tieneImagen = c.TieneImagen === 'SI';
            const btnFoto = tieneImagen
                ? ' <a href="#" class="btn-ver-foto-cuota ml-1" data-idpago="' + c.Idpagomodulo + '" title="Ver foto del voucher"><i class="fa fa-image"></i></a>'
                : '';

            html += '<tr>' +
                '<td class="text-center">' + etiquetaCuota + '</td>' +
                '<td class="text-right">' + formatoBs(c.montocuota) + '</td>' +
                '<td class="text-center">' + (c.fechapago || '-') + '</td>' +
                '<td>' + (c.nvaucher || '-') + btnFoto + '</td>' +
                '<td class="text-center">' + window.chipEstadoPago(c.Estado) + '</td>' +
            '</tr>';
        });
        $('#tablaCuotasBody').html(html);
    }

    // Vista previa de la foto/archivo del voucher apenas se selecciona, para poder
    // verificar antes de guardar que es la imagen correcta.
    $(document).on('change', '#fmodulo', function() {
        const archivo = this.files && this.files[0];
        const $preview = $('#previewFotoVoucher');

        if (!archivo) {
            $preview.html('');
            return;
        }

        if (archivo.type === 'application/pdf') {
            $preview.html('<span class="text-muted small"><i class="fa fa-file-pdf-o"></i> ' + archivo.name + '</span>');
            return;
        }

        if (!archivo.type.startsWith('image/')) {
            $preview.html('');
            return;
        }

        const lector = new FileReader();
        lector.onload = function(e) {
            $preview.html(
                '<img src="' + e.target.result + '" alt="Vista previa del voucher" ' +
                'style="max-width:100%; max-height:150px; border:1px solid #ddd; border-radius:4px; cursor:pointer;" ' +
                'onclick="window.verFotoVoucherGrande(\'' + e.target.result + '\')">'
            );
        };
        lector.readAsDataURL(archivo);
    });

    // Ver en grande la foto de un voucher (usado tanto en la vista previa antes de
    // guardar como en el historial de cuotas ya registradas). Modal de Bootstrap
    // propio (no SweetAlert) con tamaño de imagen fijo por CSS, igual que en
    // Historial de Módulos del estudiante, para que todas las fotos se vean del
    // mismo tamaño sin importar su resolución original.
    window.verFotoVoucherGrande = function(url) {
        $('#imagenVoucherGrande').attr('src', url);
        $('#modalFotoVoucher').modal('show');
    };

    // Ver la foto de un voucher ya guardado (historial de cuotas)
    $(document).on('click', '.btn-ver-foto-cuota', function(e) {
        e.preventDefault();
        const idpago = $(this).data('idpago');
        window.verFotoVoucherGrande('ajax/obtener-voucher-imagen.php?id=' + idpago);
    });

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

        // jQuery convierte automáticamente el JSON de data-detalle en objeto
        const d = $(this).data('detalle') || {};
        const valor = function(v) { return v ? v : 'No registrado'; };

        // Encabezado: estudiante y estado de la inscripción
        $('#detalleEstudianteNombre').text(d.nombre);
        $('#detalleEstudianteCI').text(d.ci);
        $('#detalleEstado')
            .text(d.estado)
            .removeClass('badge-success badge-danger')
            .addClass(d.estado === 'ACTIVO' ? 'badge-success' : 'badge-danger');

        // Contacto
        $('#detalleCelular').text(valor(d.celular));
        $('#detalleCorreo').text(valor(d.correo));
        $('#detalleProfesion').text(valor(d.profesion));

        // Programa
        $('#detalleProgramaNombre').text(d.grado + ' EN ' + d.programa);
        $('#detalleCodigo').text(valor(d.codigo));
        $('#detalleVersion').text(valor(d.version));
        $('#detalleSede').text(valor(d.sede));
        $('#detalleTramite').text(valor(d.tramite));

        // Matrícula
        $('#detalleFecha').text(d.fecha);
        $('#detalleTipoPago').html(d.pagoCompleto == 1
            ? '<span class="badge badge-success"><i class="fa fa-check-circle"></i> PAGO COMPLETO</span>'
            : '<span class="badge badge-warning"><i class="fa fa-money"></i> SOLO MATRÍCULA</span>');
        $('#detalleVoucher').text(valor(d.voucher));
        $('#detalleCosto').text('Bs. ' + d.costo);
        $('#detalleDescuento').text(d.descuento
            ? 'Bs. ' + d.descuento + (d.porcentaje ? ' (' + d.porcentaje + '%)' : '')
            : 'Sin descuento');
        $('#detalleMontoPagado').text(d.montoPagado ? 'Bs. ' + d.montoPagado : '-');

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
