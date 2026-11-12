// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Trámite A/B: formulario pre-impreso de la UTO. Se busca al solicitante, se llenan los
// datos, la vista previa (con el formulario de fondo) no registra nada y "Generar e
// imprimir" lo guarda en documentoemitido con su voucher (que no se imprime).

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('pantalla: busca al solicitante, prellena sus datos, vista previa y registro', async ({ page }) => {
  test.setTimeout(60_000);
  const { estudianteID } = await estudianteInscrito(page);
  const [ci, nombre] = String(valor(`SELECT CONCAT_WS('|', Ci, Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`)).split('|');
  const emitidos = () => valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID} AND TipoDocumento = 'TRAMITE'`);

  await page.goto('certificadotramite');
  await expect(page.locator('body')).not.toContainText(/Fatal error|Warning:|Notice:/);
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#card-tramite')).toBeVisible({ timeout: 15_000 });
  await expect(page.locator('#input-nombre')).toHaveValue(new RegExp('^' + nombre.toUpperCase()));
  await expect(page.locator('#input-ci')).toHaveValue(new RegExp('^' + ci));
  await expect(page.locator('#input-dirigido')).not.toHaveValue('');
  await expect(page.locator('#input-cargo')).not.toHaveValue('');

  await page.locator('#input-descripcion').fill('Solicito la emisión de mi certificado de calificaciones para fines de titulación.');
  await page.locator('input[name="formato-tramite"][value="2"]').check();
  const voucher = 'TR' + unico();
  await page.locator('#input-vaucher_largo').fill(voucher);
  await page.locator('#input-monto').fill('30');

  const previa = page.waitForResponse((r) => r.url().includes('certificado-tramite-pdf.php'));
  await page.locator('#btn-generar-certificado').click();
  const modal = page.locator('#modalConfirmarCertificado');
  await expect(modal).toBeVisible();
  await expect(modal.locator('#certModalDatos')).toContainText('Solicito la emisión');
  expect((await previa).headers()['content-type']).toContain('application/pdf');
  expect(emitidos()).toBe('0');

  const [ventana] = await Promise.all([page.waitForEvent('popup'), modal.locator('#certModalGenerar').click()]);
  await expect.poll(emitidos, { timeout: 15_000 }).toBe('1');
  expect(valor(`SELECT CONCAT_WS('|', NumeroVaucher, Monto) FROM documentoemitido WHERE EstudianteID = ${estudianteID} AND TipoDocumento = 'TRAMITE'`))
    .toBe(`${voucher}|30.00`);
  const datos = JSON.parse(String(valor(`SELECT Datos FROM documentoemitido WHERE EstudianteID = ${estudianteID} AND TipoDocumento = 'TRAMITE'`)));
  expect(datos.descripcion).toContain('Solicito la emisión');
  expect(datos.formato).toBe('2'); // se reimprime en el mismo formato
  await ventana.close();
});

test('el PDF exige voucher (no en la vista previa) y avisa si el texto no entra en las líneas', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const base = {
    estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), fecha: '2026-11-12',
    dirigido: 'MSc. Ing. Director de Prueba', cargo: 'DIRECTOR DE POSGRADO', nombre: 'PRUEBA TRÁMITE',
    ci: '1234567 OR', telefono: '71234567', descripcion: 'Solicitud de prueba.', monto: '30',
  };
  const contar = () => valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID} AND TipoDocumento = 'TRAMITE'`);
  const pdf = 'tcpdf/pdf/certificado-tramite-pdf.php';

  const previa = await page.request.post(pdf, { form: { ...base, vaucher: '', vistaPrevia: '1' } });
  expect(previa.headers()['content-type']).toContain('application/pdf');

  const sinVoucher = await page.request.post(pdf, { form: { ...base, vaucher: '' } });
  expect(await sinVoucher.text()).toContain('Falta el N° de voucher');

  const largo = await page.request.post(pdf, { form: { ...base, vaucher: 'TR' + unico(), descripcion: 'palabra '.repeat(400) } });
  expect(await largo.text()).toContain('no entra en las 4 líneas');
  expect(contar()).toBe('0');

  const voucher = 'TR' + unico();
  const bueno = await page.request.post(pdf, { form: { ...base, vaucher: voucher } });
  expect(bueno.headers()['content-type']).toContain('application/pdf');
  expect(contar()).toBe('1');

  // Hoja de prueba: sin voucher y sin registrar nada
  const hoja = await page.request.post(pdf, { form: { prueba: '1', ajusteX: '2.5', ajusteY: '-1' } });
  expect(hoja.headers()['content-type']).toContain('application/pdf');
  expect(contar()).toBe('1');

  // Ajuste de impresión: solo valores de -25 a 25 mm; se guarda en configuracion
  const { ajax } = await import('./ayudantes-proceso.js');
  const malo = await ajax(page, 'certificado.ajax.php', { accion: 'guardarAjusteTramite', ajusteX: '40', ajusteY: '0' });
  expect(malo.success).toBe(false);
  const ok = await ajax(page, 'certificado.ajax.php', { accion: 'guardarAjusteTramite', ajusteX: '1,5', ajusteY: '-2' });
  expect(ok.success).toBe(true);
  expect(valor(`SELECT GROUP_CONCAT(Valor ORDER BY Clave) FROM configuracion WHERE Clave IN ('TRAMITE_AJUSTE_X', 'TRAMITE_AJUSTE_Y')`)).toBe('1.5,-2.0');
  // El formato 2 (negro) tiene su propio ajuste y no toca el del formato 1
  const ok2 = await ajax(page, 'certificado.ajax.php', { accion: 'guardarAjusteTramite', formato: '2', ajusteX: '3', ajusteY: '0.5' });
  expect(ok2.success).toBe(true);
  expect(valor(`SELECT GROUP_CONCAT(Valor ORDER BY Clave) FROM configuracion WHERE Clave IN ('TRAMITE2_AJUSTE_X', 'TRAMITE2_AJUSTE_Y')`)).toBe('3.0,0.5');
  expect(valor(`SELECT Valor FROM configuracion WHERE Clave = 'TRAMITE_AJUSTE_X'`)).toBe('1.5');
  await ajax(page, 'certificado.ajax.php', { accion: 'guardarAjusteTramite', ajusteX: '0', ajusteY: '0' });
  await ajax(page, 'certificado.ajax.php', { accion: 'guardarAjusteTramite', formato: '2', ajusteX: '0', ajusteY: '0' });

  // Vista previa y hoja de prueba del formato 2 (se dibuja el formulario: no usa escaneo)
  const previa2 = await page.request.post(pdf, { form: { ...base, vaucher: '', vistaPrevia: '1', formato: '2' } });
  expect(previa2.headers()['content-type']).toContain('application/pdf');
  const hoja2 = await page.request.post(pdf, { form: { prueba: '1', formato: '2' } });
  expect(hoja2.headers()['content-type']).toContain('application/pdf');

  // El voucher es único en todo el sistema
  const repetido = await page.request.post(pdf, { form: { ...base, vaucher: voucher } });
  expect(await repetido.text()).toContain('ya fue registrado');
  expect(contar()).toBe('1');
});
