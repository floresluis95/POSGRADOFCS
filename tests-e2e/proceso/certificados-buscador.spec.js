// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Certificados: buscador por C.I. o nombre con panel de resultados, voucher con letras,
// grado con tilde y confirmación con vista previa (que no registra nada).

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('busca por C.I. o por nombre y muestra los resultados en un panel', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  await estudianteInscrito(page); // otro con el mismo nombre: la búsqueda por nombre da varios
  const [ci, apaterno, nombre] = String(valor(`SELECT CONCAT_WS('|', Ci, Apaterno, Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`)).split('|');

  // Por nombre (en cualquier orden) en el ajax
  const porNombre = await ajax(page, 'certificado.ajax.php', { accion: 'buscarEstudiantes', termino: `${nombre} ${apaterno}` });
  expect(porNombre.success).toBe(true);
  expect(porNombre.resultados.some((r) => Number(r.EstudianteID) === Number(estudianteID))).toBe(true);
  const corto = await ajax(page, 'certificado.ajax.php', { accion: 'buscarEstudiantes', termino: 'ab' });
  expect(corto.success).toBe(false);

  // En pantalla: por nombre salen varias tarjetas (los estudiantes de prueba se llaman igual)
  await page.goto('certificadorevision');
  await page.locator('#cert-termino').fill(`${apaterno} ${nombre}`);
  await expect(page.locator('#cert-resultados .cert-resultado').first()).toBeVisible({ timeout: 15_000 });

  // Por C.I.: un solo resultado, queda elegido y se abre el paso 2
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#cert-elegido')).toContainText(ci, { timeout: 15_000 });
  await expect(page.locator('#card-revision')).toBeVisible();
  await expect(page.locator('#select-inscripcion')).toHaveValue(String(estudianteID));
  await expect(page.locator('#dato-curso')).not.toContainText('MAESTRIA ');
});

test('la vista previa no registra el certificado y "Generar e imprimir" sí', async ({ page }) => {
  test.setTimeout(60_000);
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const emitidos = () => valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`);

  await page.goto('certificadomediomagnetico');
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#card-carta')).toBeVisible({ timeout: 15_000 });
  await page.locator('#input-titulo').fill('evaluación de prueba del sistema');
  // Voucher con letras: se pasa a MAYÚSCULAS
  await page.locator('#input-vaucher_largo').fill('rc' + unico());
  await expect(page.locator('#input-vaucher_largo')).toHaveValue(/^RC\d+$/);
  await page.locator('#input-monto').fill('50');

  // La vista previa es el mismo PDF (con vistaPrevia=1) cargado en la ventana
  const previa = page.waitForResponse((r) => r.url().includes('certificado-medio-magnetico-pdf.php'));
  await page.locator('#btn-generar-certificado').click();
  const modal = page.locator('#modalConfirmarCertificado');
  await expect(modal).toBeVisible();
  await expect(modal.locator('#certModalDatos')).toContainText('EVALUACIÓN DE PRUEBA DEL SISTEMA');
  await expect(modal.locator('#certModalDatos')).toContainText('Bs. 50,00');
  // La vista previa llega como PDF y no deja registro
  expect((await previa).headers()['content-type']).toContain('application/pdf');
  await expect(page.locator('#certPreviaCargando')).toBeHidden({ timeout: 10_000 });
  expect(emitidos()).toBe('0');

  const [ventana] = await Promise.all([page.waitForEvent('popup'), modal.locator('#certModalGenerar').click()]);
  await expect.poll(emitidos, { timeout: 15_000 }).toBe('1');
  expect(valor(`SELECT NumeroVaucher FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toMatch(/^RC\d+$/);
  await ventana.close();
});

test('el PDF acepta el voucher con letras y rechaza otros caracteres; la vista previa no se guarda', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const base = {
    estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'la señora', nombre: 'PRUEBA DE REVISIÓN',
    curso: 'ESPECIALIDAD EN PRUEBA', fecha: '2026-10-16', monto: '10',
  };
  const contar = () => valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`);

  const previa = await page.request.post('tcpdf/pdf/certificado-revision-pdf.php', { form: { ...base, vaucher: 'ab12-3', vistaPrevia: '1' } });
  expect(previa.headers()['content-type']).toContain('application/pdf');
  expect(contar()).toBe('0');

  const malo = await page.request.post('tcpdf/pdf/certificado-revision-pdf.php', { form: { ...base, vaucher: 'AB 12/3' } });
  expect(await malo.text()).toContain('solo puede tener letras y números');
  expect(contar()).toBe('0');

  const bueno = await page.request.post('tcpdf/pdf/certificado-revision-pdf.php', { form: { ...base, vaucher: 'ab12-3' } });
  expect(bueno.headers()['content-type']).toContain('application/pdf');
  expect(valor(`SELECT NumeroVaucher FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('AB12-3');
});
