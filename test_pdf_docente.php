<?php
/**
 * Script de prueba para generación de PDF de docente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>DIAGNÓSTICO DE GENERACIÓN DE PDF - DOCENTE</h2>";

// Iniciar sesión
session_start();

// Simular sesión de docente
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1245878'; // CI del docente JUANA DIAZ

echo "<p><strong>Sesión iniciada como:</strong> " . $_SESSION['Usuario'] . "</p>";

// Verificar que existe vendor/autoload.php
echo "<h3>1. Verificando TCPDF:</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "<p style='color: green;'>✓ vendor/autoload.php existe</p>";
    require_once 'vendor/autoload.php';

    try {
        $pdf = new TCPDF();
        echo "<p style='color: green;'>✓ TCPDF se puede instanciar correctamente</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error al instanciar TCPDF: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ vendor/autoload.php NO existe</p>";
    echo "<p>Ejecuta: composer install</p>";
}

// Verificar modelos
echo "<h3>2. Verificando modelos:</h3>";
if (file_exists('modelos/conexion.modelo.php')) {
    echo "<p style='color: green;'>✓ conexion.modelo.php existe</p>";
    require_once 'modelos/conexion.modelo.php';
} else {
    echo "<p style='color: red;'>✗ conexion.modelo.php NO existe</p>";
}

if (file_exists('modelos/calificacion.modelo.php')) {
    echo "<p style='color: green;'>✓ calificacion.modelo.php existe</p>";
    require_once 'modelos/calificacion.modelo.php';
} else {
    echo "<p style='color: red;'>✗ calificacion.modelo.php NO existe</p>";
}

// Obtener datos del docente
echo "<h3>3. Obteniendo datos del docente:</h3>";
try {
    $pdo = Conexion::Conectar();

    $stmt = $pdo->prepare("
        SELECT
            DocenteID,
            Ci,
            Complemento,
            Exp,
            Nombre,
            Apaterno,
            Amaterno,
            Especialidad
        FROM docente
        WHERE Ci = :ci
        AND Estado = 1
        LIMIT 1
    ");
    $stmt->bindParam(":ci", $_SESSION['Usuario'], PDO::PARAM_STR);
    $stmt->execute();
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($docente) {
        echo "<p style='color: green;'>✓ Docente encontrado:</p>";
        echo "<ul>";
        echo "<li>ID: " . $docente['DocenteID'] . "</li>";
        echo "<li>Nombre: " . $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'] . "</li>";
        echo "</ul>";

        // Obtener módulos
        echo "<h3>4. Obteniendo módulos del docente:</h3>";
        $modulos = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docente['DocenteID']);

        if (!empty($modulos)) {
            echo "<p style='color: green;'>✓ Módulos encontrados: " . count($modulos) . "</p>";
            echo "<ul>";
            foreach ($modulos as $modulo) {
                echo "<li>" . $modulo['nombremodulo'] . " (" . $modulo['codigomodulo'] . ") - ";
                echo "Programa: " . $modulo['NombrePrograma'] . "</li>";

                // Obtener estudiantes del módulo
                $estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($modulo['Idmodulo'], $modulo['ProgramaID']);
                echo "<ul><li>Estudiantes: " . count($estudiantes) . "</li></ul>";
            }
            echo "</ul>";

            // Probar generación de PDF simple
            echo "<h3>5. Probando generación de PDF simple:</h3>";
            try {
                $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
                $pdf->SetCreator('Test');
                $pdf->SetAuthor('Test');
                $pdf->SetTitle('Test PDF');
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(15, 15, 15);
                $pdf->SetAutoPageBreak(true, 15);
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Cell(0, 10, 'PRUEBA DE PDF', 0, 1, 'C');

                echo "<p style='color: green;'>✓ PDF de prueba creado correctamente</p>";

            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Error al crear PDF: " . $e->getMessage() . "</p>";
            }

        } else {
            echo "<p style='color: red;'>✗ No se encontraron módulos</p>";
        }

    } else {
        echo "<p style='color: red;'>✗ No se encontró el docente con CI: " . $_SESSION['Usuario'] . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>6. Verificando archivos PDF:</h3>";
if (file_exists('tcpdf/pdf/generar-calificaciones-pdf.php')) {
    echo "<p style='color: green;'>✓ generar-calificaciones-pdf.php existe</p>";
} else {
    echo "<p style='color: red;'>✗ generar-calificaciones-pdf.php NO existe</p>";
}

if (file_exists('tcpdf/pdf/reporte-completo-docente.php')) {
    echo "<p style='color: green;'>✓ reporte-completo-docente.php existe</p>";
    echo "<p><a href='tcpdf/pdf/reporte-completo-docente.php' target='_blank' style='padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>PROBAR REPORTE COMPLETO</a></p>";
} else {
    echo "<p style='color: red;'>✗ reporte-completo-docente.php NO existe</p>";
}

echo "<hr>";
echo "<h3>CONCLUSIÓN:</h3>";
echo "<p>Si aparece el botón rojo arriba, haz clic para probar el PDF directamente.</p>";
echo "<p>Si no funciona, verifica los errores en el navegador (pestaña de red en F12).</p>";
?>
