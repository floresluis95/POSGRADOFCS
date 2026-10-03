// @ts-check
import { test, expect } from '@playwright/test';

// Usuario activo que existe en la base local (solo se usa con contraseña equivocada).
const USUARIO_EXISTENTE = 'luis123';

test.describe('Ingreso al sistema', () => {

  test('la página de ingreso muestra el formulario', async ({ page }) => {
    await page.goto('ingreso');

    await expect(page.getByRole('heading', { name: 'Iniciar sesión' })).toBeVisible();
    await expect(page.getByLabel('Usuario')).toBeVisible();
    await expect(page.getByLabel('Contraseña', { exact: true })).toBeVisible();
    await expect(page.locator('#btnLogin')).toBeVisible();
  });

  test('sin sesión, el panel redirige al ingreso', async ({ page }) => {
    await page.goto('panel');

    await expect(page).toHaveURL(/ingreso$/);
  });

  test('un usuario que no existe ve el mensaje de error', async ({ page }) => {
    await page.goto('ingreso');
    await page.getByLabel('Usuario').fill('usuario_que_no_existe_xyz');
    await page.getByLabel('Contraseña', { exact: true }).fill('cualquiera');
    await page.locator('#btnLogin').click();

    await expect(page.getByText('Usuario o Contraseña incorrecto')).toBeVisible();
  });

  test('un usuario existente con contraseña incorrecta ve el mensaje de error', async ({ page }) => {
    await page.goto('ingreso');
    await page.getByLabel('Usuario').fill(USUARIO_EXISTENTE);
    await page.getByLabel('Contraseña', { exact: true }).fill('contraseña_equivocada_xyz');
    await page.locator('#btnLogin').click();

    await expect(page.getByText('Usuario o Contraseña incorrecto')).toBeVisible();
    await expect(page).toHaveURL(/ingreso$/);
  });

});
