// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Certificado de Aprobación: el tribunal se elige de un select con los docentes y las
// personas escritas a mano en tribunales anteriores.

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('tribunal: select con docentes y personas de tribunales anteriores', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const manual = `M.Sc. Ing. Persona Externa ${unico()}`;
  sql(`INSERT INTO trabajogrado (EstudianteID, ProgramaID, TituloTrabajo, Tribunal)
       VALUES (${estudianteID}, ${PROGRAMA_ID}, 'TRABAJO DE PRUEBA', 'Presidente: ${manual}')`);
  const docentes = Number(valor('SELECT COUNT(*) FROM docente WHERE Estado = 1'));

  await page.goto('certificadoaprobacionmonografia');
  await page.locator('#cert-termino').fill(String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`)));
  await expect(page.locator('#card-monografia')).toBeVisible({ timeout: 15_000 });
  // Cada fila tiene un select con los docentes y los de tribunales anteriores
  const fila = page.locator('#contenedor-tribunal .fila-tribunal').first();
  const select = fila.locator('.select-docente-tribunal');
  const nombre = fila.locator('.input-nombre-tribunal');
  await expect(select.locator(`optgroup[label="Tribunales anteriores"] option[value="${manual}"]`)).toHaveCount(1);
  const deDocentes = select.locator('optgroup[label="Docentes"] option');
  expect(await deDocentes.count()).toBeGreaterThan(0);
  expect(await deDocentes.count()).toBeLessThanOrEqual(docentes);

  // Con el buscador: se escribe parte del nombre y se elige; su nombre pasa a la casilla
  const docente = String(await deDocentes.first().getAttribute('value'));
  await fila.locator('.select2-selection').click();
  await page.locator('.select2-search__field').fill(docente.split(' ').slice(-2).join(' '));
  await expect(page.locator('.select2-results__option--highlighted')).toHaveText(docente);
  await page.keyboard.press('Enter');
  await expect(nombre).toHaveValue(docente);
  await expect(fila.locator('.select2-selection__rendered')).toHaveText(docente);

  // Escribir a mano vuelve el select a "Otro"; elegir de nuevo el nombre lo vuelve a marcar
  await nombre.fill('M.Sc. Dr. Alguien Nuevo');
  await expect(select).toHaveValue('');
  await nombre.fill(manual);
  await expect(select).toHaveValue(manual);
  await expect(fila.locator('.select2-selection__rendered')).toHaveText(manual);
});

test('tribunal: la misma persona no puede estar dos veces', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  await page.goto('certificadoaprobacionmonografia');
  await page.locator('#cert-termino').fill(String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`)));
  await expect(page.locator('#card-monografia')).toBeVisible({ timeout: 15_000 });

  // El docente elegido como presidente queda deshabilitado en la otra fila
  const filas = page.locator('#contenedor-tribunal .fila-tribunal');
  const docente = String(await filas.nth(0).locator('optgroup[label="Docentes"] option').first().getAttribute('value'));
  await filas.nth(0).locator('.select-docente-tribunal').selectOption(docente);
  await expect(filas.nth(1).locator(`.select-docente-tribunal option[value="${docente}"]`)).toBeDisabled();
  await expect(filas.nth(0).locator(`.select-docente-tribunal option[value="${docente}"]`)).toBeEnabled();

  // Escrito a mano con otro grado y sin tildes también cuenta como repetido
  const sinGrado = docente.replace(/^(\s*\S+\.)+\s*/, '').normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase();
  await filas.nth(1).locator('.input-nombre-tribunal').fill(`M.Sc. Dr. ${sinGrado}`);
  await page.locator('#input-titulo').fill('evaluación de prueba del tribunal repetido');
  await page.locator('#input-calificacion').fill('85');
  await page.locator('#btn-documento-completo').click();
  await expect(page.locator('.swal2-popup')).toContainText('Miembro repetido en el tribunal');

  // El servidor también lo rechaza
  const respuesta = await page.request.post('tcpdf/pdf/certificado-aprobacion-monografia-pdf.php', { form: {
    estudianteID: '0', programaID: '0', nombre: 'PRUEBA DEL TRIBUNAL', titulo: 'TRABAJO DE PRUEBA', calificacion: '80', completo: '1',
    tribunal: `Presidente: ${docente}|Vocal: M.Sc. Dr. ${sinGrado}` } });
  expect(await respuesta.text()).toContain('está dos veces en el tribunal');
});
