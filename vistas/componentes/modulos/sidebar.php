<?php
if ($_SESSION["Tipo"] == "ADM") {
 ?>
        <div class="kt-aside kt-aside--fixed kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside" style="background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu" data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav">

                <!-- PROGRAMAS DE POSGRADO -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-layers-1" style="color: #667eea;"></i> PROGRAMAS DE POSGRADO
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="programas" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-box-1" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Programas</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="modulos" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-layers-2" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Módulos</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportemodulos" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-file-1" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Reporte General de Programas</span>
                  </a>
                </li>

                <!-- MATRICULACIÓN -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-writing" style="color: #1dc9b7;"></i> MATRICULACIÓN
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="inscripcion" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #1dc9b7;"></i>
                    <span class="kt-menu__link-text">Nueva Inscripción</span>
                    <span class="kt-menu__link-badge">
                      <span class="kt-badge kt-badge--success kt-badge--dot"></span>
                    </span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="matriculados" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-group" style="color: #1dc9b7;"></i>
                    <span class="kt-menu__link-text">Matriculados</span>
                  </a>
                </li>

                <!-- CALIFICACIONES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-chart2" style="color: #ffb822;"></i> CALIFICACIONES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="rnotasestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-edit" style="color: #ffb822;"></i>
                    <span class="kt-menu__link-text">Registrar Notas</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportenotas" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-document" style="color: #ffb822;"></i>
                    <span class="kt-menu__link-text">Reporte de Notas</span>
                  </a>
                </li>

                <!-- NOTAS ESTUDIANTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-analytics-1" style="color: #fd397a;"></i> CONSULTA DE NOTAS
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="bnotaestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-search-1" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">Buscar por Estudiante</span>
                  </a>
                </li>
               
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="historialnotaestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-list-3" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">Historial de Notas - Estudiantes</span>
                  </a>
                </li>

                <!-- DOCENTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-user-outline-symbol" style="color: #36a3f7;"></i> DOCENTES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="docentes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-avatar" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Lista de Docentes</span>
                  </a>
                </li>
                 <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="asignaciondocente" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Asignación de Modulos</span>
                  </a>
                </li>

                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="notasdocente" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-avatar" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Registro de Notas - Docentes</span>
                  </a>
                </li>
             

              

                <!-- PERSONAL -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-user" style="color: #9c27b0;"></i> PERSONAL
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="usuario" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-user-1" style="color: #9c27b0;"></i>
                    <span class="kt-menu__link-text">Usuarios</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="estudiantes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-group" style="color: #9c27b0;"></i>
                    <span class="kt-menu__link-text">Estudiantes</span>
                  </a>
                </li>

                  <!-- REPORTES PDF -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-printer" style="color: #c49bbb;"></i> REPORTES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-file-2" style="color: #c49bbb;"></i>
                    <span class="kt-menu__link-text">Generar Reportes PDF</span>
                  </a>
                </li>
             
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="ordenpago" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-file-2" style="color: #c49bbb;"></i>
                    <span class="kt-menu__link-text">ORDEN DE PAGO</span>
                  </a>
                </li>


              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->
<?php
}
 ?>

