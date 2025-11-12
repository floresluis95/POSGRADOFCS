<?php
date_default_timezone_set('America/La_Paz');
// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/kit.modelo.php";

class ImprimirReporte{

public $codigo;

public function traerImpresionSolicitudCompra(){
error_reporting(0);  

$codigo = $this->codigo;
$Solicitud = ListaNotaModelos::ListaNotaModelo();
// $Venta = ModeloVentas::mdlMostrarAdmVentasDetalle($IdVenta);
$fechaActual =  'Oruro '.date('d-m-Y');
require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage();

// ---------------------------------------------------------

$bloque1 = <<<EOF
<hr>
	<table>
		<tr>
			<td style="width:150px"><img src="images/logo.png"></td>

			<td style="background-color:white; width:140px">

			
			</td>
			<td style="background-color:white; width:240px">

				<div style="font-size:8.5px; text-align:right; line-height:15px;">
                <td style="width:120px"><img src="images/kit.jpg"></td>
				</div>
				
			</td>

			
		</tr>
    </table>
   
    <hr width="100%" />

	<table>
    <div style="font-size:10px; text-align:right; line-height:15px;">
$fechaActual
	<br>
    Dirección: PRO. CAMPO JORDAN Y CALLE VILLANUEVA 
    <br>
    TELEFONO :72465876
	</div>
	<div style="font-size:14px; text-align:center; line-height:15px;">
	RECEPCION DE KIT'S
	</div>
	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------
$bloque3 = <<<EOF

	


	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">Fecha recepcion</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">Nota</td>
		<td style="border: 1px solid #666;  color:#fff; background-color:navy; width:240px; text-align:center">Nombre</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[fecharecepcion] </td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[notadeentrega]</td>
		<td style="border: 1px solid #666; background-color:white; width:240px; text-align:center">$item[Nombres] $item[ApellidoPaterno] $item[ApellidoMaterno]</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}
$pdf->Text(70, 265, 'EDWIN CRISTHIAN MAGNE MOYA');
$pdf->Text(85, 270, 'C.I. 14782468 CBBA.');

$pdf->Output('pdf.php');

}

}


$a = new ImprimirReporte();
$a -> codigo = $_GET["codigo"];
$a -> traerImpresionSolicitudCompra();

 ?>
 