// @ts-check
import { expect } from '@playwright/test';

// Usuarios de prueba (uno por rol): tests-e2e/datos/usuarios-prueba.sql
export const CONTRASENA = 'Prueba123';

/**
 * Inicia sesión con el usuario de prueba del rol ('adm', 'sec', 'aux', 'doc', 'est').
 * La sesión queda en el navegador, así que page.request también la usa.
 */
export async function ingresar(page, rol) {
  await page.goto('ingreso');
  await page.getByLabel('Usuario').fill(`prueba_${rol}`);
  await page.getByLabel('Contraseña', { exact: true }).fill(CONTRASENA);
  await page.locator('#btnLogin').click();
  await expect(page).toHaveURL(/panel$/);
}