<!-- SECRETARIA -->
<?php
if ($_SESSION["Tipo"] == "SEC") {
 ?>
        <div class="kt-aside kt-aside--fixed kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside" style="background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu" data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav">

                <!-- PROGRAMAS DE POSGRADO -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-layers-1" style="color: #667eea;"></i> PROGRAMAS DE POSGRADO
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="programas" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-box-1" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Programas</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="modulos" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-layers-2" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Módulos</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportemodulos" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-file-1" style="color: #667eea;"></i>
                    <span class="kt-menu__link-text">Reporte de Módulos</span>
                  </a>
                </li>

                <!-- MATRICULACIÓN -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-writing" style="color: #1dc9b7;"></i> MATRICULACIÓN
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="inscripcion" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #1dc9b7;"></i>
                    <span class="kt-menu__link-text">Nueva Inscripción</span>
                    <span class="kt-menu__link-badge">
                      <span class="kt-badge kt-badge--success kt-badge--dot"></span>
                    </span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="matriculados" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-group" style="color: #1dc9b7;"></i>
                    <span class="kt-menu__link-text">Matriculados</span>
                  </a>
                </li>

                <!-- CALIFICACIONES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-chart2" style="color: #ffb822;"></i> CALIFICACIONES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="notasestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-edit" style="color: #ffb822;"></i>
                    <span class="kt-menu__link-text">Registrar Notas</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportenotas" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-document" style="color: #ffb822;"></i>
                    <span class="kt-menu__link-text">Reporte de Notas</span>
                  </a>
                </li>

                <!-- NOTAS ESTUDIANTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-analytics-1" style="color: #fd397a;"></i> CONSULTA DE NOTAS
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="inscripcion" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-search-1" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">Buscar Estudiante</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="matriculas" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-list-3" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">Historial de Notas</span>
                  </a>
                </li>

                <!-- DOCENTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-user-outline-symbol" style="color: #36a3f7;"></i> DOCENTES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="docentes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-avatar" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Lista de Docentes</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="asignaciondocente" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Asignación de Docentes - Modulos</span>
                  </a>
                </li>

                <!-- PERSONAL -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-user" style="color: #9c27b0;"></i> PERSONAL
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="usuario" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-user-1" style="color: #9c27b0;"></i>
                    <span class="kt-menu__link-text">Usuarios</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="estudiantes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-group" style="color: #9c27b0;"></i>
                    <span class="kt-menu__link-text">Estudiantes</span>
                  </a>
                </li>

              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->
<?php
}
 ?>

<!-- DOCENTES -->
<?php
if ($_SESSION["Tipo"] == "DOC") {
 ?>
        <div class="kt-aside kt-aside--fixed kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside" style="background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu" data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav">

          

                <!-- DOCENTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-user-outline-symbol" style="color: #36a3f7;"></i> DOCENTES
                  </h4>
             
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="notasdocente" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-avatar" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Notas Docentes</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="asignaciondocente" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Modulos Asignados</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportecalificaciones" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-add-1" style="color: #36a3f7;"></i>
                    <span class="kt-menu__link-text">Reporte de calificaciones</span>
                  </a>
                </li>

                <!-- REPORTES PDF -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-printer" style="color: #c49bbb;"></i> REPORTES
                  </h4>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="reportes" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-file-2" style="color: #c49bbb;"></i>
                    <span class="kt-menu__link-text">Generar Reportes PDF</span>
                  </a>
                </li>

             

              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->
<?php
}
 ?>

<!-- ESTUDIANTES -->
<?php
if ($_SESSION["Tipo"] == "EST") {
 ?>
        <div class="kt-aside kt-aside--fixed kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside" style="background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);">
          <!-- begin:: Aside Menu -->
          <div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
            <div id="kt_aside_menu" class="kt-aside-menu" data-ktmenu-vertical="1" data-ktmenu-scroll="1">

              <ul class="kt-menu__nav">
                <!-- NOTAS ESTUDIANTES -->
                <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-analytics-1" style="color: #fd397a;"></i> CONSULTA DE NOTAS
                  </h4>
                </li>  
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="historialnotaestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-list-3" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">CALIFICACIONES</span>
                  </a>
                </li>

                  <li class="kt-menu__section">
                  <h4 class="kt-menu__section-text" style="color: #fff; font-weight: 600; font-size: 11px; letter-spacing: 1px;">
                    <i class="flaticon2-analytics-1" style="color: #fd397a;"></i> DETALLE DE PAGOS
                  </h4>
                </li>  
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="historialmodulosestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-list-3" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">MODULOS</span>
                  </a>
                </li>
                <li class="kt-menu__item menu-item-modern" aria-haspopup="true">
                  <a href="historialreciboestudiante" class="kt-menu__link">
                    <i class="kt-menu__link-icon flaticon2-list-3" style="color: #fd397a;"></i>
                    <span class="kt-menu__link-text">RECIBOS</span>
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <!-- end:: Aside Menu -->
        </div>
        <!-- end:: Aside -->
<?php
}
 ?>

