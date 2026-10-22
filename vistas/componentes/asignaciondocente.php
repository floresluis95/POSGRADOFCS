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
   ASIGNACIÓN DE DOCENTES (prefijo ad-): una tarjeta con resumen en una línea,
   filtros compactos y la tabla agrupada por programa.
======================================== */
.ad {
    --ad-primario: #1e3c72;
    --ad-primario-suave: #eef2f9;
    --ad-texto: #1f2937;
    --ad-texto-2: #64748b;
    --ad-borde: #e2e8f0;
    --ad-fondo: #f8fafc;
    --ad-exito: #15803d;
    --ad-peligro: #b91c1c;
    color: var(--ad-texto);
}
/* Ancho de pantalla, con el mismo margen a los lados que el subencabezado */
.ad-ancho { padding-left: 25px; padding-right: 25px; max-width: none; }
@media (max-width: 767px) { .ad-ancho { padding-left: 12px; padding-right: 12px; } }
.ad-card { background: #fff; border: 1px solid var(--ad-borde); border-radius: 8px; }
.ad-cab { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 16px; border-bottom: 1px solid var(--ad-borde); }
.ad-cab h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--ad-primario); }
.ad-resumen { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 16px; font-size: 12.5px; color: var(--ad-texto-2); }
.ad-resumen b { color: var(--ad-texto); font-weight: 700; }
.ad-resumen .is-sin b { color: var(--ad-peligro); }
.ad-cobertura { display: inline-flex; align-items: center; gap: 8px; }
.ad-barra { width: 90px; height: 6px; border-radius: 3px; background: var(--ad-borde); overflow: hidden; }
.ad-barra span { display: block; height: 100%; background: var(--ad-primario); }

.ad-filtros { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; padding: 12px 16px; background: var(--ad-fondo);
    border-bottom: 1px solid var(--ad-borde); }
.ad-filtro { flex: 1 1 170px; min-width: 150px; }
.ad-filtro-buscar { flex: 1.5 1 200px; }
.ad-filtro label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px;
    color: var(--ad-texto-2); margin-bottom: 3px; }
