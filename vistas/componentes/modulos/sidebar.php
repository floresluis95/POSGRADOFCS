<?php 
if ($_SESSION["Tipo"] == "ADM") {
 ?>
        <div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav ">
                
                <li class="kt-menu__section ">
                <h4 class="kt-menu__section-text">PROGRAMAS DE POSGRADO </h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="programas" class="kt-menu__link "> <i class="kt-menu__link-icon fa fa-cube"></i> <span class="kt-menu__link-text">PROGRAMAS</span></a></li>    
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">MATRICULACION</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="inscripcion" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">NUEVA INSCRIPCION</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="matriculados" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">MATRICULADOS</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="modulos" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">MODULOS</span></a></li>

                <li class="kt-menu__section ">
                <h4 class="kt-menu__section-text">CURSOS</h4>
                <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="inscripcion" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">ASIGNACION DE CURSOS</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="matriculas" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">CURSOS</span></a></li>
              
                  <li class="kt-menu__section ">
                <h4 class="kt-menu__section-text">CALIFICACIONES</h4>
                <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="inscripcion" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">NOTAS DE ESTUDIANTES</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="matriculas" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">REPORTE DE NOTAS</span></a></li>
              

                 <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">DOCENTES</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                  <li class="kt-menu__item " aria-haspopup="true"><a href="docentes" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-toolbox"></i><span class="kt-menu__link-text">LISTA DE DOCENTES</span></a></li>
                   
                  <li class="kt-menu__item " aria-haspopup="true"><a href="concluidos" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-toolbox"></i><span class="kt-menu__link-text">ASIGNACION DE DOCENTES</span></a></li>
                  
                  <li class="kt-menu__item " aria-haspopup="true"><a href="concluidos" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-toolbox"></i><span class="kt-menu__link-text">CURSOS PROGRAMADOS</span></a></li>
                 <li class="kt-menu__item " aria-haspopup="true"><a href="concluidos" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-toolbox"></i><span class="kt-menu__link-text">ESTADO DE TRABAJOS</span></a></li>
                </li>
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">PERSONAL</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="usuario" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-user-circle"></i><span class="kt-menu__link-text">USUARIOS</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="estudiantes" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-user-circle"></i><span class="kt-menu__link-text">ESTUDIANTES</span></a></li>

              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->  
<?php 
}
 ?>



 <?php 



if ($_SESSION["Tipo"] == "SEC") {
 ?>
 <div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav ">
                
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">REGISTRAR NOTA DE ENTREGA GNV</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="nentrega" class="kt-menu__link "> <i class="kt-menu__link-icon fa fa-cube"></i> <span class="kt-menu__link-text">RECEPCION DE KIT'S GNV</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="nentregac" class="kt-menu__link "> <i class="kt-menu__link-icon fa fa-circle"></i> <span class="kt-menu__link-text">RECEPCION DE CILINDROS GNV</span></a></li>
  
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">SOLICITUD PARA CONVERSIÓN</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="detalles" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">DETALLE DE SOLICITUDES</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="solicitud" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-file"></i><span class="kt-menu__link-text">NUEVA SOLICITUD</span></a></li>
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">ORDEN DE TRABAJO</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>

                <li class="kt-menu__item " aria-haspopup="true"><a href="prtrabajo" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-tools"></i><span class="kt-menu__link-text">PROGRAMAR TRABAJO DE CONVERSIÓN</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="asignados" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-check"></i><span class="kt-menu__link-text">TRABAJOS PROGRAMADOS</span></a></li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="concluidos" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-toolbox"></i><span class="kt-menu__link-text">GENERAR INFORMES DE TRABAJOS</span></a></li>
              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->  


<?php 
}
 ?>



  <?php 
if ($_SESSION["Tipo"] == "DOC") {
 ?>
<div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav ">
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">REGISTRAR TRABAJOS CONCLUIDOS</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="asignadostec" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-check"></i><span class="kt-menu__link-text">ASIGNADOS</span></a></li>
              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
<?php 
}
 ?>
   <?php 
if ($_SESSION["Tipo"] == "EST") {
 ?>
<div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav ">
                <li class="kt-menu__section ">
                  <h4 class="kt-menu__section-text">REGISTRAR TRABAJOS CONCLUIDOS</h4>
                  <i class="kt-menu__section-icon flaticon-more-v2"></i>
                </li>
                <li class="kt-menu__item " aria-haspopup="true"><a href="asignadostec" class="kt-menu__link "><i class="kt-menu__link-icon fa fa-check"></i><span class="kt-menu__link-text">ASIGNADOS</span></a></li>
              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
<?php 
}
 ?>