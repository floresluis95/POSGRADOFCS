// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Pruebas de navegador (E2E) del sistema.
 * Requiere XAMPP encendido (Apache + MySQL).
 * Ejecutar: npx playwright test
 *
 * Proyectos:
 *  - chromium: navegación y permisos sobre el Apache de XAMPP (base "proyecto", solo lee).
 *  - proceso:  pruebas que CREAN datos (duplicados, órdenes vacías, proceso completo).
 *              Usan el servidor embebido de PHP en el puerto 8099 con la copia
 *              "proyecto_pruebas", que se refresca antes de cada corrida (preparar-bd).
 *              Solo esas: npx playwright test --project=proceso
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests-e2e',
  // En serie: el Apache local de XAMPP se satura con varios navegadores a la vez
  // (cada página pide ~100 archivos) y las pruebas se quedan esperando.
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'html',
  use: {
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  webServer: {
    command: '"C:/xampp/php/php.exe" -S localhost:8099 -t . tests-e2e/servidor-pruebas.php',
    url: 'http://localhost:8099/ingreso',
    env: { POSGRADO_BD: 'proyecto_pruebas' },
    reuseExistingServer: true,
    timeout: 30_000,
  },
  projects: [
    {
      name: 'chromium',
      testIgnore: /proceso[\\/]/,
      use: { ...devices['Desktop Chrome'], baseURL: 'http://localhost/POSGRADOFCS/' },
    },
    {
      name: 'preparar-bd',
      testMatch: /proceso[\\/]preparar-bd\.setup\.js/,
    },
    {
      name: 'proceso',
      testMatch: /proceso[\\/].*\.spec\.js/,
      dependencies: ['preparar-bd'],
      use: { ...devices['Desktop Chrome'], baseURL: 'http://localhost:8099/' },
    },
  ],
});
