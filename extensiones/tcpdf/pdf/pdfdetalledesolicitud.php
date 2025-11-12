<?php
date_default_timezone_set('America/La_Paz');
// require_once "../../../modelos/solicitud.compra.material.modelo.php";
require_once "../../../modelos/concluidos.modelo.php";

class ImprimirReporte{

public $codigo;

public function traerImpresionSolicitudCompra(){
error_reporting(0);  
$codigo = $this->codigo;
$Solicitud = pdfconcluidoModelos::concluidoModelo($codigo);
// $Venta = ModeloVentas::mdlMostrarAdmVentasDetalle($IdVenta);
$fecha='Oruro '.date('j-F-o');

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage();

// ---------------------------------------------------------
foreach($Solicitud as $row => $item){
$bloque1 = <<<EOF
<hr>
	<table>
		<tr>
			<td style="width:150px"><img src="images/logo.png"></td>
            <td style="background-color:white; width:140px">
            		
            </td>
            <div style="font-size:14px; text-align:center; line-height:15px;">
	            FICHA TECNICA DE CONVERSION
            </div>
		</tr>
    </table>
   
    <hr width="100%" />

	<table>
    <div style="font-size:8.5px; text-align:right; line-height:15px;">
    FECHA SOLICITUD: $item[fechasolicitud] <br>
    FECHA ASIGNACION: $item[fechatrabajo]<br>
    FECHA CONVERSION: $item[fechaconcluido]<br>
    TECNICO: $item[Nombres] $item[ApellidoPaterno] $item[ApellidoMaterno]
	</div>
    <div style="font-size:12px; text-align:left; line-height:15px;">
     TALLER:CENTRO DE CONVERSIONES INTERMAG
	</div>
	<div style="font-size:8.5px; text-align:left; line-height:20px;">
	DATOS DEL MOTORIZADO
	</div>
	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');
}
// ---------------------------------------------------------
$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666;  background-color:silver; width:80px; text-align:center">SOLICITUD</td>
		<td style="border: 1px solid #666; background-color:silver; width:80px; text-align:center">PLACA</td>
		<td style="border: 1px solid #666; background-color:silver; width:130px; text-align:center">MARCA</td>
        <td style="border: 1px solid #666; background-color:silver; width:130px; text-align:center">TIPO</td>
        <td style="border: 1px solid #666; background-color:silver; width:120px; text-align:center">MOTOR</td>
        </tr>
	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque4 = <<<EOF
	<table style="font-size:10px; padding:5px 10px;">

		<tr>	
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[codsolicitud] </td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[nroplaca]</td>
		<td style="border: 1px solid #666; background-color:white; width:130px; text-align:center">$item[descmarca]</td>
        <td style="border: 1px solid #666; background-color:white; width:130px; text-align:center">$item[desctipo]</td>
        <td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">$item[tipomotor]</td>
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}

$bloque5 = <<<EOF
<table>
<div style="font-size:8.5px; text-align:left; line-height:30px;">
DATOS DE LA PERSONA
</div>
</table>
<br>

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; background-color:silver; width:80px; text-align:center">CI</td>
		<td style="border: 1px solid #666; background-color:silver; width:180px; text-align:center">NOMBRES</td>
		<td style="border: 1px solid #666; background-color:silver; width:140px; text-align:center">PRIMER APELLIDO</td>
        <td style="border: 1px solid #666; background-color:silver; width:140px; text-align:center">SEGUNDO APELLIDO</td>
        </tr>
	</table>

EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque6 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>	
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[ci] </td>
		<td style="border: 1px solid #666; background-color:white; width: 180px; text-align:center">$item[nombre]</td>
		<td style="border: 1px solid #666; background-color:white; width:140px; text-align:center">$item[paterno]</td>
        <td style="border: 1px solid #666; background-color:white; width:140px; text-align:center">$item[materno]</td>
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

}

