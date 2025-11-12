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

                  DETALLE DE MATRICULACION</h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
					            <h3> </h3> </a> 
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
									<img src="vistas/recursos/assets/media/icons/gr.png" width="40"" alt="">BUSCAR ESTUDIANTE
									</h3>
								</div>
								<?php $UltimaSolicitud = HeredadoModelos::UltimoIdModelo('codsolicitud', 'solicitud') + 1; ?>
									<h3 class="float-right">
									<i class="kt-menu__link-icon fa fa-file-word"></i>

										<?php echo 'SOLICITUD-'.$UltimaSolicitud; ?>
									</h3>
							</div>
				<div class="kt-portlet__body">

					<div class="form-group row" >
						<label class="col-lg-2 col-form-label" >ESTUDIANTE:</label>
						<div class="col-lg-5">	
						<div>
							<select class="form-control kt-select2 kt-select2-general" name="idcliente" required>
							<option >Buscar estudiante por cedula de identidad</option>
							<?php
								$Lista = new  EstudiantesControladores();
								$Lista-> EstudianteActivoControlador();
							?>
							</select>
						</div>
						</div>
						
						<div class="col-lg-4">
						<button type="button"  class="btn btn-outline-primary"  data-toggle="modal" data-target="#ModalInsertarEstudiante"><img src="vistas/recursos/assets/media/icons/bus.png" width="20"" alt=""></button>
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
                    <hr>
					<h3>
					<img src="vistas/recursos/assets/media/icons/inscripcion.png" width="40 " alt="">DETALLE DE PROGRAMAS
					</h3>
				</div>
			</div>

            </div>
            </div>
        </div>      
			</form>
    </div>
           
		<?php
             $RegistroCliente = new PropietarioControladores();
            $RegistroCliente -> RegistrarPropietarioControlador();
		?> 
			
			</div>
    </div>
  </div>
  <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>
		  <?php
			$Footer = new FuncionesControladores ();
			$Footer -> FooterControlador();
		?>

    <div class="modal fade" id="Modalmatriculacion" tabindex="-1" role="dialog" 
         aria-labelledby="Modalmatriculacion" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title text-white" id="Modalmatriculacion">
                        <i class="bi bi-person-plus-fill"></i> Detalle los datos de matriculacion
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form method="post" id="formNuevaMatricula" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <!-- Token CSRF -->
 
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar Matriculacion
                        </button>
                    </div>
                    
                    <?php
                        $DatosEstudiante = new EstudiantesControladores();
                        $DatosEstudiante->RegistarEstudianteControlador2();
                    ?>
                </form>
            </div>
        </div>
    </div>

   