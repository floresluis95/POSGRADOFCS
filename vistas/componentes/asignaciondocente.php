<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Verificar tipo de usuario
$tipoUsuario = isset($_SESSION['Tipo']) ? $_SESSION['Tipo'] : '';
$esDocente = ($tipoUsuario === 'DOC');
$esAdministrador = ($tipoUsuario === 'ADM');

// Si es docente, obtener su DocenteID
$docenteIDLogueado = null;
if ($esDocente && isset($_SESSION['IdUsuario'])) {
    require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
    $stmtDocente = Conexion::Conectar()->prepare("
        SELECT DocenteID FROM docente WHERE UsuarioID = :usuarioID
    ");
    $stmtDocente->bindParam(':usuarioID', $_SESSION['IdUsuario'], PDO::PARAM_INT);
    $stmtDocente->execute();
    $resultDocente = $stmtDocente->fetch(PDO::FETCH_ASSOC);
    if ($resultDocente) {
        $docenteIDLogueado = $resultDocente['DocenteID'];
    }
}
?>

<style>
/* ========================================
   ASIGNACIÓN DE DOCENTES - TABLA OPTIMIZADA
======================================== */

.tabla-asignacion {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.tabla-asignacion thead {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.tabla-asignacion thead th {
    color: white;
    font-weight: 600;
    font-size: 13px;
    padding: 15px 12px;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tabla-asignacion tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.tabla-asignacion tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

.tabla-asignacion tbody td {
    padding: 12px;
    vertical-align: middle;
    font-size: 13px;
}

.badge-codigo {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 11px;
    display: inline-block;
}

.docente-asignado {
    color: #28a745;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.sin-docente {
    color: #dc3545;
    font-weight: 600;
    font-style: italic;
}

.btn-asignar-tabla {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-asignar-tabla:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
    color: white;
}

.btn-cambiar-tabla {
    background: linear-gradient(135deg, #52616e 0%, #37424a 100%);
    color: white;
    border: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cambiar-tabla:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(55, 66, 74, 0.3);
    color: white;
}

.filter-box {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.stats-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.stat-item-header {
    text-align: center;
}

.stat-item-header .numero {
    font-size: 28px;
    font-weight: 700;
}

.stat-item-header .label {
    font-size: 12px;
    opacity: 0.9;
    text-transform: uppercase;
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

                <?php
                $NavBar = new FuncionesControladores();
                $NavBar->NavBarControlador();
                ?>
                <button class="kt-aside-close" id="kt_aside_close_btn"><i class="la la-close"></i></button>
                <?php
                $Sidebar = new FuncionesControladores();
                $Sidebar->SidebarControlador();
                ?>

                <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                    <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <!-- Subheader -->
                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class=""><?php echo $esDocente ? 'MIS MÓDULOS ASIGNADOS' : 'ASIGNACIÓN DE DOCENTES'; ?></h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4><?php echo $esDocente ? 'CONSULTA DE ASIGNACIONES' : 'MÓDULOS VIGENTES'; ?></h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha" style="font-size:13pt"><?php echo date('d/m/Y H:i:s'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="container">

                            <?php
                            // Obtener módulos vigentes (módulos y programas activos)
                            require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
                            $anioActual = date('Y');

                            // Construir consulta según tipo de usuario
                            $sqlWhere = "m.estadomodulo = 'ACTIVO' AND p.Estado = 1";
                            if ($esDocente && $docenteIDLogueado) {
                                $sqlWhere .= " AND m.DocenteID = :docenteID";
                            }

                            $stmtModulos = Conexion::Conectar()->prepare("
                                SELECT
                                    m.Idmodulo,
                                    m.nombremodulo,
                                    m.codigomodulo,
                                    m.DocenteID,
                                    m.ProgramaId,
                                    p.NombrePrograma,
                                    p.GradoAcademico,
                                    p.Codigo as CodigoPrograma,
                                    p.Sede,
                                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', IFNULL(d.Amaterno, '')) as NombreDocente,
                                    (SELECT COUNT(DISTINCT ep.EstudianteID)
                                     FROM estudianteprograma ep
                                     WHERE ep.ProgramaID = p.ProgramaID) as TotalEstudiantes
                                FROM modulos m
                                INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
                                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                                WHERE " . $sqlWhere . "
                                ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo
                            ");
                            if ($esDocente && $docenteIDLogueado) {
                                $stmtModulos->bindValue(':docenteID', $docenteIDLogueado, PDO::PARAM_INT);
                            }
                            $stmtModulos->execute();
                            $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                            // Calcular estadísticas
                            $totalModulos = count($modulos);
                            $conDocente = 0;
                            $sinDocente = 0;
                            foreach ($modulos as $mod) {
                                if (!empty($mod['DocenteID'])) {
                                    $conDocente++;
                                } else {
                                    $sinDocente++;
                                }
                            }
                            $porcentajeAsignado = $totalModulos > 0 ? round(($conDocente / $totalModulos) * 100) : 0;
                            ?>

                            <!-- Estadísticas (se recalculan según los filtros aplicados) -->
                            <div class="stats-header">
                                <div class="stat-item-header">
                                    <div class="numero" id="stat-total"><?php echo $totalModulos; ?></div>
                                    <div class="label">Total Módulos Vigentes</div>
                                </div>
                                <div class="stat-item-header">
                                    <div class="numero" id="stat-con"><?php echo $conDocente; ?></div>
                                    <div class="label">Con Docente Asignado</div>
                                </div>
                                <div class="stat-item-header">
                                    <div class="numero" id="stat-sin"><?php echo $sinDocente; ?></div>
                                    <div class="label">Sin Docente</div>
                                </div>
                                <div class="stat-item-header">
                                    <div class="numero"><span id="stat-porcentaje"><?php echo $porcentajeAsignado; ?></span>%</div>
                                    <div class="label">% Cobertura Docente</div>
                                </div>
                            </div>
                            <div id="stat-detalle-filtro" class="text-center" style="margin:-10px 0 20px; font-size:12px; color:#6c757d;"></div>

                            <!-- Sección de filtros -->
                            <div class="filter-box">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><strong><i class="fas fa-map-marker-alt"></i> Sede</strong></label>
                                            <select class="form-control select2-uniforme" id="filtro-sede">
                                                <option value="">Todas las sedes</option>
                                                <?php
                                                $stmtSedes = Conexion::Conectar()->prepare("
                                                    SELECT DISTINCT p.Sede
                                                    FROM programa p
                                                    INNER JOIN modulos m ON p.ProgramaID = m.ProgramaId
                                                    WHERE p.Estado = 1
                                                    AND m.estadomodulo = 'ACTIVO'
                                                    ORDER BY p.Sede
                                                ");
                                                $stmtSedes->execute();
                                                $sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($sedes as $s) {
                                                    if ($s['Sede'] === '' || $s['Sede'] === null) continue;
                                                    echo '<option value="' . htmlspecialchars($s['Sede']) . '">' .
                                                         htmlspecialchars($s['Sede']) .
                                                         '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><strong><i class="fas fa-graduation-cap"></i> Programa</strong></label>
                                            <select class="form-control select2-uniforme" id="filtro-programa">
                                                <option value="">Todos los programas</option>
                                                <?php
                                                $stmt = Conexion::Conectar()->prepare("
                                                    SELECT DISTINCT p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Sede
                                                    FROM programa p
                                                    INNER JOIN modulos m ON p.ProgramaID = m.ProgramaId
                                                    WHERE p.Estado = 1
                                                    AND m.estadomodulo = 'ACTIVO'
                                                    ORDER BY p.NombrePrograma
                                                ");
                                                $stmt->execute();
                                                $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($programas as $prog) {
                                                    echo '<option value="' . $prog['ProgramaID'] . '" data-sede="' . htmlspecialchars($prog['Sede']) . '">' .
                                                         htmlspecialchars($prog['GradoAcademico'] . ' - ' . $prog['NombrePrograma']) .
                                                         '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><strong><i class="fas fa-filter"></i> Estado</strong></label>
                                            <select class="form-control select2-uniforme" id="filtro-estado">
                                                <option value="">Todos</option>
                                                <option value="asignado">Con docente</option>
                                                <option value="sin_asignar">Sin docente</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><strong><i class="fas fa-search"></i> Buscar</strong></label>
                                            <input type="text" class="form-control form-control-sm" id="filtro-buscar" placeholder="Código o nombre...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de módulos -->
                            <div class="table-responsive">
                                <?php if (empty($modulos)): ?>
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                        <h5 class="mt-2">No hay módulos vigentes</h5>
                                        <p>Los módulos vigentes son aquellos que están activos y pertenecen a un programa activo.</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table tabla-asignacion" id="tabla-modulos">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">Código</th>
                                                <th>Módulo</th>
                                                <th style="width: 90px;">Sede</th>
                                                <th style="width: 120px;">Grado</th>
                                                <th>Programa</th>
                                                <th style="width: 200px;">Docente Asignado</th>
                                                <th style="width: 80px;" class="text-center">Inscritos</th>
                                                <th style="width: 120px;" class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($modulos as $modulo): ?>
                                                <?php $tieneDocente = !empty($modulo['DocenteID']); ?>
                                                <tr class="fila-modulo"
                                                    data-programa-id="<?php echo $modulo['ProgramaId']; ?>"
                                                    data-sede="<?php echo htmlspecialchars($modulo['Sede']); ?>"
                                                    data-tiene-docente="<?php echo $tieneDocente ? 'true' : 'false'; ?>">
                                                    <td>
                                                        <span class="badge-codigo">
                                                            <?php echo htmlspecialchars($modulo['codigomodulo']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($modulo['nombremodulo']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <small><?php echo htmlspecialchars($modulo['Sede']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="<?php echo FuncionesControladores::ClaseGrado($modulo['GradoAcademico']); ?>">
                                                            <?php echo htmlspecialchars($modulo['GradoAcademico']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small><?php echo htmlspecialchars($modulo['NombrePrograma']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($tieneDocente): ?>
                                                            <div class="docente-asignado">
                                                                <i class="fas fa-check-circle"></i>
                                                                <?php echo htmlspecialchars($modulo['NombreDocente']); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="sin-docente">
                                                                <i class="fas fa-exclamation-circle"></i> Sin asignar
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong><?php echo $modulo['TotalEstudiantes']; ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($tieneDocente): ?>
                                                            <button class="btn btn-cambiar-tabla btn-cambiar-docente"
                                                                    data-modulo-id="<?php echo $modulo['Idmodulo']; ?>"
                                                                    data-modulo-nombre="<?php echo htmlspecialchars($modulo['nombremodulo']); ?>">
                                                                <i class="fas fa-exchange-alt"></i> Cambiar
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-asignar-tabla btn-asignar-docente"
                                                                    data-modulo-id="<?php echo $modulo['Idmodulo']; ?>"
                                                                    data-modulo-nombre="<?php echo htmlspecialchars($modulo['nombremodulo']); ?>">
                                                                <i class="fas fa-user-plus"></i> Asignar
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>

                        </div>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterResumidoControlador();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para asignar docente -->
    <div class="modal fade" id="modalAsignarDocente" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user-plus"></i> Asignar Docente al Módulo
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Módulo:</strong> <span id="modal-modulo-nombre"></span>
                    </div>
                    <div class="form-group">
                        <label><strong>Seleccionar Docente</strong></label>
                        <select class="form-control" id="select-docente">
                            <option value="">-- Seleccione un docente --</option>
                            <?php
                            $stmtDocentes = Conexion::Conectar()->prepare("
                                SELECT DocenteID, Nombre, Apaterno, Amaterno, Especialidad
                                FROM docente
                                WHERE Estado = 1
                                ORDER BY Apaterno, Amaterno, Nombre
                            ");
                            $stmtDocentes->execute();
                            $docentes = $stmtDocentes->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($docentes as $doc) {
                                $nombreCompleto = trim($doc['Nombre'] . ' ' . $doc['Apaterno'] . ' ' . ($doc['Amaterno'] ?? ''));
                                $especialidad = !empty($doc['Especialidad']) ? ' - ' . $doc['Especialidad'] : '';
                                echo '<option value="' . $doc['DocenteID'] . '">' .
                                     htmlspecialchars($nombreCompleto . $especialidad) .
                                     '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-asignacion" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">
                        <i class="fas fa-save"></i> Guardar Asignación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script>
    $(document).ready(function() {
        let moduloActual = null;

        // Select2 (mismo tamaño/estilo en los tres filtros)
        $('.select2-uniforme').select2({ width: '100%' });

        // Copia completa de las opciones de Programa (con su Sede), para
        // reconstruir el combo al filtrar por Sede: con Select2 no basta con
        // ocultar <option> por CSS como en un <select> nativo, hay que
        // quitarlas/ponerlas del DOM.
        const programasCache = [];
        $('#filtro-programa option[value!=""]').each(function() {
            programasCache.push({
                value: $(this).val(),
                sede: $(this).data('sede') || '',
                // .html() conserva el escape que ya aplicó el servidor;
                // .text() lo decodificaría y reinyectarlo sería un riesgo XSS.
                html: $(this).html()
            });
        });

        // Filtros
        $('#filtro-sede, #filtro-programa, #filtro-estado').on('change', aplicarFiltros);
        $('#filtro-buscar').on('keyup', aplicarFiltros);

        // Cascada: al elegir sede, limitar el combo de programas a los de esa sede
        $('#filtro-sede').on('change', function() {
            const sede = $(this).val();
            const $programa = $('#filtro-programa');
            const programaSeleccionado = $programa.val();

            let opciones = '<option value="">Todos los programas</option>';
            programasCache.forEach(function(p) {
                if (!sede || p.sede === sede) {
                    opciones += '<option value="' + p.value + '" data-sede="' + p.sede + '">' + p.html + '</option>';
                }
            });
            $programa.html(opciones);

            // Conservar el programa elegido si sigue perteneciendo a la sede
            if (programaSeleccionado && $programa.find('option[value="' + programaSeleccionado + '"]').length) {
                $programa.val(programaSeleccionado);
            }
            $programa.trigger('change');
        });

        function aplicarFiltros() {
            const sede = $('#filtro-sede').val();
            const programaId = $('#filtro-programa').val();
            const estado = $('#filtro-estado').val();
            const busqueda = $('#filtro-buscar').val().toLowerCase();

            let total = 0, con = 0, sin = 0;

            $('.fila-modulo').each(function() {
                let mostrar = true;

                // Filtro por sede
                if (sede && $(this).data('sede') != sede) {
                    mostrar = false;
                }

                // Filtro por programa
                if (programaId && $(this).data('programa-id') != programaId) {
                    mostrar = false;
                }

                // Filtro por estado
                if (estado === 'asignado' && $(this).data('tiene-docente') !== true) {
                    mostrar = false;
                }
                if (estado === 'sin_asignar' && $(this).data('tiene-docente') !== false) {
                    mostrar = false;
                }

                // Filtro por búsqueda
                if (busqueda) {
                    const texto = $(this).text().toLowerCase();
                    if (texto.indexOf(busqueda) === -1) {
                        mostrar = false;
                    }
                }

                $(this).toggle(mostrar);

                if (mostrar) {
                    total++;
                    if ($(this).data('tiene-docente') === true) {
                        con++;
                    } else {
                        sin++;
                    }
                }
            });

            actualizarEstadisticas(total, con, sin, sede, programaId);
        }

        function actualizarEstadisticas(total, con, sin, sede, programaId) {
            $('#stat-total').text(total);
            $('#stat-con').text(con);
            $('#stat-sin').text(sin);
            $('#stat-porcentaje').text(total > 0 ? Math.round((con / total) * 100) : 0);

            const hayFiltro = sede || programaId || $('#filtro-estado').val() || $('#filtro-buscar').val();
            if (hayFiltro) {
                let detalle = 'Mostrando resultados filtrados';
                if (sede) detalle += ' · Sede: ' + sede;
                if (programaId) detalle += ' · Programa: ' + $('#filtro-programa option:selected').text();
                $('#stat-detalle-filtro').text(detalle);
            } else {
                $('#stat-detalle-filtro').text('');
            }
        }

        // Asignar docente
        $('.btn-asignar-docente, .btn-cambiar-docente').on('click', function() {
            moduloActual = $(this).data('modulo-id');
            const moduloNombre = $(this).data('modulo-nombre');
            $('#modal-modulo-nombre').text(moduloNombre);
            $('#select-docente').val('');
            $('#modalAsignarDocente').modal('show');
        });

        // Guardar asignación
        $('#btn-guardar-asignacion').on('click', function() {
            const docenteID = $('#select-docente').val();

            if (!docenteID) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Debe seleccionar un docente'
                });
                return;
            }

            // Actualizar en la base de datos
            $.ajax({
                url: 'ajax/asignacion.ajax.php',
                method: 'POST',
                data: {
                    accion: 'asignar',
                    moduloID: moduloActual,
                    docenteID: docenteID
                },
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Docente asignado correctamente',
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'No se pudo asignar el docente'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error en la respuesta del servidor'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de comunicación con el servidor'
                    });
                }
            });

            $('#modalAsignarDocente').modal('hide');
        });
    });
    </script>
</body>
