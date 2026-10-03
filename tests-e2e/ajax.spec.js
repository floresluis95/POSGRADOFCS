// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Permisos de los archivos de ajax/: se llaman directo (como desde la consola del
// navegador) con la sesión de cada usuario de prueba.
// Las acciones que ESCRIBEN solo se prueban negadas, sin datos y con IDs que no
// existen: así, aunque un permiso se rompiera, la petición no guardaría nada.
// Requiere los usuarios de prueba: tests-e2e/datos/usuarios-prueba.sql

const ID_INEXISTENTE = '999999';

/** POST a un archivo de ajax/ con la sesión del navegador (o sin sesión). */
async function llamar(request, archivo, datos = {}) {
  const respuesta = await request.post(`ajax/${archivo}`, { form: datos });
  const texto = await respuesta.text();
  let json = null;
  try { json = JSON.parse(texto); } catch { /* respuesta que no es JSON */ }
  return { status: respuesta.status(), json, texto };
}

function mensajeDe(r) {
  return String(r.json?.mensaje ?? r.json?.message ?? r.json?.error ?? r.texto);
}

// Hay dos estilos de negar: código 401/403 (ExigirSesionAjax y permisos por acción)
// o 200 con success:false y un mensaje de permiso (revisión dentro del controlador).
function fueNegado(r) {
  if (r.status === 401 || r.status === 403) return true;
  const fallo = r.json?.success === false || r.json?.status === 'error';
  return fallo && /permiso|administrador|sesión no válida/i.test(mensajeDe(r));
}

const describir = (r) => `HTTP ${r.status}: ${mensajeDe(r).slice(0, 120)}`;

// ---------------------------------------------------------------------------
test.describe('Ajax sin sesión', () => {
  // Un archivo de cada grupo: todos deben rechazar a quien no inició sesión.
  for (const [archivo, datos] of [
    ['usuarios.ajax.php', {}],                          // A: ExigirSesionAjax
    ['calificacion.ajax.php', { accion: 'obtenerProgramasEstudiante' }], // B: por acción
    ['noticiapanel.ajax.php', { accion: 'listar' }],    // C: sesión + controlador
    ['solicitudinscripcion.ajax.php', { accion: 'listar' }],
    ['restablecerpassword.ajax.php', { accion: 'estudiante', id: ID_INEXISTENTE }],
  ]) {
    test(`${archivo} rechaza sin sesión`, async ({ request }) => {
      const r = await llamar(request, archivo, datos);
      expect(fueNegado(r), describir(r)).toBe(true);
    });
  }

  test('obtener-voucher-imagen.php rechaza sin sesión', async ({ request }) => {
    const r = await request.get(`ajax/obtener-voucher-imagen.php?id=${ID_INEXISTENTE}`);
    expect(r.status()).toBe(403);
  });
});

// ---------------------------------------------------------------------------
// Grupo A: ExigirSesionAjax([roles]) al principio del archivo.
const PERSONAL = ['adm', 'sec', 'aux'];
const ARCHIVOS_POR_ROL = {
  'usuarios.ajax.php': ['adm'],
  'autoridad.ajax.php': ['adm'],
  'certificado.ajax.php': ['adm', 'sec'],
  'reportes.ajax.php': ['adm', 'sec'],
  'asignacion.ajax.php': PERSONAL,
  'cuenta.ajax.php': PERSONAL,
  'docentes.ajax.php': PERSONAL,
  'especialidad.ajax.php': PERSONAL,
  'estudiantes.ajax.php': PERSONAL,
  'historialpagos.ajax.php': PERSONAL,
  'inscripcion.ajax.php': PERSONAL,
  'inscripcionmodulo.ajax.php': PERSONAL,
  'material-categoria.ajax.php': PERSONAL,
  'modulo.ajax.php': PERSONAL,
  'pagoclinica.ajax.php': PERSONAL,
  'programacuenta.ajax.php': PERSONAL,
  'reportemodulos.ajax.php': PERSONAL,
  'reportenotas.ajax.php': PERSONAL,
  'voucher.ajax.php': ['est'],
  'programa.ajax.php': ['adm', 'sec', 'aux', 'doc', 'est'],
};

test.describe('Ajax grupo A: permiso por archivo', () => {
  for (const rol of ['adm', 'sec', 'aux', 'doc', 'est']) {
    test(`${rol.toUpperCase()} solo entra a los archivos de su rol`, async ({ page }) => {
      await ingresar(page, rol);

      for (const [archivo, roles] of Object.entries(ARCHIVOS_POR_ROL)) {
        // Acción inventada: pasa (o no) la puerta de entrada, pero no ejecuta nada.
        const r = await llamar(page.request, archivo, { accion: '__prueba_permisos__' });
        const debePasar = roles.includes(rol);
        expect.soft(fueNegado(r), `${archivo} → ${debePasar ? 'debía pasar' : 'debía negar'} (${describir(r)})`)
          .toBe(!debePasar);
      }
    });
  }
});

