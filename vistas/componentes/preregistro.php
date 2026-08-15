<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

// Incluir controlador
require_once 'controladores/ordenpago.controlador.php';

// Procesar formulario (usa el mismo backend de preregistro: Estado = PENDIENTE, sin voucher)
$ordenPago = new OrdenPagoControladores();
$ordenPago->RegistrarOrdenPagoControlador();

// Si venimos de crear un estudiante nuevo (modal), lo preseleccionamos automáticamente
$estudianteIDPrecargado = 0;
if (!empty($_GET['nuevoEstudianteCi'])) {
    $ciBuscado = trim($_GET['nuevoEstudianteCi']);
    $estudianteEncontrado = EstudiantesModelos::BuscarEstudianteModelo($ciBuscado);
    if ($estudianteEncontrado) {
        $estudianteIDPrecargado = (int)$estudianteEncontrado['EstudianteID'];
    }
}

// Cargar la tabla de preregistros pendientes directamente desde el servidor,
// para que se vea de una vez aunque el AJAX del navegador falle por algún motivo.
$listaPreregistrosInicial = (new OrdenPagoControladores())->ListarPreregistrosControlador();

// Estudiantes ya registrados en la tabla `estudiante`, para poder elegir uno
// existente en el PASO 1 en vez de crear siempre uno nuevo.
$listaEstudiantesInicial = EstudiantesModelos::ListaEstudianteActivoModelo();

function PintarFilaEstudiante($est)
{
    $nombreCompleto = trim(($est['Apaterno'] ?? '') . ' ' . ($est['Amaterno'] ?? '') . ' ' . ($est['Nombre'] ?? ''));
    $ciCompleto = trim(($est['Ci'] ?? '') . ($est['Complemento'] ? '-' . $est['Complemento'] : '') . ' ' . ($est['Exp'] ?? ''));

    $html = '<tr>';
    $html .= '<td>' . htmlspecialchars($ciCompleto) . '</td>';
    $html .= '<td>' . htmlspecialchars($nombreCompleto) . '</td>';
    $html .= '<td>' . htmlspecialchars($est['Correo'] ?? '-') . '</td>';
    $html .= '<td>' . htmlspecialchars($est['Celular'] ?? '-') . '</td>';
    $html .= '<td class="text-center">';
    $html .= '<button type="button" class="btn btn-sm btn-primary btn-seleccionar-estudiante" data-id="' . (int)$est['EstudianteID'] . '"><i class="flaticon2-check-mark"></i> Seleccionar</button>';
    $html .= '</td></tr>';

    return $html;
}

