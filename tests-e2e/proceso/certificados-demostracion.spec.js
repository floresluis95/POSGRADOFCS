// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// "Documento completo (demostración)" del Historial Académico y del Certificado de
// Aprobación: el certificado entero en hoja oficio, sin voucher ni monto, y sin dejar
// ningún registro (ni la emisión ni el trabajo de grado).

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

/** Elige al estudiante por C.I. en la pantalla del certificado. */
async function elegir(page, ruta, estudianteID, visible) {
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await page.goto(ruta);
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator(visible)).toBeVisible({ timeout: 15_000 });
}

/**
 * Pulsa la demostración, que abre el PDF en una pestaña nueva. Devuelve el tipo de
 * contenido y el tamaño de la primera hoja (MediaBox, en puntos).
 */
async function abrirDemostracion(page, archivoPdf) {
  // El visor de PDF del navegador no deja leer la respuesta: se captura al pasar
  let capturado;
  await page.context().route(`**/${archivoPdf}`, async (route) => {
    const respuesta = await route.fetch();
    capturado = { tipo: respuesta.headers()['content-type'], cuerpo: await respuesta.body(), envio: route.request().postData() };
    await route.fulfill({ response: respuesta });
  });
  const [ventana] = await Promise.all([page.waitForEvent('popup'), page.locator('#btn-documento-completo').click()]);
  await expect.poll(() => !!capturado, { timeout: 15_000 }).toBe(true);
  await ventana.close();
  await page.context().unroute(`**/${archivoPdf}`);
  expect(capturado.envio).toContain('completo=1');
  expect(await page.locator('#form-generar-certificado input[name="completo"]').count()).toBe(0);
  const caja = capturado.cuerpo.toString('latin1').match(/\/MediaBox \[([\d.\s]+)\]/);
  return {
    tipo: capturado.tipo,
    hoja: caja ? caja[1].trim().split(/\s+/).map((n) => Math.round(Number(n))).slice(2) : [],
  };
}

test('historial académico: demostración en oficio horizontal, sin registro', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro)
       SELECT ${estudianteID}, ${PROGRAMA_ID}, Idmodulo, 88, 'APROBADO', CURDATE()
       FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} ORDER BY Idmodulo LIMIT 8`);
  await elegir(page, 'certificadohistorialacademico', estudianteID, '#card-modulos');
  await expect(page.locator('#btn-documento-completo')).toBeEnabled();

  const pdf = await abrirDemostracion(page, 'certificado-historial-academico-pdf.php');
  expect(pdf.tipo).toContain('application/pdf');
  expect(pdf.hoja).toEqual([936, 612]); // oficio horizontal: 13 x 8,5 pulgadas
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('0');
});

test('certificado de aprobación: demostración en oficio, sin recibo y sin guardar nada', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  await elegir(page, 'certificadoaprobacionmonografia', estudianteID, '#card-monografia');
  await page.locator('#input-titulo').fill('evaluación de prueba de la demostración');
  await page.locator('#input-calificacion').fill('85');
  await page.locator('#contenedor-tribunal .input-nombre-tribunal').first().fill('M.Sc. Dr. Presidente de Prueba');

  const pdf = await abrirDemostracion(page, 'certificado-aprobacion-monografia-pdf.php');
  expect(pdf.tipo).toContain('application/pdf');
  expect(pdf.hoja).toEqual([612, 936]); // oficio vertical: 8,5 x 13 pulgadas
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('0');
  expect(valor(`SELECT COUNT(*) FROM trabajogrado WHERE EstudianteID = ${estudianteID}`)).toBe('0');

  // "Revisar y generar" sigue pidiendo el recibo
  await page.locator('#btn-generar-certificado').click();
  await expect(page.locator('.swal2-popup')).toContainText('Falta el N° de recibo');
});
