<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="panel">
         <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
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
/* ---- Mis requisitos ----
   Por programa: resumen en dos pasos (documentos de matrícula y certificados para la
   titulación), luego cada paso con lo que falta arriba y lo ya hecho plegado. */
.mr-selector { margin-bottom: 16px; }
.mr-selector label {
    display: block; margin-bottom: 4px;
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #56637a;
}

.mr-tarjeta {
    background: #fff; border-radius: 8px; margin-bottom: 20px;
    border: 1px solid #e3e7ef; box-shadow: 0 2px 10px rgba(15, 23, 42, .05);
    overflow: hidden;
}

/* Cabecera: programa, datos de inscripción y situación */
.mr-cabecera { padding: 18px 22px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; border-bottom: 1px solid #eef0f5; }
.mr-programa { margin: 0; font-size: 1.05rem; font-weight: 700; color: #1e3c72; line-height: 1.35; }
.mr-sub { margin-top: 2px; font-size: 0.8rem; color: #7e8299; }
.mr-chip { flex-shrink: 0; padding: 3px 10px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; white-space: nowrap; }
.mr-chip-curso { background: #e8eef9; color: #1e3c72; }
.mr-chip-culminado { background: #e6f4ec; color: #1e7145; }
.mr-chip-abandono { background: #eef0f5; color: #6b7280; }

/* Resumen: un recuadro por paso */
.mr-pasos { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; padding: 18px 22px; background: #f8fafc; border-bottom: 1px solid #eef0f5; }
.mr-paso { display: flex; gap: 12px; align-items: center; padding: 14px 16px; background: #fff; border: 1px solid #e3e7ef; border-left: 4px solid #d4a017; border-radius: 6px; }
.mr-paso.ok { border-left-color: #1e7145; }
.mr-paso-num { flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fdf6e3; color: #b7791f; font-weight: 700; font-size: 0.85rem; }
.mr-paso.ok .mr-paso-num { background: #e6f4ec; color: #1e7145; }
.mr-paso-texto { min-width: 0; }
.mr-paso-titulo { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; color: #7e8299; }
.mr-paso-cuenta { font-size: 1.15rem; font-weight: 700; color: #1f2937; line-height: 1.3; }
.mr-paso-estado { font-size: 0.8rem; font-weight: 600; color: #b7791f; }
.mr-paso.ok .mr-paso-estado { color: #1e7145; }

/* Cada paso en detalle */
.mr-seccion { padding: 18px 22px; }
.mr-seccion + .mr-seccion { border-top: 1px solid #eef0f5; }
.mr-seccion-titulo { margin: 0 0 10px; font-size: 0.95rem; font-weight: 700; color: #1e3c72; }
.mr-mensaje { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 12px; }
.mr-mensaje i { font-size: 1.05rem; margin-top: 1px; }
.mr-mensaje-pendiente { background: #fdf6e3; color: #7a5a0c; }
.mr-mensaje-completo { background: #e6f4ec; color: #1e5f3b; }

.mr-lista { list-style: none; margin: 0; padding: 0; }
.mr-item { display: flex; align-items: center; gap: 12px; padding: 9px 0; border-top: 1px solid #f3f4f8; }
.mr-item:first-child { border-top: none; }
.mr-icono { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
.mr-icono-pendiente { background: #fdf6e3; color: #b7791f; }
.mr-icono-entregado { background: #e6f4ec; color: #1e7145; }
.mr-texto { flex: 1; min-width: 0; }
.mr-nombre { font-weight: 600; color: #374151; font-size: 0.88rem; }
.mr-opcional { margin-left: 6px; padding: 1px 6px; border-radius: 4px; background: #eef0f5; color: #6b7280; font-size: 0.68rem; font-weight: 600; vertical-align: middle; }
.mr-detalle { font-size: 0.78rem; color: #7e8299; }
.mr-derecha { flex-shrink: 0; font-size: 0.82rem; font-weight: 600; color: #374151; white-space: nowrap; font-variant-numeric: tabular-nums; }
.mr-derecha.mr-fecha { color: #1e7145; font-weight: 500; }
.mr-consultar { color: #7e8299; font-weight: 500; }
/* Certificados: el catálogo mezcla "Medio Magnético" con "PRO BIBLIOTECA..." */
.mr-titulacion .mr-nombre { text-transform: uppercase; font-size: 0.8rem; }
.mr-total { display: flex; justify-content: space-between; gap: 12px; margin-top: 4px; padding: 10px 0 0; border-top: 2px solid #e3e7ef; font-weight: 700; color: #1f2937; font-size: 0.9rem; }

/* Lo ya hecho, plegado */
.mr-hechos { margin-top: 10px; }
.mr-hechos > summary { list-style: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 600; color: #1e3c72; padding: 4px 0; }
.mr-hechos > summary::-webkit-details-marker { display: none; }
.mr-hechos > summary i { font-size: 0.7rem; transition: transform .15s; }
.mr-hechos[open] > summary i { transform: rotate(90deg); }
.mr-hechos .mr-lista { margin-top: 4px; }
.mr-hechos .mr-nombre { color: #6b7280; font-weight: 500; }

.mr-vacio { text-align: center; color: #9aa0ac; padding: 2.5rem 1rem; background: #fff; border-radius: 8px; }

/* Celular: menos relleno; fecha y precio bajo el nombre */
@media (max-width: 575px) {
    .mr-cabecera, .mr-pasos, .mr-seccion { padding-left: 16px; padding-right: 16px; }
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

    // Un documento o certificado: ícono, nombre (con "Opcional" si no es obligatorio),
    // detalle debajo y, a la derecha, la fecha o el precio
    function item(o) {
        return '<li class="mr-item">' +
            '<span class="mr-icono ' + (o.hecho ? 'mr-icono-entregado' : 'mr-icono-pendiente') + '">' +
                (o.hecho ? '<i class="fas fa-check"></i>' : '<i class="far fa-circle"></i>') + '</span>' +
            '<div class="mr-texto">' +
                '<div class="mr-nombre">' + esc(o.nombre) + (o.opcional ? '<span class="mr-opcional">Opcional</span>' : '') + '</div>' +
                (o.detalle ? '<div class="mr-detalle">' + o.detalle + '</div>' : '') +
            '</div>' +
            (o.derecha ? '<span class="mr-derecha' + (o.claseDerecha ? ' ' + o.claseDerecha : '') + '">' + o.derecha + '</span>' : '') +
        '</li>';
    }

    // Lo ya hecho va plegado: lo importante es lo que falta
    function plegado(titulo, items) {
        if (!items.length) return '';
        return '<details class="mr-hechos"><summary><i class="fas fa-chevron-right"></i> ' + titulo + ' (' + items.length + ')</summary>' +
            '<ul class="mr-lista">' + items.join('') + '</ul></details>';
    }

    function mensaje(completo, texto) {
        return '<div class="mr-mensaje ' + (completo ? 'mr-mensaje-completo' : 'mr-mensaje-pendiente') + '">' +
            '<i class="fas ' + (completo ? 'fa-check-circle' : 'fa-info-circle') + '"></i><span>' + texto + '</span></div>';
    }

    function bs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Recuadro del resumen de un paso
    function paso(numero, titulo, cuenta, estado, ok) {
        return '<div class="mr-paso' + (ok ? ' ok' : '') + '">' +
            '<span class="mr-paso-num">' + (ok ? '<i class="fas fa-check"></i>' : numero) + '</span>' +
            '<div class="mr-paso-texto"><div class="mr-paso-titulo">' + titulo + '</div>' +
            '<div class="mr-paso-cuenta">' + cuenta + '</div>' +
            '<div class="mr-paso-estado">' + estado + '</div></div></div>';
    }

    // Lo que falta pagar de la titulación (los que no tienen precio definido no suman)
    function totalPorPagar(t) {
        return t.certificados.reduce(function (suma, c) { return suma + (!c.pagado && c.precio ? parseFloat(c.precio) : 0); }, 0);
    }

    // Paso 1: documentos de matrícula
    function seccionDocumentos(p) {
        if (!p.requisitos.length) {
            return '<div class="mr-seccion"><h6 class="mr-seccion-titulo">Documentos de matrícula</h6>' +
                '<div class="mr-detalle">Este programa no tiene requisitos registrados.</div></div>';
        }
        var pendientes = p.requisitos.filter(function (r) { return r.Entregado !== 1; });
        var entregados = p.requisitos.filter(function (r) { return r.Entregado === 1; }).map(function (r) {
            return item({ hecho: true, nombre: r.Nombre, opcional: r.Obligatorio !== 1, detalle: r.Observacion ? esc(r.Observacion) : '',
                derecha: r.FechaEntrega ? fecha(r.FechaEntrega) : '', claseDerecha: 'mr-fecha' });
        });
        var faltan = p.total - p.entregados;
        var html = '<div class="mr-seccion"><h6 class="mr-seccion-titulo">Documentos de matrícula</h6>';
        if (p.completo) {
            html += mensaje(true, 'Entregó todos sus documentos. No necesita hacer nada más.');
        } else {
            html += mensaje(false, (faltan === 1 ? 'Le falta entregar <strong>1 documento</strong>.' : 'Le faltan entregar <strong>' + faltan + ' documentos</strong>.') +
                ' Preséntelos en la Coordinación de Posgrado.');
        }
        if (pendientes.length) {
            html += '<ul class="mr-lista">' + pendientes.map(function (r) {
                return item({ hecho: false, nombre: r.Nombre, opcional: r.Obligatorio !== 1, detalle: r.Observacion ? esc(r.Observacion) : '' });
            }).join('') + '</ul>';
        }
        return html + plegado('Ver documentos entregados', entregados) + '</div>';
    }

    // Paso 2: certificados que su grado exige para titularse. Solo consulta.
    function seccionTitulacion(p) {
        var t = p.titulacion;
        if (!t || !t.total) return '';
        var pendientes = t.certificados.filter(function (c) { return !c.pagado; });
        var pagados = t.certificados.filter(function (c) { return c.pagado; }).map(function (c) {
            return item({ hecho: true, nombre: c.nombre, detalle: c.voucher ? 'Voucher ' + esc(c.voucher) : '',
                derecha: c.fecha ? fecha(c.fecha) : '', claseDerecha: 'mr-fecha' });
        });
        var html = '<div class="mr-seccion mr-titulacion' + (t.completo ? ' mr-titulacion-completa' : '') + '" id="mr-titulacion-' + p.idInscripcion + '">' +
            '<h6 class="mr-seccion-titulo">Certificados para la titulación</h6>';
        if (t.completo) {
            html += mensaje(true, 'Tiene todos los certificados que se exigen para titularse.');
        } else {
            html += mensaje(false, (pendientes.length === 1 ? 'Le falta <strong>1 certificado</strong>.' : 'Le faltan <strong>' + pendientes.length + ' certificados</strong>.') +
                ' Solicítelos en Secretaría de Posgrado.');
            html += '<ul class="mr-lista">' + pendientes.map(function (c) {
                return item({ hecho: false, nombre: c.nombre, derecha: c.precio ? bs(c.precio) : '<span class="mr-consultar">Consultar</span>' });
            }).join('') + '</ul>';
            var sinPrecio = pendientes.filter(function (c) { return !c.precio; }).length;
            html += '<div class="mr-total"><span>Total por pagar' + (sinPrecio ? ' <small class="mr-consultar">(sin los que dicen "Consultar")</small>' : '') + '</span>' +
                '<span>' + bs(totalPorPagar(t)) + '</span></div>';
        }
        return html + plegado('Ver certificados pagados', pagados) + '</div>';
    }

    // Tarjeta de un programa: resumen de los dos pasos y luego cada uno en detalle
    function tarjeta(p) {
        var situacion = situaciones[p.situacion] || situaciones['EN CURSO'];
        var sub = [nombresGrado[p.grado] || p.grado || ''];
        if (p.fechaInscripcion) sub.push('Inscrito el ' + fecha(p.fechaInscripcion));

        var t = p.titulacion;
        var pasos = '';
        if (p.requisitos.length) {
            pasos += paso(1, 'Documentos de matrícula', p.entregados + ' de ' + p.total + ' entregados',
                p.completo ? 'Completo' : 'Pendiente', p.completo);
        }
        if (t && t.total) {
            pasos += paso(2, 'Certificados para la titulación', t.cumplidos + ' de ' + t.total + ' pagados',
                t.completo ? 'Completo' : 'Por pagar: ' + bs(totalPorPagar(t)), t.completo);
        }

        return '<div class="mr-tarjeta">' +
            '<div class="mr-cabecera">' +
                '<div><h5 class="mr-programa">' + esc(p.programa) + '</h5>' +
                '<div class="mr-sub">' + esc(sub.filter(Boolean).join(' · ')) + '</div></div>' +
                '<span class="mr-chip ' + situacion.clase + '">' + situacion.texto + '</span>' +
            '</div>' +
            (pasos ? '<div class="mr-pasos">' + pasos + '</div>' : '') +
            seccionDocumentos(p) +
            seccionTitulacion(p) +
        '</div>';
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
