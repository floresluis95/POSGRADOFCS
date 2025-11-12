<?php
date_default_timezone_set('America/La_Paz');
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
	$tipo = $value['desctipo'];
	$TipoMotor = $value['tipomotor'];
	$Cilindrada = $value['cilindrada'];
	$IdSolicitud = $value['IdSolicitud'];
	$propietario = $value['nombre'].' '. $value['paterno'].' '. $value['materno'];
	$ci=$value['ci'];
}
// $Venta = ModeloVentas::mdlMostrarAdmVentasDetalle($IdVenta);



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
                <td style="width:80px"><img src="images/cil.jpg"></td>
				</div>	
			</td>		
		</tr>
    </table>
<hr width="100%" />
EOF;

date_default_timezone_set("America/La_Paz");
$fechaconcluido='Oruro '.date('j-F-o');
$sol=$_GET['IdSolicitud'];

$pdf->writeHTML($bloque1, false, false, false, false, '');
$bloque2 = <<<EOF
<table>
	<div style="font-size:12px; text-align:rigth; line-height:15px;">
	$fechaconcluido
	</div>
    </table>
<table>
	<div style="font-size:12px; text-align:left; line-height:15px;">

	<p>Señora</p>
	<p>Ing. Esther Alejandra Huaylla Vargas</p><br>
	DIRECTORA GENERAL EJECUTIVA<br>
	ENTIDAD EJECUTIVA DE CONVERSION A GNV
	<br>
	Presente.-
	</div>
    </table>
EOF;
$pdf->writeHTML($bloque2, false, false, false, false, '');
// ---------------------------------------------------------
$bloque3 = <<<EOF
<table>
	<div style="font-size:12px; text-align:center; line-height:15px;">
	
	REF :SOLICITUD DE CONVERCION A GNV 
	<br>
	Nro. de solicitud: $sol
	
	<br>
	</div>
	<div style="font-size:12px; text-align:left; line-height:15px;">
	Mediante la presente me es muy grato poder salidarla, deseandole exito en las
	funciones que desmepeña, al mismo tiempo acogiendome a la politica energetico
	emprendido por el Estado Plurinacional de Bolivia mediante la Entidad Ejecutora de 
	Conversion a Gas Natural Vehicular que usted responsablemente dirige, SOLICITO LA CONVERSION 
	A GNV de mi vehiculo cuyas caracterisiticas se desglosa a continuación.
	<br>
	
	</div>
    </table>
EOF;
$pdf->writeHTML($bloque3, false, false, false, false, '');
$bloque4 = <<<EOF

	<table>
   	 	<div style="font-size:10px; text-align:center; line-height:12px;">
			<p>Placa: $Placa</p>
			<p>Marca: $Marca</p>
			<p>Modelo: $Modelo</p>
			<p>Tipo: $tipo</p>
			<p>Tipo motor:$TipoMotor</p>
			<p>Cilindrada:$Cilindrada</p>
		</div>
    </table>
EOF;
$pdf->writeHTML($bloque4, false, false, false, false, '');
$bloque5 = <<<EOF

	<table>
   	 	<div style="font-size:12px; text-align:left; line-height:15px;">
			<p>Sin otro particular motivo y con la seguridad de contar con su colaboración me despido con las consideraciones más distinguidas.</p>
		<br>
		<p>Atentamente.</p>
			</div>
    </table>
EOF;
$pdf->writeHTML($bloque5, false, false, false, false, '');

foreach($Solicitud as $row => $item){
	// $subtotal = $item[DVPrecioVenta] * $item[DVCantidad];
$bloque4 = <<<EOF



EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}

$pdf->Text(70, 265, $propietario);
$pdf->Text(90, 270, 'C.I.'.$ci);

$pdf->Output('pdf.php');

}

}

$a = new ImprimirReporte();
$a -> IdSolicitud = $_GET["IdSolicitud"];
$a -> traerImpresionSolicitudCompra();

 ?>
 