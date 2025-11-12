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
  <div class="kt-grid kt-grid--hor kt-grid--root" style="background-color: #EAEAEA; font-size:12pt;">
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


        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body" >
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 class="">

                  ORDEN DE TRABAJO</h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
					<h3>TRABAJO PARTICULAR</h3> </a> 
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
 <!--SOLICITUD"></i>-->

        <div class="kt-container  kt-grid__item kt-grid__item--fluid">
            <div class="row justify-content-md-center">
                <div class="col-lg-11">
                <div class="kt-portlet">
			<!--Cliente-->
		<form class="kt-form kt-form--label-right" method="POST">
						<div class="kt-portlet__body">
						</div>
							<div class="kt-portlet__head">
								<div class="">
									<h3 class="">
									<img src="vistas/recursos/assets/media/icons/per.png" width="40"" alt="">DATOS DEL CLIENTE
									</h3>
								</div>
								<?php $Ultimatr = HeredadoModelos::UltimoIdModelo('codcontrato', 'contrato') + 1; ?>
									<h3 class="float-right">
									<i class="kt-menu__link-icon fa fa-file-word"></i>

										<?php echo 'TRABAJO-'.$Ultimatr; ?>
									</h3>
							</div>
				<div class="kt-portlet__body">

					<div class="form-group row" >
						<label class="col-lg-2 col-form-label" >CLIENTE:</label>
						<div class="col-lg-5">	
						<div>
							<select class="form-control kt-select2 kt-select2-general" name="idcliente" required>
							<option >Buscar cliente por cedula de identidad</option>
							<?php
								$Lista = new  PropietarioControladores();
								$Lista-> ListaPropietarioControlador();
							?>
							</select>
						</div>
						</div>
						
						<div class="col-lg-4">
						<button type="button"  class="btn btn-outline-primary"  data-toggle="modal" data-target="#ncliente"><img src="vistas/recursos/assets/media/icons/svg/Communication/Add-user.svg"/></button>
						</div>
					</div>	     
	            </div>
			
			</div>
		</div>
		</div>
        	</div>
			</div>
			</div>

			<div class="kt-container  kt-grid__item kt-grid__item--fluid">
            <div class="row justify-content-md-center">
                <div class="col-lg-11">
                <div class="kt-portlet">
				<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
					<h3>
					<img src="vistas/recursos/assets/media/icons/car.png" width="55"" alt="">DATOS DEL VEHICULO
					</h3>
				</div>
			</div>
			<!--Cliente-->
				<div class="kt-portlet__body">
					<div class="form-group row form-group-marginless kt-margin-t-20">
						<label class="col-lg-1 col-form-label">PLACA:</label>
						<div class="col-lg-2">
							<input type="text" name="nroplaca" class="form-control" placeholder="Placa" title="Ingrese la placa" required autocomplete="off">
							
						</div>
						<label class="col-lg-1 col-form-label">MARCA:</label>
						<div class="col-lg-2" id="select1">
						<select class="form-control" name="lista1" id="SelectMarca" required title="Selecciones una marca">
						<option></option>
                   		 	<?php
							$ListaMarca = new  MarcaControladores();
							$ListaMarca-> ListaMarcaControlador();
							?>
						</select> 
						</div>
						<div class="col-md-1">
            				  <div class="form-group">
			 				 	<button data-toggle="modal" data-target="#exampleModal" type="button" class="btn btn-info btn-sm round btn-min-width mr-4 mb-1">
							 	 +</button>
            				  </div>		  
            			</div>
						<label class=" col-form-label">TIPO:</label>
						<div class="col-lg-2" id="select2lista">
						<select class="form-control" name="lista2" id="SelectTipo" required>
                   		 	
							
						</select> 
						</div>
						<div class="col-md-1">
            				  <div class="form-group">
			 				 	<button data-toggle="modal" data-target="#exampleModaltipo" type="button" class="btn btn-info btn-sm round btn-min-width mr-4 mb-1">
							 	 +</button>
            				  </div>		  
            			</div>
					</div>	 
					<div class="form-group row form-group-marginless">
						<label class="col-lg-1 col-form-label">CLASE:</label>
						<div class="col-lg-2">
						<select class="form-control" name="clase" required title="Seleccione tipo">
						<option></option>
								<option value="MINIBUS">MINIBUS</option>
								<option value="VAGONETA">VAGONETA</option>
								<option value="AUTOMOVIL">AUTOMOVIL</option>
							</select>
						</div>
						<label class="col-lg-1 col-form-label">MODELO:</label>
						<div class="col-lg-2">
							<div class="kt-input-icon">
							<select class="form-control" name="modelo" required title="Seleccione el modelo">
							<option></option>
								<option value="2000">2000</option>
								<option value="2001">2001</option>
								<option value="2002">2002</option>
								<option value="2003">2003</option>
								<option value="2004">2004</option>
								<option value="2005">2005</option>
								<option value="2006">2006</option>
								<option value="2007">2007</option>
								<option value="2008">2008</option>
								<option value="2009">2009</option>
								<option value="2010">2010</option>
								<option value="2011">2011</option>
								<option value="2012">2012</option>
								<option value="2013">2013</option>
								<option value="2014">2014</option>
								<option value="2015">2015</option>
								<option value="2016">2016</option>
								<option value="2017">2017</option>
								<option value="2018">2018</option>
								<option value="2019">2019</option>
							</select>
							</div>
						</div>
						<label class="col-lg-1">TIPO MOTOR:</label>
						<div class="col-lg-2">
							<select class="form-control" name="tipomotor" required title="Seleccione un el tipo de motor">
							<option></option>
								<option value="INYECCION">INYECCION</option>
								<option value="CARBURADOR">CARBURADOR</option>
							</select>
						</div>
					</div>	 
					<hr>  
					<div class="form-group row ">
						<label class="col-lg-1 col-form-label">CILINDRADA:</label>
						<div class="col-lg-2">
							<div class="kt-input-icon">
								<input type="text" class="form-control" name="cilindrada" maxlength="4" required onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
							</div>
						</div>
						<label class="col-lg-1 col-form-label">SERVICIO:</label>
						<div class="col-lg-2">
						<select class="form-control" name="tipotransporte" required>
						<option></option>
								<option value="PUBLICO">PUBLICO</option>
								<option value="PARTICULAR">PARTICULAR</option>
							</select>
						</div>
                        
					</div>
					<div class="form-group row form-group-marginless kt-margin-t-20">
								<label class="col-lg-1 col-form-label">Serie:</label>
								<div class="col-lg-2">
									<input type="text" class="form-control" name="seriekit" placeholder="Serie del kit"  >
								</div>
								<label class="col-lg-1 col-form-label">Marca:</label>
								<div class="col-lg-3" id="cargarmarcakit">
									<select class="form-control" name="idmarca" >
											<option>Seleccionar</option>
											<?php
													$ListaMarca = new  MarcaKitControladores();
													$ListaMarca-> ListaMarcaKitControlador();
												?>
									</select>
								</div>
								<div >
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">+</button>				
								</div>
								<label class="col-lg-1 col-form-label">Tipo:</label>
							<div class="col-lg-3">
								<select class="form-control" name="idtipokit" >
										<option value="CARBURADOR" >CARBURADOR</option>
										<option Value="INYECCION" >INYECCION</option>
								</select>      
							</div>
								<div class="col-lg-2">
								<?php
											$RegistrarKIT = new  MarcaKitControladores();
											$RegistrarKIT-> RegistrarKitControlador();
								?> 
						</div>
						</div>	


						<div class="form-group row form-group-marginless kt-margin-t-20">
								<label class="col-lg-1 col-form-label">Serie:</label>
								<div class="col-lg-2">
									<input type="text" class="form-control" name="seriecilindro" placeholder="Serie del cilindro"  >
								</div>
								<label class="col-lg-1 col-form-label">Marca:</label>
								<div class="col-lg-3" id="cargarmarcakit">
									<select class="form-control" name="idmarcac" >
											<option>Seleccionar</option>
											<?php
		                        	$ListaMarcaCil = new  MarcaCilindroControladores();
		                	        $ListaMarcaCil-> ListaMarcaCilindroControlador();
	                	       ?>
									</select>
								</div>
								<div >
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">+</button>				
								</div>
								<label class="col-lg-1 col-form-label">CAPACIDAD:</label>
							<div class="col-lg-3">
								<select class="form-control" name="capacidad" >
									<option value="40 L." >40 L.</option>
									<option Value="50 L." >50 L.</option>
									<option value="60 L." >60 L.</option>
									<option Value="80 L." >80 L.</option>
									<option Value="100 L." >100 L.</option>
								</select>   
								</select>      
							</div>
								<div class="col-lg-2">
								<?php
												$RegistrarCil = new  RegistrarCilindroControlador();
												$RegistrarCil-> RegistrarCilControlador();
								?> 
						</div>
						</div>	  					               									
				</div>  					               									
				</div>	   			
	            <div class="kt-portlet__foot">
					<div class="kt-form__actions">
						<div class="row">
							<div class="col-lg-5"></div>
							<div class="col-lg-7">
								<button type="submit"   style="background-color: #0385CD;" class="btn btn-brand">REGISTRAR TRABAJO</button><br>
							</div>
							
						</div>
					</div>		

				</div>
				</div>		

				</div>
				<?php
					$registrocon= new Contratocontroladores();
					$registrocon -> RegistrarVehiculo();
				?>
			</form>
		
		
		  		
		

	

	<!--VEHICULO-->
	
	

	
 <!--SOLICITUD"-->
	<!--modal cliente-->

	  
	<!--modal vehiculo-->


               </div>
           
		<?php
             $RegistroCliente = new PropietarioControladores();
            $RegistroCliente -> RegistrarPropietarioControlador();
		?> 
			
      
			</div>
    </div>
  </div>
