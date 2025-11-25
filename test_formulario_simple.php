<!DOCTYPE html>
<html>
<head>
    <title>Test Formulario Módulos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test de Registro de Módulos</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once 'controladores/modulo.controlador.php';
            require_once 'modelos/modulo.modelo.php';
            require_once 'modelos/conexion.modelo.php';

            echo '<div class="alert alert-info">';
            echo '<h4>POST Recibido:</h4>';
            echo '<pre>';
            print_r($_POST);
            echo '</pre>';
            echo '</div>';

            $controlador = new ModuloControlador();
            $controlador->RegistrarModulosPorProgramaControlador();
        }
        ?>

        <form method="POST" action="">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Formulario de Prueba - Registro de Módulos
                </div>
                <div class="card-body">
                    <input type="hidden" name="registrarModulosPorPrograma" value="1">
                    <input type="hidden" name="programaID" value="1">
                    <input type="hidden" name="totalModulos" value="2">

                    <h5>Módulo 1</h5>
                    <div class="form-group">
                        <label>Código:</label>
                        <input type="text" class="form-control" name="codigomodulo_1" value="MODULO I" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nombre (opcional):</label>
                        <input type="text" class="form-control" name="nombremodulo_1" placeholder="Dejar vacío para usar el código">
                    </div>
                    <div class="form-group">
                        <label>Docente (opcional):</label>
                        <select class="form-control" name="docentemodulo_1">
                            <option value="">Sin asignar</option>
                            <option value="6">CARLOS UGARTE CARRILLO</option>
                        </select>
                    </div>

                    <hr>

                    <h5>Módulo 2</h5>
                    <div class="form-group">
                        <label>Código:</label>
                        <input type="text" class="form-control" name="codigomodulo_2" value="MODULO II" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nombre (opcional):</label>
                        <input type="text" class="form-control" name="nombremodulo_2" placeholder="Dejar vacío para usar el código">
                    </div>
                    <div class="form-group">
                        <label>Docente (opcional):</label>
                        <select class="form-control" name="docentemodulo_2">
                            <option value="">Sin asignar</option>
                            <option value="6">CARLOS UGARTE CARRILLO</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-lg">
                        Registrar Módulos
                    </button>
                    <a href="?limpiar=1" class="btn btn-danger">Limpiar módulos del programa 1</a>
                </div>
            </div>
        </form>

        <?php
        if (isset($_GET['limpiar'])) {
            require_once 'modelos/modulo.modelo.php';
            require_once 'modelos/conexion.modelo.php';
            ModuloModelo::EliminarModulosPorProgramaModelo(1);
            echo '<div class="alert alert-warning mt-3">Módulos del programa 1 eliminados</div>';
        }
        ?>

        <div class="mt-4">
            <h4>Módulos Actuales del Programa 1:</h4>
            <?php
            require_once 'modelos/modulo.modelo.php';
            require_once 'modelos/conexion.modelo.php';

            $modulos = ModuloModelo::ListarModulosPorProgramaModelo();
            $encontrados = false;

            echo '<table class="table table-bordered">';
            echo '<tr><th>Código</th><th>Nombre</th><th>Docente</th><th>Estado</th></tr>';

            foreach ($modulos as $mod) {
                if ($mod['ProgramaId'] == 1) {
                    $encontrados = true;
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($mod['codigomodulo']) . '</td>';
                    echo '<td>' . htmlspecialchars($mod['nombremodulo']) . '</td>';
                    echo '<td>' . htmlspecialchars($mod['NombreDocente'] ?? 'Sin asignar') . '</td>';
                    echo '<td>' . htmlspecialchars($mod['estadomodulo']) . '</td>';
                    echo '</tr>';
                }
            }

            if (!$encontrados) {
                echo '<tr><td colspan="4" class="text-center">No hay módulos registrados</td></tr>';
            }

            echo '</table>';
            ?>
        </div>

        <div class="mt-4">
            <a href="index.php?ruta=modulos" class="btn btn-primary">Volver a la vista real de módulos</a>
        </div>
    </div>
</body>
</html>
