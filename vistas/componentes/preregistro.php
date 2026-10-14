<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir controlador
require_once 'controladores/ordenpago.controlador.php';

// Procesar formulario (usa el mismo backend de preregistro: Estado = PENDIENTE, sin voucher)
$ordenPago = new OrdenPagoControladores();
$ordenPago->RegistrarOrdenPagoControlador();

// Cargar la tabla de preregistros pendientes directamente desde el servidor,
// para que se vea de una vez aunque el AJAX del navegador falle por algún motivo.
$listaPreregistrosInicial = (new OrdenPagoControladores())->ListarPreregistrosControlador();

// Sedes disponibles, para filtrar el select de programa en el PASO 2
$sedesDisponibles = ProgramasModelos::ListarSedesModelo();

// Profesiones disponibles, para el formulario de datos del estudiante en el PASO 1
$listaProfesionesInicial = ProfesionModelos::ListaprofesionModelos();

// Universidades disponibles (universidad de egreso), para el mismo formulario del PASO 1
$listaUniversidadesInicial = UniversidadModelos::ListarUniversidadesModelo();

function PintarFilaPreregistro($pr)
{
    $nombreCompleto = trim(($pr['Apaterno'] ?? '') . ' ' . ($pr['Amaterno'] ?? '') . ' ' . ($pr['Nombre'] ?? ''));

    $fechaCorta = !empty($pr['FechaGeneracion']) ? date('d/m/Y', strtotime($pr['FechaGeneracion'])) : '';

    $html = '<tr>';
    $html .= '<td class="text-center">' . (int)$pr['IdOrdenPago'] . '</td>';
    $html .= '<td>' . htmlspecialchars($nombreCompleto) . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['Ci'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['NombrePrograma'] . ' (' . $pr['CodigoPrograma'] . ')') . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['Sede'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($fechaCorta) . '</td>';
    $html .= '<td class="text-center"><div class="acciones-preregistro">';
    $html .= '<button type="button" class="btn btn-accion btn-info btn-ver-detalle-preregistro" data-id="' . (int)$pr['IdOrdenPago'] . '" title="Ver Detalle"><i class="fa fa-eye"></i></button>';
    $html .= '<button type="button" class="btn btn-accion-texto btn-success btn-generar-orden" data-id="' . (int)$pr['IdOrdenPago'] . '" title="Generar Orden de Pago"><i class="fa fa-file-invoice"></i> Generar Orden</button>';
    $html .= '<a href="inscripcion?orden=' . (int)$pr['IdOrdenPago'] . '" class="btn btn-accion-texto btn-primary btn-validar-voucher" title="Validar el voucher en Inscripción"><i class="fa fa-check-circle"></i> Validar</a>';
    $html .= '<button type="button" class="btn btn-accion btn-warning btn-editar-preregistro" data-id="' . (int)$pr['IdOrdenPago'] . '" data-pagocompleto="' . (int)($pr['pagoCompleto'] ?? 0) . '" title="Editar"><i class="fa fa-edit"></i></button>';
    $html .= '<button type="button" class="btn btn-accion btn-danger btn-anular-preregistro" data-id="' . (int)$pr['IdOrdenPago'] . '" title="Anular"><i class="fa fa-trash"></i></button>';
    $html .= '</div></td></tr>';

    return $html;
}
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right
kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed
kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left
kt-aside--fixed kt-page--loading">

  <!-- Header Mobile -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
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
  <!-- End Header Mobile -->

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background-color: #EAEAEA; font-size:12pt;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

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

        <!-- Cuerpo principal -->
        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- Subheader -->
            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>NUEVO PRE-REGISTRO</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>GENERAR ORDEN DE PAGO DE MATRÍCULA</h3>
                    </a>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contenido principal -->
            <div class="kt-container kt-grid__item kt-grid__item--fluid">
              <div class="row">
                <div class="col-lg-12">

                  

                  <form method="POST" id="formPreregistro">

                  <!-- Campos que viajan por todo el asistente, sin importar el modal visible -->
                  <input type="hidden" name="idcliente" id="selectedEstudianteID" value="">
                  <input type="hidden" name="costoTotalPrograma" id="costoTotalPrograma" value="0">
                  <input type="hidden" name="costoMatriculaPrograma" id="costoMatriculaPrograma" value="0">
                  <input type="hidden" name="montoAPagar" id="montoAPagar" value="0">
                  <input type="hidden" name="pagoCompleto" value="0">
                  <input type="hidden" name="paginaRedirect" value="preregistro">

                  <!-- Punto de entrada del asistente: reemplaza el formulario largo en pantalla -->
                  <div class="pr-inicio">
                    <div class="pr-inicio-texto">
                      <div class="pr-inicio-titulo">Pre-Registro de matrícula</div>
                      <p>Registre al estudiante y genere su orden de pago de matrícula en cuatro pasos.</p>
                      <ol class="pr-inicio-pasos">
                        <li><span>1</span> Estudiante</li>
                        <li><span>2</span> Programa</li>
                        <li><span>3</span> Orden de pago</li>
                        <li><span>4</span> Confirmar</li>
                      </ol>
                    </div>
                    <button type="button" class="btn pr-inicio-boton" data-toggle="modal" data-target="#modalPasoEstudiante">
                      <i class="fa fa-user-plus"></i> Nuevo Pre-Registro
                    </button>
                  </div>

                  </form>

                  <!-- Los 4 modales del asistente (Estudiante, Programa, Orden de Pago,
                       Confirmar) están más abajo, fuera de #kt_wrapper (junto a los demás
                       modales de esta vista) para que su position:fixed no se rompa por los
                       contenedores del layout. Sus campos usan form="formPreregistro" para
                       seguir enviándose con este formulario aunque ya no sean descendientes
                       suyos en el DOM. -->

                  <!-- TABLA DE PREREGISTROS PENDIENTES (Read + Update + Delete) -->
                  <div class="kt-portlet mt-4 kt-portlet-preregistros-pendientes">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                      <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title" style="color: white;">
                          <i class="flaticon2-list"></i> PRE-REGISTROS PENDIENTES
                        </h3>
                      </div>
                      <div class="kt-portlet__head-toolbar">
                        <div class="kt-portlet__head-actions">
                          <button type="button" class="btn btn-sm btn-light" id="btnRefrescarPreregistros">
                            <i class="flaticon2-refresh-button"></i> Actualizar
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="kt-portlet__body">

                      <!-- Filtros de búsqueda -->
                      <div class="row mb-3 align-items-end">
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                          <label class="small font-weight-bold mb-1">CI</label>
                          <input type="text" class="form-control form-control-sm" id="filtroPreregistroCi" placeholder="Buscar por CI...">
                        </div>
                        <div class="col-md-4 form-group mb-2 mb-md-0">
                          <label class="small font-weight-bold mb-1">Programa</label>
                          <select class="form-control select2-uniforme" id="filtroPreregistroPrograma">
                            <option value="">Todos los programas</option>
                          </select>
                        </div>
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                          <label class="small font-weight-bold mb-1">Sede</label>
                          <select class="form-control select2-uniforme" id="filtroPreregistroSede">
                            <option value="">Todas las sedes</option>
                            <?php foreach ($sedesDisponibles as $sedeOpcion): ?>
                              <option value="<?php echo htmlspecialchars($sedeOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars($sedeOpcion); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-2 form-group mb-0">
                          <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="btnLimpiarFiltrosPreregistro">
                            <i class="fa fa-times"></i> Limpiar
                          </button>
                        </div>
                      </div>
                      <small class="text-muted d-block mb-2" id="contadorPreregistros"></small>

                      <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="tablaPreregistros">
                          <thead style="background: #f4f4f4;">
                            <tr>
                              <th class="text-center" style="width: 40px;">#</th>
                              <th>Estudiante</th>
                              <th style="width: 110px;">CI</th>
                              <th>Programa</th>
                              <th style="width: 110px;">Sede</th>
                              <th style="width: 110px;">Fecha</th>
                              <th class="text-center" style="width: 190px;">Acciones</th>
                            </tr>
                          </thead>
                          <tbody id="tablaPreregistrosBody">
                            <?php
                              if (empty($listaPreregistrosInicial)) {
                                  echo '<tr><td colspan="7" class="text-center text-muted"><i class="flaticon2-information"></i> No hay preregistros pendientes</td></tr>';
                              } else {
                                  foreach ($listaPreregistrosInicial as $pr) {
                                      echo PintarFilaPreregistro($pr);
                                  }
                              }
                            ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <script id="datosPreregistrosIniciales" type="application/json"><?php echo json_encode($listaPreregistrosInicial, JSON_HEX_TAG | JSON_HEX_AMP); ?></script>

                </div>
              </div>
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

                  <!-- ============================================ -->
                  <!-- MODAL PASO 1: ESTUDIANTE                      -->
                  <!-- ============================================ -->
                  <div class="modal fade" id="modalPasoEstudiante" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                      <div class="modal-content ficha-modal">
                        <div class="modal-header wizard-modal-header">
                          <div>
                            <div class="wizard-paso-texto">Pre-Registro · Paso 1 de 4</div>
                            <h5 class="modal-title text-white">Datos del Estudiante</h5>
                          </div>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">

                          <!-- Búsqueda por CI: si existe se autocompletan sus datos; si no, se habilita el formulario para registrarlo -->
                          <div class="ficha-busqueda">
                            <label for="buscarPorCi" class="ficha-busqueda-titulo">Buscar estudiante por C.I.</label>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                              <div class="input-group input-group-sm ficha-busqueda-campo">
                                <input type="text" class="form-control" id="buscarPorCi" placeholder="Número de C.I." maxlength="12">
                                <div class="input-group-append">
                                  <button type="button" class="btn btn-primary" id="btnBuscarPorCi">
                                    <i class="fa fa-search"></i> Buscar
                                  </button>
                                </div>
                              </div>
                              <button type="button" class="btn btn-sm btn-link ficha-enlace" id="btnLimpiarBusquedaCi">
                                <i class="fa fa-undo"></i> Limpiar
                              </button>
                              <span id="estadoBusquedaEstudiante"></span>
                            </div>
                          </div>

                          <!-- Datos del estudiante (se autocompletan si el CI ya existe; si no, se llenan aquí para registrarlo) -->
                          <div id="datosEstudianteForm" class="ficha-form">

                            <div class="ficha-seccion">
                              <div class="ficha-seccion-titulo">Identificación</div>
                              <div class="row">
                                <div class="col-md-4 form-group">
                                  <label for="inputCi">C.I. <span class="ficha-req">*</span></label>
                                  <input type="text" id="inputCi" name="Ci" form="formPreregistro" class="form-control form-control-sm"
                                         placeholder="1234567" required pattern="[0-9]{6,12}" maxlength="12">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="inputComplemento">Complemento</label>
                                  <input type="text" id="inputComplemento" name="Complemento" form="formPreregistro"
                                         class="form-control form-control-sm text-uppercase" pattern="[A-Za-z0-9]{1,5}">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="selectExpedido">Expedido <span class="ficha-req">*</span></label>
                                  <select class="form-control form-control-sm" id="selectExpedido" name="Exp" form="formPreregistro" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option value="LP">LA PAZ</option>
                                    <option value="CB">COCHABAMBA</option>
                                    <option value="SC">SANTA CRUZ</option>
                                    <option value="OR">ORURO</option>
                                    <option value="PT">POTOSÍ</option>
                                    <option value="CH">CHUQUISACA</option>
                                    <option value="TJ">TARIJA</option>
                                    <option value="BN">BENI</option>
                                    <option value="PD">PANDO</option>
                                    <option value="OTR">OTRO</option>
                                  </select>
                                </div>
                              </div>
                            </div>

                            <div class="ficha-seccion">
                              <div class="ficha-seccion-titulo">Datos personales</div>
                              <div class="row">
                                <div class="col-md-4 form-group">
                                  <label for="inputNombres">Nombre(s) <span class="ficha-req">*</span></label>
                                  <input type="text" id="inputNombres" name="Nombre" form="formPreregistro" class="form-control form-control-sm text-uppercase"
                                         required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="apaterno">Apellido paterno <span class="ficha-req">*</span></label>
                                  <input type="text" id="apaterno" name="Apaterno" form="formPreregistro" class="form-control form-control-sm text-uppercase" required>
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="amaterno">Apellido materno</label>
                                  <input type="text" id="amaterno" name="Amaterno" form="formPreregistro" class="form-control form-control-sm text-uppercase">
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-4 form-group">
                                  <label for="fechaNacimiento">Fecha de nacimiento <span class="ficha-req">*</span></label>
                                  <input type="date" id="fechaNacimiento" name="FechaNacimiento" form="formPreregistro" class="form-control form-control-sm" required
                                         max="<?php echo date('Y-m-d'); ?>"
                                         min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="Edad">Edad <span class="ficha-req">*</span></label>
                                  <input type="number" id="Edad" name="Edad" form="formPreregistro" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="Lugarn">Lugar de nacimiento <span class="ficha-req">*</span></label>
                                  <select class="form-control form-control-sm" id="Lugarn" name="Lugarn" form="formPreregistro" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <!-- El value va en MAYÚSCULAS porque así es como queda guardado en la BD
                                         (el backend aplica mb_strtoupper() al registrar/editar el estudiante).
                                         Si no coincide exactamente, el select no puede pre-seleccionar el
                                         valor guardado al cargar los datos de un estudiante existente. -->
                                    <option value="LA PAZ">LA PAZ</option>
                                    <option value="COCHABAMBA">COCHABAMBA</option>
                                    <option value="SANTA CRUZ">SANTA CRUZ</option>
                                    <option value="ORURO">ORURO</option>
                                    <option value="POTOSÍ">POTOSÍ</option>
                                    <option value="CHUQUISACA">CHUQUISACA</option>
                                    <option value="TARIJA">TARIJA</option>
                                    <option value="BENI">BENI</option>
                                    <option value="PANDO">PANDO</option>
                                    <option value="OTRO">OTRO</option>
                                  </select>
                                </div>
                              </div>
                            </div>

                            <div class="ficha-seccion">
                              <div class="ficha-seccion-titulo">Contacto</div>
                              <div class="row">
                                <div class="col-md-4 form-group">
                                  <label for="emailInput">Correo electrónico <span class="ficha-req">*</span></label>
                                  <input type="email" id="emailInput" name="Correo" form="formPreregistro" class="form-control form-control-sm" required maxlength="100">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="inputCelular">Celular <span class="ficha-req">*</span></label>
                                  <input type="tel" id="inputCelular" name="Celular" form="formPreregistro" class="form-control form-control-sm"
                                         required pattern="[6-7][0-9]{7}">
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="inputTelefono">Teléfono</label>
                                  <input type="tel" id="inputTelefono" name="Telefono" form="formPreregistro" class="form-control form-control-sm" pattern="[0-9]{7,8}">
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12 form-group">
                                  <label for="direccionInput">Dirección domiciliaria</label>
                                  <input type="text" id="direccionInput" name="Direccion" form="formPreregistro" class="form-control form-control-sm text-uppercase" maxlength="100">
                                </div>
                              </div>
                            </div>

                            <!-- Universidad y fecha de egreso: obligatorias; la fecha no puede ser
                                 posterior a hoy (el servidor también lo valida). -->
                            <div class="ficha-seccion">
                              <div class="ficha-seccion-titulo">Formación y trabajo</div>
                              <div class="row">
                                <div class="col-md-8 form-group">
                                  <label for="IdUniversidad">Universidad de egreso <span class="ficha-req">*</span></label>
                                  <div class="input-group input-group-sm">
                                    <select class="form-control form-control-sm" id="IdUniversidad" name="IdUniversidad" form="formPreregistro" required>
                                      <option value="">Seleccione una universidad...</option>
                                      <?php foreach ($listaUniversidadesInicial as $univ): ?>
                                        <option value="<?php echo (int)$univ['IdUniversidad']; ?>" data-tipo="<?php echo htmlspecialchars($univ['TipoUniversidad']); ?>"><?php echo htmlspecialchars($univ['NombreUniversidad']); ?></option>
                                      <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                      <button type="button" class="btn ficha-btn-nueva" id="btnNuevaUniversidad" title="Agregar una universidad que no está en la lista">
                                        <i class="fa fa-plus"></i> Nueva
                                      </button>
                                    </div>
                                  </div>
                                  <small class="form-text text-muted" id="IdUniversidadTipo"></small>
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="FechaEgreso">Fecha de egreso <span class="ficha-req">*</span></label>
                                  <input type="date" id="FechaEgreso" name="FechaEgreso" form="formPreregistro" class="form-control form-control-sm"
                                         max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-8 form-group">
                                  <label for="IdProfesion">Profesión <span class="ficha-req">*</span></label>
                                  <div class="input-group input-group-sm">
                                    <select class="form-control form-control-sm" id="IdProfesion" name="IdProfesion" form="formPreregistro" required>
                                      <option value="">Seleccione una profesión...</option>
                                      <?php foreach ($listaProfesionesInicial as $prof): ?>
                                        <option value="<?php echo (int)$prof['IdProfesion']; ?>"><?php echo htmlspecialchars($prof['NombreProfesion']); ?></option>
                                      <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                      <button type="button" class="btn ficha-btn-nueva" id="btnNuevaProfesion" title="Agregar una profesión que no está en la lista">
                                        <i class="fa fa-plus"></i> Nueva
                                      </button>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4 form-group">
                                  <label for="Trabajo">Trabajo actual</label>
                                  <input type="text" id="Trabajo" name="Trabajo" form="formPreregistro" class="form-control form-control-sm text-uppercase" maxlength="100">
                                </div>
                              </div>
                            </div>

                          </div>
                          <p class="ficha-nota"><span class="ficha-req">*</span> Campos obligatorios</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                          <button type="button" class="btn btn-primary" id="btnWizardPaso1Siguiente">
                            Siguiente <i class="fa fa-arrow-right"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ============================================ -->
                  <!-- MODAL PASO 2: PROGRAMA                        -->
                  <!-- ============================================ -->
                  <div class="modal fade" id="modalPasoPrograma" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                      <div class="modal-content ficha-modal">
                        <div class="modal-header wizard-modal-header">
                          <div>
                            <div class="wizard-paso-texto">Pre-Registro · Paso 2 de 4</div>
                            <h5 class="modal-title text-white">Programa</h5>
                          </div>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">

                          <div class="preregistro-step" id="seccionPrograma" style="display: none;">
                            <div class="ficha-seccion-titulo">Selección del programa</div>
                            <div class="row">
                              <div class="col-md-6 form-group mb-2">
                                <label for="sedePrograma">Sede</label>
                                <select class="form-control form-control-sm" id="sedePrograma" name="sedePrograma" form="formPreregistro">
                                  <option value="">TODAS LAS SEDES</option>
                                  <?php foreach ($sedesDisponibles as $sedeOpcion): ?>
                                    <option value="<?php echo htmlspecialchars($sedeOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars(mb_strtoupper($sedeOpcion, 'UTF-8')); ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="col-md-6 form-group mb-2">
                                <label for="gradoAcademico">Grado académico <span class="ficha-req">*</span></label>
                                <select class="form-control form-control-sm" id="gradoAcademico" name="gradoAcademico" form="formPreregistro" required>
                                  <option value="">Seleccione el grado...</option>
                                  <option value="DIPLOMADO">DIPLOMADO</option>
                                  <option value="MAESTRIA">MAESTRÍA</option>
                                  <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                                  <option value="DOCTORADO">DOCTORADO</option>
                                </select>
                              </div>
                              <div class="col-12 form-group mb-2">
                                <label for="programa">Programa <span class="ficha-req">*</span></label>
                                <!-- Solo programas activos, el último registrado primero; cada opción muestra
                                     código, versión, inicio y estado para distinguir los del mismo nombre -->
                                <select class="form-control form-control-sm" id="programa" name="programa" form="formPreregistro" required>
                                  <option value="">Seleccione el grado primero...</option>
                                </select>
                              </div>
                            </div>

                            <!-- Detalle del programa elegido -->
                            <div id="tablaPrograma" class="programa-resumen" style="display: none;">
                              <div class="ficha-seccion-titulo">Detalle del programa</div>
                              <div class="programa-resumen-grid">
                                <div class="programa-resumen-item">
                                  <span class="programa-resumen-label">Sede</span>
                                  <span id="proSede"></span>
                                </div>
                                <div class="programa-resumen-item">
                                  <span class="programa-resumen-label">Módulos</span>
                                  <span id="proModulos"></span>
                                </div>
                                <div class="programa-resumen-item">
                                  <span class="programa-resumen-label">Costo matrícula</span>
                                  <strong class="text-primary" id="proMatricula"></strong>
                                </div>
                                <div class="programa-resumen-item">
                                  <span class="programa-resumen-label">Costo programa</span>
                                  <strong class="text-primary" id="proPrograma"></strong>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                          <button type="button" class="btn btn-secondary" id="btnWizardPaso2Atras"><i class="fa fa-arrow-left"></i> Atrás</button>
                          <button type="button" class="btn btn-primary" id="btnWizardPaso2Siguiente">
                            Siguiente <i class="fa fa-arrow-right"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ============================================ -->
                  <!-- MODAL PASO 3: ORDEN DE PAGO DE MATRÍCULA      -->
                  <!-- ============================================ -->
                  <div class="modal fade" id="modalPasoPago" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                      <div class="modal-content ficha-modal">
                        <div class="modal-header wizard-modal-header">
                          <div>
                            <div class="wizard-paso-texto">Pre-Registro · Paso 3 de 4</div>
                            <h5 class="modal-title text-white">Orden de Pago de Matrícula</h5>
                          </div>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">

                          <div class="preregistro-step" id="seccionPago" style="display: none;">

                            <!-- El Paso 3 se llena de arriba abajo, en el orden en que se decide:
                                 1) plan, 2) matrícula, 3) programa, 4) cuotas y 5) dónde depositar.
                                 Cada bloque muestra el costo de lista, lo que se cambia y el resultado. -->

                            <!-- 1. Plan de pago (Plan Grupal: el grupo se arma aquí, con estudiantes del
                                 mismo programa; cada integrante tiene su propia orden y sus descuentos) -->
                            <div class="orden-subseccion">
                              <div class="orden-subseccion-titulo"><span class="orden-paso-num">1</span> Plan de pago</div>
                              <div class="row align-items-end">
                                <div class="col-md-5 form-group mb-2 mb-md-0">
                                  <label for="tipoPagoCodigo">Plan</label>
                                  <select class="form-control form-control-sm" id="tipoPagoCodigo" name="tipoPagoCodigo" form="formPreregistro">
                                    <option value="REGULAR">REGULAR (EN CUOTAS)</option>
                                    <option value="CONTADO">AL CONTADO</option>
                                    <option value="GRUPAL">GRUPAL</option>
                                  </select>
                                </div>
                                <div class="col-md-7 form-group mb-0" id="grupoCodigoGrupoPreregistro" style="display:none;">
                                  <label for="codigoGrupo">Grupo</label>
                                  <select class="form-control form-control-sm" id="codigoGrupo" name="codigoGrupo" form="formPreregistro">
                                    <option value="">NUEVO GRUPO</option>
                                  </select>
                                  <div class="small text-muted mt-1">Elija un grupo ya armado de este programa o cree uno nuevo con este estudiante.</div>
                                </div>
                              </div>
                            </div>

                            <!-- 2. Matrícula: se cobra o no; con su % de descuento (se aplica a esta orden) -->
                            <div class="orden-subseccion">
                              <div class="orden-subseccion-titulo"><span class="orden-paso-num">2</span> Matrícula</div>
                              <div class="row align-items-end">
                                <div class="col-md-3 form-group mb-2 mb-md-0">
                                  <label class="text-nowrap">Costo de matrícula</label>
                                  <div class="form-control form-control-sm bg-light" id="paso3CostoMatricula">-</div>
                                </div>
                                <div class="col-md-3 form-group mb-2 mb-md-0" id="grupoPagoMatriculaPreregistro">
                                  <label class="d-block">&nbsp;</label>
                                  <!-- Recuadro del mismo alto que los demás campos, para que la casilla quede alineada -->
                                  <div class="form-control form-control-sm casilla-matricula">
                                    <div class="custom-control custom-checkbox">
                                      <input type="checkbox" class="custom-control-input" id="pagarMatricula" name="pagarMatricula" form="formPreregistro" value="1" checked>
                                      <label class="custom-control-label text-nowrap" for="pagarMatricula">Cobrar matrícula</label>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-2 form-group mb-2 mb-md-0" id="grupoDescuentoPreregistro">
                                  <label for="porcentajeDescuento" class="text-nowrap">Descuento</label>
                                  <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm" id="porcentajeDescuento" name="porcentajeDescuento" form="formPreregistro" min="0" max="100" step="0.01" value="0" required>
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                  </div>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                  <label for="montoSoloMatricula" class="text-nowrap">Matrícula a pagar</label>
                                  <div class="input-group input-group-sm orden-resumen-monto">
                                    <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                                    <input type="number" step="0.01" min="0" class="form-control font-weight-bold" id="montoSoloMatricula" readonly>
                                  </div>
                                </div>
                              </div>
                              <div class="small text-muted mt-1" id="textoMatriculaExonerada" style="display:none;">
                                <i class="flaticon2-information"></i> Exonerada: no se cobra.
                              </div>
                              <div class="small text-warning mt-1" id="avisoMatriculaEditable" style="display:none;">
                                <i class="fa fa-unlock-alt"></i> Matrícula a pagar editable: universidad privada, extranjera o de convenio.
                              </div>
                              <!-- Cuánto sube o baja la matrícula frente a su costo, en % y en Bs. -->
                              <div class="small mt-1" id="textoVariacionMatricula" style="display:none;"></div>
                            </div>

                            <!-- 3. Programa: su % de descuento queda en el plan al validar el voucher.
                                 Para cambiar los descuentos después hay que anular y rehacer el pre-registro. -->
                            <div class="orden-subseccion">
                              <div class="orden-subseccion-titulo"><span class="orden-paso-num">3</span> Costo del programa</div>
                              <div class="row align-items-end">
                                <div class="col-md-3 form-group mb-2 mb-md-0">
                                  <label class="text-nowrap">Costo del programa</label>
                                  <div class="form-control form-control-sm bg-light" id="paso3CostoPrograma">-</div>
                                </div>
                                <!-- El total del programa (y con él las cuotas) puede bajar o subir -->
                                <div class="col-md-5 form-group mb-2 mb-md-0" id="grupoDescuentoProgramaPreregistro">
                                  <label for="porcentajeDescuentoPrograma" class="text-nowrap">Descuento o aumento</label>
                                  <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                      <select class="custom-select custom-select-sm ajuste-programa" id="tipoAjustePrograma" name="tipoAjustePrograma" form="formPreregistro">
                                        <option value="DESCUENTO">Descuento</option>
                                        <option value="AUMENTO">Aumento</option>
                                      </select>
                                    </div>
                                    <input type="number" class="form-control form-control-sm" id="porcentajeDescuentoPrograma" name="porcentajeDescuentoPrograma" form="formPreregistro" min="0" max="100" step="0.01" value="0" required>
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                  </div>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                  <label for="montoProgramaConDescuento" class="text-nowrap">Total del programa a pagar</label>
                                  <div class="input-group input-group-sm orden-resumen-monto">
                                    <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                                    <input type="number" class="form-control font-weight-bold" id="montoProgramaConDescuento" name="montoPrograma" form="formPreregistro" min="0.01" step="0.01" required>
                                  </div>
                                </div>
                              </div>
                              <!-- El total se puede poner con el % (descuento o aumento) o escribirlo en Bs.: en ese
                                   caso el % se llena solo con el equivalente y aquí se dice cuánto baja o sube -->
                              <div class="small text-muted mt-1" id="textoDescuentoPrograma">Ponga el % de descuento o aumento, o escriba directamente el total a pagar.</div>
                            </div>

                            <!-- 4. Cuotas del total del programa (Al Contado hasta 3). Con los módulos a la vista
                                 se decide si cada cuota cubre un módulo o se cambia el N° de cuotas. El monto
                                 sale redondeado (la última ajusta) y solo se cambia marcando "Modificar cuota". -->
                            <div class="orden-subseccion" id="grupoCuotaPreregistro">
                              <div class="orden-subseccion-titulo"><span class="orden-paso-num">4</span> Cuotas del programa</div>
                              <div class="row align-items-end">
                                <div class="col-md-3 form-group mb-2 mb-md-0">
                                  <label class="text-nowrap">Módulos del programa</label>
                                  <div class="form-control form-control-sm bg-light" id="modulosProgramaPaso3">-</div>
                                </div>
                                <div class="col-md-3 form-group mb-2 mb-md-0" id="grupoNumeroCuotasPreregistro">
                                  <label for="numeroCuotas" class="text-nowrap">N° de cuotas</label>
                                  <input type="number" class="form-control form-control-sm" id="numeroCuotas" name="numeroCuotas" form="formPreregistro" min="1" max="60" step="1" value="1" required>
                                </div>
                                <div class="col-md-3 form-group mb-2 mb-md-0">
                                  <label for="montoCuota" class="text-nowrap">Monto por cuota</label>
                                  <div class="input-group input-group-sm orden-resumen-monto">
                                    <div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>
                                    <input type="number" class="form-control form-control-sm font-weight-bold" id="montoCuota" name="montoCuota" form="formPreregistro" min="0.01" step="0.01" readonly>
                                  </div>
                                </div>
                                <div class="col-md-3 form-group mb-0">
                                  <label class="d-block">&nbsp;</label>
                                  <div class="form-control form-control-sm casilla-matricula">
                                    <div class="custom-control custom-checkbox">
                                      <input type="checkbox" class="custom-control-input" id="modificarCuota" name="modificarCuota" form="formPreregistro" value="1">
                                      <label class="custom-control-label text-nowrap" for="modificarCuota">Modificar cuota</label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="small text-muted mt-2" id="textoCuotasPrograma">Se paga en cuotas después de la matrícula.</div>
                              <!-- Cuota modificada: cuánto sube o baja frente a la calculada -->
                              <div class="small mt-1" id="textoVariacionCuota" style="display:none;"></div>
                              <div class="small mt-1" id="avisoModulosCuotas" style="display:none;"></div>
                            </div>

                            <!-- 5. Cuenta a la que se deposita la matrícula y fecha de la orden -->
                            <div class="orden-subseccion mb-0">
                              <div class="orden-subseccion-titulo"><span class="orden-paso-num">5</span> Depósito de la matrícula</div>
                              <div class="row">
                                <div class="col-md-8 form-group mb-2 mb-md-0">
                                  <label for="cuentaPago">Cuenta de depósito</label>
                                  <select class="form-control form-control-sm" id="cuentaPago" name="ProgramaCuentaID" form="formPreregistro" required>
                                    <option value="">Seleccione un programa primero...</option>
                                  </select>
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                  <label for="fechaOrdenPreregistro">Fecha de orden</label>
                                  <input type="date" class="form-control form-control-sm" id="fechaOrdenPreregistro" name="fechaInscripcion" form="formPreregistro" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                              </div>
                            </div>

                          </div>

                        </div>
                        <div class="modal-footer">
                          <button type="reset" class="btn btn-outline-secondary">
                            <i class="flaticon2-reload"></i> Limpiar
                          </button>
                          <button type="button" class="btn btn-secondary" id="btnWizardPaso3Atras"><i class="fa fa-arrow-left"></i> Atrás</button>
                          <button type="button" class="btn btn-primary" id="btnWizardPaso3Revisar">
                            Revisar y Confirmar <i class="fa fa-arrow-right"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ============================================ -->
                  <!-- MODAL PASO 4: CONFIRMAR Y GUARDAR             -->
                  <!-- ============================================ -->
                  <div class="modal fade" id="modalConfirmarPreregistro" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                      <div class="modal-content ficha-modal">
                        <div class="modal-header wizard-modal-header">
                          <div>
                            <div class="wizard-paso-texto">Pre-Registro · Paso 4 de 4</div>
                            <h5 class="modal-title text-white">Confirmar Datos</h5>
                          </div>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                          <p class="text-muted small mb-3">
                            <i class="flaticon2-information"></i> Revise que los datos sean correctos antes de guardar. Puede volver atrás para corregir cualquier sección.
                          </p>

                          <div class="resumen-confirmacion-seccion">
                            <div class="resumen-confirmacion-titulo">1. Estudiante</div>
                            <table class="table table-sm table-borderless mb-0 resumen-confirmacion-tabla">
                              <tbody>
                                <tr><td>Nombre completo</td><td id="resumenEstudianteNombre">-</td></tr>
                                <tr><td>C.I.</td><td id="resumenEstudianteCi">-</td></tr>
                                <tr><td>Correo</td><td id="resumenEstudianteCorreo">-</td></tr>
                                <tr><td>Celular</td><td id="resumenEstudianteCelular">-</td></tr>
                              </tbody>
                            </table>
                          </div>

                          <div class="resumen-confirmacion-seccion">
                            <div class="resumen-confirmacion-titulo">2. Programa</div>
                            <table class="table table-sm table-borderless mb-0 resumen-confirmacion-tabla">
                              <tbody>
                                <tr><td>Programa</td><td id="resumenProgramaNombre">-</td></tr>
                                <tr><td>Grado</td><td id="resumenProgramaGrado">-</td></tr>
                                <tr><td>Sede</td><td id="resumenProgramaSede">-</td></tr>
                              </tbody>
                            </table>
                          </div>

                          <!-- Lo que se guarda del Paso 3, en el mismo orden en que se llenó -->
                          <div class="resumen-confirmacion-seccion">
                            <div class="resumen-confirmacion-titulo">3. Plan de pago</div>
                            <table class="table table-sm table-borderless mb-0 resumen-confirmacion-tabla">
                              <tbody>
                                <tr><td>Plan</td><td id="resumenPlanPago">-</td></tr>
                              </tbody>
                            </table>
                          </div>

                          <div class="resumen-confirmacion-seccion">
                            <div class="resumen-confirmacion-titulo">4. Matrícula (esta orden de pago)</div>
                            <table class="table table-sm table-borderless mb-0 resumen-confirmacion-tabla">
                              <tbody>
                                <tr><td>Costo de matrícula</td><td id="resumenCostoMatricula">-</td></tr>
                                <tr><td>Descuento / aumento</td><td id="resumenDescuentoMatricula">-</td></tr>
                                <tr><td>Cuenta de depósito</td><td id="resumenCuentaPago">-</td></tr>
                                <tr><td>Fecha de orden</td><td id="resumenFechaOrden">-</td></tr>
                                <tr class="resumen-confirmacion-total"><td>Matrícula a pagar</td><td id="resumenMontoMatricula">Bs. 0,00</td></tr>
                              </tbody>
                            </table>
                          </div>

                          <div class="resumen-confirmacion-seccion">
                            <div class="resumen-confirmacion-titulo">5. Programa y cuotas (después de la matrícula)</div>
                            <table class="table table-sm table-borderless mb-0 resumen-confirmacion-tabla">
                              <tbody>
                                <tr><td>Costo del programa</td><td id="resumenCostoPrograma">-</td></tr>
                                <tr><td>Descuento / aumento</td><td id="resumenDescuentoPrograma">-</td></tr>
                                <tr><td>Módulos del programa</td><td id="resumenModulosPrograma">-</td></tr>
                                <tr><td>Cuotas</td><td id="resumenCuotasPrograma">-</td></tr>
                                <tr class="resumen-confirmacion-total"><td>Total del programa a pagar</td><td id="resumenMontoPrograma">Bs. 0,00</td></tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" id="btnWizardPaso4Atras"><i class="fa fa-arrow-left"></i> Atrás</button>
                          <button type="submit" name="registrarOrdenPago" form="formPreregistro" class="btn btn-primary">
                            <i class="fa fa-check"></i> Confirmar y guardar
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
  <!-- Modal: Editar Preregistro (CRUD - Update) -->
  <div class="modal fade" id="ModalEditarPreregistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title text-white"><i class="flaticon2-edit"></i> Editar Pre-Registro</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditarPreregistro">
          <input type="hidden" name="idOrdenPago" id="editIdOrdenPago">
          <input type="hidden" name="pagoCompleto" id="editPagoCompleto">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Monto a Pagar (Bs.) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" class="form-control" name="montoPagado" id="editMontoPagado" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Fecha de Orden <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="fechaInscripcion" id="editFechaInscripcion" required>
            </div>
            <small class="text-muted"><i class="flaticon2-information"></i> Solo se pueden editar preregistros que sigan en estado PENDIENTE.</small>
          </div>
          <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="flaticon2-check-mark"></i> Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Agregar una profesión nueva al select -->
  <div class="modal fade" id="ModalNuevaProfesion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Nueva Profesión</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formNuevaProfesion">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Nombre de la Profesión <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" id="inputNuevaProfesion" maxlength="100" required autofocus>
              <small class="form-text text-muted">Se agregará al select y quedará seleccionada.</small>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Agregar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Agregar una universidad nueva al select -->
  <div class="modal fade" id="ModalNuevaUniversidad" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title text-white"><i class="fa fa-plus"></i> Nueva Universidad</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formNuevaUniversidad">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Nombre de la Universidad <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" id="inputNuevaUniversidad" maxlength="150" required autofocus>
              <small class="form-text text-muted">Se agregará al select y quedará seleccionada.</small>
            </div>
            <div class="form-group mb-0">
              <label class="font-weight-bold">Tipo de Universidad <span class="text-danger">*</span></label>
              <select class="form-control" id="selectNuevaUniversidadTipo" required>
                <option value="" disabled selected>Seleccione...</option>
                <option value="PUBLICA">PÚBLICA</option>
                <option value="PRIVADA">PRIVADA</option>
                <option value="EXTRANJERA">EXTRANJERA</option>
                <option value="CONVENIO">DE CONVENIO</option>
              </select>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Agregar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Detalle de la solicitud de orden de pago -->
  <div class="modal fade" id="ModalDetallePreregistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content ficha-modal">
        <div class="modal-header wizard-modal-header">
          <div>
            <div class="wizard-paso-texto" id="detalleSolicitudOrden">Solicitud de pre-registro</div>
            <h5 class="modal-title text-white">Detalle de la Solicitud</h5>
          </div>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="detallePreregistroContenido">
          <div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Generar Orden de Pago (datos para la factura) -->
  <div class="modal fade" id="ModalGenerarOrdenPago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e7145 0%, #27ae60 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title text-white"><i class="fa fa-file-invoice"></i> Generar Orden de Pago</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formGenerarOrdenPago" method="POST" target="_blank"
              action="vistas/componentes/generar-orden-pago-pdf.php">
          <input type="hidden" name="idOrdenPago" id="generarIdOrdenPago">
          <!-- Datos que se completan automáticamente (estudiante, programa, monto) para el PDF -->
          <input type="hidden" name="apaterno" id="generarApaterno">
          <input type="hidden" name="amaterno" id="generarAmaterno">
          <input type="hidden" name="nombres" id="generarNombres">
          <input type="hidden" name="correo" id="generarCorreo">
          <input type="hidden" name="ci" id="generarCi">
          <input type="hidden" name="celular" id="generarCelular">
          <input type="hidden" name="programa" id="generarPrograma">
          <input type="hidden" name="montoNumeral" id="generarMontoNumeral">
          <input type="hidden" name="montoLiteral" id="generarMontoLiteral">
          <input type="hidden" name="version" id="generarVersion">
          <input type="hidden" name="numeroTramite" id="generarNumeroTramite">
          <div class="modal-body">
            <small class="text-muted d-block mb-3">
              <i class="flaticon2-information"></i> Estos datos se usarán para la factura de la orden de pago. El resto de los datos (estudiante, programa y monto) se completan automáticamente.
            </small>
            <div class="form-group">
              <label class="font-weight-bold">Nombre para Factura <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nombreFactura" id="generarNombreFactura" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">NIT o C.I. <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nitCiFactura" id="generarNitCiFactura" required>
            </div>
            <div class="form-group">
              <label class="font-weight-bold">Responsable de la Firma</label>
              <div class="input-group">
                <select class="form-control" name="firma" id="generarFirma">
                  <option value="">-- Seleccione --</option>
                </select>
                <div class="input-group-append">
                  <button type="button" class="btn btn-outline-secondary" id="btnAgregarFirmantePreregistro" title="Agregar nuevo nombre">
                    <i class="fa fa-plus"></i>
                  </button>
                </div>
              </div>
              <!-- Caja en línea (no SweetAlert): un popup con <input> lanzado sobre un modal
                   de Bootstrap ya abierto queda visible pero no recibe el foco del teclado
                   (el modal se lo roba de vuelta). -->
              <div id="nuevoFirmanteBoxPreregistro" class="input-group mt-2" style="display:none;">
                <input type="text" class="form-control text-uppercase" id="nuevoFirmanteNombrePreregistro" placeholder="Nombre completo" maxlength="150">
                <div class="input-group-append">
                  <button type="button" class="btn btn-success" id="btnGuardarFirmantePreregistro" title="Guardar"><i class="fa fa-check"></i></button>
                  <button type="button" class="btn btn-outline-secondary" id="btnCancelarFirmantePreregistro" title="Cancelar"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <small class="form-text text-muted">Nombre que aparecerá en la línea de firma de la orden impresa</small>
            </div>
          </div>
          <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success"><i class="fa fa-file-invoice"></i> Generar Orden de Pago</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<!-- Scripts adicionales (jQuery y Select2 ya se cargan globalmente en vistas/plantilla.php) -->
