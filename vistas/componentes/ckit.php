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
      $CodRecepcion = $_GET['CodRecepcion'];
      $ListaDetalle = MarcaKitModelos::ListaDetalleKitModelo($CodRecepcion);
    ?>
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 class="">

                    EQUIPOS GNV </h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>    
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
				<div class="row justify-content-md-left">
					

          <div class="col-lg-2">
						<div class="kt-portlet">
							
							<button type="submit" class="btn btn-primary" >	<a href="extensiones/tcpdf/pdf/pdfkitdisponbles.php" style="color:white;">IMPRIMIR</a><i class="fa fa-print" aria-hidden="true"></i></button>

							</center>
						</div>
					</div>
				</div>
			</div>

  
                            <div class="kt-container">
                        <div class="row justify-content-md-center ">
                          <div class="col-lg-12">
                            <div class="kt-portlet">
                              <div class="kt-portlet__head">
                                <div class="kt-portlet__head-label">
                                    <form class="kt-form kt-form--label-right" method="POST">
                                        <div class="modal-body">
                                            <div class="kt-portlet__body">
                                                <h3 class="float-right"><i class="kt-menu__link-icon fa fa-angle-double-right"></i> BUSCAR KIT</h3>
                                                <div class="form-group row form-group-marginless kt-margin-t-20">
                                                    <label class="col-lg-3 col-form-label">SERIE</label>
                                                <div class="col-lg-6">
                                                    <input type="text" class="form-control" name="seriekit" placeholder="Serie del kit" required >
                                                </div>    
                                                <div class="col-lg-3" id="seriekit">
                                                    <button type="submit" class="btn btn-success btn-custom" id=""> BUSCAR</button>
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
    <div class="col-lg-12">
      <div class="kt-portlet">
			  <div class="kt-portlet__head">
        <table class="table">
             <thead>
                      <tr>
                            <th WIDTH="">solicitud</th>
                            <th WIDTH="">fecha solicitud</th>
                            <th WIDTH="">Placa</th>
                            <th WIDTH="">fecha detrabajo</th>				  									
										        <th scope="">fecha concluido</th>
                            <th WIDTH="">Nombres</th>						
										        <th scope="">DETALLE</th>
                      </tr>
              </thead>
              <tbody>
  
                  <tfoot>
                           <?php
                          $ver = new BuscarkitControladores();
                          $ver -> BuscarkitControlador();
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

</div> <!--pie de plantilla-->
         <?php
          $Footer = new FuncionesControladores();
         $Footer -> FooterControlador();
          ?>
      </div>
    </div>
  </div>
</body>


<?php
      $Solicitud = ConsultakitModelos::pdfKitdisponibles();
      $cantidad = ConsultakitModelos::pdfKitdisponiblesinyeccioncarburador();
    ?>