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

                        <div class="kt-subheader kt-grid__item" id="kt_subheader" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 0;">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title" style="color: white; font-size: 2rem; font-weight: 700;">
                                        <i class="flaticon2-list-2" style="font-size: 2.5rem;"></i> HISTORIAL DE MÓDULOS
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home" style="color: rgba(255,255,255,0.8);"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator" style="color: rgba(255,255,255,0.5);"></span>
                                        <h4 style="color: rgba(255,255,255,0.9); font-weight: 500;">
                                            ACCESO ESTUDIANTIL
                                        </h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha" style="font-size:13pt; color: white; font-weight: 500;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                            <div class="kt-portlet">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">
                                            Estado de Pagos de Modulos
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 2.5rem; background: #f8f9fa;">

                                    <!-- Informaci�n del estudiante -->
                                    <div class="mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                                    <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                                        <i class="flaticon2-user" style="font-size: 2rem; color: white;"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-1" style="color: white; font-weight: 700; font-size: 1.5rem;">
                                                            <span id="estudiante_nombre">Estudiante</span>
                                                        </h4>
                                                        <p class="mb-0" style="color: rgba(255,255,255,0.8); font-size: 1rem;">
                                                            <i class="flaticon2-checking"></i> Consulte el estado de sus pagos de módulos
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <div style="background: rgba(255,255,255,0.2); border-radius: 30px; padding: 12px 24px; display: inline-block;">
                                                    <i class="flaticon2-protected" style="color: white; font-size: 1.2rem;"></i>
                                                    <span style="color: white; font-weight: 600; font-size: 1rem; margin-left: 8px;">Sesión Activa</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selector de programa -->
                                    <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 2rem;">
                                        <div class="row align-items-center">
                                            <div class="col-lg-3">
                                                <label class="mb-0" style="font-weight: 700; font-size: 1.1rem; color: #464E5F;">
                                                    <i class="flaticon2-layers-1" style="color: #667eea; font-size: 1.5rem; margin-right: 10px;"></i>
                                                    Programa:
                                                </label>
                                            </div>
                                            <div class="col-lg-9">
                                                <select class="form-control" id="select_programa_modulos" name="select_programa_modulos" disabled style="height: 50px; border: 2px solid #E4E6EF; border-radius: 10px; font-size: 1rem; font-weight: 500;">
                                                    <option value="">Cargando programas...</option>
                                                </select>
                                                <small class="form-text" style="color: #B5B5C3; margin-top: 8px;">
                                                    <i class="flaticon2-information"></i> Seleccione un programa para visualizar el detalle completo de sus módulos
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- �rea de resultados -->
                                    <div id="area_resultados_modulos" style="display: none;">

                                        <!-- Resumen de pagos -->
                                        <div class="row mb-5" id="resumen_pagos_container">
                                            <div class="col-lg-4 mb-3">
                                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); height: 100%;">
                                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                                        <div>
                                                            <div style="background: rgba(255,255,255,0.2); border-radius: 12px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                                                <i class="flaticon2-pie-chart" style="font-size: 2rem; color: white;"></i>
                                                            </div>
                                                            <h5 style="color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">
                                                                Costo Total Programa
                                                            </h5>
                                                            <h2 style="color: white; font-weight: 700; font-size: 2.2rem; margin: 0;">
                                                                Bs. <span id="total_programa">0.00</span>
                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3); height: 100%;">
                                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                                        <div>
                                                            <div style="background: rgba(255,255,255,0.2); border-radius: 12px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                                                <i class="flaticon2-check-mark" style="font-size: 2rem; color: white;"></i>
                                                            </div>
                                                            <h5 style="color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">
                                                                Total Pagado
                                                            </h5>
                                                            <h2 style="color: white; font-weight: 700; font-size: 2.2rem; margin: 0;">
                                                                Bs. <span id="total_pagado">0.00</span>
                                                            </h2>
                                                            <p style="color: rgba(255,255,255,0.8); margin-top: 0.5rem; margin-bottom: 0; font-size: 0.95rem;">
                                                                <i class="flaticon2-files-and-folders"></i> <span id="cantidad_pagados">0</span> módulos cancelados
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 mb-3">
                                                <div style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(238, 9, 121, 0.3); height: 100%;">
                                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                                        <div>
                                                            <div style="background: rgba(255,255,255,0.2); border-radius: 12px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                                                <i class="flaticon2-hourglass-1" style="font-size: 2rem; color: white;"></i>
                                                            </div>
                                                            <h5 style="color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">
                                                                Total Pendiente
                                                            </h5>
                                                            <h2 style="color: white; font-weight: 700; font-size: 2.2rem; margin: 0;">
                                                                Bs. <span id="total_pendiente">0.00</span>
                                                            </h2>
                                                            <p style="color: rgba(255,255,255,0.8); margin-top: 0.5rem; margin-bottom: 0; font-size: 0.95rem;">
                                                                <i class="flaticon2-warning"></i> <span id="cantidad_pendientes">0</span> módulos por cancelar
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- M�dulos Pagados -->
                                        <div class="mb-5" id="modulos_pagados_container" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                            <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 1.5rem 2rem;">
                                                <h3 style="color: white; font-weight: 700; margin: 0; font-size: 1.4rem;">
                                                    <i class="flaticon2-check-mark" style="font-size: 1.8rem; margin-right: 10px;"></i>
                                                    Módulos Cancelados (Pagados)
                                                </h3>
                                            </div>
                                            <div style="padding: 2rem;">
                                                <div class="table-responsive">
                                                    <table class="table table-hover" id="tabla_modulos_pagados" style="margin-bottom: 0;">
                                                        <thead>
                                                            <tr style="background: #f8f9fa; border-bottom: 2px solid #11998e;">
                                                                <th class="text-center" width="5%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">#</th>
                                                                <th width="20%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">MÓDULO</th>
                                                                <th width="10%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">CÓDIGO</th>
                                                                <th width="18%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">DOCENTE</th>
                                                                <th width="15%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">ESPECIALIDAD</th>
                                                                <th class="text-center" width="10%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">COSTO PAGADO</th>
                                                                <th class="text-center" width="10%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">FECHA PAGO</th>
                                                                <th class="text-center" width="12%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">Nº VOUCHER</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_modulos_pagados">
                                                            <tr>
                                                                <td colspan="8" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                                                                    <i class="flaticon2-information" style="font-size: 3rem; color: #E4E6EF;"></i>
                                                                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay módulos pagados</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- M�dulos Pendientes -->
                                        <div class="mb-4" id="modulos_pendientes_container" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                            <div style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); padding: 1.5rem 2rem;">
                                                <h3 style="color: white; font-weight: 700; margin: 0; font-size: 1.4rem;">
                                                    <i class="flaticon2-hourglass-1" style="font-size: 1.8rem; margin-right: 10px;"></i>
                                                    Módulos Por Cancelar (Pendientes de Pago)
                                                </h3>
                                            </div>
                                            <div style="padding: 2rem;">
                                                <div class="table-responsive">
                                                    <table class="table table-hover" id="tabla_modulos_pendientes" style="margin-bottom: 0;">
                                                        <thead>
                                                            <tr style="background: #f8f9fa; border-bottom: 2px solid #ee0979;">
                                                                <th class="text-center" width="5%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">#</th>
                                                                <th width="25%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">MÓDULO</th>
                                                                <th width="12%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">CÓDIGO</th>
                                                                <th width="20%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">DOCENTE</th>
                                                                <th width="18%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">ESPECIALIDAD</th>
                                                                <th class="text-center" width="10%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">COSTO</th>
                                                                <th class="text-center" width="10%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">ESTADO</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_modulos_pendientes">
                                                            <tr>
                                                                <td colspan="7" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                                                                    <i class="flaticon2-check-mark" style="font-size: 3rem; color: #38ef7d;"></i>
                                                                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 600; color: #38ef7d;">¡Felicidades! No tiene módulos pendientes de pago</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

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

    <script>
        // Esperar a que jQuery esté completamente cargado
        window.addEventListener('load', function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery no está cargado!');
                return;
            }

            // Cargar el script después de que jQuery esté disponible
            var script = document.createElement('script');
            script.src = 'vistas/recursos/assets/js/scripts/historial-modulos-estudiante.js?v=<?php echo time(); ?>';
            document.body.appendChild(script);
        });
    </script>
</body>
