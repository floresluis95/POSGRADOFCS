
<?PHP
// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/solicitud.modelo.php";
class ImprimirReporte{
public $IdSolicitud;
public $nroplaca;
public $marca;
public $modelo;
public $tipo;
public $tipomotor;
public function traerImpresionSolicitudCompra(){
error_reporting(0);  
$IdSolicitud = $this->IdSolicitud;
$Solicitud = pdfsolicitudModelos::SolicitudModelo($IdSolicitud);

foreach ($Solicitud as $key => $value) {
	$Placa = $value['nroplaca'];
	$Marca = $value['descmarca'];
	$Modelo = $value['modelo'];
	$tipo = $value['tipo'];
	$TipoMotor = $value['tipomotor'];
	$Cilindrada = $value['cilindrada'];
}
}

}

require('pdfprueba.php');

$pdf = new FPDF();
$pdf->AddPage('P', 'A4');
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTopMargin(10);
$pdf->SetLeftMargin(10);
$pdf->SetRightMargin(10);


/* --- Text --- */
$pdf->Text(16, 19, 'fecha');
/* --- Text --- */
$pdf->Text(16, 45, 'Señor:');
/* --- Text --- */
$pdf->Text(16, 57, 'DIRECTOR EJECUTIVO DE  LA EEC. GNV');
/* --- Text --- */
$pdf->Text(16, 70, 'PRESENTE.');
/* --- Text --- */
$pdf->Text(16, 88, 'PLACA:'.$Placa );
/* --- Text --- */
$pdf->Text(79, 108, 'Text');
/* --- Text --- */
$pdf->Text(79, 124, 'Text');
/* --- Text --- */
$pdf->Text(79, 141, 'Text');
/* --- Text --- */
$pdf->Text(79, 159, 'Text');
/* --- Text --- */
$pdf->Text(79, 176, 'Text');
/* --- Text --- */
$pdf->Text(16, 194, 'Text');
/* --- Text --- */
$pdf->Text(97, 262, 'Text');
/* --- Text --- */
$pdf->Text(97, 276, 'Text');


$pdf->Output('created_pdf.pdf','I');

$a = new ImprimirReporte();
$a -> IdSolicitud = $_GET["IdSolicitud"];
$a -> traerImpresionSolicitudCompra();

?>