<script src="vistas/recursos/sweetalert.min.js"></script>

<script>
// IMPORTANTE: se usa un listener nativo de DOMContentLoaded (en vez de $(document).ready)
// a propósito. Varios scripts globales del tema (cargados en vistas/plantilla.php en TODAS
// las páginas, ej. wizard-1.js) asumen elementos que no existen aquí y lanzan errores dentro
// de su propio $(document).ready(). jQuery dispara todos los .ready() registrados en una sola
// cola: si uno de ellos lanza una excepción, corta la cola y los .ready() registrados después
// (como el de esta vista) nunca llegan a ejecutarse. Un listener nativo queda aislado de eso.
function iniciarPreregistro() {

    console.log('=== INICIANDO PRE-REGISTRO ===');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Select2 disponible:', typeof $.fn.select2);

    // ============================================
    // PASO 1: BUSCAR ESTUDIANTE POR CI (autocompleta si existe, o habilita el
    // formulario para registrarlo ahí mismo si no existe)
    // ============================================

    // Profesión y Universidad con buscador (las listas crecen con el tiempo).
    // dropdownParent: dentro de un modal de Bootstrap, si la lista se dibuja fuera
    // de él, el cuadro de búsqueda no recibe el foco. Si el valor se cambia por
    // código, hay que avisarle con .trigger('change.select2').
    $('#IdProfesion, #IdUniversidad').each(function() {
        $(this).select2({
            width: 'style',
            dropdownParent: $('#modalPasoEstudiante'),
            placeholder: $(this).find('option[value=""]').text(),
            language: { noResults: function() { return 'Sin resultados: use el botón "Nueva"'; } }
        });
    });

    // Hasta que se haga una búsqueda, el formulario de datos permanece bloqueado.
    // Incluye los botones "+" de Nueva Profesión y Nueva Universidad: si el
    // estudiante ya está registrado, sus datos quedan de solo lectura, así que
    // tampoco debe poder agregarse un valor nuevo en esos selects desde ahí.
    function habilitarCamposEstudiante(habilitar) {
        $('#datosEstudianteForm input, #datosEstudianteForm select').prop('disabled', !habilitar);
        $('#btnNuevaProfesion').prop('disabled', !habilitar);
        $('#btnNuevaUniversidad').prop('disabled', !habilitar);
    }
    habilitarCamposEstudiante(false);

    function autocompletarEstudiante(est) {
        $('#inputCi').val(est.Ci || '');
        $('#inputComplemento').val(est.Complemento || '');
        $('#selectExpedido').val(est.Exp || '');
        $('#inputNombres').val(est.Nombre || '');
        $('#apaterno').val(est.Apaterno || '');
        $('#amaterno').val(est.Amaterno || '');
        $('#fechaNacimiento').val(est.FechaNacimiento ? est.FechaNacimiento.substring(0, 10) : '');
        $('#Edad').val(est.Edad || '');
        $('#Lugarn').val(est.Lugarn || '');
        $('#emailInput').val(est.Correo || '');
        $('#inputTelefono').val(est.Telefono || '');
        $('#inputCelular').val(est.Celular || '');
        $('#IdProfesion').val(est.IdProfesion || '').trigger('change.select2');
        $('#Trabajo').val(est.Trabajo || '');
        $('#direccionInput').val(est.Direccion || '');
        $('#IdUniversidad').val(est.IdUniversidad || '').trigger('change.select2');
        actualizarTipoUniversidad();
        $('#FechaEgreso').val(est.FechaEgreso ? est.FechaEgreso.substring(0, 10) : '');
        validarFechaEgreso();
    }

    function limpiarCamposEstudiante(ciPrellenado) {
        $('#datosEstudianteForm input[type="text"], #datosEstudianteForm input[type="email"], #datosEstudianteForm input[type="tel"], #datosEstudianteForm input[type="number"], #datosEstudianteForm input[type="date"]').val('');
        $('#datosEstudianteForm select').val('').trigger('change.select2');
        $('#inputCi').val(ciPrellenado || '');
        actualizarTipoUniversidad();
        validarFechaEgreso();
    }

    function buscarEstudiantePorCi() {
        const ci = $('#buscarPorCi').val().trim();

        if (!ci) {
            swal("Atención", "Ingrese un CI para buscar", "warning");
            return;
        }

        $('#estadoBusquedaEstudiante').html('<i class="fa fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'buscarEstudiantePorCi', ci: ci },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    $('#estadoBusquedaEstudiante').html('<span class="text-danger">Error al buscar</span>');
                    swal("Error", resp.mensaje || "No se pudo buscar el estudiante", "error");
                    return;
                }

                if (resp.encontrado) {
                    autocompletarEstudiante(resp.estudiante);
                    habilitarCamposEstudiante(false);
                    $('#selectedEstudianteID').val(resp.estudiante.EstudianteID);
                    $('#estadoBusquedaEstudiante').html('<span class="badge badge-success"><i class="fa fa-check"></i> Estudiante encontrado, datos autocompletados</span>');
                    // Sus datos aquí son de solo lectura: si le falta el egreso, se completa en Estudiantes
                    if (!resp.estudiante.IdUniversidad || !resp.estudiante.FechaEgreso) {
                        swal("Faltan datos", "Este estudiante no tiene registrada su universidad o su fecha de egreso. Complételos en el módulo Estudiantes antes de generar el pre-registro.", "warning");
                    }
                } else {
                    limpiarCamposEstudiante(ci);
                    habilitarCamposEstudiante(true);
                    $('#selectedEstudianteID').val('');
                    $('#estadoBusquedaEstudiante').html('<span class="badge badge-warning"><i class="fa fa-info-circle"></i> No existe, complete los datos para registrarlo</span>');
                }

                $('#seccionPrograma').slideDown();
            },
            error: function() {
                $('#estadoBusquedaEstudiante').html('<span class="text-danger">Error al buscar</span>');
                swal("Error", "No se pudo buscar el estudiante", "error");
            }
        });
    }

    $('#btnBuscarPorCi').on('click', buscarEstudiantePorCi);

    $('#buscarPorCi').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarEstudiantePorCi();
        }
    });

    $('#btnLimpiarBusquedaCi').on('click', function() {
        $('#buscarPorCi').val('');
        $('#selectedEstudianteID').val('');
        limpiarCamposEstudiante('');
        habilitarCamposEstudiante(false);
        $('#estadoBusquedaEstudiante').html('');
        $('#seccionPrograma').slideUp();
        $('#seccionPago').slideUp();
    });

    // FIX: modales "anidados" (Nueva Profesión / Nueva Universidad, abiertos con
    // el botón "+" desde dentro del modal "Paso 1: Datos del Estudiante", que a
    // su vez es scrollable). Bootstrap 4 tiene un bug conocido: al cerrarse un
    // modal abierto encima de otro, quita la clase "modal-open" del <body>
    // aunque el modal de atrás siga visible. Esa clase es la que le da
    // "overflow-y: auto" al modal (ver CSS ".modal-open .modal"); sin ella, el
    // modal de atrás vuelve a su "overflow: hidden" por defecto y ya no se
    // puede hacer scroll para llegar al botón de continuar/guardar. Se
    // restaura la clase a mano si, al cerrarse, todavía queda algún modal abierto.
    $('#ModalNuevaProfesion, #ModalNuevaUniversidad').on('hidden.bs.modal', function() {
        if ($('.modal.show').length) {
            $('body').addClass('modal-open');
        }
    });

    // Agregar una profesión que no está en el select (botón "+")
    // Nombre comparable: sin tildes, sin espacios de más y en mayúsculas (igual que
    // compara la base), para no registrar dos veces la misma profesión/universidad.
    function nombreComparable(texto) {
        return String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ').trim().toUpperCase();
    }

    // Universidad: además se ignora la sigla entre paréntesis y los signos, y cuenta
    // como la misma si coincide la sigla o si se escribió solo la sigla (igual que
    // UniversidadModelos::BuscarParecidaModelo en el servidor).
    function claveUniversidad(texto) {
        return nombreComparable(String(texto).replace(/\([^)]*\)/g, ' ')).replace(/[^A-Z0-9Ñ ]+/g, ' ').replace(/\s+/g, ' ').trim();
    }
    function siglaUniversidad(texto) {
        const m = String(texto).match(/\(([^)]*)\)/);
        return m ? claveUniversidad(m[1]) : '';
    }
    function mismaUniversidad(escrito, existente) {
        const clave = claveUniversidad(escrito), sigla = siglaUniversidad(escrito), siglaExistente = siglaUniversidad(existente);
        return (clave !== '' && clave === claveUniversidad(existente)) ||
            (siglaExistente !== '' && (siglaExistente === sigla || siglaExistente === clave));
    }

    // Si el nombre escrito ya está en el select, no se registra de nuevo: se
    // selecciona el existente y se avisa. Devuelve true si ya existía.
    function seleccionarSiYaExiste(selector, nombre, etiqueta, modal, esLaMisma) {
        esLaMisma = esLaMisma || function(a, b) { return nombreComparable(a) === nombreComparable(b); };
        const opcion = $(selector).find('option').filter(function() {
            return this.value !== '' && esLaMisma(nombre, $(this).text());
        }).first();
        if (!opcion.length) return false;

        $(selector).val(opcion.val()).trigger('change.select2');
        $(modal).modal('hide');
        swal('Ya está registrada', 'La ' + etiqueta + ' "' + opcion.text() + '" ya existe en la lista: se seleccionó, no se registra de nuevo.', 'warning');
        return true;
    }

    $('#btnNuevaProfesion').on('click', function() {
        $('#formNuevaProfesion')[0].reset();
        $('#ModalNuevaProfesion').modal('show');
    });

    $('#formNuevaProfesion').on('submit', function(e) {
        e.preventDefault();

        const nombre = $('#inputNuevaProfesion').val().trim();
        if (!nombre) {
            return;
        }
        if (seleccionarSiYaExiste('#IdProfesion', nombre, 'profesión', '#ModalNuevaProfesion')) {
            return;
        }

        const btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true);

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarProfesionRapida', nombreProfesion: nombre },
            dataType: 'json',
            success: function(resp) {
                btnSubmit.prop('disabled', false);

                if (!resp.success) {
                    swal('Error', resp.mensaje || 'No se pudo agregar la profesión', 'error');
                    return;
                }

                const prof = resp.profesion;
                const yaExiste = $('#IdProfesion option[value="' + prof.IdProfesion + '"]').length > 0;
                if (!yaExiste) {
                    $('#IdProfesion').append(
                        $('<option></option>').attr('value', prof.IdProfesion).text(prof.NombreProfesion)
                    );
                }
                $('#IdProfesion').val(prof.IdProfesion).trigger('change.select2');

                $('#ModalNuevaProfesion').modal('hide');
                if (resp.duplicado) {
                    swal('Ya está registrada', 'Esa profesión ya existía: se seleccionó del listado, no se registró de nuevo.', 'warning');
                }
            },
            error: function() {
                btnSubmit.prop('disabled', false);
                swal('Error', 'No se pudo agregar la profesión', 'error');
            }
        });
    });

    // ============================================
    // UNIVERSIDAD DE EGRESO: mostrar su tipo (pública/privada/extranjera/convenio)
    // apenas se selecciona, y agregar una nueva al select (botón "+"), igual que
    // con Profesión. Cada universidad del catálogo tiene un tipo fijo que se
    // define al agregarla.
    // ============================================
    const etiquetasTipoUniversidad = {
        PUBLICA: 'PÚBLICA',
        PRIVADA: 'PRIVADA',
        EXTRANJERA: 'EXTRANJERA',
        CONVENIO: 'DE CONVENIO'
    };

    function actualizarTipoUniversidad() {
        const tipo = $('#IdUniversidad').find('option:selected').data('tipo');
        $('#IdUniversidadTipo').text(tipo ? ('TIPO: ' + (etiquetasTipoUniversidad[tipo] || tipo)) : '');
        actualizarEditabilidadMatricula(tipo);
    }

    $('#IdUniversidad').on('change', actualizarTipoUniversidad);

    // El costo de matrícula normalmente es fijo (costo del programa menos el
    // descuento del plan). Si el estudiante egresó de una universidad PRIVADA,
    // EXTRANJERA o DE CONVENIO, se habilita para editarlo a mano, ya que la
    // coordinación suele cobrar un monto distinto en esos casos (solo las
    // públicas mantienen el monto fijo). Sigue recalculándose con la fórmula
    // normal como punto de partida; lo que cambia es que ahora se puede
    // sobrescribir.
    const tiposUniversidadMatriculaEditable = ['PRIVADA', 'EXTRANJERA', 'CONVENIO'];

    function actualizarEditabilidadMatricula(tipoUniversidad) {
        const esEditable = tiposUniversidadMatriculaEditable.includes(tipoUniversidad);
        $('#montoSoloMatricula').prop('readonly', !esEditable);
        $('#avisoMatriculaEditable').toggle(esEditable);
    }

    // Si el estudiante ya está seleccionado (autocompletado) al cargar la pestaña
    // de pago, refleja de una vez si su universidad habilita el monto editable.
    actualizarEditabilidadMatricula($('#IdUniversidad').find('option:selected').data('tipo'));

    // Edición manual del monto de matrícula (solo posible cuando no es readonly,
    // es decir, universidad privada): se refleja de inmediato en el monto a pagar.
    $('#montoSoloMatricula').on('input', function() {
        if ($(this).prop('readonly') || $(this).prop('disabled')) return;
        $('#montoAPagar').val($(this).val());
        actualizarVariacionMatricula();
    });

    $('#btnNuevaUniversidad').on('click', function() {
        $('#formNuevaUniversidad')[0].reset();
        $('#ModalNuevaUniversidad').modal('show');
    });

    $('#formNuevaUniversidad').on('submit', function(e) {
        e.preventDefault();

        const nombre = $('#inputNuevaUniversidad').val().trim();
        const tipo = $('#selectNuevaUniversidadTipo').val();
        if (!nombre || !tipo) {
            return;
        }
        if (seleccionarSiYaExiste('#IdUniversidad', nombre, 'universidad', '#ModalNuevaUniversidad', mismaUniversidad)) {
            actualizarTipoUniversidad();
            return;
        }

        const btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true);

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarUniversidadRapida', nombreUniversidad: nombre, tipoUniversidad: tipo },
            dataType: 'json',
            success: function(resp) {
                btnSubmit.prop('disabled', false);

                if (!resp.success) {
                    swal('Error', resp.mensaje || 'No se pudo agregar la universidad', 'error');
                    return;
                }

                const univ = resp.universidad;
                const yaExiste = $('#IdUniversidad option[value="' + univ.IdUniversidad + '"]').length > 0;
                if (!yaExiste) {
                    $('#IdUniversidad').append(
                        $('<option></option>').attr('value', univ.IdUniversidad).attr('data-tipo', univ.TipoUniversidad).text(univ.NombreUniversidad)
                    );
                }
                $('#IdUniversidad').val(univ.IdUniversidad).trigger('change.select2');
                actualizarTipoUniversidad();

                $('#ModalNuevaUniversidad').modal('hide');
                if (resp.duplicado) {
                    swal('Ya está registrada', 'La universidad ya existe como "' + univ.NombreUniversidad + '": se seleccionó del listado, no se registró de nuevo.', 'warning');
                }
            },
            error: function() {
                btnSubmit.prop('disabled', false);
                swal('Error', 'No se pudo agregar la universidad', 'error');
            }
        });
    });

    // ============================================
    // PASO 2: SELECCIONAR SEDE, GRADO Y PROGRAMA
    // ============================================
    function cargarProgramasPorSedeYGrado() {
        const grado = $('#gradoAcademico').val();
        const sede = $('#sedePrograma').val();

        $('#tablaPrograma').slideUp();
        $('#seccionPago').slideUp();

        if (!grado) {
            $('#programa').empty().append('<option value="">Seleccione el grado primero...</option>').trigger('change.select2');
            return;
        }

        $.ajax({
            url: 'ajax/programa.ajax.php',
            type: 'POST',
            data: { grado: grado, sede: sede },
            dataType: 'json',
            success: function(response) {
                $('#programa').empty().append('<option value=""></option>');

                // Ya vienen el último registrado primero. Los datos extra (código, versión,
                // inicio, estado) sirven para distinguir programas con el mismo nombre.
                response.forEach(function(p) {
                    $('<option></option>')
                        .val(p.ProgramaID)
                        .text(p.NombrePrograma)
                        .attr('data-detalle', [p.Codigo, p.Version, p.Sede, 'Inicio ' + mesAnio(p.FechaInicio)].filter(Boolean).join(' · '))
                        .attr('data-estado', p.EstadoClave || '')
                        .attr('data-estado-texto', p.EstadoEtiqueta || '')
                        .appendTo('#programa');
                });
                $('#programa').trigger('change.select2');
            },
            error: function() {
                swal("Error", "No se pudieron obtener los programas", "error");
            }
        });
    }

    $('#gradoAcademico').on('change', cargarProgramasPorSedeYGrado);
    $('#sedePrograma').on('change', cargarProgramasPorSedeYGrado);

    // "2024-03-01" => "03/2024"
    function mesAnio(fecha) {
        return fecha ? fecha.substring(5, 7) + '/' + fecha.substring(0, 4) : '';
    }

    // Paso 2 con buscador. En la lista, cada programa muestra debajo su código,
    // versión, sede, inicio y estado; ya elegido, solo el nombre y su versión/inicio.
    function opcionPrograma(opcion) {
        if (!opcion.id) return opcion.text;
        const $o = $(opcion.element);
        return $('<div class="opcion-programa"></div>')
            .append($('<div class="opcion-programa-nombre"></div>').text(opcion.text))
            .append($('<div class="opcion-programa-detalle"></div>').text($o.data('detalle') || '')
                .append($o.data('estado-texto')
                    ? $('<span class="badge-estado-programa"></span>').addClass('estado-' + $o.data('estado')).text($o.data('estado-texto'))
                    : ''));
    }
    function programaElegido(opcion) {
        if (!opcion.id) return opcion.text;
        const $o = $(opcion.element);
        const detalle = String($o.data('detalle') || '').split(' · ');
        const texto = opcion.text + (detalle.length > 1 ? '  (' + detalle[1] + ' · ' + detalle[detalle.length - 1] + ')' : '');
        // Ya elegido, solo se marca si está concluido (en rojo)
        return $o.data('estado') === 'concluido'
            ? $('<span></span>').text(texto).append($('<span class="badge-estado-programa estado-concluido"></span>').text($o.data('estado-texto')))
            : texto;
    }
    $('#sedePrograma, #gradoAcademico').select2({ width: '100%', dropdownParent: $('#modalPasoPrograma') });
    $('#programa').select2({
        width: '100%',
        dropdownParent: $('#modalPasoPrograma'),
        templateResult: opcionPrograma,
        templateSelection: programaElegido,
        placeholder: 'Seleccione el programa...',
        language: { noResults: function() { return 'No hay programas activos con ese grado/sede'; } }
    });

    // ============================================
    // PASO 3: MONTO DE MATRÍCULA
    // El backend (RegistrarPreregistroModelo) exige que este monto ya refleje el
    // % de descuento de la matrícula (en todos los planes, aparte del % del programa).
    // En cualquier plan, además, se puede exonerar la matrícula (checkbox
    // "Cobrar matrícula"), dejando el monto en 0.
    // ============================================
    function porcentajeValido(selector) {
        const valor = parseFloat($(selector).val()) || 0;
        return Math.min(100, Math.max(0, valor));
    }

    // Total del programa a pagar (lo que pactará el plan de cuotas): el costo con su
    // % de descuento o de aumento (hasta 100%) o, si se escribió a mano en Bs., ese
    // monto; entonces el % y "Descuento/Aumento" se llenan solos con el equivalente
    // (el servidor guarda el monto exacto)
    const MAX_AUMENTO_PROGRAMA = 100;
    let totalProgramaManual = false;

    function esAumentoPrograma() {
        return $('#tipoAjustePrograma').val() === 'AUMENTO';
    }

    function recalcularMontoPrograma() {
        const costoPrograma = parseFloat($('#costoTotalPrograma').val()) || 0;
        $('#paso3CostoPrograma').text(formatoBs(costoPrograma));
        $('#montoProgramaConDescuento').attr('max', (costoPrograma * (1 + MAX_AUMENTO_PROGRAMA / 100)).toFixed(2));
        if (!totalProgramaManual) {
            const porcentaje = porcentajeValido('#porcentajeDescuentoPrograma') * (esAumentoPrograma() ? 1 : -1);
            const monto = Math.round((costoPrograma + (costoPrograma * porcentaje / 100)) * 100) / 100;
            $('#montoProgramaConDescuento').val(monto.toFixed(2));
        }
        actualizarTextoDescuentoPrograma();
        recalcularCuota();
    }

    function formatoPorcentaje(valor) {
        return valor.toLocaleString('es-BO', { maximumFractionDigits: 2 }) + '%';
    }

    // Cuánto sube o baja un monto frente a su base: "aumento de 20% (+ Bs. 300,00)"
    // o "descuento de 10% (− Bs. 150,00)"; null si son iguales
    function variacion(base, valor) {
        const diferencia = Math.round((valor - base) * 100) / 100;
        if (base <= 0 || Math.abs(diferencia) < 0.005) return null;
        const porcentaje = Math.round(Math.abs(diferencia) * 10000 / base) / 100;
        return {
            sube: diferencia > 0,
            texto: (diferencia > 0 ? 'aumento de ' : 'descuento de ') + formatoPorcentaje(porcentaje) +
                ' (' + (diferencia > 0 ? '+ ' : '− ') + formatoBs(Math.abs(diferencia)) + ')'
        };
    }

    // Matrícula a pagar frente al costo de matrícula (con % de descuento o escrita a
    // mano en universidad privada, extranjera o de convenio)
    function actualizarVariacionMatricula() {
        const $texto = $('#textoVariacionMatricula');
        const v = $('#pagarMatricula').is(':checked')
            ? variacion(parseFloat($('#costoMatriculaPrograma').val()) || 0, parseFloat($('#montoSoloMatricula').val()) || 0)
            : null;
        if (!v) { $texto.hide(); return; }
        $texto.toggleClass('text-warning', v.sube).toggleClass('text-muted', !v.sube).show()
            .html('<i class="fa ' + (v.sube ? 'fa-arrow-up' : 'fa-arrow-down') + '"></i> Matrícula con ' + v.texto + ' sobre su costo.');
    }

    // Total del programa frente a su costo: v = descuento o aumento en % y Bs. (cuando
    // el total se escribe a mano, el % es el equivalente redondeado)
    function descuentoPrograma() {
        const costo = parseFloat($('#costoTotalPrograma').val()) || 0;
        const total = parseFloat($('#montoProgramaConDescuento').val()) || 0;
        const diferencia = Math.round((total - costo) * 100) / 100;
        return {
            costo: costo, total: total, diferencia: diferencia,
            porcentaje: costo > 0 ? Math.round(Math.abs(diferencia) * 10000 / costo) / 100 : 0,
            v: variacion(costo, total)
        };
    }

    function errorTotalPrograma() {
        const d = descuentoPrograma();
        if (d.total <= 0) return 'El total del programa a pagar debe ser mayor a 0.';
        if (d.costo > 0 && d.diferencia * 100 / d.costo > MAX_AUMENTO_PROGRAMA + 0.005) {
            return 'El aumento del programa no puede pasar del ' + MAX_AUMENTO_PROGRAMA + '% (hasta ' + formatoBs(d.costo * (1 + MAX_AUMENTO_PROGRAMA / 100)) + ').';
        }
        return '';
    }

    function actualizarTextoDescuentoPrograma() {
        const d = descuentoPrograma();
        const error = errorTotalPrograma();
        let html;
        if (error) html = error;
        else if (!d.v) html = 'Sin descuento ni aumento. Ponga el % o escriba directamente el total a pagar.';
        else html = '<i class="fa ' + (d.v.sube ? 'fa-arrow-up' : 'fa-arrow-down') + '"></i> ' +
            (totalProgramaManual ? 'Total escrito a mano: ' : 'Programa con ') + d.v.texto + ' sobre su costo' +
            (d.v.sube ? ': las cuotas suben.' : '.');
        $('#textoDescuentoPrograma')
            .toggleClass('text-danger', !!error)
            .toggleClass('text-warning', !error && !!d.v && d.v.sube)
            .toggleClass('text-muted', !error && !(d.v && d.v.sube))
            .html(html);
    }

    // N° de cuotas del programa: en todos los planes; Al Contado, máximo 3.
    // Por defecto, una cuota por módulo (Regular/Grupal) o una sola (Contado).
    const MAX_CUOTAS_CONTADO = 3;
    let modulosPrograma = 0;

    function maxCuotasPlan() {
        return $('#tipoPagoCodigo').val() === 'CONTADO' ? MAX_CUOTAS_CONTADO : 60;
    }

    function cuotasPorDefecto() {
        return $('#tipoPagoCodigo').val() === 'CONTADO' ? 1 : Math.max(1, Math.min(60, modulosPrograma || 1));
    }

    function numeroCuotasValido() {
        const n = parseInt($('#numeroCuotas').val(), 10);
        return (n >= 1 && n <= maxCuotasPlan()) ? n : 0;
    }

    // Monto de cada cuota por defecto: el total entre el N° de cuotas; si no sale
    // exacto, redondeado hacia arriba a decenas (30000 / 11 = 2727,27 -> 2730) y la
    // última cuota ajusta (30000 - 10 x 2730 = 2700). Si así la última quedara en 0
    // o menos, se redondea a bolivianos y luego a centavos. Misma regla que
    // PagoProgramaModelos::CuotaRedondeada (planpago.modelo.php).
    function cuotaRedondeada(total, n) {
        total = Math.round(total * 100) / 100;
        if (n <= 1 || total <= 0) return total;
        const base = total / n;
        if (Math.abs(base - Math.round(base)) < 0.005) return Math.round(base * 100) / 100;
        for (const multiplo of [10, 1]) {
            const cuota = Math.ceil(Math.round(base * 1e6) / 1e6 / multiplo) * multiplo;
            if (Math.round((total - cuota * (n - 1)) * 100) / 100 > 0) return cuota;
        }
        return Math.round(base * 100) / 100;
    }

    function formatoBs(valor) {
        return 'Bs. ' + valor.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Cuotas del plan: n, monto de cada una y la última (que ajusta la diferencia)
    function detalleCuotas() {
        const n = numeroCuotasValido();
        const total = parseFloat($('#montoProgramaConDescuento').val()) || 0;
        const cuota = Math.round((parseFloat($('#montoCuota').val()) || 0) * 100) / 100;
        const ultima = n ? Math.round((total - cuota * (n - 1)) * 100) / 100 : 0;
        return { n: n, total: total, cuota: cuota, ultima: ultima };
    }

    // Mismas reglas que PagoProgramaModelos::ValidarMontoCuota
    function errorMontoCuota() {
        const d = detalleCuotas();
        if (!d.n) return '';
        if (d.cuota <= 0) return 'El monto de la cuota debe ser mayor a 0.';
        if (d.n === 1) return Math.abs(d.cuota - d.total) < 0.005 ? '' : 'Con una sola cuota, la cuota debe ser el total del programa (' + formatoBs(d.total) + ').';
        if (d.ultima <= 0) return 'Con ' + d.n + ' cuotas de ' + formatoBs(d.cuota) + ' se pasa del total del programa (' + formatoBs(d.total) + ').';
        return '';
    }

    // Sin "Modificar cuota", el monto se recalcula solo al cambiar el total o el N° de cuotas
    function recalcularCuota() {
        if (!$('#modificarCuota').is(':checked')) {
            const n = numeroCuotasValido();
            const total = parseFloat($('#montoProgramaConDescuento').val()) || 0;
            $('#montoCuota').val(n ? cuotaRedondeada(total, n).toFixed(2) : '');
        }
        actualizarTextoCuotas();
    }

    // "10 cuotas de Bs. 2.730,00 y la última de Bs. 2.700,00"
    function textoCuotas(d) {
        if (d.n === 1) return '1 cuota de ' + formatoBs(d.cuota);
        if (Math.abs(d.ultima - d.cuota) < 0.005) return d.n + ' cuotas de ' + formatoBs(d.cuota);
        return (d.n - 1) + (d.n - 1 === 1 ? ' cuota' : ' cuotas') + ' de ' + formatoBs(d.cuota) + ' y la última de ' + formatoBs(d.ultima);
    }

    function actualizarTextoCuotas() {
        const d = detalleCuotas();
        const error = errorMontoCuota();
        $('#textoCuotasPrograma').toggleClass('text-danger', !!error).toggleClass('text-muted', !error).text(
            !d.n ? 'Indique en cuántas cuotas (de 1 a ' + maxCuotasPlan() + ') se paga el programa.'
                : error || 'Se paga en ' + textoCuotas(d) + ', después de la matrícula.');

        // Cuota modificada a mano: cuánto sube o baja frente a la calculada
        const vCuota = variacionCuota(d);
        $('#textoVariacionCuota').toggle(!!vCuota)
            .toggleClass('text-warning', !!vCuota && vCuota.sube).toggleClass('text-muted', !!vCuota && !vCuota.sube)
            .html(vCuota ? '<i class="fa ' + (vCuota.sube ? 'fa-arrow-up' : 'fa-arrow-down') + '"></i> Cuota modificada: ' + vCuota.texto +
                ' frente a la calculada (' + formatoBs(vCuota.base) + ').' : '');

        // Cuotas frente a módulos, para decidir si cada cuota cubre un módulo
        $('#modulosProgramaPaso3').text(modulosPrograma ? modulosPrograma + (modulosPrograma === 1 ? ' módulo' : ' módulos') : '-');
        const $aviso = $('#avisoModulosCuotas');
        if (!modulosPrograma || !d.n) {
            $aviso.hide();
        } else if (d.n === modulosPrograma) {
            $aviso.removeClass('text-warning').addClass('text-success').show()
                .html('<i class="fa fa-check-circle"></i> Una cuota por módulo: cada cuota cubre un módulo del programa.');
        } else {
            $aviso.removeClass('text-success').addClass('text-warning').show()
                .html('<i class="fa fa-exclamation-triangle"></i> El programa tiene ' + modulosPrograma + (modulosPrograma === 1 ? ' módulo' : ' módulos') +
                    ' y se eligieron ' + d.n + (d.n === 1 ? ' cuota' : ' cuotas') + ': los pagos no coinciden uno a uno con los módulos.');
        }
    }

    // Al cambiar de programa o de plan, el N° de cuotas vuelve al de por defecto y
    // la cuota al redondeado (se quita "Modificar cuota")
    // Cuota escrita a mano frente a la calculada (redondeada); null si no se modificó
    function variacionCuota(d) {
        if (!$('#modificarCuota').is(':checked') || !d.n) return null;
        const base = cuotaRedondeada(d.total, d.n);
        const v = variacion(base, d.cuota);
        return v ? Object.assign(v, { base: base }) : null;
    }

    function reiniciarNumeroCuotas() {
        $('#numeroCuotas').attr('max', maxCuotasPlan()).val(cuotasPorDefecto());
        $('#modificarCuota').prop('checked', false);
        $('#montoCuota').prop('readonly', true);
        recalcularCuota();
    }

    function recalcularMontoMatricula() {
        recalcularMontoPrograma();
        const tipo = $('#tipoPagoCodigo').val();
        const costoBase = parseFloat($('#costoMatriculaPrograma').val()) || 0;
        $('#paso3CostoMatricula').text(formatoBs(costoBase));
        const matriculaExonerada = !$('#pagarMatricula').is(':checked');

        let montoFinal;
        if (matriculaExonerada) {
            montoFinal = 0;
        } else {
            const porcentaje = parseFloat($('#porcentajeDescuento').val()) || 0;
            montoFinal = Math.round((costoBase - (costoBase * porcentaje / 100)) * 100) / 100;
        }

        $('#montoSoloMatricula').val(montoFinal.toFixed(2));
        $('#montoAPagar').val(montoFinal.toFixed(2));
        $('#textoMatriculaExonerada').toggle(matriculaExonerada);
        $('#porcentajeDescuento').prop('disabled', matriculaExonerada);
        // Exonerada: se deshabilita aunque sea de universidad privada, para que no
        // quede un monto manual "fantasma" que no se vaya a cobrar en realidad.
        $('#montoSoloMatricula').prop('disabled', matriculaExonerada);
        actualizarVariacionMatricula();
    }

    // ============================================
    // PLAN DE PAGO DEL PROGRAMA (Regular/Contado/Grupal): elegido aquí, queda definitivo
    // al validar el voucher de matrícula (también Grupal: el grupo se elige aquí)
    // ============================================
    $('#tipoPagoCodigo').on('change', function() {
        // Los dos descuentos y "Cobrar matrícula" están en todos los planes y se
        // conservan al cambiar de plan. El N° de cuotas vuelve al de este plan.
        recalcularMontoMatricula();
        reiniciarNumeroCuotas();
        const esGrupal = $(this).val() === 'GRUPAL';
        $('#grupoCodigoGrupoPreregistro').toggle(esGrupal);
        if (esGrupal) {
            cargarGruposPrograma($('#programa').val());
        }
    });

    // Grupos ya armados del programa elegido; "NUEVO GRUPO" (vacío) crea uno con este estudiante
    function cargarGruposPrograma(programaId) {
        const $select = $('#codigoGrupo');
        $select.html('<option value="">NUEVO GRUPO</option>');
        if (!programaId) return;
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarGruposPrograma', programaID: programaId },
            dataType: 'json',
            success: function(resp) {
                (resp.grupos || []).forEach(function(g) {
                    $select.append($('<option>').val(g.CodigoGrupo)
                        .text(g.CodigoGrupo + ' (' + g.Cantidad + '): ' + g.Integrantes));
                });
            }
        });
    }

    // El select ya viene con "Plan Regular" seleccionado por defecto (primera opción), así
    // que el evento "change" nunca se dispara solo con la carga de la página; se fuerza aquí
    // para que los campos de descuento/matrícula del plan por defecto se muestren de una vez.
    $('#tipoPagoCodigo').trigger('change');

    $('#porcentajeDescuento').on('input', recalcularMontoMatricula);
    $('#porcentajeDescuentoPrograma').on('input', function() {
        totalProgramaManual = false;
        recalcularMontoPrograma();
    });
    $('#tipoAjustePrograma').on('change', function() {
        totalProgramaManual = false;
        recalcularMontoPrograma();
    });
    // Total escrito a mano: el % y "Descuento/Aumento" pasan a ser el equivalente
    $('#montoProgramaConDescuento').on('input', function() {
        totalProgramaManual = true;
        const d = descuentoPrograma();
        $('#tipoAjustePrograma').val(d.diferencia > 0 ? 'AUMENTO' : 'DESCUENTO');
        $('#porcentajeDescuentoPrograma').val(Math.min(100, d.porcentaje));
        actualizarTextoDescuentoPrograma();
        recalcularCuota();
    });
    $('#numeroCuotas').on('input', recalcularCuota);
    $('#montoCuota').on('input', actualizarTextoCuotas);
    // El monto de la cuota solo se escribe a mano con la casilla marcada; al
    // desmarcarla vuelve al redondeado por defecto
    $('#modificarCuota').on('change', function() {
        $('#montoCuota').prop('readonly', !this.checked);
        if (this.checked) $('#montoCuota').trigger('focus').trigger('select');
        recalcularCuota();
    });

    $('#pagarMatricula').on('change', recalcularMontoMatricula);

    $('#programa').on('change', function() {
        const programaId = $(this).val();

        if (programaId) {
            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { idprograma: programaId },
                dataType: 'json',
                success: function(respuesta) {
                    // Detalle: solo sede, módulos y costos
                    $('#proModulos').text(respuesta.Modulos);
                    $('#proSede').text(respuesta.Sede);

                    const costoMatricula = parseFloat(respuesta.CostoMatricula) || 0;
                    const costoPrograma = parseFloat(respuesta.Costo) || 0;

                    $('#proMatricula').text('Bs. ' + costoMatricula.toFixed(2));
                    $('#proPrograma').text('Bs. ' + costoPrograma.toFixed(2));

                    // Guardar en campos hidden
                    $('#costoMatriculaPrograma').val(costoMatricula.toFixed(2));
                    $('#costoTotalPrograma').val(costoPrograma.toFixed(2));
                    modulosPrograma = parseInt(respuesta.Modulos, 10) || 0;
                    totalProgramaManual = false;

                    // Mostrar tabla y siguiente sección
                    $('#tablaPrograma').slideDown();
                    $('#seccionPago').slideDown();

                    // El pre-registro SIEMPRE es orden de pago de matrícula (nunca programa completo)
                    recalcularMontoMatricula();
                    reiniciarNumeroCuotas();

                    // Cargar las cuentas de MATRÍCULA de este programa en el select
                    cargarCuentasPrograma(programaId);

                    // Los grupos son por programa: al cambiar de programa se recargan
                    if ($('#tipoPagoCodigo').val() === 'GRUPAL') {
                        cargarGruposPrograma(programaId);
                    }
                },
                error: function() {
                    swal("Error", "No se pudieron obtener los detalles del programa", "error");
                }
            });
        } else {
            $('#tablaPrograma').slideUp();
            $('#seccionPago').slideUp();
        }
    });

    function cargarCuentasPrograma(programaId) {
        $('#cuentaPago').html('<option value="">Cargando cuentas...</option>');
        $.ajax({
            url: 'ajax/programacuenta.ajax.php',
            type: 'POST',
            data: { accion: 'listarOptionsHtml', ProgramaID: programaId, TipoCuenta: 'MATRICULA' },
            success: function(html) {
                $('#cuentaPago').html(html);
            },
            error: function() {
                $('#cuentaPago').html('<option value="">No se pudieron cargar las cuentas</option>');
            }
        });
    }

    // ============================================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ============================================
    const formPreregistroEl = document.getElementById('formPreregistro');
    // El botón vive en el modal de confirmación (fuera de #formPreregistro en el
    // DOM, aunque asociado a él vía form="formPreregistro"), por eso se busca
    // por su name en toda la página en vez de como descendiente del form.
    const btnRegistrarOrdenPago = document.querySelector('button[name="registrarOrdenPago"]');
    let envioEnCurso = false;

    // Dispara el envío real haciendo click en el botón (así el navegador sí incluye
    // su name="registrarOrdenPago" en el POST; form.submit() por script NO lo haría).
    function enviarFormularioPreregistro() {
        envioEnCurso = true;
        btnRegistrarOrdenPago.click();
    }

    $('#formPreregistro').on('submit', function(e) {

        if (envioEnCurso) {
            // Reenvío nativo ya autorizado tras crear al estudiante (ver más abajo)
            return true;
        }

        e.preventDefault();

        const fechaMatricula = $('input[name="fechaInscripcion"]').val();
        if (!fechaMatricula) {
            swal("Error", "Por favor seleccione la fecha de orden", "error");
            return false;
        }

        const programaID = $('#programa').val();
        if (!programaID) {
            swal("Error", "Por favor seleccione un programa", "error");
            return false;
        }

        if (!$('#cuentaPago').val()) {
            swal("Error", "Por favor seleccione la cuenta a la que el estudiante debe depositar", "error");
            return false;
        }

        if (!numeroCuotasValido()) {
            swal("Error", "El número de cuotas del programa debe estar entre 1 y " + maxCuotasPlan() +
                ($('#tipoPagoCodigo').val() === 'CONTADO' ? " en el Plan al Contado" : ""), "error");
            return false;
        }

        if (errorTotalPrograma()) {
            swal("Error", errorTotalPrograma(), "error");
            return false;
        }

        if (errorMontoCuota()) {
            swal("Error", errorMontoCuota(), "error");
            return false;
        }

        // Validar que el monto de matrícula sea mayor a 0, salvo que se haya marcado
        // explícitamente la matrícula como ya pagada / exonerada
        const montoAPagar = parseFloat($('#montoAPagar').val()) || 0;
        const matriculaExonerada = !$('#pagarMatricula').is(':checked');
        if (montoAPagar <= 0 && !matriculaExonerada) {
            swal("Error", "El monto de matrícula debe ser mayor a 0", "error");
            return false;
        }

        const estudianteID = $('#selectedEstudianteID').val();

        if (estudianteID) {
            // Estudiante ya existente: se envía el formulario tal cual
            enviarFormularioPreregistro();
            return false;
        }

        // Estudiante nuevo: se deben haber completado sus datos en el PASO 1
        if (!formPreregistroEl.checkValidity()) {
            formPreregistroEl.reportValidity();
            return false;
        }

        // Se registra primero al estudiante (vía AJAX) y recién con su ID se envía la orden
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarEstudianteRapido',
                Ci: $('#inputCi').val(),
                Complemento: $('#inputComplemento').val(),
                Exp: $('#selectExpedido').val(),
                Nombre: $('#inputNombres').val(),
                Apaterno: $('#apaterno').val(),
                Amaterno: $('#amaterno').val(),
                FechaNacimiento: $('#fechaNacimiento').val(),
                Edad: $('#Edad').val(),
                Lugarn: $('#Lugarn').val(),
                Correo: $('#emailInput').val(),
                IdProfesion: $('#IdProfesion').val(),
                Trabajo: $('#Trabajo').val(),
                Direccion: $('#direccionInput').val(),
                Telefono: $('#inputTelefono').val(),
                Celular: $('#inputCelular').val(),
                IdUniversidad: $('#IdUniversidad').val(),
                FechaEgreso: $('#FechaEgreso').val()
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#selectedEstudianteID').val(resp.estudianteID);
                    enviarFormularioPreregistro();
                } else {
                    swal("Error", resp.mensaje || "No se pudo registrar al estudiante", "error");
                }
            },
            error: function() {
                swal("Error", "No se pudo registrar al estudiante", "error");
            }
        });

        return false;
    });

    // ============================================
    // RESET FORM
    // ============================================
    $('button[type="reset"]').on('click', function() {
        location.reload();
    });

    // Convertir texto a mayúsculas automáticamente
    $('#inputComplemento, #inputNombres, #apaterno, #amaterno').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Validar solo números en CI, Teléfono y Celular
    $('#inputCi, #inputTelefono, #inputCelular').on('keypress', function(e) {
        if (e.which < 48 || e.which > 57) {
            e.preventDefault();
        }
    });

    // Validar solo letras en nombres y apellidos
    $('#inputNombres, #apaterno, #amaterno').on('keypress', function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[A-Za-zñÑáéíóúÁÉÍÓÚ\s]/.test(char)) {
            e.preventDefault();
        }
    });

    // Validar edad mínima (debe ser mayor de 15 años)
    $('#fechaNacimiento').on('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();

        let edad = today.getFullYear() - birthDate.getFullYear();
        const mes = today.getMonth() - birthDate.getMonth();
        if (mes < 0 || (mes === 0 && today.getDate() < birthDate.getDate())) {
            edad--;
        }

        if (edad < 15) {
            alert('El estudiante debe tener al menos 15 años de edad.');
            this.value = '';
        } else {
            $('#Edad').val(edad);
        }
    });

    // Fecha de egreso: como mínimo a los 15 años de edad (nacimiento + 15 años) y como
    // máximo hoy (atributo max). Se marca inválida con un mensaje propio para que
    // "Siguiente" no deje avanzar; el servidor aplica la misma regla.
    const EDAD_MINIMA_EGRESO = 15;
    function validarFechaEgreso() {
        const $egreso = $('#FechaEgreso');
        const nacimiento = $('#fechaNacimiento').val();
        let mensaje = '';

        if (nacimiento) {
            const [anio, mes, dia] = nacimiento.split('-');
            const minima = (parseInt(anio, 10) + EDAD_MINIMA_EGRESO) + '-' + mes + '-' + dia;
            $egreso.attr('min', minima);
            if ($egreso.val() && $egreso.val() < minima) {
                mensaje = 'La fecha de egreso debe ser al menos ' + EDAD_MINIMA_EGRESO +
                    ' años posterior a la fecha de nacimiento (desde el ' + [dia, mes, parseInt(anio, 10) + EDAD_MINIMA_EGRESO].join('/') + ').';
            }
        } else {
            $egreso.removeAttr('min');
        }
        $egreso[0].setCustomValidity(mensaje);
    }
    $('#fechaNacimiento, #FechaEgreso').on('change', validarFechaEgreso);

    // ============================================
    // TABLA DE PREREGISTROS PENDIENTES (CRUD)
    // ============================================

    function renderTablaPreregistros(lista) {
        if (!lista || lista.length === 0) {
            $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-muted"><i class="flaticon2-information"></i> No hay preregistros que coincidan con la búsqueda</td></tr>');
            return;
        }

        let html = '';
        lista.forEach(function(pr, idx) {
            const nombreCompleto = ((pr.Apaterno || '') + ' ' + (pr.Amaterno || '') + ' ' + (pr.Nombre || '')).trim();
            const fechaCorta = pr.FechaGeneracion ? pr.FechaGeneracion.substring(0, 10).split('-').reverse().join('/') : '';

            html += '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td>' + nombreCompleto + '</td>' +
                '<td>' + (pr.Ci || '') + '</td>' +
                '<td>' + pr.NombrePrograma + ' (' + pr.CodigoPrograma + ')</td>' +
                '<td>' + (pr.Sede || '') + '</td>' +
                '<td>' + fechaCorta + '</td>' +
                '<td class="text-center"><div class="acciones-preregistro">' +
                    '<button type="button" class="btn btn-accion btn-info btn-ver-detalle-preregistro" data-id="' + pr.IdOrdenPago + '" title="Ver Detalle"><i class="fa fa-eye"></i></button>' +
                    '<button type="button" class="btn btn-accion-texto btn-success btn-generar-orden" data-id="' + pr.IdOrdenPago + '" title="Generar Orden de Pago"><i class="fa fa-file-invoice"></i> Generar Orden</button>' +
                    '<a href="inscripcion?orden=' + pr.IdOrdenPago + '" class="btn btn-accion-texto btn-primary btn-validar-voucher" title="Validar el voucher en Inscripción"><i class="fa fa-check-circle"></i> Validar</a>' +
                    '<button type="button" class="btn btn-accion btn-warning btn-editar-preregistro" data-id="' + pr.IdOrdenPago + '" data-pagocompleto="' + pr.pagoCompleto + '" title="Editar"><i class="fa fa-edit"></i></button>' +
                    '<button type="button" class="btn btn-accion btn-danger btn-anular-preregistro" data-id="' + pr.IdOrdenPago + '" title="Anular"><i class="fa fa-trash"></i></button>' +
                '</div></td>' +
            '</tr>';
        });

        $('#tablaPreregistrosBody').html(html);
    }

    // Dataset completo en memoria (se filtra en el cliente). Se precarga desde el
    // servidor para que los filtros funcionen de una vez, sin esperar un refresh.
    let preregistrosData = [];
    try {
        const datosIniciales = document.getElementById('datosPreregistrosIniciales');
        preregistrosData = datosIniciales ? (JSON.parse(datosIniciales.textContent) || []) : [];
    } catch (e) {
        preregistrosData = [];
    }

    function poblarFiltroProgramas() {
        const seleccionActual = $('#filtroPreregistroPrograma').val();
        const vistos = {};
        let opciones = '<option value="">Todos los programas</option>';

        preregistrosData.forEach(function(pr) {
            const clave = pr.ProgramaID || (pr.NombrePrograma + '|' + pr.CodigoPrograma);
            if (vistos[clave]) return;
            vistos[clave] = true;
            const etiqueta = (pr.NombrePrograma || '') + ' (' + (pr.CodigoPrograma || '') + ')';
            opciones += '<option value="' + clave + '">' + etiqueta + '</option>';
        });

        // trigger('change') es necesario para que Select2 actualice el texto
        // visible (un .val() plano no repinta el widget).
        $('#filtroPreregistroPrograma').html(opciones).val(seleccionActual || '').trigger('change');
    }

    function quitarAcentosPreregistro(texto) {
        return (texto || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    function aplicarFiltrosPreregistro() {
        const ci = quitarAcentosPreregistro($('#filtroPreregistroCi').val().trim());
        const programa = $('#filtroPreregistroPrograma').val();
        const sede = $('#filtroPreregistroSede').val();

        const filtrada = preregistrosData.filter(function(pr) {
            if (ci && quitarAcentosPreregistro(pr.Ci).indexOf(ci) === -1) return false;
            if (sede && (pr.Sede || '') !== sede) return false;
            if (programa) {
                const clave = pr.ProgramaID || (pr.NombrePrograma + '|' + pr.CodigoPrograma);
                if (String(clave) !== String(programa)) return false;
            }
            return true;
        });

        renderTablaPreregistros(filtrada);
        $('#contadorPreregistros').text(filtrada.length + ' de ' + preregistrosData.length + ' preregistro(s)');
    }

    $('#filtroPreregistroCi').on('input', aplicarFiltrosPreregistro);
    $('#filtroPreregistroPrograma, #filtroPreregistroSede').on('change', aplicarFiltrosPreregistro);
    $('#btnLimpiarFiltrosPreregistro').on('click', function() {
        $('#filtroPreregistroCi').val('');
        $('#filtroPreregistroPrograma').val('').trigger('change');
        $('#filtroPreregistroSede').val('').trigger('change');
        aplicarFiltrosPreregistro();
    });

    poblarFiltroProgramas();
    $('.select2-uniforme').select2({ width: '100%' });
    aplicarFiltrosPreregistro();

    function cargarPreregistros() {
        $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarPreregistros' },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    preregistrosData = resp.preregistros || [];
                    poblarFiltroProgramas();
                    aplicarFiltrosPreregistro();
                } else {
                    $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los preregistros</td></tr>');
                }
            },
            error: function() {
                $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los preregistros</td></tr>');
            }
        });
    }

    $('#btnRefrescarPreregistros').on('click', cargarPreregistros);

    // Ver el detalle completo de la solicitud de orden de pago (estudiante, programa,
    // montos, plan propuesto) sin tener que abrir el modal de generar/editar.
    $(document).on('click', '.btn-ver-detalle-preregistro', function() {
        const id = $(this).data('id');

        $('#detallePreregistroContenido').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#ModalDetallePreregistro').modal('show');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerPreregistro', idOrdenPago: id },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    $('#detallePreregistroContenido').html('<div class="alert alert-danger">' + (resp.mensaje || 'No se pudo cargar el detalle') + '</div>');
                    return;
                }

                const p = resp.preregistro;
                const nombreCompleto = ((p.Apaterno || '') + ' ' + (p.Amaterno || '') + ' ' + (p.Nombre || '')).trim();
                const fechaCorta = p.FechaGeneracion ? p.FechaGeneracion.substring(0, 10).split('-').reverse().join('/') : '';

                const esc = function(texto) { return $('<span>').text(texto == null ? '' : String(texto)).html(); };
                const monto = parseFloat(p.MontoFinal || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const nombresPlan = { REGULAR: 'Regular (en cuotas)', CONTADO: 'Al contado', GRUPAL: 'Grupal' };
                const porcentaje = function(valor) { return (parseFloat(valor) || 0).toString().replace(/\.0+$/, '') + '%'; };
                const exonerada = parseFloat(p.MontoFinal || 0) <= 0 && p.PagoCompleto != 1;
                const fila = function(etiqueta, valor) {
                    return '<div class="detalle-sol-item"><span class="detalle-sol-label">' + etiqueta + '</span>' +
                        '<span class="detalle-sol-valor">' + valor + '</span></div>';
                };

                $('#detalleSolicitudOrden').text('Orden N° ' + (p.NumeroOrden || '-'));

                // Lo que hay que depositar, a qué cuenta, y en qué estado está
                const cuenta = p.CuentaNumero
                    ? '<div class="detalle-sol-cuenta"><span>Cuenta</span> <strong>' + esc(p.CuentaNumero) + '</strong>' +
                        (p.CuentaBanco ? ' · ' + esc(p.CuentaBanco) : '') + '</div>'
                    : '<div class="detalle-sol-cuenta text-warning"><i class="fa fa-exclamation-triangle"></i> El programa no tiene cuenta de matrícula (Programas &gt; Cuentas)</div>';
                const htmlMonto = '' +
                    '<div class="detalle-sol-monto">' +
                        '<div>' +
                            '<div class="detalle-sol-label">' + (exonerada ? 'Matrícula' : 'Monto a depositar') + '</div>' +
                            '<div class="detalle-sol-cifra">' + (exonerada ? 'Exonerada' : 'Bs. ' + monto) + '</div>' +
                            cuenta +
                        '</div>' +
                        '<span class="detalle-sol-estado">' + esc(p.Estado || 'PENDIENTE') + '</span>' +
                    '</div>';

                // Ficha: solo lo necesario para identificar la solicitud
                const plan = nombresPlan[p.PlanPropuestoTipo] || '-';
                // % del programa negativo = aumento
                const porcentajePrograma = parseFloat(p.PlanPropuestoPorcentaje) || 0;
                const descuentos = 'Matrícula ' + porcentaje(p.PorcentajeDescuento) + ' · Programa ' +
                    (porcentajePrograma < 0 ? 'aumento de ' + porcentaje(-porcentajePrograma) : porcentaje(porcentajePrograma));
                const htmlFicha = '' +
                    '<div class="detalle-sol-grid">' +
                        fila('Estudiante', '<strong>' + esc(nombreCompleto) + '</strong><br><small>C.I. ' + esc(p.CiCompleto) + '</small>') +
                        fila('Contacto', esc(p.Celular || '-') + '<br><small>' + esc(p.Correo || '-') + '</small>') +
                        fila('Programa', '<strong>' + esc(p.NombrePrograma) + '</strong><br><small>' + esc(p.CodigoPrograma) + ' · ' + esc(p.Sede || '-') + '</small>') +
                        fila('Plan de pago', esc(plan) + (p.PlanPropuestoCodigoGrupo ? ' · ' + esc(p.PlanPropuestoCodigoGrupo) : '') +
                            (p.PlanPropuestoNumeroCuotas ? ' · ' + esc(p.PlanPropuestoNumeroCuotas) + (p.PlanPropuestoNumeroCuotas == 1 ? ' cuota' : ' cuotas') : '') +
                            '<br><small>' + descuentos + '</small>') +
                        fila('Fecha de solicitud', esc(fechaCorta)) +
                    '</div>';

                $('#detallePreregistroContenido').html(htmlMonto + htmlFicha);
            },
            error: function() {
                $('#detallePreregistroContenido').html('<div class="alert alert-danger">No se pudo cargar el detalle</div>');
            }
        });
    });

    // Abrir modal para generar la orden de pago (pide datos de facturación; el resto
    // de los datos necesarios para el PDF se traen y se guardan en campos ocultos).
    // Se reutiliza tanto para el clic manual del botón "Generar Orden" como para el
    // auto-abrir justo después de registrar un preregistro (ver ?ordenGenerada= abajo).
    function abrirModalGenerarOrden(id) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerPreregistro', idOrdenPago: id },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    const p = resp.preregistro;
                    $('#generarIdOrdenPago').val(p.IdOrdenPago);
                    $('#generarNombreFactura').val(p.NombreFactura || (p.Apaterno + ' ' + p.Amaterno + ' ' + p.Nombre).trim());
                    $('#generarNitCiFactura').val(p.NitCiFactura || p.CiCompleto);
                    cargarOpcionesFirmantePreregistro(p.Firma || '');

                    $('#generarApaterno').val(p.Apaterno || '');
                    $('#generarAmaterno').val(p.Amaterno || '');
                    $('#generarNombres').val(p.Nombre || '');
                    $('#generarCorreo').val(p.Correo || '');
                    $('#generarCi').val(p.CiCompleto || '');
                    $('#generarCelular').val(p.Celular || '');
                    $('#generarPrograma').val(p.ProgramaTexto || '');
                    $('#generarMontoNumeral').val(parseFloat(p.MontoFinal).toFixed(2));
                    $('#generarMontoLiteral').val(p.MontoLiteral || '');
                    $('#generarVersion').val(p.Version || '');
                    $('#generarNumeroTramite').val(p.NumeroTramite || '');

                    $('#ModalGenerarOrdenPago').modal('show');
                } else {
                    swal('Error', resp.mensaje || 'No se pudo cargar el preregistro', 'error');
                }
            },
            error: function() {
                swal('Error', 'No se pudo cargar el preregistro', 'error');
            }
        });
    }

    $(document).on('click', '.btn-generar-orden', function() {
        abrirModalGenerarOrden($(this).data('id'));
    });

    // Responsable de la Firma: catálogo de nombres (independiente del usuario que
    // generó la orden en el sistema, que se captura solo del lado del servidor).
    function cargarOpcionesFirmantePreregistro(seleccionar) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarFirmantes' },
            dataType: 'json',
            success: function(resp) {
                const firmantes = (resp && resp.success) ? resp.firmantes : [];
                let opciones = '<option value="">-- Seleccione --</option>';
                firmantes.forEach(function(f) {
                    opciones += '<option value="' + f.Nombre + '">' + f.Nombre + '</option>';
                });
                $('#generarFirma').html(opciones);
                if (seleccionar) {
                    $('#generarFirma').val(seleccionar);
                }
            },
            error: function() {
                $('#generarFirma').html('<option value="">No se pudieron cargar los nombres</option>');
            }
        });
    }

    $(document).on('click', '#btnAgregarFirmantePreregistro', function() {
        $('#nuevoFirmanteBoxPreregistro').show();
        $('#nuevoFirmanteNombrePreregistro').val('').focus();
    });

    $(document).on('click', '#btnCancelarFirmantePreregistro', function() {
        $('#nuevoFirmanteBoxPreregistro').hide();
        $('#nuevoFirmanteNombrePreregistro').val('');
    });

    $(document).on('keydown', '#nuevoFirmanteNombrePreregistro', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#btnGuardarFirmantePreregistro').click();
        } else if (e.key === 'Escape') {
            $('#btnCancelarFirmantePreregistro').click();
        }
    });

    $(document).on('click', '#btnGuardarFirmantePreregistro', function() {
        const nombre = ($('#nuevoFirmanteNombrePreregistro').val() || '').trim();
        if (!nombre) {
            $('#nuevoFirmanteNombrePreregistro').focus();
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarFirmante', nombre: nombre },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false);
                if (resp.success) {
                    cargarOpcionesFirmantePreregistro(resp.nombre);
                    $('#nuevoFirmanteBoxPreregistro').hide();
                    $('#nuevoFirmanteNombrePreregistro').val('');
                } else {
                    swal('Error', resp.mensaje || 'No se pudo agregar el nombre', 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                swal('Error', 'No se pudo agregar el nombre', 'error');
            }
        });
    });

    // Justo después de registrar un preregistro, el controlador redirige de vuelta
    // a esta misma vista con ?ordenGenerada=<id> (en vez de a una pantalla aparte):
    // se abre directo el modal para generar el PDF de la orden recién creada.
    (function abrirOrdenRecienGenerada() {
        const params = new URLSearchParams(window.location.search);
        const idGenerado = params.get('ordenGenerada');
        if (idGenerado) {
            abrirModalGenerarOrden(idGenerado);
            params.delete('ordenGenerada');
            const queryRestante = params.toString();
            const nuevaUrl = window.location.pathname + (queryRestante ? '?' + queryRestante : '');
            window.history.replaceState({}, document.title, nuevaUrl);
        }
    })();

    // Al enviar: guarda los datos de facturación por AJAX (en paralelo) y deja que el
    // formulario haga su envío normal hacia el generador de PDF (target="_blank"),
    // igual que en orden-generada.php, para que el navegador no bloquee la apertura.
    $('#formGenerarOrdenPago').on('submit', function() {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'actualizarFacturacion',
                idOrdenPago: $('#generarIdOrdenPago').val(),
                nombreFactura: $('#generarNombreFactura').val(),
                nitCiFactura: $('#generarNitCiFactura').val(),
                firma: $('#generarFirma').val()
            }
        }).catch(function() { /* no bloquea la generación del PDF si falla */ });

        $('#ModalGenerarOrdenPago').modal('hide');
        // No se hace preventDefault: el formulario sigue su envío normal hacia el PDF
    });

    // Abrir modal de edición precargado con los datos del preregistro
    $(document).on('click', '.btn-editar-preregistro', function() {
        const id = $(this).data('id');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerPreregistro', idOrdenPago: id },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    const p = resp.preregistro;
                    $('#editIdOrdenPago').val(p.IdOrdenPago);
                    $('#editPagoCompleto').val(p.PagoCompleto);
                    $('#editMontoPagado').val(parseFloat(p.MontoFinal).toFixed(2));
                    $('#editFechaInscripcion').val(p.FechaGeneracion ? p.FechaGeneracion.substring(0, 10) : '');
                    $('#ModalEditarPreregistro').modal('show');
                } else {
                    swal('Error', resp.mensaje || 'No se pudo cargar el preregistro', 'error');
                }
            },
            error: function() {
                swal('Error', 'No se pudo cargar el preregistro', 'error');
            }
        });
    });

    // Guardar cambios del preregistro (Update)
    $('#formEditarPreregistro').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: $(this).serialize() + '&accion=actualizarPreregistro',
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#ModalEditarPreregistro').modal('hide');
                    swal('Éxito', resp.mensaje, 'success');
                    cargarPreregistros();
                } else {
                    swal('Error', resp.mensaje, 'error');
                }
            },
            error: function() {
                swal('Error', 'No se pudo actualizar el preregistro', 'error');
            }
        });
    });

    // Anular preregistro (Delete lógico)
    $(document).on('click', '.btn-anular-preregistro', function() {
        const id = $(this).data('id');

        swal({
            title: '¿Anular este preregistro?',
            text: 'El preregistro quedará como ANULADO. Esta acción no se puede deshacer desde esta pantalla.',
            icon: 'warning',
            buttons: ['Cancelar', 'Sí, anular'],
            dangerMode: true
        }).then((confirmado) => {
            if (confirmado) {
                $.ajax({
                    url: 'ajax/ordenpago.ajax.php',
                    type: 'POST',
                    data: { accion: 'anularPreregistro', idOrdenPago: id },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.success) {
                            swal('Anulado', resp.mensaje, 'success');
                            cargarPreregistros();
                        } else {
                            swal('Error', resp.mensaje, 'error');
                        }
                    },
                    error: function() {
                        swal('Error', 'No se pudo anular el preregistro', 'error');
                    }
                });
            }
        });
    });

    // ============================================
    // ASISTENTE POR PASOS (solo navegación entre modales y armado del resumen
    // final; no reemplaza ni duplica las validaciones/envío ya existentes,
    // que siguen corriendo tal cual al enviar el formulario en el PASO 4).
    // ============================================

    // Pasa de un modal al siguiente evitando el problema de doble backdrop de
    // Bootstrap 4 al encadenar modales (se espera a que el actual termine de
    // ocultarse antes de mostrar el próximo).
    function irAlSiguienteModal(idModalActual, idModalSiguiente) {
        $('#' + idModalActual).one('hidden.bs.modal', function() {
            $('#' + idModalSiguiente).modal('show');
        });
        $('#' + idModalActual).modal('hide');
    }

    // Validación puramente mecánica: usa los mismos required/pattern ya
    // declarados en los campos (constraint validation nativa del navegador),
    // solo para no dejar avanzar el asistente con un paso a medio llenar. No
    // agrega ninguna regla de negocio nueva.
    function pasoTieneCamposValidos($contenedor) {
        let esValido = true;
        $contenedor.find('input, select, textarea').each(function() {
            if (this.disabled) return;
            if (!this.checkValidity()) {
                this.reportValidity();
                esValido = false;
                return false;
            }
        });
        return esValido;
    }

    $('#btnWizardPaso1Siguiente').on('click', function() {
        if (!pasoTieneCamposValidos($('#modalPasoEstudiante .modal-body'))) return;
        irAlSiguienteModal('modalPasoEstudiante', 'modalPasoPrograma');
    });

    $('#btnWizardPaso2Atras').on('click', function() {
        irAlSiguienteModal('modalPasoPrograma', 'modalPasoEstudiante');
    });

    $('#btnWizardPaso2Siguiente').on('click', function() {
        if (!pasoTieneCamposValidos($('#modalPasoPrograma .modal-body'))) return;
        irAlSiguienteModal('modalPasoPrograma', 'modalPasoPago');
    });

    $('#btnWizardPaso3Atras').on('click', function() {
        irAlSiguienteModal('modalPasoPago', 'modalPasoPrograma');
    });

    // Arma la tabla de revisión del PASO 4 leyendo los valores ya cargados en
    // pantalla (no recalcula ni valida nada de negocio, solo los muestra).
    function construirResumenConfirmacion() {
        const nombreCompleto = ($('#inputNombres').val() + ' ' + $('#apaterno').val() + ' ' + $('#amaterno').val())
            .replace(/\s+/g, ' ').trim();

        $('#resumenEstudianteNombre').text(nombreCompleto || '-');
        $('#resumenEstudianteCi').text($('#inputCi').val() || '-');
        $('#resumenEstudianteCorreo').text($('#emailInput').val() || '-');
        $('#resumenEstudianteCelular').text($('#inputCelular').val() || '-');

        // Nombre y "código · versión · sede · inicio" del programa elegido (paso 2)
        const $programa = $('#programa option:selected');
        const detallePrograma = $programa.val() ? String($programa.data('detalle') || '') : '';
        $('#resumenProgramaNombre').text(($programa.val() ? $programa.text() : '-') + (detallePrograma ? ' (' + detallePrograma.split(' · ')[0] + ')' : ''));
        $('#resumenProgramaGrado').text($('#gradoAcademico option:selected').text() || '-');
        $('#resumenProgramaSede').text($('#proSede').text() || '-');

        $('#resumenPlanPago').text(($('#tipoPagoCodigo option:selected').text() || '-') +
            ($('#tipoPagoCodigo').val() === 'GRUPAL' ? ' · ' + ($('#codigoGrupo').val() || 'NUEVO GRUPO') : ''));
        // Matrícula: costo de lista, descuento (o exonerada) y lo que se cobra en esta orden
        const costoMatricula = parseFloat($('#costoMatriculaPrograma').val()) || 0;
        const montoMatricula = parseFloat($('#montoSoloMatricula').val()) || 0;
        const porcentajeMatricula = porcentajeValido('#porcentajeDescuento');
        $('#resumenCostoMatricula').text(formatoBs(costoMatricula));
        const vMatricula = variacion(costoMatricula, montoMatricula);
        const matriculaAMano = Math.abs(montoMatricula - Math.round((costoMatricula - costoMatricula * porcentajeMatricula / 100) * 100) / 100) > 0.005;
        $('#resumenDescuentoMatricula').text(!$('#pagarMatricula').is(':checked')
            ? 'Exonerada: no se cobra'
            : (vMatricula
                ? vMatricula.texto.charAt(0).toUpperCase() + vMatricula.texto.slice(1) + (matriculaAMano ? ' · monto ajustado a mano' : '')
                : 'Sin descuento'));
        $('#resumenMontoMatricula').text(formatoBs(montoMatricula));

        // Programa: costo de lista, descuento y total que se paga en cuotas
        const descuento = descuentoPrograma();
        $('#resumenCostoPrograma').text(formatoBs(descuento.costo));
        $('#resumenDescuentoPrograma').text(!descuento.v
            ? 'Sin descuento ni aumento'
            : descuento.v.texto.charAt(0).toUpperCase() + descuento.v.texto.slice(1) +
              (totalProgramaManual ? ' · total escrito a mano' : ''));
        $('#resumenMontoPrograma').text(formatoBs(descuento.total));

        // Cuotas: cuántas, de cuánto (la última ajusta) y frente a los módulos
        const cuotas = detalleCuotas();
        $('#resumenModulosPrograma').text(modulosPrograma ? modulosPrograma + (modulosPrograma === 1 ? ' módulo' : ' módulos') : '-');
        $('#resumenCuotasPrograma').text(cuotas.n
            ? textoCuotas(cuotas) + (variacionCuota(cuotas)
                ? ' (cuota modificada a mano: ' + variacionCuota(cuotas).texto + ' frente a ' + formatoBs(variacionCuota(cuotas).base) + ')'
                : '')
            : '-');

        $('#resumenCuentaPago').text($('#cuentaPago option:selected').text() || '-');
        const fechaOrden = String($('input[name="fechaInscripcion"]').val() || '').split('-');
        $('#resumenFechaOrden').text(fechaOrden.length === 3 ? fechaOrden[2] + '/' + fechaOrden[1] + '/' + fechaOrden[0] : '-');
    }

    $('#btnWizardPaso3Revisar').on('click', function() {
        if (!pasoTieneCamposValidos($('#modalPasoPago .modal-body'))) return;
        if (errorTotalPrograma()) {
            swal("Error", errorTotalPrograma(), "error");
            return;
        }
        if (errorMontoCuota()) {
            swal("Error", errorMontoCuota(), "error");
            return;
        }
        construirResumenConfirmacion();
        irAlSiguienteModal('modalPasoPago', 'modalConfirmarPreregistro');
    });

    $('#btnWizardPaso4Atras').on('click', function() {
        irAlSiguienteModal('modalConfirmarPreregistro', 'modalPasoPago');
    });

    console.log('=== PRE-REGISTRO INICIALIZADO CORRECTAMENTE ===');

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarPreregistro);
} else {
    iniciarPreregistro();
}
</script>

