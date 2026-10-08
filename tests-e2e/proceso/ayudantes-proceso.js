// @ts-check
import { expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

// Ayudantes de las pruebas que crean datos. Todo va contra la copia "proyecto_pruebas"
// (servidor de pruebas en el puerto 8099), nunca contra la base real.

const MYSQL = 'C:/xampp/mysql/bin/mysql.exe';

/** Consulta directa a proyecto_pruebas. Devuelve las filas como arreglos de texto. */
export function sql(consulta) {
  const salida = execFileSync(MYSQL, ['-u', 'root', '-N', '-B', 'proyecto_pruebas', '-e', consulta]).toString().trim();
  return salida === '' ? [] : salida.split(/\r?\n/).map((fila) => fila.split('\t'));
}

/** Primer valor de la primera fila (p. ej. un COUNT). */
export function valor(consulta) {
  const filas = sql(consulta);
  return filas.length ? filas[0][0] : null;
}

let contador = 0;
/** Número distinto en cada llamada (para C.I., vouchers, nombres de prueba). */
export function unico() {
  contador += 1;
  return String(Date.now()).slice(-6) + String(contador).padStart(2, '0');
}

/** Inicia sesión con un usuario de prueba (prueba_adm, prueba_sec...). */
export async function ingresar(page, rol = 'adm') {
  await page.goto('ingreso');
  await page.getByLabel('Usuario').fill(`prueba_${rol}`);
  await page.getByLabel('Contraseña', { exact: true }).fill('Prueba123');
  await page.locator('#btnLogin').click();
  await expect(page).toHaveURL(/panel$/);
}

/** POST a un archivo ajax con la sesión del navegador; devuelve el JSON. */
export async function ajax(page, archivo, campos, archivos = {}) {
  const multipart = { ...campos };
  for (const [nombre, contenido] of Object.entries(archivos)) {
    multipart[nombre] = contenido;
  }
  const respuesta = await page.request.post(`ajax/${archivo}`, { multipart });
  const texto = await respuesta.text();
  try {
    return JSON.parse(texto);
  } catch {
    throw new Error(`ajax/${archivo} no devolvió JSON (${respuesta.status()}): ${texto.slice(0, 300)}`);
  }
}

/**
 * POST de un formulario clásico a una página (los que responden con swal y recargan). Devuelve el HTML.
 * Un campo con valor undefined no se envía (como una casilla sin marcar).
 */
export async function enviarFormulario(page, pagina, campos) {
  const form = Object.fromEntries(Object.entries(campos).filter(([, v]) => v !== undefined));
  const respuesta = await page.request.post(pagina, { form });
  return respuesta.text();
}

/** Imagen PNG mínima para los vouchers. */
export const IMAGEN_VOUCHER = {
  name: 'voucher.png',
  mimeType: 'image/png',
  buffer: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', 'base64'),
};

/** Programa de la base de pruebas que tiene cuentas MATRICULA, CUOTAS y CLINICA. */
export const PROGRAMA_ID = 1;

/** Registra un estudiante nuevo por el registro rápido (el mismo del Pre-Registro). */
export async function registrarEstudiante(page, cambios = {}) {
  const ci = cambios.Ci ?? unico();
  const datos = {
    accion: 'registrarEstudianteRapido',
    Ci: ci,
    Complemento: '',
    Exp: 'OR',
    Nombre: 'PRUEBA',
    Apaterno: 'PROCESO',
    Amaterno: 'E2E',
    FechaNacimiento: '1995-05-10',
    Edad: '31',
    Lugarn: 'ORURO',
    Correo: `prueba${ci}@correo-prueba.com`,
    IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion'),
    Celular: '70000000',
    ...cambios,
  };
  return { ci, respuesta: await ajax(page, 'ordenpago.ajax.php', datos) };
}

/**
 * Pre-registro (orden de pago de matrícula) tal como lo envía el formulario de la
 * página Pre-Registro. Devuelve el HTML de respuesta.
 */
export async function preregistrar(page, estudianteID, cambios = {}) {
  const matricula = Number(valor(`SELECT CostoMatricula FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  return enviarFormulario(page, 'preregistro', {
    registrarOrdenPago: '1',
    paginaRedirect: 'preregistro',
    idcliente: String(estudianteID),
    programa: String(PROGRAMA_ID),
    ProgramaCuentaID: valor(`SELECT ProgramaCuentaID FROM programacuenta WHERE ProgramaID = ${PROGRAMA_ID} AND TipoCuenta = 'MATRICULA' AND Estado = 1 LIMIT 1`),
    montoAPagar: matricula.toFixed(2),
    fechaInscripcion: new Date().toISOString().slice(0, 10),
    pagoCompleto: '0',
    tipoPagoCodigo: 'REGULAR',
    tipoPago: 'PLAN REGULAR',
    numeroCuotas: '10',
    porcentajeDescuento: '0',
    pagarMatricula: '1',
    ...cambios,
  });
}

/** Orden de matrícula PENDIENTE más reciente del estudiante. */
export function ordenPendiente(estudianteID) {
  return valor(`SELECT IdOrdenPago FROM ordenpago WHERE EstudianteID = ${estudianteID} AND Estado = 'PENDIENTE' ORDER BY IdOrdenPago DESC LIMIT 1`);
}

/** Valida el voucher de matrícula (pantalla Inscripción) => el estudiante queda inscrito. */
export async function validarVoucherMatricula(page, idOrdenPago, numeroVoucher) {
  return ajax(page, 'ordenpago.ajax.php', {
    accion: 'validarVoucherMatricula',
    idOrdenPago: String(idOrdenPago),
    numeroVoucher,
    fechaInscripcion: new Date().toISOString().slice(0, 10),
  }, { fotoVoucher: IMAGEN_VOUCHER });
}

/** Orden de pago de cuota (Matriculados > Gestión de Pagos > Orden de pago). */
export async function generarOrdenCuota(page, { estudianteID, idInscripcion }, montoTotal = '3000') {
  return ajax(page, 'ordenpago.ajax.php', {
    accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(estudianteID), idinscripcion: String(idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: String(montoTotal), fechaVencimiento: '2027-12-31',
  });
}

/** Registra el voucher de la cuota pendiente (Gestión de Pagos > Cuotas del programa). */
export async function pagarCuota(page, idInscripcion, montoCuota, numeroVaucher) {
  return ajax(page, 'pagomodulo.ajax.php', {
    accion: 'registrarPagoModulo', idinscripcion: String(idInscripcion), programaID: String(PROGRAMA_ID),
    fechaPago: new Date().toISOString().slice(0, 10), montoCuota: String(montoCuota), numeroVaucher,
  }, { fmodulo: IMAGEN_VOUCHER });
}

/** Estudiante nuevo ya inscrito en el programa de prueba (Plan Regular). Devuelve sus ids. */
export async function estudianteInscrito(page) {
  const { respuesta } = await registrarEstudiante(page);
  expect(respuesta.success, respuesta.mensaje).toBe(true);
  const estudianteID = respuesta.estudianteID;
  await preregistrar(page, estudianteID);
  const idOrden = ordenPendiente(estudianteID);
  expect(idOrden, 'se debió crear la orden de matrícula').not.toBeNull();
  const validacion = await validarVoucherMatricula(page, idOrden, 'MAT-' + unico());
  expect(validacion.success, validacion.mensaje).toBe(true);
  const idInscripcion = valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${PROGRAMA_ID}`);
  return { estudianteID, idInscripcion: Number(idInscripcion) };
}
