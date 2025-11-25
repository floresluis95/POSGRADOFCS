<?php
require_once "controladores/modulo.controlador.php";
require_once "modelos/modulo.modelo.php";
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Obtener lista de docentes activos para el select
$docentes = ModuloModelo::ListarDocentesActivosModelo();
?>
 <?php
          $NavBar = new FuncionesControladores();
          $NavBar->NavBarControlador();
        ?>
 <button class="kt-aside-close" id="kt_aside_close_btn">
          <i class="la la-close"></i>
        </button>

        <?php
          $Sidebar = new FuncionesControladores();
          $Sidebar->SidebarControlador();
        ?>


<div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">

        <!-- Título de la Página -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title" style="color: white;">
                                <i class="fa fa-book"></i> GESTIÓN DE MÓDULOS POR PROGRAMA
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Selección de Programa -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                        <div class="kt-portlet__head-label">
                            <h4 style="color: white; margin: 0;">
                                <i class="fa fa-graduation-cap"></i> CARGAR MÓDULOS DE UN PROGRAMA
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label"><strong>SELECCIONAR PROGRAMA:</strong></label>
                            <div class="col-lg-7">
                                <select class="form-control select2-programa" id="selectPrograma" style="width: 100%;">
                                    <option value="">Buscar programa...</option>
                                    <?php
                                    $programas = ProgramasModelos::ListaProgramaModelo();
                                    foreach ($programas as $prog) {
                                        $programaID = htmlspecialchars($prog['ProgramaID'] ?? '', ENT_QUOTES);
                                        $nombrePrograma = htmlspecialchars($prog['NombrePrograma'] ?? '', ENT_QUOTES);
                                        $codigo = htmlspecialchars($prog['Codigo'] ?? '', ENT_QUOTES);
                                        $grado = htmlspecialchars($prog['GradoAcademico'] ?? '', ENT_QUOTES);
                                        $modulos = htmlspecialchars($prog['Modulos'] ?? 0, ENT_QUOTES);

                                        echo '<option value="' . $programaID . '"
                                                    data-nombre="' . $nombrePrograma . '"
                                                    data-codigo="' . $codigo . '"
                                                    data-grado="' . $grado . '"
                                                    data-modulos="' . $modulos . '">';
                                        echo $nombrePrograma . ' - ' . $grado . ' (' . $codigo . ')';
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary btn-block" id="btnAbrirModalModulos" disabled>
                                    <i class="fa fa-plus"></i> Agregar Módulos
                                </button>
                            </div>
                        </div>

                      <div id="infoProgramaSeleccionadoNuevo" style="display: none;" class="card border-info shadow-sm mt-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Programa Seleccionado</h5>
    </div>
    
    <div class="card-body">
        
        <h4 class="card-title text-primary" id="infoNombrePrograma">
            </h4>
        <hr>
        
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Grado:</dt>
            <dd class="col-sm-8 fw-bold" id="infoGradoPrograma">
                </dd>

            <dt class="col-sm-4 text-muted">N° de Módulos:</dt>
            <dd class="col-sm-8">
                <span id="infoNumModulosPrograma" class="badge bg-success fs-6">
                    </span>
            </dd>
            
            </dl>
        
    </div>
    
    <div class="card-footer text-end bg-light">
        <small class="text-muted">Detalles del programa seleccionado.</small>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Módulos Registrados -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h4>
                                <i class="fa fa-list"></i> Módulos Registrados
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaModulos">
                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <tr>
                                        <th class="text-center" style="color: white;">#</th>
                                        <th class="text-center" style="color: white;">CÓDIGO</th>
                                        <th style="color: white;">NOMBRE MÓDULO</th>
                                        <th style="color: white;">PROGRAMA</th>
                                        <th class="text-center" style="color: white;">COD. PROGRAMA</th>
                                        <th style="color: white;">DOCENTE ASIGNADO</th>
                                        <th class="text-center" style="color: white;">ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $listar = new ModuloControlador();
                                    $listar->ListarModulosPorProgramaControlador();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal para Registrar Módulos del Programa -->
<div class="modal fade" id="modalRegistrarModulos" tabindex="-1" role="dialog" style="z-index: 1050;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">
                    <i class="fa fa-book"></i> REGISTRAR MÓDULOS DEL PROGRAMA
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
            </div>

            <form method="POST" id="formRegistrarModulosPorPrograma">
                <div class="modal-body" style="overflow: visible !important;">
                    <!-- Información del Programa -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Programa:</strong> <span id="modalNombrePrograma"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Código:</strong> <span id="modalCodigoPrograma"></span>
                            </div>
                            <div class="col-md-4">
                                <strong>N° Módulos:</strong> <span id="modalNumModulos" class="badge badge-primary"></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="programaID" id="modalProgramaID">
                    <input type="hidden" name="totalModulos" id="modalTotalModulos">

                    <hr>

                    <!-- Contenedor dinámico de módulos -->
                    <div id="contenedorModulosModal">
                        <!-- Se generarán dinámicamente aquí -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="registrarModulosPorPrograma" class="btn btn-success btn-lg">
                        <i class="fa fa-save"></i> Guardar Módulos
                    </button>
                </div>

                <?php
                $registrarProg = new ModuloControlador();
                $registrarProg->RegistrarModulosPorProgramaControlador();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="vistas/recursos/sweetalert.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Pasar datos de docentes de PHP a JavaScript
const docentesDisponibles = <?php echo json_encode($docentes); ?>;

$(document).ready(function() {
    console.log('=== SISTEMA DE MÓDULOS INICIADO ===');
    console.log('jQuery cargado:', typeof $ !== 'undefined');
    console.log('Select2 disponible:', typeof $.fn.select2 !== 'undefined');
    console.log('Docentes disponibles:', docentesDisponibles.length);

    // ========================================
    // FUNCIÓN PARA CONVERTIR NÚMEROS A ROMANOS
    // ========================================
    function convertirANumeroRomano(num) {
        const valores = [
            { valor: 1000, simbolo: 'M' },
            { valor: 900, simbolo: 'CM' },
            { valor: 500, simbolo: 'D' },
            { valor: 400, simbolo: 'CD' },
            { valor: 100, simbolo: 'C' },
            { valor: 90, simbolo: 'XC' },
            { valor: 50, simbolo: 'L' },
            { valor: 40, simbolo: 'XL' },
            { valor: 10, simbolo: 'X' },
            { valor: 9, simbolo: 'IX' },
            { valor: 5, simbolo: 'V' },
            { valor: 4, simbolo: 'IV' },
            { valor: 1, simbolo: 'I' }
        ];

        let resultado = '';
        for (let i = 0; i < valores.length; i++) {
            while (num >= valores[i].valor) {
                resultado += valores[i].simbolo;
                num -= valores[i].valor;
            }
        }
        return resultado;
    }

    // ========================================
    // INICIALIZAR SELECT2 PARA PROGRAMAS
    // ========================================
    console.log('Inicializando Select2 para programas...');
    try {
        $('.select2-programa').select2({
            placeholder: 'Buscar programa...',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
        console.log('✓ Select2 para programas inicializado correctamente');
    } catch (error) {
        console.error('✗ Error al inicializar Select2:', error);
    }

    // ========================================
    // AL SELECCIONAR UN PROGRAMA
    // ========================================
    function manejarSeleccionPrograma() {
        console.log('>>> Función manejarSeleccionPrograma ejecutada');
        try {
            const selectedOption = $('#selectPrograma').find('option:selected');
            const programaID = $('#selectPrograma').val();

            console.log('Programa seleccionado ID:', programaID);

            if (!programaID || programaID === '') {
                console.log('No hay programa seleccionado, ocultando información');
                $('#infoProgramaSeleccionadoNuevo').hide();
                $('#btnAbrirModalModulos').prop('disabled', true);
                return;
            }

            const nombrePrograma = selectedOption.data('nombre');
            const codigoPrograma = selectedOption.data('codigo');
            const gradoPrograma = selectedOption.data('grado');
            const numModulos = parseInt(selectedOption.data('modulos')) || 0;

            console.log('Datos del programa:', {
                id: programaID,
                nombre: nombrePrograma,
                codigo: codigoPrograma,
                grado: gradoPrograma,
                modulos: numModulos
            });

            // Actualizar información en la interfaz
            $('#infoNombrePrograma').text(nombrePrograma || 'N/A');
            $('#infoGradoPrograma').text(gradoPrograma || 'N/A');
            $('#infoNumModulosPrograma').text(numModulos);

            // Guardar datos en el botón para usarlos en el modal
            $('#btnAbrirModalModulos').data('programaid', programaID);
            $('#btnAbrirModalModulos').data('nombre', nombrePrograma);
            $('#btnAbrirModalModulos').data('codigo', codigoPrograma);
            $('#btnAbrirModalModulos').data('modulos', numModulos);

            // Habilitar botón y mostrar info
            console.log('Habilitando botón "Agregar Módulos"');
            $('#btnAbrirModalModulos').prop('disabled', false);
            $('#infoProgramaSeleccionadoNuevo').slideDown(300);

            console.log('✓ Programa seleccionado correctamente');

        } catch (error) {
            console.error('✗ Error al seleccionar programa:', error);
            console.error('Stack:', error.stack);
        }
    }

    // Eventos de Select2
    console.log('Registrando eventos de Select2...');
    $('#selectPrograma').on('select2:select', function(e) {
        console.log('>>> Evento select2:select disparado');
        console.log('Datos del evento:', e.params.data);
        setTimeout(function() {
            manejarSeleccionPrograma();
        }, 50);
    });

    $('#selectPrograma').on('select2:clear', function(e) {
        console.log('>>> Evento select2:clear disparado');
        $('#infoProgramaSeleccionadoNuevo').hide();
        $('#btnAbrirModalModulos').prop('disabled', true);
    });
    console.log('✓ Eventos de Select2 registrados');

    // ========================================
    // ABRIR MODAL PARA REGISTRAR MÓDULOS
    // ========================================
    $('#btnAbrirModalModulos').on('click', function() {
        console.log('>>> Botón "Agregar Módulos" clickeado');

        const programaID = $(this).data('programaid');
        const nombrePrograma = $(this).data('nombre');
        const codigoPrograma = $(this).data('codigo');
        const numModulos = parseInt($(this).data('modulos'));

        console.log('Datos del programa para el modal:', {
            programaID,
            nombrePrograma,
            codigoPrograma,
            numModulos
        });

        if (numModulos <= 0) {
            console.log('⚠ Programa sin módulos configurados');
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Este programa no tiene módulos configurados. Configure primero el número de módulos en la tabla de programas.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // Actualizar información en el modal
        $('#modalNombrePrograma').text(nombrePrograma);
        $('#modalCodigoPrograma').text(codigoPrograma);
        $('#modalNumModulos').text(numModulos);
        $('#modalProgramaID').val(programaID);
        $('#modalTotalModulos').val(numModulos);

        console.log('Generando campos para', numModulos, 'módulos...');

        // Generar campos de módulos
        generarCamposModulosModal(numModulos);

        console.log('Abriendo modal...');

        // Abrir modal
        $('#modalRegistrarModulos').modal('show');

        console.log('✓ Modal abierto (Bootstrap show llamado)');
    });

    // Inicializar Select2 cuando el modal se ha mostrado completamente
    $('#modalRegistrarModulos').on('shown.bs.modal', function () {
        console.log('✓ Modal mostrado completamente (evento shown.bs.modal)');
        console.log('Inicializando Select2 de docentes...');

        $('.select2-docente').each(function() {
            $(this).select2({
                placeholder: '-- Seleccionar Docente (Opcional) --',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron docentes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                dropdownParent: $('#modalRegistrarModulos .modal-content'),
                width: '100%'
            });
        });

        console.log('✓ Select2 de docentes inicializado (' + $('.select2-docente').length + ' selects)');
    });

    // ========================================
    // GENERAR CAMPOS DINÁMICOS EN EL MODAL
    // ========================================
    function generarCamposModulosModal(cantidad) {
        let html = '<div class="row">';

        // Generar opciones de docentes para el select
        let optionsDocentes = '<option value="">-- Seleccionar Docente (Opcional) --</option>';
        if (docentesDisponibles && docentesDisponibles.length > 0) {
            docentesDisponibles.forEach(function(docente) {
                const especialidad = docente.Especialidad ? ` - ${docente.Especialidad}` : '';
                optionsDocentes += `<option value="${docente.DocenteID}">${docente.NombreCompleto}${especialidad}</option>`;
            });
        } else {
            optionsDocentes = '<option value="">No hay docentes disponibles</option>';
        }

        for (let i = 1; i <= cantidad; i++) {
            // Generar código en números romanos
            const numeroRomano = convertirANumeroRomano(i);
            const codigoModulo = `MODULO ${numeroRomano}`;

            html += `
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <strong><i class="fa fa-book"></i> ${codigoModulo}</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><strong>Código Módulo:</strong></label>
                                <input type="text"
                                       class="form-control"
                                       name="codigomodulo_${i}"
                                       value="${codigoModulo}"
                                       readonly
                                       style="background-color: #e9ecef; font-weight: bold;">
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Código generado automáticamente
                                </small>
                            </div>
                            <div class="form-group">
                                <label><strong>Nombre del Módulo:</strong></label>
                                <input type="text"
                                       class="form-control"
                                       name="nombremodulo_${i}"
                                       placeholder="Ej: Metodología de Investigación"
                                       maxlength="100"
                                       required>
                            </div>
                            <div class="form-group mb-0">
                                <label><strong>Docente Asignado:</strong></label>
                                <select class="form-control select2-docente"
                                        name="docentemodulo_${i}"
                                        id="docentemodulo_${i}"
                                        style="width: 100%;">
                                    ${optionsDocentes}
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Puede asignar un docente al módulo (opcional)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Salto de fila cada 2 módulos
            if (i % 2 === 0) {
                html += '</div><div class="row">';
            }
        }

        html += '</div>';
        $('#contenedorModulosModal').html(html);
    }

    // ========================================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ========================================
    $('#formRegistrarModulosPorPrograma').on('submit', function(e) {
        e.preventDefault();

        const numModulos = parseInt($('#modalTotalModulos').val());
        let modulosValidos = 0;

        for (let i = 1; i <= numModulos; i++) {
            const nombre = $(`input[name="nombremodulo_${i}"]`).val();
            const codigo = $(`input[name="codigomodulo_${i}"]`).val();

            if (nombre && nombre.trim() !== '' && codigo && codigo.length > 0) {
                modulosValidos++;
            }
        }

        if (modulosValidos === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe completar al menos un módulo',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Confirmar
        Swal.fire({
            title: '¿Registrar módulos?',
            text: `Se van a registrar ${modulosValidos} módulos para este programa`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Registrando módulos, por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar formulario
                $('#formRegistrarModulosPorPrograma')[0].submit();
            }
        });
    });

    // ========================================
    // LIMPIAR SELECT2 AL CERRAR EL MODAL
    // ========================================
    $('#modalRegistrarModulos').on('hidden.bs.modal', function () {
        if ($('.select2-docente').hasClass('select2-hidden-accessible')) {
            $('.select2-docente').select2('destroy');
        }
    });

    // ========================================
    // INICIALIZAR DATATABLE
    // ========================================
    console.log('Inicializando DataTable...');

    // Verificar si la tabla tiene filas válidas (sin colspan)
    const $tbody = $('#tablaModulos tbody');
    const $firstRow = $tbody.find('tr:first');
    const hasColspan = $firstRow.find('td[colspan]').length > 0;

    console.log('Tabla tiene colspan (vacía):', hasColspan);

    if (hasColspan) {
        // Si la tabla está vacía (tiene colspan), no inicializar DataTables
        console.log('⚠ Tabla vacía detectada. DataTable no se inicializará.');
        console.log('ℹ Registra módulos para ver la funcionalidad de búsqueda y filtrado.');
    } else {
        // Solo inicializar DataTable si hay datos reales
        try {
            $('#tablaModulos').DataTable({
                language: {
                    processing: "Procesando...",
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    loadingRecords: "Cargando...",
                    zeroRecords: "No se encontraron registros coincidentes",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Último"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna ascendente",
                        sortDescending: ": activar para ordenar la columna descendente"
                    }
                },
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true
            });
            console.log('✓ DataTable inicializado correctamente');
        } catch (error) {
            console.error('✗ Error al inicializar DataTable:', error);
        }
    }

    console.log('=== SISTEMA DE MÓDULOS LISTO ===');
});
</script>

<style>
.card {
    border: 2px solid #ddd;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
}

.card-header {
    font-weight: 600;
}

#infoProgramaSeleccionado {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table thead th {
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.table tbody tr:hover {
    background-color: #f8f9fa !important;
}

/* Estilos para Select2 */
.select2-container {
    width: 100% !important;
    display: block !important;
}

.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
    padding-right: 30px !important;
    color: #495057 !important;
    font-size: 14px !important;
    display: block !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 5px !important;
    top: 1px !important;
}

.select2-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    background-color: #fff !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #667eea !important;
    color: white !important;
}

/* Z-index para modales - CRÍTICO para que funcione Select2 dentro de modales */
.select2-container--open {
    z-index: 99999999 !important;
}

.select2-dropdown {
    z-index: 99999999 !important;
}

.select2-container {
    z-index: inherit !important;
}

/* Fix para scroll en modal */
#modalRegistrarModulos .modal-body {
    max-height: 70vh !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

/* Los cards deben permitir overflow visible para dropdowns */
.card {
    overflow: visible !important;
}

.card-body {
    overflow: visible !important;
}
</style>
