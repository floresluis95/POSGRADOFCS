<?php
require_once "controladores/modulo.controlador.php";
require_once "modelos/modulo.modelo.php";
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Obtener lista de docentes activos para el select
$docentes = ModuloModelo::ListarDocentesActivosModelo();

// Obtener lista de sedes para el select de filtro
$sedes = ProgramasModelos::ListarSedesModelo();
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
                        <div class="row filtros-modulos align-items-end">
                            <div class="col-lg-2 col-md-6 form-group mb-3">
                                <label class="filtro-label">Sede</label>
                                <select class="form-control select2-uniforme" id="selectSede">
                                    <option value="">Todas las sedes</option>
                                    <?php
                                    foreach ($sedes as $sede) {
                                        $sedeVal = htmlspecialchars($sede, ENT_QUOTES);
                                        echo '<option value="' . $sedeVal . '">' . $sedeVal . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-6 form-group mb-3">
                                <label class="filtro-label">Grado académico</label>
                                <select class="form-control select2-uniforme" id="selectGrado">
                                    <option value="">Todos los grados</option>
                                    <option value="DIPLOMADO">DIPLOMADO</option>
                                    <option value="MAESTRIA">MAESTRIA</option>
                                    <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                                    <option value="DOCTORADO">DOCTORADO</option>
                                </select>
                            </div>

                            <div class="col-lg-6 col-md-8 form-group mb-3">
                                <label class="filtro-label">Programa</label>
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
                                        $costo = htmlspecialchars($prog['Costo'] ?? 0, ENT_QUOTES);
                                        $sedePrograma = htmlspecialchars($prog['Sede'] ?? '', ENT_QUOTES);
                                        $fechaInicioPrograma = htmlspecialchars($prog['FechaInicio'] ?? '', ENT_QUOTES);

                                        echo '<option value="' . $programaID . '"
                                                    data-nombre="' . $nombrePrograma . '"
                                                    data-codigo="' . $codigo . '"
                                                    data-grado="' . $grado . '"
                                                    data-modulos="' . $modulos . '"
                                                    data-costo="' . $costo . '"
                                                    data-sede="' . $sedePrograma . '"
                                                    data-fechainicio="' . $fechaInicioPrograma . '">';
                                        echo $nombrePrograma . ' - ' . $grado . ' (' . $codigo . ') - ' . $sedePrograma;
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4 form-group mb-3">
                                <button type="button" class="btn btn-success btn-block" id="btnAbrirModalModulos" disabled>
                                    <i class="fa fa-plus"></i> Agregar Módulos
                                </button>
                            </div>
                        </div>

                        <style>
                            .filtros-modulos .filtro-label {
                                font-size: 0.72rem;
                                font-weight: 600;
                                color: #6c757d;
                                text-transform: uppercase;
                                letter-spacing: 0.3px;
                                margin-bottom: 4px;
                            }

                            .filtros-modulos .form-control {
                                font-size: 0.9rem;
                            }
                        </style>


<style>
    /* Ficha de información del programa seleccionado: compacta y formal */
    .card-modern {
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }

    .header-modern {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 10px 16px;
    }

    .header-modern h4 {
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .program-title {
        font-size: 1.05rem;
        font-weight: 700;
        border-left: 3px solid #6c757d;
        padding-left: 10px;
    }

    .label-modern {
        font-size: 0.72rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .value-modern {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2b2b2b;
    }

    .modulo-highlight {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2b2b2b;
    }
</style>


<div id="infoProgramaSeleccionadoNuevo"
     style="display:none;"
     class="card card-modern mt-4">

    <!-- Header -->
    <div class="card-header header-modern d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center">
            <i class="bi bi-journal-bookmark me-2 text-muted"></i>
            <h4 class="mb-0 text-dark">Programa Seleccionado</h4>
        </div>

        <span class="badge badge-success">Activo</span>

    </div>

    <!-- Body -->
    <div class="card-body px-3 py-3">

        <!-- Título -->
        <h2 class="program-title text-dark mb-3" id="infoNombrePrograma">
            [Nombre del Programa]
        </h2>

        <div class="row">

            <!-- Grado -->
            <div class="col-md-4">
                <div class="label-modern mb-1">Grado</div>
                <div class="value-modern mb-2" id="infoGradoPrograma">
                    [Grado]
                </div>
                <div class="label-modern mb-1">Código del programa</div>
                <div class="value-modern" id="infoCodigoPrograma">
                    [0]
                </div>
            </div>

            <!-- Número de módulos -->
            <div class="col-md-4">
                <div class="label-modern mb-1">N° de Módulos</div>
                <span id="infoNumModulosPrograma" class="modulo-highlight">
                    [0]
                </span>
                <div id="avisoModulosCompletos" class="mt-2" style="display:none;">
                    <span class="badge badge-success"><i class="fa fa-check-circle"></i> Módulos completos</span>
                </div>
            </div>

            <!-- Costo Total del Programa -->
            <div class="col-md-4">
                <div class="label-modern mb-1">Costo Total</div>
                <div class="value-modern" id="infoCostoPrograma">
                    Bs. 0.00
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div class="card-footer text-center bg-transparent py-2">
        <small class="text-muted">
            Información relevante para la matrícula.
        </small>
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
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                        <div class="kt-portlet__head-label">
                            <h4 style="color: white; margin: 0;">
                                <i class="fa fa-list"></i> Módulos Registrados
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaModulos" style="width: 100%;">
                                <thead style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                                    <tr>
                                        <th class="text-center" style="color: white; width: 50px;">#</th>
                                        <th class="text-center" style="color: white; width: 120px;">CÓDIGO</th>
                                        <th style="color: white; min-width: 180px;">NOMBRE MÓDULO</th>
                                        <th style="color: white; min-width: 180px;">PROGRAMA</th>
                                        <th class="text-center" style="color: white; width: 130px;">FECHAS</th>
                                        <th class="text-center" style="color: white; width: 110px;">ESTADO MÓDULO</th>
                                        <th style="color: white; min-width: 150px;">DOCENTE ASIGNADO</th>
                                        <th class="text-center" style="color: white; width: 100px;">ESTADO</th>
                                        <th class="text-center" style="color: white; width: 120px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaModulosBody">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fa fa-hand-pointer-o"></i> Seleccione un programa para ver los módulos registrados
                                        </td>
                                    </tr>
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
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title text-white">
                    <i class="fa fa-book"></i> REGISTRAR MÓDULOS DEL PROGRAMA
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
            </div>

            <form method="POST" id="formRegistrarModulosPorPrograma">
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto; overflow-x: hidden; padding: 20px;">
                    <!-- Información del Programa -->
                    <div class="detalle-programa-modal">
                        <div class="row no-gutters">
                            <div class="col detalle-programa-item">
                                <div class="label-modern">Programa</div>
                                <div class="value-modern" id="modalNombrePrograma">-</div>
                            </div>
                            <div class="col detalle-programa-item">
                                <div class="label-modern">N° Módulos</div>
                                <div class="value-modern" id="modalNumModulos">-</div>
                            </div>
                            <div class="col detalle-programa-item">
                                <div class="label-modern">Fecha inicio programa</div>
                                <div class="value-modern" id="modalFechaInicioProgramaTexto">-</div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .detalle-programa-modal {
                            background-color: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 6px;
                            padding: 4px 0;
                            margin-bottom: 1rem;
                        }

                        .detalle-programa-item {
                            padding: 10px 16px;
                            border-right: 1px solid #dee2e6;
                        }

                        .detalle-programa-item:last-child {
                            border-right: none;
                        }
                    </style>

                    <input type="hidden" name="programaID" id="modalProgramaID">
                    <input type="hidden" name="totalModulos" id="modalTotalModulos">
                    <input type="hidden" name="registrarModulosPorPrograma" value="1">

                    <hr>

                    <!-- Contenedor dinámico de módulos -->
                    <div id="contenedorModulosModal">
                        <!-- Se generarán dinámicamente aquí -->
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 2px solid #ddd; background-color: #f8f9fa; padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" id="btnGuardarModulosPrograma" name="registrarModulosPorPrograma" class="btn btn-success">
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

<!-- Modal para Editar Módulo -->
<div class="modal fade" id="modalEditarModulo" tabindex="-1" role="dialog" style="z-index: 1050;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%); color: white;">
                <h5 class="modal-title text-white">
                    <i class="fa fa-edit"></i> EDITAR MÓDULO
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
            </div>

            <form method="POST" id="formEditarModulo">
                <div class="modal-body">
                    

                    <!-- Campos ocultos -->
                    <input type="hidden" name="idmodulo" id="editIdmodulo">
                    <input type="hidden" name="programaID" id="editProgramaID">
                    <input type="hidden" name="actualizarModulo" value="1">

                    <!-- Formulario de edición -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fa fa-code"></i> Código del Módulo:</strong></label>
                                <input type="text"
                                       class="form-control"
                                       name="codigomodulo"
                                       id="editCodigoModulo"
                                       placeholder="Ej: MODULO I"
                                       maxlength="50"
                                       required
                                       readonly
                                       style="background-color: #e9ecef; font-weight: bold;">
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> El código del módulo no se puede modificar
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong><i class="fa fa-book"></i> Nombre del Módulo:</strong> <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       name="nombremodulo"
                                       id="editNombreModulo"
                                       placeholder="Ej: Metodología de Investigación"
                                       maxlength="100"
                                       required>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Campo obligatorio
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
    <!-- Columna 1: Fecha Inicio -->
                        <div class="col-md-6 form-group">
                            <label><strong><i class="fa fa-book"></i> Fecha del Inicio:</strong> <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fechainicio" id="editFechaInicio">
                        </div>

                        <!-- Columna 2: Fecha Final -->
                        <div class="col-md-6 form-group">
                            <label><strong><i class="fa fa-book"></i> Fecha Final:</strong> <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fechafin" id="editFechaFinal">
                        </div>
                    </div>
                 
                    <div class="form-group">
                        <label><strong><i class="fa fa-user-tie"></i> Docente Asignado:</strong></label>
                        <select class="form-control select2-docente-edit"
                                name="docenteID"
                                id="editDocenteID"
                                style="width: 100%;">
                            <option value="">-- Sin Docente Asignado --</option>
                            <?php
                            foreach ($docentes as $doc) {
                                $especialidad = $doc['Especialidad'] ? ' - ' . $doc['Especialidad'] : '';
                                echo '<option value="' . $doc['DocenteID'] . '">' .
                                     htmlspecialchars($doc['NombreCompleto']) .
                                     htmlspecialchars($especialidad) . '</option>';
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Puede cambiar o dejar sin asignar docente
                        </small>
                    </div>
                </div>

                <div class="modal-footer" style="border-top: 2px solid #ddd; background-color: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Actualizar Módulo
                    </button>
                </div>

                <?php
                $actualizar = new ModuloControlador();
                $actualizar->ActualizarModuloControlador();
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
    // FILTRO DE PROGRAMAS POR SEDE Y GRADO ACADÉMICO (usado por Select2)
    // ========================================
    function filtrarProgramaPorSede(params, data) {
        // Opción placeholder ("Buscar programa...") siempre visible
        if (!data.id) {
            return data;
        }

        const sedeSeleccionada = $('#selectSede').val();
        const sedeOpcion = $(data.element).data('sede');

        // Si hay una sede elegida, ocultar los programas que no pertenezcan a ella
        if (sedeSeleccionada && sedeSeleccionada !== '' && String(sedeOpcion) !== String(sedeSeleccionada)) {
            return null;
        }

        const gradoSeleccionado = $('#selectGrado').val();
        const gradoOpcion = $(data.element).data('grado');

        // Si hay un grado elegido, ocultar los programas que no pertenezcan a él
        if (gradoSeleccionado && gradoSeleccionado !== '' && String(gradoOpcion) !== String(gradoSeleccionado)) {
            return null;
        }

        // Filtro de texto normal de Select2
        if ($.trim(params.term) === '') {
            return data;
        }

        if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
            return data;
        }

        return null;
    }

    // ========================================
    // INICIALIZAR SELECT2 PARA PROGRAMAS
    // ========================================
    // Mismo tamaño/estilo para Sede y Grado (el matcher de abajo sigue leyendo
    // su valor con $().val(), sea select nativo o Select2, así que esto no
    // afecta el filtrado en cascada).
    $('.select2-uniforme').select2({ width: '100%' });

    console.log('Inicializando Select2 para programas...');
    try {
        $('.select2-programa').select2({
            placeholder: 'Buscar programa...',
            allowClear: true,
            matcher: filtrarProgramaPorSede,
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
    // EVENTO: CAMBIO DE SEDE (filtra los programas)
    // ========================================
    $('#selectSede').on('change', function() {
        const sedeSeleccionada = $(this).val();
        console.log('>>> Sede seleccionada:', sedeSeleccionada || '(todas)');

        // Si el programa actualmente elegido no pertenece a la sede filtrada, limpiarlo
        const opcionActual = $('#selectPrograma').find('option:selected');
        const sedeProgramaActual = opcionActual.data('sede');

        if (sedeSeleccionada && $('#selectPrograma').val() && String(sedeProgramaActual) !== String(sedeSeleccionada)) {
            $('#selectPrograma').val('').trigger('change');
            $('#infoProgramaSeleccionadoNuevo').hide();
            $('#btnAbrirModalModulos').prop('disabled', true);
        }
    });

    // ========================================
    // EVENTO: CAMBIO DE GRADO ACADÉMICO (filtra los programas)
    // ========================================
    $('#selectGrado').on('change', function() {
        const gradoSeleccionado = $(this).val();
        console.log('>>> Grado seleccionado:', gradoSeleccionado || '(todos)');

        // Si el programa actualmente elegido no pertenece al grado filtrado, limpiarlo
        const opcionActual = $('#selectPrograma').find('option:selected');
        const gradoProgramaActual = opcionActual.data('grado');

        if (gradoSeleccionado && $('#selectPrograma').val() && String(gradoProgramaActual) !== String(gradoSeleccionado)) {
            $('#selectPrograma').val('').trigger('change');
            $('#infoProgramaSeleccionadoNuevo').hide();
            $('#btnAbrirModalModulos').prop('disabled', true);
        }
    });

    // ========================================
    // FUNCIÓN PARA MANEJAR LA SELECCIÓN DE PROGRAMA
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
            const costoTotal = parseFloat(selectedOption.data('costo')) || 0;
            const fechaInicioPrograma = selectedOption.data('fechainicio') || '';

            console.log('Datos del programa:', {
                id: programaID,
                nombre: nombrePrograma,
                codigo: codigoPrograma,
                grado: gradoPrograma,
                modulos: numModulos,
                costo: costoTotal,
                fechaInicio: fechaInicioPrograma
            });

            // Actualizar información en la interfaz
            $('#infoNombrePrograma').text(nombrePrograma || 'N/A');
            $('#infoGradoPrograma').text(gradoPrograma || 'N/A');
            $('#infoCodigoPrograma').text(codigoPrograma || 'N/A')
            $('#infoNumModulosPrograma').text(numModulos);
            $('#infoCostoPrograma').text('Bs. ' + costoTotal.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            // Guardar datos en el botón para usarlos en el modal
            $('#btnAbrirModalModulos').data('programaid', programaID);
            $('#btnAbrirModalModulos').data('nombre', nombrePrograma);
            $('#btnAbrirModalModulos').data('codigo', codigoPrograma);
            $('#btnAbrirModalModulos').data('modulos', numModulos);
            $('#btnAbrirModalModulos').data('costo', costoTotal);
            $('#btnAbrirModalModulos').data('fechainicio', fechaInicioPrograma);

            // Habilitar botón y mostrar info
            console.log('Habilitando botón "Agregar Módulos"');
            $('#btnAbrirModalModulos').prop('disabled', false);
            $('#infoProgramaSeleccionadoNuevo').slideDown(300);

            // Cargar módulos del programa en la tabla
            cargarTablaModulos(programaID);

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

        // Limpiar tabla y volver al mensaje inicial
        $('#tablaModulosBody').html('<tr><td colspan="7" class="text-center text-muted"><i class="fa fa-hand-pointer-o"></i> Seleccione un programa para ver los módulos registrados</td></tr>');

        // Destruir DataTable si existe
        if ($.fn.DataTable.isDataTable('#tablaModulos')) {
            $('#tablaModulos').DataTable().destroy();
        }
    });
    console.log('✓ Eventos de Select2 registrados');

    // ========================================
    // RESTAURAR PROGRAMA SELECCIONADO DESPUÉS DE GUARDAR
    // ========================================
    const programaIDGuardado = sessionStorage.getItem('programaIDSeleccionado');
    if (programaIDGuardado) {
        console.log('>>> Restaurando programa seleccionado:', programaIDGuardado);
        console.log('Tipo de dato:', typeof programaIDGuardado);

        // Seleccionar el programa en el select (sin disparar eventos)
        $('#selectPrograma').val(programaIDGuardado);

        // Actualizar visual de Select2 sin disparar eventos
        $('#selectPrograma').trigger('change.select2');

        // Obtener la opción seleccionada para extraer los datos
        const selectedOption = $('#selectPrograma').find('option:selected');
        const nombrePrograma = selectedOption.data('nombre');
        const codigoPrograma = selectedOption.data('codigo');
        const gradoPrograma = selectedOption.data('grado');
        const numModulos = parseInt(selectedOption.data('modulos')) || 0;
        const costoTotal = parseFloat(selectedOption.data('costo')) || 0;
        const fechaInicioPrograma = selectedOption.data('fechainicio') || '';

        console.log('Datos extraídos del programa:', {
            id: programaIDGuardado,
            nombre: nombrePrograma,
            grado: gradoPrograma,
            modulos: numModulos,
            costo: costoTotal,
            fechaInicio: fechaInicioPrograma
        });

        // Actualizar información en la interfaz
        $('#infoNombrePrograma').text(nombrePrograma || 'N/A');
        $('#infoGradoPrograma').text(gradoPrograma || 'N/A');
        $('#infoNumModulosPrograma').text(numModulos);
        $('#infoCostoPrograma').text('Bs. ' + costoTotal.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // Guardar datos en el botón para usarlos en el modal
        $('#btnAbrirModalModulos').data('programaid', programaIDGuardado);
        $('#btnAbrirModalModulos').data('nombre', nombrePrograma);
        $('#btnAbrirModalModulos').data('codigo', codigoPrograma);
        $('#btnAbrirModalModulos').data('modulos', numModulos);
        $('#btnAbrirModalModulos').data('costo', costoTotal);
        $('#btnAbrirModalModulos').data('fechainicio', fechaInicioPrograma);

        // Habilitar botón y mostrar info
        $('#btnAbrirModalModulos').prop('disabled', false);
        $('#infoProgramaSeleccionadoNuevo').slideDown(300);

        // Cargar módulos del programa en la tabla
        cargarTablaModulos(programaIDGuardado);

        // Limpiar el sessionStorage después de usar
        sessionStorage.removeItem('programaIDSeleccionado');
        console.log('✓ Programa restaurado automáticamente y tabla cargada');
    }

    // ========================================
    // FUNCIÓN PARA CARGAR TABLA DE MÓDULOS VÍA AJAX
    // ========================================
    // Si el programa ya tiene registrados todos los módulos definidos al crearlo,
    // se bloquea el botón "Agregar Módulos" (el backend igual lo rechazaría,
    // pero así el usuario no llega a intentarlo).
    function actualizarEstadoBotonModulos(modulosRegistrados) {
        const modulosEsperados = parseInt($('#btnAbrirModalModulos').data('modulos')) || 0;
        const completos = modulosEsperados > 0 && modulosRegistrados >= modulosEsperados;

        if (completos) {
            $('#btnAbrirModalModulos').prop('disabled', true).attr('title', 'Este programa ya tiene todos sus módulos registrados');
            $('#avisoModulosCompletos').show();
        } else {
            $('#btnAbrirModalModulos').prop('disabled', false).attr('title', '');
            $('#avisoModulosCompletos').hide();
        }
    }

    function cargarTablaModulos(programaID) {
        console.log('>>> Cargando módulos del programa:', programaID);

        // Destruir DataTable si existe
        if ($.fn.DataTable.isDataTable('#tablaModulos')) {
            $('#tablaModulos').DataTable().destroy();
        }

        // Mostrar mensaje de carga
        $('#tablaModulosBody').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</td></tr>');

        // Petición AJAX
        $.ajax({
            url: 'ajax/modulo.ajax.php',
            type: 'POST',
            data: {
                accion: 'cargarTablaModulos',
                programaIDFiltro: programaID
            },
            success: function(response) {
                console.log('✓ Módulos cargados correctamente');
                $('#tablaModulosBody').html(response);

                // Verificar si hay datos reales (sin colspan)
                const $firstRow = $('#tablaModulosBody tr:first');
                const hasColspan = $firstRow.find('td[colspan]').length > 0;

                // Si el programa ya tiene registrados todos sus módulos, bloquear el botón de agregar
                const modulosRegistrados = hasColspan ? 0 : $('#tablaModulosBody > tr').length;
                actualizarEstadoBotonModulos(modulosRegistrados);

                if (!hasColspan) {
                    // Inicializar DataTable solo si hay datos
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
                            order: [[0, 'asc']],
                            pageLength: 25,
                            responsive: true
                        });
                        console.log('✓ DataTable inicializado después de cargar módulos');
                    } catch (error) {
                        console.error('✗ Error al inicializar DataTable:', error);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('✗ Error al cargar módulos:', error);
                $('#tablaModulosBody').html('<tr><td colspan="9" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error al cargar los módulos</td></tr>');
            }
        });
    }

    // ========================================
    // ABRIR MODAL PARA REGISTRAR MÓDULOS
    // ========================================
    $('#btnAbrirModalModulos').on('click', function() {
        console.log('>>> Botón "Agregar Módulos" clickeado');

        const programaID = $(this).data('programaid');
        const nombrePrograma = $(this).data('nombre');
        const codigoPrograma = $(this).data('codigo');
        const numModulos = parseInt($(this).data('modulos'));
        const costoTotal = parseFloat($(this).data('costo')) || 0;
        const fechaInicioPrograma = $(this).data('fechainicio') || '';

        console.log('Datos del programa para el modal:', {
            programaID,
            nombrePrograma,
            codigoPrograma,
            numModulos,
            costoTotal,
            fechaInicioPrograma
        });

        if (numModulos <= 0) {
            console.log('⚠ Programa sin módulos configurados');
            alert('Este programa no tiene módulos configurados. Configure primero el número de módulos en la tabla de programas.');
            return;
        }

        // Actualizar información en el modal
        $('#modalNombrePrograma').text(nombrePrograma);
        $('#modalNumModulos').text(numModulos);
        $('#modalFechaInicioProgramaTexto').text(fechaInicioPrograma || 'N/A');
        $('#modalProgramaID').val(programaID);
        $('#modalTotalModulos').val(numModulos);

        console.log('Generando campos para', numModulos, 'módulos...');

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
                    <div class="card shadow-sm" style="height: auto;">
                        <div class="card-header bg-primary text-white" style="padding: 10px 15px;">
                            <strong><i class="fa fa-book"></i> ${codigoModulo}</strong>
                        </div>
                        <div class="card-body" style="padding: 15px;">
                            <div class="form-group">
                                <label class="mb-1"><strong>Código Módulo:</strong></label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="codigomodulo_${i}"
                                       value="${codigoModulo}"
                                       readonly
                                       style="background-color: #e9ecef; font-weight: bold;">
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Código generado automáticamente
                                </small>
                            </div>
                            <div class="form-group">
                                <label class="mb-1"><strong>Nombre del Módulo:</strong></label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="nombremodulo_${i}"
                                       placeholder="Ej: Metodología de Investigación"
                                       maxlength="100">
                            </div>

                        <div class="row">

                            <div class="col-md-6 form-group mb-3">
                                <label class="mb-1"><strong>Fecha de inicio:</strong></label>
                                <input type="date"
                                    class="form-control form-control-sm modulo-fecha-inicio"
                                    name="fechainicio_${i}"
                                    id="fechainicio_${i}">
                            </div>

                           
                            <div class="col-md-6 form-group mb-3">
                                <label class="mb-1"><strong>Fecha final:</strong></label>
                                <input type="date"
                                    class="form-control form-control-sm"
                                    name="fechafin_${i}"
                                    id="fechafin_${i}">
                            </div>
                        </div>

                            <div class="form-group mb-0">
                                <label class="mb-1"><strong>Docente Asignado:</strong></label>
                                <select class="form-control form-control-sm select2-docente"
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
        let modulosConNombre = 0;

        // Contar cuántos módulos tienen nombre
        for (let i = 1; i <= numModulos; i++) {
            const nombre = $(`input[name="nombremodulo_${i}"]`).val();

            if (nombre && nombre.trim() !== '') {
                modulosConNombre++;
            }
        }

        if (modulosConNombre === 0) {
            alert('Debe completar el nombre de al menos un módulo');
            return false;
        }

        // Confirmar
        const mensaje = modulosConNombre === 1
            ? '¿Desea registrar 1 módulo para este programa?'
            : `¿Desea registrar ${modulosConNombre} módulos para este programa?`;

        const confirmacion = confirm(mensaje);

        if (!confirmacion) {
            return;
        }

        // Enviar por AJAX: así el modal se cierra y la tabla se refresca
        // automáticamente, sin recargar toda la página.
        const $btnGuardar = $('#btnGuardarModulosPrograma');
        $btnGuardar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/modulo.ajax.php',
            method: 'POST',
            data: $('#formRegistrarModulosPorPrograma').serialize() + '&accion=registrarModulosPorPrograma',
            dataType: 'json',
            success: function(respuesta) {
                if (respuesta.estado === 'exitoso') {
                    // Primero el aviso, y solo al cerrarlo se oculta el modal y se
                    // refresca la tabla: hacerlo en el otro orden puede dejar el
                    // aviso atrapado detrás del backdrop del modal (pantalla "congelada").
                    // Se usa Swal.fire (API real de SweetAlert2) en vez de swal(...):
                    // ese alias de compatibilidad con la v1 está roto en esta página
                    // (lanza "can't access property constructor, this is undefined").
                    Swal.fire('EXITOSO!', respuesta.mensaje, 'success').then(function() {
                        $('#modalRegistrarModulos').modal('hide');
                        cargarTablaModulos(respuesta.programaID);
                    });
                } else {
                    Swal.fire('ERROR!', respuesta.mensaje, 'error');
                }
            },
            error: function() {
                Swal.fire('ERROR!', 'No se pudo conectar con el servidor', 'error');
            },
            complete: function() {
                $btnGuardar.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Módulos');
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
    // MODAL DE EDICIÓN DE MÓDULO
    // ========================================

    // Abrir modal de edición cuando se hace clic en el botón editar
    $(document).on('click', '.btn-editar-modulo', function() {
        console.log('>>> Botón editar módulo clickeado');

        const datosModulo = $(this).data('modulo');
        console.log('Datos del módulo:', datosModulo);

        // Llenar el modal con los datos
        $('#editIdmodulo').val(datosModulo.idmodulo);
        $('#editProgramaID').val(datosModulo.programaId);
        $('#editCodigoModulo').val(datosModulo.codigomodulo);
        $('#editNombreModulo').val(datosModulo.nombremodulo);
        $('#editFechaInicio').val(datosModulo.fechaInicio || '');
        $('#editFechaFinal').val(datosModulo.fechaFinal || '');

        // Establecer el docente seleccionado
        $('#editDocenteID').val(datosModulo.docenteID || '');

        // Abrir el modal
        $('#modalEditarModulo').modal('show');

        console.log('✓ Modal de edición abierto');
    });

    // Inicializar Select2 cuando el modal de edición se muestra
    $('#modalEditarModulo').on('shown.bs.modal', function () {
        console.log('✓ Modal de edición mostrado, inicializando Select2...');

        $('.select2-docente-edit').select2({
            placeholder: '-- Sin Docente Asignado --',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron docentes";
                },
                searching: function() {
                    return "Buscando...";
                }
            },
            dropdownParent: $('#modalEditarModulo .modal-content'),
            width: '100%'
        });

        console.log('✓ Select2 del modal de edición inicializado');
    });

    // Limpiar Select2 al cerrar el modal de edición
    $('#modalEditarModulo').on('hidden.bs.modal', function () {
        if ($('.select2-docente-edit').hasClass('select2-hidden-accessible')) {
            $('.select2-docente-edit').select2('destroy');
        }
    });

    // Envío del formulario de edición
    $('#formEditarModulo').on('submit', function(e) {
        e.preventDefault();
        console.log('>>> Formulario de edición enviado');

        const nombreModulo = $('#editNombreModulo').val().trim();

        if (nombreModulo === '') {
            alert('El nombre del módulo es obligatorio');
            return false;
        }

        // Confirmar actualización
        const confirmacion = confirm('¿Está seguro de actualizar este módulo?');

        if (confirmacion) {
            console.log('✓ Confirmado, enviando por AJAX...');

            const $formEditar = $(this);
            const $btnActualizar = $formEditar.find('button[type="submit"]');
            $btnActualizar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Actualizando...');

            $.ajax({
                url: 'ajax/modulo.ajax.php',
                method: 'POST',
                data: $formEditar.serialize() + '&accion=actualizarModulo',
                dataType: 'json',
                success: function(respuesta) {
                    if (respuesta.estado === 'exitoso') {
                        // Primero el aviso, y solo al cerrarlo se oculta el modal y se
                        // refresca la tabla: hacerlo en el otro orden puede dejar el
                        // aviso atrapado detrás del backdrop del modal (pantalla "congelada").
                        // Se usa Swal.fire (API real de SweetAlert2) en vez de swal(...):
                        // ese alias de compatibilidad con la v1 está roto en esta página
                        // (lanza "can't access property constructor, this is undefined").
                        Swal.fire('EXITOSO!', respuesta.mensaje, 'success').then(function() {
                            $('#modalEditarModulo').modal('hide');
                            cargarTablaModulos(respuesta.programaID);
                        });
                    } else {
                        Swal.fire('ERROR!', respuesta.mensaje, 'error');
                    }
                },
                error: function() {
                    Swal.fire('ERROR!', 'No se pudo conectar con el servidor', 'error');
                },
                complete: function() {
                    $btnActualizar.prop('disabled', false).html('<i class="fa fa-save"></i> Actualizar Módulo');
                }
            });
        } else {
            console.log('✗ Actualización cancelada');
        }
    });

    // ========================================
    // NOTA: DataTable se inicializa dinámicamente
    // ========================================
    // El DataTable se inicializa automáticamente después de cargar
    // los módulos vía AJAX cuando se selecciona un programa.
    // No es necesario inicializarlo aquí.

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
    height: 32px !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    background-color: #fff !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    /* El bundle del tema le pone padding vertical propio a este mismo
       elemento; hay que anular los cuatro lados (no solo left/right)
       para que no se siga sumando y el texto no se salga de la caja. */
    padding: 0 28px 0 10px !important;
    color: #495057 !important;
    font-size: 13px !important;
    display: block !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    /* El bundle del tema posiciona esta flecha con "top: 50%" sin
       transform, así que un top fijo la deja corrida hacia arriba;
       top+translateY(-50%) la centra sin importar el alto real de la caja. */
    top: 50% !important;
    transform: translateY(-50%) !important;
    height: auto !important;
    right: 5px !important;
}

.select2-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    background-color: #fff !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #1e3c72 !important;
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
    max-height: 60vh !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

#modalRegistrarModulos .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#modalRegistrarModulos .modal-footer {
    flex-shrink: 0;
    border-top: 2px solid #ddd !important;
    background-color: #f8f9fa !important;
    position: sticky;
    bottom: 0;
    z-index: 10;
}

/* Los cards dentro del modal con tamaño controlado */
#modalRegistrarModulos .card {
    overflow: visible !important;
    margin-bottom: 15px;
    width: 100%;
}

#modalRegistrarModulos .card-body {
    overflow: visible !important;
    padding: 15px;
}

/* Contenedor de módulos con padding adecuado */
#contenedorModulosModal {
    width: 100%;
    padding: 0 10px;
}

#contenedorModulosModal .row {
    margin-left: -10px;
    margin-right: -10px;
}

