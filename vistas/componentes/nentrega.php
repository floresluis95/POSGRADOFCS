<?php
	$Validar = new FuncionesControladores();
	$Validar -> ValidarSessionControlador();
	date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
	<div class="kt-header-mobile__logo">
	  <a href="demo9/index.html">
	  <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />>
	  </a>
	</div>
	<div class="kt-header-mobile__toolbar">
	  <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler"><span></span></button>

	  <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
	  <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
	</div>
  </div>
  <!-- end:: Header Mobile -->
  <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
	<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
	  <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">
	  
		<?php
			$NavBar = new FuncionesControladores();
			$NavBar -> NavBarControlador();
		?>
		<button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
		<?php
			$Sidebar = new FuncionesControladores();
			$Sidebar -> SidebarControlador();
		?>


		<div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
		  <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

			<!-- begin:: Subheader -->
			<div class="kt-subheader   kt-grid__item" id="kt_subheader">
			  <div class="kt-container ">
				<div class="kt-subheader__main">
				  <h2 class="">

					ALMACEN </h2>

				  <span class="kt-subheader__separator kt-hidden"></span>
				  <div class="kt-subheader__breadcrumbs">
					<a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
					<span class="kt-subheader__breadcrumbs-separator"></span>
					
					 <h3> RECEPCION DE KIT'S GNV</h3>
					<!-- <span class="kt-subheader__breadcrumbs-link kt-subheader__breadcrumbs-link--active">Active link</span> -->
				  </div>
				</div>
				<div class="kt-subheader__toolbar">
				  <div class="kt-subheader__wrapper">
				  <div id="lafecha" style="font-size:13pt"></div>
				  </div>
				</div>
			  </div>
			</div>
			<!-- end:: Subheader -->
			
			<!-- begin:: Content -->
			<div class="kt-container">
			
				<div class="row justify-content-md-rihgt ">
					<div class="col-lg-12">
						<div class="kt-portlet">
							<div class="kt-portlet__head">
								<div class="kt-portlet__head-label">
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
									<i class="kt-menu__link-icon flaticon-add  "></i> Nueva recepcion
									</button>									
								</div>	
								<div class="kt-portlet__head-label">
								<div class="input-group mb-3">
  									<button class="btn btn-outline-secondary" type="button" id="button-addon1">Buscar</button>
 									 <input type="date" class="form-control" placeholder=""  aria-describedby="button-addon1">
									</div>									
								</div>													
							</div>
							
						</div>				
					</div>			
				</div>
			</div>
			<div class="kt-container">
				<div class="row justify-content-md-center ">
					<div class="col-lg-8">
						<div class="kt-portlet">
						<form action="">
							<div class="kt-portlet__head">
								
								<table class="table " id="tabladetalle">
									<thead>
									<tr>
									<th scope="col">N°</th>
										<th scope="col">Fecha de recepcion</th>
										<th scope="col">Codigo</th>
										<th scope="col">Usuario</th>
										<th scope="col">Tipo</th>
										<th scope="col">Detalle</th>
									</tr>
									</thead>
									<tbody>
									<?php
										$listanotakit = new NotaEntrgaControladores();
									   $listanotakit -> ListaNotaentregakitControlador();
									?>
									</tbody>
								</table>
							</div>
							<center>
							<button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdf.php?codigo=6" style="color:white;">REPORTE NOTAS</a><i class="fa fa-print" aria-hidden="true"></i></button>

							</center>
						
							</form>
						</div>
					</div>
				</div>
			</div>
<!-- Modal nota d entrega -->
<div class="modal fade bd-example-modal-sm" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Registrar nota de entrega</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
			</div>
			<form method="post">
				<div class="modal-body">
					<input type="text" title="Se necesita el codigo" id="txtnotak" class="form-control" name="NotaEntregakit" autocomplete="off" required/>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Guardar</button>
					<!-- guardar nota de entrega -->
					<?php
						$regnotakit = new NotaEntrgaControladores();
						$regnotakit -> RegistrarNotaEntregaControlador();
					?>
				</div>
			</form>
		</div>
	</div>
</div>
</div>

		


<?php
			$Footer = new FuncionesControladores();
			$Footer -> FooterControlador();
		?>


	  </div>
	</div>
  </div>
</body>
<div class="modal fade" id="listadenotaskit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Nota de entrega->Detalle de recepcion de Kits</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
				<form class="kt-form kt-form--fit kt-form--label-right">
					<div class="kt-container">
						<div class="row justify-content-md-center ">
							<div class="col-lg-12">
								<table class="table table-responsive" id="mitabla" >
									<thead>
										<tr>
											<th scope="col">Serie</th>
											<th scope="col">Descripcion</th>
											<th scope="col">tipo</th>
											<th scope="col">Nota de ntrega</th>		
										</tr>
									</thead>
									<tbody class="detallenotakit">
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					</div>
				</form>
            </div>
		</div>
    </div>
</div>