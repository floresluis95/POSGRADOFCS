<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");

    $tipoSesion = $_SESSION['Tipo'] ?? '';
    $esAdminOSecPanel = in_array($tipoSesion, ['ADM', 'SEC'], true);

    // Noticias e Informaciones y Cronograma de Defensa: contenido editable
    // por Administración/Secretaría, visible para todos en el Panel.
    $noticiasPanel = NoticiaPanelModelo::ListarActivasModelo();
    $defensasPanel = CronogramaDefensaModelo::ListarActivasModelo();
    $slidesCarrusel = CarruselPanelModelo::ListarActivosModelo();
    $programasParaDefensa = ProgramasModelos::ListaProgramaModelo();

    // Imagenes de respaldo (rotan) para noticias sin imagen propia asignada
    $imagenesRespaldoNoticia = [
        'vistas/recursos/assets/media/co/c1.png',
        'vistas/recursos/assets/media/co/c2.png',
        'vistas/recursos/assets/media/co/c3.png',
    ];
    // Clase de color de cada etiqueta de noticia
    $clasesEtiqueta = [
        'NUEVO' => 'is-nuevo',
        'IMPORTANTE' => 'is-importante',
        'EVENTO' => 'is-evento',
    ];
    $mesesCortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    // Tablero según el tipo de usuario: saludo, indicadores, listas y accesos rápidos
    $tablero = TableroControlador::DatosTableroControlador();
