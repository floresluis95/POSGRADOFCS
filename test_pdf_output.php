<?php
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
?>