<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
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

            <div class="kt-subheader kt-grid__item" id="kt_subheader">
              <div class="kt-container">
                <div class="kt-subheader__main">
                  <h2>HISTORIAL DE PAGOS</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <a href="#" class="kt-subheader__breadcrumbs-link">
                      <h3>CONSULTA POR C.I.</h3>
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

              <!-- Buscador unificado de estudiante -->
              <div class="kt-portlet historialpagos-portlet">
                <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                  <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title text-white">
                      <i class="flaticon2-search"></i> Búsqueda de Estudiante
                    </h3>
                  </div>
                </div>
                <div class="kt-portlet__body py-4">
                  <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                      <label class="filtro-label d-block mb-2">Buscar estudiante</label>
                      <div class="input-group historial-busqueda-unificada">
                        <input type="text" id="valorBusquedaHistorial" class="form-control"
                               placeholder="Ingrese el C.I. o el nombre y/o apellidos del estudiante" maxlength="150" autocomplete="off">
                        <div class="input-group-append">
                          <button type="button" id="btnBuscarHistorial" class="btn btn-primary">
                            <i class="flaticon2-search"></i> Buscar
                          </button>
                        </div>
                      </div>
                      <small class="form-text text-muted mt-2">
                        <i class="flaticon2-information"></i> Puede buscar por número de C.I. (sin complemento ni expedido) o escribiendo el nombre y/o apellidos.
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Estado vacío / sin búsqueda todavía -->
              <div id="historialSinBuscar" class="text-center text-muted py-5">
                <i class="flaticon2-time" style="font-size: 3rem; color: #E4E6EF;"></i>
                <p class="mt-3 mb-0" style="font-size: 1.05rem;">Busque un estudiante por C.I. o por nombre/apellidos para ver su historial de pagos</p>
              </div>

              <!-- Resultado: lista de coincidencias (búsqueda por nombre con varios resultados) -->
              <div id="historialListaResultados" style="display:none;">
                <div class="kt-portlet historialpagos-portlet">
                  <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                      <h3 class="kt-portlet__head-title">
                        <i class="flaticon2-user"></i> Seleccione un estudiante
                      </h3>
                    </div>
                  </div>
                  <div class="kt-portlet__body py-3">
                    <div class="table-responsive">
                      <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th>Apellidos y Nombre</th>
                            <th>C.I.</th>
                            <th>Correo</th>
                            <th class="text-center">Acción</th>
                          </tr>
                        </thead>
                        <tbody id="historialListaResultadosBody"></tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Resultado: datos del estudiante -->
              <div id="historialResultado" style="display:none;">

                <div class="kt-portlet historialpagos-portlet">
                  <div class="kt-portlet__body py-3">
                    <div class="row align-items-center">
                      <div class="col-md-8">
                        <h4 class="mb-1 font-weight-bold" id="historialEstudianteNombre"></h4>
                        <small class="text-muted" id="historialEstudianteDatos"></small>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="historialProgramas"></div>

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

<!-- Modal: Foto del voucher (matrícula o cuota, según a quién se invoque) -->
<div class="modal fade" id="modalFotoHistorialPago" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <h5 class="modal-title text-white"><i class="fa fa-image"></i> Foto del Voucher</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-2" id="infoVoucherHistorialPago" style="display:none;">
          <span class="text-muted small d-block">N° VOUCHER</span>
          <strong id="numeroVoucherHistorialPagoGrande" style="font-size:1.1rem;"></strong>
        </p>
        <img id="imagenHistorialPagoGrande" src="" alt="Foto del voucher"
             style="max-width:100%; max-height:70vh; border-radius:10px; background:#f8f9fa;">
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar pago (matrícula o cuota, según data-tipo del botón que lo abrió).
     Corrige errores de captura: fecha, N° de voucher, monto y, opcionalmente, la
     foto (si no se selecciona una nueva, se conserva la actual). No cambia el
     Estado del pago ni vuelve a validar/confirmar nada. -->
