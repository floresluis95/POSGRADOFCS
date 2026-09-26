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
    $coloresBadgeEtiqueta = [
        'NUEVO' => 'var(--primary-gradient)',
        'IMPORTANTE' => 'linear-gradient(135deg, #52616e 0%, #37424a 100%)',
        'EVENTO' => 'linear-gradient(135deg, #0f6674 0%, #159895 100%)',
    ];
?>
<link href="vistas/recursos/assets/css/panel-informativo.css?v=<?php echo filemtime('vistas/recursos/assets/css/panel-informativo.css'); ?>" rel="stylesheet" type="text/css" />

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
    <div class="kt-header-mobile__logo">
      <a href="panel">
      <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
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

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>PANEL PRINCIPAL</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h4>SISTEMA DE REGISTRO Y CONTROL</h4>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="kt-container">

              <!-- CARRUSEL 3D DE PROGRAMAS -->
              <?php if ($esAdminOSecPanel): ?>
              <div class="admin-toolbar-panel" style="margin-top: 10px;">
                <button type="button" class="btn-admin-agregar" id="btnGestionarCarrusel">
                  <i class="fa fa-images"></i> Gestionar Carrusel
                </button>
              </div>
              <?php endif; ?>

              <?php if (empty($slidesCarrusel)): ?>
                <p class="text-center text-muted" style="margin: 40px 0;">No hay programas destacados en el carrusel por el momento.</p>
              <?php else: ?>
              <div class="carousel-3d-container">
                <div class="carousel-3d" id="carousel3d">
                  <?php foreach ($slidesCarrusel as $i => $s): ?>
                  <?php
                      $imagenSlide = !empty($s['ImagenURL']) ? $s['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
                      $tituloSlide = !empty($s['TituloPersonalizado']) ? $s['TituloPersonalizado'] : $s['NombrePrograma'];
                      $descSlide = !empty($s['DescripcionPersonalizada']) ? $s['DescripcionPersonalizada'] : ($s['Detalle'] ?: 'Programa de posgrado de la Facultad de Ciencias de la Salud.');
                      $fechaInicioSlide = !empty($s['FechaInicio']) ? date('d/m/Y', strtotime($s['FechaInicio'])) : 'A definir';
                  ?>
                  <div class="carousel-3d-slide<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>">
                    <img src="<?php echo htmlspecialchars($imagenSlide); ?>" alt="<?php echo htmlspecialchars($tituloSlide); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
                    <div class="slide-content">
                      <h3><?php echo htmlspecialchars($tituloSlide); ?></h3>
                      <p><?php echo htmlspecialchars($descSlide); ?></p>
                      <button class="btn-info-programa"
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
                        Más Información
                      </button>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>

                <!-- Controles -->
                <div class="carousel-3d-controls">
                  <button class="carousel-3d-btn" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <button class="carousel-3d-btn" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>

                <!-- Indicadores -->
                <div class="carousel-3d-indicators">
                  <?php foreach ($slidesCarrusel as $i => $s): ?>
                  <button class="carousel-3d-indicator<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

              <!-- SECCIÓN DE NOTICIAS E INFORMACIONES -->
              <div class="section-title animate-fade-in animate-delay-2">
                <h2>Noticias e Informaciones</h2>
                <p>Mantente informado sobre las últimas novedades del posgrado</p>
              </div>

              <?php if ($esAdminOSecPanel): ?>
              <div class="admin-toolbar-panel">
                <button type="button" class="btn-admin-agregar" id="btnNuevaNoticia">
                  <i class="fa fa-plus"></i> Nueva Noticia
                </button>
              </div>
              <?php endif; ?>

              <div class="news-grid">
                <?php if (empty($noticiasPanel)): ?>
                  <p class="text-center text-muted" style="grid-column: 1/-1;">No hay noticias publicadas por el momento.</p>
                <?php endif; ?>
                <?php foreach ($noticiasPanel as $i => $n): ?>
                <?php
                    $imagenNoticia = !empty($n['ImagenURL']) ? $n['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
                    $fondoBadge = $coloresBadgeEtiqueta[$n['Etiqueta']] ?? 'var(--primary-gradient)';
                    $fechaNoticia = !empty($n['FechaPublicacion']) ? date('d/m/Y', strtotime($n['FechaPublicacion'])) : '';
                ?>
                <div class="news-card animate-fade-in animate-delay-<?php echo min($i + 2, 4); ?>" data-id-noticia="<?php echo (int)$n['IdNoticia']; ?>">
                  <div class="news-card-image">
                    <img src="<?php echo htmlspecialchars($imagenNoticia); ?>" alt="<?php echo htmlspecialchars($n['Titulo']); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
                    <span class="news-card-badge" style="background: <?php echo $fondoBadge; ?>;"><?php echo htmlspecialchars($n['Etiqueta']); ?></span>
                    <?php if ($esAdminOSecPanel): ?>
                    <div class="news-card-admin-actions">
                      <button type="button" class="btn-admin-icon btn-editar-noticia" title="Editar"
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
                      <button type="button" class="btn-admin-icon btn-admin-icon-danger btn-eliminar-noticia" title="Eliminar" data-id="<?php echo (int)$n['IdNoticia']; ?>">
                        <i class="la la-trash"></i>
                      </button>
                    </div>
                    <?php endif; ?>
                  </div>
                  <div class="news-card-content">
                    <div class="news-card-meta">
                      <span><i class="far fa-calendar"></i> <?php echo htmlspecialchars($fechaNoticia); ?></span>
                      <span><i class="far fa-user"></i> <?php echo htmlspecialchars($n['Autor']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($n['Titulo']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($n['Descripcion'])); ?></p>
                    <?php if (!empty($n['Enlace'])): ?>
                    <div class="news-card-footer">
                      <a href="<?php echo htmlspecialchars($n['Enlace']); ?>" class="btn-read-more" target="_blank">
                        Leer más <i class="fas fa-arrow-right"></i>
                      </a>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>

              <!-- SECCIÓN DE CRONOGRAMA DE DEFENSA -->
              <div class="section-title animate-fade-in">
                <h2>Cronograma de Defensa</h2>
                <p>Fechas, horarios y lugares de defensa de tesis / monografía</p>
              </div>

              <?php if ($esAdminOSecPanel): ?>
              <div class="admin-toolbar-panel">
                <button type="button" class="btn-admin-agregar" id="btnNuevaDefensa">
                  <i class="fa fa-plus"></i> Nueva Defensa
                </button>
              </div>
              <?php endif; ?>

              <div class="defensa-tabla-wrapper animate-fade-in">
                <table class="defensa-tabla">
                  <thead>
                    <tr>
                      <th>Estudiante</th>
                      <th>Programa</th>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Lugar</th>
                      <?php if ($esAdminOSecPanel): ?><th class="text-right">Acciones</th><?php endif; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($defensasPanel)): ?>
                    <tr>
                      <td colspan="<?php echo $esAdminOSecPanel ? 6 : 5; ?>" class="text-center text-muted" style="padding: 30px;">
                        No hay defensas programadas por el momento.
                      </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($defensasPanel as $d): ?>
                    <?php
                        $fechaDefensa = date('d/m/Y', strtotime($d['FechaDefensa']));
                        $horaDefensa = !empty($d['HoraDefensa']) ? date('H:i', strtotime($d['HoraDefensa'])) : '-';
                        $programaTexto = !empty($d['NombrePrograma']) ? $d['NombrePrograma'] . ' (' . $d['GradoAcademico'] . ')' : '-';
                    ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($d['NombreEstudiante']); ?></strong>
                        <?php if (!empty($d['Detalle'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($d['Detalle']); ?></small><?php endif; ?>
                      </td>
                      <td><?php echo htmlspecialchars($programaTexto); ?></td>
                      <td><?php echo htmlspecialchars($fechaDefensa); ?></td>
                      <td><?php echo htmlspecialchars($horaDefensa); ?></td>
                      <td><?php echo htmlspecialchars($d['Lugar']); ?></td>
                      <?php if ($esAdminOSecPanel): ?>
                      <td class="text-right">
                        <button type="button" class="btn-admin-icon btn-editar-defensa" title="Editar"
                          data-id="<?php echo (int)$d['IdDefensa']; ?>"
                          data-nombre="<?php echo htmlspecialchars($d['NombreEstudiante'], ENT_QUOTES); ?>"
                          data-programa="<?php echo (int)($d['ProgramaID'] ?? 0); ?>"
                          data-fecha="<?php echo htmlspecialchars($d['FechaDefensa'], ENT_QUOTES); ?>"
                          data-hora="<?php echo htmlspecialchars($d['HoraDefensa'] ?? '', ENT_QUOTES); ?>"
                          data-lugar="<?php echo htmlspecialchars($d['Lugar'], ENT_QUOTES); ?>"
                          data-detalle="<?php echo htmlspecialchars($d['Detalle'] ?? '', ENT_QUOTES); ?>">
                          <i class="la la-edit"></i>
                        </button>
                        <button type="button" class="btn-admin-icon btn-admin-icon-danger btn-eliminar-defensa" title="Eliminar" data-id="<?php echo (int)$d['IdDefensa']; ?>">
                          <i class="la la-trash"></i>
                        </button>
                      </td>
                      <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- SECCIÓN DE REDES SOCIALES -->
              <div class="social-section animate-fade-in">
                <div class="section-title" style="margin: 0 0 20px;">
                  <h2>Síguenos en Redes Sociales</h2>
                  <p>Mantente conectado y no te pierdas ninguna actualización</p>
                </div>

                <div class="social-grid">
                  <!-- Facebook -->
                  <div class="social-card facebook">
                    <div class="social-icon">
                      <i class="fab fa-facebook-f"></i>
                    </div>
                    <h4>Facebook</h4>
                    <p>Síguenos para estar al día con nuestras publicaciones</p>
                    <button class="btn-social" onclick="window.open('https://facebook.com', '_blank')">
                      Seguir
                    </button>
                  </div>

                  <!-- Instagram -->
                  <div class="social-card instagram">
                    <div class="social-icon">
                      <i class="fab fa-instagram"></i>
                    </div>
                    <h4>Instagram</h4>
                    <p>Descubre momentos únicos de nuestro posgrado</p>
                    <button class="btn-social" onclick="window.open('https://instagram.com', '_blank')">
                      Seguir
                    </button>
                  </div>

                  <!-- YouTube -->
                  <div class="social-card youtube">
                    <div class="social-icon">
                      <i class="fab fa-youtube"></i>
                    </div>
                    <h4>YouTube</h4>
                    <p>Videos educativos y transmisiones en vivo</p>
                    <button class="btn-social" onclick="window.open('https://youtube.com', '_blank')">
                      Suscribirse
                    </button>
                  </div>

                  <!-- Twitter -->
                  <div class="social-card twitter">
                    <div class="social-icon">
                      <i class="fab fa-twitter"></i>
                    </div>
                    <h4>Twitter</h4>
                    <p>Noticias y actualizaciones al instante</p>
                    <button class="btn-social" onclick="window.open('https://twitter.com', '_blank')">
                      Seguir
                    </button>
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
          <div class="modal-body">
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
              <label class="font-weight-bold">Detalle (opcional)</label>
              <input type="text" class="form-control" id="defensaDetalle" maxlength="255" placeholder="Ej: Título del trabajo de grado">
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

<script src="vistas/recursos/assets/js/scripts/panel-carrusel.js?v=<?php echo filemtime('vistas/recursos/assets/js/scripts/panel-carrusel.js'); ?>"></script>

<?php if (in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)): ?>
<script>
// ========================================
// AVISO DE MORA: estudiantes con una cuota vencida al ingresar al sistema.
// Solo Administrador/Secretaría (control de cobranza).
// ========================================
// NOTA: se difiere a window 'load' (en vez de $(document).ready) porque jQuery
// se imprime más abajo en el HTML final de la página; para cuando el evento
// 'load' dispara, ya está disponible.
window.addEventListener('load', function() {
    $.ajax({
        url: 'ajax/ordenpago.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerOrdenesVencidas' },
        dataType: 'json',
        success: function(resp) {
            if (!resp.success || !resp.ordenes || resp.ordenes.length === 0) {
                return;
            }

            let filas = '';
            resp.ordenes.forEach(function(o) {
                const whatsappBtn = o.whatsapp
                    ? '<a href="' + o.whatsapp + '" target="_blank" class="btn btn-sm" ' +
                      'style="background:#25D366; color:#fff; padding:3px 10px;">' +
                      '<i class="fab fa-whatsapp"></i> WhatsApp</a>'
                    : '<span class="text-muted small">Sin celular</span>';

                filas += '<tr>' +
                    '<td style="text-align:left;">' + o.nombreEstudiante + '<br>' +
                    '<small class="text-muted">' + o.nombrePrograma + ' (' + o.sede + ')</small></td>' +
                    '<td class="text-right">Bs. ' + o.monto.toFixed(2) + '</td>' +
                    '<td class="text-center"><span class="badge badge-danger">' + o.diasVencido + ' día(s)</span></td>' +
                    '<td class="text-center">' + whatsappBtn + '</td>' +
                    '</tr>';
            });

            const tabla = '<div class="table-responsive" style="max-height:320px; overflow-y:auto; text-align:left;">' +
                '<table class="table table-sm table-bordered mb-0" style="font-size:13px;">' +
                '<thead><tr><th>Estudiante</th><th class="text-right">Monto</th><th class="text-center">Vencido hace</th><th class="text-center">Contactar</th></tr></thead>' +
                '<tbody>' + filas + '</tbody></table></div>';

            Swal.fire({
                icon: 'warning',
                title: 'Estudiantes con cuotas vencidas',
                html: '<p>Hay <strong>' + resp.ordenes.length + '</strong> orden(es) de pago vencidas sin cancelar:</p>' + tabla,
                width: 650,
                confirmButtonText: 'Entendido'
            });
        }
    });
});
</script>
<?php endif; ?>

<script src="vistas/recursos/assets/js/scripts/panel-info-programa.js?v=<?php echo time(); ?>"></script>

<?php if ($esAdminOSecPanel): ?>
<script src="vistas/recursos/assets/js/scripts/panel-admin.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>
