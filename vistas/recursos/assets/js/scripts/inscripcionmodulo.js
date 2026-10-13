/**
 * Sistema de Inscripción a Módulos
 * Maneja la inscripción de estudiantes a módulos específicos
 */

$(document).ready(function() {
    console.log('Script de inscripción a módulos cargado correctamente');

    // Inicializar DataTable, salvo que la tabla venga vacía: la fila "No hay estudiantes
    // matriculados" usa colspan y DataTables no la acepta (igual que en cargarEstudiantes)
    // Desde Inscripción (matriculados?buscar=CI) llega filtrada por el estudiante recién inscrito
    const buscarInicial = new URLSearchParams(window.location.search).get('buscar') || '';
    if (buscarInicial) {
        // Se quita de la URL para que al recargar se vea la lista completa
        window.history.replaceState(null, '', window.location.pathname);
    }
    if (!$('#tablaMatriculadosBody tr:first td[colspan]').length) {
        $('#tablaMatriculados').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            order: [[0, 'asc']], // El modelo ya entrega las filas por fecha de inscripción descendente
            pageLength: 25,
            search: { search: buscarInicial }
        });
    }

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
        $('#previewFotoVoucher').html('<div class="vc-vista-vacia"><i class="fa fa-image"></i><span>Aquí se verá la foto del voucher para compararla con lo registrado</span></div>');

        // Mostrar loader en el resumen de pago
        $('#resumenPagoPrograma').html('<div class="gp-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando resumen...</div>');
        $('#cuotaPendienteInfo').html('');
        $('#seccionValidarCuota').hide();
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
                'Sin plan de pago del programa. El plan se define en Pre-Registro y queda ' +
                'registrado al validar el voucher de matrícula.</div>'
            );
            return;
        }

        const costoTotal = parseFloat(r.costoTotal) || 0;
        const totalPagado = parseFloat(r.totalPagado) || 0;
        const saldoPendiente = parseFloat(r.saldoPendiente) || 0;
        const porcentaje = costoTotal > 0 ? Math.min(100, Math.round((totalPagado / costoTotal) * 100)) : 0;

        // Progreso de cuotas: solo si el plan tiene número de cuotas definido
        const etiquetaCuotas = (r.numeroCuotasPlan)
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

    // Fecha "2026-10-13 17:39:30" o "2026-10-13" → "13/10/2026"
    function fechaCorta(valor) {
        const p = String(valor || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        return p ? p[3] + '/' + p[2] + '/' + p[1] : (valor || '-');
    }

    function escHtml(t) {
        return $('<div>').text(t == null ? '' : String(t)).html();
    }

    // Texto del botón de validar: incluye el monto de la orden
    function textoBotonValidar() {
        const monto = parseFloat($('#montoCuota').val()) || 0;
        return '<i class="fa fa-check-circle"></i> Validar pago' + (monto ? ' de ' + formatoBs(monto) : '');
    }

    // Aviso a ancho completo (sin orden, completado, sin plan) y formulario oculto
    function estadoCuotaSinValidar(icono, clase, titulo, texto) {
        $('#seccionValidarCuota').hide();
        $('#btnGuardarPagoCuota').prop('disabled', true);
        $('#cuotaPendienteInfo').html(
            '<div class="gp-section gp-estado-vacio ' + clase + '">' +
                '<i class="fa ' + icono + '"></i>' +
                '<h6>' + titulo + '</h6>' +
                '<p class="mb-0">' + texto + '</p>' +
            '</div>'
        );
    }

    function renderCuotaPendienteInfo(r, orden) {
        $('#montoCuota').val('');

        if (!r.tienePlan) {
            estadoCuotaSinValidar('fa-exclamation-triangle', 'is-alerta', 'Sin plan de pago',
                'El estudiante no tiene plan de pago del programa.');
            return;
        }

        if (r.completado) {
            estadoCuotaSinValidar('fa-check-circle', 'is-exito', 'Programa pagado en su totalidad',
                'No queda saldo pendiente; no hay cuotas por validar.');
            return;
        }

        if (!orden) {
            estadoCuotaSinValidar('fa-file-invoice', '', 'No hay una orden de pago pendiente',
                'Para validar un voucher primero debe generarse la orden de la cuota.<br>' +
                '<button type="button" class="btn btn-primary btn-sm mt-3 btn-ir-orden-pago"><i class="fa fa-plus"></i> Generar orden de pago</button>');
            return;
        }

        // Orden a validar: datos formales de lo que se está confirmando
        const numeroCuota = (parseInt(r.cuotasPagadasCount, 10) || 0) + 1;
        const textoCuota = 'Cuota ' + numeroCuota + (r.numeroCuotasPlan ? ' de ' + r.numeroCuotasPlan : '');
        const dato = function(etiqueta, valor) {
            return '<div class="vc-dato"><span>' + etiqueta + '</span><strong>' + valor + '</strong></div>';
        };

        $('#montoCuota').val(orden.MontoTotal);
        $('#cuotaPendienteInfo').html(
            '<div class="vc-orden">' +
                '<div class="vc-orden-cab">' +
                    '<div>' +
                        '<div class="vc-orden-eyebrow">Orden de pago a validar</div>' +
                        '<div class="vc-orden-titulo">' + textoCuota + '</div>' +
                    '</div>' +
                    '<div class="vc-orden-monto">' + formatoBs(orden.MontoTotal) + '</div>' +
                '</div>' +
                '<div class="vc-orden-datos">' +
                    dato('N° de orden', escHtml(orden.NumeroOrden)) +
                    dato('Generada', fechaCorta(orden.FechaGeneracion)) +
                    dato('Fecha límite', orden.FechaVencimiento ? fechaCorta(orden.FechaVencimiento) : '—') +
                    dato('Cuenta', escHtml(orden.NumeroCuentaDeposito || '—')) +
                    (orden.ModulosCubiertos ? dato('Módulo(s)', escHtml(orden.ModulosCubiertos)) : '') +
                '</div>' +
            '</div>'
        );

        if (!$('#fechaPago').val()) {
            $('#fechaPago').val(new Date().toLocaleDateString('sv-SE'));
        }
        $('#seccionValidarCuota').show();
        $('#btnGuardarPagoCuota').prop('disabled', false).html(textoBotonValidar());
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
                ? ' <a href="#" class="btn-ver-foto-cuota vc-ver-foto" data-idpago="' + c.Idpagomodulo + '" data-nvaucher="' + escHtml(c.nvaucher || '').replace(/"/g, '&quot;') + '" title="Ver foto del voucher"><i class="fa fa-image"></i> Ver</a>'
                : '';

            html += '<tr>' +
                '<td class="text-center"><strong>' + etiquetaCuota + '</strong></td>' +
                '<td class="text-right">' + formatoBs(c.montocuota) + '</td>' +
                '<td class="text-center">' + fechaCorta(c.fechapago) + '</td>' +
                '<td>' + escHtml(c.nvaucher || '-') + btnFoto + '</td>' +
                '<td class="text-center">' + window.chipEstadoPago(c.Estado) + '</td>' +
            '</tr>';
        });
        $('#tablaCuotasBody').html(html);
    }

    // Vista previa de la foto/archivo del voucher apenas se selecciona, para poder
    // verificar antes de guardar que es la imagen correcta.
    // Recuadro vacío de la vista previa (antes de elegir la foto)
    const VISTA_PREVIA_VACIA = '<div class="vc-vista-vacia"><i class="fa fa-image"></i>' +
        '<span>Aquí se verá la foto del voucher para compararla con lo registrado</span></div>';

    $(document).on('change', '#fmodulo', function() {
        const archivo = this.files && this.files[0];
        const $preview = $('#previewFotoVoucher');

        if (!archivo) {
            $preview.html(VISTA_PREVIA_VACIA);
            return;
        }

        if (archivo.type === 'application/pdf') {
            $preview.html('<div class="fv-numero"></div><div class="vc-vista-vacia"><i class="fa fa-file-pdf"></i><span>' + escHtml(archivo.name) + '</span></div>');
            pintarNumeroVoucher($preview.find('.fv-numero'), $('#numeroVaucher').val());
            return;
        }

        if (!archivo.type.startsWith('image/')) {
            $preview.html(VISTA_PREVIA_VACIA);
            return;
        }

        const lector = new FileReader();
        lector.onload = function(e) {
            $preview.html(
                '<div class="fv-numero"></div>' +
                '<img class="vc-vista-img" src="' + e.target.result + '" alt="Vista previa del voucher" title="Ver en grande" ' +
                'onclick="window.verFotoVoucherGrande(\'' + e.target.result + '\', $(\'#numeroVaucher\').val())">'
            );
            pintarNumeroVoucher($preview.find('.fv-numero'), $('#numeroVaucher').val());
        };
        lector.readAsDataURL(archivo);
    });

    // El N° digitado se actualiza sobre la vista previa mientras se escribe
    $(document).on('input', '#numeroVaucher', function() {
        const $numero = $('#previewFotoVoucher .fv-numero');
        if ($numero.length) pintarNumeroVoucher($numero, $(this).val());
    });

    // N° de voucher arriba de la foto, para comparar que se registró bien
    function pintarNumeroVoucher($destino, numero) {
        numero = String(numero || '').trim();
        $destino.toggleClass('is-vacio', !numero).html(numero
            ? '<span>N° de voucher registrado:</span> <strong>' + $('<div>').text(numero).html() + '</strong>'
            : '<i class="fa fa-exclamation-triangle"></i> <span>Aún no se escribió el N° de voucher</span>');
    }

    // Ver en grande la foto de un voucher (usado tanto en la vista previa antes de
    // guardar como en el historial de cuotas ya registradas). Modal de Bootstrap
    // propio (no SweetAlert) con tamaño de imagen fijo por CSS, igual que en
    // Historial de Módulos del estudiante, para que todas las fotos se vean del
    // mismo tamaño sin importar su resolución original.
    window.verFotoVoucherGrande = function(url, numeroVoucher) {
        const $numero = $('#numeroVoucherGrande');
        if (numeroVoucher !== undefined) {
            pintarNumeroVoucher($numero, numeroVoucher);
            $numero.show();
        } else {
            $numero.hide();
        }
        $('#imagenVoucherGrande').attr('src', url);
        $('#modalFotoVoucher').modal('show');
    };

    // Ver la foto de un voucher ya guardado (historial de cuotas)
    $(document).on('click', '.btn-ver-foto-cuota', function(e) {
        e.preventDefault();
        const idpago = $(this).data('idpago');
        window.verFotoVoucherGrande('ajax/obtener-voucher-imagen.php?id=' + idpago, String($(this).attr('data-nvaucher') || ''));
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

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validando...');

        $.ajax({
            url: 'ajax/pagomodulo.ajax.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.estado === 'exitoso') {
                    // Limpiar para la próxima cuota
                    $('#numeroVaucher').val('');
                    $('#fmodulo').val('');
                    $('#previewFotoVoucher').html(VISTA_PREVIA_VACIA);
                    Swal.fire({ type: 'success', title: 'Pago validado', text: resp.mensaje, timer: 1800, showConfirmButton: false })
                        .then(function() {
                            cargarCuotasPrograma(programaID);
                        });
                } else {
                    Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje });
                    $btn.prop('disabled', false).html(textoBotonValidar());
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo registrar el pago' });
                $btn.prop('disabled', false).html(textoBotonValidar());
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
            type: 'warning',
            title: esAbandono ? '¿Marcar como Abandono?' : '¿Reactivar estudiante?',
            html: esAbandono
                ? '<strong>' + nombre + '</strong> dejará de aparecer como activo en Matriculados. Su historial de pagos no se modifica.'
                : '<strong>' + nombre + '</strong> volverá a aparecer como activo en Matriculados.',
            showCancelButton: true,
            confirmButtonText: esAbandono ? 'Sí, marcar abandono' : 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.value) { // SweetAlert2 v8: no tiene isConfirmed
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
                        Swal.fire({ type: 'success', title: 'Listo', text: resp.mensaje, timer: 1600, showConfirmButton: false });
                        if (typeof window.refrescarTablaMatriculados === 'function') {
                            window.refrescarTablaMatriculados();
                        }
                    } else {
                        Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo actualizar el estado' });
                    }
                },
                error: function() {
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo actualizar el estado' });
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
        if (d.grupo) {
            $('#detalleTipoPago').append(' ', $('<span class="badge badge-light">').text(d.grupo).prepend('<i class="fa fa-users"></i> '));
        }
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

        // Encabezado: estudiante y programa en líneas separadas
        $('#modulosEstudianteNombre').text(estudianteNombre);
        $('#modulosProgramaNombre').text(programaNombre);
        $('#modulosLeyenda').text('');

        // Mostrar modal con loader
        $('#contenidoModulos').html('<div class="mi-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</div>');
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
                const notaMinima = window.NOTA_APROBACION || 76;
                $('#modulosLeyenda').html('<i class="fa fa-info-circle"></i> Nota mínima de aprobación: <strong>' + notaMinima + ' pts.</strong>');

                if (!response || response.length === 0) {
                    $('#contenidoModulos').html(`
                        <div class="mi-tabla-wrap mi-vacio">
                            <i class="fa fa-book-open"></i>
                            <strong>El programa no tiene módulos activos</strong><br>
                            Cuando se registren módulos en el programa, aparecerán aquí con su calificación.
                        </div>
                    `);
                    return;
                }

                const escapar = (texto) => $('<div>').text(texto == null ? '' : String(texto)).html();
                const fechaCorta = (f) => {
                    if (!f || f === '0000-00-00') return '';
                    const p = String(f).substring(0, 10).split('-');
                    return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : '';
                };
                const pad = (n) => String(n).padStart(2, '0');
                const ahora = new Date();
                const hoy = ahora.getFullYear() + '-' + pad(ahora.getMonth() + 1) + '-' + pad(ahora.getDate());

                // Periodo del módulo según sus fechas (mismo criterio que la lista de Módulos)
                const periodoModulo = (m) => {
                    const ini = m.FechaInicio ? String(m.FechaInicio).substring(0, 10) : '';
                    const fin = m.FechaFinal ? String(m.FechaFinal).substring(0, 10) : '';
                    if (!ini || !fin || ini === '0000-00-00' || fin === '0000-00-00') return '<span class="mi-tag">Sin fechas</span>';
                    if (hoy < ini) return '<span class="mi-tag is-alerta">Por iniciar</span>';
                    if (hoy > fin) return '<span class="mi-tag">Culminado</span>';
                    return '<span class="mi-tag is-info">En curso</span>';
                };

                // Resumen académico
                const total = response.length;
                const aprobados = response.filter(m => m.EstadoAprobacion === 'APROBADO').length;
                const reprobados = response.filter(m => m.EstadoAprobacion === 'REPROBADO').length;
                const sinNota = total - aprobados - reprobados;
                const notas = response
                    .filter(m => m.Nota !== null && m.Nota !== undefined && m.Nota !== '')
                    .map(m => parseFloat(m.Nota))
                    .filter(n => !isNaN(n));
                const promedio = notas.length ? (notas.reduce((a, b) => a + b, 0) / notas.length).toFixed(1) : '—';
                const avance = Math.round(aprobados / total * 100);

                let html = `
                    <div class="mi-stats">
                        <div class="mi-stat"><div class="mi-stat-label">Módulos</div><div class="mi-stat-valor">${total}</div></div>
                        <div class="mi-stat is-aprobado"><div class="mi-stat-label">Aprobados</div><div class="mi-stat-valor">${aprobados}</div></div>
                        <div class="mi-stat is-reprobado"><div class="mi-stat-label">Reprobados</div><div class="mi-stat-valor">${reprobados}</div></div>
                        <div class="mi-stat"><div class="mi-stat-label">Sin calificación</div><div class="mi-stat-valor">${sinNota}</div></div>
                        <div class="mi-stat is-promedio"><div class="mi-stat-label">Promedio</div><div class="mi-stat-valor">${promedio}</div></div>
                    </div>
                    <div class="mi-progreso">
                        <span>Avance académico</span>
                        <div class="progress"><div class="progress-bar" style="width: ${avance}%"></div></div>
                        <span><strong>${aprobados} de ${total}</strong> aprobados (${avance}%)</span>
                    </div>
                    <div class="mi-tabla-wrap">
                        <div class="table-responsive">
                            <table class="table mi-tabla">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Módulo</th>
                                        <th>Docente</th>
                                        <th class="text-center">Periodo</th>
                                        <th class="text-center">Cuota</th>
                                        <th class="text-center">Nota</th>
                                        <th class="text-center">Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                response.forEach((modulo, index) => {
                    // Módulo: código arriba, nombre y fechas debajo
                    const inicio = fechaCorta(modulo.FechaInicio);
                    const final = fechaCorta(modulo.FechaFinal);
                    const fechas = inicio && final
                        ? `<span class="mi-fechas"><i class="fa fa-calendar-alt"></i> ${inicio} al ${final}</span>`
                        : '';

                    const docente = modulo.NombreDocente
                        ? `${escapar(modulo.NombreDocente)}${modulo.EspecialidadDocente ? '<small>' + escapar(modulo.EspecialidadDocente) + '</small>' : ''}`
                        : '<span class="text-muted">Sin asignar</span>';

                    // Cuota que cubre el módulo (marcada al generar la orden de cuota)
                    let cuota = '<span class="mi-tag" title="Ninguna orden de cuota marca este módulo">Sin cuota</span>';
                    if (modulo.CuotaOrden) {
                        const pagada = modulo.CuotaEstado === 'CONFIRMADO';
                        const vencida = modulo.CuotaEstado === 'VENCIDO';
                        cuota = `<span class="mi-tag ${pagada ? 'is-exito' : (vencida ? 'is-peligro' : 'is-alerta')}">${pagada ? 'Pagada' : (vencida ? 'Vencida' : 'Pendiente')}</span>` +
                            `<span class="mi-sub">${escapar(modulo.CuotaOrden)}</span>`;
                    }

                    // Nota y resultado
                    const tieneNota = modulo.Nota !== null && modulo.Nota !== undefined && modulo.Nota !== '';
                    const claseNota = modulo.EstadoAprobacion === 'APROBADO' ? 'is-aprobado' : (modulo.EstadoAprobacion === 'REPROBADO' ? 'is-reprobado' : '');
                    const nota = tieneNota ? `<span class="mi-nota ${claseNota}">${escapar(modulo.Nota)}</span>` : '<span class="text-muted">—</span>';
                    let resultado = '<span class="mi-tag">Sin calificación</span>';
                    if (modulo.EstadoAprobacion === 'APROBADO') {
                        resultado = '<span class="mi-tag is-exito"><i class="fa fa-check"></i> Aprobado</span>';
                    } else if (modulo.EstadoAprobacion === 'REPROBADO') {
                        resultado = '<span class="mi-tag is-peligro"><i class="fa fa-times"></i> Reprobado</span>';
                    }

                    html += `
                        <tr>
                            <td class="mi-num">${index + 1}</td>
                            <td>
                                <span class="mi-codigo">${escapar(modulo.Codigo || '')}</span>
                                <span class="mi-nombre">${escapar(modulo.NombreModulo || 'N/A')}</span>
                                ${fechas}
                            </td>
                            <td class="mi-docente">${docente}</td>
                            <td class="text-center">${periodoModulo(modulo)}</td>
                            <td class="text-center">${cuota}</td>
                            <td class="text-center">${nota}</td>
                            <td class="text-center">${resultado}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                $('#contenidoModulos').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar módulos inscritos:', error);
                $('#contenidoModulos').html(`
                    <div class="mi-tabla-wrap mi-vacio" style="color: var(--mi-peligro);">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong>No se pudieron cargar los módulos</strong><br>
                        Cierre esta ventana e intente nuevamente.
                    </div>
                `);
            }
        });
    });

    console.log('Sistema de inscripción a módulos configurado correctamente');
});