#contenedorModulosModal .col-lg-6 {
    padding-left: 10px;
    padding-right: 10px;
}

/* Estilos para formularios más compactos */
#modalRegistrarModulos .form-group {
    margin-bottom: 12px;
}

#modalRegistrarModulos .form-control-sm {
    height: 32px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

#modalRegistrarModulos label {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

#modalRegistrarModulos small.text-muted {
    font-size: 11px;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 992px) {
    #modalRegistrarModulos .modal-dialog {
        max-width: 95% !important;
    }

    #contenedorModulosModal .col-lg-6 {
        width: 100%;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    #modalRegistrarModulos .modal-body {
        padding: 15px !important;
    }

    #modalRegistrarModulos .card-body {
        padding: 10px !important;
    }
}

/* ========================================
   ESTILOS MEJORADOS PARA LA TABLA
   ======================================== */

/* Mejoras visuales para la tabla */
#tablaModulos {
    font-size: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
}

#tablaModulos thead th {
    padding: 15px 10px;
    vertical-align: middle;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

#tablaModulos tbody td {
    padding: 12px 10px;
    vertical-align: middle;
}

#tablaModulos tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #e9ecef;
}

#tablaModulos tbody tr:hover {
    background-color: #f5f7fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* Badges mejorados */
.badge {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    letter-spacing: 0.3px;
}

