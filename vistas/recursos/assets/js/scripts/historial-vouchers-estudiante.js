/**
 * JavaScript para Historial de Vouchers del Estudiante
 * Vista: historialreciboestudiante.php
 */

console.log('=== HISTORIAL VOUCHERS ESTUDIANTE CARGADO ===');

$(document).ready(function() {
    console.log('jQuery ready - Iniciando carga de programas...');

    // Cargar programas del estudiante al iniciar
    cargarProgramasEstudiante();

    // Evento al cambiar el programa seleccionado
    $('#select_programa_vouchers').on('change', function() {
        const programaID = $(this).val();
        if (programaID) {
            cargarVouchersPrograma(programaID);
        } else {
            ocultarAreaVouchers();
        }
    });

    // Mostrar fecha actual
    mostrarFechaActual();
});

/**
 * Cargar programas inscritos del estudiante
 */
function cargarProgramasEstudiante() {
    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerProgramasEstudiante'
        },
        beforeSend: function() {
            $('#select_programa_vouchers')
                .prop('disabled', true)
                .html('<option value="">Cargando programas...</option>');
        },
        success: function(response) {
            if (response.success) {
                const programas = response.programas;
                let opciones = '<option value="">-- Seleccione un programa --</option>';

                if (programas && programas.length > 0) {
                    programas.forEach(function(programa) {
                        const grado = programa.GradoAcademico || '';
                        const nombre = programa.NombrePrograma || '';
                        opciones += `<option value="${programa.ProgramaID}">${grado} - ${nombre}</option>`;
                    });

                    // Obtener nombre del estudiante si está disponible
                    if (programas.length > 0) {
                        $('#estudiante_nombre').text('Estudiante Registrado');
                    }
                } else {
                    opciones = '<option value="">No hay programas inscritos</option>';
                }

                $('#select_programa_vouchers')
                    .html(opciones)
                    .prop('disabled', false);
            } else {
                console.error('Error al cargar programas:', response.error);
                swal({
                    title: 'Error',
                    text: response.error || 'No se pudieron cargar los programas',
                    icon: 'error',
                    button: 'Aceptar'
                });

                $('#select_programa_vouchers')
                    .html('<option value="">Error al cargar programas</option>')
                    .prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                icon: 'error',
                button: 'Aceptar'
            });

            $('#select_programa_vouchers')
                .html('<option value="">Error de conexión</option>')
                .prop('disabled', true);
        }
    });
}

/**
 * Cargar vouchers del programa seleccionado
 */
