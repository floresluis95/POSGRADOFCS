<?php
require_once "controladores/modulo.controlador.php";
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

?>
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


<div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
    <div class="kt-container kt-container--fluid kt-grid__item kt-grid__item--fluid">

        <!-- T�tulo de la P�gina -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title" style="color: white;">
                                <i class="fa fa-book"></i> GESTI�N DE M�DULOS POR PROGRAMA
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Registro de M�dulos -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h4>
                                <i class="fa fa-plus-circle"></i> Registrar M�dulos de un Programa
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">

                        <!-- Paso 1: Seleccionar Estudiante/Inscripci�n -->
                        <div class="alert alert-info">
                            <h5><i class="fa fa-info-circle"></i> Instrucciones</h5>
                            <p class="mb-0">1. Seleccione un estudiante matriculado en un programa</p>
                            <p class="mb-0">2. Se mostrar�n autom�ticamente los campos para registrar los m�dulos seg�n el programa</p>
                            <p class="mb-0">3. Complete el nombre y c�digo de cada m�dulo</p>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label"><strong>ESTUDIANTE MATRICULADO:</strong></label>
                            <div class="col-lg-9">
                                <select class="form-control" id="selectInscripcion">
                                    <option value="">Seleccione un estudiante...</option>
                                    <?php
                                    // Listar estudiantes matriculados
                                    $pdo = Conexion::Conectar();
                                    $stmt = $pdo->query(
                                        "SELECT
                                            ep.idInscripcion,
                                            ep.EstudianteID,
                                            ep.ProgramaID,
                                            e.Nombre,
                                            e.Apaterno,
                                            e.Amaterno,
                                            e.Ci,
                                            p.NombrePrograma,
                                            p.Codigo,
                                            p.Modulos
                                        FROM estudianteprograma ep
                                        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                                        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                                        WHERE ep.Estado = 'ACTIVO'
                                        ORDER BY ep.FechaInscripcion DESC"
                                    );
                                    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($inscripciones as $insc) {
                                        $nombreCompleto = $insc['Nombre'] . ' ' . $insc['Apaterno'] . ' ' . $insc['Amaterno'];
                                        echo '<option value="' . $insc['idInscripcion'] . '"
                                                      data-estudiante="' . htmlspecialchars($nombreCompleto) . '"
                                                      data-ci="' . $insc['Ci'] . '"
                                                      data-programa="' . htmlspecialchars($insc['NombrePrograma']) . '"
                                                      data-codigo-programa="' . $insc['Codigo'] . '"
                                                      data-num-modulos="' . ($insc['Modulos'] ?? 0) . '">';
                                        echo $nombreCompleto . ' (' . $insc['Ci'] . ') - ' . $insc['NombrePrograma'];
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Informaci�n del Programa Seleccionado -->
                        <div id="infoProgramaSeleccionado" style="display: none;" class="alert alert-success mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Estudiante:</strong> <span id="infoEstudiante"></span></p>
                                    <p class="mb-0"><strong>CI:</strong> <span id="infoCI"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Programa:</strong> <span id="infoPrograma"></span></p>
                                    <p class="mb-0"><strong>N� de M�dulos:</strong> <span id="infoNumModulos" class="badge badge-primary"></span></p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Formulario Din�mico de M�dulos -->
                        <form method="POST" id="formRegistrarModulos">
                            <input type="hidden" name="idinscripcion" id="idinscripcion">
                            <input type="hidden" name="numModulos" id="numModulos">

                            <div id="contenedorModulos">
                                <!-- Los campos se generar�n din�micamente aqu� -->
                            </div>

                            <div id="botonesAccion" style="display: none;" class="mt-4">
                                <button type="submit" name="registrarModulos" class="btn btn-success btn-lg btn-block">
                                    <i class="fa fa-save"></i> Registrar M�dulos
                                </button>
                            </div>

                            <?php
                            $registrar = new ModuloControlador();
                            $registrar->RegistrarModulosControlador();
                            ?>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de M�dulos Registrados -->
        <div class="row">
            <div class="col-lg-12">
                <div class="kt-portlet">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h4>
                                <i class="fa fa-list"></i> M�dulos Registrados
                            </h4>
                        </div>
                    </div>

                    <div class="kt-portlet__body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="tablaModulos">
                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <tr>
                                        <th class="text-center" style="color: white;">#</th>
                                        <th class="text-center" style="color: white;">C�DIGO</th>
                                        <th style="color: white;">NOMBRE M�DULO</th>
                                        <th style="color: white;">ESTUDIANTE</th>
                                        <th class="text-center" style="color: white;">CI</th>
                                        <th style="color: white;">PROGRAMA</th>
                                        <th class="text-center" style="color: white;">ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $listar = new ModuloControlador();
                                    $listar->ListarModulosControlador();
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

<!-- Scripts -->
<script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Script de m�dulos cargado');

    // Inicializar DataTable
    $('#tablaModulos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 25
    });

    // ========================================
    // AL SELECCIONAR UNA INSCRIPCI�N
    // ========================================
    $('#selectInscripcion').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const idinscripcion = $(this).val();

        if (!idinscripcion) {
            $('#infoProgramaSeleccionado').hide();
            $('#contenedorModulos').html('');
            $('#botonesAccion').hide();
            return;
        }

        const estudiante = selectedOption.data('estudiante');
        const ci = selectedOption.data('ci');
        const programa = selectedOption.data('programa');
        const codigoPrograma = selectedOption.data('codigo-programa');
        const numModulos = parseInt(selectedOption.data('num-modulos'));

        console.log('Inscripci�n seleccionada:', idinscripcion);
        console.log('N�mero de m�dulos:', numModulos);

        // Actualizar informaci�n
        $('#infoEstudiante').text(estudiante);
        $('#infoCI').text(ci);
        $('#infoPrograma').text(programa + ' (' + codigoPrograma + ')');
        $('#infoNumModulos').text(numModulos);

        // Actualizar campos hidden
        $('#idinscripcion').val(idinscripcion);
        $('#numModulos').val(numModulos);

        // Mostrar info
        $('#infoProgramaSeleccionado').slideDown();

        if (numModulos > 0) {
            generarCamposModulos(numModulos);
            $('#botonesAccion').slideDown();
        } else {
            $('#contenedorModulos').html(`
                <div class="alert alert-warning text-center">
                    <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5>Este programa no tiene m�dulos configurados</h5>
                    <p>Por favor, configure el n�mero de m�dulos en la tabla de programas primero.</p>
                </div>
            `);
            $('#botonesAccion').hide();
        }
    });

    // ========================================
    // GENERAR CAMPOS DIN�MICOS DE M�DULOS
    // ========================================
    function generarCamposModulos(cantidad) {
        let html = '<h5 class="text-primary"><i class="fa fa-edit"></i> Complete la Informaci�n de los M�dulos</h5>';
        html += '<div class="row">';

        for (let i = 1; i <= cantidad; i++) {
            html += `
                <div class="col-lg-6 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <strong><i class="fa fa-book"></i> M�dulo ${i}</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><strong>C�digo M�dulo ${i}:</strong></label>
                                <input type="number"
                                       class="form-control"
                                       name="codigomodulo_${i}"
                                       placeholder="Ej: ${i}"
                                       value="${i}"
                                       required>
                            </div>
                            <div class="form-group mb-0">
                                <label><strong>Nombre del M�dulo ${i}:</strong></label>
                                <input type="text"
                                       class="form-control"
                                       name="nombremodulo_${i}"
                                       placeholder="Ej: Metodolog�a de Investigaci�n"
                                       maxlength="100"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Salto de fila cada 2 m�dulos
            if (i % 2 === 0) {
                html += '</div><div class="row">';
            }
        }

        html += '</div>';
        $('#contenedorModulos').html(html);
    }

    // ========================================
    // VALIDACI�N DEL FORMULARIO
    // ========================================
    $('#formRegistrarModulos').on('submit', function(e) {
        const numModulos = parseInt($('#numModulos').val());
        let modulosValidos = 0;

        for (let i = 1; i <= numModulos; i++) {
            const nombre = $(`input[name="nombremodulo_${i}"]`).val();
            const codigo = $(`input[name="codigomodulo_${i}"]`).val();

            if (nombre && nombre.trim() !== '' && codigo && codigo > 0) {
                modulosValidos++;
            }
        }

        if (modulosValidos === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atenci�n',
                text: 'Debe completar al menos un m�dulo',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }

        // Confirmar
        e.preventDefault();
        Swal.fire({
            title: '�Registrar m�dulos?',
            text: `Se van a registrar ${modulosValidos} m�dulos`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'S�, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Registrando m�dulos, por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar formulario
                $('#formRegistrarModulos')[0].submit();
            }
        });
    });

    console.log('Sistema de m�dulos configurado correctamente');
});
</script>

<style>
.card {
    border: 2px solid #ddd;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
}

.card-header {
    font-weight: 600;
}

#infoProgramaSeleccionado {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table thead th {
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

.table tbody tr:hover {
    background-color: #f8f9fa !important;
}
</style>
