<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
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

                <!-- Misma estructura que las demás páginas del estudiante (kt-body / kt-content
                     con el pie adentro): sin ella el tema calcula mal el alto y no deja bajar. -->
                <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                    <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title">MIS REQUISITOS</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>ACCESO ESTUDIANTIL</h4>
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
                            <div class="alert alert-light mb-4" style="border-left: 4px solid #1e3c72; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                                <i class="flaticon2-information text-primary"></i>
                                Estos son los documentos que debe entregar en la Coordinación de Posgrado para completar su
                                matrícula. Si alguno figura como <strong>pendiente</strong>, preséntelo en oficinas.
                            </div>
                            <div id="misRequisitos">
                                <div class="text-center text-muted p-5">Cargando sus requisitos...</div>
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
<style>
.mr-lista { list-style: none; margin: 0; padding: 0; }
.mr-item { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eef0f4; }
.mr-item:last-child { border-bottom: 0; }
.mr-icono { font-size: 18px; width: 22px; text-align: center; padding-top: 1px; }
.mr-nombre { font-weight: 600; color: #1f2937; }
.mr-detalle { font-size: 12px; color: #6b7280; }
/* Selector de programa (cuando tiene más de uno) */
.mr-selector { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 12px; margin-bottom: 16px; }
.mr-selector label { margin: 0; font-weight: 600; color: #1f2937; }
.mr-selector select { max-width: 100%; min-width: 280px; }
.mr-cabecera { background: #1e3c72; color: #fff; }
.mr-cabecera small { color: #c9d6ee; }
.mr-cabecera.mr-cerrado { background: #6b7280; }
.mr-cabecera.mr-cerrado small { color: #e5e7eb; }
</style>

<script>
// jQuery se carga al final de la plantilla: esperar a que esté.
document.addEventListener('DOMContentLoaded', function () {
    // Situación de cada programa (la calcula RequisitoControlador::SituacionPrograma)
    var situaciones = {
        'EN CURSO':  { texto: 'En curso',  clase: 'badge-primary' },
        'CULMINADO': { texto: 'Culminado', clase: 'badge-success' },
        'ABANDONO':  { texto: 'Abandono',  clase: 'badge-secondary' }
    };
    var nombresGrado = { DIPLOMADO: 'Diplomado', ESPECIALIDAD: 'Especialidad', MAESTRIA: 'Maestría', 'MAESTRÍA': 'Maestría', DOCTORADO: 'Doctorado' };

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function fecha(f) {
        if (!f) return '';
        var p = f.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function badgeSituacion(s) {
        var d = situaciones[s] || situaciones['EN CURSO'];
        return '<span class="badge ' + d.clase + '">' + d.texto + '</span>';
    }

    // Tarjeta con los requisitos de un programa
    function tarjeta(p) {
        var cerrado = p.situacion !== 'EN CURSO';
        var html = '<div class="card shadow-sm mb-4">' +
            '<div class="card-header mr-cabecera' + (cerrado ? ' mr-cerrado' : '') + ' d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">' +
                '<div>' +
                    '<h5 class="mb-1 text-white">' + esc(p.programa) + ' ' + badgeSituacion(p.situacion) + '</h5>' +
                    '<small>' + esc(nombresGrado[p.grado] || p.grado || '') +
                        (p.fechaInscripcion ? ' · Inscrito el ' + fecha(p.fechaInscripcion) : '') + '</small>' +
                '</div>' +
                '<span class="badge ' + (p.completo ? 'badge-success' : 'badge-warning') + '" style="font-size:13px;">' +
                    (p.completo ? 'Completo' : 'Pendiente') + ': ' + p.entregados + ' de ' + p.total + '</span>' +
            '</div>' +
            '<div class="card-body py-2"><ul class="mr-lista">';

        p.requisitos.forEach(function (r) {
            var entregado = r.Entregado === 1;
            html += '<li class="mr-item">' +
                '<span class="mr-icono">' + (entregado
                    ? '<i class="fas fa-check-circle text-success"></i>'
                    : '<i class="far fa-circle text-warning"></i>') + '</span>' +
                '<div><div class="mr-nombre">' + esc(r.Nombre) +
                    (r.Obligatorio === 1 ? '' : ' <small class="text-muted">(opcional)</small>') + '</div>' +
                '<div class="mr-detalle">' + (entregado
                    ? 'Entregado' + (r.FechaEntrega ? ' el ' + fecha(r.FechaEntrega) : '')
                    : 'Pendiente de entrega') +
                    (r.Observacion ? ' · ' + esc(r.Observacion) : '') + '</div></div>' +
                '</li>';
        });
        if (!p.requisitos.length) {
            html += '<li class="mr-item text-muted">Este programa no tiene requisitos registrados.</li>';
        }

        return html + '</ul></div></div>';
    }

    $.post('ajax/requisito.ajax.php', { accion: 'misRequisitos' }, function (resp) {
        if (!resp || !resp.success) {
            $('#misRequisitos').html('<div class="alert alert-warning">No se pudieron cargar sus requisitos.</div>');
            return;
        }
        var programas = resp.programas;
        if (!programas.length) {
            $('#misRequisitos').html('<div class="alert alert-info">Aún no tiene una matrícula registrada.</div>');
            return;
        }

        // Un solo programa: la tarjeta directamente
        if (programas.length === 1) {
            $('#misRequisitos').html(tarjeta(programas[0]));
            return;
        }

        // Varios programas (cursa dos a la vez, o culminó uno y empezó otro): un
        // select para elegir cuál ver. Al entrar se abren los que siguen en curso.
        var enCurso = programas.filter(function (p) { return p.situacion === 'EN CURSO'; });
        var opciones = '';
        if (enCurso.length) {
            opciones += '<option value="curso">Programas en curso (' + enCurso.length + ')</option>';
        }
        programas.forEach(function (p, i) {
            var d = situaciones[p.situacion] || situaciones['EN CURSO'];
            opciones += '<option value="' + i + '">' + esc(p.programa) + ' — ' + d.texto + '</option>';
        });
        opciones += '<option value="todos">Todos mis programas (' + programas.length + ')</option>';

        $('#misRequisitos').html(
            '<div class="mr-selector">' +
                '<label for="mrSelectPrograma"><i class="fas fa-graduation-cap"></i> Programa:</label>' +
                '<select id="mrSelectPrograma" class="form-control">' + opciones + '</select>' +
                '<small class="text-muted">Usted tiene ' + programas.length + ' programas registrados.</small>' +
            '</div>' +
            '<div id="mrProgramasVista"></div>'
        );

        function mostrar(valor) {
            var lista = valor === 'curso' ? enCurso
                : valor === 'todos' ? programas
                : [programas[parseInt(valor)]];
            $('#mrProgramasVista').html(lista.map(tarjeta).join(''));
        }

        $('#mrSelectPrograma').on('change', function () { mostrar(this.value); });
        // Sin programas en curso se abre el primero de la lista (el más reciente)
        mostrar(enCurso.length ? 'curso' : '0');
        $('#mrSelectPrograma').val(enCurso.length ? 'curso' : '0');
    }, 'json').fail(function () {
        $('#misRequisitos').html('<div class="alert alert-warning">No se pudieron cargar sus requisitos.</div>');
    });
});
</script>

</body>
