<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
	date_default_timezone_set("America/La_Paz");
	
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
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


        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body"  style="background-color: #EAEAEA;">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader" >
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2>

                    ORDEN DE TRABAJO </h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>
                      PROGRAMAR TRABAJO </h3> 
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
		
		

		
            <div class="kt-container" style="color: #000">
				<div class="row justify-content-md-center ">
					
					<div class="col-lg-12">
						<div class="kt-portlet"><div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
				<h3 class="kt-portlet__head-title" style="color: #095764">
						BUSCAR SOLICITUD
					</h3>
				</div>
			</div>
						
		<form class="kt-form kt-form--label-right" method="POST">
								<div class="kt-portlet__body">
									<div class="form-group row form-group-marginless kt-margin-t-20">
										<label class="col-lg-1 col-form-label">Solicitud:</label>
										<div class="col-lg-3">
											<div class="form-group row">
											<select class="form-control kt-select2 kt-select2-general" name="idsolicitud" id="idsolicitud">
											<option>N° de solicitud</option>
											<?php
		                 					       	$Listasolicitud = new  SolicitudesControladores();
		                						    $Listasolicitud-> ListaSolicitudesControlador();
	                	 					?>
											</select>
											</div>
										</div>
									</div>	  
									<div class="form-group row form-group-marginless">
										<label class="col-lg-1 col-form-label">C.I.:</label>
										<div class="col-lg-2">
											<input type="text" readonly class="form-control" id="cisol" placeholder="C.I.">
										</div>
									</div>	  
									<div class="form-group row">
										<label class="col-lg-1 col-form-label">Nombre:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly class="form-control" id="nombresol" placeholder="Apellido Materno">
											</div>
										</div>
										<label class="col-lg-2 col-form-label">Apellido Materno:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly class="form-control" id="maternosol" placeholder="Apellido Materno">
											</div>
										</div>
										<label class="col-lg-2 col-form-label">Apellido Materno:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly  class="form-control" id="paternosol" placeholder="Apellido Materno">
											</div>
										</div>							
									</div>
									<div class="form-group row form-group-marginless">
										<label class="col-lg-1 col-form-label">Placa:</label>
										<div class="col-lg-2">
											<input type="text" readonly class="form-control" id="placasol" placeholder="Placa">
										</div>
									</div>	  
									<div class="form-group row">
										<label class="col-lg-1 col-form-label">Marca:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly  class="form-control" id="marcasol" placeholder="Marca">
											</div>
										</div>
										<label class="col-lg-2 col-form-label">TipoMotor:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly class="form-control" id="tipomsol" placeholder="Motor">
											</div>
										</div>
										<label class="col-lg-2 col-form-label">fecha de solicitud:</label>
										<div class="col-lg-2">
											<div class="kt-input-icon">
												<input type="text" readonly class="form-control" id="fechasol" placeholder="fecha de solicitud">
											</div>
										</div>							
									</div>	         	               
								</div>
							
		<div class="kt-portlet" >
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<h3 class="kt-portlet__head-title" style="color: #095764">
						REGISTRO DE EQUIPO GNV
					</h3>
				</div>
			</div>
			<!--begin::Form-->
			
          			
				<div style="color: #000" class="kt-portlet__body">
				
					<div class="form-group row">
					
						<label class="col-lg-2 col-form-label">Kit:</label>
						<div class="col-lg-3">
							<select class="form-control kt-select2 kt-select2-general" name="seriekit">		
									<?php
		                 				$Listakit = new  ListaKitAsignarControladores();
		                				$Listakit-> ListaKitControlador();
	                	 			?>
							</select>
						</div>
						<label class="col-lg-2 col-form-label">Cilindro:</label>
						<div class="col-lg-3">
							<select class="form-control kt-select2 kt-select2-general" name="seriecilindro">			
									<?php
		                 				$Listacil = new  ListacilAsignarControladores();
		                				$Listacil-> ListacilControlador();
	                	 			?>
							</select>							
						</div>
					</div>	     
					<div class="form-group row">
						<label class="col-lg-2 col-form-label">Tecnico:</label>
						<div class="col-lg-3">
							<select class="form-control kt-select2 kt-select2-general" name="idtecnico">
									<?php
		                 				$Listatecnico = new  TecnicoControladores();
		                				$Listatecnico-> ListaTecnicoControlador();
	                	 			?>
							</select>
						</div>
						<label class="col-lg-2 col-form-label">Fecha de trabajo:</label>
						<div class="col-lg-3">
							<input type="date" class="form-control"  id="fecha" placeholder="registra una fecha" name="fechatrabajo"  required min=<?php $hoy=date("Y-m-d"); echo $hoy;?> />
							

						</div>
					</div>	               
	            </div>
				<center>
				<div class="kt-portlet__foot kt-portlet__foot--fit-x">
					<div class="kt-form__actions">
						<div class="row">
							<div class="col-lg-2"></div>
							<div class="col-lg-7">
								<button type="submit"  style="background-color: #0385CD;" class="btn btn-success">PROGRAMAR TRABAJO</button>
							</div>
						</div>
					</div>
				</div>
				</center>
								<?php
									$regpr = new PRTrabajoControladores();
									$regpr -> PRTrabajoControlador();
								?>
									</center>
		</form>
			<!--end::Form-->
		</div>
						</div>
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