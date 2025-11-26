<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
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
                  <h2 class="">

                    Personal </h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>
                  
                      Usuarios
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
    
<!-- end:: Subheader -->
<!-- begin:: Content -->
<!-- BEGIN: Contenedor-->



<div class="app-content content" >
  <div class="content-wrapper"  >
  
  <div class="content-body">
    <!-- HTML (DOM) sourced data -->
    <section id="html">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              
              <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                 
                  <button data-toggle="modal" data-target="#ModalInsertarUsuario" type="button" class="btn btn-info btn-sm round btn-min-width mr-1 mb-1">
                  <i class="fas fa-user-plus mr-1"></i>Nuevo usuario
                  </button>
                </ul>
              </div>
            </div>
            <div class="card-content  collapse show">
              <div class="card-body card-dashboard">
              
                <div class="table-responsive">


                
<div class="kt-portlet__body">
    <!--begin: Datatable -->
    <table class="table table-striped- table-bordered table-hover table-checkable TablaUsuarios" id="kt_table_1 " >
        <thead>
          <tr>
                  <th style="width:10px">Nº</th>
                  <th>CI</th>
                  <th>Nombres</th>
                  <th>DiRECCION</th>
                  <th>CELULAR</th>
                  <th>FECHA INGRESO</th>
                  <th>CARGO</th>
                  <th>ESTADO</th>          
                  <th>EDITAR</th>
                  <th>DAR BAJA</th>
                  <th>SUBIR</th>
          </tr>
          </thead>
      
        <tbody>
                     <?php
                       $ListaUsuarios = new UsuarioControladores();
                       $ListaUsuarios -> ListaUsuariosControlador();
                       
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
      </div>
    </section>
    <!--/ HTML (DOM) sourced data -->
  </div>
  </div>
</div>
<!-- END: Contenedor-->

<!-- Modal Insertar Usuario -->
<div style="z-index: 1500;" class="modal fade text-left" id="ModalInsertarUsuario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel9" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info white">
        <h4 class="modal-title white" id="myModalLabel9"><i class="fas fa-user-plus mr-1"></i> Nuevo usuario</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data" class="needs-validation">
       <div class="col-md-12">
              <h4 class="form-section">
                <i class="la la-arrow-right"></i> Datos del personal
              </h4>
           <div class="row">

            <div class="col-md-3">
              <div class="form-group" >
                  <label for="validationCustum01">C. I.</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="validationCustum01" name="ci" class="form-control" placeholder="C.I."   required onkeypress='return event.charCode >= 48 && event.charCode <= 57'/ title="No indtrodusca letras">
                  </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                  <label for="validationCustum02">Apellido Paterno</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="validationCustum02" name="apaterno" class="form-control" placeholder="Apellido paterno" required="" pattern="[A-Za-z]+" title="No introduzca números">
                  </div>
              </div>
            </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="validationCustum03">Apellido Materno</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="validationCustum03" name="amaterno" class="form-control" placeholder="Apellido materno" required="" pattern="[A-Za-z]+" title="No introduzca números">
                  </div>
                </div>
              </div>
               <div class="col-md-4">
                <div class="form-group">
                  <label for="validationCustum04">Nombres</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="validationCustum04"  name="nombres" class="form-control" placeholder="Nombre completo" required pattern="[A-Za-z ]+" title="No introduzca números">
                  </div>
                </div>
              </div>
          </div>
          <div class="row">
           <div class="col-md-6">
                <div class="form-group">
                  <label for="validationCustum05">Direccion</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="validationCustum05" name="direccion" class="form-control" placeholder="Direccion" required="" >  
                  </div>
                </div>
            </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="timesheetinput2">Celular</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="timesheetinput2" name="celular" class="form-control" placeholder="Celular"  >  
                  </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="form-group">
                  <label for="timesheetinput2">Telefono</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="timesheetinput2"  name="telefono" class="form-control" placeholder="Telefono" >  
                  </div>
                </div>
            </div>
          </div>
          </div>
          <div class="col-md-12">
              <h4 class="form-section">
                <i class="la la-arrow-right"></i> Datos de usuario
              </h4>
              <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="validationCustum06">Usuario</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="validationCustum06" name="usuario" class="form-control" placeholder="Usuario" required="" >                
                  </div>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-group">
                  <label for="validationCustum07">Cargo</label>
                  <div class="position-relative has-icon-left">
                    <select class="form-control" name="UICargo"  id="validationCustum07" required="">
                      <option disabled selected value="">Cargo</option>
                      
                      <option value="TEC">TECNICO</option>
                    </select>
                    
                  </div>        
                </div>
               </div>
               </div>       

            </div>
            <div class="col-md-12" >
                <div class="form-group">
                <button type="button" class="btn grey btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar</button>
                </div>
               </div>  
               
      </form>
      <?php
                $insusuario = new UsuarioControladores();
                $insusuario -> InsertarUsuarioControlador();
                
                ?>
    </div>
  </div>
