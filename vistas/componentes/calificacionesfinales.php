<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");

    // === SIMULACIÓN: ID DEL ESTUDIANTE LOGUEADO (ASUMIDO DE SESIÓN) ===
    // Usaremos el ID 301 (Ana Morales) para filtrar los programas.
    const ID_ESTUDIANTE_ACTUAL = 301;
    // ==================================================================

    // === 1. DATOS DE EJEMPLO PHP: Programas, Módulos y Estudiantes ===
    // En una aplicación real, esta información vendría de la Base de Datos
    $datos_maestros = [
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
                    ]
                ],
                '102' => [
                    'nombre' => 'Bases de Datos Avanzadas',
                    'docente' => 'Ing. Clara Mendoza',
                    'nota_aprobacion' => 60,
                    'estudiantes' => [
                        ['id_estudiante' => 301, 'nombre_completo' => 'Ana Morales', 'nota_final' => 60, 'estado' => 'Aprobado'], // Ana Morales en este módulo
                        ['id_estudiante' => 304, 'nombre_completo' => 'Roberto Paz', 'nota_final' => 60, 'estado' => 'Aprobado'],
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
                    ]
                ]
            ],
        ],
        '3' => [ // ID del Programa
            'nombre' => 'Marketing Digital',
            'modulos' => [
                '301' => [
                    'nombre' => 'Estrategias SEO',
                    'docente' => 'Lic. Jaime Pardo',
                    'nota_aprobacion' => 70,
                    'estudiantes' => [
                        ['id_estudiante' => 301, 'nombre_completo' => 'Ana Morales', 'nota_final' => 55, 'estado' => 'Reprobado'], // Ana Morales en otro programa
                    ]
                ]
            ],
        ],
    ];

    // === 2. FILTRADO DE DATOS PARA EL ESTUDIANTE Y PROGRAMAS INSCRITOS ===
    $programas_inscritos = [];
    $nombre_estudiante = "Estudiante Desconocido";
    $primer_programa_id = null;
    $primer_programa_nombre = null;

    foreach ($datos_maestros as $id_programa => $programa) {
        $programa_nombre = $programa['nombre'];
        $modulos_del_programa = [];
        $esta_inscrito_en_programa = false;

        foreach ($programa['modulos'] as $id_modulo => $modulo) {
            $nota_minima = $modulo['nota_aprobacion'];
            
            foreach ($modulo['estudiantes'] as $estudiante) {
                if ($estudiante['id_estudiante'] == ID_ESTUDIANTE_ACTUAL) {
                    $esta_inscrito_en_programa = true;
                    $nombre_estudiante = $estudiante['nombre_completo']; // Asigna el nombre
                    
                    $nota_final = $estudiante['nota_final'];
                    
                    // Calcular el estado basado en la nota mínima por seguridad
                    if ($nota_final !== null) {
                        $estado = ($nota_final >= $nota_minima) ? 'Aprobado' : 'Reprobado';
                    } else {
                        $estado = 'Pendiente';
                    }

                    $modulos_del_programa[] = [
                        'modulo' => $modulo['nombre'],
                        'docente' => $modulo['docente'],
                        'nota_aprobacion' => $nota_minima,
                        'nota_final' => $nota_final,
                        'estado' => $estado,
                    ];
                    break; // Pasa al siguiente módulo/programa una vez encontrado
                }
            }
        }
        
        if ($esta_inscrito_en_programa) {
            if ($primer_programa_id === null) {
                $primer_programa_id = $id_programa;
                $primer_programa_nombre = $programa_nombre;
            }
            $programas_inscritos[$id_programa] = [
                'nombre' => $programa_nombre,
                'modulos' => $modulos_del_programa
            ];
        }
    }
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
                <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                    <!-- begin:: Subheader -->
                    <div class="kt-subheader kt-grid__item" id="kt_subheader">
                        <div class="kt-container ">
                            <div class="kt-subheader__main">
                                <h2 class="">CALIFICACIONES</h2>
                                <span class="kt-subheader__separator kt-hidden"></span>
                                <div class="kt-subheader__breadcrumbs">
                                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                    <span class="kt-subheader__breadcrumbs-separator"></span>
                                    <h4>REPORTE DE NOTAS FINALES</h4>
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
                                        <i class="fa fa-user text-primary"></i> Mis Calificaciones: **<?php echo htmlspecialchars($nombre_estudiante); ?>**
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="kt-portlet__body">
                                
                                <!-- FILTROS DE BÚSQUEDA DEL ESTUDIANTE -->
                                <div class="row mb-5 justify-content-center">
                                    <!-- SELECT PROGRAMA -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="programa-select-estudiante">**1. Seleccione el Programa:**</label>
                                            <select class="form-control select2-buscador" id="programa-select-estudiante">
                                                <option value="">-- Seleccionar Programa Inscrito --</option>
                                                <?php foreach ($programas_inscritos as $id => $programa): ?>
                                                    <option value="<?php echo $id; ?>" 
                                                            <?php echo ($id == $primer_programa_id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($programa['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- BOTÓN DE BÚSQUEDA -->
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-group w-100">
                                            <button class="btn btn-brand btn-block" onclick="buscarCalificaciones()">
                                                <i class="la la-search"></i> Buscar Calificaciones
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- INFORMACIÓN DEL PROGRAMA SELECCIONADO -->
                                <div id="info-programa-container" style="display: none;" class="alert alert-light alert-elevate">
                                    <p class="mb-0"><i class="fa fa-book-open"></i> <strong>Programa Seleccionado:</strong> <span id="detalle-programa" class="kt-font-brand kt-font-boldest"></span></p>
                                </div>


                                <!-- ÁREA DE TABLA DE NOTAS -->
                                <div id="notas-container" class="mt-4">
                                    <div class="alert alert-secondary text-center">
                                        <i class="flaticon-edit"></i> Seleccione un **Programa Inscrito** y presione **Buscar Calificaciones**.
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
    // Copiar los datos filtrados de PHP a una variable JavaScript
    const programasInscritos = <?php echo json_encode($programas_inscritos); ?>;
    const notasContainer = document.getElementById('notas-container');
    const selectProgramaEstudiante = document.getElementById('programa-select-estudiante');
    const infoProgramaContainer = document.getElementById('info-programa-container');
    const detalleProgramaSpan = document.getElementById('detalle-programa');
    
    // El ID del primer programa inscrito para cargar al inicio si existe
    const primerProgramaId = "<?php echo $primer_programa_id; ?>";
    

    // ==========================================================
    // INICIALIZACIÓN
    // ==========================================================
    $(document).ready(function() {
        // Inicializar Select2 para buscador (Asume que Select2.js está cargado en la plantilla)
        $('.select2-buscador').select2({
            placeholder: 'Seleccione una opción',
            allowClear: true
        });
        
        // Cargar las calificaciones del primer programa al inicio
        if (primerProgramaId) {
            buscarCalificaciones(primerProgramaId);
        }
    });

    // ==========================================================
    // FUNCIÓN PRINCIPAL: BUSCAR Y RENDERIZAR CALIFICACIONES
    // ==========================================================
    function buscarCalificaciones(initialId = null) {
        const programaId = initialId || selectProgramaEstudiante.value;

        if (!programaId || !programasInscritos[programaId]) {
            notasContainer.innerHTML = '<div class="alert alert-danger text-center"><i class="flaticon-warning-sign"></i> Debe seleccionar un **Programa Inscrito** válido.</div>';
            infoProgramaContainer.style.display = 'none';
            return;
        }

        const programaData = programasInscritos[programaId];

        // Mostrar información del programa
        detalleProgramaSpan.textContent = programaData.nombre;
        infoProgramaContainer.style.display = 'block';

        // Generar la tabla
        let htmlTabla = `
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr class="kt-bg-brand text-white">
                            <th style="width: 5%;">#</th>
                            <th>Módulo</th>
                            <th class="text-center">Docente</th>
                            <th class="text-center">Nota Mín. Aprob.</th>
                            <th class="text-center">Nota Final</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (programaData.modulos.length === 0) {
            htmlTabla += '<tr><td colspan="6" class="text-center text-warning">⚠️ No hay calificaciones disponibles para este programa en este momento.</td></tr>';
        } else {
            programaData.modulos.forEach((calificacion, index) => {
                const notaFinal = calificacion.nota_final;
                const notaAprobacion = calificacion.nota_aprobacion;
                
                // Determinar clases CSS
                let notaClase = '';
                if (notaFinal !== null) {
                    notaClase = (notaFinal >= notaAprobacion) ? 'kt-font-success kt-font-boldest' : 'kt-font-danger kt-font-boldest';
                }
                
                let estadoClase = 'kt-badge--secondary';
                switch (calificacion.estado) {
                    case 'Aprobado':
                        estadoClase = 'kt-badge--success';
                        break;
                    case 'Reprobado':
                        estadoClase = 'kt-badge--danger';
                        break;
                    case 'Pendiente':
                    default:
                        estadoClase = 'kt-badge--secondary';
                        break;
                }
                
                htmlTabla += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${calificacion.modulo}</td>
                        <td class="text-center">${calificacion.docente}</td>
                        <td class="text-center">${notaAprobacion}</td>
                        <td class="text-center ${notaClase}">
                            ${notaFinal !== null ? notaFinal : '--'}
                        </td>
                        <td class="text-center">
                            <span class="kt-badge kt-badge--xl ${estadoClase} kt-badge--inline">
                                ${calificacion.estado}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }

        htmlTabla += `
                    </tbody>
                </table>
            </div>
        `;

        notasContainer.innerHTML = htmlTabla;
    }

    // ==========================================================
    // LÓGICA DE LA FECHA (Mantenida del código original)
    // ==========================================================
    setInterval(function() {
        const lafecha = document.getElementById('lafecha');
        if (lafecha) {
            const now = new Date();
            const pad = (num) => (num < 10 ? '0' : '') + num;
            const date = `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()}`;
            const time = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
            lafecha.textContent = `${date} ${time}`;
        }
    }, 1000);
</script>