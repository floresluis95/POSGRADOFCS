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


        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 >

                    REPORTES </h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="fas fa-tools"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>
                      GENERAR INFORME DE CONVERSION
                    </h3>
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
				<div class="row justify-content-md-center ">
					<div class="col-lg-12">

          <div class="kt-portlet kt-portlet--mobile">
              <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
                  <span class="kt-portlet__head-icon">
                  <i class="fas fa-tools"></i>
                  </span>
                  <h3 class="kt-portlet__head-title">
                  TRABAJOS CONCLUIDOS
                  </h3>
                </div>
              </div>
                <div class="kt-portlet__body">
                  <!--begin: Datatable -->
                  <table class="table table-striped- table-bordered table-hover table-checkable" id="kt_table_1">
                    <thead>
                    <tr>
                    <th scope="col">Solicitud</th>
                      <th scope="col">Fecha de solicitud</th>
                      <th scope="col">Placa</th>
                      <th scope="col">Fecha asignacion</th>
                      <th scope="col">Fecha de conversion</th>
                      <th scope="col">Tecnico</th>
                      <th scope="col">Estado</th>		
                      <th scope="col"></th>		
                    </tr>
                  </thead>
                    <tbody>
                    <?php
                        $asignacion = new ListaDeTrabajosControladores();
                        $asignacion -> ListaDeTrabajosConcluidosControlador();
                      ?>
                    </tbody>
					      </table>
		<!--end: Datatable -->
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