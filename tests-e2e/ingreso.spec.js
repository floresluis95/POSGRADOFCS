// @ts-check
import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

// Usuario activo que existe en la base local (solo se usa con contraseña equivocada).
const USUARIO_EXISTENTE = 'luis123';
const USUARIO_INEXISTENTE = 'usuario_que_no_existe_xyz';
const USUARIO_BLOQUEO = 'usuario_bloqueo_xyz';

// Estas pruebas van contra el servidor de pruebas (copia "proyecto_pruebas"). Fallan el
// ingreso a propósito, así que se borran sus intentos para no bloquear a luis123 en la copia
// durante la misma corrida. La base real no se toca.
function borrarIntentos() {
  execFileSync('C:/xampp/mysql/bin/mysql.exe', ['-u', 'root', 'proyecto_pruebas'], {
    input: `DELETE FROM intentoingreso WHERE Usuario IN ('${USUARIO_EXISTENTE}', '${USUARIO_INEXISTENTE}', '${USUARIO_BLOQUEO}')`,
  });
}

test.describe('Ingreso al sistema', () => {

  test.beforeEach(borrarIntentos);
  test.afterAll(borrarIntentos);

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
    await page.getByLabel('Usuario').fill(USUARIO_INEXISTENTE);
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
  test('con 5 intentos fallidos el usuario queda bloqueado', async ({ page }) => {
    for (let i = 0; i < 5; i++) {
      await page.goto('ingreso');
      await page.getByLabel('Usuario').fill(USUARIO_BLOQUEO);
      await page.getByLabel('Contraseña', { exact: true }).fill('cualquiera');
      await page.locator('#btnLogin').click();
      await expect(page.getByText('Usuario o Contraseña incorrecto')).toBeVisible();
    }

    await page.goto('ingreso');
    await page.getByLabel('Usuario').fill(USUARIO_BLOQUEO);
    await page.getByLabel('Contraseña', { exact: true }).fill('cualquiera');
    await page.locator('#btnLogin').click();

    await expect(page.getByText('Demasiados intentos fallidos')).toBeVisible();
  });

});