// ---------------------------------------------------------------------------
// Grupos B y C: el permiso depende de la acción (en el ajax o en el controlador).
// [rol, archivo, datos, 'permitido' | 'negado']
const CASOS_POR_ACCION = [
  // B: calificacion — el EST solo consulta lo suyo
  ['est', 'calificacion.ajax.php', { accion: 'obtenerProgramasEstudiante' }, 'permitido'],
  ['est', 'calificacion.ajax.php', { accion: 'guardarCalificaciones' }, 'negado'],
  ['est', 'calificacion.ajax.php', { accion: 'validarCerrarModulo' }, 'negado'],

  // B: pagomodulo — el EST consulta sus pagos; el DOC no entra
  ['est', 'pagomodulo.ajax.php', { accion: 'obtenerProgramasEstudiante' }, 'permitido'],
  ['est', 'pagomodulo.ajax.php', { accion: 'registrarPagoModulo' }, 'negado'],
  ['est', 'pagomodulo.ajax.php', { accion: 'anularCuota', id: ID_INEXISTENTE }, 'negado'],
  ['doc', 'pagomodulo.ajax.php', { accion: 'obtenerProgramasEstudiante' }, 'negado'],

  // B: ordenpago — el EST solo ve sus órdenes
  ['est', 'ordenpago.ajax.php', { accion: 'misOrdenesPago' }, 'permitido'],
  ['est', 'ordenpago.ajax.php', { accion: 'registrarOrdenPago' }, 'negado'],
  ['est', 'ordenpago.ajax.php', { accion: 'validarVoucherMatricula' }, 'negado'],
  ['doc', 'ordenpago.ajax.php', { accion: 'misOrdenesPago' }, 'negado'],

  // B: requisito — el EST los suyos; el catálogo solo el ADM
  ['est', 'requisito.ajax.php', { accion: 'misRequisitos' }, 'permitido'],
  ['est', 'requisito.ajax.php', { accion: 'guardar' }, 'negado'],
  ['doc', 'requisito.ajax.php', { accion: 'misRequisitos' }, 'negado'],
  ['adm', 'requisito.ajax.php', { accion: 'listarCatalogo' }, 'permitido'],
  ['sec', 'requisito.ajax.php', { accion: 'guardarCatalogo' }, 'negado'],
  ['aux', 'requisito.ajax.php', { accion: 'listarCatalogo' }, 'negado'],

  // B: restablecerpassword — estudiantes/docentes el personal; el personal solo el ADM
  ['est', 'restablecerpassword.ajax.php', { accion: 'estudiante', id: ID_INEXISTENTE }, 'negado'],
  ['doc', 'restablecerpassword.ajax.php', { accion: 'docente', id: ID_INEXISTENTE }, 'negado'],
  ['sec', 'restablecerpassword.ajax.php', { accion: 'personal', id: ID_INEXISTENTE }, 'negado'],
  ['aux', 'restablecerpassword.ajax.php', { accion: 'personal', id: ID_INEXISTENTE }, 'negado'],

  // C: listar es para todos; escribir lo revisa puedeEscribir() del controlador
  ['est', 'noticiapanel.ajax.php', { accion: 'listar' }, 'permitido'],
  ['est', 'noticiapanel.ajax.php', { accion: 'registrar' }, 'negado'],
  ['est', 'noticiapanel.ajax.php', { accion: 'actualizar', id: ID_INEXISTENTE }, 'negado'],
  ['aux', 'noticiapanel.ajax.php', { accion: 'anular', id: ID_INEXISTENTE }, 'negado'],

  ['doc', 'carruselpanel.ajax.php', { accion: 'registrar' }, 'negado'],
  ['aux', 'carruselpanel.ajax.php', { accion: 'anular', id: ID_INEXISTENTE }, 'negado'],

  ['est', 'cronogramadefensa.ajax.php', { accion: 'listar' }, 'permitido'],
  ['est', 'cronogramadefensa.ajax.php', { accion: 'registrar' }, 'negado'],
  ['doc', 'cronogramadefensa.ajax.php', { accion: 'anular', id: ID_INEXISTENTE }, 'negado'],

  ['doc', 'calendarioevento.ajax.php', { accion: 'listarEventos' }, 'permitido'],
  ['est', 'calendarioevento.ajax.php', { accion: 'registrarEvento' }, 'negado'],
  ['est', 'calendarioevento.ajax.php', { accion: 'anularEvento', id: ID_INEXISTENTE }, 'negado'],

  ['est', 'plazocuotaprograma.ajax.php', { accion: 'registrarPlazo' }, 'negado'],
  ['doc', 'plazocuotaprograma.ajax.php', { accion: 'anularPlazo', id: ID_INEXISTENTE }, 'negado'],

  ['est', 'solicitudinscripcion.ajax.php', { accion: 'listar' }, 'negado'],
  ['doc', 'solicitudinscripcion.ajax.php', { accion: 'actualizarEstado', idSolicitud: ID_INEXISTENTE }, 'negado'],
];

test.describe('Ajax grupos B y C: permiso por acción', () => {
  for (const [rol, archivo, datos, esperado] of CASOS_POR_ACCION) {
    test(`${rol.toUpperCase()} → ${archivo} ${datos.accion}: ${esperado}`, async ({ page }) => {
      await ingresar(page, rol);
      const r = await llamar(page.request, archivo, datos);

      expect(fueNegado(r), describir(r)).toBe(esperado === 'negado');
    });
  }
});

// ---------------------------------------------------------------------------
// Grupo D: el dato tiene que ser del usuario.
test.describe('Ajax grupo D: voucher ajeno', () => {
  test('EST no puede ver un voucher que no es suyo', async ({ page }) => {
    await ingresar(page, 'est');
    const r = await page.request.get(`ajax/obtener-voucher-imagen.php?id=${ID_INEXISTENTE}`);
    expect(r.status()).toBe(403);
  });

  test('el personal pasa el permiso (el voucher no existe: 404)', async ({ page }) => {
    await ingresar(page, 'sec');
    const r = await page.request.get(`ajax/obtener-voucher-imagen.php?id=${ID_INEXISTENTE}`);
    expect(r.status()).toBe(404);
  });
});
