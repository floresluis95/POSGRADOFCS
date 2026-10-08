// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Recorre TODOS los enlaces del menú lateral de cada rol y abre cada página.
// Así se detecta un enlace del menú que falta en la lista blanca
// (controladores/enlaces.controlador.php) o una página que se rompe con ese rol.
// Requiere los usuarios de prueba: tests-e2e/datos/usuarios-prueba.sql

const PANEL = (page) => page.locator('section.pn-bienvenida');
const PAGINA_404 = (page) => page.getByText('PAGINA NO ENCONTRADA');

// Textos que PHP imprime cuando algo falla (display_errors activo en local)
const ERROR_PHP = /(Fatal error|Parse error|Warning|Notice|Deprecated|Uncaught)\s*:/;

for (const rol of ['adm', 'sec', 'aux', 'doc', 'est']) {
  test(`${rol.toUpperCase()}: todas las páginas de su menú abren sin errores`, async ({ page }) => {
    test.setTimeout(240_000);
    await ingresar(page, rol);

    const enlaces = await page.locator('#kt_aside_menu a.kt-menu__link[href]').evaluateAll(
      (as) => [...new Set(as.map((a) => a.getAttribute('href')))]
        .filter((h) => h && !h.startsWith('#') && !h.startsWith('javascript') && h !== 'panel' && h !== 'salir'),
    );
    expect(enlaces.length).toBeGreaterThan(0);

    const erroresJs = [];
    page.on('pageerror', (e) => erroresJs.push(`${page.url()}: ${e.message}`));

    for (const enlace of enlaces) {
      await test.step(enlace, async () => {
        await page.goto(enlace);
        await expect(PANEL(page), `${enlace} mostró el panel (no está en la lista blanca)`).toHaveCount(0);
        await expect(PAGINA_404(page), `${enlace} mostró la 404`).toHaveCount(0);
        const texto = await page.locator('body').innerText();
        expect(texto, `${enlace} muestra un error de PHP`).not.toMatch(ERROR_PHP);
      });
    }

    expect(erroresJs, 'errores de JavaScript en las páginas').toEqual([]);
  });
}
