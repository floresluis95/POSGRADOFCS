<?php

// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/consultas.modelo.php";

class ImprimirReporte{

public $codigo;

public function traerImpresionSolicitudCompra(){
error_reporting(0);  
$codigo = $this->codigo;
$Solicitud = ConsultakitModelos::pdfKitdisponibles();
$cantidad = ConsultakitModelos::pdfKitdisponiblesinyeccioncarburador();
$total = ConsultakitModelos::totalkit();
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
	<div style="font-size:12px; text-align:right; line-height:15px;">
	Lugar y fecha de emision: $fechaActual
    <br>
    Dirección: PRO. CAMPO JORDAN Y CALLE VILLANUEVA 
    <br>
    TELEFONO :72465876
	</div>
	<div style="font-size:14px; text-align:center; line-height:15px;">
	KITS DISPONBLES
	</div>
	</table>


EOF;



$pdf->writeHTML($bloque1, false, false, false, false, '');// ---------------------------------------------------------
foreach($total as $row => $item){
$bloque2 = <<<EOF

<table style="font-size:8px; padding:10px 5px;">
<tr>
<td style="border: 1px solid #666; color:#fff; background-color:navy; width:50px; text-align:center">TOTAL</td>
<td style="border: 1px solid #666; color:#000; background-color:white; width:40px; text-align:center">$item[cantidad]</td>
</tr>
</table>
	<table style="font-size:10px; padding:5px 10px;">
        <tr>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:100px; text-align:center">MARCA</td>
        <td style="border: 1px solid #666; color:#fff; background-color:navy; width:90px; text-align:center">CANTIDAD</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">TIPO</td>
		</tr>
	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');
}
foreach($cantidad as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

        <tr>
        <td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[descripcion] </td>
        <td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[cantidad] </td>
        <td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[tipo]</td>
      
     
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');
}


// ---------------------------------------------------------
$bloque4 = <<<EOF
'<div style="font-size:14px; text-align:center; line-height:15px;">
DETALLE
</div>';
	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:120px; text-align:center">SERIE KIT</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:130px; text-align:center">MARCA</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:150px; text-align:center">TIPO</td>
		<td style="border: 1px solid #666; color:#fff; background-color:navy; width:120px; text-align:center">NOTA/E</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');


foreach($Solicitud as $row => $item){
$bloque5 = <<<EOF
	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">$item[seriekit] </td>
		<td style="border: 1px solid #666; background-color:white; width:130px; text-align:center">$item[descripcion]</td>
		<td style="border: 1px solid #666; background-color:white; width:150px; text-align:center">$item[tipo]</td>
		<td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">$item[notadeentrega]</td>
		</tr>
	</table>
EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');

}
$pdf->Text(70, 265, 'EDWIN CRISTHIAN MAGNE MOYA');
$pdf->Text(85, 270, 'C.I. 3789020 CBBA.');
$pdf->Output('pdf.php');

}

}

$a = new ImprimirReporte();
$a -> codigo = $_GET["codigo"];
$a -> traerImpresionSolicitudCompra();

 ?>
 