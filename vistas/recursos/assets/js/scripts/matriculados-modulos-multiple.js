/**
 * Script para manejo de selección múltiple de módulos en Matriculados
 * Permite seleccionar 1, 2 o más módulos para registrar pagos simultáneamente
 */

// Array para almacenar módulos seleccionados (GLOBAL)
var modulosSeleccionados = [];

console.log('=== Script de selección múltiple de módulos cargado ===');

// Al cargar el documento
$(document).ready(function() {
    console.log('jQuery ready - Inicializando eventos de selección múltiple');

    // ========================================
    // BOTÓN: Seleccionar Todos los módulos pendientes
    // ========================================
    $('#btnSeleccionarTodos').on('click', function() {
        console.log('Botón "Seleccionar Todos" clickeado');
        $('.modulo-card.seleccionable').each(function() {
            const $card = $(this);
            const moduloID = $card.attr('data-moduloid');
            console.log('Procesando módulo:', moduloID);
            if (moduloID && !moduloEstaSel(moduloID)) {
                agregarModuloSeleccionado($card);
                $card.addClass('seleccionado');
                $card.find('.modulo-checkbox').prop('checked', true);
            }
        });
        actualizarVistaSeleccionados();
        console.log('Módulos seleccionados:', modulosSeleccionados.length);
    });

    // ========================================
    // BOTÓN: Limpiar selección
    // ========================================
    $('#btnLimpiarSeleccion').on('click', function() {
        modulosSeleccionados = [];
        $('.modulo-card').removeClass('seleccionado');
        $('.modulo-checkbox').prop('checked', false);
        actualizarVistaSeleccionados();
    });

    // ========================================
    // CLICK EN TARJETA DE MÓDULO
    // ========================================
    $(document).on('click', '.modulo-card.seleccionable', function(e) {
        // Prevenir que el checkbox interno dispare el evento dos veces
        if ($(e.target).hasClass('modulo-checkbox')) {
            e.stopPropagation();
            return;
        }

        // Stop propagation para prevenir múltiples eventos
        e.stopPropagation();

        // Obtener el ID del módulo directamente del atributo
        const $card = $(this);
        const moduloID = $card.attr('data-moduloid');

        console.log('=== CLICK EN TARJETA ===');
        console.log('Elemento clickeado:', this);
        console.log('Atributo data-moduloid:', $card.attr('data-moduloid'));
        console.log('jQuery .data("moduloid"):', $card.data('moduloid'));
        console.log('moduloID final:', moduloID);
        console.log('Datos de la tarjeta:', {
            codigo: $card.attr('data-codigo'),
            nombre: $card.attr('data-nombre'),
            costo: $card.attr('data-costo')
        });

        if (!moduloID) {
            console.error('❌ ERROR: No se pudo obtener el ID del módulo');
            console.log('HTML del elemento:', $card[0].outerHTML.substring(0, 200));
            return;
        }

        if (moduloEstaSel(moduloID)) {
            // Si ya está seleccionado, quitarlo
            console.log('Quitando módulo', moduloID);
            quitarModuloSeleccionado(moduloID);
            $card.removeClass('seleccionado');
            $card.find('.modulo-checkbox').prop('checked', false);
        } else {
            // Si no está seleccionado, agregarlo
            console.log('Agregando módulo', moduloID);
            agregarModuloSeleccionado($card);
            $card.addClass('seleccionado');
            $card.find('.modulo-checkbox').prop('checked', true);
        }

        actualizarVistaSeleccionados();
        console.log('Total módulos seleccionados:', modulosSeleccionados.length);
        console.log('Array actual:', modulosSeleccionados);
    });
});

/**
 * Verificar si un módulo ya está seleccionado
 */
function moduloEstaSel(moduloID) {
    return modulosSeleccionados.some(m => m.ModuloID == moduloID);
}

/**
 * Agregar módulo a la selección
 */
function agregarModuloSeleccionado(card) {
    const modulo = {
        ModuloID: card.attr('data-moduloid'),
        Codigo: card.attr('data-codigo'),
        NombreModulo: card.attr('data-nombre'),
        Costo: parseFloat(card.attr('data-costo')) || 0
    };

    console.log('Creando objeto módulo:', modulo);

    if (!modulo.ModuloID) {
        console.error('❌ ERROR: No se pudo crear el módulo - falta ModuloID');
        return;
    }

    if (!moduloEstaSel(modulo.ModuloID)) {
        modulosSeleccionados.push(modulo);
        console.log('✅ Módulo agregado exitosamente:', modulo);
    } else {
        console.log('⚠️ Módulo ya estaba en la lista');
    }
}

/**
 * Quitar módulo de la selección
 */
function quitarModuloSeleccionado(moduloID) {
    modulosSeleccionados = modulosSeleccionados.filter(m => m.ModuloID != moduloID);
}

/**
 * Actualizar vista de módulos seleccionados
 */
