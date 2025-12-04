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

                        <div class="kt-subheader   kt-grid__item" id="kt_subheader">
                            <div class="kt-container ">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title">
                                        CALIFICACIONES FINALES
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>
                                            RESULTADOS ACADÉMICOS
                                        </h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha" style="font-size:13pt"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-container kt-grid__item kt-grid__item--fluid">

                            <!-- Card de Filtros Mejorado -->
                            <div class="kt-portlet kt-portlet--modern">
                                <div class="kt-portlet__head kt-portlet__head--gradient">
                                    <div class="kt-portlet__head-label">
                                        <span class="kt-portlet__head-icon">
                                            <i class="flaticon2-search-1"></i>
                                        </span>
                                        <h3 class="kt-portlet__head-title">
                                            <span>Filtros de Búsqueda</span>
                                            <small>Busque y filtre las calificaciones de estudiantes</small>
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body kt-portlet__body--modern">

                                    <!-- Filtro por Programa -->
                                    <div class="filter-card">
                                        <div class="filter-card__icon">
                                            <i class="flaticon2-layers-1"></i>
                                        </div>
                                        <div class="filter-card__content">
                                            <label class="filter-card__label">Programa Académico</label>
                                            <select class="form-control form-control-modern" id="select_programa_calificaciones" name="select_programa_calificaciones">
                                                <option value="">-- Todos los Programas --</option>
                                            </select>
                                            <span class="filter-card__hint">Seleccione un programa para filtrar resultados</span>
                                        </div>
                                    </div>

                                    <div class="kt-separator kt-separator--dashed kt-separator--space-lg"></div>

                                    <!-- Búsqueda Avanzada -->
                                    <div class="search-section">
                                        <div class="search-section__header">
                                            <i class="flaticon-user-settings"></i>
                                            <h5>Búsqueda de Estudiante</h5>
                                        </div>
                                        <div class="row search-section__body">
                                            <div class="col-lg-3 col-md-12">
                                                <label class="modern-label">Criterio de Búsqueda</label>
                                                <select class="form-control form-control-modern" id="select_filtro_tipo" name="select_filtro_tipo">
                                                    <option value="ci">
                                                        <i class="flaticon-id-card"></i> Cédula de Identidad
                                                    </option>
                                                    <option value="nombre">
                                                        <i class="flaticon-user"></i> Nombre
                                                    </option>
                                                    <option value="apellido">
                                                        <i class="flaticon-users"></i> Apellido
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 col-md-12">
                                                <label class="modern-label">Valor de Búsqueda</label>
                                                <div class="input-group input-group-modern">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="flaticon-search"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control form-control-modern" id="input_filtro_valor" name="input_filtro_valor" placeholder="Ingrese el valor a buscar...">
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-12 d-flex align-items-end">
                                                <button type="button" class="btn btn-search-modern" id="btn_buscar_calificaciones">
                                                    <i class="flaticon-search"></i>
                                                    <span>Buscar</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Card de Resultados Mejorado -->
                            <div class="kt-portlet kt-portlet--modern kt-portlet--results">
                                <div class="kt-portlet__head kt-portlet__head--results">
                                    <div class="kt-portlet__head-label">
                                        <span class="kt-portlet__head-icon">
                                            <i class="flaticon2-chart2"></i>
                                        </span>
                                        <h3 class="kt-portlet__head-title">
                                            <span>Resultados de Calificaciones</span>
                                            <small>Visualización de calificaciones finales</small>
                                        </h3>
                                    </div>
                                    <div class="kt-portlet__head-toolbar">
                                        <span class="results-counter" id="results_counter">
                                            <i class="flaticon-list-2"></i> 0 resultados
                                        </span>
                                    </div>
                                </div>
                                <div class="kt-portlet__body kt-portlet__body--table">
                                    <div class="table-responsive table-modern">
                                        <table class="table table-modern table-hover" id="tabla_calificaciones_finales">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <i class="flaticon-user"></i>
                                                        <span>CI / Estudiante</span>
                                                    </th>
                                                    <th>
                                                        <i class="flaticon-book"></i>
                                                        <span>Módulo / Asignatura</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <i class="flaticon2-percentage"></i>
                                                        <span>Calificación</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <i class="flaticon-medal"></i>
                                                        <span>Estado</span>
                                                    </th>
                                                    <th class="text-center">
                                                        <i class="flaticon-calendar"></i>
                                                        <span>Fecha Registro</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="contenido_calificaciones">
                                                <tr class="empty-state">
                                                    <td colspan="5">
                                                        <div class="empty-state__content">
                                                            <div class="empty-state__icon">
                                                                <i class="flaticon2-search"></i>
                                                            </div>
                                                            <h4>No hay resultados para mostrar</h4>
                                                            <p>Realice una búsqueda o seleccione un programa para ver las calificaciones</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="vistas/recursos/assets/js/scripts/buscar-calificaciones.js"></script>

    <style>
        /* ========================================
           PORTLET MODERNO
        ======================================== */
        .kt-portlet--modern {
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
        }

        .kt-portlet--modern:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        /* Header con Gradiente */
        .kt-portlet__head--gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border: none;
        }

        .kt-portlet__head--results {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px 30px;
            border: none;
        }

        .kt-portlet__head-icon {
            font-size: 28px;
            margin-right: 15px;
            opacity: 0.9;
        }

        .kt-portlet__head-title {
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .kt-portlet__head-title span {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .kt-portlet__head-title small {
            font-size: 13px;
            font-weight: 400;
            margin-top: 5px;
            opacity: 0.9;
        }

        .kt-portlet__body--modern {
            padding: 35px 30px;
            background: white;
        }

        /* ========================================
           FILTRO CARD
        ======================================== */
        .filter-card {
            display: flex;
            align-items: center;
            padding: 25px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .filter-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-card__icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .filter-card__icon i {
            font-size: 28px;
            color: white;
        }

        .filter-card__content {
            flex: 1;
        }

        .filter-card__label {
            font-size: 14px;
            font-weight: 600;
            color: #464457;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-card__hint {
            font-size: 12px;
            color: #74788d;
            display: block;
            margin-top: 8px;
        }

        /* ========================================
           SECCIÓN DE BÚSQUEDA
        ======================================== */
        .search-section {
            background: white;
            border: 2px solid #e8ecef;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .search-section:hover {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }

        .search-section__header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f3ff;
        }

        .search-section__header i {
            font-size: 24px;
            color: #667eea;
            margin-right: 12px;
        }

        .search-section__header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #464457;
        }

        .search-section__body {
            margin-top: 20px;
        }

        /* ========================================
           CONTROLES MODERNOS
        ======================================== */
        .modern-label {
            font-size: 13px;
            font-weight: 600;
            color: #464457;
            margin-bottom: 10px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control-modern {
            border: 2px solid #e8ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: #f8f9ff;
        }

        .input-group-modern .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 8px 0 0 8px;
            padding: 12px 16px;
        }

        .input-group-modern .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        /* ========================================
           BOTÓN DE BÚSQUEDA
        ======================================== */
        .btn-search-modern {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 15px;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-search-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-search-modern:active {
            transform: translateY(0);
        }

        .btn-search-modern i {
            font-size: 16px;
        }

        /* ========================================
           CONTADOR DE RESULTADOS
        ======================================== */
        .results-counter {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .results-counter i {
            font-size: 16px;
        }

        /* ========================================
           TABLA MODERNA
        ======================================== */
        .kt-portlet__body--table {
            padding: 0;
        }

        .table-modern {
            border-radius: 0 0 16px 16px;
            overflow: hidden;
            margin: 0;
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .table-modern thead th {
            padding: 20px 20px;
            font-weight: 700;
            font-size: 13px;
            color: #464457;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            vertical-align: middle;
        }

        .table-modern thead th i {
            margin-right: 8px;
            color: #667eea;
            font-size: 16px;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f0f3ff;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%);
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .table-modern tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            color: #464457;
            font-size: 14px;
        }

        /* ========================================
           ESTADO VACÍO
        ======================================== */
        .empty-state {
            border: none !important;
        }

        .empty-state td {
            padding: 60px 20px !important;
        }

        .empty-state__content {
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        .empty-state__icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        .empty-state__icon i {
            font-size: 36px;
            color: white;
        }

        .empty-state__content h4 {
            font-size: 18px;
            font-weight: 700;
            color: #464457;
            margin-bottom: 8px;
        }

        .empty-state__content p {
            font-size: 14px;
            color: #74788d;
            margin: 0;
        }

        /* ========================================
           BADGES Y UTILIDADES
        ======================================== */
        .kt-badge--pill {
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .kt-user-card-v2 {
            display: flex;
            align-items: center;
        }

        .kt-user-card-v2__details {
            display: flex;
            flex-direction: column;
        }

        .kt-user-card-v2__name {
            font-weight: 600;
            color: #464457;
            font-size: 1rem;
        }

        .kt-user-card-v2__desc {
            color: #74788d;
            font-size: 0.875rem;
        }

        /* ========================================
           ANIMACIONES
        ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* ========================================
           RESPONSIVE
        ======================================== */
        @media (max-width: 991px) {
            .filter-card {
                flex-direction: column;
                text-align: center;
            }

            .filter-card__icon {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .search-section__body .col-md-12 {
                margin-bottom: 15px;
            }

            .btn-search-modern {
                margin-top: 10px;
            }

            .kt-portlet__body--modern {
                padding: 20px 15px;
            }

            .table-modern thead th,
            .table-modern tbody td {
                padding: 12px 10px;
                font-size: 12px;
            }
        }

        /* ========================================
           SEPARADOR MEJORADO
        ======================================== */
        .kt-separator--dashed {
            border-top: 2px dashed #e8ecef;
            margin: 25px 0;
        }
    </style>

    </body>