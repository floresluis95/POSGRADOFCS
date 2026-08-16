<?php
/**
 * Test simple de generación de PDF
 */

// Iniciar sesión
session_start();
$_SESSION['Validar'] = true;

// Verificar si TCPDF existe
$tcpdfPath = __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
echo "<h1>Test de PDF</h1>";
echo "<h2>Verificando TCPDF...</h2>";

if (file_exists($tcpdfPath)) {
    echo "<p style='color: green;'>✓ TCPDF encontrado en: $tcpdfPath</p>";

    require_once $tcpdfPath;

    try {
        // Crear PDF simple
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('Test');
        $pdf->SetAuthor('Test');
        $pdf->SetTitle('Test PDF');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TEST DE PDF - FUNCIONA CORRECTAMENTE', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

        echo "<p style='color: green;'>✓ PDF creado exitosamente</p>";
        echo "<p><a href='#' onclick='generarPDF(); return false;' style='padding: 10px 20px; background: blue; color: white; text-decoration: none; border-radius: 5px;'>Generar PDF de Prueba</a></p>";

        echo "
        <script>
        function generarPDF() {
            window.open('test_pdf_output.php', '_blank');
        }
        </script>
        ";

        // Crear archivo que genera el PDF
        file_put_contents('test_pdf_output.php', '<?php
session_start();
$_SESSION["Validar"] = true;
require_once "vendor/tecnickcom/tcpdf/tcpdf.php";
$pdf = new TCPDF("P", "mm", "LETTER", true, "UTF-8", false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont("helvetica", "B", 16);
$pdf->Cell(0, 10, "TEST DE PDF - FUNCIONA CORRECTAMENTE", 0, 1, "C");
$pdf->SetFont("helvetica", "", 12);
$pdf->Cell(0, 10, "Fecha: " . date("d/m/Y H:i:s"), 0, 1, "C");
if (ob_get_level()) ob_end_clean();
$pdf->Output("Test.pdf", "I");
exit;
?>');

    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error al crear PDF: " . $e->getMessage() . "</p>";
    }

} else {
    echo "<p style='color: red;'>✗ TCPDF NO encontrado en: $tcpdfPath</p>";
    echo "<p>Verifica que Composer haya instalado TCPDF correctamente</p>";
    echo "<p>Ejecuta: <code>composer require tecnickcom/tcpdf</code></p>";
}

// Verificar directorio vendor
echo "<h2>Verificando estructura de vendor...</h2>";
$vendorDir = __DIR__ . '/vendor';
if (is_dir($vendorDir)) {
    echo "<p style='color: green;'>✓ Directorio vendor existe</p>";

    if (is_dir($vendorDir . '/tecnickcom')) {
        echo "<p style='color: green;'>✓ Directorio tecnickcom existe</p>";

        if (is_dir($vendorDir . '/tecnickcom/tcpdf')) {
            echo "<p style='color: green;'>✓ Directorio tcpdf existe</p>";
        } else {
            echo "<p style='color: red;'>✗ Directorio tcpdf NO existe</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Directorio tecnickcom NO existe</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Directorio vendor NO existe</p>";
}
?>
