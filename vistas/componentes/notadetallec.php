<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
   
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
    <div class="kt-header-mobile__logo">
      <a href="demo9/index.html">
         <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
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
     <?php
      $CodRecepcion = $_GET['CodRecepcioncil'];
      $ListaDetalle = ListaNotaModelos::ListaDetallecilModelo($CodRecepcion);
      $nota = $_GET['notac'];
    ?>
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h3 class="kt-subheader__title">

                    ALMACEN </h3>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      REGISTRO DE CILINDROS </a> 
                      
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
  
<!--tabla kit-->

  
  <div class="kt-container">
				<div class="row justify-content-md-center ">
					<div class="col-lg-12">
						<div class="kt-portlet">
							<div class="kt-portlet__head">
								<div class="kt-portlet__head-label">
      <form class="kt-form kt-form--label-right" method="POST">
              <div class="modal-body">
                <!--begin::Form-->
			
				<div class="kt-portlet__body">
        <h3 class="float-right">
									<i class="kt-menu__link-icon fa fa-angle-double-right"></i>
                  <?php echo "". $nota; ?>
						  </h3>
          <div class="form-group row form-group-marginless kt-margin-t-20">
           <?php echo $CodRecepcioncil; ?>
					</div>
        <div class="form-group row form-group-marginless kt-margin-t-20">
						<label class=" col-form-label">Serie:</label>
						<div class="col-lg-2">
              <input type="text" class="form-control" name="seriecil" placeholder="Serie del cilindro" >
						</div>
						<label class="col-lg-1 col-form-label">Marca:</label>
                <div class="col-lg-2" id="cargarmarcacil">
                    <select class="form-control" name="idmarca" >
                          <option>Seleccionar</option>
                           <?php
		                        	$ListaMarcaCil = new  MarcaCilindroControladores();
		                	        $ListaMarcaCil-> ListaMarcaCilindroControlador();
	                	       ?>
                    </select>
                </div>
                <div >
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
										+
				</button> 
				</div>
     
						<label class="col-lg-1 col-form-label">Capacidad:</label>
						<div class="col-lg-2">
                    <select class="form-control" name="capacidad" >
                          <option value="40 L." >40 L.</option>
                          <option Value="50 L." >50 L.</option>
                          <option value="60 L." >60 L.</option>
                          <option Value="80 L." >80 L.</option>
                          <option Value="100 L." >100 L.</option>
                    </select>      
            </div>
            <label class=" col-form-label">Fecha fab:</label>
						<div class="col-lg-2">
              <input type="date" class="form-control" name="aofab" >
                 
            </div>
						<div class="">
            <button type="submit" class="btn btn-success btn-custom" id=""> Guardar</button>
                <?php
		                        	$RegistrarCil = new  RegistrarCilindroControlador();
		                	        $RegistrarCil-> RegistrarCilControlador();
	              ?> 
            </div>
				</div>	  					               
			
        
            </div>
    </form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

<!--NTABLE -->

  <div class="kt-container">
  <div class="row justify-content-md-center ">
    <div class="col-lg-8">
      <div class="kt-portlet">
			  <div class="kt-portlet__head">
        <table class="table">
             <thead>
                      <tr>
                            <th WIDTH="50">ID</th>
                            <th WIDTH="180">Serie</th>
                            <th WIDTH="180">Marca</th>
				  									<th WIDTH="180">Capacidad</th>
                            <th WIDTH="180">Año de fab</th>
										        
                      </tr>
              </thead>
              <tbody>
  
                  <tfoot>
                  <?php
                    foreach ($ListaDetalle as $key => $Kit) {
                      $i++;
                      echo '<tr>
                            <td>'.$i.'</td>
                            <td>'.$Kit['seriecilindro'].'</td>
                            <td>'.$Kit['descripcioncil'].'</td>
                            <td>'.$Kit['capacidad'].'</td>    
                            <td>'.$Kit['aofab'].'</td>          
                            <td>
                            <button type="button" class="btn btn-outline-info"><i class="kt-menu__link-icon fa flaticon2-rubbish-bin  "></i></button>
                            </td>
                          </tr>
                          ';
                    }
                  ?>
					        </tfoot>
              </tbody>
            </table>

			  </div>
	    </div>
    </div>
  </div>
</div>
	<!--tabla kit fin-->
  <!--boton imprimir-->
 <center>
 <button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfdetalledenotac.php?codigo=<?php echo $_GET['CodRecepcioncil']?>" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>

 </center>

</div>

<div class="modal fade bd-example-modal-sm" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Registrar nueva marca</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
			</div>
      <form method="post" enctype="multipart/form-data">
              <div class="modal-body">
                <h5>Registrar Marca </h5>
                  <input type="text" class="form-control" name="nmarcacil" required>
              </div>
              <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Guardar</button>
					<!-- guardar nota de entrega -->
					<?php
						$regmarcaCil = new MarcaCilindroControladores();
						$regmarcaCil -> RegistrarMarcaCilindroControlador();
					?>
				</div>
      </form>
		</div>
	</div>
</div>


	      <!--pie de plantilla-->
         <?php
          $Footer = new FuncionesControladores();
         $Footer -> FooterControlador();
          ?>
      </div>
    </div>
  </div>
</body>