function PintarFilaPreregistro($pr)
{
    $nombreCompleto = trim(($pr['Apaterno'] ?? '') . ' ' . ($pr['Amaterno'] ?? '') . ' ' . ($pr['Nombre'] ?? ''));
    $tipo = ((int)($pr['pagoCompleto'] ?? 0) === 1)
        ? '<span class="badge badge-success">Programa Completo</span>'
        : '<span class="badge badge-primary">Solo Matrícula</span>';

    $html = '<tr>';
    $html .= '<td class="text-center">' . (int)$pr['idInscripcion'] . '</td>';
    $html .= '<td>' . htmlspecialchars($nombreCompleto) . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['Ci'] ?? '') . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['NombrePrograma'] . ' (' . $pr['CodigoPrograma'] . ')') . '</td>';
    $html .= '<td>' . $tipo . '</td>';
    $html .= '<td>' . htmlspecialchars($pr['FechaInscripcion'] ?? '') . '</td>';
    $html .= '<td class="text-center">';
    $html .= '<button type="button" class="btn btn-sm btn-info btn-ver-pdf" data-id="' . (int)$pr['idInscripcion'] . '" title="Ver Orden de Pago"><i class="fa fa-file-pdf-o"></i></button> ';
    $html .= '<button type="button" class="btn btn-sm btn-warning btn-editar-preregistro" data-id="' . (int)$pr['idInscripcion'] . '" data-pagocompleto="' . (int)($pr['pagoCompleto'] ?? 0) . '" title="Editar"><i class="fa fa-edit"></i></button> ';
    $html .= '<button type="button" class="btn btn-sm btn-danger btn-anular-preregistro" data-id="' . (int)$pr['idInscripcion'] . '" title="Anular"><i class="fa fa-trash"></i></button>';
    $html .= '</td></tr>';

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

                    <!-- PASO 1: SELECCIONAR ESTUDIANTE -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-user-outline-symbol"></i> PASO 1: SELECCIONAR ESTUDIANTE
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">
                        <input type="hidden" name="idcliente" id="selectedEstudianteID" value="">

                        <div id="bloqueNuevoEstudiante" class="py-3">
                          <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                            <div class="form-group mb-0" style="min-width: 280px;">
                              <label class="font-weight-bold mb-1">Buscar estudiante registrado:</label>
                              <input type="text" class="form-control" id="buscarEstudianteExistente"
                                     placeholder="Buscar por CI, nombre o apellido...">
                            </div>
                            <button type="button" class="btn btn-primary btn-lg mt-2" data-toggle="modal" data-target="#ModalInsertarEstudiante">
                              <i class="flaticon2-plus"></i> Nuevo Estudiante
                            </button>
                          </div>

                          <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-striped table-bordered table-hover mb-0" id="tablaEstudiantesExistentes">
                              <thead style="background: #f4f4f4; position: sticky; top: 0;">
                                <tr>
                                  <th style="width: 15%;">CI</th>
                                  <th>Nombre Completo</th>
                                  <th style="width: 20%;">Correo</th>
                                  <th style="width: 12%;">Celular</th>
                                  <th class="text-center" style="width: 15%;">Acción</th>
                                </tr>
                              </thead>
                              <tbody id="tablaEstudiantesExistentesBody">
                                <?php
                                  if (empty($listaEstudiantesInicial)) {
                                      echo '<tr><td colspan="5" class="text-center text-muted">No hay estudiantes registrados</td></tr>';
                                  } else {
                                      foreach ($listaEstudiantesInicial as $est) {
                                          echo PintarFilaEstudiante($est);
                                      }
                                  }
                                ?>
                              </tbody>
                            </table>
                          </div>
                          <small class="text-muted d-block mt-2">
                            <i class="flaticon2-information"></i> Seleccione un estudiante de la lista o registre uno nuevo para continuar con el pre-registro.
                          </small>
                        </div>

                        <!-- Tabla de datos del estudiante -->
                        <div id="tablaEstudiante" style="display: none; margin-top: 20px;">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-weight-bold mb-0">
                              <i class="flaticon2-information"></i> Estudiante Seleccionado
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCambiarEstudiante">
                              <i class="flaticon2-cross"></i> Quitar / Registrar otro
                            </button>
                          </div>
                          <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                              <tbody>
                                <tr>
                                  <th width="25%" style="background-color: #f8f9fa;">Nombre Completo:</th>
                                  <td id="datosNombre"></td>
                                  <th width="20%" style="background-color: #f8f9fa;">CI:</th>
                                  <td id="datosCI"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Correo:</th>
                                  <td id="datosCorreo"></td>
                                  <th style="background-color: #f8f9fa;">Celular:</th>
                                  <td id="datosCelular"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- PASO 2: SELECCIONAR PROGRAMA -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4" id="seccionPrograma" style="display: none;">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-writing"></i> PASO 2: SELECCIONAR PROGRAMA
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">
                        <div class="row">
                          <div class="col-lg-4">
                            <label class="font-weight-bold">Grado Académico:</label>
                            <select class="form-control" id="gradoAcademico" name="gradoAcademico" required>
                              <option value="">Seleccione el grado...</option>
                              <option value="DIPLOMADO">DIPLOMADO</option>
                              <option value="MAESTRIA">MAESTRÍA</option>
                              <option value="ESPECIALIDAD">ESPECIALIDAD</option>
                            </select>
                          </div>
                          <div class="col-lg-8">
                            <label class="font-weight-bold">Programa:</label>
                            <select class="form-control" id="programa" name="programa" required>
                              <option value="">Seleccione un programa...</option>
                            </select>
                          </div>
                        </div>

                        <!-- Tabla de datos del programa -->
                        <div id="tablaPrograma" style="display: none; margin-top: 20px;">
                          <h5 class="font-weight-bold mb-3">
                            <i class="flaticon2-document"></i> Datos del Programa Seleccionado
                          </h5>
                          <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                              <tbody>
                                <tr>
                                  <th width="25%" style="background-color: #f8f9fa;">Programa:</th>
                                  <td id="proNombre"></td>
                                  <th width="20%" style="background-color: #f8f9fa;">Código:</th>
                                  <td id="proCodigo"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Grado:</th>
                                  <td id="proGrado"></td>
                                  <th style="background-color: #f8f9fa;">Duración:</th>
                                  <td id="proDuracion"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Costo Matrícula:</th>
                                  <td id="proMatricula" class="font-weight-bold text-primary"></td>
                                  <th style="background-color: #f8f9fa;">Costo Programa:</th>
                                  <td id="proPrograma" class="font-weight-bold text-primary"></td>
                                </tr>
                                <tr>
                                  <th style="background-color: #f8f9fa;">Módulos:</th>
                                  <td id="proModulos"></td>
                                  <th style="background-color: #f8f9fa;">Sede:</th>
                                  <td id="proSede"></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- PASO 3: ORDEN DE PAGO DE MATRÍCULA -->
                    <div class="kt-portlet kt-portlet--height-fluid mb-4" id="seccionPago" style="display: none;">
                      <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title" style="color: white;">
                            <i class="flaticon2-crisp-icons"></i> PASO 3: ORDEN DE PAGO DE MATRÍCULA
                          </h3>
                        </div>
                      </div>
                      <div class="kt-portlet__body">

                        <div class="alert alert-info">
                            <h5><i class="flaticon2-information"></i> Pre-Registro: Orden de Pago de Matrícula</h5>
                            <p class="mb-0">
                                El pre-registro solo genera la orden de pago de la <strong>matrícula</strong> del programa seleccionado.
                                El estudiante queda <strong>PENDIENTE</strong>; recién cuando cancele la matrícula se le podrá
                                matricular formalmente en el programa (módulos, pago del programa, etc.).
                            </p>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                              <label class="font-weight-bold">Monto de Matrícula:</label>
                              <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="montoSoloMatricula" readonly>
                              </div>
                            </div>
                            <div class="col-lg-4">
                              <label class="font-weight-bold">Fecha de Orden:</label>
                              <input type="date" class="form-control" name="fechaInscripcion" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Campos hidden -->
                        <input type="hidden" name="costoTotalPrograma" id="costoTotalPrograma" value="0">
                        <input type="hidden" name="costoMatriculaPrograma" id="costoMatriculaPrograma" value="0">
                        <input type="hidden" name="montoAPagar" id="montoAPagar" value="0">
                        <input type="hidden" name="porcentajeDescuento" value="0">
                        <input type="hidden" name="montoDescuento" value="0">
                        <input type="hidden" name="pagoCompleto" value="0">
                        <input type="hidden" name="paginaRedirect" value="preregistro">

                        <!-- Botones -->
                        <hr class="my-4">
                        <div class="row">
                          <div class="col-lg-12 text-right">
                            <button type="reset" class="btn btn-secondary btn-lg">
                              <i class="flaticon2-reload"></i> Limpiar
                            </button>
                            <button type="submit" name="registrarOrdenPago" class="btn btn-success btn-lg">
                              <i class="flaticon2-check-mark"></i> Registrar Pre-Registro
                            </button>
                          </div>
                        </div>

                      </div>
                    </div>

                  </form>

                  <!-- TABLA DE PREREGISTROS PENDIENTES (Read + Update + Delete) -->
                  <div class="kt-portlet kt-portlet--height-fluid mt-4">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1dc9b7 0%, #667eea 100%);">
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
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="tablaPreregistros">
                          <thead style="background: #f4f4f4;">
                            <tr>
                              <th class="text-center" style="width: 40px;">#</th>
                              <th>Estudiante</th>
                              <th style="width: 110px;">CI</th>
                              <th>Programa</th>
                              <th style="width: 140px;">Tipo</th>
                              <th style="width: 110px;">Fecha</th>
                              <th class="text-center" style="width: 140px;">Acciones</th>
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

                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
          $Footer = new FuncionesControladores();
          $Footer->FooterControlador();
        ?>
      </div>
    </div>
  </div>

  <!-- Modal: Nuevo Estudiante -->
  <div class="modal fade" id="ModalInsertarEstudiante" tabindex="-1" role="dialog"
       aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
          <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel" style="font-weight: 600;">
            <i class="flaticon2-user-outline-symbol"></i> Nuevo Registro de Estudiante
          </h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
            <span aria-hidden="true" style="font-size: 2rem;">&times;</span>
          </button>
        </div>

        <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
          <input type="hidden" name="paginaRedirect" value="preregistro">

          <div class="modal-body" style="padding: 2rem; background-color: #fafafa;">

            <div class="mb-4 p-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border-left: 4px solid #667eea; border-radius: 8px;">
              <h5 class="mb-0" style="color: #667eea; font-weight: 600;">
                <i class="flaticon2-user"></i> DATOS PERSONALES
              </h5>
            </div>

            <!-- C.I. y complementos -->
            <div class="row">
              <div class="col-md-4">
                <label for="inputCi">C.I. <span class="text-danger">*</span></label>
                <input type="text" id="inputCi" name="Ci" class="form-control"
                       placeholder="1234567" required pattern="[0-9]{6,12}" maxlength="12">
                <div class="invalid-feedback">Ingrese un CI válido.</div>
              </div>
              <div class="col-md-4">
                <label for="inputComplemento">Complemento</label>
                <input type="text" id="inputComplemento" name="Complemento"
                       class="form-control text-uppercase" pattern="[A-Za-z0-9]{1,5}">
              </div>
              <div class="col-md-4">
                <label for="selectExpedido">Expedido <span class="text-danger">*</span></label>
                <select class="form-control" id="selectExpedido" name="Exp" required>
                  <option value="" disabled selected>Seleccione departamento</option>
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
                <div class="invalid-feedback">Seleccione el lugar de expedición.</div>
              </div>
            </div>

            <!-- Nombre y fecha -->
            <div class="row mt-2">
              <div class="col-md-3">
                <label for="inputNombres">Nombre(s) <span class="text-danger">*</span></label>
                <input type="text" id="inputNombres" name="Nombre" class="form-control"
                       required pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{2,50}">
                <div class="invalid-feedback">Ingrese un nombre válido.</div>
              </div>
              <div class="col-md-3">
                <label for="apaterno">Apellido Paterno <span class="text-danger">*</span></label>
                <input type="text" id="apaterno" name="Apaterno" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label for="amaterno">Apellido Materno</label>
                <input type="text" id="amaterno" name="Amaterno" class="form-control">
              </div>
              <div class="col-md-3">
                <label for="fechaNacimiento">Fecha Nacimiento <span class="text-danger">*</span></label>
                <input type="date" id="fechaNacimiento" name="FechaNacimiento" class="form-control" required
                       max="<?php echo date('Y-m-d'); ?>"
                       min="<?php echo date('Y-m-d', strtotime('-100 years')); ?>">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-md-3">
                <label for="Edad">Edad <span class="text-danger">*</span></label>
                <input type="number" id="Edad" name="Edad" class="form-control" required>
                <div class="invalid-feedback">Ingrese una edad válida.</div>
              </div>

              <div class="col-md-4">
                <label for="selectLugarn">Lugar de nacimiento <span class="text-danger">*</span></label>
                <select class="form-control" id="Lugarn" name="Lugarn" required>
                  <option value="" disabled selected>Seleccione departamento</option>
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
                <div class="invalid-feedback">Seleccione el lugar de nacimiento.</div>
              </div>
            </div>

            <div class="mb-4 mt-4 p-3" style="background: linear-gradient(135deg, rgba(29, 201, 183, 0.1) 0%, rgba(102, 126, 234, 0.1) 100%); border-left: 4px solid #1dc9b7; border-radius: 8px;">
              <h5 class="mb-0" style="color: #1dc9b7; font-weight: 600;">
                <i class="flaticon2-phone"></i> OTROS DATOS
              </h5>
            </div>
            <div class="row">

              <div class="col-md-4">
                <label for="emailInput">Correo <span class="text-danger">*</span></label>
                <input type="email" id="emailInput" name="Correo" class="form-control" required maxlength="100">
              </div>

              <div class="col-md-6">
                <label for="IdProfesion">Profesión <span class="text-danger">*</span></label>
                <select class="form-control" id="IdProfesion" name="IdProfesion" required>
                  <option value="">Seleccione una profesión...</option>
                  <?php
                    $ListaProfesion = new ProfesionControlador();
                    $ListaProfesion->ListaProfesionControlador();
                  ?>
                </select>
                <div class="invalid-feedback">Seleccione la profesión.</div>
              </div>
              <div class="col-md-6">
                <label for="trabajoInput">Trabajo actual</label>
                <input type="text" id="Trabajo" name="Trabajo" class="form-control" maxlength="100">
              </div>

              <div class="col-md-6">
                <label for="direccionInput">Dirección domiciliaria</label>
                <input type="text" id="direccionInput" name="Direccion" class="form-control" maxlength="100">
              </div>

              <div class="col-md-4">
                <label for="inputTelefono">Teléfono</label>
                <input type="tel" id="inputTelefono" name="Telefono" class="form-control" pattern="[0-9]{7,8}">
              </div>

              <div class="col-md-4">
                <label for="inputCelular">Celular <span class="text-danger">*</span></label>
                <input type="tel" id="inputCelular" name="Celular" class="form-control"
                       required pattern="[6-7][0-9]{7}">
                <div class="invalid-feedback">Celular inválido (8 dígitos, empieza con 6 o 7).</div>
              </div>

              <div class="col-md-12">
                <div class="alert mt-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%); border: 2px solid #667eea; border-radius: 8px;">
                  <i class="flaticon2-information"></i> <strong>Importante:</strong> Los campos marcados con
                  <span class="text-danger font-weight-bold">*</span> son obligatorios.
                </div>
              </div>

            </div>
          </div>

          <div class="modal-footer" style="background-color: #f5f5f5; padding: 1.5rem; border-radius: 0 0 15px 15px;">
            <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal"
                    style="padding: 0.75rem 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
              <i class="flaticon2-cancel"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-success btn-lg"
                    style="padding: 0.75rem 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(29, 201, 183, 0.4);">
              <i class="flaticon2-check-mark"></i> Guardar Estudiante
            </button>
          </div>

          <?php
            $DatosEstudiante = new EstudiantesControladores();
            $DatosEstudiante->RegistarEstudianteControlador2();
          ?>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Editar Preregistro (CRUD - Update) -->
  <div class="modal fade" id="ModalEditarPreregistro" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, #ffb822 0%, #f5576c 100%); color: white; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title"><i class="flaticon2-edit"></i> Editar Pre-Registro</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditarPreregistro">
          <input type="hidden" name="idInscripcion" id="editIdInscripcion">
          <input type="hidden" name="pagoCompleto" id="editPagoCompleto">
          <div class="modal-body">
            <div class="form-group">
              <label class="font-weight-bold">Monto a Pagar (Bs.) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" class="form-control" name="montoPagado" id="editMontoPagado" required>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label class="font-weight-bold">% Descuento</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" name="porcentajeDescuento" id="editPorcentajeDescuento">
              </div>
              <div class="col-md-6 form-group">
                <label class="font-weight-bold">Monto Descuento (Bs.)</label>
                <input type="number" step="0.01" min="0" class="form-control" name="montoDescuento" id="editMontoDescuento">
              </div>
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

