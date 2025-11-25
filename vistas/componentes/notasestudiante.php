<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");

    // === 1. DATOS DE EJEMPLO PHP: Programas, Módulos y Estudiantes ===
    // En una aplicación real, esta información vendría de la Base de Datos
    $datos_notas = [
        '1' => [ // ID del Programa
            'nombre' => 'Ingeniería de Software',
            'modulos' => [
                '101' => [
                    'nombre' => 'Fundamentos de Programación',
                    'docente' => 'Dr. Alex Vaca',
                    'nota_aprobacion' => 60, // Nota mínima para aprobar
                    'estudiantes' => [
                        ['id_estudiante' => 301, 'nombre_completo' => 'Ana Morales', 'nota_final' => 85, 'estado' => 'Aprobado'],
                        ['id_estudiante' => 302, 'nombre_completo' => 'Luis Rojas', 'nota_final' => 45, 'estado' => 'Reprobado'],
                        ['id_estudiante' => 303, 'nombre_completo' => 'Sofía Díaz', 'nota_final' => 70, 'estado' => 'Aprobado'],
                        ['id_estudiante' => 306, 'nombre_completo' => 'Carlos Quispe', 'nota_final' => null, 'estado' => 'Pendiente'],
                    ]
                ],
                '102' => [
                    'nombre' => 'Bases de Datos Avanzadas',
                    'docente' => 'Ing. Clara Mendoza',
                    'nota_aprobacion' => 60,
                    'estudiantes' => [
                        ['id_estudiante' => 304, 'nombre_completo' => 'Roberto Paz', 'nota_final' => 60, 'estado' => 'Aprobado'],
                        ['id_estudiante' => 305, 'nombre_completo' => 'Julieta Rios', 'nota_final' => 92, 'estado' => 'Aprobado'],
                        ['id_estudiante' => 307, 'nombre_completo' => 'Daniela Soto', 'nota_final' => null, 'estado' => 'Pendiente'],
                    ]
                ],
            ],
        ],
        '2' => [ // ID del Programa
            'nombre' => 'Diseño Gráfico Digital',
            'modulos' => [
                '201' => [
                    'nombre' => 'Teoría del Color',
                    'docente' => 'Lic. Pedro Soliz',
                    'nota_aprobacion' => 65,
                    'estudiantes' => [
                        ['id_estudiante' => 401, 'nombre_completo' => 'Marta Roca', 'nota_final' => 75, 'estado' => 'Aprobado'],
                        ['id_estudiante' => 402, 'nombre_completo' => 'Jorge Vega', 'nota_final' => null, 'estado' => 'Pendiente'],
                    ]
                ]
            ],
        ],
    ];
    $programas_disponibles = array_map(function($p) {
        return ['id' => key($p), 'nombre' => $p['nombre']];
    }, $datos_notas);
    // ========================================================
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
<!-- El kt-header-mobile se mantiene para compatibilidad con la plantilla base -->
<div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">
        
            <?php $NavBar = new FuncionesControladores(); $NavBar -> NavBarControlador(); ?>
            <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
            <?php $Sidebar = new FuncionesControladores(); $Sidebar -> SidebarControlador(); ?>

            <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                    <!-- begin:: Subheader -->
                    <div class="kt-subheader kt-grid__item" id="kt_subheader">
                        <div class="kt-container ">
                            <div class="kt-subheader__main">
                                <h2 class="">REGISTRO DE NOTAS</h2>
                                <span class="kt-subheader__separator kt-hidden"></span>
                                <div class="kt-subheader__breadcrumbs">
                                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                    <span class="kt-subheader__breadcrumbs-separator"></span>
                                    <h4>PANEL DE CARGA DE NOTAS FINALES</h4>
                                </div>
                            </div>
                            <div class="kt-subheader__toolbar">
                                <div class="kt-subheader__wrapper">
                                    <div id="lafecha" style="font-size:13pt"><?php echo date('d/m/Y H:i:s'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COMIENZO DEL CONTENIDO PRINCIPAL -->
                    <div class="container">
                        <div class="kt-portlet kt-portlet--mobile">
                            <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title">
                                        <i class="fa fa-graduation-cap text-primary"></i> Carga de Calificaciones Finales
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="kt-portlet__body">
                                
                                <!-- FILTROS DE BÚSQUEDA -->
                                <div class="row mb-5">
                                    <!-- SELECT PROGRAMA -->
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="programa-select">**1. Seleccione Programa:**</label>
                                            <select class="form-control select2-buscador" id="programa-select" onchange="cargarModulos()">
                                                <option value="">-- Seleccionar Programa --</option>
                                                <?php foreach ($datos_notas as $id => $programa): ?>
                                                    <option value="<?php echo $id; ?>">
                                                        <?php echo htmlspecialchars($programa['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- SELECT MÓDULO -->
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="modulo-select">**2. Seleccione Módulo:**</label>
                                            <select class="form-control select2-buscador" id="modulo-select" disabled>
                                                <option value="">-- Seleccionar Módulo --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- BOTÓN DE BÚSQUEDA -->
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="form-group w-100">
                                            <button class="btn btn-brand btn-block" onclick="buscarEstudiantes()">
                                                <i class="la la-table"></i> Cargar Notas
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>

                                <!-- INFORMACIÓN DEL MÓDULO SELECCIONADO -->
                                <div id="info-modulo-container" style="display: none;" class="alert alert-light alert-elevate">
                                    <p class="mb-1"><i class="fa fa-chalkboard-teacher"></i> <strong>Docente:</strong> <span id="detalle-docente"></span></p>
                                    <p class="mb-0"><i class="fa fa-percentage"></i> <strong>Nota Mínima de Aprobación:</strong> <span id="detalle-min-nota" class="kt-font-success kt-font-boldest"></span></p>
                                </div>

                                <!-- ÁREA DE TABLA DE NOTAS -->
                                <div id="notas-container" class="mt-4">
                                    <div class="alert alert-secondary text-center">
                                        <i class="flaticon-edit"></i> Use los filtros y presione **Cargar Notas** para empezar a registrar.
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- FOOTER CON BOTÓN DE GUARDAR -->
                            <div class="kt-portlet__foot" id="footer-guardar" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 kt-align-left">
                                        <span class="text-danger small"><i class="fa fa-exclamation-triangle"></i> Asegúrese de verificar todas las notas antes de guardar.</span>
                                    </div>
                                    <div class="col-lg-6 kt-align-right">
                                        <button class="btn btn-success btn-lg" onclick="guardarNotas()">
                                            <i class="fa fa-save"></i> Guardar Notas Finales
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FIN DEL CONTENIDO PRINCIPAL -->

                    <?php $Footer = new FuncionesControladores(); $Footer -> FooterControlador(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT DE JAVASCRIPT Y LÓGICA -->
<script>
    // Copiar los datos de PHP a una variable JavaScript para el manejo en el cliente
    const datosNotas = <?php echo json_encode($datos_notas); ?>;
    const infoModuloContainer = document.getElementById('info-modulo-container');
    const notasContainer = document.getElementById('notas-container');
    const footerGuardar = document.getElementById('footer-guardar');
    const selectPrograma = document.getElementById('programa-select');
    const selectModulo = document.getElementById('modulo-select');

    let moduloActual = null; // Almacena los datos del módulo cargado

    // Inicializar Select2 para buscador (Asume que Select2.js está cargado en la plantilla)
    $(document).ready(function() {
        $('.select2-buscador').select2({
            placeholder: 'Seleccione una opción',
            allowClear: true
        });
    });

    // ==========================================================
    // 1. CARGA EN CASCADA: PROGRAMA -> MÓDULO
    // ==========================================================
    function cargarModulos() {
        const programaId = selectPrograma.value;
        
        // Limpiar y deshabilitar
        selectModulo.innerHTML = '<option value="">-- Seleccionar Módulo --</option>';
        selectModulo.disabled = true;
        
        // Limpiar resultados anteriores
        notasContainer.innerHTML = '<div class="alert alert-secondary text-center"><i class="flaticon-edit"></i> Use los filtros y presione **Cargar Notas** para empezar a registrar.</div>';
        infoModuloContainer.style.display = 'none';
        footerGuardar.style.display = 'none';

        if (programaId && datosNotas[programaId]) {
            const modulos = datosNotas[programaId].modulos;
            for (const id in modulos) {
                const option = new Option(modulos[id].nombre, id);
                selectModulo.add(option);
            }
            selectModulo.disabled = false;
        }
        // Actualizar la vista de Select2
        $('#modulo-select').trigger('change');
    }

    // ==========================================================
    // 2. BUSCAR Y RENDERIZAR ESTUDIANTES
    // ==========================================================
    function buscarEstudiantes() {
        const programaId = selectPrograma.value;
        const moduloId = selectModulo.value;

        if (!programaId || !moduloId) {
            notasContainer.innerHTML = '<div class="alert alert-danger text-center"><i class="flaticon-warning-sign"></i> Debe seleccionar un **Programa** y un **Módulo** válidos.</div>';
            infoModuloContainer.style.display = 'none';
            footerGuardar.style.display = 'none';
            return;
        }

        const programaData = datosNotas[programaId];
        moduloActual = programaData.modulos[moduloId];

        if (!moduloActual) {
             notasContainer.innerHTML = '<div class="alert alert-danger text-center"><i class="flaticon-warning-sign"></i> Módulo no encontrado.</div>';
             return;
        }

        // Mostrar información del módulo
        document.getElementById('detalle-docente').textContent = moduloActual.docente;
        document.getElementById('detalle-min-nota').textContent = moduloActual.nota_aprobacion;
        infoModuloContainer.style.display = 'block';


        // Generar la tabla
        let htmlTabla = `
            <form id="form-notas">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="kt-bg-brand text-white">
                            <th style="width: 5%;">#</th>
                            <th>Estudiante</th>
                            <th style="width: 15%;">Nota Final</th>
                            <th style="width: 15%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (moduloActual.estudiantes.length === 0) {
            htmlTabla += '<tr><td colspan="4" class="text-center text-warning">⚠️ No hay estudiantes inscritos en este módulo.</td></tr>';
            footerGuardar.style.display = 'none';
        } else {
            moduloActual.estudiantes.forEach((estudiante, index) => {
                const notaActual = estudiante.nota_final !== null ? estudiante.nota_final : '';
                const idEstudiante = estudiante.id_estudiante;
                
                // Determinar la clase CSS para el campo de nota
                let notaClass = 'form-control';
                if (notaActual !== '') {
                    notaClass += (notaActual >= moduloActual.nota_aprobacion) ? ' is-valid' : ' is-invalid';
                }

                htmlTabla += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            ${estudiante.nombre_completo}
                            <input type="hidden" name="id_estudiante[${idEstudiante}]" value="${idEstudiante}">
                        </td>
                        <td>
                            <input 
                                type="number" 
                                name="nota[${idEstudiante}]" 
                                class="${notaClass}" 
                                value="${notaActual}" 
                                min="0" 
                                max="100"
                                required 
                                oninput="validarNota(this, ${moduloActual.nota_aprobacion}, ${idEstudiante})"
                            >
                        </td>
                        <td>
                            <span id="estado-${idEstudiante}" class="kt-badge kt-badge--xl ${obtenerClaseEstado(notaActual, moduloActual.nota_aprobacion)}">
                                ${estudiante.estado}
                            </span>
                        </td>
                    </tr>
                `;
            });
            footerGuardar.style.display = 'block'; // Mostrar botón de guardar
        }

        htmlTabla += `
                    </tbody>
                </table>
            </form>
        `;

        notasContainer.innerHTML = htmlTabla;
    }

    // ==========================================================
    // 3. LÓGICA DE VALIDACIÓN Y ESTADO
    // ==========================================================
    
    function obtenerClaseEstado(nota, notaMinima) {
        if (nota === null || nota === '') return 'kt-badge--secondary';
        return (nota >= notaMinima) ? 'kt-badge--success' : 'kt-badge--danger';
    }

    function validarNota(input, notaMinima, idEstudiante) {
        let nota = parseInt(input.value);
        let estadoSpan = document.getElementById(`estado-${idEstudiante}`);

        if (isNaN(nota) || nota < 0 || nota > 100) {
            input.classList.remove('is-valid', 'is-invalid');
            input.classList.add('is-invalid');
            estadoSpan.className = 'kt-badge kt-badge--xl kt-badge--warning';
            estadoSpan.textContent = 'Inválida';
            return;
        }

        input.classList.remove('is-valid', 'is-invalid');
        
        if (nota >= notaMinima) {
            input.classList.add('is-valid');
            estadoSpan.className = 'kt-badge kt-badge--xl kt-badge--success';
            estadoSpan.textContent = 'Aprobado';
        } else {
            input.classList.add('is-invalid');
            estadoSpan.className = 'kt-badge kt-badge--xl kt-badge--danger';
            estadoSpan.textContent = 'Reprobado';
        }
    }

    // ==========================================================
    // 4. FUNCIÓN DE GUARDADO (Placeholder)
    // ==========================================================

    function guardarNotas() {
        const form = document.getElementById('form-notas');
        const formData = new FormData(form);
        const notasParaGuardar = {};

        // Recolectar datos
        formData.forEach((value, key) => {
            // Buscamos solo las claves 'nota[ID]'
            if (key.startsWith('nota[')) {
                // Extraer el ID del estudiante (e.g., de 'nota[301]')
                const idMatch = key.match(/\[(\d+)\]/);
                if (idMatch) {
                    const idEstudiante = idMatch[1];
                    const nota = parseInt(value);

                    if (isNaN(nota) || nota < 0 || nota > 100) {
                        // Usar SweetAlert o una notificación elegante en lugar de alert()
                        alert(`Error: La nota para el estudiante ID ${idEstudiante} no es válida.`);
                        return; // Detener el proceso
                    }

                    notasParaGuardar[idEstudiante] = nota;
                }
            }
        });

        // Simulación de envío de datos al servidor
        console.log("Notas a enviar (Programa:", selectPrograma.value, ", Módulo:", selectModulo.value, "):", notasParaGuardar);
        
        // Aquí iría la llamada AJAX o Fetch API al controlador PHP (ej. `NotasControlador.php`)
        
        // Simulación de éxito
        alert('✅ ¡Notas guardadas exitosamente!');
        // Opcional: Recargar la tabla o mostrar un mensaje de confirmación elegante
    }
</script>