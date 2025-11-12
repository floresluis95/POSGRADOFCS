<?php

// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/consultas.modelo.php";

class ImprimirReporte{

public $codigo;

public function traerImpresionSolicitudCompra(){
error_reporting(0);  
$incio = $this->inicio;
$finali = $this->finali;
$Solicitud = trabajosConcluidosModelos::pdfTrabajosConcluidos($inicio,$finali);
$cantidad = trabajosConcluidosModelos::tipotransporte($inicio,$finali);
$motor = trabajosConcluidosModelos::motor($inicio,$finali);
// $Venta = ModeloVentas::mdlMostrarAdmVentasDetalle($IdVenta);

$fecha='Oruro '.date('j-F-o');

require_once('tcpdf_include.php');

$width = 300; $height = 300;
		$pageLayout = array($width, $height);
        $pdf = new TCPDF('p', 'mm', $pageLayout, true, 'UTF-8', false);
          
$pdf->startPageGroup();

$pdf->AddPage();
date_default_timezone_set('America/La_Paz');
$fechaActual =  'Oruro '.date('d-m-Y');
// ---------------------------------------------------------

$bloque1 = <<<EOF

<hr>
	<table>
		<tr>
			<td style="width:150px"><img src="images/logo.png"></td>

			<td style="background-color:white; width:140px">

			
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
	<div style="font-size:16px; text-align:center; line-height:15px;">
	REPORTE DE TRABAJOS CONCLUIDOS
	</div>
	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

foreach($cantidad as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque3 = <<<EOF

    <table style="font-size:10px; padding:5px 10px;">
        <tr>
        <td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[tipotransporte] </td>
        <td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[cantidad] </td> 
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');
}
foreach($motor as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque3 = <<<EOF

    <table style="font-size:10px; padding:5px 10px;">
    

        <tr>
        <td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[tipomotor] </td>
        <td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[cantidad] </td> 
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');
}




// ---------------------------------------------------------
$bloque3 = <<<EOF

	


	<table style="font-size:8px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:40px; text-align:center">COD</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:80px; text-align:center">FECHA SOL.</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:90px; text-align:center">CONVERTIDO</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">CLIENTE</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:75px; text-align:center">PLACA</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:85px; text-align:center">CLASE</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:95px; text-align:center">MOTOR</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:90px; text-align:center">KIT</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:80px; text-align:center">CILINDRO</td>       
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		<td style="border: 1px solid #666; background-color:white; width:40px; text-align:center">$item[codsolicitud] </td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[fechasolicitud]</td>
        <td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[fechatrabajo]</td>
        <td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[nombre] $item[paterno] $item[materno]</td>
        <td style="border: 1px solid #666; background-color:white; width:75px; text-align:center">$item[nroplaca]</td>
        <td style="border: 1px solid #666; background-color:white; width:85px; text-align:center">$item[clase]</td>
        <td style="border: 1px solid #666; background-color:white; width:95px; text-align:center">$item[tipomotor]</td>
        <td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[seriekit]</td>
        <td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[seriecilindro]</td>
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}

$pdf->Text(100, 265, 'EDWIN CRISTHIAN MAGNE MOYA');
$pdf->Text(115, 270, 'C.I. 14782468 CBBA.');
$pdf->Output('pdf.php');

}

}

$a = new ImprimirReporte();
$a -> inicio = $_GET["inicio"];
$a -> finali = $_GET["finali"];
$a -> traerImpresionSolicitudCompra();

 ?>
 