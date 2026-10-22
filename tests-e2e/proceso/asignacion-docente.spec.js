// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar } from './ayudantes-proceso.js';

// Asignación de docentes: tabla agrupada por programa, resumen que sigue a los filtros
// y asignación desde la ventana (con el docente actual ya elegido al "Cambiar").

test('filtros, resumen y asignación de un docente', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('asignaciondocente');
  const filas = page.locator('.fila-modulo:visible');
  const total = await filas.count();
  expect(total).toBeGreaterThan(0);
  await expect(page.locator('#stat-total')).toHaveText(String(total));
  await page.screenshot({ path: test.info().outputPath('asignacion.png') });
  // A todo el ancho: la tarjeta ocupa casi toda la ventana (antes quedaba en un contenedor fijo)
  const ancho = await page.locator('.ad-card').evaluate((el) => el.getBoundingClientRect().width);
  expect(ancho).toBeGreaterThan(page.viewportSize().width * 0.9);

  // Búsqueda sin resultados: se ocultan los grupos y aparece el aviso
  await page.locator('#filtro-buscar').fill('zzz-no-existe');
  await expect(filas).toHaveCount(0);
  await expect(page.locator('tbody.ad-programa:visible')).toHaveCount(0);
  await expect(page.locator('#ad-sin-resultados')).toBeVisible();
  await expect(page.locator('#stat-total')).toHaveText('0');
  await page.locator('#filtro-buscar').fill('');

  // Solo con docente: el resumen cuadra
  await page.evaluate(() => jQuery('#filtro-estado').val('asignado').trigger('change'));
  const con = await filas.count();
  await expect(page.locator('#stat-con')).toHaveText(String(con));
  await expect(page.locator('#stat-sin')).toHaveText('0');
  await page.evaluate(() => jQuery('#filtro-estado').val('').trigger('change'));

  // Cambiar: la ventana abre con el docente actual elegido; se guarda otro
  const boton = page.locator('.btn-cambiar-docente').first();
  const idModulo = await boton.getAttribute('data-modulo-id');
  const actual = String(await boton.getAttribute('data-docente-id'));
  await boton.click();
  await expect(page.locator('#modalAsignarDocente')).toBeVisible();
  await expect(page.locator('#modalAsignarDocenteTitulo')).toHaveText('Cambiar docente');
  await expect(page.locator('#select-docente')).toHaveValue(actual);
  const otro = String(valor(`SELECT DocenteID FROM docente WHERE Estado = 1 AND DocenteID <> ${actual} ORDER BY DocenteID LIMIT 1`));
  await page.locator('#select-docente').selectOption(otro);
  await page.locator('#btn-guardar-asignacion').click();
  await expect.poll(() => valor(`SELECT DocenteID FROM modulos WHERE Idmodulo = ${idModulo}`), { timeout: 10_000 }).toBe(otro);

  // Se deja como estaba
  await page.goto('asignaciondocente');
  await page.locator(`.btn-cambiar-docente[data-modulo-id="${idModulo}"]`).click();
  await page.locator('#select-docente').selectOption(actual);
  await page.locator('#btn-guardar-asignacion').click();
  await expect.poll(() => valor(`SELECT DocenteID FROM modulos WHERE Idmodulo = ${idModulo}`), { timeout: 10_000 }).toBe(actual);
});
