<?php
/**
 * Vista: Registro de Estudiantes
 * Versión optimizada y validada
 */

// Validación de sesión
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();

date_default_timezone_set("America/La_Paz");

// Generar Token CSRF (Corregido)
$csrf_token = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiantes</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        .text-danger { color: #dc3545; }
        .card-header { padding: 1.25rem; }
        .btn-actions { display: inline-flex; gap: 5px; }
    </style>
</head>

<body class="kt-page--loading-enabled kt-header--fixed kt-aside--fixed">

    <!-- Header móvil -->
    <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
        <div class="kt-header-mobile__logo">
            <a href="index.php">
                <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40">
            </a>
        </div>
    </div>

    <!-- Contenedor Principal -->
    <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
            <div class="kt-grid__item kt-grid__item--fluid kt-wrapper" id="kt_wrapper">
                
                <!-- Navbar y Sidebar -->
                <?php
                    $NavBar = new FuncionesControladores();
                    $NavBar->NavBarControlador();

                    $Sidebar = new FuncionesControladores();
                    $Sidebar->SidebarControlador();
                ?>

                <!-- Subheader -->
                <div class="kt-subheader kt-grid__item" id="kt_subheader">
                    <div class="kt-container">
                        <div class="kt-subheader__main">
                            <h2 class="kt-subheader__title">
                                <i class="flaticon2-user"></i> REGISTRO DE ESTUDIANTES
                            </h2>
                            <div class="kt-subheader__breadcrumbs">
                                <a href="index.php" class="kt-subheader__breadcrumbs-home">
                                    <i class="flaticon2-shelter"></i>
                                </a>
                                <span class="kt-subheader__breadcrumbs-separator"></span>
                                <span class="kt-subheader__breadcrumbs-link">Estudiantes</span>
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
                <div class="app-content content">
                    <div class="content-wrapper">
                        <div class="content-body">
                            <section id="estudiantes-section">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">
                                                    <i class="bi bi-people-fill"></i> Lista de Estudiantes
                                                </h4>
                                                <div class="heading-elements">
                                                    <button data-toggle="modal" 
                                                            data-target="#ModalInsertarEstudiante" 
                                                            type="button" 
                                                            class="btn btn-info btn-sm round btn-min-width">
                                                        <i class="bi bi-person-plus-fill"></i> Nuevo Registro
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="card-content collapse show">
                                                <div class="card-body card-dashboard">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover" id="kt_table_1">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nº</th>
                                                                    <th>CI</th>
                                                                    <th>NOMBRE COMPLETO</th>
                                                                    <th>PROFESION</th>
                                                                    <th>CORREO</th>
                                                                    <th>CONTACTOS</th>                                                                 
                                                                    <th>DETALLE</th>
                                                                    <th>BAJA</th>
                                                                    <th>ELIMINAR</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                    $ListaEstudiantes = new EstudiantesControladores();
                                                                    $ListaEstudiantes->ListaEstudianteControladores();
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal: Nuevo Estudiante -->
    <div class="modal fade" id="ModalInsertarEstudiante" tabindex="-1" role="dialog" 
         aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title text-white" id="modalNuevoEstudianteLabel">
                        <i class="bi bi-person-plus-fill"></i> Nuevo Registro de Estudiante
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form method="post" id="formNuevoEstudiante" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="modal-body">

                        <h5 class="form-section text-info">
                            <i class="bi bi-person-vcard"></i> DATOS PERSONALES
                        </h5>

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

                        <hr>

                        <!-- Contacto -->
                        <h5 class="form-section text-info"><i class="bi bi-telephone-fill"></i> OTROS DATOS</h5>
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

                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i> Campos marcados con 
                                <span class="text-danger">*</span> son obligatorios.
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar
                        </button>
                    </div>

                    <?php
                        $DatosEstudiante = new EstudiantesControladores();
                        $DatosEstudiante->RegistarEstudianteControlador();
                    ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php
        $Footer = new FuncionesControladores();
        $Footer->FooterControlador();
    ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="vistas/recursos/assets/js/scripts/estudiante.js"></script>

    <script>
    $(document).ready(function() {
        const form = document.getElementById('formNuevoEstudiante');

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Mayúsculas automáticas
        $('#inputComplemento, #inputNombres, #apaterno, #amaterno').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Validar edad mínima
        $('#fechaNacimiento').on('change', function() {
            const nacimiento = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;

            if (edad < 15) {
                alert('El estudiante debe tener al menos 15 años.');
                this.value = '';
            }
        });

        // Mostrar fecha actual
        function actualizarFecha() {
            const opciones = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
            const fecha = new Date().toLocaleDateString('es-ES', opciones);
            $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
        }
        actualizarFecha();
        setInterval(actualizarFecha, 60000);
    });
    </script>
</body>
</html>
