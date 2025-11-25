

<?php 
if ($_SESSION["Tipo"] == "ADM") {
 ?>
<div id="kt_header" class="kt-header  kt-header--fixed " data-ktheader-minimize="on">
          <div class="kt-container  kt-container--fluid ">
            <!-- begin: Header Menu -->
            <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
            <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">

              <button class="kt-aside-toggler kt-aside-toggler--left" id="kt_aside_toggler"><span></span></button>

              <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile ">
                <ul class="kt-menu__nav ">
                <li class="kt-menu__item " aria-haspopup="true"><a href="panel" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-circle-notch"></i><span class="kt-menu__link-text">INICIO</span></a></li> 
                <li class="kt-menu__item " aria-haspopup="true"><a href="consultas" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-filter"></i><span class="kt-menu__link-text">CONSULTAS</span></a></li>   
                  
                
                  </li>
                  </li>
                </ul>
              </div>

            </div>
            <!-- end: Header Menu -->
            <!-- begin:: Brand -->
            <div class="kt-header__brand   kt-grid__item" id="kt_header_brand">
              <a class="kt-header__brand-logo" href="demo9/index.html">
                <img alt="Logo" src="vistas/recursos/assets/media/logos/logo.png" width="150" />
              </a>
            </div>
            <!-- end:: Brand -->
            <!-- begin:: Header Topbar -->
            <div class="kt-header__topbar kt-grid__item">
            
              <div class="kt-header__topbar-item kt-header__topbar-item--user">
                <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-welcome kt-visible-desktop">
                     <h4>
                     
                     </h4> 
                  </span>
                  <h3><span class="badge badge-secondary"> 
                   <?php
                      echo "Usuario: ".$_SESSION['Nombres']." ".$_SESSION['ApellidoPaterno']." ".$_SESSION['ApellidoMaterno']." Tipo:".$_SESSION['Tipo'];
                      ?></span></h3>
                  <!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
                  <span class="kt-badge kt-badge--username kt-badge--unified-success kt-badge--lg kt-badge--rounded kt-badge--bold kt-hidden">S</span>
                </div>
                <div class="dropdown-menu dropdown-menu-right ">
                 <li class="user-body">
                    <div class="pull-right">
                     <a href="salir" class="btn btn-default btn-flat">Salir</a>
                    
                    </div>
                   
                 </li>
                </div>
              </div>
              <!--end: User bar -->

              <!--begin: Quick panel toggler -->
  
              <!--end: Quick panel toggler -->
            </div>
            <!-- end:: Header Topbar -->
          </div>
        </div>

<?php 
}
 ?>


<!-- SECRETARIA -->

<?php 
if ($_SESSION["Tipo"] == "SEC") {
 ?>
<div id="kt_header" class="kt-header  kt-header--fixed " data-ktheader-minimize="on">
          <div class="kt-container  kt-container--fluid ">
            <!-- begin: Header Menu -->
            <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
            <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">

              <button class="kt-aside-toggler kt-aside-toggler--left" id="kt_aside_toggler"><span></span></button>

              <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile ">
                <ul class="kt-menu__nav ">
                <li class="kt-menu__item " aria-haspopup="true"><a href="panel" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-circle-notch"></i><span class="kt-menu__link-text">INICIO</span></a></li> 
                <li class="kt-menu__item " aria-haspopup="true"><a href="consultas" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-filter"></i><span class="kt-menu__link-text">CONSULTAS</span></a></li>   
                  
                
                  </li>
                  </li>
                </ul>
              </div>

            </div>
            <!-- end: Header Menu -->
            <!-- begin:: Brand -->
            <div class="kt-header__brand   kt-grid__item" id="kt_header_brand">
              <a class="kt-header__brand-logo" href="demo9/index.html">
                <img alt="Logo" src="vistas/recursos/assets/media/logos/logo.png" width="150" />
              </a>
            </div>
            <!-- end:: Brand -->
            <!-- begin:: Header Topbar -->
            <div class="kt-header__topbar kt-grid__item">
            
              <div class="kt-header__topbar-item kt-header__topbar-item--user">
                <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-welcome kt-visible-desktop">
                     <h4>
                     
                     </h4> 
                  </span>
                  <h3><span class="badge badge-secondary"> 
                   <?php
                      echo "Usuario: ".$_SESSION['Nombres']." ".$_SESSION['ApellidoPaterno']." ".$_SESSION['ApellidoMaterno']." Tipo:".$_SESSION['Tipo'];
                      ?></span></h3>
                  <!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
                  <span class="kt-badge kt-badge--username kt-badge--unified-success kt-badge--lg kt-badge--rounded kt-badge--bold kt-hidden">S</span>
                </div>
                <div class="dropdown-menu dropdown-menu-right ">
                 <li class="user-body">
                    <div class="pull-right">
                     <a href="salir" class="btn btn-default btn-flat">Salir</a>
                    
                    </div>
                   
                 </li>
                </div>
              </div>
              <!--end: User bar -->

              <!--begin: Quick panel toggler -->
  
              <!--end: Quick panel toggler -->
            </div>
            <!-- end:: Header Topbar -->
          </div>
        </div>

<?php 
}
 ?>

<!-- DOCENTES -->

<?php 
if ($_SESSION["Tipo"] == "DOC") {
 ?>
<div id="kt_header" class="kt-header  kt-header--fixed " data-ktheader-minimize="on">
          <div class="kt-container  kt-container--fluid ">
            <!-- begin: Header Menu -->
            <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
            <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">

              <button class="kt-aside-toggler kt-aside-toggler--left" id="kt_aside_toggler"><span></span></button>

              <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile ">
                <ul class="kt-menu__nav ">
                <li class="kt-menu__item " aria-haspopup="true"><a href="panel" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-circle-notch"></i><span class="kt-menu__link-text">INICIO</span></a></li>   
                </ul>
              </div>

            </div>
            <!-- end: Header Menu -->
            <!-- begin:: Brand -->
            <div class="kt-header__brand   kt-grid__item" id="kt_header_brand">
              <a class="kt-header__brand-logo" href="demo9/index.html">
                <img alt="Logo" src="vistas/recursos/assets/media/logos/logo.png" width="150" />
              </a>
            </div>
            <!-- end:: Brand -->
            <!-- begin:: Header Topbar -->
            <div class="kt-header__topbar kt-grid__item">
            
              <div class="kt-header__topbar-item kt-header__topbar-item--user">
                <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                  <span class="kt-header__topbar-welcome kt-visible-desktop">
                     <h4>
                     
                     </h4> 
                  </span>
                  <h3><span class="badge badge-secondary"> 
                   <?php
                      echo "Usuario: ".$_SESSION['Nombres']." ".$_SESSION['ApellidoPaterno']." ".$_SESSION['ApellidoMaterno']." Tipo:".$_SESSION['Tipo'];
                      ?></span></h3>
                 
        
                  <!--use below badge element instead the user avatar to display username's first letter(remove kt-hidden class to display it) -->
                  <span class="kt-badge kt-badge--username kt-badge--unified-success kt-badge--lg kt-badge--rounded kt-badge--bold kt-hidden">S</span>
                </div>
                <div class="dropdown-menu dropdown-menu-right ">
                 <li class="user-body">
                    <div class="pull-right">
                     <a href="salir" class="btn btn-default btn-flat">Salir</a>
                    
                    </div>
                   
                 </li>
                </div>
              </div>
              <!--end: User bar -->

              <!--begin: Quick panel toggler -->
  
              <!--end: Quick panel toggler -->
            </div>
            <!-- end:: Header Topbar -->
          </div>
        </div>

<?php 
}
 ?>

