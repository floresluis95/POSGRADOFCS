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
				  <h2>

					ORDEN DE TRABAJO </h2>

				  <span class="kt-subheader__separator kt-hidden"></span>
				  <div class="kt-subheader__breadcrumbs">
					<a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
					<span class="kt-subheader__breadcrumbs-separator"></span>
					<h4>
					 TRABAJOS PROGRAMADOS
					</h4>
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
				<div class="row justify-content-md-center ">
					<div class="col-lg-8">
						<div class="kt-portlet">
						<form action="" method="POST">
							<div class="kt-portlet__head">
							
							<table class="table table-striped- table-bordered table-hover table-checkable TablaUsuarios" id="kt_table_1 " >
									<thead>
									<tr>
									<th scope="col">SOLICITUD</th>
										<th scope="col">FECHA ASGNACION</th>
										<th scope="col">PLACA</th>
                                        <th scope="col">TRABAJO</th>
										<th scope="col">CONCLUIR</th>
                                        
									</tr>
									</thead>
									<tbody>
									<?php
										$asignacion = new ListaDeAsignacionControladores();
										$asignacion -> ListaDeAsignacionTEcControladore();
									?>
                                   
									</tbody>
								</table>
							</div>
						
							</form>
						</div>
					</div>
				</div>
			</div>
<!-- Modal nota d entrega -->
        </div>
        </div>
    </div>
    </body>


<div class="modal fade" id="modalt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">CONCLUIR TRABAJO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
      <input type="hidden"  id="idtrabajo" name="idt">
				<h4>Fecha de conversion: </h4>
				<input type="date"  readonly id="txtfecha" name="txtfecha" class="form-control" value="<?php echo date('Y-n-j'); ?>" /><br>
				<div class="row">
					<div class="col">
					<input type="checkbox" id="op1"  name="c1" value="SI" style="width:20px;height:20px;"> <label for="op1">PRUEBA DE INYECTORES</label><br />
					<input type="checkbox"  id="op2"  name="c2" value="SI" style="width:20px;height:20px;"> <label for="op2">PRUEBA DE ARRANQUE</label><br />
					<input type="checkbox"  id="op3" name="c3"  value="SI" style="width:20px;height:20px;"> <label for="op3">PRUEBA DE ACELERACION</label><br />
					</div>
					<div class="col">
					<input type="checkbox" id="op4"  name="c4" value="SI" style="width:20px;height:20px;"> <label for="op4">PRUEBA DE VELOCIDAD</label><br />
					<input type="checkbox"  id="op5"  name="c5" value="SI" style="width:20px;height:20px;"> <label for="op5">EVALUCAION ELECTRICA </label><br />
					</div>
				</div>
				<div class="row">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">DESCRIPCION</span>
					</div>
					<textarea class="form-control" aria-label="With textarea" name="descripciond"></textarea>
					</div>
				</div>
	  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Aceptar</button>
	
      </div>
	  </form>
	  <?php
             $Registrot = new ListaDeAsignacionControladores();
            $Registrot -> trabajosControlador();
		?> 
    
    </div>
  </div>
</div>



