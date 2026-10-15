<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
        <div class="kt-header-mobile__logo">
            <a href="panel">
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
                                        HISTORIAL DE MÓDULOS
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
                                            Estado de pagos del programa
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body hm-cuerpo">

                                    <!-- Selector de programa (si tiene uno solo, se elige solo) -->
                                    <div class="form-group mb-4">
                                        <label for="select_programa_modulos" class="hm-etiqueta">Programa</label>
                                        <select class="form-control select2-uniforme" id="select_programa_modulos" name="select_programa_modulos" disabled>
                                            <option value="">Cargando programas...</option>
                                        </select>
                                    </div>

                                    <div id="aviso_elegir_programa" class="hm-vacio" style="display: none;">
                                        <i class="flaticon2-layers"></i>
                                        <p>Elija un programa para ver sus pagos.</p>
                                    </div>

                                    <!-- Área de resultados -->
                                    <div id="area_resultados_modulos" style="display: none;">

                                        <!-- Resumen de pagos -->
                                        <div class="hm-resumen mb-4" id="resumen_pagos_container">
                                            <div class="row">
                                                <div class="col-4 hm-dato">
                                                    <span class="hm-dato-titulo">Costo total</span>
                                                    <strong id="total_programa">Bs. 0,00</strong>
                                                </div>
                                                <div class="col-4 hm-dato">
                                                    <span class="hm-dato-titulo">Pagado</span>
                                                    <strong class="text-success" id="total_pagado">Bs. 0,00</strong>
                                                    <small class="text-muted"><span id="cantidad_pagados">0</span> cuota(s)</small>
                                                </div>
                                                <div class="col-4 hm-dato">
                                                    <span class="hm-dato-titulo">Saldo</span>
                                                    <strong class="text-danger" id="total_pendiente">Bs. 0,00</strong>
                                                </div>
                                            </div>
                                            <div class="progress hm-barra" title="Avance del pago">
                                                <div class="progress-bar" id="barra_avance" role="progressbar" style="width: 0%;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between flex-wrap hm-avance">
                                                <span><strong id="porcentaje_avance">0%</strong> pagado</span>
                                                <span id="estado_plan_texto">Saldo por cancelar</span>
                                            </div>
                                        </div>

                                        <!-- Matrícula -->
                                        <div class="hm-seccion mb-4" id="matricula_container">
                                            <div class="hm-seccion-titulo">
                                                <i class="flaticon2-correct"></i> Matrícula
                                            </div>
                                            <div class="hm-seccion-cuerpo" id="matricula_cuerpo">
                                                <span class="text-muted">Sin información de matrícula</span>
                                            </div>
                                        </div>

                                        <!-- Cuotas del programa -->
                                        <div class="hm-seccion" id="modulos_pagados_container">
                                            <div class="hm-seccion-titulo">
                                                <i class="flaticon2-list-3"></i> Cuotas
                                            </div>
                                            <div class="hm-seccion-cuerpo">
                                                <table class="table table-sm mb-0" id="tabla_modulos_pagados">
                                                    <thead>
                                                        <tr>
                                                            <th>N°</th>
                                                            <th class="text-right">Monto</th>
                                                            <th>Generada</th>
                                                            <th>Pagada</th>
                                                            <th>N° voucher</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody_modulos_pagados"></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Clínicas: solo si tiene alguna (aparte del saldo del programa) -->
                                        <div class="hm-seccion mt-4" id="clinicas_container" style="display: none;">
                                            <div class="hm-seccion-titulo">
                                                <i class="flaticon2-medical-records"></i> Clínicas
                                                <small class="text-muted font-weight-normal">(aparte del saldo del programa)</small>
                                            </div>
                                            <div class="hm-seccion-cuerpo">
                                                <table class="table table-sm mb-0" id="tabla_clinicas">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-right">Monto</th>
                                                            <th>Generada</th>
                                                            <th>Pagada</th>
                                                            <th>N° voucher</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody_clinicas"></tbody>
                                                </table>
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
                    $Footer -> FooterResumidoControlador();
                ?>
            </div>
        </div>
    </div>

    <!-- Modal: Foto del recibo/voucher de una cuota. Tamaño fijo (ver estilo abajo)
         para que todas las fotos se muestren igual sin importar su resolución
         original. -->
    <div class="modal fade" id="modalFotoRecibo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="flaticon2-photograph"></i> Foto del Recibo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagen_recibo_grande" src="" alt="Foto del recibo" class="foto-recibo-tamano-fijo">
                </div>
            </div>
        </div>
    </div>

    <style>
        .hm-cuerpo {
            padding: 2rem;
            background: #f8f9fa;
        }

        .hm-etiqueta {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #56637a;
            margin-bottom: 4px;
        }

        .hm-vacio {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #B5B5C3;
        }

        .hm-vacio i {
            font-size: 2.5rem;
            color: #E4E6EF;
        }

        .hm-vacio p {
            margin: 0.75rem 0 0;
            font-weight: 500;
        }

        /* Resumen: costo, pagado y saldo con la barra de avance */
        .hm-resumen {
            background: #fff;
            border: 1px solid #E4E6EF;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .hm-dato {
            display: flex;
            flex-direction: column;
        }

        .hm-dato-titulo {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #7e8299;
        }

        .hm-dato strong {
            font-size: 1.25rem;
            color: #464E5F;
            white-space: nowrap;
        }

        .hm-dato strong.text-success { color: #1e7145 !important; }
        .hm-dato strong.text-danger { color: #c0392b !important; }

        .hm-barra {
            height: 8px;
            border-radius: 4px;
            background: #eef0f5;
            margin: 1rem 0 0.5rem;
        }

        .hm-barra .progress-bar {
            background: linear-gradient(90deg, #1e7145 0%, #27ae60 100%);
            border-radius: 4px;
        }

        .hm-avance {
            gap: 4px 12px;
            font-size: 0.82rem;
            color: #7e8299;
        }

        /* Matrícula y cuotas */
        .hm-seccion {
            background: #fff;
            border: 1px solid #E4E6EF;
            border-radius: 8px;
        }

        .hm-seccion-titulo {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #E4E6EF;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #464E5F;
        }

        .hm-seccion-titulo i {
            color: #1e3c72;
            margin-right: 4px;
        }

        .hm-seccion-cuerpo {
            padding: 1rem 1.25rem;
        }

        .hm-insignia {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            color: #fff;
            font-weight: 600;
            font-size: 0.72rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .hm-insignia-pagado { background: #1e7145; }
        .hm-insignia-pendiente { background: #b7791f; }
        .hm-insignia-rechazado { background: #c0392b; }
        .hm-insignia-anulado { background: #9aa0ac; }

        :is(#tabla_modulos_pagados, #tabla_clinicas) thead th {
            border-top: none;
            border-bottom: 2px solid #E4E6EF;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #7e8299;
            white-space: nowrap;
        }

        :is(#tabla_modulos_pagados, #tabla_clinicas) th,
        :is(#tabla_modulos_pagados, #tabla_clinicas) td {
            padding: 0.6rem 1rem;
        }

        :is(#tabla_modulos_pagados, #tabla_clinicas) tbody td {
            vertical-align: middle;
            color: #464E5F;
        }

        :is(#tabla_modulos_pagados, #tabla_clinicas) td[data-label="Monto"] {
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-ver-foto-cuota-estudiante {
            background: none;
            border: none;
            margin-left: 6px;
            padding: 0 4px;
            cursor: pointer;
            color: #2a5298;
        }

        /* Celular: resumen más compacto y cada cuota como tarjeta, con la etiqueta de
           cada dato a la izquierda */
        @media (max-width: 767px) {
            .hm-cuerpo {
                padding: 1rem;
            }

            .hm-resumen {
                padding: 1rem;
            }

            .hm-resumen .row {
                margin: 0 -4px;
            }

            .hm-resumen [class*="col-"] {
                padding: 0 4px;
            }

            .hm-dato strong {
                font-size: 0.95rem;
            }

            .hm-dato-titulo {
                font-size: 0.65rem;
            }

            .hm-seccion-cuerpo {
                padding: 0.75rem;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) thead {
                display: none;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas),
            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody,
            :is(#tabla_modulos_pagados, #tabla_clinicas) tr,
            :is(#tabla_modulos_pagados, #tabla_clinicas) td {
                display: block;
                width: 100%;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody tr {
                border: 1px solid #eef0f5;
                border-radius: 8px;
                margin-bottom: 10px;
                padding: 2px 12px;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody tr:last-child {
                margin-bottom: 0;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 7px 0;
                border-top: 1px solid #f3f4f8;
                text-align: right !important;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody tr td:first-child {
                border-top: none;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody td::before {
                content: attr(data-label);
                flex-shrink: 0;
                font-size: 0.75rem;
                font-weight: 600;
                color: #7e8299;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody td[colspan] {
                display: block;
                text-align: center !important;
                border: none;
            }

            :is(#tabla_modulos_pagados, #tabla_clinicas) tbody td[colspan]::before {
                content: none;
            }
        }

        .foto-recibo-tamano-fijo {
            width: 500px;
            max-width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 10px;
            background: #f8f9fa;
        }
    </style>

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
