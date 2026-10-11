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
                            <div id="misRequisitos">
                                <div class="mr-vacio">Cargando sus requisitos...</div>
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
/* ---- Mis requisitos ---- */
.mr-selector { margin-bottom: 16px; }
.mr-selector label {
    display: block; margin-bottom: 4px;
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #56637a;
}

.mr-tarjeta {
    background: #fff; border-radius: 12px; margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(30, 60, 114, 0.08), 0 6px 16px rgba(30, 60, 114, 0.05);
    overflow: hidden;
}

/* Cabecera: programa, datos de inscripción y situación */
.mr-cabecera { padding: 18px 22px 0; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.mr-programa { margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e3c72; line-height: 1.35; }
.mr-sub { margin-top: 2px; font-size: 0.8rem; color: #7e8299; }
.mr-chip {
    flex-shrink: 0; padding: 3px 10px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 600; white-space: nowrap;
}
.mr-chip-curso { background: #e8eef9; color: #1e3c72; }
.mr-chip-culminado { background: #e6f4ec; color: #1e7145; }
.mr-chip-abandono { background: #eef0f5; color: #6b7280; }

/* Avance: contador, barra y qué hacer */
.mr-avance { padding: 16px 22px 18px; border-bottom: 1px solid #eef0f5; }
.mr-avance-fila { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
.mr-contador { font-size: 1.5rem; font-weight: 700; color: #464E5F; line-height: 1; }
.mr-contador small { font-size: 0.85rem; font-weight: 500; color: #7e8299; }
.mr-porcentaje { font-size: 0.85rem; font-weight: 600; color: #7e8299; }
.mr-barra { height: 8px; border-radius: 4px; background: #eef0f5; margin: 10px 0 12px; overflow: hidden; }
.mr-barra > div { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #d4a017 0%, #e0b23a 100%); transition: width .4s ease; }
.mr-tarjeta.mr-completo .mr-barra > div { background: linear-gradient(90deg, #1e7145 0%, #27ae60 100%); }
.mr-mensaje { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 8px; font-size: 0.85rem; }
.mr-mensaje i { font-size: 1.05rem; margin-top: 1px; }
.mr-mensaje-pendiente { background: #fdf6e3; color: #7a5a0c; }
.mr-mensaje-completo { background: #e6f4ec; color: #1e5f3b; }

/* Grupos de documentos */
.mr-grupo { padding: 14px 22px 6px; }
.mr-grupo + .mr-grupo { border-top: 1px solid #eef0f5; }
/* Pantalla ancha: por entregar y entregados lado a lado */
@media (min-width: 992px) {
    .mr-grupos { display: flex; }
    .mr-grupos .mr-grupo { flex: 1; min-width: 0; padding-bottom: 14px; }
    .mr-grupo + .mr-grupo { border-top: none; border-left: 1px solid #eef0f5; }
}
.mr-grupo-titulo { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #7e8299; margin-bottom: 6px; }
.mr-lista { list-style: none; margin: 0; padding: 0; }
.mr-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-top: 1px solid #f3f4f8; }
.mr-item:first-child { border-top: none; }
.mr-icono {
    flex-shrink: 0; width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
}
.mr-icono-pendiente { background: #fdf6e3; color: #b7791f; }
.mr-icono-entregado { background: #e6f4ec; color: #1e7145; }
.mr-texto { flex: 1; min-width: 0; }
.mr-nombre { font-weight: 600; color: #374151; font-size: 0.9rem; }
.mr-opcional { margin-left: 6px; padding: 1px 6px; border-radius: 4px; background: #eef0f5; color: #6b7280; font-size: 0.68rem; font-weight: 600; vertical-align: middle; }
.mr-detalle { font-size: 0.78rem; color: #7e8299; }
.mr-detalle-fecha { display: none; }
.mr-fecha { flex-shrink: 0; font-size: 0.78rem; color: #1e7145; font-weight: 600; white-space: nowrap; }
.mr-entregados-lista .mr-nombre { color: #6b7280; font-weight: 500; }

.mr-vacio { text-align: center; color: #9aa0ac; padding: 2.5rem 1rem; background: #fff; border-radius: 12px; }

/* Celular: menos relleno y la fecha de entrega pasa debajo del nombre */
@media (max-width: 575px) {
    .mr-cabecera { padding: 14px 16px 0; }
    .mr-avance { padding: 14px 16px; }
    .mr-grupo { padding: 12px 16px 4px; }
    .mr-fecha { display: none; }
    .mr-detalle-fecha { display: inline; }
}
</style>

<script>
// jQuery se carga al final de la plantilla: esperar a que esté.
document.addEventListener('DOMContentLoaded', function () {
    // Situación de cada programa (la calcula RequisitoControlador::SituacionPrograma)
    var situaciones = {
        'EN CURSO':  { texto: 'En curso',  clase: 'mr-chip-curso' },
        'CULMINADO': { texto: 'Culminado', clase: 'mr-chip-culminado' },
        'ABANDONO':  { texto: 'Abandono',  clase: 'mr-chip-abandono' }
    };
    var nombresGrado = { DIPLOMADO: 'Diplomado', ESPECIALIDAD: 'Especialidad', MAESTRIA: 'Maestría', 'MAESTRÍA': 'Maestría', DOCTORADO: 'Doctorado' };

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function fecha(f) {
        if (!f) return '';
        var p = String(f).slice(0, 10).split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    // Un documento: ícono, nombre (con "Opcional" si no es obligatorio),
    // observación y, si ya se entregó, la fecha a la derecha
    function item(r, entregado) {
        var detalle = [];
        if (entregado && r.FechaEntrega) {
            detalle.push('<span class="mr-detalle-fecha">Entregado el ' + fecha(r.FechaEntrega) + '</span>');
        }
        if (r.Observacion) detalle.push(esc(r.Observacion));

        return '<li class="mr-item">' +
            '<span class="mr-icono ' + (entregado ? 'mr-icono-entregado' : 'mr-icono-pendiente') + '">' +
                (entregado ? '<i class="fas fa-check"></i>' : '<i class="far fa-file-alt"></i>') + '</span>' +
            '<div class="mr-texto">' +
                '<div class="mr-nombre">' + esc(r.Nombre) +
                    (r.Obligatorio === 1 ? '' : '<span class="mr-opcional">Opcional</span>') + '</div>' +
                (detalle.length ? '<div class="mr-detalle">' + detalle.join(' · ') + '</div>' : '') +
            '</div>' +
            (entregado && r.FechaEntrega ? '<span class="mr-fecha">' + fecha(r.FechaEntrega) + '</span>' : '') +
        '</li>';
    }

    // Tarjeta con los requisitos de un programa: avance arriba, luego lo que falta
    // entregar y al final lo ya entregado
    function tarjeta(p) {
        var situacion = situaciones[p.situacion] || situaciones['EN CURSO'];
        var pendientes = p.requisitos.filter(function (r) { return r.Entregado !== 1; });
        var entregados = p.requisitos.filter(function (r) { return r.Entregado === 1; });
        var porcentaje = p.total > 0 ? Math.round((p.entregados / p.total) * 100) : 100;
        var sub = [nombresGrado[p.grado] || p.grado || ''];
        if (p.fechaInscripcion) sub.push('Inscrito el ' + fecha(p.fechaInscripcion));

        var html = '<div class="mr-tarjeta' + (p.completo ? ' mr-completo' : '') + '">' +
            '<div class="mr-cabecera">' +
                '<div><h5 class="mr-programa">' + esc(p.programa) + '</h5>' +
                '<div class="mr-sub">' + esc(sub.filter(Boolean).join(' · ')) + '</div></div>' +
                '<span class="mr-chip ' + situacion.clase + '">' + situacion.texto + '</span>' +
            '</div>';

        if (!p.requisitos.length) {
            return html + '<div class="mr-avance"><div class="mr-vacio" style="padding:1rem;">Este programa no tiene requisitos registrados.</div></div></div>';
        }

        var faltan = pendientes.length;
        html += '<div class="mr-avance">' +
                '<div class="mr-avance-fila">' +
                    '<span class="mr-contador">' + p.entregados + ' <small>de ' + p.total + ' documentos entregados</small></span>' +
                    '<span class="mr-porcentaje">' + porcentaje + '%</span>' +
                '</div>' +
                '<div class="mr-barra"><div style="width:' + porcentaje + '%;"></div></div>' +
                (p.completo
                    ? '<div class="mr-mensaje mr-mensaje-completo"><i class="fas fa-check-circle"></i><span>Entregó todos sus documentos. No necesita hacer nada más.</span></div>'
                    : '<div class="mr-mensaje mr-mensaje-pendiente"><i class="fas fa-info-circle"></i><span>' +
                        (faltan === 1 ? 'Le falta <strong>1 documento</strong>.' : 'Le faltan <strong>' + faltan + ' documentos</strong>.') +
                        ' Preséntelos en la Coordinación de Posgrado.</span></div>') +
            '</div>';

        html += '<div class="mr-grupos">';
        if (pendientes.length) {
            html += '<div class="mr-grupo"><div class="mr-grupo-titulo">Por entregar (' + pendientes.length + ')</div>' +
                '<ul class="mr-lista">' + pendientes.map(function (r) { return item(r, false); }).join('') + '</ul></div>';
        }
        if (entregados.length) {
            html += '<div class="mr-grupo"><div class="mr-grupo-titulo">Entregados (' + entregados.length + ')</div>' +
                '<ul class="mr-lista mr-entregados-lista">' + entregados.map(function (r) { return item(r, true); }).join('') + '</ul></div>';
        }

        return html + '</div></div>';
    }

    $.post('ajax/requisito.ajax.php', { accion: 'misRequisitos' }, function (resp) {
        if (!resp || !resp.success) {
            $('#misRequisitos').html('<div class="mr-vacio">No se pudieron cargar sus requisitos.</div>');
            return;
        }
        var programas = resp.programas;
        if (!programas.length) {
            $('#misRequisitos').html('<div class="mr-vacio">Aún no tiene una matrícula registrada.</div>');
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
                '<label for="mrSelectPrograma">Programa</label>' +
                '<select id="mrSelectPrograma" class="form-control">' + opciones + '</select>' +
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
        $('#misRequisitos').html('<div class="mr-vacio">No se pudieron cargar sus requisitos.</div>');
    });
});
</script>
</body>
