// @ts-check
import { test, expect } from '@playwright/test';
import { valor, sql, unico, ingresar, ajax, estudianteInscrito, IMAGEN_VOUCHER } from './ayudantes-proceso.js';

// El estudiante genera la orden de su siguiente cuota (Mis órdenes de pago). La orden
// queda marcada como suya y al imprimirla lleva la firma del coordinador y el sello
// (Configuración); las del personal no.

/** Usuario EST (contraseña Prueba123) para un estudiante recién inscrito. */
function crearUsuarioEstudiante(estudianteID) {
  const usuario = 'cuota_' + unico().slice(-8);
  sql(`INSERT INTO usuario (EstudianteID, Usuario, Password, Estado, Tipo)
       SELECT ${estudianteID}, '${usuario}', Password, '1', 'EST' FROM usuario WHERE Usuario = 'prueba_est'`);
  return usuario;
}

async function ingresarComo(page, usuario) {
  await page.goto('ingreso');
  await page.getByLabel('Usuario').fill(usuario);
  await page.getByLabel('Contraseña', { exact: true }).fill('Prueba123');
  await page.locator('#btnLogin').click();
  await expect(page).toHaveURL(/panel$/);
}

/** Cuántas imágenes distintas trae el PDF (logos, firma, sello). */
async function imagenesDelPdf(page, idOrden) {
  const pdf = (await (await page.request.get(`vistas/componentes/orden-pago-pdf.php?idordenpago=${idOrden}`)).body()).toString('latin1');
  return (pdf.match(/\/Subtype \/Image/g) || []).length;
}

test('el estudiante genera su cuota y la orden sale con firma y sello', async ({ page, browser }) => {
  test.setTimeout(90_000);
  await ingresar(page, 'adm');

  // Configuración: firma y sello (imágenes de prueba)
  await page.goto('autoridades');
  await page.locator('.input-imagen-firma[data-clave="FIRMA"]').setInputFiles(IMAGEN_VOUCHER);
  await expect(page.locator('#vista_FIRMA img')).toBeVisible({ timeout: 15_000 });
  await page.locator('.input-imagen-firma[data-clave="SELLO"]').setInputFiles(IMAGEN_VOUCHER);
  await expect(page.locator('#vista_SELLO img')).toBeVisible({ timeout: 15_000 });

  const inscrito = await estudianteInscrito(page); // Plan Regular de 10 cuotas
  const usuario = crearUsuarioEstudiante(inscrito.estudianteID);
  const cuotaPlan = Number(valor(`SELECT MontoCuota FROM pagoprograma WHERE idInscripcion = ${inscrito.idInscripcion}`));

  const est = await browser.newPage();
  await ingresarComo(est, usuario);
  await est.goto('misordenespago');
  const recuadro = est.locator('#lista_generar_cuota .mop-pp');
  await expect(recuadro).toContainText('Cuota 1 de 10', { timeout: 15_000 });
  await recuadro.locator('.btn-generar-cuota').click();
  await expect(est.locator('.swal2-popup')).toContainText('Tesoro Universitario');
  await est.locator('.swal2-confirm').click();
  await expect(est.locator('.swal2-popup')).toContainText('Orden generada', { timeout: 15_000 });
  await est.locator('.swal2-cancel').click();

  const [idOrden, monto, generada, firma] = String(valor(
    `SELECT CONCAT_WS('|', IdOrdenPago, MontoTotal, GeneradaPorEstudiante, Firma) FROM ordenpago
     WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`,
  )).split('|');
  expect(Number(monto)).toBe(cuotaPlan);
  expect(generada).toBe('1');
  const coordinador = valor("SELECT UPPER(Nombre) FROM autoridad WHERE Cargo = 'COORDINADOR'");
  if (coordinador) expect(firma).toBe(coordinador);

  // Ya tiene una por pagar: no se ofrece otra y el servidor la rechaza
  await expect(est.locator('#lista_por_pagar .mop-pp')).toHaveCount(1, { timeout: 15_000 });
  await expect(est.locator('#generar_cuota')).toBeHidden();
  const otra = await ajax(est, 'ordenpago.ajax.php', { accion: 'generarMiCuota', idinscripcion: String(inscrito.idInscripcion) });
  expect(otra.success).toBe(false);

  // La orden del estudiante trae la firma y el sello; la misma orden marcada como del personal, no
  const conFirma = await imagenesDelPdf(est, idOrden);
  sql(`UPDATE ordenpago SET GeneradaPorEstudiante = 0 WHERE IdOrdenPago = ${idOrden}`);
  const sinFirma = await imagenesDelPdf(est, idOrden);
  // Firma y sello en el original y en la copia (cada PNG transparente lleva además su máscara)
  expect(conFirma - sinFirma).toBe(8);
  await est.close();
});

test('un estudiante no puede generar cuotas de otra inscripción', async ({ page }) => {
  await ingresar(page, 'adm');
  const ajeno = await estudianteInscrito(page);
  await ingresar(page, 'est');
  const r = await ajax(page, 'ordenpago.ajax.php', { accion: 'calcularMiCuota', idinscripcion: String(ajeno.idInscripcion) });
  expect(r.success).toBe(false);
  // Y el personal no usa esta acción (solo la ve el estudiante en su pantalla)
  const g = await ajax(page, 'ordenpago.ajax.php', { accion: 'generarMiCuota', idinscripcion: String(ajeno.idInscripcion) });
  expect(g.success).toBe(false);
  expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${ajeno.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`)).toBe('0');
});
