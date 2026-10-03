// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Pruebas de navegador (E2E) del sistema.
 * Requiere XAMPP encendido (Apache + MySQL).
 * Ejecutar: npx playwright test
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
    baseURL: 'http://localhost/POSGRADOFCS/',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
