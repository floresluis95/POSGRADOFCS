<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
        <div class="kt-header-mobile__logo">
            <a href="demo9/index.html">
            <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
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

                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title">
                                        CONSULTA DE CALIFICACIONES
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>
                                            ACCESO ESTUDIANTIL
                                        </h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                            <div class="kt-portlet">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">
                                            Consulta de calificaciones
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body hn-cuerpo">

                                    <!-- Selector de programa (si tiene uno solo, se elige solo) -->
                                    <div class="form-group mb-4" id="grupo_programa">
                                        <label for="select_programa_calificaciones" class="hn-etiqueta">Programa</label>
                                        <select class="form-control select2-uniforme" id="select_programa_calificaciones" name="select_programa_calificaciones" disabled>
                                            <option value="">Cargando programas...</option>
                                        </select>
                                    </div>

                                    <!-- Calificaciones finales -->
                                    <div class="hn-seccion">
                                        <div class="hn-seccion-titulo">
                                            <span>Calificaciones finales</span>
                                            <button class="btn btn-outline-secondary btn-sm" id="btn_descargar_pdf" style="display: none;">
                                                <i class="flaticon2-download"></i> Descargar PDF
                                            </button>
                                        </div>
                                        <div class="calificaciones-cuerpo">
                                            <div id="resumen_calificaciones"></div>
                                            <table class="table mb-0" id="tabla_calificaciones_finales">
                                                <thead>
                                                    <tr>
                                                        <th>Módulo</th>
                                                        <th>Docente</th>
                                                        <th class="text-center">Nota</th>
                                                        <th class="text-center">Ponderación</th>
                                                        <th class="text-center">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_calificaciones">
                                                    <tr>
                                                        <td colspan="5" class="hn-vacio">Elija un programa para ver sus calificaciones.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php
                            $Footer = new FuncionesControladores();
                            $Footer -> FooterResumidoControlador();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="vistas/recursos/assets/js/scripts/historial-estudiante.js?v=<?php echo time(); ?>"></script>
    <style>
        .hn-cuerpo {
            padding: 2rem;
            background: #f8f9fa;
        }

        .hn-etiqueta {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #56637a;
            margin-bottom: 4px;
        }

        .hn-seccion {
            background: #fff;
            border: 1px solid #E4E6EF;
            border-radius: 8px;
        }

        .hn-seccion-titulo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #E4E6EF;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #464E5F;
        }

        .calificaciones-cuerpo {
            padding: 1rem 1.25rem;
        }

        /* Resumen: una franja con los datos separados por líneas finas */
        .hn-resumen {
            display: flex;
            border: 1px solid #eef0f5;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .hn-resumen-dato {
            flex: 1;
            padding: 0.6rem 0.75rem;
            text-align: center;
            border-left: 1px solid #eef0f5;
        }

        .hn-resumen-dato:first-child {
            border-left: none;
        }

        .hn-resumen-dato span {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #7e8299;
        }

        .hn-resumen-dato strong {
            font-size: 1.1rem;
            color: #464E5F;
        }

        #tabla_calificaciones_finales thead th {
            border-top: none;
            border-bottom: 1px solid #E4E6EF;
            padding: 0.5rem 0.75rem;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #7e8299;
            white-space: nowrap;
        }

        #tabla_calificaciones_finales tbody td {
            padding: 0.6rem 0.75rem;
            vertical-align: middle;
            font-size: 0.88rem;
            color: #464E5F;
            border-top: 1px solid #f3f4f8;
        }

        .hn-modulo {
            font-weight: 600;
        }

        .hn-codigo {
            display: block;
            font-size: 0.75rem;
            color: #9aa0ac !important;
        }

        .hn-nota {
            font-weight: 700;
        }

        .hn-nota-aprobado { color: #1e7145; }
        .hn-nota-reprobado { color: #b02a1e; }

        .hn-estado {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            border: 1px solid currentColor;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .hn-estado-aprobado { color: #1e7145; }
        .hn-estado-reprobado { color: #b02a1e; }

        .hn-vacio {
            text-align: center;
            color: #9aa0ac;
            padding: 1.5rem !important;
        }

        /* Celular: resumen de 2 en 2 y cada módulo como tarjeta, con la etiqueta de
           cada dato a la izquierda (sin desplazarse de lado) */
        @media (max-width: 767px) {
            .hn-cuerpo {
                padding: 1rem;
            }

            .calificaciones-cuerpo {
                padding: 0.75rem;
            }

            .hn-resumen {
                flex-wrap: wrap;
            }

            .hn-resumen-dato {
                flex: 1 1 50%;
                border-left: none;
                border-top: 1px solid #eef0f5;
            }

            .hn-resumen-dato:nth-child(-n+2) {
                border-top: none;
            }

            .hn-resumen-dato:nth-child(even) {
                border-left: 1px solid #eef0f5;
            }

            #tabla_calificaciones_finales thead {
                display: none;
            }

            #tabla_calificaciones_finales,
            #tabla_calificaciones_finales tbody,
            #tabla_calificaciones_finales tr,
            #tabla_calificaciones_finales td {
                display: block;
                width: 100%;
            }

            #tabla_calificaciones_finales tbody tr {
                border: 1px solid #eef0f5;
                border-radius: 6px;
                margin-bottom: 10px;
                padding: 2px 12px;
            }

            #tabla_calificaciones_finales tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 6px 0;
                text-align: right !important;
            }

            #tabla_calificaciones_finales tbody td::before {
                content: attr(data-label);
                flex-shrink: 0;
                font-size: 0.72rem;
                font-weight: 600;
                color: #7e8299;
            }

            #tabla_calificaciones_finales tbody td.celda-modulo,
            #tabla_calificaciones_finales tbody td[colspan] {
                display: block;
                border-top: none;
                text-align: left !important;
            }

            #tabla_calificaciones_finales tbody td[colspan] {
                text-align: center !important;
            }

            #tabla_calificaciones_finales tbody td.celda-modulo::before,
            #tabla_calificaciones_finales tbody td[colspan]::before {
                content: none;
            }
        }
    </style>

    </body>