<!-- Estilos CSS personalizados para el sidebar mejorado -->
<style>
/* Sidebar moderno con gradiente */
.kt-aside {
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

/* Asegurar visibilidad de todos los enlaces */
.kt-aside .kt-menu__link {
  color: #ffffff !important;
}

.kt-aside .kt-menu__link * {
  color: inherit;
}

/* Mejorar contraste general */
.kt-aside .kt-menu__nav {
  background: transparent;
}

/* Secciones del menú */
.kt-menu__section {
  margin-top: 20px;
  margin-bottom: 10px;
  padding: 12px 20px 8px;
  border-radius: 8px;
  margin-left: 10px;
  margin-right: 10px;
  border-left: 4px solid;
  background: rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.kt-menu__section:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(3px);
}

.kt-menu__section-text {
  display: flex;
  align-items: center;
  gap: 8px;
  text-transform: uppercase;
  color: #ffffff !important;
  font-weight: 600 !important;
  font-size: 11px;
  letter-spacing: 1px;
}

/* Colores por categoría - Secciones */
.kt-menu__section:nth-child(1) { border-left-color: #667eea; } /* Programas */
.kt-menu__section:nth-child(5) { border-left-color: #1dc9b7; } /* Matriculación */
.kt-menu__section:nth-child(9) { border-left-color: #ffb822; } /* Calificaciones */
.kt-menu__section:nth-child(13) { border-left-color: #fd397a; } /* Consulta Notas */
.kt-menu__section:nth-child(17) { border-left-color: #36a3f7; } /* Docentes */
.kt-menu__section:nth-child(21) { border-left-color: #c49bbb; } /* Reportes */
.kt-menu__section:nth-child(24) { border-left-color: #9c27b0; } /* Personal */

/* Items del menú modernos */
.menu-item-modern .kt-menu__link {
  padding: 12px 20px;
  border-radius: 8px;
  margin: 4px 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  border-left: 3px solid transparent;
}

.menu-item-modern .kt-menu__link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 100%;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.menu-item-modern .kt-menu__link:hover::before,
.menu-item-modern.kt-menu__item--active .kt-menu__link::before {
  opacity: 1;
}

.menu-item-modern .kt-menu__link:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Colores por categoría - Items de Programas de Posgrado */
.kt-menu__nav > li:nth-child(2) .kt-menu__link,
.kt-menu__nav > li:nth-child(3) .kt-menu__link,
.kt-menu__nav > li:nth-child(4) .kt-menu__link {
  border-left-color: #667eea;
}
.kt-menu__nav > li:nth-child(2) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(3) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(4) .kt-menu__link:hover {
  background: rgba(102, 126, 234, 0.2);
}
.kt-menu__nav > li:nth-child(2).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(3).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(4).kt-menu__item--active .kt-menu__link {
  background: rgba(102, 126, 234, 0.3);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Colores por categoría - Items de Matriculación */
.kt-menu__nav > li:nth-child(6) .kt-menu__link,
.kt-menu__nav > li:nth-child(7) .kt-menu__link {
  border-left-color: #1dc9b7;
}
.kt-menu__nav > li:nth-child(6) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(7) .kt-menu__link:hover {
  background: rgba(29, 201, 183, 0.2);
}
.kt-menu__nav > li:nth-child(6).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(7).kt-menu__item--active .kt-menu__link {
  background: rgba(29, 201, 183, 0.3);
  box-shadow: 0 4px 12px rgba(29, 201, 183, 0.4);
}

/* Colores por categoría - Items de Calificaciones */
.kt-menu__nav > li:nth-child(10) .kt-menu__link,
.kt-menu__nav > li:nth-child(11) .kt-menu__link {
  border-left-color: #ffb822;
}
.kt-menu__nav > li:nth-child(10) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(11) .kt-menu__link:hover {
  background: rgba(255, 184, 34, 0.2);
}
.kt-menu__nav > li:nth-child(10).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(11).kt-menu__item--active .kt-menu__link {
  background: rgba(255, 184, 34, 0.3);
  box-shadow: 0 4px 12px rgba(255, 184, 34, 0.4);
}

/* Colores por categoría - Items de Consulta de Notas */
.kt-menu__nav > li:nth-child(14) .kt-menu__link,
.kt-menu__nav > li:nth-child(15) .kt-menu__link {
  border-left-color: #fd397a;
}
.kt-menu__nav > li:nth-child(14) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(15) .kt-menu__link:hover {
  background: rgba(253, 57, 122, 0.2);
}
.kt-menu__nav > li:nth-child(14).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(15).kt-menu__item--active .kt-menu__link {
  background: rgba(253, 57, 122, 0.3);
  box-shadow: 0 4px 12px rgba(253, 57, 122, 0.4);
}

/* Colores por categoría - Items de Docentes */
.kt-menu__nav > li:nth-child(18) .kt-menu__link,
.kt-menu__nav > li:nth-child(19) .kt-menu__link {
  border-left-color: #36a3f7;
}
.kt-menu__nav > li:nth-child(18) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(19) .kt-menu__link:hover {
  background: rgba(54, 163, 247, 0.2);
}
.kt-menu__nav > li:nth-child(18).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(19).kt-menu__item--active .kt-menu__link {
  background: rgba(54, 163, 247, 0.3);
  box-shadow: 0 4px 12px rgba(54, 163, 247, 0.4);
}

/* Colores por categoría - Items de Reportes */
.kt-menu__nav > li:nth-child(22) .kt-menu__link {
  border-left-color: #c49bbb;
}
.kt-menu__nav > li:nth-child(22) .kt-menu__link:hover {
  background: rgba(196, 155, 187, 0.2);
}
.kt-menu__nav > li:nth-child(22).kt-menu__item--active .kt-menu__link {
  background: rgba(196, 155, 187, 0.3);
  box-shadow: 0 4px 12px rgba(196, 155, 187, 0.4);
}

/* Colores por categoría - Items de Personal */
.kt-menu__nav > li:nth-child(25) .kt-menu__link,
.kt-menu__nav > li:nth-child(26) .kt-menu__link {
  border-left-color: #9c27b0;
}
.kt-menu__nav > li:nth-child(25) .kt-menu__link:hover,
.kt-menu__nav > li:nth-child(26) .kt-menu__link:hover {
  background: rgba(156, 39, 176, 0.2);
}
.kt-menu__nav > li:nth-child(25).kt-menu__item--active .kt-menu__link,
.kt-menu__nav > li:nth-child(26).kt-menu__item--active .kt-menu__link {
  background: rgba(156, 39, 176, 0.3);
  box-shadow: 0 4px 12px rgba(156, 39, 176, 0.4);
}

/* Iconos del menú */
.kt-menu__link-icon {
  font-size: 1.3rem;
  margin-right: 12px;
  transition: all 0.3s ease;
  opacity: 1 !important;
}

.menu-item-modern .kt-menu__link:hover .kt-menu__link-icon {
  transform: scale(1.1) rotate(5deg);
  filter: brightness(1.2);
}

/* Texto del menú */
.kt-menu__link-text {
  color: #ffffff !important;
  font-weight: 500;
  font-size: 13px;
  transition: color 0.3s ease;
}

.menu-item-modern .kt-menu__link:hover .kt-menu__link-text {
  color: #ffffff !important;
}

.menu-item-modern.kt-menu__item--active .kt-menu__link-text {
  color: #ffffff !important;
  font-weight: 600;
}

/* Badge para items especiales */
.kt-menu__link-badge {
  margin-left: auto;
}

.kt-badge--dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.2);
  }
}

/* Scrollbar personalizado para el sidebar */
.kt-aside-menu::-webkit-scrollbar {
  width: 6px;
}

.kt-aside-menu::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.kt-aside-menu::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.5);
  border-radius: 10px;
  transition: background 0.3s ease;
}

.kt-aside-menu::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.8);
}

/* Responsive */
@media (max-width: 1024px) {
  .menu-item-modern .kt-menu__link {
    padding: 10px 15px;
    margin: 3px 8px;
  }

  .kt-menu__link-text {
    font-size: 12px;
  }
}
</style>
