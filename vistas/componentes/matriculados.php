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
                                        <th class="text-center" style="color: white; width: 120px;">N° VOUCHER</th>
                                        <th class="text-center" style="color: white; width: 120px;">FECHA INSCRIPCIÓN</th>
                                        <th class="text-center" style="color: white; width: 150px;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                    <input type="hidden" name="moduloSeleccionado" id="moduloSeleccionado">

                    <!-- Leyenda de Estados -->
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="badge badge-success"><i class="fa fa-check-circle"></i> PAGADO</span>
                                <small class="ml-2">Módulo ya cancelado</small>
                            </div>
                            <div class="col-md-6">
                                <span class="badge badge-danger"><i class="fa fa-times-circle"></i> PENDIENTE</span>
                                <small class="ml-2">Módulo por cancelar (clic para pagar)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Módulos del Programa -->
                    <h6 class="text-primary"><i class="fa fa-list"></i> Módulos del Programa</h6>
                    <small class="text-muted d-block mb-3">Haga clic en un módulo ROJO para registrar el pago</small>

                    <div id="contenedorModulos" class="modulos-grid">
                        <div class="text-center p-4">
                            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2">Cargando módulos...</p>
                        </div>
                    </div>

                    <!-- Módulo Seleccionado (se muestra cuando se hace clic en uno rojo) -->
                    <div id="moduloSeleccionadoInfo" style="display: none;" class="alert alert-info mt-3">
                        <h6><i class="fa fa-check"></i> Módulo Seleccionado para Pago</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Código:</strong> <span id="detalle-codigo"></span></p>
                                <p class="mb-1"><strong>Nombre:</strong> <span id="detalle-nombre"></span></p>
                                <p class="mb-1"><strong>Créditos:</strong> <span id="detalle-creditos"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Horas Teóricas:</strong> <span id="detalle-horas-teoricas"></span></p>
                                <p class="mb-1"><strong>Horas Prácticas:</strong> <span id="detalle-horas-practicas"></span></p>
                                <p class="mb-1"><strong>Costo Sugerido:</strong> <span id="detalle-costo-modulo" class="text-primary font-weight-bold"></span></p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Datos de Pago -->
                    <h6 class="text-primary"><i class="fa fa-money"></i> Datos del Pago</h6>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>COSTO DEL MÓDULO (Bs.): *</label>
                                <input type="number" class="form-control" name="costoModulo" id="costoModulo"
                                       placeholder="0.00" step="0.01" min="0" required>
                                <div class="invalid-feedback">Ingrese el costo del módulo.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>FECHA DE PAGO: *</label>
                                <input type="date" class="form-control" name="fechaPago"
                                       id="fechaPago" required>
                                <div class="invalid-feedback">Seleccione la fecha de pago.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>N° VOUCHER:</label>
                                <input type="text" class="form-control" name="numeroVaucher"
                                       placeholder="Número de comprobante (opcional)">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>FOTO/ARCHIVO DE VOUCHER:</label>
                                <input type="file" class="form-control-file" name="fmodulo" id="fmodulo"
                                       accept="image/*,.pdf">
                                <small class="form-text text-muted">Adjunte una imagen o PDF del comprobante de pago (opcional)</small>
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
<script src="vistas/recursos/assets/js/scripts/inscripcionmodulo.js"></script>

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
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        max-height: 500px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .modulo-card {
        background: white;
        border: 2px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
        top: 10px;
        right: 10px;
        background: #28a745;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
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
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
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
        margin-bottom: 10px;
    }

    .modulo-card-codigo {
        background: #667eea;
        color: white;
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .modulo-card-titulo {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin: 10px 0;
        padding-right: 35px;
        line-height: 1.3;
        min-height: 40px;
    }

    .modulo-card-info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
        font-size: 12px;
        color: #666;
    }

    .modulo-card-info-item {
        display: flex;
        flex-direction: column;
    }

    .modulo-card-info-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 2px;
    }

    .modulo-card-info-valor {
        font-weight: bold;
        color: #333;
    }

    .modulo-card-costo {
        font-size: 16px;
        font-weight: bold;
        color: #667eea;
        margin-top: 8px;
    }

    .modulo-card-estado {
        margin-top: 10px;
        padding: 5px;
        border-radius: 5px;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
    }

    .modulo-card.pagado .modulo-card-estado {
        background: #28a745;
        color: white;
    }

    .modulo-card.pendiente .modulo-card-estado {
        background: #dc3545;
        color: white;
    }

    /* Información de pago (solo en tarjetas pagadas) */
    .modulo-card-pago-info {
        margin-top: 10px;
        padding: 8px;
        background: rgba(40, 167, 69, 0.1);
        border-radius: 5px;
        font-size: 11px;
    }

    .modulo-card-pago-info p {
        margin: 3px 0;
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
</style>