<!-- Scripts adicionales (jQuery y Select2 ya se cargan globalmente en vistas/plantilla.php) -->
<script src="vistas/recursos/sweetalert.min.js"></script>

<script>
const estudianteIDPrecargado = <?php echo (int)$estudianteIDPrecargado; ?>;

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
    // PASO 1: ESTUDIANTE (solo vía botón "Nuevo Estudiante")
    // ============================================

    // Carga los datos del estudiante y lo deja seleccionado en el formulario
    function cargarEstudianteSeleccionado(estudianteID) {
        $.ajax({
            url: 'ajax/estudiantes.ajax.php',
            type: 'POST',
            data: { idestudiante: estudianteID },
            dataType: 'json',
            success: function(response) {
                if (response && !response.error) {
                    const nombreCompleto = (response.Apaterno || '') + ' ' + (response.Amaterno || '') + ' ' + (response.Nombre || '');
                    const ci = (response.Ci || '') + (response.Complemento ? '-' + response.Complemento : '') + ' ' + (response.Exp || '');

                    $('#selectedEstudianteID').val(estudianteID);
                    $('#datosNombre').text(nombreCompleto.trim());
                    $('#datosCI').text(ci);
                    $('#datosCorreo').text(response.Correo || '-');
                    $('#datosCelular').text(response.Celular || '-');

                    $('#bloqueNuevoEstudiante').hide();
                    $('#tablaEstudiante').slideDown();
                    $('#seccionPrograma').slideDown();
                } else {
                    swal("Error", response.error || "No se encontraron datos del estudiante", "error");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                swal("Error", "No se pudieron obtener los datos del estudiante: " + error, "error");
            }
        });
    }

    // Si venimos de guardar un estudiante nuevo, se preselecciona automáticamente
    if (estudianteIDPrecargado > 0) {
        cargarEstudianteSeleccionado(estudianteIDPrecargado);
    }

    // Seleccionar un estudiante ya registrado desde la tabla
    $(document).on('click', '.btn-seleccionar-estudiante', function() {
        const estudianteID = $(this).data('id');
        cargarEstudianteSeleccionado(estudianteID);
    });

    // Filtro en vivo de la tabla de estudiantes registrados (por CI, nombre o apellido)
    $('#buscarEstudianteExistente').on('keyup', function() {
        const texto = $(this).val().toLowerCase().trim();
        $('#tablaEstudiantesExistentesBody tr').each(function() {
            const fila = $(this).text().toLowerCase();
            $(this).toggle(fila.indexOf(texto) > -1);
        });
    });

    // Permitir quitar la selección y registrar otro estudiante
    $('#btnCambiarEstudiante').on('click', function() {
        $('#selectedEstudianteID').val('');
        $('#tablaEstudiante').slideUp();
        $('#bloqueNuevoEstudiante').show();
        $('#seccionPrograma').slideUp();
        $('#seccionPago').slideUp();
    });

    // ============================================
    // PASO 2: SELECCIONAR GRADO Y PROGRAMA
    // ============================================
    $('#gradoAcademico').on('change', function() {
        const grado = $(this).val();

        $('#tablaPrograma').slideUp();
        $('#seccionPago').slideUp();

        if (grado) {
            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { grado: grado },
                dataType: 'json',
                success: function(response) {
                    $('#programa').empty().append('<option value="">Seleccione un programa</option>');

                    if (response.length > 0) {
                        response.forEach(function(p) {
                            $('#programa').append('<option value="' + p.ProgramaID + '">' + p.Codigo + ' - ' + p.NombrePrograma + '</option>');
                        });
                    }
                },
                error: function() {
                    swal("Error", "No se pudieron obtener los programas", "error");
                }
            });
        } else {
            $('#programa').empty().append('<option value="">Seleccione un grado primero</option>');
        }
    });

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
                    $('#montoSoloMatricula').val(costoMatricula.toFixed(2));
                    $('#montoAPagar').val(costoMatricula.toFixed(2));
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

    // ============================================
    // VALIDACIÓN Y ENVÍO DEL FORMULARIO
    // ============================================
    $('#formPreregistro').on('submit', function(e) {

        const fechaMatricula = $('input[name="fechaInscripcion"]').val();
        if (!fechaMatricula) {
            e.preventDefault();
            swal("Error", "Por favor seleccione la fecha de orden", "error");
            return false;
        }

        // Validar que se haya seleccionado estudiante y programa
        const estudianteID = $('#selectedEstudianteID').val();
        const programaID = $('#programa').val();

        if (!estudianteID) {
            e.preventDefault();
            swal("Error", "Por favor registre al estudiante con el botón \"Nuevo Estudiante\"", "error");
            return false;
        }

        if (!programaID) {
            e.preventDefault();
            swal("Error", "Por favor seleccione un programa", "error");
            return false;
        }

        // Validar que el monto de matrícula sea mayor a 0
        const montoAPagar = parseFloat($('#montoAPagar').val()) || 0;
        if (montoAPagar <= 0) {
            e.preventDefault();
            swal("Error", "El monto de matrícula debe ser mayor a 0", "error");
            return false;
        }

        // Permitir que el formulario se envíe
        return true;
    });

    // ============================================
    // RESET FORM
    // ============================================
    $('button[type="reset"]').on('click', function() {
        location.reload();
    });

    // ============================================
    // MODAL: NUEVO ESTUDIANTE
    // ============================================
    const formNuevoEstudiante = document.getElementById('formNuevoEstudiante');

    formNuevoEstudiante.addEventListener('submit', function(event) {
        if (!formNuevoEstudiante.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        formNuevoEstudiante.classList.add('was-validated');
    }, false);

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

    // Limpiar el formulario y su validación cada vez que se abre el modal
    $('#ModalInsertarEstudiante').on('show.bs.modal', function() {
        formNuevoEstudiante.reset();
        formNuevoEstudiante.classList.remove('was-validated');
    });

    // ============================================
    // TABLA DE PREREGISTROS PENDIENTES (CRUD)
    // ============================================

    function renderTablaPreregistros(lista) {
        if (!lista || lista.length === 0) {
            $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-muted"><i class="flaticon2-information"></i> No hay preregistros pendientes</td></tr>');
            return;
        }

        let html = '';
        lista.forEach(function(pr, idx) {
            const nombreCompleto = ((pr.Apaterno || '') + ' ' + (pr.Amaterno || '') + ' ' + (pr.Nombre || '')).trim();
            const tipo = (pr.pagoCompleto == 1)
                ? '<span class="badge badge-success">Programa Completo</span>'
                : '<span class="badge badge-primary">Solo Matrícula</span>';

            html += '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td>' + nombreCompleto + '</td>' +
                '<td>' + (pr.Ci || '') + '</td>' +
                '<td>' + pr.NombrePrograma + ' (' + pr.CodigoPrograma + ')</td>' +
                '<td>' + tipo + '</td>' +
                '<td>' + pr.FechaInscripcion + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-info btn-ver-pdf" data-id="' + pr.idInscripcion + '" title="Ver Orden de Pago"><i class="fa fa-file-pdf-o"></i></button> ' +
                    '<button type="button" class="btn btn-sm btn-warning btn-editar-preregistro" data-id="' + pr.idInscripcion + '" data-pagocompleto="' + pr.pagoCompleto + '" title="Editar"><i class="fa fa-edit"></i></button> ' +
                    '<button type="button" class="btn btn-sm btn-danger btn-anular-preregistro" data-id="' + pr.idInscripcion + '" title="Anular"><i class="fa fa-trash"></i></button>' +
                '</td>' +
            '</tr>';
        });

        $('#tablaPreregistrosBody').html(html);
    }

    function cargarPreregistros() {
        $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarPreregistros' },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    renderTablaPreregistros(resp.preregistros);
                } else {
                    $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los preregistros</td></tr>');
                }
            },
            error: function() {
                $('#tablaPreregistrosBody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los preregistros</td></tr>');
            }
        });
    }

    // La tabla ya viene renderizada desde el servidor al cargar la página (ver PHP arriba),
    // así que aquí solo se usa cargarPreregistros() para refrescar (botón, tras editar/anular).
    $('#btnRefrescarPreregistros').on('click', cargarPreregistros);

    // Ver PDF de la orden de pago
    $(document).on('click', '.btn-ver-pdf', function() {
        const id = $(this).data('id');
        window.open('vistas/componentes/orden-pago-pdf.php?idinscripcion=' + id, '_blank');
    });

    // Abrir modal de edición precargado con los datos del preregistro
    $(document).on('click', '.btn-editar-preregistro', function() {
        const id = $(this).data('id');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerPreregistro', idInscripcion: id },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    const p = resp.preregistro;
                    $('#editIdInscripcion').val(p.idInscripcion);
                    $('#editPagoCompleto').val(p.pagoCompleto);
                    $('#editMontoPagado').val(parseFloat(p.montoPagado).toFixed(2));
                    $('#editPorcentajeDescuento').val(p.porcentajeDescuento || 0);
                    $('#editMontoDescuento').val(p.montoDescuento || 0);
                    $('#editFechaInscripcion').val(p.FechaInscripcion);
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
                    data: { accion: 'anularPreregistro', idInscripcion: id },
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

.table th {
    font-weight: 600;
}

.custom-control-label {
    cursor: pointer;
    width: 100%;
}
</style>

</body>
</html>