<div class="modal fade" id="modalEditarPagoHistorial" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <h5 class="modal-title text-white" id="tituloEditarPagoHistorial"><i class="fa fa-pencil-alt"></i> Editar pago</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formEditarPagoHistorial" enctype="multipart/form-data">
        <input type="hidden" id="editPagoTipo" name="tipo">
        <input type="hidden" id="editPagoId">
        <div class="modal-body">
          <div class="form-group">
            <label class="small font-weight-bold mb-1">N° de Voucher <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="editPagoVoucher" maxlength="100" required>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="small font-weight-bold mb-1">Fecha de pago <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="editPagoFecha" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="small font-weight-bold mb-1">Monto (Bs.) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" class="form-control" id="editPagoMonto" required>
            </div>
          </div>
          <div class="form-group mb-0">
            <label class="small font-weight-bold mb-1">Foto del voucher</label>
            <input type="file" class="form-control-file" id="editPagoFoto" accept="image/*">
            <small class="form-text text-muted">Opcional: solo si desea reemplazar la foto actual (máx. 5MB).</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.historialpagos-portlet {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    overflow: hidden;
}

.historial-busqueda-unificada .form-control:focus {
    box-shadow: none;
    border-color: #2a5298;
}

.historial-busqueda-unificada .btn-primary {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border: none;
    font-weight: 600;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.programa-historial-card {
    background: #fff;
    border: 1px solid #e1e3ea;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.programa-historial-head {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.programa-historial-head h5 {
    color: #fff;
    margin: 0;
    font-weight: 700;
}

.programa-historial-body {
    padding: 1.5rem;
}

.seccion-detalle-pago {
    background-color: #fbfbfd;
    border: 1px solid #e1e3ea;
    border-radius: 6px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.seccion-detalle-pago-titulo {
    font-size: 12px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 10px;
}

.foto-voucher-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
}

.badge-tipo-plan {
    font-size: 12px;
    padding: 6px 12px;
}
</style>

<script>
function iniciarHistorialPagos() {

    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    const etiquetasTipoPlan = {
        REGULAR: 'Plan Regular',
        DESCUENTO: 'Plan al Contado',
        GRUPAL: 'Plan Grupal'
    };

    const badgeTipoPlan = {
        REGULAR: 'badge-primary',
        DESCUENTO: 'badge-success',
        GRUPAL: 'badge-info'
    };

    const badgeEstadoCuota = {
        PAGADO: 'badge-success',
        PENDIENTE: 'badge-warning',
        RECHAZADO: 'badge-danger',
        ANULADO: 'badge-secondary'
    };

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        const partes = fecha.split('-');
        if (partes.length !== 3) return fecha;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // Escapar un valor para usarlo dentro de un atributo HTML armado por
    // concatenación de strings (data-voucher, etc.), evitando que comillas u
    // otros caracteres del voucher rompan el markup.
    function escaparAtributo(valor) {
        const div = document.createElement('div');
        div.textContent = String(valor);
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function renderPrograma(programa) {
        const tipoPlan = programa.TipoPlan
            ? '<span class="badge ' + (badgeTipoPlan[programa.TipoPlan] || 'badge-secondary') + ' badge-tipo-plan">' +
                (etiquetasTipoPlan[programa.TipoPlan] || programa.TipoPlan) + '</span>'
            : '<span class="badge badge-light badge-tipo-plan">Sin plan definido</span>';

        const fotoMatriculaHtml = programa.TieneFotoMatricula === 'SI'
            ? '<img src="ajax/obtener-foto-matricula.php?idInscripcion=' + programa.idInscripcion + '&thumb=1" ' +
              'class="foto-voucher-thumb" alt="Foto voucher matrícula" ' +
              'data-url="ajax/obtener-foto-matricula.php?idInscripcion=' + programa.idInscripcion + '" ' +
              'data-voucher="' + escaparAtributo(programa.nvauchermatricula || '-') + '">'
            : '<span class="text-muted small">Sin foto</span>';

        let filasCuotas = '';
        if (programa.Cuotas && programa.Cuotas.length > 0) {
            programa.Cuotas.forEach(function(c) {
                const fotoCuotaHtml = c.TieneFoto === 'SI'
                    ? '<img src="ajax/obtener-voucher-imagen.php?id=' + c.Idpagomodulo + '&thumb=1" ' +
                      'class="foto-voucher-thumb" alt="Foto voucher cuota" ' +
                      'data-url="ajax/obtener-voucher-imagen.php?id=' + c.Idpagomodulo + '" ' +
                      'data-voucher="' + escaparAtributo(c.NumeroVoucher || '-') + '">'
                    : '<span class="text-muted small">Sin foto</span>';

                filasCuotas += '<tr>' +
                    '<td class="text-center">' + (c.NumeroCuota || '-') + '</td>' +
                    '<td class="text-center">Bs. ' + parseFloat(c.Monto).toFixed(2) + '</td>' +
                    '<td class="text-center">' + formatearFecha(c.FechaPago) + '</td>' +
                    '<td>' + (c.NumeroVoucher || '-') + '</td>' +
                    '<td class="text-center"><span class="badge ' + (badgeEstadoCuota[c.Estado] || 'badge-secondary') + '">' + c.Estado + '</span></td>' +
                    '<td class="text-center">' + fotoCuotaHtml + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-warning btnEditarPagoHistorial" title="Editar cuota" ' +
                            'data-tipo="cuota" data-id="' + c.Idpagomodulo + '" ' +
                            'data-fecha="' + (c.FechaPago || '') + '" data-voucher="' + (c.NumeroVoucher ? c.NumeroVoucher.replace(/"/g, '&quot;') : '') + '" ' +
                            'data-monto="' + parseFloat(c.Monto).toFixed(2) + '" ' +
                            'data-titulo="Editar cuota' + (c.NumeroCuota ? ' N° ' + c.NumeroCuota : '') + '">' +
                            '<i class="fa fa-pencil-alt"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });
        } else {
            filasCuotas = '<tr><td colspan="7" class="text-center text-muted py-3">Sin cuotas registradas</td></tr>';
        }

        const matriculaExonerada = programa.EstadoInscripcion && parseFloat(programa.costomatricula) === 0 && !programa.nvauchermatricula;

        return '<div class="programa-historial-card">' +
            '<div class="programa-historial-head">' +
                '<h5><i class="fa fa-graduation-cap"></i> ' + programa.NombrePrograma + ' (' + programa.GradoAcademico + ')</h5>' +
                tipoPlan +
            '</div>' +
            '<div class="programa-historial-body">' +

                '<div class="seccion-detalle-pago">' +
                    '<div class="seccion-detalle-pago-titulo"><i class="fa fa-file-invoice"></i> Matrícula</div>' +
                    (matriculaExonerada
                        ? '<div class="text-muted small">Sin voucher de matrícula (inscrito sin pago de matrícula; el programa completo se cobra por cuotas).</div>'
                        : '<div class="row align-items-center">' +
                            '<div class="col-md-2"><small class="text-muted d-block">Monto</small><strong>Bs. ' + parseFloat(programa.costomatricula || 0).toFixed(2) + '</strong></div>' +
                            '<div class="col-md-2"><small class="text-muted d-block">Fecha</small><strong>' + formatearFecha(programa.FechaInscripcion) + '</strong></div>' +
                            '<div class="col-md-3"><small class="text-muted d-block">N° Voucher</small><strong>' + (programa.nvauchermatricula || '-') + '</strong></div>' +
                            '<div class="col-md-2"><small class="text-muted d-block">Foto</small>' + fotoMatriculaHtml + '</div>' +
                            '<div class="col-md-3 text-md-right">' +
                                '<button type="button" class="btn btn-sm btn-outline-warning btnEditarPagoHistorial" title="Editar matrícula" ' +
                                    'data-tipo="matricula" data-id="' + programa.idInscripcion + '" ' +
                                    'data-fecha="' + (programa.FechaInscripcion || '') + '" data-voucher="' + (programa.nvauchermatricula ? programa.nvauchermatricula.replace(/"/g, '&quot;') : '') + '" ' +
                                    'data-monto="' + parseFloat(programa.costomatricula || 0).toFixed(2) + '" ' +
                                    'data-titulo="Editar matrícula">' +
                                    '<i class="fa fa-pencil-alt"></i> Editar' +
                                '</button>' +
                            '</div>' +
                          '</div>') +
                '</div>' +

                '<div class="seccion-detalle-pago mb-0">' +
                    '<div class="seccion-detalle-pago-titulo"><i class="fa fa-history"></i> Cuotas del programa' +
                        (programa.NumeroCuotasPlan ? ' <span class="text-muted font-weight-normal">(plan: ' + programa.NumeroCuotasPlan + ' cuotas)</span>' : '') +
                    '</div>' +
                    '<div class="table-responsive">' +
                        '<table class="table table-sm table-bordered table-hover mb-0">' +
                            '<thead class="thead-light">' +
                                '<tr>' +
                                    '<th class="text-center" style="width:60px;">N°</th>' +
                                    '<th class="text-center">Monto</th>' +
                                    '<th class="text-center">Fecha</th>' +
                                    '<th>Voucher</th>' +
                                    '<th class="text-center">Estado</th>' +
                                    '<th class="text-center">Foto</th>' +
                                    '<th class="text-center">Acción</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' + filasCuotas + '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>' +

            '</div>' +
        '</div>';
    }

    // Último criterio usado, para poder refrescar el historial tras editar un
    // pago sin importar si se llegó por C.I., por nombre (único) o eligiendo
    // de la lista de coincidencias.
    let ultimaBusqueda = null;

    function mostrarResultadoEstudiante(resp) {
        const est = resp.estudiante;
        const nombreCompleto = est.Apaterno + ' ' + est.Amaterno + ' ' + est.Nombre;
        let ciCompleto = est.Ci;
        if (est.Complemento) ciCompleto += '-' + est.Complemento;
        ciCompleto += ' ' + est.Exp;

        $('#historialEstudianteNombre').text(nombreCompleto);
        $('#historialEstudianteDatos').html(
            '<i class="fa fa-id-card"></i> C.I. ' + ciCompleto +
            (est.Correo ? ' &nbsp;|&nbsp; <i class="fa fa-envelope"></i> ' + est.Correo : '') +
            (est.Celular ? ' &nbsp;|&nbsp; <i class="fa fa-mobile-alt"></i> ' + est.Celular : '')
        );

        if (!resp.programas || resp.programas.length === 0) {
            $('#historialProgramas').html(
                '<div class="text-center text-muted py-5"><i class="flaticon2-information" style="font-size:2.5rem;"></i>' +
                '<p class="mt-3 mb-0">Este estudiante no tiene inscripciones registradas.</p></div>'
            );
        } else {
            let html = '';
            resp.programas.forEach(function(p) {
                html += renderPrograma(p);
            });
            $('#historialProgramas').html(html);
        }

        $('#historialListaResultados').hide();
        $('#historialSinBuscar').hide();
        $('#historialResultado').show();
    }

    function refrescarUltimaBusqueda() {
        if (!ultimaBusqueda) return;
        if (ultimaBusqueda.tipo === 'ci') {
            buscarPorCi();
        } else {
            buscarPorEstudianteId(ultimaBusqueda.estudianteId);
        }
    }

    function buscarPorCi() {
        const ci = $('#valorBusquedaHistorial').val().trim();

        if (!ci) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un C.I. para buscar' });
            return;
        }

        const $btn = $('#btnBuscarHistorial');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorCi', ci: ci },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="flaticon2-search"></i> Buscar');

                if (!resp.success) {
                    $('#historialResultado').hide();
                    $('#historialSinBuscar').show();
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró al estudiante' });
                    return;
                }

                ultimaBusqueda = { tipo: 'ci' };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="flaticon2-search"></i> Buscar');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    // Un solo campo de búsqueda con varios parámetros posibles: si lo escrito
    // son solo dígitos se busca por C.I., si trae letras se busca por nombre
    // y/o apellidos. Así no hace falta un selector de criterio aparte.
    function ejecutarBusquedaHistorial() {
        const valor = $('#valorBusquedaHistorial').val().trim();

        if (!valor) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un C.I. o un nombre para buscar' });
            return;
        }

        const esSoloDigitos = /^\d+$/.test(valor);

        if (esSoloDigitos) {
            buscarPorCi();
        } else {
            buscarPorNombre();
        }
    }

    function renderListaCoincidencias(estudiantes) {
        let filas = '';
        estudiantes.forEach(function(est) {
            let ciCompleto = est.Ci;
            if (est.Complemento) ciCompleto += '-' + est.Complemento;
            ciCompleto += ' ' + est.Exp;

            filas += '<tr>' +
                '<td>' + est.Apaterno + ' ' + est.Amaterno + ' ' + est.Nombre + '</td>' +
                '<td>' + ciCompleto + '</td>' +
                '<td>' + (est.Correo || '-') + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-primary btnVerHistorialEstudiante" data-id="' + est.EstudianteID + '">' +
                        '<i class="flaticon2-search"></i> Ver historial' +
                    '</button>' +
                '</td>' +
            '</tr>';
        });
        $('#historialListaResultadosBody').html(filas);
        $('#historialResultado').hide();
        $('#historialSinBuscar').hide();
        $('#historialListaResultados').show();
    }

    function buscarPorEstudianteId(estudianteId) {
        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorEstudianteId', estudianteId: estudianteId },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró al estudiante' });
                    return;
                }

                ultimaBusqueda = { tipo: 'estudianteId', estudianteId: estudianteId };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    function buscarPorNombre() {
        const nombre = $('#valorBusquedaHistorial').val().trim();

        if (nombre.length < 3) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese al menos 3 caracteres para buscar' });
            return;
        }

        const $btn = $('#btnBuscarHistorial');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: { accion: 'buscarPorNombre', nombre: nombre },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="flaticon2-search"></i> Buscar');

                if (!resp.success) {
                    $('#historialResultado').hide();
                    $('#historialListaResultados').hide();
                    $('#historialSinBuscar').show();
                    Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se encontró ningún estudiante' });
                    return;
                }

                if (resp.tipo === 'lista') {
                    renderListaCoincidencias(resp.estudiantes);
                    return;
                }

                ultimaBusqueda = { tipo: 'estudianteId', estudianteId: resp.estudiante.EstudianteID };
                mostrarResultadoEstudiante(resp);
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="flaticon2-search"></i> Buscar');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo realizar la búsqueda' });
            }
        });
    }

    $('#btnBuscarHistorial').on('click', ejecutarBusquedaHistorial);
    $('#valorBusquedaHistorial').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            ejecutarBusquedaHistorial();
        }
    });

    $(document).on('click', '.btnVerHistorialEstudiante', function() {
        buscarPorEstudianteId($(this).data('id'));
    });

    // Ver la foto en grande: se dispara desde las miniaturas (data-url/data-voucher),
    // mostrando también el N° de voucher arriba de la imagen para que coincida con
    // el dato que ya se ve en la fila/tarjeta.
    $(document).on('click', '.foto-voucher-thumb', function() {
        const $img = $(this);
        const numeroVoucher = $img.data('voucher');

        $('#imagenHistorialPagoGrande').attr('src', $img.data('url'));

        if (numeroVoucher) {
            $('#numeroVoucherHistorialPagoGrande').text(numeroVoucher);
            $('#infoVoucherHistorialPago').show();
        } else {
            $('#infoVoucherHistorialPago').hide();
        }

        $('#modalFotoHistorialPago').modal('show');
    });

    // ============================================
    // EDITAR PAGO (matrícula o cuota): corrige fecha, N° de voucher, monto y,
    // opcionalmente, la foto. Al guardar, se vuelve a buscar el mismo C.I. para
    // refrescar el historial con los datos corregidos.
    // ============================================
    $(document).on('click', '.btnEditarPagoHistorial', function() {
        const $btn = $(this);

        $('#tituloEditarPagoHistorial').html('<i class="fa fa-pencil-alt"></i> ' + $btn.data('titulo'));
        $('#editPagoTipo').val($btn.data('tipo'));
        $('#editPagoId').val($btn.data('id'));
        $('#editPagoVoucher').val($btn.data('voucher'));
        $('#editPagoFecha').val($btn.data('fecha'));
        $('#editPagoMonto').val($btn.data('monto'));
        $('#editPagoFoto').val('');

        $('#modalEditarPagoHistorial').modal('show');
    });

    $('#formEditarPagoHistorial').on('submit', function(e) {
        e.preventDefault();

        const tipo = $('#editPagoTipo').val();
        const id = $('#editPagoId').val();
        const accion = tipo === 'matricula' ? 'actualizarMatricula' : 'actualizarCuota';

        const formData = new FormData();
        formData.append('accion', accion);
        formData.append(tipo === 'matricula' ? 'idInscripcion' : 'idPagoModulo', id);
        formData.append('numeroVoucher', $('#editPagoVoucher').val().trim());
        formData.append('fechaPago', $('#editPagoFecha').val());
        formData.append('monto', $('#editPagoMonto').val());

        const archivoFoto = $('#editPagoFoto')[0].files[0];
        if (archivoFoto) {
            formData.append('fotoVoucher', archivoFoto);
        }

        const $btnSubmit = $(this).find('button[type="submit"]');
        $btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/historialpagos.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                $btnSubmit.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Cambios');

                if (!resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo guardar el cambio' });
                    return;
                }

                $('#modalEditarPagoHistorial').modal('hide');
                Swal.fire({ icon: 'success', title: 'Guardado', text: resp.mensaje, timer: 1800, showConfirmButton: false })
                    .then(function() {
                        refrescarUltimaBusqueda();
                    });
            },
            error: function() {
                $btnSubmit.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Cambios');
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el cambio' });
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarHistorialPagos);
} else {
    iniciarHistorialPagos();
}
</script>