function actualizarVistaSeleccionados() {
    const contenedor = $('#modulosSeleccionadosInfo');
    const lista = $('#listaModulosSeleccionados');
    const contador = $('#contadorSeleccionados');
    const total = $('#totalAPagar');
    const contenedorCostos = $('#contenedorCostos');
    const camposCostos = $('#camposCostos');

    if (modulosSeleccionados.length === 0) {
        contenedor.hide();
        contenedorCostos.hide();
        return;
    }

    // Mostrar contenedor
    contenedor.show();
    contenedorCostos.show();
    contador.text(modulosSeleccionados.length);

    // Limpiar lista
    lista.empty();
    camposCostos.empty();

    let totalCosto = 0;

    // Generar lista de módulos seleccionados
    modulosSeleccionados.forEach((modulo, index) => {
        // Agregar a la lista visual
        lista.append(`
            <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                <div>
                    <span class="badge badge-primary">${modulo.Codigo}</span>
                    <strong class="ml-2">${modulo.NombreModulo}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-danger btn-quitar-modulo" data-moduloid="${modulo.ModuloID}">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `);

        // Agregar campo de costo para este módulo
        camposCostos.append(`
            <div class="col-md-6 mb-3">
                <label>
                    <strong>${modulo.Codigo}:</strong> ${modulo.NombreModulo}
                </label>
                <input type="number"
                       class="form-control input-costo-modulo"
                       name="costos[${modulo.ModuloID}]"
                       data-moduloid="${modulo.ModuloID}"
                       placeholder="0.00"
                       step="0.01"
                       min="0"
                       value="${modulo.Costo}"
                       required>
                <small class="text-muted">Costo en Bs.</small>
            </div>
        `);

        totalCosto += modulo.Costo;
    });

    // Actualizar total
    total.text('Bs. ' + totalCosto.toFixed(2));

    // Event listener para botones de quitar
    $('.btn-quitar-modulo').on('click', function() {
        const moduloID = $(this).data('moduloid');
        quitarModuloSeleccionado(moduloID);
        $(`.modulo-card[data-moduloid="${moduloID}"]`).removeClass('seleccionado');
        $(`.modulo-card[data-moduloid="${moduloID}"] .modulo-checkbox`).prop('checked', false);
        actualizarVistaSeleccionados();
    });

    // Event listener para actualizar total cuando cambia el costo
    $('.input-costo-modulo').on('input', function() {
        let nuevoTotal = 0;
        $('.input-costo-modulo').each(function() {
            nuevoTotal += parseFloat($(this).val()) || 0;
        });
        total.text('Bs. ' + nuevoTotal.toFixed(2));

        // Actualizar el costo en el array
        const moduloID = $(this).data('moduloid');
        const moduloIndex = modulosSeleccionados.findIndex(m => m.ModuloID == moduloID);
        if (moduloIndex !== -1) {
            modulosSeleccionados[moduloIndex].Costo = parseFloat($(this).val()) || 0;
        }
    });
}

/**
 * Generar HTML de tarjeta de módulo con checkbox
 */
function generarTarjetaModulo(modulo) {
    const pagado = parseInt(modulo.Pagado) === 1;
    const claseEstado = pagado ? 'pagado' : 'pendiente';
    const estadoTexto = pagado ? 'PAGADO' : 'PENDIENTE';
    const badge = pagado ? 'badge-success' : 'badge-danger';
    const checkbox = pagado ? '' : `
        <input type="checkbox" class="modulo-checkbox"
               style="position: absolute; top: 10px; left: 10px; width: 20px; height: 20px; cursor: pointer; z-index: 10;">
    `;

    let infoPago = '';
    if (pagado && modulo.FechaPago) {
        infoPago = `
            <div class="modulo-card-pago-info">
                <p><strong>Fecha:</strong> ${modulo.FechaPago}</p>
                ${modulo.CostoPagado ? `<p><strong>Costo:</strong> Bs. ${parseFloat(modulo.CostoPagado).toFixed(2)}</p>` : ''}
                ${modulo.NumeroVaucher ? `<p><strong>Voucher:</strong> ${modulo.NumeroVaucher}</p>` : ''}
            </div>
        `;
    }

    return `
        <div class="modulo-card ${claseEstado} ${pagado ? '' : 'seleccionable'}"
             data-moduloid="${modulo.ModuloID}"
             data-codigo="${modulo.Codigo}"
             data-nombre="${modulo.NombreModulo}"
             data-costo="${modulo.Costo || 0}">
            ${checkbox}
            <div class="modulo-card-header">
                <span class="modulo-card-codigo">${modulo.Codigo}</span>
                <span class="badge ${badge}">${estadoTexto}</span>
            </div>
            <div class="modulo-card-titulo">${modulo.NombreModulo}</div>
            ${modulo.NombreDocente ? `
                <div class="modulo-card-docente">
                    <i class="fa fa-user"></i> ${modulo.NombreDocente}
                </div>
            ` : ''}
            ${infoPago}
        </div>
    `;
}

/**
 * Validar formulario antes de enviar
 */
$(document).ready(function() {
    $('#formPagoModulo').on('submit', function(e) {
        console.log('=== VALIDACIÓN DE FORMULARIO ===');
        console.log('Módulos seleccionados:', modulosSeleccionados.length);
        console.log('Array:', modulosSeleccionados);

        if (modulosSeleccionados.length === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un módulo para registrar el pago');
            console.log('❌ Formulario bloqueado: No hay módulos seleccionados');
            return false;
        }

        // Validar que todos los módulos tengan costo
        let todosTienenCosto = true;
        $('.input-costo-modulo').each(function() {
            const valor = $(this).val();
            console.log('Costo del módulo:', valor);
            if (!valor || parseFloat(valor) <= 0) {
                todosTienenCosto = false;
            }
        });

        if (!todosTienenCosto) {
            e.preventDefault();
            alert('Debe ingresar el costo para todos los módulos seleccionados');
            console.log('❌ Formulario bloqueado: Faltan costos');
            return false;
        }

        console.log('✅ Validación exitosa - Enviando formulario');
        return true;
    });
});
