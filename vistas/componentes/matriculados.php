<?php
	$Validar = new FuncionesControladores();
	$Validar -> ValidarSessionControlador();
	date_default_timezone_set("America/La_Paz");
require_once 'controladores/inscripcionmodulo.controlador.php';
?>
	<?php
			$NavBar = new FuncionesControladores();
			$NavBar -> NavBarControlador();
		?>
        <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
		<?php
			$Sidebar = new FuncionesControladores();
			$Sidebar -> SidebarControlador();
		?>

<!-- Content -->
<div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">

        <!-- Título -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                <img src="vistas/recursos/assets/media/icons/inscripcion.png" width="40" alt="Icono Matriculados">
                                ESTUDIANTES MATRICULADOS
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Filtro por Programa -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                        <div class="kt-portlet__head-label">
                            <h4 style="color: white; margin: 0;">
                                <i class="fa fa-filter"></i> FILTRAR POR PROGRAMA
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label"><strong>SELECCIONAR PROGRAMA:</strong></label>
                            <div class="col-lg-8">
                                <select class="form-control select2-programa" id="selectProgramaFiltro" style="width: 100%;">
                                    <option value="">Todos los programas...</option>
                                    <?php
                                    require_once 'modelos/programa.modelo.php';
                                    $programas = ProgramasModelos::ListaProgramaModelo();
                                    foreach ($programas as $prog) {
                                        $programaID = htmlspecialchars($prog['ProgramaID'] ?? '', ENT_QUOTES);
                                        $nombrePrograma = htmlspecialchars($prog['NombrePrograma'] ?? '', ENT_QUOTES);
                                        $codigo = htmlspecialchars($prog['Codigo'] ?? '', ENT_QUOTES);
                                        $grado = htmlspecialchars($prog['GradoAcademico'] ?? '', ENT_QUOTES);

                                        echo '<option value="' . $programaID . '">';
                                        echo $nombrePrograma . ' - ' . $grado . ' (' . $codigo . ')';
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-secondary btn-block" id="btnLimpiarFiltro">
                                    <i class="fa fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Estudiantes Matriculados -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet kt-portlet--modern">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title" style="color: white;">
                                <i class="fa fa-list-alt"></i> LISTA DE ESTUDIANTES MATRICULADOS EN PROGRAMAS
                            </h3>
                        </div>
                        <div class="kt-portlet__head-toolbar">
                            <div class="kt-portlet__head-wrapper">
                                <span style="color: white; font-size: 11px;">
                                    <i class="fa fa-users" style="color: #28a745;"></i> ACTIVOS
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="tablaMatriculados" style="background: white;">
                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <tr>
                                        <th class="text-center" style="color: white; width: 50px;">#</th>
                                        <th style="color: white;">ESTUDIANTE</th>
                                        <th class="text-center" style="color: white; width: 100px;">CI</th>
                                        <th style="color: white;">PROGRAMA</th>
                                        <th class="text-center" style="color: white; width: 120px;">GRADO</th>
                                        <th class="text-center" style="color: white; width: 100px;">CÓDIGO</th>
                                        <th class="text-center" style="color: white; width: 120px;">COSTO MATRÍCULA</th>
                                        <th class="text-center" style="color: white; width: 100px;">DESCUENTO</th>
                                        <th class="text-center" style="color: white; width: 150px;">TIPO DE PAGO</th>
                                        <th class="text-center" style="color: white; width: 120px;">N° VOUCHER</th>
                                        <th class="text-center" style="color: white; width: 120px;">FECHA INSCRIPCIÓN</th>
                                        <th class="text-center" style="color: white; width: 150px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaMatriculadosBody">
                                    <?php
                                        $listar = new InscripcionModuloControladores();
                                        $listar->ListarEstudiantesMatriculadosControlador();
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

<!-- Modal Ver Detalles del Estudiante Matriculado -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1" role="dialog" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title text-white" id="modalVerDetallesLabel">
                    <i class="fa fa-user-circle"></i> Detalles del Estudiante Matriculado
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h5 class="mb-0"><i class="fa fa-user"></i> <strong id="detalleEstudianteNombre"></strong></h5>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>CI:</strong></label>
                            <p id="detalleEstudianteCI" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Programa:</strong></label>
                            <p id="detalleProgramaNombre" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Grado Académico:</strong></label>
                            <p id="detalleGrado" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Código del Programa:</strong></label>
                            <p id="detalleCodigo" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><strong>Costo de Matrícula:</strong></label>
                            <p id="detalleCosto" class="form-control-plaintext text-primary font-weight-bold"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>N° Voucher:</strong></label>
                            <p id="detalleVoucher" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Fecha de Inscripción:</strong></label>
                            <p id="detalleFecha" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Módulos Inscritos -->
<div class="modal fade" id="modalVerModulos" tabindex="-1" role="dialog" aria-labelledby="modalVerModulosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                <h5 class="modal-title text-white" id="modalVerModulosLabel">
                    <i class="fa fa-list"></i> Módulos Inscritos - <span id="modulosEstudianteNombre"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="contenidoModulos">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Cargando módulos...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Inscribir a Módulo (Registrar Pago) -->
<div class="modal fade" id="modalInscribirModulo" tabindex="-1" role="dialog" aria-labelledby="modalInscribirModuloLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title text-white" id="modalInscribirModuloLabel">
                    <i class="fa fa-book"></i> Registrar Pago de Módulo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" id="formPagoModulo" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="modal-body">

                    <!-- Información del Estudiante -->
                    <div class="alert alert-info">
                        <h5><i class="fa fa-user"></i> Información del Estudiante</h5>
                        <p class="mb-0"><strong>Estudiante:</strong> <span id="infoEstudianteNombre"></span></p>
                        <p class="mb-0"><strong>Programa:</strong> <span id="infoProgramaNombre"></span></p>
                    </div>

                    <input type="hidden" name="estudianteID" id="estudianteID">
                    <input type="hidden" name="programaID" id="programaID">
                    <input type="hidden" name="idinscripcion" id="idinscripcion">

                    <!-- Leyenda de Estados -->
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> PAGADO</span>
                                <small class="ml-2">Módulo ya cancelado</small>
                            </div>
                            <div class="col-md-4">
                                <span class="badge badge-danger"><i class="fa fa-times-circle"></i> PENDIENTE</span>
                                <small class="ml-2">Módulo por cancelar</small>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-primary" id="btnSeleccionarTodos">
                                    <i class="fa fa-check-square"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnLimpiarSeleccion">
                                    <i class="fa fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Módulos del Programa -->
                    <h6 class="text-primary"><i class="fa fa-list"></i> Módulos del Programa</h6>
                    <small class="text-muted d-block mb-3">
                        <i class="fa fa-info-circle"></i> Seleccione uno o más módulos pendientes para registrar el pago
                    </small>

                    <div id="contenedorModulos" class="modulos-grid">
                        <div class="text-center p-4">
                            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2">Cargando módulos...</p>
                        </div>
                    </div>

                    <!-- Módulos Seleccionados (se muestra cuando se seleccionan uno o más módulos) -->
                    <div id="modulosSeleccionadosInfo" style="display: none;" class="card border-success shadow-sm mt-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fa fa-check-square"></i>
                                Módulos Seleccionados (<span id="contadorSeleccionados">0</span>)
                            </h6>
                        </div>
                        <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                            <div id="listaModulosSeleccionados">
                                <!-- Se llenará dinámicamente con JavaScript -->
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total a Pagar:</strong>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span id="totalAPagar" class="text-success font-weight-bold h5">Bs. 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Datos de Pago -->
                    <h6 class="text-primary"><i class="fa fa-money"></i> Datos del Pago</h6>

                    <!-- Costos por módulo (se genera dinámicamente) -->
                    <div id="contenedorCostos" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            <strong>Nota:</strong> Ingrese el costo de cada módulo seleccionado
                        </div>
                        <div id="camposCostos" class="row">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <!-- Datos comunes del pago -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>FECHA DE PAGO: *</label>
                                <input type="date" class="form-control" name="fechaPago"
                                       id="fechaPago" required>
                                <div class="invalid-feedback">Seleccione la fecha de pago.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>N° VOUCHER:</label>
                                <input type="text" class="form-control" name="numeroVaucher" id="numeroVaucher"
                                       placeholder="Número de comprobante (opcional)">
                                <small class="form-text text-muted">Mismo voucher para todos los módulos</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>FOTO/ARCHIVO DE VOUCHER:</label>
                                <input type="file" class="form-control-file" name="fmodulo" id="fmodulo"
                                       accept="image/*,.pdf">
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i>
                                    Adjunte una imagen o PDF del comprobante de pago (opcional). Este archivo se asociará a todos los módulos seleccionados.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="registrarPagoModulo" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Pago
                    </button>
                </div>

                <?php
                    require_once 'controladores/pagomodulo.controlador.php';
                    $registrarPago = new PagoModuloControlador();
                    $registrarPago->RegistrarPagoModuloControlador();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="vistas/recursos/assets/js/scripts/inscripcionmodulo.js?v=<?php echo time(); ?>"></script>

<style>
    /* Estilos modernos para la tabla de matriculados */
    .kt-portlet--modern {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .kt-portlet__head {
        border-radius: 10px 10px 0 0;
        padding: 15px 25px;
    }

    #tablaMatriculados {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 0;
    }

    #tablaMatriculados thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        vertical-align: middle;
        padding: 12px 8px;
        border: none;
    }

    #tablaMatriculados tbody tr {
        transition: all 0.3s ease;
    }

    #tablaMatriculados tbody tr:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    #tablaMatriculados tbody td {
        vertical-align: middle;
        font-size: 13px;
        padding: 10px 8px;
    }

    /* Estilos para los botones dropdown */
    .btn-actions-dropdown {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .btn-actions-dropdown:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .dropdown-menu {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        border: none;
        border-radius: 8px;
    }

    .dropdown-item {
        padding: 8px 20px;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #667eea;
        color: white;
        padding-left: 25px;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 8px;
    }

    .btn-inscribir-modulo {
        white-space: nowrap;
    }

    .modal-header.bg-primary {
        background-color: #667eea !important;
    }

    #detallesModulo {
        background-color: #f8f9fa;
        border-left: 4px solid #667eea;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #tablaMatriculados tbody tr {
        animation: fadeIn 0.5s ease;
    }

    /* Badge para mostrar información */
    .badge-custom {
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #tablaMatriculados {
            font-size: 11px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 10px;
        }
    }

    /* ====================================
       ESTILOS PARA TARJETAS DE MÓDULOS
       ==================================== */
    .modulos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .modulo-card {
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .modulo-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Tarjeta de módulo PAGADO (verde) */
    .modulo-card.pagado {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #f1f9f4 100%);
        cursor: default;
    }

    .modulo-card.pagado::before {
        content: "✓";
        position: absolute;
        top: 8px;
        right: 8px;
        background: #28a745;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }

    .modulo-card.pagado:hover {
        transform: none;
        cursor: not-allowed;
    }

    /* Tarjeta de módulo PENDIENTE (rojo) */
    .modulo-card.pendiente {
        border-color: #dc3545;
        background: linear-gradient(135deg, #f8d7da 0%, #fff5f5 100%);
    }

    .modulo-card.pendiente::before {
        content: "!";
        position: absolute;
        top: 8px;
        right: 8px;
        background: #dc3545;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }

    .modulo-card.pendiente:hover {
        border-color: #bd2130;
        background: linear-gradient(135deg, #f5c6cb 0%, #ffe0e3 100%);
    }

    /* Tarjeta seleccionada */
    .modulo-card.seleccionado {
        border-color: #007bff;
        background: linear-gradient(135deg, #cce5ff 0%, #e7f3ff 100%);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.3);
    }

    .modulo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .modulo-card-codigo {
        background: #667eea;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }

    .modulo-card-titulo {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin: 8px 0;
        padding-right: 30px;
        line-height: 1.2;
        min-height: 32px;
    }

    .modulo-card-docente {
        font-size: 11px;
        color: #666;
        margin-top: 6px;
        padding: 4px 0;
    }

    .modulo-card-docente i {
        color: #667eea;
        margin-right: 4px;
    }

    /* Información de pago (solo en tarjetas pagadas) */
    .modulo-card-pago-info {
        margin-top: 8px;
        padding: 6px;
        background: rgba(40, 167, 69, 0.1);
        border-radius: 4px;
        font-size: 10px;
    }

    .modulo-card-pago-info p {
        margin: 2px 0;
        color: #155724;
    }

    /* Scroll personalizado */
    .modulos-grid::-webkit-scrollbar {
        width: 8px;
    }

    .modulos-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .modulos-grid::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    .modulos-grid::-webkit-scrollbar-thumb:hover {
        background: #5568d3;
    }

    /* Responsive para tarjetas */
    @media (max-width: 768px) {
        .modulos-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .modulo-card-titulo {
            font-size: 14px;
        }
    }

    /* Checkbox en tarjetas */
    .modulo-checkbox {
        pointer-events: none;
    }

    .modulo-card.seleccionable {
        cursor: pointer;
    }

    /* Estilos para Select2 */
    .select2-container {
        width: 100% !important;
        display: block !important;
    }

    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0 !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        padding-right: 28px !important;
        color: #495057 !important;
        font-size: 14px !important;
        display: block !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 5px !important;
        top: 1px !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #667eea !important;
        color: white !important;
    }
</style>

<!-- Script para selección múltiple de módulos -->
<script src="vistas/recursos/assets/js/scripts/matriculados-modulos-multiple.js?v=<?php echo time(); ?>"></script>

<!-- Script para filtro por programa -->
<script>
$(document).ready(function() {
    console.log('=== FILTRO DE MATRICULADOS POR PROGRAMA ===');

    // Inicializar Select2
    $('.select2-programa').select2({
        placeholder: 'Todos los programas...',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Función para cargar estudiantes filtrados por programa
    function cargarEstudiantes(programaID) {
        console.log('>>> Cargando estudiantes. ProgramaID:', programaID || 'TODOS');

        // Destruir DataTable si existe
        if ($.fn.DataTable.isDataTable('#tablaMatriculados')) {
            $('#tablaMatriculados').DataTable().destroy();
        }

        // Mostrar mensaje de carga
        $('#tablaMatriculadosBody').html('<tr><td colspan="12" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando estudiantes...</td></tr>');

        // Petición AJAX
        $.ajax({
            url: 'ajax/inscripcionmodulo.ajax.php',
            type: 'POST',
            data: {
                accion: 'cargarTablaMatriculados',
                programaIDFiltro: programaID || ''
            },
            success: function(response) {
                console.log('✓ Estudiantes cargados correctamente');
                $('#tablaMatriculadosBody').html(response);

                // Verificar si hay datos reales (sin colspan)
                const $firstRow = $('#tablaMatriculadosBody tr:first');
                const hasColspan = $firstRow.find('td[colspan]').length > 0;

                if (!hasColspan) {
                    // Inicializar DataTable solo si hay datos
                    try {
                        $('#tablaMatriculados').DataTable({
                            language: {
                                processing: "Procesando...",
                                search: "Buscar:",
                                lengthMenu: "Mostrar _MENU_ registros",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ estudiantes",
                                infoEmpty: "Mostrando 0 a 0 de 0 estudiantes",
                                infoFiltered: "(filtrado de _MAX_ estudiantes totales)",
                                infoPostFix: "",
                                loadingRecords: "Cargando...",
                                zeroRecords: "No se encontraron estudiantes",
                                emptyTable: "No hay estudiantes matriculados",
                                paginate: {
                                    first: "Primero",
                                    previous: "Anterior",
                                    next: "Siguiente",
                                    last: "Último"
                                }
                            },
                            order: [[0, 'asc']],
                            pageLength: 25,
                            responsive: true
                        });
                        console.log('✓ DataTable inicializado');
                    } catch (error) {
                        console.error('✗ Error al inicializar DataTable:', error);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('✗ Error al cargar estudiantes:', error);
                $('#tablaMatriculadosBody').html('<tr><td colspan="12" class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error al cargar los estudiantes</td></tr>');
            }
        });
    }

    // Evento al cambiar el programa
    $('#selectProgramaFiltro').on('change', function() {
        const programaID = $(this).val();
        console.log('>>> Programa seleccionado:', programaID);
        cargarEstudiantes(programaID);
    });

    // Evento al limpiar filtro
    $('#btnLimpiarFiltro').on('click', function() {
        console.log('>>> Limpiando filtro');
        $('#selectProgramaFiltro').val('').trigger('change');
    });

    console.log('=== FILTRO INICIALIZADO ===');
});
</script>
