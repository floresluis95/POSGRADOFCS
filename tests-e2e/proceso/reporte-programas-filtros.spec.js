// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, PROGRAMA_ID } from './ayudantes-proceso.js';

// Reporte General de Programas: ocupa toda la pantalla y cada pestaña tiene sus filtros
// (Pagos por programa, Programas y módulos, y el detalle por estudiante).

const filas = (page) => page.locator('#tablaReportePagosBody tr');

test('Pagos por programa: filtros de sede, situación y búsqueda', async ({ page }) => {
  await page.setViewportSize({ width: 1600, height: 900 });
  await ingresar(page, 'adm');
  await page.goto('reportemodulos');
  await expect(page.locator('#reportePagosResultado')).toContainText('Mostrando', { timeout: 15_000 });

  // Toda la pantalla: el contenido es más ancho que el contenedor fijo de antes (1340 px)
  const ancho = await page.locator('.rp-pagina').evaluate((el) => el.getBoundingClientRect().width);
  expect(ancho).toBeGreaterThan(1340);

  const total = await filas(page).count();
  expect(total).toBeGreaterThan(0);

  // Búsqueda que no coincide con nada
  await page.locator('#reportePagosBuscar').fill('zzz-no-existe');
  await expect(filas(page).first()).toContainText('No hay programas');
  await expect(page.locator('#reportePagosResultado')).toContainText('Mostrando 0');

  // Limpiar vuelve a mostrar todo
  await page.locator('#reportePagosLimpiar').click();
  await expect(filas(page)).toHaveCount(total);

  // Situación: con saldo pendiente → solo programas con saldo
  await page.evaluate(() => jQuery('#reportePagosSituacion').val('saldo').trigger('change'));
  const nombre = String(valor(`SELECT NombrePrograma FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  await expect(page.locator('#tablaReportePagosBody')).toContainText(nombre);

  // Sede inexistente en la lista → la lista de sedes sale de los datos
  const sedes = await page.locator('#reportePagosSede option').allTextContents();
  expect(sedes[0]).toBe('Todas las sedes');
  expect(sedes.length).toBeGreaterThan(1);
});

test('Programas y módulos: filtros por estado e inscritos', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('reportemodulos');
  await page.locator('#rpTabProgramasLink').click();
  await expect(page.locator('#rpTabProgramas')).toBeVisible();
  await expect(page.locator('#rpProgResultado')).toBeVisible();
  const visibles = page.locator('#rpTablaProgramas tr.rp-fila-programa:visible');
  const total = await visibles.count();
  expect(total).toBeGreaterThan(0);
  await expect(page.locator('#rpProgResultado')).toContainText(`Mostrando ${total} de`);

  await page.locator('#rpProgBuscar').fill('zzz-no-existe');
  await expect(visibles).toHaveCount(0);
  await expect(page.locator('#rpProgSinResultados')).toBeVisible();

  await page.locator('#rpProgLimpiar').click();
  await expect(visibles).toHaveCount(total);

  await page.evaluate(() => jQuery('#rpProgInscritos').val('con').trigger('change'));
  for (const n of await visibles.evaluateAll((trs) => trs.map((tr) => Number(tr.getAttribute('data-inscritos'))))) {
    expect(n).toBeGreaterThan(0);
  }
});

test('Detalle por estudiante: filtro de situación y búsqueda', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('reportemodulos');
  const boton = page.locator(`.btn-detalle-pagos-programa[data-programa-id="${PROGRAMA_ID}"]`);
  await boton.click({ timeout: 15_000 });
  const cuerpo = page.locator('#tablaDetallePagosEstudianteBody');
  await expect(cuerpo.locator('tr').first()).not.toContainText('Cargando', { timeout: 15_000 });
  const total = await cuerpo.locator('tr').count();

  await page.locator('#detallePagosEstado').selectOption('saldo');
  await expect(cuerpo.locator('.rp-chip.is-exito')).toHaveCount(0); // nadie con "Completado"

  await page.locator('#detallePagosEstado').selectOption('');
  await page.locator('#detallePagosBuscar').fill('zzz-no-existe');
  await expect(cuerpo).toContainText('Ningún estudiante coincide');
  await page.locator('#detallePagosBuscar').fill('');
  await expect(cuerpo.locator('tr')).toHaveCount(total);
});