<style>
/* Paso 2: Select2 del alto de los campos pequeños, y opciones de programa en dos líneas */
#seccionPrograma .select2-container .select2-selection--single {
    height: 32px !important;
}
#seccionPrograma .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: .875rem;
}
.opcion-programa-nombre {
    font-weight: 600;
}
.opcion-programa-detalle {
    font-size: .8rem;
    opacity: .75;
}
.badge-estado-programa {
    margin-left: .5rem;
    padding: 0 .4rem;
    border-radius: 3px;
    font-size: .7rem;
    font-weight: 600;
    background: #e8f0fe;
    color: #2c5bd8;
}
.badge-estado-programa.estado-en-curso { background: #e6f6ee; color: #1a7f4b; }
/* Concluido en rojo: todavía se puede elegir (para pruebas), pero debe notarse */
.badge-estado-programa.estado-concluido { background: #fde8e8; color: #c62828; }

/* Paso 3: casilla "¿Pagar matrícula?" dentro de un recuadro del alto de un campo */
.casilla-matricula {
    display: flex;
    align-items: center;
}
.casilla-matricula .custom-control {
    margin: 0;
}

/* ---------- Inicio de la página: acción principal "Nuevo Pre-Registro" ---------- */
.pr-inicio {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px 24px;
    background: #fff;
    border: 1px solid #e6e9ef;
    border-left: 4px solid #1e3c72;
    border-radius: 8px;
    padding: 18px 24px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(16, 24, 40, .04);
}
.pr-inicio-titulo { font-size: 17px; font-weight: 700; color: #1e3c72; }
.pr-inicio-texto p { margin: 2px 0 10px; font-size: 13px; color: #6b7280; }
.pr-inicio-pasos {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 18px;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 12px;
    color: #4a5568;
}
.pr-inicio-pasos li { display: inline-flex; align-items: center; gap: 6px; }
.pr-inicio-pasos span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #eef2f8;
    color: #1e3c72;
    font-size: 11px;
    font-weight: 700;
}
.btn.pr-inicio-boton {
    background: #1e3c72 !important;
    border: 1px solid #1e3c72 !important;
    border-bottom: 3px solid #c9a227 !important;
    color: #fff !important;
    font-size: 15px;
    font-weight: 600;
    padding: 12px 26px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(30, 60, 114, .25);
    white-space: nowrap;
}
.btn.pr-inicio-boton:hover { background: #2a5298 !important; border-color: #2a5298 !important; }
.btn.pr-inicio-boton i { color: #fff !important; margin-right: 6px; }
@media (max-width: 575px) {
    .btn.pr-inicio-boton { width: 100%; }
}

/* ---------- Paso 1: ficha del estudiante (estilo formal) ---------- */
.ficha-modal { border: 0; border-radius: 10px; overflow: hidden; }
.ficha-modal .wizard-modal-header {
    background: #1e3c72;
    padding: 16px 24px;
    border-bottom: 3px solid #c9a227;
    border-radius: 0;
}
.ficha-modal .wizard-paso-texto {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: rgba(255, 255, 255, .7);
    margin-bottom: 2px;
}
.ficha-modal .modal-title { font-size: 18px; font-weight: 600; letter-spacing: .2px; }
.ficha-modal .modal-body { padding: 16px 24px 8px; background: #fff; }
.ficha-modal .modal-footer { background: #f8f9fb; border-top: 1px solid #e6e9ef; padding: 12px 24px; }

/* Búsqueda por C.I.: franja propia arriba del formulario */
.ficha-busqueda {
    margin: -16px -24px 6px;
    padding: 14px 24px;
    background: #f5f7fa;
    border-bottom: 1px solid #e6e9ef;
}
.ficha-busqueda-titulo {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #1e3c72;
    margin-bottom: 6px;
}
.ficha-busqueda-campo { width: 280px; max-width: 100%; }
.ficha-busqueda-campo .form-control { background: #fff; border-color: #cfd6e0; }
.ficha-busqueda-campo .btn { background: #1e3c72; border-color: #1e3c72; }
.ficha-enlace { color: #5b6b80; text-decoration: none !important; }
.ficha-enlace:hover { color: #1e3c72; }

/* Secciones con título pequeño en mayúsculas y línea fina */
.ficha-seccion { padding-top: 14px; }
.ficha-seccion-titulo {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #1e3c72;
    padding-bottom: 6px;
    margin-bottom: 10px;
    border-bottom: 1px solid #e6e9ef;
}
.ficha-modal .form-group { margin-bottom: 10px; }
.ficha-modal .modal-body label:not(.custom-control-label) {
    font-size: 12px;
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 3px;
}
.ficha-req { color: #b42318; }
.ficha-modal .modal-body .form-control {
    background-color: #fff;
    border: 1px solid #cfd6e0;
    border-radius: 4px;
    color: #1f2937;
}
.ficha-modal .modal-body .form-control:focus {
    border-color: #1e3c72;
    box-shadow: 0 0 0 2px rgba(30, 60, 114, .12);
}
/* Bloqueado hasta buscar el C.I. (o de solo lectura si el estudiante ya existe) */
.ficha-modal .modal-body .form-control:disabled,
.ficha-modal .modal-body .form-control[readonly] { background-color: #f7f8fa; color: #6b7280; }
.ficha-btn-nueva {
    background: #fff;
    border: 1px solid #cfd6e0;
    border-left: 0;
    color: #1e3c72;
    font-weight: 500;
}
.ficha-btn-nueva:hover:not(:disabled) { background: #1e3c72; border-color: #1e3c72; color: #fff; }
.ficha-btn-nueva:disabled { color: #9aa5b5; background: #f7f8fa; }
/* Pasos 2 a 4 con el mismo estilo: subsecciones sin caja, títulos como en el paso 1 */
.ficha-modal .orden-subseccion { border: 0; border-radius: 0; padding: 0; margin-bottom: 16px; }
/* Paso 3: "Descuento/Aumento" del programa pegado al % */
.ajuste-programa { flex: 0 0 auto; width: auto; border-top-right-radius: 0; border-bottom-right-radius: 0; }
/* Paso 3: número de cada bloque (1 plan, 2 matrícula, 3 programa, 4 cuotas, 5 depósito) */
.orden-paso-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    margin-right: 6px;
    border-radius: 50%;
    background: #1e3c72;
    color: #fff;
    font-size: 10px;
    letter-spacing: 0;
}
.ficha-modal .orden-subseccion-titulo,
.ficha-modal .resumen-confirmacion-titulo {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #1e3c72;
    padding-bottom: 6px;
    margin-bottom: 10px;
    border-bottom: 1px solid #e6e9ef;
}
.ficha-modal .programa-resumen,
.ficha-modal .orden-resumen {
    background: #f5f7fa;
    border: 1px solid #e6e9ef;
    border-left: 3px solid #1e3c72;
    border-radius: 4px;
    margin-top: 6px;
}
.ficha-modal .programa-resumen .ficha-seccion-titulo { border-bottom-color: #dfe3ea; }
.ficha-modal .programa-resumen-label,
.ficha-modal .orden-resumen-label { color: #6b7280; font-weight: 600; letter-spacing: .6px; }
.ficha-modal .modal-body .orden-resumen-monto input[readonly] { color: #1e3c72; font-weight: 700; background: #fff; }
.ficha-modal .input-group-text { background: #f5f7fa; border-color: #cfd6e0; color: #4a5568; }
.ficha-modal .casilla-matricula { background: #fff; }
.ficha-modal #avisoMatriculaEditable { color: #8a5a00 !important; }
.ficha-modal .resumen-confirmacion-seccion { border-bottom: 0; }
.ficha-modal .resumen-confirmacion-tabla td:first-child { color: #6b7280; }

/* Resultado de la búsqueda: aviso sobrio en vez de la etiqueta de color fuerte */
#estadoBusquedaEstudiante .badge {
    font-size: 12px;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 4px;
}
#estadoBusquedaEstudiante .badge-success { background: #e8f3ec; color: #1f6b3a; border: 1px solid #c5e1cf; }
#estadoBusquedaEstudiante .badge-warning { background: #fdf4e3; color: #8a5a00; border: 1px solid #f1dcae; }
.ficha-nota { font-size: 11.5px; color: #6b7280; margin: 4px 0 10px; }
#datosEstudianteForm .select2-container .select2-selection--single { border-color: #cfd6e0 !important; }

/* Profesión / Universidad (Select2) junto al botón "Nueva": ocupan el ancho
   restante y tienen la misma altura que los demás campos pequeños */
#datosEstudianteForm .input-group > .select2-container {
    flex: 1 1 auto;
    width: 1% !important;
}
#datosEstudianteForm .select2-container .select2-selection--single {
    height: 32px !important;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
#datosEstudianteForm .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: .875rem;
}
#datosEstudianteForm .select2-container--disabled .select2-selection--single {
    background-color: #f7f8fa;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.kt-portlet__head {
    border-radius: 4px 4px 0 0;
}

/* La tarjeta "PRE-REGISTROS PENDIENTES" se recorta a sus bordes redondeados:
   sin esto, el fondo degradado del encabezado y la tabla ancha se salen del recuadro. */
.kt-portlet-preregistros-pendientes {
    overflow: hidden;
}

.kt-portlet-preregistros-pendientes .table-responsive {
    max-width: 100%;
    overflow-x: auto;
}

/* El script de altura "stretched" del tema a veces falla en esta vista y deja
   una altura fija incorrecta en estos contenedores. */
#kt_body,
#kt_content {
    height: auto !important;
    min-height: 0 !important;
}

/* kt-portlet--height-fluid le da a la tarjeta una altura fija en base al
   contenedor padre (calc(100% - 20px)), sin relacion con la cantidad de filas
   de la tabla. Con muchos preregistros el contenido excede esa altura fija y
   se dibuja encima del footer. Esta vista no necesita altura tipo dashboard,
   así que la tarjeta debe crecer segun su contenido real. */
.kt-portlet-preregistros-pendientes {
    height: auto !important;
}

.table th {
    font-weight: 600;
}

.custom-control-label {
    cursor: pointer;
    width: 100%;
}

/* Formulario compacto de pre-registro */
.preregistro-step {
    padding: 6px 0;
}

.step-title {
    font-size: 14px;
    font-weight: 700;
    color: #3b3f5c;
    margin-bottom: 10px;
}

.step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    font-size: 12px;
    margin-right: 6px;
}

.step-divider {
    margin: 12px 0;
    border-top: 1px dashed #dfe1ea;
}

.chip-summary {
    margin-top: 10px;
    padding: 8px 12px;
    background: #f4f6fb;
    border-left: 3px solid #1e3c72;
    border-radius: 6px;
}

.chip-summary-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #444;
}

/* Resumen del programa seleccionado (PASO 2) */
.programa-resumen {
    margin-top: 12px;
    padding: 12px 16px;
    background: #f4f6fb;
    border: 1px solid #e2e6f2;
    border-left: 3px solid #1e3c72;
    border-radius: 6px;
}

.programa-resumen-titulo {
    font-size: 14px;
    font-weight: 700;
    color: #1e3c72;
    margin-bottom: 10px;
}

.programa-resumen-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 10px 20px;
}

.programa-resumen-item {
    display: flex;
    flex-direction: column;
    font-size: 13px;
    color: #3b3f5c;
}

.programa-resumen-label {
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #8a8fa3;
    font-weight: 700;
    margin-bottom: 2px;
}

/* PASO 3: Orden de Pago de Matrícula, organizada en subsecciones */
.orden-subseccion {
    background: #fff;
    border: 1px solid #e9ecf5;
    border-radius: 6px;
    padding: 12px 14px;
    margin-bottom: 12px;
}

.orden-subseccion-titulo {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #52618a;
    margin-bottom: 10px;
}

.orden-subseccion-titulo i {
    color: #1e3c72;
    margin-right: 4px;
}

.orden-resumen {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    background: #f4f6fb;
    border-left: 3px solid #27ae60;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 10px;
}

.orden-resumen-item {
    min-width: 200px;
    flex: 1;
}

.orden-resumen-label {
    display: block;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #8a8fa3;
    font-weight: 700;
    margin-bottom: 4px;
}

.orden-resumen-monto input {
    font-size: 15px;
    color: #1e3c72;
}

/* Asistente de Pre-Registro por pasos (modales) */
.wizard-modal-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 0.3rem 0.3rem 0 0;
}

.wizard-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #fff;
    color: #1e3c72;
    font-weight: 700;
    font-size: 13px;
    margin-right: 8px;
    vertical-align: middle;
}

/* PASO 4: tabla de revisión antes de guardar */
.resumen-confirmacion-seccion {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px dashed #dfe1ea;
}

.resumen-confirmacion-seccion:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

/* Modal "Detalle de la Solicitud": monto y cuenta arriba, ficha compacta abajo */
.detalle-sol-monto {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    background: #f5f7fa;
    border: 1px solid #e6e9ef;
    border-left: 3px solid #1e3c72;
    border-radius: 4px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.detalle-sol-cifra {
    font-size: 24px;
    font-weight: 700;
    color: #1e3c72;
    line-height: 1.2;
}
.detalle-sol-cuenta { font-size: 13px; color: #4a5568; margin-top: 4px; }
.detalle-sol-cuenta span { color: #6b7280; }
.detalle-sol-estado {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: #8a5a00;
    background: #fdf4e3;
    border: 1px solid #f1dcae;
    border-radius: 4px;
    padding: 3px 8px;
    white-space: nowrap;
}
.detalle-sol-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 20px;
    padding-bottom: 6px;
}
.detalle-sol-label {
    display: block;
    font-size: 10.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #6b7280;
    margin-bottom: 2px;
}
.detalle-sol-valor { font-size: 13px; color: #1f2937; line-height: 1.4; }
.detalle-sol-valor small { color: #6b7280; font-size: 12px; }
@media (max-width: 575px) {
    .detalle-sol-grid { grid-template-columns: 1fr; }
}

.resumen-confirmacion-titulo {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #1e3c72;
    margin-bottom: 4px;
}

.resumen-confirmacion-tabla td {
    padding: 4px 6px;
    font-size: 13px;
    vertical-align: top;
}

.resumen-confirmacion-tabla td:first-child {
    color: #8a8fa3;
    width: 45%;
    font-weight: 600;
}

.resumen-confirmacion-total td {
    font-weight: 700;
    color: #1e3c72;
    font-size: 14px;
    border-top: 1px solid #e2e6f2 !important;
}

.btn-xs {
    padding: 2px 8px;
    font-size: 11px;
    line-height: 1.5;
}

.acciones-preregistro {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
}

.btn-accion {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-size: 13px;
    line-height: 1;
}

.btn-accion-texto {
    height: 30px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    color: #ffffff;
}
</style>

</body>
</html>