?>
<link href="vistas/recursos/assets/css/panel-tablero.css?v=<?php echo filemtime('vistas/recursos/assets/css/panel-tablero.css'); ?>" rel="stylesheet" type="text/css" />

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
    <div class="kt-header-mobile__logo">
      <a href="panel">
      <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
    </div>
  </div>

  <div class="kt-grid kt-grid--hor kt-grid--root">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

		<?php
			$NavBar = new FuncionesControladores();
			$NavBar -> NavBarControlador();
		?>
		<button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
		<?php
			$Sidebar = new FuncionesControladores();
			$Sidebar -> SidebarControlador();
		?>

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- CONTENIDO PRINCIPAL: tablero según el tipo de usuario (datos en TableroControlador) -->
            <div class="kt-container pn">

              <!-- Bienvenida -->
              <section class="pn-bienvenida">
                <div>
                  <span class="pn-fecha"><i class="far fa-calendar"></i> <?php echo htmlspecialchars($tablero['fecha']); ?></span>
                  <h1><?php echo htmlspecialchars($tablero['saludo']); ?></h1>
                  <p>
                    <?php if ($esAdminOSecPanel): ?>
                      Este es el resumen de la gestión del posgrado.
                    <?php elseif ($tipoSesion === 'EST'): ?>
                      Aquí tienes el estado de tus programas y tus próximas actividades.
                    <?php elseif ($tipoSesion === 'DOC'): ?>
                      Aquí tienes tus módulos asignados y tus próximas actividades.
                    <?php else: ?>
                      Bienvenido al sistema de registro y control del posgrado.
                    <?php endif; ?>
                  </p>
                </div>
                <span class="pn-rol"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($tablero['rol']); ?></span>
              </section>

              <?php if ($esAdminOSecPanel): $r = $tablero['resumen']; ?>
              <!-- ===== ADMINISTRACIÓN / SECRETARÍA ===== -->
              <div class="pn-kpis">
                <a href="matriculados" class="pn-kpi">
                  <span class="pn-kpi-icono is-azul"><i class="fas fa-user-graduate"></i></span>
                  <div>
                    <div class="pn-kpi-valor"><?php echo (int)$r['estudiantesActivos']; ?></div>
                    <div class="pn-kpi-label">Estudiantes activos</div>
                    <div class="pn-kpi-sub"><?php echo (int)$r['programasActivos']; ?> programas vigentes</div>
                  </div>
                </a>
                <a href="reportemodulos" class="pn-kpi">
                  <span class="pn-kpi-icono is-verde"><i class="fas fa-hand-holding-usd"></i></span>
                  <div>
                    <div class="pn-kpi-valor">Bs. <?php echo number_format((float)$r['recaudadoMes'], 2, ',', '.'); ?></div>
                    <div class="pn-kpi-label">Recaudado este mes</div>
                    <div class="pn-kpi-sub">Matrículas y cuotas<?php echo (float)$r['clinicasMes'] > 0 ? ' · Clínicas Bs. ' . number_format((float)$r['clinicasMes'], 2, ',', '.') : ''; ?></div>
                  </div>
                </a>
                <a href="preregistro" class="pn-kpi">
                  <span class="pn-kpi-icono is-ambar"><i class="fas fa-user-clock"></i></span>
                  <div>
                    <div class="pn-kpi-valor"><?php echo (int)$r['preregistrosPendientes']; ?></div>
                    <div class="pn-kpi-label">Pre-registros pendientes</div>
                    <div class="pn-kpi-sub"><?php echo (int)$r['solicitudesPendientes']; ?> solicitud(es) de inscripción</div>
                  </div>
                </a>
                <a href="matriculados" class="pn-kpi">
                  <span class="pn-kpi-icono <?php echo (int)$r['ordenesVencidas'] > 0 ? 'is-rojo' : 'is-azul'; ?>"><i class="fas fa-file-invoice-dollar"></i></span>
                  <div>
                    <div class="pn-kpi-valor"><?php echo (int)$r['ordenesPendientes']; ?></div>
                    <div class="pn-kpi-label">Órdenes por cobrar</div>
                    <div class="pn-kpi-sub <?php echo (int)$r['ordenesVencidas'] > 0 ? 'is-rojo' : ''; ?>">
                      <?php echo (int)$r['ordenesVencidas'] > 0 ? (int)$r['ordenesVencidas'] . ' vencida(s)' : 'Ninguna vencida'; ?>
                      · Bs. <?php echo number_format((float)$r['montoOrdenesPendientes'], 2, ',', '.'); ?>
                    </div>
                  </div>
                </a>
              </div>

              <div class="pn-grid">
                <div class="pn-col">
                  <!-- Cuotas vencidas (antes era una ventana emergente al entrar) -->
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-exclamation-circle"></i> Cuotas vencidas</h3>
                      <a href="matriculados" class="pn-link">Ir a Matriculados</a>
                    </div>
                    <div id="pnVencidas" class="pn-lista">
                      <div class="pn-vacio"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    </div>
                  </div>

                  <!-- Últimos pagos -->
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-receipt"></i> Últimos pagos registrados</h3>
                      <a href="historialpagos" class="pn-link">Ver historial</a>
                    </div>
                    <?php if (empty($tablero['ultimosPagos'])): ?>
                      <div class="pn-vacio"><i class="far fa-folder-open"></i>Aún no hay pagos registrados.</div>
                    <?php else: ?>
                    <ul class="pn-lista">
                      <?php foreach ($tablero['ultimosPagos'] as $pago): ?>
                      <li class="pn-item">
                        <span class="pn-item-icono <?php echo $pago['Concepto'] === 'CLINICA' ? 'is-clinica' : 'is-verde'; ?>">
                          <i class="fas <?php echo $pago['Concepto'] === 'CLINICA' ? 'fa-stethoscope' : 'fa-check'; ?>"></i>
                        </span>
                        <a href="matriculados?buscar=<?php echo urlencode($pago['Ci']); ?>" class="pn-item-cuerpo pn-item-enlace" title="Ver en Matriculados">
                          <strong><?php echo htmlspecialchars($pago['Estudiante']); ?></strong>
                          <small><?php echo $pago['Concepto'] === 'CLINICA' ? 'Clínica' : 'Cuota'; ?> · <?php echo htmlspecialchars($pago['NombrePrograma']); ?></small>
                        </a>
                        <div class="pn-item-fin">
                          <strong>Bs. <?php echo number_format((float)$pago['Monto'], 2, ',', '.'); ?></strong>
                          <small><?php echo $pago['Fecha'] ? date('d/m/Y', strtotime($pago['Fecha'])) : ''; ?></small>
                        </div>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="pn-col">
                  <?php include __DIR__ . '/modulos/panel-accesos-eventos.php'; ?>
                </div>
              </div>

              <?php elseif ($tipoSesion === 'EST'): ?>
              <!-- ===== ESTUDIANTE ===== -->
              <?php if (!empty($tablero['avisosMora'])): ?>
              <div class="pn-alerta">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                  <strong>Tienes pagos vencidos</strong>
                  <?php foreach ($tablero['avisosMora'] as $aviso): ?>
                    <div><?php echo htmlspecialchars($aviso['NombrePrograma']); ?>: venció el <?php echo date('d/m/Y', strtotime($aviso['FechaPlazo'])); ?> (hace <?php echo (int)$aviso['DiasVencido']; ?> día(s)).</div>
                  <?php endforeach; ?>
                </div>
                <a href="misordenespago" class="pn-btn">Ver mis órdenes</a>
              </div>
              <?php endif; ?>

              <div class="pn-grid">
                <div class="pn-col">
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-graduation-cap"></i> Mis programas</h3>
                      <a href="misordenespago" class="pn-link">Mis órdenes de pago</a>
                    </div>
                    <?php if (empty($tablero['programas'])): ?>
                      <div class="pn-vacio"><i class="far fa-folder-open"></i>No tienes programas activos.</div>
                    <?php endif; ?>
                    <?php foreach ($tablero['programas'] as $prog): $res = $prog['resumen']; ?>
                    <?php
                        $pct = $res['costoTotal'] > 0 ? min(100, round($res['totalPagado'] / $res['costoTotal'] * 100)) : 0;
                        $ordenesPend = (int)($prog['ordenes']['cantidad'] ?? 0);
                    ?>
                    <div class="pn-programa">
                      <div class="pn-programa-cabecera">
                        <div>
                          <span class="pn-chip"><?php echo htmlspecialchars($prog['GradoAcademico']); ?></span>
                          <h4><?php echo htmlspecialchars($prog['NombrePrograma']); ?></h4>
                          <small><?php echo htmlspecialchars($prog['Codigo']); ?> · <?php echo htmlspecialchars($prog['Sede']); ?></small>
                        </div>
                        <?php if (!$res['tienePlan']): ?>
                          <span class="pn-chip is-ambar">Plan en revisión</span>
                        <?php elseif ($res['completado']): ?>
                          <span class="pn-chip is-verde"><i class="fas fa-check-circle"></i> Pagado</span>
                        <?php endif; ?>
                      </div>
                      <?php if ($res['tienePlan']): ?>
                      <div class="pn-programa-cifras">
                        <div><small>Costo</small><strong>Bs. <?php echo number_format($res['costoTotal'], 2, ',', '.'); ?></strong></div>
                        <div><small>Pagado</small><strong class="is-verde">Bs. <?php echo number_format($res['totalPagado'], 2, ',', '.'); ?></strong></div>
                        <div><small>Saldo</small><strong class="<?php echo $res['saldoPendiente'] > 0 ? 'is-rojo' : ''; ?>">Bs. <?php echo number_format($res['saldoPendiente'], 2, ',', '.'); ?></strong></div>
                      </div>
                      <div class="pn-progreso"><span style="width: <?php echo $pct; ?>%"></span></div>
                      <div class="pn-programa-pie">
                        <span><?php echo $pct; ?>% pagado · <?php echo (int)$res['cuotasPagadasCount']; ?> cuota(s) pagada(s)</span>
                        <?php if ($ordenesPend > 0): ?>
                          <a href="misordenespago" class="pn-chip is-ambar"><i class="fas fa-file-invoice"></i> <?php echo $ordenesPend; ?> orden(es) por pagar · Bs. <?php echo number_format((float)$prog['ordenes']['monto'], 2, ',', '.'); ?></a>
                        <?php endif; ?>
                      </div>
                      <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <div class="pn-col">
                  <?php include __DIR__ . '/modulos/panel-accesos-eventos.php'; ?>
                </div>
              </div>

              <?php elseif ($tipoSesion === 'DOC'): ?>
              <!-- ===== DOCENTE ===== -->
              <?php include __DIR__ . '/modulos/aviso-calificaciones-docente.php'; ?>
              <div class="pn-grid">
                <div class="pn-col">
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-chalkboard-teacher"></i> Mis módulos</h3>
                      <a href="reportecalificaciones" class="pn-link">Registrar calificaciones</a>
                    </div>
                    <?php if (empty($tablero['modulos'])): ?>
                      <div class="pn-vacio"><i class="far fa-folder-open"></i>No tienes módulos asignados.</div>
                    <?php else: ?>
                    <ul class="pn-lista">
                      <?php foreach ($tablero['modulos'] as $mod): ?>
                      <?php
                          $estadoMod = strtoupper($mod['estadomodulo'] ?? '');
                          $claseEstado = $estadoMod === 'ACTIVO' ? 'is-verde' : (in_array($estadoMod, ['VALIDADO', 'CERRADO'], true) ? '' : 'is-ambar');
                      ?>
                      <li class="pn-item">
                        <span class="pn-item-icono is-azul"><i class="fas fa-book-open"></i></span>
                        <a href="reportecalificaciones" class="pn-item-cuerpo pn-item-enlace" title="Registrar calificaciones">
                          <strong><?php echo htmlspecialchars($mod['codigomodulo']); ?> · <?php echo htmlspecialchars($mod['nombremodulo']); ?></strong>
                          <small><?php echo htmlspecialchars($mod['NombrePrograma']); ?>
                            <?php if (!empty($mod['FechaInicio'])): ?> · <?php echo date('d/m/Y', strtotime($mod['FechaInicio'])); ?><?php echo !empty($mod['FechaFinal']) ? ' al ' . date('d/m/Y', strtotime($mod['FechaFinal'])) : ''; ?><?php endif; ?>
                          </small>
                        </a>
                        <span class="pn-chip <?php echo $claseEstado; ?>"><?php echo htmlspecialchars(mb_convert_case($estadoMod, MB_CASE_TITLE, 'UTF-8')); ?></span>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="pn-col">
                  <?php include __DIR__ . '/modulos/panel-accesos-eventos.php'; ?>
                </div>
              </div>

              <?php elseif ($tipoSesion === 'AUX'): ?>
              <!-- ===== AUXILIAR: accesos y eventos (sin indicadores de cobranza, que son de ADM/SEC) ===== -->
              <div class="pn-grid pn-grid-aux">
                <?php include __DIR__ . '/modulos/panel-accesos-eventos.php'; ?>
              </div>
              <?php endif; ?>

              <!-- ===== CONTENIDO INFORMATIVO (todos) ===== -->
              <div class="pn-grid pn-grid-info">
                <!-- Noticias -->
                <div class="pn-col">
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="far fa-newspaper"></i> Noticias e informaciones</h3>
                      <?php if ($esAdminOSecPanel): ?>
                        <button type="button" class="pn-btn pn-btn-sm" id="btnNuevaNoticia"><i class="fa fa-plus"></i> Nueva noticia</button>
                      <?php endif; ?>
                    </div>
                    <?php if (empty($noticiasPanel)): ?>
                      <div class="pn-vacio"><i class="far fa-newspaper"></i>No hay noticias publicadas por el momento.</div>
                    <?php else: ?>
                    <div class="pn-noticias">
                      <?php foreach ($noticiasPanel as $i => $n): ?>
                      <?php
                          $imagenNoticia = !empty($n['ImagenURL']) ? $n['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
                          $claseEtiqueta = $clasesEtiqueta[$n['Etiqueta']] ?? '';
                          $fechaNoticia = !empty($n['FechaPublicacion']) ? date('d/m/Y', strtotime($n['FechaPublicacion'])) : '';
                      ?>
                      <article class="pn-noticia" data-id-noticia="<?php echo (int)$n['IdNoticia']; ?>">
                        <div class="pn-noticia-imagen">
                          <img src="<?php echo htmlspecialchars($imagenNoticia); ?>" alt="<?php echo htmlspecialchars($n['Titulo']); ?>" loading="lazy" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
                          <?php if (!empty($n['Etiqueta'])): ?><span class="pn-etiqueta <?php echo $claseEtiqueta; ?>"><?php echo htmlspecialchars($n['Etiqueta']); ?></span><?php endif; ?>
                          <?php if ($esAdminOSecPanel): ?>
                          <div class="pn-acciones-admin">
                            <button type="button" class="pn-btn-icono btn-editar-noticia" title="Editar"
                              data-id="<?php echo (int)$n['IdNoticia']; ?>"
                              data-titulo="<?php echo htmlspecialchars($n['Titulo'], ENT_QUOTES); ?>"
                              data-descripcion="<?php echo htmlspecialchars($n['Descripcion'], ENT_QUOTES); ?>"
                              data-autor="<?php echo htmlspecialchars($n['Autor'], ENT_QUOTES); ?>"
                              data-etiqueta="<?php echo htmlspecialchars($n['Etiqueta'], ENT_QUOTES); ?>"
                              data-enlace="<?php echo htmlspecialchars($n['Enlace'] ?? '', ENT_QUOTES); ?>"
                              data-orden="<?php echo (int)$n['Orden']; ?>"
                              data-fecha="<?php echo htmlspecialchars($n['FechaPublicacion'] ?? '', ENT_QUOTES); ?>">
                              <i class="la la-edit"></i>
                            </button>
                            <button type="button" class="pn-btn-icono is-peligro btn-eliminar-noticia" title="Eliminar" data-id="<?php echo (int)$n['IdNoticia']; ?>">
                              <i class="la la-trash"></i>
                            </button>
                          </div>
                          <?php endif; ?>
                        </div>
                        <div class="pn-noticia-cuerpo">
                          <small class="pn-noticia-meta"><?php echo htmlspecialchars($fechaNoticia); ?><?php echo !empty($n['Autor']) ? ' · ' . htmlspecialchars($n['Autor']) : ''; ?></small>
                          <h4><?php echo htmlspecialchars($n['Titulo']); ?></h4>
                          <p><?php echo nl2br(htmlspecialchars($n['Descripcion'])); ?></p>
                          <?php if (!empty($n['Enlace'])): ?>
                            <a href="<?php echo htmlspecialchars($n['Enlace']); ?>" class="pn-link" target="_blank" rel="noopener">Leer más <i class="fas fa-arrow-right"></i></a>
                          <?php endif; ?>
                        </div>
                      </article>
                      <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="pn-col">
                  <!-- Programas destacados (carrusel compacto) -->
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-star"></i> Programas destacados</h3>
                      <?php if ($esAdminOSecPanel): ?>
                        <button type="button" class="pn-btn pn-btn-sm pn-btn-claro" id="btnGestionarCarrusel"><i class="fa fa-images"></i> Gestionar</button>
                      <?php endif; ?>
                    </div>
                    <div class="pn-slider" id="pnSlider">
                      <?php if (empty($slidesCarrusel)): ?>
                        <div class="pn-vacio"><i class="far fa-images"></i>No hay programas destacados.</div>
                      <?php else: ?>
                        <?php foreach ($slidesCarrusel as $i => $s): ?>
                        <?php
                            $imagenSlide = !empty($s['ImagenURL']) ? $s['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
                            $tituloSlide = !empty($s['TituloPersonalizado']) ? $s['TituloPersonalizado'] : $s['NombrePrograma'];
                            $fechaInicioSlide = !empty($s['FechaInicio']) ? date('d/m/Y', strtotime($s['FechaInicio'])) : 'A definir';
                        ?>
                        <div class="pn-slide<?php echo $i === 0 ? ' is-activa' : ''; ?>">
                          <img src="<?php echo htmlspecialchars($imagenSlide); ?>" alt="<?php echo htmlspecialchars($tituloSlide); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
                          <div class="pn-slide-info">
                            <span class="pn-chip is-claro"><?php echo htmlspecialchars($s['GradoAcademico']); ?></span>
                            <h4><?php echo htmlspecialchars($tituloSlide); ?></h4>
                            <button type="button" class="pn-btn pn-btn-sm pn-btn-claro btn-info-programa"
                              data-programa-id="<?php echo (int) $s['ProgramaID']; ?>"
                              data-programa="<?php echo htmlspecialchars($s['NombrePrograma'], ENT_QUOTES); ?>"
                              data-grado="<?php echo htmlspecialchars($s['GradoAcademico'], ENT_QUOTES); ?>"
                              data-codigo="<?php echo htmlspecialchars($s['Codigo'], ENT_QUOTES); ?>"
                              data-costo="<?php echo htmlspecialchars($s['Costo'], ENT_QUOTES); ?>"
                              data-matricula="<?php echo htmlspecialchars($s['CostoMatricula'], ENT_QUOTES); ?>"
                              data-modulos="<?php echo htmlspecialchars($s['Modulos'], ENT_QUOTES); ?>"
                              data-duracion="<?php echo htmlspecialchars($s['DuracionMeses'] ?? '', ENT_QUOTES); ?>"
                              data-fecha="<?php echo htmlspecialchars($fechaInicioSlide, ENT_QUOTES); ?>"
                              data-sede="<?php echo htmlspecialchars($s['Sede'], ENT_QUOTES); ?>"
                              data-detalle="<?php echo htmlspecialchars($s['Detalle'] ?? '', ENT_QUOTES); ?>">
                              Más información
                            </button>
                          </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($slidesCarrusel) > 1): ?>
                          <div class="pn-slider-puntos">
                            <?php foreach ($slidesCarrusel as $i => $s): ?>
                              <button type="button" class="pn-slider-punto<?php echo $i === 0 ? ' is-activa' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Programa <?php echo $i + 1; ?>"></button>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Cronograma de defensas -->
                  <div class="pn-card">
                    <div class="pn-card-head">
                      <h3><i class="fas fa-user-graduate"></i> Cronograma de defensas</h3>
                      <?php if ($esAdminOSecPanel): ?>
                        <button type="button" class="pn-btn pn-btn-sm" id="btnNuevaDefensa"><i class="fa fa-plus"></i> Nueva</button>
                      <?php endif; ?>
                    </div>
                    <?php if (empty($defensasPanel)): ?>
                      <div class="pn-vacio"><i class="far fa-calendar-alt"></i>No hay defensas programadas.</div>
                    <?php else: ?>
                    <ul class="pn-lista">
                      <?php foreach ($defensasPanel as $d): ?>
                      <?php
                          $tsDefensa = strtotime($d['FechaDefensa']);
                          $horaDefensa = !empty($d['HoraDefensa']) ? date('H:i', strtotime($d['HoraDefensa'])) : '';
                      ?>
                      <li class="pn-item">
                        <span class="pn-fecha-caja"><strong><?php echo date('d', $tsDefensa); ?></strong><small><?php echo $mesesCortos[(int)date('n', $tsDefensa) - 1]; ?></small></span>
                        <div class="pn-item-cuerpo">
                          <strong><?php echo htmlspecialchars($d['NombreEstudiante']); ?></strong>
                          <?php if (!empty($d['TituloDefensa'])): ?>
                            <small class="d-block" style="font-style:italic;"><?php echo htmlspecialchars($d['TituloDefensa']); ?></small>
                          <?php endif; ?>
                          <small>
                            <?php echo $horaDefensa ? $horaDefensa . ' · ' : ''; ?><?php echo htmlspecialchars($d['Lugar']); ?>
                            <?php echo !empty($d['NombrePrograma']) ? ' · ' . htmlspecialchars($d['NombrePrograma']) : ''; ?>
                          </small>
                        </div>
                        <?php if ($esAdminOSecPanel): ?>
                        <div class="pn-acciones-fila">
                          <button type="button" class="pn-btn-icono btn-editar-defensa" title="Editar"
                            data-id="<?php echo (int)$d['IdDefensa']; ?>"
                            data-nombre="<?php echo htmlspecialchars($d['NombreEstudiante'], ENT_QUOTES); ?>"
                            data-programa="<?php echo (int)($d['ProgramaID'] ?? 0); ?>"
                            data-fecha="<?php echo htmlspecialchars($d['FechaDefensa'], ENT_QUOTES); ?>"
                            data-hora="<?php echo htmlspecialchars($d['HoraDefensa'] ?? '', ENT_QUOTES); ?>"
                            data-lugar="<?php echo htmlspecialchars($d['Lugar'], ENT_QUOTES); ?>"
                            data-estudiante="<?php echo (int)($d['EstudianteID'] ?? 0); ?>"
                            data-detalle="<?php echo htmlspecialchars($d['TituloDefensa'] ?? '', ENT_QUOTES); ?>">
                            <i class="la la-edit"></i>
                          </button>
                          <button type="button" class="pn-btn-icono is-peligro btn-eliminar-defensa" title="Eliminar" data-id="<?php echo (int)$d['IdDefensa']; ?>">
                            <i class="la la-trash"></i>
                          </button>
                        </div>
                        <?php endif; ?>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

            </div>
            <!-- FIN CONTENIDO PRINCIPAL -->

          </div>
        </div>

        <?php
			$Footer = new FuncionesControladores();
			$Footer -> FooterControlador();
		?>

      </div>
    </div>
  </div>

  <?php if ($esAdminOSecPanel): ?>
  <!-- Modal: Nueva / Editar Noticia -->
  <div class="modal fade" id="modalNoticia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white" id="modalNoticiaTitulo"><i class="fa fa-newspaper"></i> Nueva Noticia</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <form id="formNoticia">
          <input type="hidden" id="noticiaIdNoticia" value="">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Título <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="noticiaTitulo" maxlength="200" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Descripción <span class="text-danger">*</span></label>
              <textarea class="form-control" id="noticiaDescripcion" rows="4" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Autor</label>
                  <input type="text" class="form-control" id="noticiaAutor" maxlength="100" placeholder="Administración">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Etiqueta</label>
                  <select class="form-control" id="noticiaEtiqueta">
                    <option value="NUEVO">NUEVO</option>
                    <option value="IMPORTANTE">IMPORTANTE</option>
                    <option value="EVENTO">EVENTO</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Fecha de publicación</label>
                  <input type="date" class="form-control" id="noticiaFecha">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Orden</label>
                  <input type="number" class="form-control" id="noticiaOrden" value="0" min="0">
                  <small class="form-text text-muted">Menor número aparece primero.</small>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Enlace "Leer más" (opcional)</label>
              <input type="text" class="form-control" id="noticiaEnlace" placeholder="https://...">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger mr-auto" id="btnEliminarNoticia" style="display:none;">
              <i class="fa fa-trash"></i> Eliminar
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Nueva / Editar Defensa -->
  <div class="modal fade" id="modalDefensa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white" id="modalDefensaTitulo"><i class="fa fa-user-graduate"></i> Nueva Defensa</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <form id="formDefensa">
          <input type="hidden" id="defensaIdDefensa" value="">
          <input type="hidden" id="defensaEstudianteID" value="">
          <div class="modal-body">
            <!-- Buscar al estudiante por C.I.: llena nombre, programa y el título guardado
                 de su trabajo de grado (el mismo del Certificado de Aprobación Programa de Posgrado). -->
            <div class="form-group">
              <label class="font-weight-bold">Buscar estudiante por C.I.</label>
              <div class="input-group">
                <input type="text" class="form-control" id="defensaBuscarCi" placeholder="Número de carnet">
                <div class="input-group-append">
                  <button type="button" class="btn btn-primary" id="btnDefensaBuscarCi"><i class="fa fa-search"></i></button>
                </div>
              </div>
              <select class="form-control mt-2" id="defensaInscripcion" style="display:none;"></select>
              <small class="text-muted" id="defensaEstudianteAviso">Opcional: si lo busca, el título del trabajo se completa solo.</small>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Nombre del estudiante <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="defensaNombre" maxlength="150" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Programa</label>
              <select class="form-control" id="defensaPrograma">
                <option value="">Sin especificar</option>
                <?php foreach ($programasParaDefensa as $p): ?>
                  <option value="<?php echo (int)$p['ProgramaID']; ?>">
                    <?php echo htmlspecialchars($p['NombrePrograma'] . ' - ' . $p['GradoAcademico']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Fecha de defensa <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="defensaFecha" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="font-weight-bold">Hora</label>
                  <input type="time" class="form-control" id="defensaHora">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Lugar <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="defensaLugar" maxlength="150" placeholder="Ej: Auditorio FCS-UTO">
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Título del trabajo</label>
              <textarea class="form-control" id="defensaDetalle" maxlength="255" rows="2" placeholder="Se completa al buscar al estudiante"></textarea>
              <small class="text-muted">Si el estudiante fue buscado por C.I., este título se guarda también en su trabajo de grado.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger mr-auto" id="btnEliminarDefensa" style="display:none;">
              <i class="fa fa-trash"></i> Eliminar
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Listado de diapositivas del Carrusel -->
  <div class="modal fade" id="modalListaCarrusel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white"><i class="fa fa-images"></i> Diapositivas del Carrusel</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3" style="font-size: 13px;">
            <i class="fa fa-info-circle"></i>
            Cada diapositiva muestra un programa real: la información de "Más Información" (costo, módulos, fecha de
            inicio) se toma siempre del módulo Programas. Aquí solo eliges la imagen, el texto de la tarjeta y el orden.
          </p>
          <div class="text-right mb-2">
            <button type="button" class="btn btn-sm btn-danger" id="btnNuevaDiapositiva">
              <i class="fa fa-plus"></i> Nueva Diapositiva
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-sm" id="tablaCarrusel">
              <thead>
                <tr>
                  <th>Imagen</th>
                  <th>Programa</th>
                  <th>Título mostrado</th>
                  <th class="text-center">Orden</th>
                  <th class="text-right">Acciones</th>
                </tr>
              </thead>
              <tbody id="tablaCarruselBody">
                <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Nueva / Editar Diapositiva del Carrusel -->
  <div class="modal fade" id="modalCarruselSlide" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white" id="modalCarruselSlideTitulo"><i class="fa fa-image"></i> Nueva Diapositiva</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <form id="formCarruselSlide" enctype="multipart/form-data">
          <input type="hidden" id="slideIdSlide" value="">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Programa <span class="text-danger">*</span></label>
              <select class="form-control" id="slidePrograma" required>
                <option value="">Seleccione un programa...</option>
                <?php foreach ($programasParaDefensa as $p): ?>
                  <option value="<?php echo (int)$p['ProgramaID']; ?>">
                    <?php echo htmlspecialchars($p['NombrePrograma'] . ' - ' . $p['GradoAcademico']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Del programa elegido se toma el costo, los módulos y la fecha de inicio que se ven en "Más Información".</small>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Título mostrado (opcional)</label>
              <input type="text" class="form-control" id="slideTitulo" maxlength="150" placeholder="Por defecto usa el nombre del programa">
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Descripción mostrada (opcional)</label>
              <textarea class="form-control" id="slideDescripcion" rows="2" maxlength="255" placeholder="Frase breve promocional"></textarea>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Orden</label>
              <input type="number" class="form-control" id="slideOrden" value="0" min="0">
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Imagen</label>
              <input type="file" class="form-control-file" id="slideImagen" accept=".jpg,.jpeg,.png,.webp,.gif">
              <small class="form-text text-muted">Formatos: JPG, PNG, WEBP o GIF (máx. 4MB). Si edita y no sube una nueva, se conserva la actual.</small>
              <div class="mt-2">
                <img id="slideImagenPreview" src="" alt="" style="max-width: 100%; max-height: 160px; display: none; border-radius: 8px;">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger mr-auto" id="btnEliminarDiapositiva" style="display:none;">
              <i class="fa fa-trash"></i> Eliminar
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Modal: Más Información del Programa (público, solo lectura) -->
  <div class="modal fade" id="modalInfoPrograma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white"><i class="fa fa-graduation-cap"></i> <span id="infoProgramaTitulo">Programa</span></h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <span class="badge badge-info mb-3" id="infoProgramaGrado" style="font-size: 12px;"></span>
          <div class="row">
            <div class="col-6 mb-3">
              <small class="text-muted d-block">COSTO TOTAL</small>
              <strong id="infoProgramaCosto" style="font-size: 18px; color: #1e3c72;"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">MATRÍCULA</small>
              <strong id="infoProgramaMatricula" style="font-size: 18px; color: #1e3c72;"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">MÓDULOS</small>
              <strong id="infoProgramaModulos"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">DURACIÓN</small>
              <strong id="infoProgramaDuracion"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">FECHA DE INICIO</small>
              <strong id="infoProgramaFecha"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">SEDE</small>
              <strong id="infoProgramaSede"></strong>
            </div>
          </div>
          <div id="infoProgramaDetalleWrap">
            <small class="text-muted d-block">DETALLE</small>
            <p id="infoProgramaDetalle" class="mb-0"></p>
          </div>
        </div>
        <div class="modal-footer">
          <a href="autoinscripcion" id="linkInscribirmePrograma" class="btn mr-auto" style="background: var(--primary-gradient); color: #fff;">
            <i class="fa fa-file-signature"></i> Inscribirme
          </a>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</body>

<script>
/* Programas destacados: carrusel compacto con desvanecido cada 6 s (pausa al pasar el mouse). */
(function () {
    var slider = document.getElementById('pnSlider');
    if (!slider) return;
    var slides = slider.querySelectorAll('.pn-slide');
    var puntos = slider.querySelectorAll('.pn-slider-punto');
    if (slides.length < 2) return;
    var actual = 0, timer = null;
    var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function mostrar(i) {
        actual = (i + slides.length) % slides.length;
        slides.forEach(function (s, k) { s.classList.toggle('is-activa', k === actual); });
        puntos.forEach(function (p, k) { p.classList.toggle('is-activa', k === actual); });
    }
    function iniciar() { if (reducir) return; detener(); timer = setInterval(function () { mostrar(actual + 1); }, 6000); }
    function detener() { if (timer) { clearInterval(timer); timer = null; } }

    puntos.forEach(function (p) {
        p.addEventListener('click', function () { mostrar(parseInt(p.getAttribute('data-index'), 10)); iniciar(); });
    });
    slider.addEventListener('mouseenter', detener);
    slider.addEventListener('mouseleave', iniciar);
    iniciar();
})();
</script>

<?php if ($esAdminOSecPanel): ?>
<script>
// ========================================
// CUOTAS VENCIDAS (Administrador/Secretaría): se muestran en la tarjeta del
// tablero, con acceso directo a WhatsApp. Antes era una ventana emergente al entrar.
// Se difiere a window 'load' porque jQuery se imprime más abajo en el HTML final.
// ========================================
window.addEventListener('load', function() {
    var $contenedor = $('#pnVencidas');
    var escapar = function (t) { return $('<div>').text(t == null ? '' : String(t)).html(); };

    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerOrdenesVencidas' },
        dataType: 'json',
        success: function(resp) {
            var ordenes = (resp && resp.success && resp.ordenes) ? resp.ordenes : [];
            if (ordenes.length === 0) {
                $contenedor.html('<div class="pn-vacio"><i class="far fa-check-circle" style="color:#15803d"></i>No hay cuotas vencidas. ¡Todo al día!</div>');
                return;
            }

            var html = '';
            ordenes.slice(0, 6).forEach(function(o) {
                var contacto = o.whatsapp
                    ? '<a href="' + escapar(o.whatsapp) + '" target="_blank" rel="noopener" class="pn-btn pn-btn-sm" style="background:#25D366;"><i class="fab fa-whatsapp"></i> WhatsApp</a>'
                    : '<small class="text-muted">Sin celular</small>';
                html += '<li class="pn-item">' +
                    '<span class="pn-item-icono is-rojo"><i class="fas fa-clock"></i></span>' +
                    // El nombre lleva a Matriculados filtrado por el C.I. del estudiante
                    '<a href="matriculados?buscar=' + encodeURIComponent(o.ci || '') + '" class="pn-item-cuerpo pn-item-enlace" title="Ver en Matriculados">' +
                        '<strong>' + escapar(o.nombreEstudiante) + '</strong>' +
                        '<small>' + escapar(o.nombrePrograma) + ' · Bs. ' + Number(o.monto).toFixed(2) + ' · hace ' + parseInt(o.diasVencido, 10) + ' día(s)</small>' +
                    '</a>' +
                    contacto +
                '</li>';
            });
            if (ordenes.length > 6) {
                html += '<li class="pn-item"><a href="matriculados" class="pn-link">Y ' + (ordenes.length - 6) + ' más en Matriculados</a></li>';
            }
            $contenedor.html('<ul class="pn-lista">' + html + '</ul>');
        },
        error: function() {
            $contenedor.html('<div class="pn-vacio">No se pudieron cargar las cuotas vencidas.</div>');
        }
    });
});
</script>
<?php endif; ?>

<script src="vistas/recursos/assets/js/scripts/panel-info-programa.js?v=<?php echo time(); ?>"></script>

<?php if ($esAdminOSecPanel): ?>
<script src="vistas/recursos/assets/js/scripts/panel-admin.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>
