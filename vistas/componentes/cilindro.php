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
                      NOTAS DE ENTREGA</a> 
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
  

<div class="kt-container">
<div class="row justify-content-md-rihgt ">
<div class="col-lg-12">
  <div class="kt-portlet">
			<div class="kt-portlet__head">
				<div class="kt-portlet__head-label">
          <button data-toggle="modal" data-target="#ModalNuevaRecepcion" class="btn btn-primary"><i class="far fa-clipboard"></i>Nueva Recepcion</button>
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
			  <div class="kt-portlet__head">
        <table class="table">
  <thead>
    <tr>
   
      <th scope="col">codrecepcion</th>
      <th scope="col">Fecha de recepcion</th>
      <th scope="col">Codigo</th>
      <th scope="col">Usuario</th>
      <th scope="col">Tipo</th>
      <th scope="col">Detalle</th>
    </tr>
  </thead>
  <tbody>
  
  <?php
          $ListaNotas = new NotasentregaControladores();
         $ListaNotas -> ListaNotaControladores();
          ?>
  </tbody>
</table>

			  </div>
	    </div>
    </div>
  </div>
</div>
		<!--Modal Nueva recepcion-->
<div class="modal fade" id="ModalNuevaRecepcion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="far fa-clipboard"></i>NOTA DE ENTREGA</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <form method="post">
              <div class="modal-body">
                  <input type="text" class="form-control" name="NotaEntrega" required>
              </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <div>
                <button type="submit" class="btn btn-success btn-custom" id=""> Guardar</button>
                <?php
		            	$NotaEntrega = new MarcaKitControladores();
		          	  $NotaEntrega -> RegistrarNotaEntregaControlador();
		            ?>
                
              </div>
            </div>
            </form>
        </div>
    </div>
</div>
<!--detalle de nota kit-->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <h1>
       Detalle
    </h1>
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

