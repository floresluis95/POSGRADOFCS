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
                                        <i class="flaticon2-document" style="font-size: 2.5rem;"></i> HISTORIAL DE VOUCHERS
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
                                            Consulta de Vouchers de Pago
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 2.5rem; background: #f8f9fa;">

                                    <!-- Información del estudiante -->
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
                                                            <i class="flaticon2-file-1"></i> Consulte los vouchers de pago registrados
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
                                                <select class="form-control" id="select_programa_vouchers" name="select_programa_vouchers" disabled style="height: 50px; border: 2px solid #E4E6EF; border-radius: 10px; font-size: 1rem; font-weight: 500;">
                                                    <option value="">Cargando programas...</option>
                                                </select>
                                                <small class="form-text" style="color: #B5B5C3; margin-top: 8px;">
                                                    <i class="flaticon2-information"></i> Seleccione un programa para visualizar los vouchers de pago
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Área de vouchers -->
                                    <div id="area_vouchers" style="display: none;">
                                        <div class="mb-4" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem 2rem;">
                                                <h3 style="color: white; font-weight: 700; margin: 0; font-size: 1.4rem;">
                                                    <i class="flaticon2-file-1" style="font-size: 1.8rem; margin-right: 10px;"></i>
                                                    Vouchers de Pago Registrados
                                                </h3>
                                            </div>
                                            <div style="padding: 2rem;">
                                                <div id="contenido_vouchers">
                                                    <!-- Los vouchers se cargarán aquí dinámicamente -->
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
        </div>
    </div>

    <!-- Modal para ver imagen grande -->
    <div class="modal fade" id="modalImagenVoucher" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <h5 class="modal-title" style="color: white; font-weight: 700;">
                        <i class="flaticon2-photograph"></i> Voucher de Pago
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" style="padding: 2rem; background: #f8f9fa;">
                    <div id="info_voucher" class="mb-3"></div>
                    <img id="imagen_voucher_grande" src="" class="img-fluid" style="max-height: 600px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">
                </div>
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
            script.src = 'vistas/recursos/assets/js/scripts/historial-vouchers-estudiante.js?v=<?php echo time(); ?>';
            document.body.appendChild(script);
        });
    </script>

    <style>
        /* Estilos personalizados */
        .voucher-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .voucher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .voucher-image {
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            max-height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .voucher-image:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modulo-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin: 5px;
            display: inline-block;
        }

        .no-image-placeholder {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 3rem;
            text-align: center;
            color: #B5B5C3;
        }

        /* Modal responsive */
        @media (max-width: 768px) {
            #imagen_voucher_grande {
                max-height: 400px;
            }
        }

        /* Animación de carga */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .voucher-card {
            animation: fadeIn 0.5s ease-in;
        }
    </style>

</body>
