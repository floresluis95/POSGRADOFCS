// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Biblioteca de tesis: catálogo (trabajogrado). El personal ve todas (con C.I.); estudiantes y
// docentes solo las aprobadas, sin C.I. ni calificación. Préstamos: tests-e2e/proceso/prestamo-tesis.spec.js
test.describe('Biblioteca de tesis', () => {
  test('busca por autor, palabras clave y título', async ({ page }) => {
    await ingresar(page, 'sec');
    await page.goto('bibliotecatesis');

    // Sin buscar: ya lista todas
    await expect(page.locator('.bt-tesis').first()).toBeVisible();
    expect(await page.locator('.bt-tesis').count()).toBeGreaterThan(0);
    await expect(page.locator('.bt-autor').first()).toContainText('C.I.');

    // Una palabra que no existe: sin resultados
    await page.locator('#bt-q').fill('zzqqxx inexistente');
    await page.locator('label[for="bt-campo-palabras"]').click();
    await page.getByRole('button', { name: 'Buscar' }).click();
    await expect(page.getByText('No se encontraron tesis')).toBeVisible();

    // Por autor con el apellido del primer resultado (minúsculas: no distingue)
    await page.getByRole('link', { name: /Quitar la búsqueda/ }).click();
    const autor = (await page.locator('.bt-autor').first().innerText()).split('·')[0].trim();
    const apellido = autor.split(' ').pop() ?? '';
    await page.locator('#bt-q').fill(apellido.toLowerCase());
    await page.locator('label[for="bt-campo-autor"]').click();
    await page.getByRole('button', { name: 'Buscar' }).click();
    await expect(page.locator('.bt-tesis').first()).toBeVisible();
    await expect(page.locator('.bt-tesis mark').first()).toBeVisible();

    // El personal administrativo no solicita préstamos
    await expect(page.locator('.bt-solicitar')).toHaveCount(0);
  });

  for (const rol of ['est', 'doc']) {
    test(`${rol.toUpperCase()}: ve solo las aprobadas, sin C.I., con la cita APA`, async ({ page }) => {
      await ingresar(page, rol);
      await page.goto('bibliotecatesis');
      await expect(page.locator('.bt-tesis').first()).toBeVisible();
      await expect(page.locator('.bt-disp.proceso')).toHaveCount(0);
      await expect(page.locator('.bt-autor').first()).not.toContainText('C.I.');
      await expect(page.locator('.bt-aprobadas')).toHaveCount(0);
      await page.getByText('Cómo citar (APA 7)').first().click();
      await expect(page.locator('.bt-cita-texto').first()).toContainText(/\(\d{4}\)\./);
      await expect(page.getByRole('link', { name: /Mis préstamos/ })).toBeVisible();
    });
  }
});