</body>


		  <?php
			$Footer = new FuncionesControladores ();
			$Footer -> FooterControlador();
		?>
		  



<div class="modal fade" id="ncliente" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
			<img src="vistas/recursos/assets/media/icons/svg/Communication/Add-user.svg"/><h5 class="modal-title" id="">REGISTRAR CLIENTE</h5>
				<button type="submint" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" class="la la-remove"></span>
				</button>
			</div>
			<form class="kt-form kt-form--fit kt-form--label-right" method="POST">
				<div class="modal-body">
       				<div class="form-group row kt-margin-t-20">
						<label class="col-form-label col-lg-3 col-sm-12">CI:</label>
						<div class="col-lg-4 col-md-5 col-sm-5">
							<div class="typeahead">
								<input class="form-control" name="ci" id="ci" type="text" placeholder="C. I." required maxlength="9" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-3 col-md-5 col-sm-5">
							<div class="typeahead">
							<select class="form-control" name="exp" required>
						
								<option value="OR">OR.</option>
								<option value="CBBA.">CBBA.</option>
								<option value="PT.">PT.</option>
								<option value="SCZ.">SCZ.</option>
								<option value="LP.">LP.</option>
								<option value="CH.">CH.</option>
								<option value="TJ.">TJ.</option>
								<option value="BE.">BE.</option>
								<option value="PD.">PD.</option>
								<option value="EXT.">EXT.</option>
							</select>							
							</div>
						</div>
					  </div>
					  <div class="form-group row">
						<label class="col-form-label col-lg-3 col-sm-12">NOMBRES:</label>
						<div class="col-lg-7 col-md-9 col-sm-12">
							<div class="typeahead">
								<input class="form-control" name="nombre" id="" type="text" placeholder="Nombres" pattern="[A-Za-z ]+" title="No indtrodusca números" autocomplete="off" required>
							</div>
						</div>
					</div>
					<div class="form-group row kt-margin-t-20">
						<label class="col-form-label col-lg-3 col-sm-12">APELLIDO PATERNO:</label>
						<div class="col-lg-7 col-md-7 col-sm-7">
							<div class="typeahead">
								<input class="form-control" name="paterno" id="" type="text" placeholder="Apellido Paterno" pattern="[A-Za-z]+" title="No indtrodusca números" autocomplete="off" required>
							</div>
						</div>
          			</div>
          			<div class="form-group row kt-margin-t-20">
						<label class="col-form-label col-lg-3 col-sm-12">APELLIDO MATERNO:</label>
						<div class="col-lg-7 col-md-7 col-sm-7">
           					<div class="typeahead">
							   <input class="form-control" name="materno" id="" type="text" placeholder="Apellido Materno" pattern="[A-Za-z]+" title="No indtrodusca números" autocomplete="off" required>
							</div>
						</div>
					</div>
         			 <div class="form-group row kt-margin-b-20">
						<label class="col-form-label col-lg-3 col-sm-12">TELEFONO:</label>
						<div class="col-lg-4 col-md-5 col-sm-5">
							<div class="typeahead">
           					   <input class="form-control" id="" name="telefono" type="text" placeholder="Telefono" onkeypress='return event.charCode >= 48 && event.charCode <= 57'/>
							</div>
						</div>
					</div>
					</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-success btn-custom" > Guardar</button>
				</div>
			</form>

		</div>
	</div>
</div>




<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar marca</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
	  <input name="descmarca" type="text" id=""  class="form-control" placeholder="Ingrese una marca" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
	
      </div>
	  <?php
             $Registromv = new VehiculoControladores();
            $Registromv -> RegistrarMarcaVControlador();
		?> 
	  </form>
    
    </div>
  </div>
</div>


<div class="modal fade" id="exampleModaltipo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar tipo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
	  <h5 class="modal-title" id="exampleModalLabel">Tipo</h5>
	  <br>
	  <input type="hidden" id="TIIdMarca" name="idmarca">
	  <input name="desctipo" type="text" id=""  class="form-control" placeholder="Ingrese tipo" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
	
      </div>
	  <?php
             $Regtipo = new MarcaControladores();
            $Regtipo -> RegistrarTipoControlador();
		?> 
	  
	  </form>
    
    </div>
  </div>
</div>