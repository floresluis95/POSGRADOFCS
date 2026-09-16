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
        <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40">
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
                  <h2>PRE-REGISTRO</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>NUEVO PRE-REGISTRO (SIN MATRÍCULA)</h3>
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

                  <div class="kt-portlet kt-portlet--height-fluid mb-3 preregistro-compacta">
                    <div class="kt-portlet__body py-3">

                      <!-- PASO 1: BUSCAR / REGISTRAR ESTUDIANTE -->
                      <div class="preregistro-step">
                        <div class="step-title"><span class="step-num">1</span> Estudiante</div>

                        <input type="hidden" name="idcliente" id="selectedEstudianteID" value="">

                        <!-- Búsqueda manual por CI -->
                        <div class="d-flex align-items-end flex-wrap mb-2" style="gap: 10px;">
                          <div class="form-group mb-0">
                            <label class="small font-weight-bold mb-1">Buscar estudiante por CI</label>
                            <input type="text" class="form-control form-control-sm" id="buscarPorCi"
                                   style="max-width: 220px;" placeholder="Ingrese el CI..." maxlength="12">
                          </div>
                          <button type="button" class="btn btn-sm btn-primary" id="btnBuscarPorCi">
                            <i class="flaticon2-search"></i> Buscar
                          </button>
                          <button type="button" class="btn btn-sm btn-secondary" id="btnLimpiarBusquedaCi">
                            <i class="flaticon2-reload"></i> Limpiar
                          </button>
                          <span id="estadoBusquedaEstudiante" class="ml-2"></span>
                        </div>

                        <!-- Datos del estudiante (se autocompletan si el CI ya existe; si no, se llenan aquí para registrarlo) -->
                        <div id="datosEstudianteForm">

                          <div class="row">
                            <div class="col-md-3">
                              <label for="inputCi" class="small font-weight-bold mb-1">C.I. <span class="text-danger">*</span></label>
                              <input type="text" id="inputCi" name="Ci" class="form-control form-control-sm"
                                     placeholder="1234567" required pattern="[0-9]{6,12}" maxlength="12">
                            </div>
                            <div class="col-md-3">
                              <label for="inputComplemento" class="small font-weight-bold mb-1">Complemento</label>
                              <input type="text" id="inputComplemento" name="Complemento"
                                     class="form-control form-control-sm text-uppercase" pattern="[A-Za-z0-9]{1,5}">
                            </div>
                            <div class="col-md-3">
                              <label for="selectExpedido" class="small font-weight-bold mb-1">Expedido <span class="text-danger">*</span></label>
                              <select class="form-control form-control-sm" id="selectExpedido" name="Exp" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="LP">La Paz</option>
                                <option value="CB">Cochabamba</option>
                                <option value="SC">Santa Cruz</option>
                                <option value="OR">Oruro</option>
                                <option value="PT">Potosí</option>
                                <option value="CH">Chuquisaca</option>
                                <option value="TJ">Tarija</option>
                                <option value="BN">Beni</option>
                                <option value="PD">Pando</option>
                                <option value="OTR">Otro</option>
                              </select>
                            </div>
                            <div class="col-md-3">
                              <label for="fechaNacimiento" class="small font-weight-bold mb-1">Fecha Nacimiento <span class="text-danger">*</span></label>
                              <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control form-control-sm" required
                                     max="<?php echo date('Y-m-d'); ?>"
                                     min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
                            </div>
                          </div>

                          <div class="row mt-2">
                            <div class="col-md-3">
                              <label for="inputNombres" class="small font-weight-bold mb-1">Nombre(s) <span class="text-danger">*</span></label>
                              <input type="text" id="inputNombres" name="Nombre" class="form-control form-control-sm"
                                     required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}">
                            </div>
                            <div class="col-md-3">
                              <label for="apaterno" class="small font-weight-bold mb-1">Apellido Paterno <span class="text-danger">*</span></label>
                              <input type="text" id="apaterno" name="Apaterno" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                              <label for="amaterno" class="small font-weight-bold mb-1">Apellido Materno</label>
                              <input type="text" id="amaterno" name="Amaterno" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                              <label for="Edad" class="small font-weight-bold mb-1">Edad <span class="text-danger">*</span></label>
                              <input type="number" id="Edad" name="Edad" class="form-control form-control-sm" required>
                            </div>
                          </div>

                          <div class="row mt-2">
                            <div class="col-md-3">
                              <label for="Lugarn" class="small font-weight-bold mb-1">Lugar de nacimiento <span class="text-danger">*</span></label>
                              <select class="form-control form-control-sm" id="Lugarn" name="Lugarn" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="La Paz">La Paz</option>
                                <option value="Cochabamba">Cochabamba</option>
                                <option value="Santa Cruz">Santa Cruz</option>
                                <option value="Oruro">Oruro</option>
                                <option value="Potosí">Potosí</option>
                                <option value="Chuquisaca">Chuquisaca</option>
                                <option value="Tarija">Tarija</option>
                                <option value="Beni">Beni</option>
                                <option value="Pando">Pando</option>
                                <option value="Otro">Otro</option>
                              </select>
                            </div>
                            <div class="col-md-3">
                              <label for="emailInput" class="small font-weight-bold mb-1">Correo <span class="text-danger">*</span></label>
                              <input type="email" id="emailInput" name="Correo" class="form-control form-control-sm" required maxlength="100">
                            </div>
                            <div class="col-md-3">
                              <label for="inputTelefono" class="small font-weight-bold mb-1">Teléfono</label>
                              <input type="tel" id="inputTelefono" name="Telefono" class="form-control form-control-sm" pattern="[0-9]{7,8}">
                            </div>
                            <div class="col-md-3">
                              <label for="inputCelular" class="small font-weight-bold mb-1">Celular <span class="text-danger">*</span></label>
                              <input type="tel" id="inputCelular" name="Celular" class="form-control form-control-sm"
                                     required pattern="[6-7][0-9]{7}">
                            </div>
                          </div>

                          <div class="row mt-2">
                            <div class="col-md-6">
                              <label for="IdProfesion" class="small font-weight-bold mb-1">Profesión <span class="text-danger">*</span></label>
                              <div class="input-group input-group-sm">
                                <select class="form-control form-control-sm" id="IdProfesion" name="IdProfesion" required>
                                  <option value="">Seleccione una profesión...</option>
                                  <?php foreach ($listaProfesionesInicial as $prof): ?>
                                    <option value="<?php echo (int)$prof['IdProfesion']; ?>"><?php echo htmlspecialchars($prof['NombreProfesion']); ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                  <button type="button" class="btn btn-outline-primary" id="btnNuevaProfesion" title="Agregar una profesión que no está en la lista">
                                    <i class="fa fa-plus"></i>
                                  </button>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <label for="Trabajo" class="small font-weight-bold mb-1">Trabajo actual</label>
                              <input type="text" id="Trabajo" name="Trabajo" class="form-control form-control-sm" maxlength="100">
                            </div>
                            <div class="col-md-3">
                              <label for="direccionInput" class="small font-weight-bold mb-1">Dirección domiciliaria</label>
                              <input type="text" id="direccionInput" name="Direccion" class="form-control form-control-sm" maxlength="100">
                            </div>
                          </div>

                        </div>
                      </div>

                      <hr class="step-divider">

                      <!-- PASO 2: SELECCIONAR PROGRAMA -->
                      <div class="preregistro-step" id="seccionPrograma" style="display: none;">
                        <div class="step-title"><span class="step-num">2</span> Programa</div>

                        <div class="row">
                          <div class="col-lg-3 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Sede</label>
                            <select class="form-control form-control-sm" id="sedePrograma" name="sedePrograma">
                              <option value="">Todas las sedes</option>
                              <?php foreach ($sedesDisponibles as $sedeOpcion): ?>
                                <option value="<?php echo htmlspecialchars($sedeOpcion, ENT_QUOTES); ?>"><?php echo htmlspecialchars($sedeOpcion); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-lg-3 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Grado Académico</label>
                            <select class="form-control form-control-sm" id="gradoAcademico" name="gradoAcademico" required>
                              <option value="">Seleccione el grado...</option>
                              <option value="DIPLOMADO">DIPLOMADO</option>
                              <option value="MAESTRIA">MAESTRÍA</option>
                              <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                            </select>
                          </div>
                          <div class="col-lg-6 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Programa</label>
                            <select class="form-control form-control-sm" id="programa" name="programa" required>
                              <option value="">Seleccione un programa...</option>
                            </select>
                          </div>
                        </div>

                        <!-- Resumen del programa seleccionado -->
                        <div id="tablaPrograma" class="chip-summary" style="display: none;">
                          <div class="chip-summary-row flex-wrap">
                            <span><strong id="proNombre"></strong> (<span id="proCodigo"></span>)</span>
                            <span>Grado: <span id="proGrado"></span></span>
                            <span>Duración: <span id="proDuracion"></span></span>
                            <span>Matrícula: <strong class="text-primary" id="proMatricula"></strong></span>
                            <span>Programa: <strong class="text-primary" id="proPrograma"></strong></span>
                            <span>Módulos: <span id="proModulos"></span></span>
                            <span>Sede: <span id="proSede"></span></span>
                          </div>
                        </div>
                      </div>

                      <hr class="step-divider">

                      <!-- PASO 3: ORDEN DE PAGO DE MATRÍCULA -->
                      <div class="preregistro-step" id="seccionPago" style="display: none;">
                        <div class="step-title"><span class="step-num">3</span> Orden de Pago de Matrícula</div>

                        <small class="text-muted d-block mb-2">
                          <i class="flaticon2-information"></i> Solo se genera la orden de pago de la <strong>matrícula</strong>; el estudiante queda <strong>PENDIENTE</strong> hasta presentar el voucher.
                        </small>

                        <div class="row">
                          <div class="col-lg-4 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Plan de Pago del Programa</label>
                            <select class="form-control form-control-sm" id="tipoPagoCodigo" name="tipoPagoCodigo">
                              <option value="REGULAR">Plan Regular (cuotas libres)</option>
                              <option value="CONTADO">Plan al Contado (con descuento)</option>
                              <option value="GRUPAL">Plan Grupal (definir después en Matriculados)</option>
                            </select>
                          </div>
                          <div class="col-lg-3 form-group mb-2" id="grupoDescuentoPreregistro" style="display:none;">
                            <label class="small font-weight-bold mb-1" id="labelPorcentajeDescuento">% Descuento</label>
                            <input type="number" class="form-control form-control-sm" id="porcentajeDescuento" name="porcentajeDescuento" min="0" max="100" step="0.01" value="0">
                          </div>
                          <div class="col-lg-2 form-group mb-2" id="grupoPagoMatriculaPreregistro" style="display:none;">
                            <label class="small font-weight-bold mb-1 d-block">Matrícula</label>
                            <div class="custom-control custom-checkbox mt-1">
                              <input type="checkbox" class="custom-control-input" id="pagarMatricula" name="pagarMatricula" value="1" checked>
                              <label class="custom-control-label" for="pagarMatricula">¿Pagar matrícula?</label>
                            </div>
                          </div>
                          <div class="col-lg-3 form-group mb-2">
                            <label class="small font-weight-bold mb-1">Tipo y N° de Cuenta a depositar</label>
                            <select class="form-control form-control-sm" id="cuentaPago" name="ProgramaCuentaID" required>
                              <option value="">Seleccione un programa primero...</option>
                            </select>
                          </div>
                        </div>
                        <small class="text-muted d-block mb-2" id="textoMatriculaExonerada" style="display:none;">
                          <i class="flaticon2-information"></i> La matrícula queda en <strong>Bs. 0.00</strong> (ya pagada / exonerada).
                        </small>
                        <small class="text-muted d-block mb-2" id="planInfoTexto">
                          Este plan queda <strong>definitivo</strong> apenas se valide el voucher de matrícula.
                        </small>

                        <div class="row">
                            <div class="col-lg-3 form-group mb-2">
                              <label class="small font-weight-bold mb-1">Monto de Matrícula</label>
                              <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="montoSoloMatricula" readonly>
                              </div>
                            </div>
                            <div class="col-lg-3 form-group mb-2">
                              <label class="small font-weight-bold mb-1">Fecha de Orden</label>
                              <input type="date" class="form-control form-control-sm" name="fechaInscripcion" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Campos hidden -->
                        <input type="hidden" name="costoTotalPrograma" id="costoTotalPrograma" value="0">
                        <input type="hidden" name="costoMatriculaPrograma" id="costoMatriculaPrograma" value="0">
                        <input type="hidden" name="montoAPagar" id="montoAPagar" value="0">
                        <input type="hidden" name="pagoCompleto" value="0">
                        <input type="hidden" name="paginaRedirect" value="preregistro">

                        <!-- Botones -->
                        <div class="text-right mt-2">
                          <button type="reset" class="btn btn-sm btn-secondary">
                            <i class="flaticon2-reload"></i> Limpiar
                          </button>
                          <button type="submit" name="registrarOrdenPago" class="btn btn-sm btn-success">
                            <i class="flaticon2-check-mark"></i> Registrar Pre-Registro
                          </button>
                        </div>
                      </div>

                    </div>
                  </div>

                  </form>

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
                          <select class="form-control form-control-sm" id="filtroPreregistroPrograma">
                            <option value="">Todos los programas</option>
                          </select>
                        </div>
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                          <label class="small font-weight-bold mb-1">Sede</label>
                          <select class="form-control form-control-sm" id="filtroPreregistroSede">
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

  <!-- Modal: Editar Preregistro (CRUD - Update) -->
  <div class="modal fade" id="ModalEditarPreregistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title"><i class="flaticon2-edit"></i> Editar Pre-Registro</h5>
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
              <input type="number" step="0.01" min="0.01" class="form-control" name="montoPagado" id="editMontoPagado" required>
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
          <h5 class="modal-title"><i class="fa fa-plus"></i> Nueva Profesión</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formNuevaProfesion">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Nombre de la Profesión <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" id="inputNuevaProfesion" maxlength="25" required autofocus>
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

  <!-- Modal: Detalle de la solicitud de orden de pago -->
  <div class="modal fade" id="ModalDetallePreregistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title"><i class="fa fa-eye"></i> Detalle de la Solicitud</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="detallePreregistroContenido">
          <div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>
        </div>
        <div class="modal-footer" style="background-color: #f5f5f5; border-radius: 0 0 15px 15px;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Generar Orden de Pago (datos para la factura) -->
  <div class="modal fade" id="ModalGenerarOrdenPago" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e7145 0%, #27ae60 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title"><i class="fa fa-file-invoice"></i> Generar Orden de Pago</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formGenerarOrdenPago" method="POST" target="_blank"
              action="vistas/componentes/generar-orden-pago-pdf.php">
          <input type="hidden" name="idOrdenPago" id="generarIdOrdenPago">
          <input type="hidden" name="responsable" id="generarResponsable">
          <input type="hidden" name="firma" id="generarFirma">
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

    // Hasta que se haga una búsqueda, el formulario de datos permanece bloqueado
    function habilitarCamposEstudiante(habilitar) {
        $('#datosEstudianteForm input, #datosEstudianteForm select').prop('disabled', !habilitar);
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
        $('#IdProfesion').val(est.IdProfesion || '');
        $('#Trabajo').val(est.Trabajo || '');
        $('#direccionInput').val(est.Direccion || '');
    }

    function limpiarCamposEstudiante(ciPrellenado) {
        $('#datosEstudianteForm input[type="text"], #datosEstudianteForm input[type="email"], #datosEstudianteForm input[type="tel"], #datosEstudianteForm input[type="number"], #datosEstudianteForm input[type="date"]').val('');
        $('#datosEstudianteForm select').val('');
        $('#inputCi').val(ciPrellenado || '');
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

    // Agregar una profesión que no está en el select (botón "+")
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
                $('#IdProfesion').val(prof.IdProfesion);

                $('#ModalNuevaProfesion').modal('hide');
                if (resp.duplicado) {
                    swal('Atención', 'Esa profesión ya existía, se seleccionó del listado', 'info');
                }
            },
            error: function() {
                btnSubmit.prop('disabled', false);
                swal('Error', 'No se pudo agregar la profesión', 'error');
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
            $('#programa').empty().append('<option value="">Seleccione un grado primero</option>');
            return;
        }

        $.ajax({
            url: 'ajax/programa.ajax.php',
            type: 'POST',
            data: { grado: grado, sede: sede },
            dataType: 'json',
            success: function(response) {
                $('#programa').empty().append('<option value="">Seleccione un programa</option>');

                if (response.length > 0) {
                    response.forEach(function(p) {
                        $('#programa').append('<option value="' + p.ProgramaID + '">' + p.Codigo + ' - ' + p.NombrePrograma + ' (' + (p.Sede || '') + ')</option>');
                    });
                }
            },
            error: function() {
                swal("Error", "No se pudieron obtener los programas", "error");
            }
        });
    }

    $('#gradoAcademico').on('change', cargarProgramasPorSedeYGrado);
    $('#sedePrograma').on('change', cargarProgramasPorSedeYGrado);

    // ============================================
    // PASO 3: MONTO DE MATRÍCULA
    // El backend (RegistrarPreregistroModelo) exige que este monto ya refleje el mismo
    // % de descuento del Plan de Pago del Programa elegido arriba (hoy es un descuento
    // único compartido; un descuento de matrícula totalmente independiente queda pendiente).
    // Para Plan Regular, además se puede exonerar la matrícula (checkbox "¿Pagar matrícula?"),
    // dejando el monto en 0, y aplicar su propio % de descuento independiente.
    // ============================================
    function recalcularMontoMatricula() {
        const tipo = $('#tipoPagoCodigo').val();
        const costoBase = parseFloat($('#costoMatriculaPrograma').val()) || 0;
        const matriculaExonerada = tipo !== 'GRUPAL' && !$('#pagarMatricula').is(':checked');

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
    }

    // ============================================
    // PLAN DE PAGO DEL PROGRAMA (Regular/Contado/Grupal): elegido aquí, queda definitivo
    // al validar el voucher de matrícula (salvo Grupal, que se arma después en Matriculados)
    // ============================================
    $('#tipoPagoCodigo').on('change', function() {
        const tipo = $(this).val();

        $('#grupoPagoMatriculaPreregistro').hide();
        $('#pagarMatricula').prop('checked', true);

        if (tipo === 'CONTADO') {
            $('#grupoDescuentoPreregistro').show();
            $('#grupoPagoMatriculaPreregistro').show();
            $('#labelPorcentajeDescuento').text('% Descuento (programa)');
            $('#planInfoTexto').html('Este plan queda <strong>definitivo</strong> apenas se valide el voucher de matrícula.');
        } else if (tipo === 'GRUPAL') {
            $('#grupoDescuentoPreregistro').hide();
            $('#porcentajeDescuento').val(0);
            $('#planInfoTexto').html('No se fija ningún plan todavía: el estudiante quedará disponible para armar un <strong>Plan Grupal</strong> después, desde Matriculados.');
        } else {
            $('#grupoDescuentoPreregistro').show();
            $('#grupoPagoMatriculaPreregistro').show();
            $('#porcentajeDescuento').val(0);
            $('#labelPorcentajeDescuento').text('% Descuento (matrícula)');
            $('#planInfoTexto').html('Este plan queda <strong>definitivo</strong> apenas se valide el voucher de matrícula.');
        }

        recalcularMontoMatricula();
    });

    // El select ya viene con "Plan Regular" seleccionado por defecto (primera opción), así
    // que el evento "change" nunca se dispara solo con la carga de la página; se fuerza aquí
    // para que los campos de descuento/matrícula del plan por defecto se muestren de una vez.
    $('#tipoPagoCodigo').trigger('change');

    $('#porcentajeDescuento').on('input', recalcularMontoMatricula);

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
                    // Llenar tabla de datos del programa
                    $('#proNombre').text(respuesta.NombrePrograma);
                    $('#proCodigo').text(respuesta.Codigo);
                    $('#proGrado').text(respuesta.GradoAcademico);
                    $('#proDuracion').text(respuesta.DuracionMeses + ' meses');
                    $('#proModulos').text(respuesta.Modulos);
                    $('#proSede').text(respuesta.Sede);

                    const costoMatricula = parseFloat(respuesta.CostoMatricula) || 0;
                    const costoPrograma = parseFloat(respuesta.Costo) || 0;

                    $('#proMatricula').text('Bs. ' + costoMatricula.toFixed(2));
                    $('#proPrograma').text('Bs. ' + costoPrograma.toFixed(2));

                    // Guardar en campos hidden
                    $('#costoMatriculaPrograma').val(costoMatricula.toFixed(2));
                    $('#costoTotalPrograma').val(costoPrograma.toFixed(2));

                    // Mostrar tabla y siguiente sección
                    $('#tablaPrograma').slideDown();
                    $('#seccionPago').slideDown();

                    // El pre-registro SIEMPRE es orden de pago de matrícula (nunca programa completo)
                    recalcularMontoMatricula();

                    // Cargar las cuentas de MATRÍCULA de este programa en el select
                    cargarCuentasPrograma(programaId);
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
    const btnRegistrarOrdenPago = document.querySelector('#formPreregistro button[name="registrarOrdenPago"]');
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

        // Validar que el monto de matrícula sea mayor a 0, salvo que el Plan Regular
        // haya marcado explícitamente la matrícula como ya pagada / exonerada
        const montoAPagar = parseFloat($('#montoAPagar').val()) || 0;
        const matriculaExonerada = $('#tipoPagoCodigo').val() !== 'GRUPAL' && !$('#pagarMatricula').is(':checked');
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
                Celular: $('#inputCelular').val()
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

        $('#filtroPreregistroPrograma').html(opciones).val(seleccionActual || '');
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
        $('#filtroPreregistroPrograma').val('');
        $('#filtroPreregistroSede').val('');
        aplicarFiltrosPreregistro();
    });

    poblarFiltroProgramas();
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

                const html = '' +
                    '<div class="chip-summary mb-2">' +
                        '<div class="chip-summary-row">' +
                            '<span><i class="flaticon2-user-outline-symbol"></i> <strong>' + nombreCompleto + '</strong></span>' +
                            '<span>CI: ' + (p.CiCompleto || '') + '</span>' +
                            '<span>' + (p.Correo || '-') + '</span>' +
                            '<span>' + (p.Celular || '-') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="chip-summary mb-2">' +
                        '<div class="chip-summary-row flex-wrap">' +
                            '<span><strong>' + (p.NombrePrograma || '') + '</strong> (' + (p.CodigoPrograma || '') + ')</span>' +
                            '<span>Grado: ' + (p.GradoAcademico || '') + '</span>' +
                            '<span>Sede: ' + (p.Sede || '-') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<table class="table table-sm table-bordered mb-2">' +
                        '<tbody>' +
                            '<tr><th style="width:45%;">N° de Orden</th><td>' + (p.NumeroOrden || '-') + '</td></tr>' +
                            '<tr><th>Fecha de solicitud</th><td>' + fechaCorta + '</td></tr>' +
                            '<tr><th>Monto de matrícula</th><td>Bs. ' + parseFloat(p.MontoFinal || 0).toFixed(2) + '</td></tr>' +
                            '<tr><th>Estado</th><td><span class="badge badge-warning">' + (p.Estado || 'PENDIENTE') + '</span></td></tr>' +
                        '</tbody>' +
                    '</table>';

                $('#detallePreregistroContenido').html(html);
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
                    $('#generarResponsable').val(p.ResponsableGeneracion || '');
                    $('#generarFirma').val(p.Firma || '');

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
                responsable: $('#generarResponsable').val(),
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

    console.log('=== PRE-REGISTRO INICIALIZADO CORRECTAMENTE ===');

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarPreregistro);
} else {
    iniciarPreregistro();
}
</script>

<style>
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