</div>

<!-- Modal Editar Usuario -->
<div style="z-index: 1500;" class="modal fade text-left" id="ModalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="myModalLabel9" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info white">
        <h4 class="modal-title white" id="myModalLabel9"><i class="fas fa fa-edit mr-1"></i>Editar Usuario</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data">
       <div class="col-md-12">
            
           <div class="row">
            

            <div class="col-md-3">
              <div class="form-group">
                  <label for="UECedulaIdentidad">C. I.</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="UECedulaIdentidad" name="editarci" class="form-control" value="">
                  </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                  <label for="UEApellidoPaterno">Apellido Paterno</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="UEApellidoPaterno" name="editarapaterno" class="form-control" value="" >
                  </div>
              </div>
            </div>
              <div class="col-md-3">  
                <div class="form-group">
                  <label for="UEApellidoMaterno">Apellido Materno</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="UEApellidoMaterno" name="editaramaterno" class="form-control" value="">
                  </div>
                </div>
              </div>
               <div class="col-md-6">  
                <div class="form-group">
                  <label for="UENombres">Nombres</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="UENombres"  name="editarnombres"class="form-control" value="">
                  </div>
                </div>
              </div>
          </div>
          <div class="row">
           <div class="col-md-6">
                <div class="form-group">
                  <label for="UEDireccion">Direccion</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="UEDireccion" name="editardireccion" class="form-control" value="" >  
                  </div>
                </div>
            </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="UECelular">Celular</label>
                  <div class="position-relative has-icon-left">
                    <input  type="text" id="UECelular" name="editarcelular" class="form-control" value="">  
                  </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="form-group">
                  <label for="UETelefono">Telefono</label>
                  <div class="position-relative has-icon-left">
                    <input type="text" id="UETelefono"  name="editartelefono" class="form-control" value="" >  
                  </div>
                </div>
            </div>
          </div>
          </div>
            <div class="col-md-12" >
                <div class="form-group">
                <button type="button" class="btn grey btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
               </div> 
      </form>
      <?php
                $edsusuario = new UsuarioControladores();
                $edsusuario -> EditarUsuarioControlador();
                
                ?>
     
    </div>
  </div>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
?>
 


</body>
<div class="modal fade" id="modalu" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">DAR DE BAJA</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
    <input type="hidden"  id="idusr" name="idusr">
      <center>
      <h1>El usuario ya no podra ingresar al sistema</h1>
      </center>
	 
	  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Aceptar</button>
	
      </div>
	  <?php
             $estadousr = new UsuarioControladores();
            $estadousr -> CambiarEstadoModelos();
		?> 
	  </form>
    
    </div>
  </div>
</div>


<div class="modal fade" id="modals" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PERMISO DE USUARIO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form class="kt-form kt-form--fit kt-form--label-right" method="POST">
	  <div class="modal-body">
    <input type="hidden"  id="idusrs" name="idusrs">
      <center>
      <h1>El usuario tendra permiso de ingresar al sistema</h1>
      </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Aceptar</button>
	
      </div>
	  <?php
             $subirusr = new UsuarioControladores();
            $subirusr -> CambiarEstadosControlador();
		?> 
	  </form>
    
    </div>
  </div>
</div>