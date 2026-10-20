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
