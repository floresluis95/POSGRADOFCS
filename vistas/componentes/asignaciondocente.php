<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 11px;
    display: inline-block;
}

.badge-grado {
    background: #e9ecef;
    color: #495057;
    padding: 4px 10px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 11px;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-cambiar-tabla {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
    box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
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
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
                                    <h2 class="">ASIGNACIÓN DE DOCENTES</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>MÓDULOS VIGENTES</h4>
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
                            // Obtener módulos vigentes (año actual)
                            require_once 'modelos/conexion.modelo.php';
                            $anioActual = date('Y');

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
                                     WHERE ep.ProgramaID = p.ProgramaID) as TotalEstudiantes
                                FROM modulos m
                                INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
                                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                                WHERE m.estadomodulo = 'ACTIVO'
                                AND p.Estado = 1
                                AND p.Codigo LIKE :anioActual
                                ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo
                            ");
                            $stmtModulos->bindValue(':anioActual', '%-' . $anioActual, PDO::PARAM_STR);
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
                            ?>

                            <!-- Estadísticas -->
                            <div class="stats-header">
                                <div class="stat-item-header">
                                    <div class="numero"><?php echo $totalModulos; ?></div>
                                    <div class="label">Total Módulos Vigentes</div>
                                </div>
                                <div class="stat-item-header">
                                    <div class="numero"><?php echo $conDocente; ?></div>
                                    <div class="label">Con Docente Asignado</div>
                                </div>
                                <div class="stat-item-header">
                                    <div class="numero"><?php echo $sinDocente; ?></div>
                                    <div class="label">Sin Docente</div>
                                </div>
                            </div>

                            <!-- Sección de filtros -->
                            <div class="filter-box">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong><i class="fas fa-graduation-cap"></i> Programa</strong></label>
                                            <select class="form-control form-control-sm" id="filtro-programa">
                                                <option value="">Todos los programas</option>
                                                <?php
                                                $stmt = Conexion::Conectar()->prepare("
                                                    SELECT DISTINCT p.ProgramaID, p.NombrePrograma, p.GradoAcademico
                                                    FROM programa p
                                                    INNER JOIN modulos m ON p.ProgramaID = m.ProgramaId
                                                    WHERE p.Estado = 1
                                                    AND p.Codigo LIKE :anioActual
                                                    AND m.estadomodulo = 'ACTIVO'
                                                    ORDER BY p.NombrePrograma
                                                ");
                                                $stmt->bindValue(':anioActual', '%-' . $anioActual, PDO::PARAM_STR);
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
                                            <label><strong><i class="fas fa-filter"></i> Estado</strong></label>
                                            <select class="form-control form-control-sm" id="filtro-estado">
                                                <option value="">Todos</option>
                                                <option value="asignado">Con docente</option>
                                                <option value="sin_asignar">Sin docente</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
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
                                        <h5 class="mt-2">No hay módulos vigentes para el año <?php echo $anioActual; ?></h5>
                                        <p>Los módulos vigentes son aquellos que pertenecen a programas del año en curso.</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table tabla-asignacion" id="tabla-modulos">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px;">Código</th>
                                                <th>Módulo</th>
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
                                                        <span class="badge-grado">
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

            $('.fila-modulo').each(function() {
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
