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


        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 class="">

                    ORDEN DE PAGO </h2>

                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h4>
                    
                    </h4>
                   
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

            <div class="container">

                <!-- Formulario de Búsqueda -->
                <div class="kt-portlet mb-4">
                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="kt-portlet__head-label">
                            <h3 style="color: white; margin: 0;">
                                <i class="flaticon2-search"></i> Buscar Estudiante
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="flaticon2-user"></i> <strong>Buscar Estudiante</strong></label>
                                    <select class="form-control select2-estudiantes" id="select_estudiante">
                                        <option value=""></option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle"></i> Puede escribir directamente en el campo para buscar por nombre, apellido o CI
                                    </small>
                                </div>
                            </div>
                           
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-secondary btn-block" id="btn_limpiar_busqueda" style="height: 45px;">
                                        <i class="fa fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Área de Resultados del Estudiante -->
                <div id="area_datos_estudiante" style="display: none;">

                    <!-- Información del Estudiante -->
                    <div class="kt-portlet mb-4">
                        <div class="kt-portlet__head" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="kt-portlet__head-label">
                                <h3 style="color: white; margin: 0;">
                                    <i class="flaticon2-user-1"></i> <span id="nombre_estudiante_header">Estudiante</span>
                                </h3>
                            </div>
                        </div>
                        <div class="kt-portlet__body" style="padding: 1.5rem;">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong style="color: #B5B5C3;">C.I.:</strong>
                                    <h5 id="dato_ci" style="color: #464E5F; font-weight: 600;">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <strong style="color: #B5B5C3;">Correo:</strong>
                                    <h5 id="dato_correo" style="color: #464E5F; font-weight: 600;">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <strong style="color: #B5B5C3;">Celular:</strong>
                                    <h5 id="dato_celular" style="color: #464E5F; font-weight: 600;">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <strong style="color: #B5B5C3;">Profesión:</strong>
                                    <h5 id="dato_profesion" style="color: #464E5F; font-weight: 600;">-</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de Pagos de Módulos -->
                    <div class="kt-portlet">
                        <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="kt-portlet__head-label">
                                <h3 style="color: white; margin: 0;">
                                    <i class="flaticon2-shopping-cart"></i> Detalle de Pagos de Módulos
                                </h3>
                            </div>
                        </div>
                        <div class="kt-portlet__body">
                            <div id="contenido_tabla_pagos">
                                <div class="text-center p-3">
                                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                                    <p class="mt-2">Cargando detalle de pagos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje inicial -->
               <div id="mensaje_inicial" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="alert alert-secondary text-center p-4 w-100" role="alert" style="max-width: 450px; border: 1px solid #dee2e6; border-radius: 12px; background-color: #f8f9fa;">

                        <i class="fas fa-list-alt fa-3x mb-3" style="color: #adb5bd;"></i>

                        <h5 class="fw-bold" style="color: #495057;">
                            Listado de Estudiantes
                        </h5>

                        <p class="mb-0 text-muted">
                            Por favor, elija un nombre de la lista para cargar sus datos y revisar los pagos correspondientes a los módulos.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Modal para Adicionar Datos -->
            <div class="modal fade" id="modalAdicionarDatos" tabindex="-1" role="dialog" aria-labelledby="modalAdicionarDatosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h5 class="modal-title" id="modalAdicionarDatosLabel">
                                <i class="fa fa-edit"></i> Adicionar Datos del Pago
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                            <form id="form_adicionar_datos">
                                <input type="hidden" id="modal_pago_id" name="pagoID">

                                <!-- Datos del Estudiante (No editables) -->
                                <div class="kt-portlet mb-3" style="margin-bottom: 1rem;">
                                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); min-height: 40px;">
                                        <div class="kt-portlet__head-label">
                                            <h6 style="color: white; margin: 0; font-size: 14px;">
                                                <i class="fa fa-user"></i> DATOS DEL ESTUDIANTE
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="kt-portlet__body" style="padding: 1rem;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">APELLIDO PATERNO</small>
                                                <p id="modal_estudiante_apaterno" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">APELLIDO MATERNO</small>
                                                <p id="modal_estudiante_amaterno" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">NOMBRES</small>
                                                <p id="modal_estudiante_nombres" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">CORREO ELECTRÓNICO</small>
                                                <p id="modal_estudiante_correo" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">C.I.</small>
                                                <p id="modal_estudiante_ci" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <small style="color: #B5B5C3; font-weight: 600;">N° CELULAR</small>
                                                <p id="modal_estudiante_celular" style="margin: 0; color: #464E5F; font-weight: 600;">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos para la Emisión de Comprobante de Pago -->
                                <div class="kt-portlet mb-3" style="margin-bottom: 1rem;">
                                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #fd397a 0%, #e91e63 100%); min-height: 40px;">
                                        <div class="kt-portlet__head-label">
                                            <h6 style="color: white; margin: 0; font-size: 14px;">
                                                <i class="fa fa-receipt"></i> DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="kt-portlet__body" style="padding: 1rem;">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <small style="color: #B5B5C3; font-weight: 600;">PROGRAMA</small>
                                                <p id="modal_programa_nombre" style="margin: 0; color: #464E5F; font-weight: 600; font-size: 14px;">-</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <small style="color: #B5B5C3; font-weight: 600;">MÓDULO</small>
                                                <p id="modal_modulo_nombre" style="margin: 0; color: #464E5F; font-weight: 600; font-size: 14px;">-</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <small style="color: #B5B5C3; font-weight: 600;">MONTO (NUMERAL)</small>
                                                <p id="modal_monto_numeral" style="margin: 0; color: #11998e; font-weight: 700; font-size: 18px;">Bs. 0.00</p>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <small style="color: #B5B5C3; font-weight: 600;">MONTO (LITERAL)</small>
                                                <p id="modal_monto_literal" style="margin: 0; color: #464E5F; font-weight: 600; font-size: 12px; font-style: italic;">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario de Registro -->
                                <hr>
                                <h6 style="color: #464E5F; font-weight: 700; margin-bottom: 1rem;">
                                    <i class="fa fa-edit"></i> FORMULARIO DE REGISTRO
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>VERSIÓN</strong> <span style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="version" name="version" placeholder="Ej: V-1" readonly>
                                            <small class="form-text text-muted">Se obtiene del programa</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>N° DE TRÁMITE (CUENTA AUXILIAR)</strong> <span style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="numero_tramite" name="numeroTramite" placeholder="Ej: 2024-001" readonly>
                                            <small class="form-text text-muted">Se obtiene del programa - Este es el número de cuenta auxiliar</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos para la Emisión de Factura -->
                                <div class="kt-portlet mb-3" style="margin-bottom: 1rem;">
                                    <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 40px;">
                                        <div class="kt-portlet__head-label">
                                            <h6 style="color: white; margin: 0; font-size: 14px;">
                                                <i class="fa fa-file-invoice"></i> DATOS PARA LA EMISIÓN DE FACTURA
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="kt-portlet__body" style="padding: 1rem;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>NOMBRE DE LA FACTURA</strong> <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="nombre_factura" name="nombreFactura" placeholder="Nombre para la factura">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><strong>NIT O CI</strong> <span style="color: red;">*</span></label>
                                                    <input type="text" class="form-control" id="nit_ci_factura" name="nitCiFactura" placeholder="NIT o CI para factura">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Denominación de la Cuenta -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert" style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 1rem;">
                                            <h6 style="color: #464E5F; font-weight: 700; margin-bottom: 0.5rem;">
                                                <i class="fa fa-university"></i> DENOMINACIÓN DE LA CUENTA
                                            </h6>
                                            <p style="margin: 0; color: #464E5F; font-weight: 600; font-size: 14px;">
                                                UTO - APORTES EXTRAORDINARIOS - NÚMERO DE CUENTA 10000006050938
                                            </p>
                                            <p style="margin: 0; color: #11998e; font-weight: 700; font-size: 14px;">
                                                NIT: 120129022
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Responsable y Firma -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>RESPONSABLE</strong> <span style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Nombre del responsable">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>FIRMA</strong></label>
                                            <input type="text" class="form-control" id="firma" name="firma" placeholder="Firma del responsable">
                                            <small class="form-text text-muted">Puede dejarlo vacío si firmará físicamente</small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fa fa-times"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" id="btn_imprimir_pdf">
                                <i class="fa fa-file-pdf"></i> Imprimir Orden de Pago
                            </button>
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

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Cargar scripts después de jQuery -->
  <script>
    window.addEventListener('load', function() {
        console.log('Window loaded - Cargando Select2 y script de ordenpago...');

        // Verificar que jQuery esté cargado
        if (typeof jQuery === 'undefined') {
            console.error('jQuery no está cargado!');
            return;
        }

        // Cargar Select2
        var select2Script = document.createElement('script');
        select2Script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
        select2Script.onload = function() {
            console.log('Select2 cargado exitosamente');

            // Después de cargar Select2, cargar el script de ordenpago
            var ordenpagoScript = document.createElement('script');
            ordenpagoScript.src = 'vistas/recursos/assets/js/scripts/ordenpago.js?v=<?php echo time(); ?>';
            ordenpagoScript.onload = function() {
                console.log('Script de ordenpago cargado exitosamente');
            };
            document.body.appendChild(ordenpagoScript);
        };
        document.body.appendChild(select2Script);
    });
  </script>

  <style>
    /* Estilos para Select2 */
    .select2-container--default .select2-selection--single {
        height: 45px !important;
        padding: 6px 12px !important;
        border: 1px solid #e2e5ec !important;
        border-radius: 4px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 8px !important;
        color: #464E5F !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px !important;
        right: 8px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #B5B5C3 !important;
    }

    /* Dropdown del select2 */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #667eea !important;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e5ec !important;
        border-radius: 4px !important;
        padding: 8px 12px !important;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #667eea !important;
        outline: none !important;
    }

  <style>
    .programa-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #667eea;
    }

    .programa-card h5 {
        color: #464E5F;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .badge-programa {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 11px;
    }

    .badge-estado-activo {
        background: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 10px;
    }

    .tabla-modulos {
        margin-top: 1rem;
    }

    .tabla-modulos th {
        background: #f8f9fa;
        color: #464E5F;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        padding: 10px;
    }

    .tabla-modulos td {
        padding: 10px;
        vertical-align: middle;
    }

    .badge-pagado {
        background: #28a745;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 11px;
    }

    .badge-pendiente {
        background: #ffc107;
        color: #000;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 11px;
    }
  </style>
</body>