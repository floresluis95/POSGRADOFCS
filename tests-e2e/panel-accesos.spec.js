// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Accesos rápidos del panel: cada rol ve sus grupos y cada enlace abre una página
// que ese rol puede ver (no lo devuelve al panel ni muestra la 404).
// Requiere los usuarios de prueba: tests-e2e/datos/usuarios-prueba.sql

const PANEL = (page) => page.locator('section.pn-bienvenida');
const PAGINA_404 = (page) => page.getByText('PAGINA NO ENCONTRADA');

// Grupos que se esperan por rol ('' = sin títulos de grupo)
const GRUPOS = {
  adm: ['Matrícula y pagos', 'Calificaciones', 'Certificados', 'Reportes y agenda'],
  sec: ['Matrícula y pagos', 'Calificaciones', 'Reportes y agenda'],
  aux: ['Matrícula y pagos', 'Calificaciones', 'Reportes y agenda'],
  doc: [],
  est: [],
};

for (const rol of Object.keys(GRUPOS)) {
  test(`${rol.toUpperCase()}: accesos rápidos del panel abren páginas permitidas`, async ({ page }) => {
    test.setTimeout(180_000);
    await ingresar(page, rol);
    // El panel puede tardar: se espera a que los accesos estén en la página antes de leerlos
    await page.locator('.pn-accesos a.pn-acceso').first().waitFor();

    const titulos = await page.locator('.pn-accesos-grupo').allInnerTexts();
    expect(titulos.map((t) => t.trim().toUpperCase())).toEqual(GRUPOS[rol].map((g) => g.toUpperCase()));

    const enlaces = await page.locator('.pn-accesos a.pn-acceso').evaluateAll(
      (as) => as.map((a) => a.getAttribute('href')),
    );
    expect(enlaces.length).toBeGreaterThan(0);

    for (const enlace of enlaces) {
      await test.step(enlace, async () => {
        await page.goto(enlace);
        if (enlace !== 'panel') {
          await expect(PANEL(page), `${enlace} devolvió al panel (rol sin permiso)`).toHaveCount(0);
        }
        await expect(PAGINA_404(page), `${enlace} mostró la 404`).toHaveCount(0);
      });
    }
  });
}

test('ADM: el grupo Certificados trae los 5 certificados y el Registro de documentos', async ({ page }) => {
  await ingresar(page, 'adm');
  const certificados = page.locator('.pn-accesos.is-certificados a.pn-acceso');
  await expect(certificados).toHaveCount(6);
  await expect(certificados.locator('strong')).toHaveText([
    'Calificaciones', 'Historial académico', 'Aprobación', 'Medio magnético', 'Revisión', 'Registro de documentos',
  ]);
});
