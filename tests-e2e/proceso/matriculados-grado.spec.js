// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Matriculados: filtro por grado académico (acota la tabla y la lista de programas).

test('el filtro de grado académico acota la tabla y los programas', async ({ page }) => {
  await ingresar(page, 'adm');
  await estudianteInscrito(page);
  const grado = valor(`SELECT GradoAcademico FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);
  const activosDelGrado = Number(valor(`SELECT COUNT(*) FROM estudianteprograma ep INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                                        WHERE ep.Estado = 'ACTIVO' AND p.GradoAcademico = '${grado}'`));

  await page.goto('matriculados');
  await expect(page.locator('#selectGradoFiltro option', { hasText: String(grado) })).toHaveCount(1);

  // Al elegir el grado: la tabla tiene solo sus matriculados activos
  const respuesta = page.waitForResponse((r) => r.url().includes('inscripcionmodulo.ajax.php') && (r.request().postData() || '').includes('gradoFiltro'));
  await page.evaluate((g) => window.jQuery('#selectGradoFiltro').val(g).trigger('change'), grado);
  await respuesta;
  await expect(page.locator('#tablaMatriculados_info')).toContainText(`de ${activosDelGrado} estudiantes`, { timeout: 15_000 });

  // La lista de programas muestra solo los de ese grado (más "Todos")
  const programasDelGrado = Number(valor(`SELECT COUNT(*) FROM programa WHERE Estado = 1 AND GradoAcademico = '${grado}'`));
  const opciones = await page.locator('#selectProgramaFiltro option').count();
  expect(opciones - 1).toBeLessThanOrEqual(programasDelGrado);
  expect(await page.locator('#selectProgramaFiltro option[data-grado]').evaluateAll((os, g) => os.every((o) => o.getAttribute('data-grado') === g), grado)).toBe(true);

  // Limpiar filtros vuelve a todos los grados y programas
  await page.locator('#btnLimpiarFiltro').click();
  await expect(page.locator('#selectGradoFiltro')).toHaveValue('');
  expect(await page.locator('#selectProgramaFiltro option').count()).toBeGreaterThanOrEqual(opciones);
});