.ad .form-control { height: 34px; font-size: 13px; border-color: #cbd5e1; border-radius: 6px; }
.ad .select2-container--default .select2-selection--single { height: 34px; border-color: #cbd5e1; border-radius: 6px; }
.ad .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px; font-size: 13px; }
.ad .select2-container--default .select2-selection--single .select2-selection__arrow { height: 32px; }
.ad-detalle { font-size: 12px; color: var(--ad-texto-2); padding: 6px 16px 0; }

.tabla-asignacion { margin: 0; font-size: 13px; }
.tabla-asignacion thead th { background: #fff; color: var(--ad-texto-2); font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .3px; padding: 8px 12px; border-top: 0; border-bottom: 1px solid var(--ad-borde); }
.tabla-asignacion td { padding: 7px 12px; vertical-align: middle; border-top: 1px solid #f1f5f9; }
.tabla-asignacion tr.fila-modulo:hover td { background: #fafbfd; }
.ad-grupo td { background: var(--ad-primario-suave); border-top: 1px solid var(--ad-borde); padding: 6px 12px; }
.ad-grupo-nombre { font-weight: 700; font-size: 12.5px; color: var(--ad-primario); }
.ad-grupo-meta { font-size: 12px; color: var(--ad-texto-2); margin-left: 8px; }
.ad-codigo { font-size: 12px; color: var(--ad-texto-2); white-space: nowrap; }
.ad-docente { display: inline-flex; align-items: center; gap: 6px; }
.ad-docente i { color: var(--ad-exito); font-size: 12px; }
.ad-sin { color: var(--ad-peligro); font-size: 12.5px; font-weight: 600; }
.ad-sin i { font-size: 12px; margin-right: 4px; }
/* Los botones de la tabla usan el estilo común de acciones en tablas (tema-institucional.css) */
#btn-guardar-asignacion { background: var(--ad-primario); border: 1px solid var(--ad-primario); color: #fff; font-weight: 600; }
#btn-guardar-asignacion:hover, #btn-guardar-asignacion:focus { background: #16305c; color: #fff; }
.ad-vacio { text-align: center; color: var(--ad-texto-2); padding: 28px 16px; font-size: 13px; }

#modalAsignarDocente .modal-content { border-radius: 8px; border: 0; }
#modalAsignarDocente .modal-header { background: #fff; border-bottom: 1px solid var(--ad-borde); padding: 12px 16px; }
#modalAsignarDocente .modal-title { font-size: 15px; font-weight: 700; color: var(--ad-primario); }
#modalAsignarDocente .ad-modulo { font-size: 13px; color: var(--ad-texto-2); margin-bottom: 12px; }
#modalAsignarDocente .ad-modulo b { color: var(--ad-texto); display: block; font-size: 14px; }
@media (max-width: 767px) {
    .ad-col-codigo { display: none; }
}
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
        <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler">
        <i class="flaticon-more-1"></i>
      </button>
    </div>
  </div>

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
                            <div class="kt-container kt-container--fluid ad-ancho">
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
                        <div class="container-fluid ad ad-ancho">

                            <?php
                            // Obtener módulos vigentes (módulos y programas activos)
                            require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';

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
                                ORDER BY p.GradoAcademico, p.NombrePrograma, m.Idmodulo
                            ");
                            if ($esDocente && $docenteIDLogueado) {
                                $stmtModulos->bindValue(':docenteID', $docenteIDLogueado, PDO::PARAM_INT);
                            }
                            $stmtModulos->execute();
                            $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                            // Estadísticas y agrupación por programa
                            $totalModulos = count($modulos);
                            $conDocente = 0;
                            $porPrograma = [];
                            foreach ($modulos as $mod) {
                                if (!empty($mod['DocenteID'])) {
                                    $conDocente++;
                                }
                                $porPrograma[$mod['ProgramaId']][] = $mod;
                            }
                            $sinDocente = $totalModulos - $conDocente;
                            $porcentajeAsignado = $totalModulos > 0 ? round(($conDocente / $totalModulos) * 100) : 0;

                            // Sedes y programas para los filtros
                            $sedes = [];
                            $programas = [];
                            foreach ($porPrograma as $programaId => $mods) {
                                $p = $mods[0];
                                if ($p['Sede'] !== '' && $p['Sede'] !== null) $sedes[$p['Sede']] = true;
                                $programas[] = ['id' => $programaId, 'sede' => $p['Sede'], 'texto' => $p['GradoAcademico'] . ' - ' . $p['NombrePrograma']];
                            }
                            ksort($sedes);
                            usort($programas, function ($a, $b) { return strcmp($a['texto'], $b['texto']); });
                            ?>

                            <div class="ad-card">
                                <!-- Título y resumen en una línea (se recalcula con los filtros) -->
                                <div class="ad-cab">
                                    <h3><i class="fas fa-chalkboard-teacher"></i> Módulos vigentes</h3>
                                    <div class="ad-resumen">
                                        <span><b id="stat-total"><?php echo $totalModulos; ?></b> módulos</span>
                                        <span><b id="stat-con"><?php echo $conDocente; ?></b> con docente</span>
                                        <span class="is-sin"><b id="stat-sin"><?php echo $sinDocente; ?></b> sin docente</span>
                                        <span class="ad-cobertura" title="Porcentaje de módulos con docente asignado">
                                            <span class="ad-barra"><span id="stat-barra" style="width: <?php echo $porcentajeAsignado; ?>%"></span></span>
                                            <b><span id="stat-porcentaje"><?php echo $porcentajeAsignado; ?></span>%</b> cobertura
                                        </span>
                                    </div>
                                </div>

                                <!-- Filtros -->
                                <div class="ad-filtros">
                                    <div class="ad-filtro ad-filtro-buscar">
                                        <label for="filtro-buscar">Buscar</label>
                                        <input type="search" class="form-control" id="filtro-buscar" placeholder="Módulo, código o docente...">
                                    </div>
                                    <div class="ad-filtro">
                                        <label for="filtro-sede">Sede</label>
                                        <select class="form-control select2-uniforme" id="filtro-sede">
                                            <option value="">Todas las sedes</option>
                                            <?php foreach (array_keys($sedes) as $sede): ?>
                                                <option value="<?php echo htmlspecialchars($sede); ?>"><?php echo htmlspecialchars($sede); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro">
                                        <label for="filtro-programa">Programa</label>
                                        <select class="form-control select2-uniforme" id="filtro-programa">
                                            <option value="">Todos los programas</option>
                                            <?php foreach ($programas as $prog): ?>
                                                <option value="<?php echo (int)$prog['id']; ?>" data-sede="<?php echo htmlspecialchars($prog['sede']); ?>"><?php echo htmlspecialchars($prog['texto']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro">
                                        <label for="filtro-estado">Docente</label>
                                        <select class="form-control select2-uniforme" id="filtro-estado">
                                            <option value="">Todos</option>
                                            <option value="asignado">Con docente</option>
                                            <option value="sin_asignar">Sin docente</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="stat-detalle-filtro" class="ad-detalle"></div>

                                <!-- Tabla de módulos, agrupada por programa -->
                                <?php if (empty($modulos)): ?>
                                    <div class="ad-vacio">
                                        <i class="fas fa-info-circle"></i> No hay módulos vigentes (activos y de un programa activo).
                                    </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table tabla-asignacion" id="tabla-modulos">
                                        <thead>
                                            <tr>
                                                <th class="ad-col-codigo" style="width: 110px;">Código</th>
                                                <th>Módulo</th>
                                                <th style="width: 32%;">Docente</th>
                                                <th style="width: 100px;" class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <?php foreach ($porPrograma as $programaId => $modsPrograma): ?>
                                            <?php $prog = $modsPrograma[0]; ?>
                                            <tbody class="ad-programa" data-programa-id="<?php echo (int)$programaId; ?>">
                                                <tr class="ad-grupo">
                                                    <td colspan="4">
                                                        <span class="ad-grupo-nombre"><?php echo htmlspecialchars($prog['NombrePrograma']); ?></span>
                                                        <span class="ad-grupo-meta">
                                                            <?php echo htmlspecialchars(implode(' · ', array_filter([$prog['GradoAcademico'], $prog['Sede'], (int)$prog['TotalEstudiantes'] . ' inscrito(s)']))); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php foreach ($modsPrograma as $modulo): ?>
                                                    <?php $tieneDocente = !empty($modulo['DocenteID']); ?>
                                                    <tr class="fila-modulo"
                                                        data-programa-id="<?php echo (int)$modulo['ProgramaId']; ?>"
                                                        data-sede="<?php echo htmlspecialchars($modulo['Sede']); ?>"
                                                        data-tiene-docente="<?php echo $tieneDocente ? 'true' : 'false'; ?>">
                                                        <td class="ad-col-codigo"><span class="ad-codigo"><?php echo htmlspecialchars($modulo['codigomodulo']); ?></span></td>
                                                        <td><?php echo htmlspecialchars($modulo['nombremodulo']); ?></td>
                                                        <td>
                                                            <?php if ($tieneDocente): ?>
                                                                <span class="ad-docente"><i class="fas fa-check-circle"></i><?php echo htmlspecialchars(trim($modulo['NombreDocente'])); ?></span>
                                                            <?php else: ?>
                                                                <span class="ad-sin"><i class="fas fa-exclamation-circle"></i>Sin asignar</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <button type="button" class="btn <?php echo $tieneDocente ? 'btn-cambiar-docente' : 'btn-asignar-docente'; ?>"
                                                                    data-modulo-id="<?php echo (int)$modulo['Idmodulo']; ?>"
                                                                    data-modulo-nombre="<?php echo htmlspecialchars($modulo['nombremodulo']); ?>"
                                                                    data-docente-id="<?php echo (int)$modulo['DocenteID']; ?>">
                                                                <i class="fas <?php echo $tieneDocente ? 'fa-exchange-alt' : 'fa-user-plus'; ?>"></i> <?php echo $tieneDocente ? 'Cambiar' : 'Asignar'; ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        <?php endforeach; ?>
                                        <tbody id="ad-sin-resultados" style="display:none;">
                                            <tr><td colspan="4" class="ad-vacio">Ningún módulo coincide con los filtros.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
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
    <div class="modal fade ad" id="modalAsignarDocente" tabindex="-1" role="dialog" aria-labelledby="modalAsignarDocenteTitulo">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAsignarDocenteTitulo">Asignar docente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ad-modulo">Módulo<b id="modal-modulo-nombre"></b></div>
                    <div class="form-group mb-0">
                        <label for="select-docente" style="font-size:12px; font-weight:600; color:#64748b;">Docente</label>
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
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm" id="btn-guardar-asignacion">Guardar</button>
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
        $('#filtro-buscar').on('input', aplicarFiltros);

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

                if (sede && $(this).data('sede') != sede) mostrar = false;
                if (programaId && $(this).data('programa-id') != programaId) mostrar = false;
                if (estado === 'asignado' && $(this).data('tiene-docente') !== true) mostrar = false;
                if (estado === 'sin_asignar' && $(this).data('tiene-docente') !== false) mostrar = false;
                // La búsqueda también mira el nombre del programa (va en la fila del grupo)
                if (busqueda) {
                    const texto = ($(this).text() + ' ' + $(this).closest('tbody').find('.ad-grupo').text()).toLowerCase();
                    if (texto.indexOf(busqueda) === -1) mostrar = false;
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

            // Programas sin módulos visibles: se oculta también su encabezado
            $('tbody.ad-programa').each(function() {
                $(this).toggle($(this).find('.fila-modulo').filter(function() { return $(this).css('display') !== 'none'; }).length > 0);
            });
            $('#ad-sin-resultados').toggle(total === 0 && $('.fila-modulo').length > 0);

            actualizarEstadisticas(total, con, sin, sede, programaId);
        }

        function actualizarEstadisticas(total, con, sin, sede, programaId) {
            const porcentaje = total > 0 ? Math.round((con / total) * 100) : 0;
            $('#stat-total').text(total);
            $('#stat-con').text(con);
            $('#stat-sin').text(sin);
            $('#stat-porcentaje').text(porcentaje);
            $('#stat-barra').css('width', porcentaje + '%');

            const hayFiltro = sede || programaId || $('#filtro-estado').val() || $('#filtro-buscar').val();
            if (hayFiltro) {
                let detalle = 'Resultados filtrados';
                if (sede) detalle += ' · Sede: ' + sede;
                if (programaId) detalle += ' · ' + $('#filtro-programa option:selected').text();
                $('#stat-detalle-filtro').text(detalle);
            } else {
                $('#stat-detalle-filtro').text('');
            }
        }

        // Asignar o cambiar docente (al cambiar, queda elegido el docente actual)
        $('.btn-asignar-docente, .btn-cambiar-docente').on('click', function() {
            moduloActual = $(this).data('modulo-id');
            const moduloNombre = $(this).data('modulo-nombre');
            const docenteActual = $(this).data('docente-id');
            $('#modal-modulo-nombre').text(moduloNombre);
            $('#modalAsignarDocenteTitulo').text($(this).hasClass('btn-cambiar-docente') ? 'Cambiar docente' : 'Asignar docente');
            $('#select-docente').val(docenteActual ? String(docenteActual) : '');
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