.badge-primary {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.badge-info {
    background: linear-gradient(135deg, #0f6674 0%, #159895 100%);
}

.badge-success {
    background: linear-gradient(135deg, #1e7145 0%, #27ae60 100%);
}

.badge-secondary {
    background: linear-gradient(135deg, #52616e 0%, #37424a 100%);
}

/* Botones de acción mejorados */
.btn-editar-modulo {
    background: linear-gradient(135deg, #52616e 0%, #37424a 100%);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(55, 66, 74, 0.3);
}

.btn-editar-modulo:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(55, 66, 74, 0.5);
    background: linear-gradient(135deg, #37424a 0%, #52616e 100%);
    color: white;
}

.btn-editar-modulo:active {
    transform: translateY(0);
}

.btn-editar-modulo i {
    font-size: 14px;
}

/* Modal de edición mejorado */
#modalEditarModulo .modal-content {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

#modalEditarModulo .modal-header {
    padding: 20px 25px;
    border-bottom: none;
}

#modalEditarModulo .modal-body {
    padding: 25px;
}

#modalEditarModulo .form-control {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

#modalEditarModulo .form-control:focus {
    border-color: #37424a;
    box-shadow: 0 0 0 3px rgba(55, 66, 74, 0.1);
}

#modalEditarModulo .alert-info {
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    border: none;
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
}

/* DataTable mejorado */
.dataTables_wrapper .dataTables_filter input {
    border-radius: 20px;
    padding: 8px 15px;
    border: 1px solid #d1d5db;
    margin-left: 10px;
}

.dataTables_wrapper .dataTables_length select {
    border-radius: 6px;
    padding: 5px 30px 5px 10px;
    border: 1px solid #d1d5db;
    margin: 0 10px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 6px !important;
    margin: 0 3px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
    border: none !important;
    color: white !important;
}

/* Animaciones */
@keyframes slideInFromRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.btn-editar-modulo {
    animation: slideInFromRight 0.3s ease;
}

/* Tooltips mejorados */
.btn-editar-modulo[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #1f2937;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    margin-bottom: 5px;
    z-index: 1000;
}
</style>
