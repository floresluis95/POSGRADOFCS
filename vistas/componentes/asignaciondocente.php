<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<style>
/* ========================================
   ASIGNACIÓN DE DOCENTES - ESTILOS MODERNOS
======================================== */

.assignment-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.assignment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
    border-left-color: #667eea;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.module-code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
}

.module-name {
    font-size: 18px;
    font-weight: 600;
    color: #2d3436;
    margin: 10px 0;
}

.module-program {
    color: #636e72;
    font-size: 14px;
}

.teacher-assigned {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.no-teacher-assigned {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
}

.btn-assign {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-assign:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-remove {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
}

.filter-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.stats-mini {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.stat-mini {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #636e72;
}

.stat-mini i {
    color: #667eea;
    font-size: 16px;
}

.stat-mini strong {
    color: #2d3436;
    font-size: 16px;
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
                                    <h2 class="">ASIGNACIÓN DE DOCENTES</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>GESTIÓN DE MÓDULOS Y DOCENTES</h4>
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

                            <!-- Sección de filtros -->
                            <div class="filter-section">
                                <h4 style="margin-bottom: 20px; color: #2d3436;">
                                    <i class="fas fa-filter"></i> Filtrar Módulos
                                </h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Programa</strong></label>
                                            <select class="form-control" id="filtro-programa">
                                                <option value="">Todos los programas</option>
                                                <?php
                                                require_once 'modelos/conexion.modelo.php';
                                                $stmt = Conexion::Conectar()->prepare("SELECT ProgramaID, NombrePrograma, GradoAcademico FROM programa WHERE Estado = 'ACTIVO' ORDER BY NombrePrograma");
                                                $stmt->execute();
                                                $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($programas as $prog) {
                                                    echo '<option value="' . $prog['ProgramaID'] . '">' .
                                                         htmlspecialchars($prog['GradoAcademico'] . ' - ' . $prog['NombrePrograma']) .
                                                         '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Estado de Asignación</strong></label>
                                            <select class="form-control" id="filtro-estado">
                                                <option value="">Todos</option>
                                                <option value="asignado">Con docente asignado</option>
                                                <option value="sin_asignar">Sin docente asignado</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Buscar</strong></label>
                                            <input type="text" class="form-control" id="filtro-buscar" placeholder="Buscar por nombre o código...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de módulos -->
                            <div id="lista-modulos">
                                <?php
                                // Obtener todos los módulos con información del programa
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
                                        CONCAT(d.Nombre, ' ', d.Apaterno, ' ', IFNULL(d.Amaterno, '')) as NombreDocente,
                                        (SELECT COUNT(DISTINCT ep.EstudianteID)
                                         FROM estudianteprograma ep
                                         WHERE ep.ProgramaID = p.ProgramaID
                                         AND ep.Estado = 'ACTIVO') as TotalEstudiantes
                                    FROM modulos m
                                    INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
                                    LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                                    WHERE m.estadomodulo = 'ACTIVO'
                                    AND p.Estado = 'ACTIVO'
                                    ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo
                                ");
                                $stmtModulos->execute();
                                $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($modulos)) {
                                    echo '<div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle fa-2x"></i>
                                            <h5 class="mt-2">No hay módulos activos registrados</h5>
                                          </div>';
                                } else {
                                    foreach ($modulos as $modulo) {
                                        $tieneDocente = !empty($modulo['DocenteID']);
                                        ?>
                                        <div class="assignment-card"
                                             data-programa-id="<?php echo $modulo['ProgramaId']; ?>"
                                             data-tiene-docente="<?php echo $tieneDocente ? 'true' : 'false'; ?>">
                                            <div class="assignment-header">
                                                <div>
                                                    <span class="module-code">
                                                        <?php echo htmlspecialchars($modulo['codigomodulo']); ?>
                                                    </span>
                                                    <div class="module-name">
                                                        <?php echo htmlspecialchars($modulo['nombremodulo']); ?>
                                                    </div>
                                                    <div class="module-program">
                                                        <i class="fas fa-graduation-cap"></i>
                                                        <?php echo htmlspecialchars($modulo['GradoAcademico'] . ' - ' . $modulo['NombrePrograma']); ?>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <?php if ($tieneDocente): ?>
                                                        <div class="teacher-assigned">
                                                            <i class="fas fa-user-check"></i>
                                                            <?php echo htmlspecialchars($modulo['NombreDocente']); ?>
                                                        </div>
                                                        <div class="mt-2">
                                                            <button class="btn-remove btn-cambiar-docente"
                                                                    data-modulo-id="<?php echo $modulo['Idmodulo']; ?>"
                                                                    data-modulo-nombre="<?php echo htmlspecialchars($modulo['nombremodulo']); ?>">
                                                                <i class="fas fa-exchange-alt"></i> Cambiar Docente
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="no-teacher-assigned">
                                                            <i class="fas fa-user-times"></i> Sin docente asignado
                                                        </div>
                                                        <div class="mt-2">
                                                            <button class="btn-assign btn-asignar-docente"
                                                                    data-modulo-id="<?php echo $modulo['Idmodulo']; ?>"
                                                                    data-modulo-nombre="<?php echo htmlspecialchars($modulo['nombremodulo']); ?>">
                                                                <i class="fas fa-user-plus"></i> Asignar Docente
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="stats-mini">
                                                <div class="stat-mini">
                                                    <i class="fas fa-users"></i>
                                                    <span><strong><?php echo $modulo['TotalEstudiantes']; ?></strong> estudiantes en programa</span>
                                                </div>
                                                <div class="stat-mini">
                                                    <i class="fas fa-code"></i>
                                                    <span>ID: <strong><?php echo $modulo['Idmodulo']; ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                        </div>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterControlador();
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
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
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
                    <button type="button" class="btn btn-primary" id="btn-guardar-asignacion" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
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

        // Filtros
        $('#filtro-programa, #filtro-estado').on('change', aplicarFiltros);
        $('#filtro-buscar').on('keyup', aplicarFiltros);

        function aplicarFiltros() {
            const programaId = $('#filtro-programa').val();
            const estado = $('#filtro-estado').val();
            const busqueda = $('#filtro-buscar').val().toLowerCase();

            $('.assignment-card').each(function() {
                let mostrar = true;

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
            });
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
