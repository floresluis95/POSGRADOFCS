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

/* Pestañas: Asignar docentes / Carga docente */
.ad-tabs { display: flex; gap: 4px; margin-bottom: -1px; position: relative; z-index: 1; }
.ad-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-size: 13.5px; font-weight: 600;
    color: var(--ad-texto-2); background: #eef1f5; border: 1px solid var(--ad-borde); border-bottom: 0;
    border-radius: 8px 8px 0 0; cursor: pointer; }
.ad-tab:hover { color: var(--ad-primario); background: #f6f8fb; }
.ad-tab.is-activa { color: var(--ad-primario); background: #fff; box-shadow: inset 0 3px 0 var(--ad-primario); }
.ad-tab:focus { outline: none; }
.ad-tab-num { min-width: 22px; padding: 0 7px; border-radius: 10px; font-size: 11.5px; line-height: 19px; text-align: center;
    background: var(--ad-borde); color: var(--ad-texto-2); }
.ad-tab.is-activa .ad-tab-num { background: var(--ad-primario); color: #fff; }
.ad-panel > .ad-card { border-top-left-radius: 0; }
@media (max-width: 575px) { .ad-tab { flex: 1 1 0; justify-content: center; padding: 10px 8px; font-size: 12.5px; } }

/* Carga docente */
.ad-cab-ayuda { font-size: 12px; font-weight: 400; color: var(--ad-texto-2); margin-left: 6px; }
@media (max-width: 767px) { .ad-cab-ayuda { display: none; } }
.ad-filtro-corto { flex: 0 1 150px; min-width: 120px; }
.ad-filtro-boton { flex: 0 0 auto; min-width: 0; }
.ad-filtro-boton .btn { height: 34px; }
.cd-barra { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; padding: 12px 16px 8px; }
.cd-vistas { display: inline-flex; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; }
.cd-vista { border: 0; background: #fff; padding: 6px 14px; font-size: 12.5px; font-weight: 600; color: var(--ad-texto-2); cursor: pointer; }
.cd-vista + .cd-vista { border-left: 1px solid #cbd5e1; }
.cd-vista.is-activa { background: var(--ad-primario); color: #fff; }
.cd-vista:focus { outline: none; }
.cd-filtro-texto { flex: 1 1 200px; font-size: 12px; color: var(--ad-texto-2); }
.cd-filtro-texto b { color: var(--ad-texto); }
.cd-exportar { margin-left: auto; }
.cd-ayuda { margin: 0; padding: 0 16px 6px; font-size: 12px; color: var(--ad-texto-2); }
.cd-col-oculta { display: none; }
#tabla-carga-resumen tbody tr.cd-docente-fila { cursor: pointer; }
#tabla-carga-resumen tbody tr.cd-docente-fila:hover td { background: #f5f8fd; }
.cd-ver { font-size: 12px; padding: 3px 10px; white-space: nowrap; border: 1px solid #cbd5e1; background: #fff; color: var(--ad-primario); border-radius: 5px; }
.cd-docente-fila:hover .cd-ver { background: var(--ad-primario); color: #fff; border-color: var(--ad-primario); }
.cd-grupo td { background: var(--ad-primario-suave); border-top: 1px solid var(--ad-borde); padding: 7px 12px; }
.cd-grupo .ad-grupo-nombre i { font-size: 11px; margin-right: 3px; opacity: .7; }
.cd-grado { display: inline-block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
    color: var(--ad-texto-2); background: #f1f5f9; border-radius: 4px; padding: 0 6px; margin-right: 4px; }
.cd-sede { display: block; font-size: 11px; color: var(--ad-texto-2); }
.cd-vista i { margin-right: 5px; }
@media (max-width: 767px) { .cd-col-especialidad, .cd-col-movil { display: none; } }
@media (max-width: 575px) { .ad-tab { white-space: nowrap; } .ad-tab .ad-tab-num { display: none; } }
.cd-cantidad { display: inline-block; min-width: 28px; padding: 1px 8px; border-radius: 10px; font-weight: 700;
    background: var(--ad-primario-suave); color: var(--ad-primario); }
.ad-estado { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 11.5px; font-weight: 600;
    background: #f1f5f9; color: var(--ad-texto-2); }
.ad-estado.is-activo { background: #dcfce7; color: var(--ad-exito); }
.ad-estado.is-validado, .ad-estado.is-cerrado { background: var(--ad-primario-suave); color: var(--ad-primario); }
.ad-estado.is-inactivo { background: #fee2e2; color: var(--ad-peligro); }
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
        <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
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
                                        <h4 id="ad-subtitulo"><?php echo $esDocente ? 'CONSULTA DE ASIGNACIONES' : 'MÓDULOS VIGENTES'; ?></h4>
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

                            // ===== Carga docente: cuántos módulos tiene cada docente (vigentes y
                            // concluidos), filtrable por docente, año, grado, programa y estado =====
                            require_once __DIR__ . '/../../modelos/docentes.modelo.php';
                            $carga = DocentesModelo::CargaDocenteModelo();

                            $cdDocentes = [];
                            $cdAnios = [];
                            $cdGrados = [];
                            $cdProgramas = [];
                            $cdEstados = [];
                            foreach ($carga as $c) {
                                $cdDocentes[$c['DocenteID']] = trim($c['Apaterno'] . ' ' . ($c['Amaterno'] ?? '') . ' ' . $c['Nombre']);
                                if (!empty($c['Anio'])) $cdAnios[$c['Anio']] = true;
                                if ($c['GradoAcademico'] !== '') $cdGrados[$c['GradoAcademico']] = true;
                                $cdProgramas[$c['ProgramaID']] = ['grado' => $c['GradoAcademico'], 'texto' => $c['GradoAcademico'] . ' - ' . $c['NombrePrograma'] . ($c['Sede'] ? ' (' . $c['Sede'] . ')' : '')];
                                $cdEstados[$c['estadomodulo']] = true;
                            }
                            asort($cdDocentes);
                            krsort($cdAnios);
                            ksort($cdGrados);
                            uasort($cdProgramas, function ($a, $b) { return strcmp($a['texto'], $b['texto']); });
                            ksort($cdEstados);
                            ?>

                            <!-- Dos pestañas: asignar docentes a los módulos vigentes / consultar la carga de cada docente -->
                            <div class="ad-tabs" role="tablist">
                                <button type="button" class="ad-tab is-activa" role="tab" data-panel="asignar" aria-selected="true">
                                    <i class="fas fa-chalkboard-teacher"></i> Asignar docentes
                                    <span class="ad-tab-num"><?php echo $totalModulos; ?></span>
                                </button>
                                <button type="button" class="ad-tab" role="tab" data-panel="carga" aria-selected="false">
                                    <i class="fas fa-user-tie"></i> Carga docente
                                    <span class="ad-tab-num"><?php echo count($cdDocentes); ?></span>
                                </button>
                            </div>

                            <div class="ad-panel" id="panel-asignar" role="tabpanel">
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
                            // Detalle agrupado por docente (en el orden de la consulta: por apellido)
                            $cargaPorDocente = [];
                            foreach ($carga as $c) {
                                $cargaPorDocente[$c['DocenteID']][] = $c;
                            }
                            ?>
                            <div class="ad-panel" id="panel-carga" role="tabpanel" style="display:none;">
                            <div class="ad-card ad-carga" id="carga-docente">
                                <div class="ad-cab">
                                    <h3><i class="fas fa-user-tie"></i> Carga docente <span class="ad-cab-ayuda">módulos asignados a cada docente, vigentes y concluidos</span></h3>
                                    <div class="ad-resumen">
                                        <span><b id="cd-total-docentes"><?php echo count($cdDocentes); ?></b> docente(s)</span>
                                        <span><b id="cd-total-modulos"><?php echo count($carga); ?></b> módulo(s)</span>
                                        <span><b id="cd-total-programas"><?php echo count($cdProgramas); ?></b> programa(s)</span>
                                    </div>
                                </div>

                                <div class="ad-filtros">
                                    <div class="ad-filtro ad-filtro-buscar">
                                        <label for="cd-docente">Docente</label>
                                        <select class="form-control select2-uniforme" id="cd-docente">
                                            <option value="">Todos los docentes</option>
                                            <?php foreach ($cdDocentes as $id => $nombre): ?>
                                                <option value="<?php echo (int)$id; ?>"><?php echo htmlspecialchars($nombre); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro ad-filtro-corto">
                                        <label for="cd-anio">Año</label>
                                        <select class="form-control select2-uniforme" id="cd-anio">
                                            <option value="">Todos</option>
                                            <?php foreach (array_keys($cdAnios) as $anio): ?>
                                                <option value="<?php echo (int)$anio; ?>"><?php echo (int)$anio; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro ad-filtro-corto">
                                        <label for="cd-grado">Grado</label>
                                        <select class="form-control select2-uniforme" id="cd-grado">
                                            <option value="">Todos</option>
                                            <?php foreach (array_keys($cdGrados) as $grado): ?>
                                                <option value="<?php echo htmlspecialchars($grado); ?>"><?php echo htmlspecialchars($grado); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro ad-filtro-buscar">
                                        <label for="cd-programa">Programa</label>
                                        <select class="form-control select2-uniforme" id="cd-programa">
                                            <option value="">Todos los programas</option>
                                            <?php foreach ($cdProgramas as $id => $prog): ?>
                                                <option value="<?php echo (int)$id; ?>" data-grado="<?php echo htmlspecialchars($prog['grado']); ?>"><?php echo htmlspecialchars($prog['texto']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro ad-filtro-corto">
                                        <label for="cd-estado">Estado del módulo</label>
                                        <select class="form-control select2-uniforme" id="cd-estado">
                                            <option value="">Todos</option>
                                            <?php foreach (array_keys($cdEstados) as $estado): ?>
                                                <option value="<?php echo htmlspecialchars($estado); ?>"><?php echo htmlspecialchars(mb_convert_case($estado, MB_CASE_TITLE, 'UTF-8')); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ad-filtro ad-filtro-boton">
                                        <button type="button" class="btn btn-sm btn-light" id="cd-limpiar" style="display:none;"><i class="fas fa-times"></i> Quitar filtros</button>
                                    </div>
                                </div>

                                <?php if (empty($carga)): ?>
                                    <div class="ad-vacio">
                                        <i class="fas fa-info-circle"></i> Todavía no hay módulos con docente asignado.
                                    </div>
                                <?php else: ?>
                                <!-- Una sola tabla a la vez: el resumen por docente o la lista de sus módulos -->
                                <div class="cd-barra">
                                    <div class="cd-vistas" role="tablist">
                                        <button type="button" class="cd-vista is-activa" data-vista="resumen"><i class="fas fa-users"></i> Por docente</button>
                                        <button type="button" class="cd-vista" data-vista="detalle"><i class="fas fa-list-ul"></i> Lista de módulos</button>
                                    </div>
                                    <div id="cd-detalle-filtro" class="cd-filtro-texto"></div>
                                    <div class="cd-exportar" id="cd-botones-resumen"></div>
                                    <div class="cd-exportar" id="cd-botones-detalle" style="display:none;"></div>
                                </div>

                                <div class="cd-vista-panel" id="cd-vista-resumen">
                                    <p class="cd-ayuda"><i class="fas fa-hand-pointer"></i> Haga clic en un docente para ver sus módulos.</p>
                                    <div class="table-responsive">
                                        <table class="table tabla-asignacion" id="tabla-carga-resumen" data-exportar="Carga docente - Resumen" data-exportar-archivo="carga-docente-resumen" data-exportar-botones="#cd-botones-resumen">
                                            <thead>
                                                <tr>
                                                    <th class="cd-col-movil" style="width: 40px;">N°</th>
                                                    <th>Docente</th>
                                                    <th class="cd-col-oculta">C.I.</th>
                                                    <th class="cd-col-especialidad">Especialidad</th>
                                                    <th class="text-center" style="width: 90px;">Módulos</th>
                                                    <th class="text-center cd-col-movil" style="width: 90px;">Programas</th>
                                                    <th style="width: 130px;">Años</th>
                                                    <th class="cd-col-movil" style="width: 110px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="cd-resumen-cuerpo"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="cd-vista-panel" id="cd-vista-detalle" style="display:none;">
                                    <div class="table-responsive">
                                        <table class="table tabla-asignacion" id="tabla-carga-detalle" data-exportar="Carga docente - Detalle" data-exportar-archivo="carga-docente-detalle" data-exportar-botones="#cd-botones-detalle">
                                            <thead>
                                                <tr>
                                                    <th class="cd-col-oculta">Docente</th>
                                                    <th style="width: 60px;">Año</th>
                                                    <th class="cd-col-oculta">Grado</th>
                                                    <th>Programa</th>
                                                    <th class="ad-col-codigo" style="width: 100px;">Código</th>
                                                    <th>Módulo</th>
                                                    <th style="width: 100px;">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cargaPorDocente as $docenteID => $modsDocente): ?>
                                                <?php $d0 = $modsDocente[0]; ?>
                                                <tr class="cd-grupo no-exportar" data-docente="<?php echo (int)$docenteID; ?>">
                                                    <td colspan="7">
                                                        <span class="ad-grupo-nombre"><i class="fas fa-user"></i> <?php echo htmlspecialchars($cdDocentes[$docenteID]); ?></span>
                                                        <?php if ((int)$d0['EstadoDocente'] !== 1): ?><small class="ad-sin">(de baja)</small><?php endif; ?>
                                                        <span class="ad-grupo-meta"><b class="cd-grupo-num"><?php echo count($modsDocente); ?></b> módulo(s)<?php echo $d0['Especialidad'] ? ' · ' . htmlspecialchars($d0['Especialidad']) : ''; ?></span>
                                                    </td>
                                                </tr>
                                                <?php foreach ($modsDocente as $c): ?>
                                                <tr class="cd-fila"
                                                    data-docente="<?php echo (int)$c['DocenteID']; ?>"
                                                    data-anio="<?php echo (int)$c['Anio']; ?>"
                                                    data-grado="<?php echo htmlspecialchars($c['GradoAcademico']); ?>"
                                                    data-programa="<?php echo (int)$c['ProgramaID']; ?>"
                                                    data-estado="<?php echo htmlspecialchars($c['estadomodulo']); ?>"
                                                    data-ci="<?php echo htmlspecialchars($c['Ci']); ?>"
                                                    data-especialidad="<?php echo htmlspecialchars($c['Especialidad'] ?? ''); ?>"
                                                    data-de-baja="<?php echo (int)$c['EstadoDocente'] === 1 ? '0' : '1'; ?>">
                                                    <td class="cd-col-oculta cd-docente"><?php echo htmlspecialchars($cdDocentes[$c['DocenteID']]); ?></td>
                                                    <td><?php echo $c['Anio'] ? (int)$c['Anio'] : '—'; ?></td>
                                                    <td class="cd-col-oculta"><?php echo htmlspecialchars($c['GradoAcademico']); ?></td>
                                                    <td><span class="cd-grado no-exportar"><?php echo htmlspecialchars($c['GradoAcademico']); ?></span> <?php echo htmlspecialchars($c['NombrePrograma']); ?><?php echo $c['Sede'] ? ' <small class="cd-sede">Sede: ' . htmlspecialchars($c['Sede']) . '</small>' : ''; ?></td>
                                                    <td class="ad-col-codigo"><span class="ad-codigo"><?php echo htmlspecialchars($c['codigomodulo']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($c['nombremodulo']); ?></td>
                                                    <td><span class="ad-estado is-<?php echo strtolower(htmlspecialchars($c['estadomodulo'])); ?>"><?php echo htmlspecialchars(mb_convert_case($c['estadomodulo'], MB_CASE_TITLE, 'UTF-8')); ?></span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php endforeach; ?>
                                                <tr id="cd-sin-resultados" class="no-exportar" style="display:none;">
                                                    <td colspan="7" class="ad-vacio">Ningún módulo coincide con los filtros.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
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

        // ===== Carga docente =====
        // El resumen por docente se arma con las filas del detalle que quedan visibles, así
        // los números y lo que se exporta (Excel/PDF) siempre coinciden con los filtros.
        const $cdFilas = $('.cd-fila');
        const escaparHtml = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };

        // Programas por grado: al elegir un grado, el combo de programas solo muestra los suyos
        const cdProgramasCache = [];
        $('#cd-programa option[value!=""]').each(function() {
            cdProgramasCache.push({ value: $(this).val(), grado: $(this).data('grado') || '', html: $(this).html() });
        });
        $('#cd-grado').on('change', function() {
            const grado = $(this).val();
            const $programa = $('#cd-programa');
            const elegido = $programa.val();
            let opciones = '<option value="">Todos los programas</option>';
            cdProgramasCache.forEach(function(p) {
                if (!grado || p.grado === grado) {
                    opciones += '<option value="' + p.value + '" data-grado="' + escaparHtml(p.grado) + '">' + p.html + '</option>';
                }
            });
            $programa.html(opciones);
            if (elegido && $programa.find('option[value="' + elegido + '"]').length) {
                $programa.val(elegido);
            }
            $programa.trigger('change.select2');
            filtrarCarga();
        });
        $('#cd-docente, #cd-anio, #cd-programa, #cd-estado').on('change', filtrarCarga);
        $('#cd-limpiar').on('click', function() {
            $('#cd-docente, #cd-anio, #cd-estado').val('').trigger('change.select2');
            $('#cd-grado').val('').trigger('change');
        });

        // Pestañas de la pantalla (se recuerda la elegida en la dirección: #carga)
        function mostrarPanel(panel) {
            $('.ad-tab').each(function() {
                const activa = $(this).data('panel') === panel;
                $(this).toggleClass('is-activa', activa).attr('aria-selected', activa ? 'true' : 'false');
            });
            $('#panel-asignar').toggle(panel === 'asignar');
            $('#panel-carga').toggle(panel === 'carga');
            $('#ad-subtitulo').text(panel === 'carga' ? 'CARGA DOCENTE' : 'MÓDULOS VIGENTES');
            if (history.replaceState) history.replaceState(null, '', panel === 'carga' ? '#carga' : location.pathname + location.search);
        }
        $('.ad-tab').on('click', function() { mostrarPanel($(this).data('panel')); });
        if (location.hash === '#carga') mostrarPanel('carga');

        // Vista de la carga: resumen por docente o lista de módulos (una tabla a la vez)
        function mostrarVista(vista) {
            $('.cd-vista').each(function() { $(this).toggleClass('is-activa', $(this).data('vista') === vista); });
            $('#cd-vista-resumen, #cd-botones-resumen').toggle(vista === 'resumen');
            $('#cd-vista-detalle, #cd-botones-detalle').toggle(vista === 'detalle');
        }
        $('.cd-vista').on('click', function() { mostrarVista($(this).data('vista')); });

        // Clic en un docente del resumen: sus módulos en la lista
        $('#cd-resumen-cuerpo').on('click', 'tr.cd-docente-fila', function() {
            $('#cd-docente').val(String($(this).data('docente'))).trigger('change.select2');
            filtrarCarga();
            mostrarVista('detalle');
        });

        function filtrarCarga() {
            const filtro = {
                docente: $('#cd-docente').val(),
                anio: $('#cd-anio').val(),
                grado: $('#cd-grado').val(),
                programa: $('#cd-programa').val(),
                estado: $('#cd-estado').val()
            };

            const porDocente = {};
            const programas = {};
            let modulos = 0;

            $cdFilas.each(function() {
                const $f = $(this);
                const mostrar =
                    (!filtro.docente || String($f.data('docente')) === filtro.docente) &&
                    (!filtro.anio || String($f.data('anio')) === filtro.anio) &&
                    (!filtro.grado || $f.data('grado') === filtro.grado) &&
                    (!filtro.programa || String($f.data('programa')) === filtro.programa) &&
                    (!filtro.estado || $f.data('estado') === filtro.estado);
                $f.toggle(mostrar);
                if (!mostrar) return;

                modulos++;
                programas[$f.data('programa')] = true;
                const id = $f.data('docente');
                if (!porDocente[id]) {
                    porDocente[id] = {
                        id: id,
                        nombre: $f.find('.cd-docente').text().trim(),
                        deBaja: String($f.data('de-baja')) === '1',
                        ci: $f.data('ci'),
                        especialidad: $f.data('especialidad'),
                        modulos: 0,
                        programas: {},
                        anios: {}
                    };
                }
                porDocente[id].modulos++;
                porDocente[id].programas[$f.data('programa')] = true;
                if ($f.data('anio')) porDocente[id].anios[$f.data('anio')] = true;
            });

            // Resumen: el que más módulos tiene, primero
            const docentes = Object.keys(porDocente).map(function(id) { return porDocente[id]; })
                .sort(function(a, b) { return b.modulos - a.modulos || a.nombre.localeCompare(b.nombre); });
            let html = '';
            docentes.forEach(function(d, i) {
                const anios = Object.keys(d.anios).sort().reverse().join(', ');
                html += '<tr class="cd-docente-fila" data-docente="' + escaparHtml(d.id) + '" title="Ver los módulos de este docente">' +
                    '<td class="cd-col-movil">' + (i + 1) + '</td>' +
                    '<td><b>' + escaparHtml(d.nombre) + '</b>' + (d.deBaja ? ' <small class="ad-sin">(de baja)</small>' : '') + '</td>' +
                    '<td class="cd-col-oculta">' + escaparHtml(d.ci) + '</td>' +
                    '<td class="cd-col-especialidad">' + escaparHtml(d.especialidad || '—') + '</td>' +
                    '<td class="text-center"><span class="cd-cantidad">' + d.modulos + '</span></td>' +
                    '<td class="text-center cd-col-movil">' + Object.keys(d.programas).length + '</td>' +
                    '<td>' + escaparHtml(anios || '—') + '</td>' +
                    '<td class="text-right cd-col-movil"><button type="button" class="cd-ver">Ver módulos <i class="fas fa-chevron-right"></i></button></td>' +
                '</tr>';
            });
            $('#cd-resumen-cuerpo').html(html || '<tr><td colspan="8" class="ad-vacio">Ningún docente coincide con los filtros.</td></tr>');
            $('#cd-sin-resultados').toggle(modulos === 0);

            // Encabezado de cada docente en la lista: solo si le quedan módulos, con su cantidad
            $('#tabla-carga-detalle tr.cd-grupo').each(function() {
                const d = porDocente[$(this).data('docente')];
                $(this).toggle(!!d);
                if (d) $(this).find('.cd-grupo-num').text(d.modulos);
            });

            $('#cd-total-docentes').text(docentes.length);
            $('#cd-total-modulos').text(modulos);
            $('#cd-total-programas').text(Object.keys(programas).length);

            // Los filtros elegidos van en el texto de la pantalla y en el título del Excel/PDF
            const partes = [];
            if (filtro.docente) partes.push($('#cd-docente option:selected').text().trim());
            if (filtro.anio) partes.push('Año ' + filtro.anio);
            if (filtro.grado) partes.push(filtro.grado);
            if (filtro.programa) partes.push($('#cd-programa option:selected').text().trim());
            if (filtro.estado) partes.push('Módulos ' + $('#cd-estado option:selected').text().trim().toLowerCase());
            const sufijo = partes.length ? ' - ' + partes.join(' · ') : '';
            $('#cd-detalle-filtro').html(partes.length ? 'Filtrado por: <b>' + escaparHtml(partes.join(' · ')) + '</b>' : '');
            $('#cd-limpiar').toggle(partes.length > 0);
            $('#tabla-carga-resumen').attr('data-exportar', 'Carga docente - Resumen' + sufijo);
            $('#tabla-carga-detalle').attr('data-exportar', 'Carga docente - Detalle' + sufijo);
        }

        if ($cdFilas.length) filtrarCarga();
    });
    </script>
</body>
