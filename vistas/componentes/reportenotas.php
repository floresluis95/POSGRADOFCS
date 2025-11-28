<?php
/**
 * Vista: Reporte de Notas por Programa
 * Lista todos los programas, sus módulos y permite generar reportes PDF
 */

// Validación de sesión
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();

date_default_timezone_set("America/La_Paz");

// Generar Token CSRF
$csrf_token = bin2hex(random_bytes(32));
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
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
            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 class="">REPORTES DE CALIFICACIONES</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h3>Reporte de Notas por Programa</h3>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                  <div id="lafecha" style="font-size:13pt"></div>
                  </div>
                </div>
              </div>
            </div>

<!-- end:: Subheader -->
<!-- begin:: Content -->
<div class="app-content content">
  <div class="content-wrapper">
    <div class="content-body">
      <section id="html">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-gradient-primary">
                <h4 class="card-title text-white mb-0">
                  <i class="fas fa-file-alt"></i> Programas Académicos - Reportes de Notas
                </h4>
                <div class="heading-elements">
                  <button type="button" class="btn btn-light btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt mr-1"></i>Actualizar
                  </button>
                </div>
              </div>
              <div class="card-content collapse show">
                <div class="card-body card-dashboard">
                  <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="tablaProgramas">
                      <thead class="thead-dark">
                        <tr>
                          <th class="text-center" style="width: 5%;">Nº</th>
                          <th style="width: 35%;">
                            <i class="fas fa-graduation-cap"></i> Programa Académico
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-award"></i> Grado
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-code"></i> Código
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-book"></i> Módulos
                          </th>
                          <th class="text-center" style="width: 10%;">
                            <i class="fas fa-users"></i> Estudiantes
                          </th>
                          <th class="text-center" style="width: 15%;">
                            <i class="fas fa-cog"></i> Acciones
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $ListaProgramas = new ReporteNotasControlador();
                          $ListaProgramas -> ListarProgramasConModulosControlador();
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<style>
  .table thead.thead-dark th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 15px 10px;
    font-size: 13px;
    vertical-align: middle;
  }

  .table tbody tr {
    transition: all 0.2s ease;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.005);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }

  .table tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    border-color: #e9ecef;
    font-size: 13px;
  }

  .card.shadow-sm {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    border: none;
    border-radius: 10px;
    overflow: hidden;
  }

  .card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .btn-sm {
    padding: 5px 10px;
    font-size: 12px;
  }

  .badge {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
  }
</style>

<!-- Modal: Ver Módulos del Programa -->
<div class="modal fade" id="ModalModulosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalModulosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h4 class="modal-title text-white" id="modalModulosLabel">
          <i class="fas fa-book mr-2"></i> Módulos del Programa: <span id="nombreProgramaModal"></span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 30px;">
        <div id="loadingModulos" class="text-center" style="display: none;">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
          </div>
          <p class="mt-3">Cargando módulos...</p>
        </div>
        <div id="contenedorModulos"></div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa;">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<?php
  $Footer = new FuncionesControladores();
  $Footer -> FooterControlador();
?>

<!-- Scripts -->
<script>
$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaProgramas').DataTable({
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay programas disponibles",
            zeroRecords: "No se encontraron resultados"
        }
    });

    // Evento: Ver módulos del programa
    $(document).on('click', '.btnVerModulos', function() {
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');

        $('#nombreProgramaModal').text(programaNombre);
        $('#loadingModulos').show();
        $('#contenedorModulos').html('');

        // Petición AJAX para obtener módulos
        $.ajax({
            url: 'ajax/reportenotas.ajax.php',
            type: 'POST',
            data: {
                action: 'obtenerModulos',
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingModulos').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center" style="width: 5%;">Nº</th>';
                    html += '<th>Código</th>';
                    html += '<th>Módulo</th>';
                    html += '<th>Docente</th>';
                    html += '<th class="text-center">Inscritos</th>';
                    html += '<th class="text-center">Calificados</th>';
                    html += '<th class="text-center">Estado</th>';
                    html += '<th class="text-center">Acciones</th>';
                    html += '</tr>';
                    html += '</thead><tbody>';

                    let contador = 0;
                    response.data.forEach(function(modulo) {
                        contador++;
                        const inscritos = parseInt(modulo.TotalInscritos) || 0;
                        const calificados = parseInt(modulo.TotalCalificados) || 0;
                        const porcentaje = inscritos > 0 ? Math.round((calificados / inscritos) * 100) : 0;

                        let estadoBadge = '';
                        if (calificados === 0) {
                            estadoBadge = '<span class="badge badge-secondary">Sin calificar</span>';
                        } else if (calificados < inscritos) {
                            estadoBadge = '<span class="badge badge-warning">Parcial (' + porcentaje + '%)</span>';
                        } else {
                            estadoBadge = '<span class="badge badge-success">Completo (100%)</span>';
                        }

                        html += '<tr>';
                        html += '<td class="text-center">' + contador + '</td>';
                        html += '<td><strong>' + (modulo.codigomodulo || 'N/A') + '</strong></td>';
                        html += '<td>' + modulo.nombremodulo + '</td>';
                        html += '<td>' + (modulo.NombreDocente || '<span class="text-muted">Sin docente</span>') + '</td>';
                        html += '<td class="text-center"><span class="badge badge-info">' + inscritos + '</span></td>';
                        html += '<td class="text-center"><span class="badge badge-primary">' + calificados + '</span></td>';
                        html += '<td class="text-center">' + estadoBadge + '</td>';
                        html += '<td class="text-center">';

                        if (calificados > 0) {
                            html += '<button type="button" class="btn btn-success btn-sm btnGenerarPDF" ' +
                                   'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                   'data-programa-id="' + programaID + '" ' +
                                   'data-modulo-nombre="' + modulo.nombremodulo + '" ' +
                                   'title="Generar PDF">' +
                                   '<i class="fas fa-file-pdf"></i> PDF' +
                                   '</button>';
                        } else {
                            html += '<button type="button" class="btn btn-secondary btn-sm" disabled>' +
                                   '<i class="fas fa-ban"></i> Sin notas' +
                                   '</button>';
                        }

                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#contenedorModulos').html(html);
                } else {
                    $('#contenedorModulos').html('<div class="alert alert-warning text-center">' +
                        '<i class="fas fa-exclamation-triangle"></i> Este programa no tiene módulos registrados.' +
                        '</div>');
                }
            },
            error: function() {
                $('#loadingModulos').hide();
                $('#contenedorModulos').html('<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-times-circle"></i> Error al cargar los módulos.' +
                    '</div>');
            }
        });
    });

    // Evento: Generar PDF
    $(document).on('click', '.btnGenerarPDF', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');

        if (confirm('¿Generar reporte PDF de calificaciones para el módulo: ' + moduloNombre + '?')) {
            // Redirigir a la página de generación de PDF
            window.open('extensiones/tcpdf/pdf/planilla_calificaciones.php?moduloID=' + moduloID + '&programaID=' + programaID, '_blank');
        }
    });

    // Actualizar fecha
    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);
});
</script>

</body>