$bloque5 = <<<EOF
<table>
<div style="font-size:8.5px; text-align:left; line-height:30px;">
DATOS DEL KIT ASIGNADO
</div>
</table>
<br>

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; background-color:silver; width:180px; text-align:center">MARCA</td>
		<td style="border: 1px solid #666; background-color:silver; width:180px; text-align:center">SERIE</td>
		<td style="border: 1px solid #666; background-color:silver; width:180px; text-align:center">TIPO</td>
        
        </tr>
	</table>

EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque6 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>	
		<td style="border: 1px solid #666; background-color:white; width:180px; text-align:center">$item[descripcion] </td>
		<td style="border: 1px solid #666; background-color:white; width: 180px; text-align:center">$item[seriekit]</td>
		<td style="border: 1px solid #666; background-color:white; width:180px; text-align:center">$item[tipo]</td>
       
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

}

$bloque5 = <<<EOF
<table>
<div style="font-size:8.5px; text-align:left; line-height:30px;">
DATOS DEL CILINDRO ASIGNADO
</div>
</table>
<br>

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; background-color:silver; width:180px; text-align:center">MARCA</td>
		<td style="border: 1px solid #666; background-color:silver; width:140px; text-align:center">SERIE</td>
		<td style="border: 1px solid #666; background-color:silver; width:100px; text-align:center">CAPACIDAD</td>
        <td style="border: 1px solid #666; background-color:silver; width:120px; text-align:center">F. DE FABRICACION</td>
      
        </tr>
	</table>

EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque6 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>	
		<td style="border: 1px solid #666; background-color:white; width:180px; text-align:center">$item[descripcioncil] </td>
		<td style="border: 1px solid #666; background-color:white; width: 140px; text-align:center">$item[seriecilindro]</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[capacidad]</td>
        <td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">$item[aofab]</td>
        
        </tr>

	</table>

EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

}

foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque6 = <<<EOF


EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

}

$bloque5 = <<<EOF
<table>
<div style="font-size:8.5px; text-align:left; line-height:30px;">
PRUEBAS TECNICAS
</div>
</table>
<br>

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		<td style="border: 1px solid #666; background-color:silver; width:100px; text-align:center">INYECTORES</td>
		<td style="border: 1px solid #666; background-color:silver; width:150px; text-align:center">ARRANQUE</td>
		<td style="border: 1px solid #666; background-color:silver; width:90px; text-align:center">ACELERACION</td>
        <td style="border: 1px solid #666; background-color:silver; width:120px; text-align:center">VELOCIDDAD</td>
        <td style="border: 1px solid #666; background-color:silver; width:80px; text-align:center">CABLEADO</td>
        </tr>
    </table>
    

EOF;

$pdf->writeHTML($bloque5, false, false, false, false, '');


foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque6 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>	
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">$item[inyecctores] </td>
		<td style="border: 1px solid #666; background-color:white; width: 150px; text-align:center">$item[arranque]</td>
		<td style="border: 1px solid #666; background-color:white; width:90px; text-align:center">$item[aceleracion]</td>
        <td style="border: 1px solid #666; background-color:white; width:120px; text-align:center">$item[velocidad]</td>
        <td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">$item[elctrica]</td>
        </tr>
    </table>
    <br>
    <HR>
    <br>
    <table style="font-size:10px; padding:10px 5px;">
		<tr>
        <td style="border: 1px solid #666; background-color:silver; width:100px; text-align:center">OBSERVACIONES</td>
        <td style="border: 1px solid #666; background-color:white; width:300px; text-align:left">$item[descripciond]</td>
        </tr>
    </table>


EOF;

$pdf->writeHTML($bloque6, false, false, false, false, '');

}
$pdf->Text(130, 265, '---------------------------------------');
$pdf->Text(130, 270, 'FIRMA DEL BENEFICIARIO');
$pdf->Text(25, 265, '------------------------------------------');
$pdf->Text(25, 270, 'SELLO Y FIRMA DEL TALLER');

$pdf->Output('pdf.php');

}

}


$a = new ImprimirReporte();
$a -> codigo = $_GET["codigo"];
$a -> traerImpresionSolicitudCompra();

 ?>
 