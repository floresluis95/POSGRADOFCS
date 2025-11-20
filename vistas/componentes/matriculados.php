<?php
// Verificar sesión
if (!isset($_SESSION["validar"])) {
    header("location:index.php?ruta=login");
    exit();
}

require_once 'controladores/inscripcionmodulo.controlador.php';
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
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h4>
                                <i class="fa fa-list"></i> Lista de Estudiantes Matriculados en Programas
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaMatriculados">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Estudiante</th>
                                        <th class="text-center">CI</th>
                                        <th>Programa</th>
                                        <th class="text-center">Grado</th>
                                        <th class="text-center">Código</th>
                                        <th class="text-center">Costo Matrícula</th>
                                        <th class="text-center">N° Voucher</th>
                                        <th class="text-center">Fecha Inscripción</th>
                                        <th class="text-center">Acciones</th>
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

<!-- Modal para Inscribir a Módulo -->
<div class="modal fade" id="modalInscribirModulo" tabindex="-1" role="dialog" aria-labelledby="modalInscribirModuloLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalInscribirModuloLabel">
                    <i class="fa fa-book"></i> Inscribir Estudiante a Módulo
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" id="formInscripcionModulo" class="needs-validation" novalidate>
                <div class="modal-body">

                    <!-- Información del Estudiante -->
                    <div class="alert alert-info">
                        <h5><i class="fa fa-user"></i> Información del Estudiante</h5>
                        <p class="mb-0"><strong>Estudiante:</strong> <span id="infoEstudianteNombre"></span></p>
                        <p class="mb-0"><strong>Programa:</strong> <span id="infoProgramaNombre"></span></p>
                    </div>

                    <input type="hidden" name="estudianteID" id="estudianteID">
                    <input type="hidden" name="programaID" id="programaID">

                    <!-- Selección de Módulo -->
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">MÓDULO: *</label>
                        <div class="col-lg-9">
                            <select class="form-control" name="moduloID" id="moduloID" required>
                                <option value="">Seleccione un módulo...</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un módulo.</div>
                        </div>
                    </div>

                    <!-- Detalles del Módulo -->
                    <div id="detallesModulo" style="display: none;" class="alert alert-secondary">
                        <h6><i class="fa fa-info-circle"></i> Detalles del Módulo</h6>
                        <p class="mb-1"><strong>Código:</strong> <span id="detalle-codigo"></span></p>
                        <p class="mb-1"><strong>Créditos:</strong> <span id="detalle-creditos"></span></p>
                        <p class="mb-1"><strong>Horas Teóricas:</strong> <span id="detalle-horas-teoricas"></span></p>
                        <p class="mb-1"><strong>Horas Prácticas:</strong> <span id="detalle-horas-practicas"></span></p>
                        <p class="mb-0"><strong>Costo:</strong> <span id="detalle-costo-modulo" class="text-primary font-weight-bold"></span></p>
                    </div>

                    <hr>

                    <!-- Datos de Pago -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>COSTO DEL MÓDULO: *</label>
                                <input type="number" class="form-control" name="costoModulo" id="costoModulo"
                                       placeholder="0.00" step="0.01" min="0" required>
                                <div class="invalid-feedback">Ingrese el costo del módulo.</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>N° VOUCHER: *</label>
                                <input type="text" class="form-control" name="numeroVaucherModulo"
                                       placeholder="Número de comprobante" required>
                                <div class="invalid-feedback">Ingrese el número de voucher.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>FECHA DE INSCRIPCIÓN: *</label>
                        <input type="date" class="form-control" name="fechaInscripcionModulo"
                               id="fechaInscripcionModulo" required>
                        <div class="invalid-feedback">Seleccione la fecha de inscripción.</div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" name="registrarInscripcionModulo" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Inscripción
                    </button>
                </div>

                <?php
                    $registrar = new InscripcionModuloControladores();
                    $registrar->RegistrarInscripcionModuloControlador();
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
    .table thead th {
        background-color: #5867dd;
        color: white;
        font-weight: 600;
        text-align: center;
    }

    .btn-inscribir-modulo {
        white-space: nowrap;
    }

    .modal-header.bg-primary {
        background-color: #5867dd !important;
    }

    #detallesModulo {
        background-color: #f8f9fa;
        border-left: 4px solid #5867dd;
    }
</style>
