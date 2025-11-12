<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div class="kt-grid kt-grid--ver kt-grid--root kt-page">
        <div class="kt-grid kt-grid--hor kt-grid--root  kt-login kt-login--v1" id="kt_login">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--desktop kt-grid--ver-desktop kt-grid--hor-tablet-and-mobile">
        <!--begin::Aside-->
        <div class="kt-grid__item kt-grid__item--order-tablet-and-mobile-2 kt-grid kt-grid--hor kt-login__aside" style="background-image: url(vistas/recursos/assets/media//bg/fondo1.png);">
            <div class="kt-grid__item">
                <a href="#" class="kt-login__logo">
                     <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="70" />
                </a>
            </div>
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver">
                
            </div>
            
        </div>
        <!--begin::Aside-->

        <!--begin::Content-->
        <div class="kt-grid__item kt-grid__item--fluid  kt-grid__item--order-tablet-and-mobile-1  kt-login__wrapper">
            <!--begin::Head-->
        
            <!--end::Head-->
  <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver">
               
            </div>
         
            <!--begin::Body-->
            <div class="kt-login__body">
                <!--begin::Signin-->
                <div class="kt-login__form">
                    <div class="kt-login__title">
                    <h1 class="kt-login__title">POSGRADO FACULTAD CIENCIAS DE LA SALUD <br>"ODONTOLOGIA"</h1><hr>
                        <h3>INICIO DE SESIÓN</h3>
                    </div>          

                    <!--begin::Form-->
                    <form class="kt-form" method="POST">
                        <div class="form-group">
                             <input class="form-control"   type="text" placeholder="Usuario" name="IUsuario" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                             <input class="form-control" type="password" placeholder="Contraseña" name="IPassword" required>
                        </div>
                        <!--begin::Action-->
                      <div class="kt-login__actions">
                            <button type="submit" class="btn btn-primary btn-elevate kt-login__btn-primary">INGRESAR</button>
                        </div>
                       
    
                        <!--end::Action-->
                    </form>
                    <!--end::Form-->
                         <?php
                                $Ingreso = new IngresoControladores();
                                $Ingreso -> ValidarIngresoControlador();
                            ?>
                    <!--begin::Divider-->
                   
                    <!--end::Divider-->

                    <!--begin::Options-->
                  
                    <!--end::Options-->
                </div>
                <!--end::Signin-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Content-->
    </div>
</div>



                </div>
</body>