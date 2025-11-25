<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");

    // === 1. DATOS DE EJEMPLO PHP ===
    // En una aplicación real, esta información vendría de una base de datos.
    $programas = [
        ['id' => 1, 'nombre' => 'Ingeniería de Software', 'modulos' => ['Fundamentos', 'Bases de Datos', 'Arquitectura']],
        ['id' => 2, 'nombre' => 'Diseño Gráfico Digital', 'modulos' => ['Teoría del Color', 'Diseño UI', 'Animación 3D']],
        ['id' => 3, 'nombre' => 'Marketing Digital', 'modulos' => ['SEO/SEM', 'Estrategia de Contenido', 'Analítica Web']],
    ];
    // =============================
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
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
                                    <h2 class="">BUSCAR</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>MODULOS DE PROGRAMAS</h4>
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
                            <div class="kt-portlet">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">
                                            🔍 Buscador de Módulos por Programa
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body">
                                    <div class="form-group">
                                        <label for="programa-select">**Seleccione un Programa:**</label>
                                        
                                        <select class="form-control" id="programa-select" onchange="mostrarModulos()">
                                            <option value="">-- Seleccionar un Programa --</option>
                                            <?php foreach ($programas as $programa): ?>
                                                <option value="<?php echo $programa['id']; ?>">
                                                    <?php echo htmlspecialchars($programa['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <hr>

                                    <div id="modulos-container">
                                        <p class="text-muted">Seleccione un programa de la lista para ver los módulos asociados.</p>
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

    <script>
        // Copiar los datos de PHP a una variable JavaScript para el manejo en el cliente
        const datosProgramas = <?php echo json_encode($programas); ?>;

        function mostrarModulos() {
            const selectElement = document.getElementById('programa-select');
            const modulosContainer = document.getElementById('modulos-container');
            const programaId = selectElement.value;

            // Limpiar el contenedor anterior
            modulosContainer.innerHTML = '';

            // Si no se ha seleccionado un programa (valor vacío)
            if (!programaId) {
                modulosContainer.innerHTML = '<p class="text-muted">Seleccione un programa de la lista para ver los módulos asociados.</p>';
                return;
            }

            // Buscar el programa seleccionado por su ID
            const programaSeleccionado = datosProgramas.find(p => p.id == parseInt(programaId));

            if (programaSeleccionado) {
                // Título
                let htmlContent = `<h4>✅ Módulos para: **${programaSeleccionado.nombre}**</h4>`;
                
                // Lista de Módulos
                htmlContent += '<ul class="list-group">';
                programaSeleccionado.modulos.forEach(modulo => {
                    htmlContent += `<li class="list-group-item"><i class="flaticon2-cube"></i> ${modulo}</li>`;
                });
                htmlContent += '</ul>';

                modulosContainer.innerHTML = htmlContent;
            } else {
                modulosContainer.innerHTML = '<p class="text-danger">Programa no encontrado.</p>';
            }
        }
    </script>
    </body>