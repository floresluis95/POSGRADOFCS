<?php

// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/consultas.modelo.php";

class ImprimirReporte{

public $codigo;

public function traerImpresionSolicitudCompra(){
error_reporting(0);  
$codigo = $this->codigo;
$Solicitud = ConsultacilModelos::pdfcildisponiblesdesc();
$cantidad = ConsultacilModelos::pdfCildisponiblesmarca();
// $Venta = ModeloVentas::mdlMostrarAdmVentasDetalle($IdVenta);

$fecha='Oruro '.date('j-F-o');

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
			<td style="background-color:white; width:240px">

				<div style="font-size:8.5px; text-align:right; line-height:15px;">
                <td style="width:100px"><img src="images/kit.jpg"></td>
				</div>
				
			</td>

			
		</tr>
    </table>
   
    <hr width="100%" />

	<table>
	<div style="font-size:10px; text-align:right; line-height:15px;">
	Fecha de emision: $fechaActual
    <br>
    Dirección: PRO. CAMPO JORDAN Y CALLE VILLANUEVA 
    <br>
    TELEFONO :72465876
	</div>
	<div style="font-size:14px; text-align:center; line-height:15px;">
	CILINDROS DISPONBLES
	</div>
	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');// ---------------------------------------------------------
$bloque2 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
        <tr>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">CANTIDAD</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">CAPACIDAD</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">MARCA</td>
      
       
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');
foreach($cantidad as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

        <tr>
        <td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[cantidad] </td>
        <td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[capacidad] </td>
        <td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[descripcioncil]</td>
      
     
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');
}
// ---------------------------------------------------------
$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">SERIE CILINDRO</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">MARCA</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">CAPACIDAD</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:100px; text-align:center">NOTA</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


foreach($Solicitud as $row => $item){
    // $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
   
   
$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[seriecilindro] </td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[descripcioncil]</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[capacidad]</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[notadeentrega]</td>
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
 