function cargarVouchersPrograma(programaID) {
    $.ajax({
        url: 'ajax/voucher.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerVouchersPrograma',
            programaID: programaID
        },
        beforeSend: function() {
            $('#contenido_vouchers').html(`
                <div class="text-center" style="padding: 3rem;">
                    <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                    <p style="margin-top: 1.5rem; color: #667eea; font-weight: 600; font-size: 1.1rem;">Cargando vouchers...</p>
                </div>
            `);
            $('#area_vouchers').show();
        },
        success: function(response) {
            if (response.success && response.vouchers) {
                mostrarVouchers(response.vouchers);
            } else {
                $('#contenido_vouchers').html(`
                    <div class="text-center" style="padding: 3rem; color: #B5B5C3;">
                        <i class="flaticon2-file" style="font-size: 3rem; color: #E4E6EF;"></i>
                        <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay vouchers registrados para este programa</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar vouchers:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudieron cargar los vouchers. Por favor, intente nuevamente.',
                icon: 'error',
                button: 'Aceptar'
            });
            ocultarAreaVouchers();
        }
    });
}

/**
 * Mostrar vouchers en tarjetas
 */
function mostrarVouchers(vouchers) {
    let html = '';

    if (vouchers.length === 0) {
        html = `
            <div class="text-center" style="padding: 3rem; color: #B5B5C3;">
                <i class="flaticon2-file" style="font-size: 3rem; color: #E4E6EF;"></i>
                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay vouchers registrados para este programa</p>
            </div>
        `;
    } else {
        vouchers.forEach(function(voucher, index) {
            const fechaPago = voucher.FechaPago ? formatearFecha(voucher.FechaPago) : 'N/A';
            const numeroVoucher = voucher.NumeroVoucher || 'S/N';
            const monto = parseFloat(voucher.MontoTotal || 0).toFixed(2);

            // Construir lista de módulos
            let modulosHTML = '';
            if (voucher.Modulos && voucher.Modulos.length > 0) {
                voucher.Modulos.forEach(function(modulo) {
                    modulosHTML += `<span class="modulo-badge">${modulo.NombreModulo}</span>`;
                });
            } else {
                modulosHTML = '<span style="color: #B5B5C3;">Sin módulos asociados</span>';
            }

            // Verificar si tiene imagen
            const tieneImagen = voucher.TieneImagen && voucher.TieneImagen === 'SI';
            const imagenURL = tieneImagen ? `ajax/obtener-voucher-imagen.php?id=${voucher.IdPago}&thumb=1` : '';
            const imagenURLGrande = tieneImagen ? `ajax/obtener-voucher-imagen.php?id=${voucher.IdPago}` : '';

            html += `
                <div class="voucher-card">
                    <div class="row">
                        <div class="col-md-4">
                            ${tieneImagen ? `
                                <div class="voucher-image-container" style="position: relative;">
                                    <div class="image-loader" style="display: flex; align-items: center; justify-content: center; height: 200px; background: #f8f9fa; border-radius: 10px;">
                                        <div class="kt-spinner kt-spinner--sm kt-spinner--brand"></div>
                                    </div>
                                    <img data-src="${imagenURL}"
                                         class="voucher-image lazy-image"
                                         alt="Voucher ${numeroVoucher}"
                                         style="display: none;"
                                         onclick="verImagenGrande('${imagenURLGrande}', '${numeroVoucher}', '${fechaPago}', 'Bs. ${monto}')">
                                </div>
                            ` : `
                                <div class="no-image-placeholder">
                                    <i class="flaticon2-image-file" style="font-size: 3rem;"></i>
                                    <p style="margin-top: 1rem; font-weight: 500;">Sin imagen adjunta</p>
                                </div>
                            `}
                        </div>
                        <div class="col-md-8">
                            <div style="padding: 1rem;">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <h6 style="color: #B5B5C3; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem;">Nº Voucher</h6>
                                        <h5 style="color: #464E5F; font-weight: 700; margin: 0;">
                                            <i class="flaticon2-tag" style="color: #667eea;"></i> ${numeroVoucher}
                                        </h5>
                                    </div>
                                    <div class="col-6">
                                        <h6 style="color: #B5B5C3; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem;">Fecha de Pago</h6>
                                        <h5 style="color: #464E5F; font-weight: 700; margin: 0;">
                                            <i class="flaticon2-calendar-8" style="color: #667eea;"></i> ${fechaPago}
                                        </h5>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6 style="color: #B5B5C3; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem;">Monto Total</h6>
                                        <h4 style="color: #11998e; font-weight: 700; margin: 0; font-size: 1.8rem;">
                                            Bs. ${monto}
                                        </h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <h6 style="color: #B5B5C3; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem;">
                                            <i class="flaticon2-layers"></i> Módulos Pagados (${voucher.Modulos ? voucher.Modulos.length : 0})
                                        </h6>
                                        <div>
                                            ${modulosHTML}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    $('#contenido_vouchers').html(html);

    // Cargar imágenes de forma lazy (una por una)
    cargarImagenesLazy();
}

/**
 * Cargar imágenes de forma lazy (progresiva)
 */
function cargarImagenesLazy() {
    const imagenes = $('.lazy-image');

    imagenes.each(function(index) {
        const $img = $(this);
        const $loader = $img.siblings('.image-loader');
        const src = $img.attr('data-src');

        // Cargar con un pequeño delay entre cada imagen
        setTimeout(function() {
            // Crear una nueva imagen para precargar
            const tempImg = new Image();

            tempImg.onload = function() {
                $img.attr('src', src);
                $img.fadeIn(300);
                $loader.fadeOut(300);
            };

            tempImg.onerror = function() {
                $loader.html(`
                    <div style="text-align: center; color: #B5B5C3;">
                        <i class="flaticon2-warning" style="font-size: 2rem;"></i>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Error al cargar imagen</p>
                    </div>
                `);
            };

            tempImg.src = src;
        }, index * 200); // 200ms de delay entre cada imagen
    });
}

/**
 * Ver imagen grande en modal
 */
function verImagenGrande(url, numeroVoucher, fecha, monto) {
    // Mostrar spinner de carga
    const $imgGrande = $('#imagen_voucher_grande');
    $imgGrande.hide();

    const infoHTML = `
        <div style="background: white; border-radius: 10px; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="row text-center">
                <div class="col-4">
                    <h6 style="color: #B5B5C3; font-size: 0.8rem; margin-bottom: 0.5rem;">Nº VOUCHER</h6>
                    <strong style="color: #464E5F;">${numeroVoucher}</strong>
                </div>
                <div class="col-4">
                    <h6 style="color: #B5B5C3; font-size: 0.8rem; margin-bottom: 0.5rem;">FECHA</h6>
                    <strong style="color: #464E5F;">${fecha}</strong>
                </div>
                <div class="col-4">
                    <h6 style="color: #B5B5C3; font-size: 0.8rem; margin-bottom: 0.5rem;">MONTO</h6>
                    <strong style="color: #11998e;">${monto}</strong>
                </div>
            </div>
        </div>
        <div id="modal-loader" style="display: flex; align-items: center; justify-content: center; min-height: 300px;">
            <div>
                <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                <p style="margin-top: 1rem; color: #667eea; font-weight: 600;">Cargando imagen completa...</p>
            </div>
        </div>
    `;

    $('#info_voucher').html(infoHTML);
    $('#modalImagenVoucher').modal('show');

    // Precargar la imagen completa
    const tempImg = new Image();
    tempImg.onload = function() {
        $imgGrande.attr('src', url);
        $('#modal-loader').fadeOut(200, function() {
            $imgGrande.fadeIn(300);
        });
    };
    tempImg.onerror = function() {
        $('#modal-loader').html(`
            <div style="text-align: center; color: #ee0979;">
                <i class="flaticon2-warning" style="font-size: 3rem;"></i>
                <p style="margin-top: 1rem; font-weight: 600;">Error al cargar la imagen completa</p>
            </div>
        `);
    };
    tempImg.src = url;
}

/**
 * Ocultar área de vouchers
 */
function ocultarAreaVouchers() {
    $('#area_vouchers').fadeOut();
    $('#contenido_vouchers').html('');
}

/**
 * Formatear fecha de DD/MM/YYYY
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';

    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();

    return `${dia}/${mes}/${anio}`;
}

/**
 * Mostrar fecha actual en el header
 */
function mostrarFechaActual() {
    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                   'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const ahora = new Date();
    const diaSemana = diasSemana[ahora.getDay()];
    const dia = ahora.getDate();
    const mes = meses[ahora.getMonth()];
    const anio = ahora.getFullYear();

    const fechaFormateada = `${diaSemana}, ${dia} de ${mes} de ${anio}`;
    $('#lafecha').text(fechaFormateada);
}
