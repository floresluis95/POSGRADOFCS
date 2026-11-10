// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Biblioteca de tesis: solo consulta sobre trabajogrado (no escribe en la base)
test.describe('Biblioteca de tesis', () => {
  test('busca por autor, palabras clave y título', async ({ page }) => {
    await ingresar(page, 'sec');
    await page.goto('bibliotecatesis');
    await expect(page.getByText('Escriba el nombre del autor')).toBeVisible();

    // Sin texto: lista todas
    await page.getByRole('button', { name: 'Buscar' }).click();
    await expect(page.locator('.bt-tesis').first()).toBeVisible();
    const total = await page.locator('.bt-tesis').count();
    expect(total).toBeGreaterThan(0);

    // Una palabra que no existe: sin resultados
    await page.locator('#bt-q').fill('zzqqxx inexistente');
    await page.locator('#bt-campo').selectOption('palabras');
    await page.getByRole('button', { name: 'Buscar' }).click();
    await expect(page.getByText('No se encontraron tesis')).toBeVisible();

    // Por autor con el nombre del primer resultado (minúsculas: no distingue)
    await page.locator('#bt-q').fill('');
    await page.locator('#bt-campo').selectOption('todo');
    await page.getByRole('button', { name: 'Buscar' }).click();
    const autor = (await page.locator('.bt-tesis .bt-meta span').first().innerText()).split('·')[0].trim();
    const apellido = autor.split(' ').pop() ?? '';
    await page.locator('#bt-q').fill(apellido.toLowerCase());
    await page.locator('#bt-campo').selectOption('autor');
    await page.getByRole('button', { name: 'Buscar' }).click();
    await expect(page.locator('.bt-tesis').first()).toBeVisible();
    await expect(page.locator('.bt-tesis mark').first()).toBeVisible();
  });
});
