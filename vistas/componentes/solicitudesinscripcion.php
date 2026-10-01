<?php
/**
 * Panel ADM/SEC: Solicitudes de Inscripción recibidas desde el formulario
 * suelto "Inscribirme" del panel de estudiante/docente. Es solo de consulta y
 * seguimiento (cambiar Estado); no crea preregistros ni matrículas. Cuando
 * corresponda, el propio ADM/SEC inicia el trámite formal desde
 * "Nuevo registro" (Pre-Registro) con los datos que ve aquí.
 */
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

$tipoUsuario = $_SESSION['Tipo'] ?? '';
if (!in_array($tipoUsuario, ['ADM', 'SEC', 'AUX'], true)) {
    header('Location: panel');
    exit;
}
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
                  <h2>SOLICITUDES DE INSCRIPCIÓN</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>ESTUDIANTES INTERESADOS</h3>
                    </a>
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

              <div class="alert alert-light border mb-3" style="font-size: 13px;">
                <i class="fa fa-info-circle text-primary"></i>
                Estas solicitudes las envían estudiantes o docentes desde el botón "Inscribirme" del panel.
                Son solo un aviso de interés: para inscribir formalmente al estudiante, use
                <strong>Nuevo registro</strong> (Pre-Registro) como siempre.
              </div>

              <div class="kt-portlet">
                <div class="kt-portlet__head" style="background-color: #34495e;">
                  <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title text-white">
                      <i class="fa fa-list"></i> Listado de Solicitudes
                    </h3>
                  </div>
                  <div class="kt-portlet__head-toolbar">
                    <select id="filtroEstadoSolicitud" class="form-control form-control-sm" style="min-width: 180px;">
                      <option value="">Todos los estados</option>
                      <option value="PENDIENTE" selected>Pendientes</option>
                      <option value="CONTACTADO">Contactados</option>
                      <option value="DESCARTADO">Descartados</option>
                    </select>
                  </div>
                </div>
                <div class="kt-portlet__body">
                  <div id="loadingSolicitudes" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                  </div>
                  <div class="table-responsive" id="tablaSolicitudesContainer" style="display:none;">
                    <table class="table table-bordered table-hover">
                      <thead class="thead-light">
                        <tr>
                          <th>Fecha</th>
                          <th>Interesado</th>
                          <th>C.I.</th>
                          <th>Contacto</th>
                          <th>Programa de interés</th>
                          <th>Mensaje</th>
                          <th class="text-center">Estado</th>
                          <th class="text-center">Acciones</th>
                        </tr>
                      </thead>
                      <tbody id="tablaSolicitudesBody"></tbody>
                    </table>
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
</div>

<script>
$(document).ready(function() {
    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    function escaparHtml(texto) {
        return $('<div>').text(texto == null ? '' : texto).html();
    }

    function formatearFecha(fechaHora) {
        if (!fechaHora) return '-';
        const d = new Date(fechaHora.replace(' ', 'T'));
        if (isNaN(d.getTime())) return fechaHora;
        return d.toLocaleDateString('es-ES') + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    const badgeEstado = {
        PENDIENTE: 'badge-warning',
        CONTACTADO: 'badge-success',
        DESCARTADO: 'badge-secondary'
    };

    function cargarSolicitudes() {
        $('#loadingSolicitudes').show();
        $('#tablaSolicitudesContainer').hide();

        $.ajax({
            url: 'ajax/solicitudinscripcion.ajax.php',
            type: 'POST',
            data: { accion: 'listar', estado: $('#filtroEstadoSolicitud').val() },
            dataType: 'json',
            success: function(resp) {
                $('#loadingSolicitudes').hide();
                $('#tablaSolicitudesContainer').show();

                if (!resp.success) {
                    $('#tablaSolicitudesBody').html('<tr><td colspan="8" class="text-center text-danger py-4">' + escaparHtml(resp.mensaje || 'No se pudo cargar la lista') + '</td></tr>');
                    return;
                }

                if (!resp.solicitudes || resp.solicitudes.length === 0) {
                    $('#tablaSolicitudesBody').html('<tr><td colspan="8" class="text-center text-muted py-4">No hay solicitudes con ese filtro</td></tr>');
                    return;
                }

                let filas = '';
                resp.solicitudes.forEach(function(s) {
                    const nombreCompleto = escaparHtml([s.Apaterno, s.Amaterno, s.Nombre].filter(Boolean).join(' '));
                    const ciCompleto = escaparHtml(s.Ci + (s.Complemento ? '-' + s.Complemento : ''));
                    const programa = escaparHtml(s.ProgramaNombreActual || s.ProgramaNombre || 'No especificado');
                    const badge = badgeEstado[s.Estado] || 'badge-light';

                    filas += '<tr>' +
                        '<td><small>' + formatearFecha(s.FechaSolicitud) + '</small></td>' +
                        '<td><strong>' + nombreCompleto + '</strong></td>' +
                        '<td>' + ciCompleto + '</td>' +
                        '<td><small>' +
                            (s.Celular ? '<i class="fa fa-mobile-alt"></i> ' + escaparHtml(s.Celular) : '') +
                            (s.Correo ? '<br><i class="fa fa-envelope"></i> ' + escaparHtml(s.Correo) : '') +
                        '</small></td>' +
                        '<td>' + programa + '</td>' +
                        '<td><small>' + (s.Mensaje ? escaparHtml(s.Mensaje) : '<span class="text-muted">-</span>') + '</small></td>' +
                        '<td class="text-center"><span class="badge ' + badge + '">' + s.Estado + '</span></td>' +
                        '<td class="text-center">' + botonesAccion(s) + '</td>' +
                    '</tr>';
                });

                $('#tablaSolicitudesBody').html(filas);
            },
            error: function() {
                $('#loadingSolicitudes').hide();
                $('#tablaSolicitudesContainer').show();
                $('#tablaSolicitudesBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Error al conectar con el servidor</td></tr>');
            }
        });
    }

    function botonesAccion(s) {
        let html = '<div class="btn-group btn-group-sm" role="group">';
        if (s.Estado !== 'CONTACTADO') {
            html += '<button type="button" class="btn btn-success btn-cambiar-estado" data-id="' + s.IdSolicitud + '" data-estado="CONTACTADO" title="Marcar como contactado"><i class="fa fa-check"></i></button>';
        }
        if (s.Estado !== 'DESCARTADO') {
            html += '<button type="button" class="btn btn-outline-danger btn-cambiar-estado" data-id="' + s.IdSolicitud + '" data-estado="DESCARTADO" title="Descartar"><i class="fa fa-times"></i></button>';
        }
        if (s.Estado !== 'PENDIENTE') {
            html += '<button type="button" class="btn btn-outline-secondary btn-cambiar-estado" data-id="' + s.IdSolicitud + '" data-estado="PENDIENTE" title="Volver a pendiente"><i class="fa fa-undo"></i></button>';
        }
        html += '</div>';
        return html;
    }

    $('#filtroEstadoSolicitud').on('change', cargarSolicitudes);

    $(document).on('click', '.btn-cambiar-estado', function() {
        const id = $(this).data('id');
        const estado = $(this).data('estado');

        $.ajax({
            url: 'ajax/solicitudinscripcion.ajax.php',
            type: 'POST',
            data: { accion: 'actualizarEstado', idSolicitud: id, estado: estado },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo actualizar' });
                    return;
                }
                cargarSolicitudes();
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el estado' });
            }
        });
    });

    cargarSolicitudes();
});
</script>
</body>
