// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, ajax, PROGRAMA_ID } from './ayudantes-proceso.js';

// Pestaña Gráficos: gráficos nuevos (antigüedad de la deuda, estado de pago, notas y
// aprobación) y lo dinámico (por mes / acumulado, leyenda con clic y clic en un programa).

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('servidor: antigüedad de la deuda y notas coinciden con la base', async ({ page }) => {
  const datos = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerDatosGraficos', meses: '12', programaID: String(PROGRAMA_ID) });
  expect(datos.antiguedad.map((t) => t.tramo)).toEqual(['1-30', '31-60', '61-90', '90+']);
  const vencidas = Number(valor(`SELECT COUNT(*) FROM ordenpago WHERE ProgramaID = ${PROGRAMA_ID} AND Estado = 'PENDIENTE'
    AND Observaciones IN ('CUOTA PROGRAMA', 'CLINICA') AND FechaVencimiento < CURDATE()`));
  expect(datos.antiguedad.reduce((t, x) => t + x.cantidad, 0)).toBe(vencidas);

  const notas = Number(valor(`SELECT COUNT(*) FROM calificacion WHERE ProgramaId = ${PROGRAMA_ID} AND Nota IS NOT NULL`));
  expect(datos.notas.rangos.reduce((t, r) => t + r.cantidad, 0)).toBe(notas);
  expect(datos.notas.rangos[0].reprobado).toBe(true);
  const aprobados = Number(valor(`SELECT COUNT(*) FROM calificacion WHERE ProgramaId = ${PROGRAMA_ID} AND Nota >= ${datos.notas.minima}`));
  const prog = datos.notas.programas.find((p) => p.programaID === PROGRAMA_ID);
  expect(prog ? prog.aprobados : 0).toBe(aprobados);
});

test('pantalla: gráficos nuevos, acumulado, leyenda y clic en un programa', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('reportemodulos#graficos');
  await expect(page.locator('#rgEstado .rg-estado-item')).toHaveCount(3, { timeout: 20_000 });
  await expect(page.locator('#rgNotas .rg-barra-fila')).toHaveCount(4);
  await expect(page.locator('#rgAntiguedad')).not.toContainText('Cargando');

  // Acumulado: el último mes muestra el total del periodo (mismo número que el título)
  const columnas = page.locator('#rgCobradoMes .rg-col');
  await expect(columnas.first()).toBeVisible();
  await page.locator('[data-rg-vista="acumulado"]').click();
  await expect(page.locator('[data-rg-vista="acumulado"]')).toHaveAttribute('aria-pressed', 'true');
  const total = await page.locator('#rgCobradoTotal').textContent();
  await columnas.last().hover();
  await expect(page.locator('#rgTooltip')).toContainText(String(total).trim());

  // Leyenda: ocultar Matrícula la tacha y la quita del tooltip
  await page.locator('#rgCobradoLeyenda [data-rg-serie="matricula"]').click();
  await expect(page.locator('#rgCobradoLeyenda [data-rg-serie="matricula"]')).toHaveClass(/is-oculta/);
  await columnas.last().hover();
  await expect(page.locator('#rgTooltip')).not.toContainText('Matrícula');
  await page.locator('#rgCobradoLeyenda [data-rg-serie="matricula"]').click();
  await page.locator('[data-rg-vista="mensual"]').click();
  await page.screenshot({ path: test.info().outputPath('graficos.png'), fullPage: true });

  // Clic en un programa de la cartera: filtra toda la pestaña y aparece "quitar"
  await page.locator(`#rgCartera [data-rg-programa="${PROGRAMA_ID}"]`).click();
  await expect(page.locator('#rgPrograma')).toHaveValue(String(PROGRAMA_ID));
  await expect(page.locator('#rgQuitarPrograma')).toBeVisible();
  await page.locator('#rgQuitarPrograma').click();
  await expect(page.locator('#rgPrograma')).toHaveValue('');
  await expect(page.locator('#rgQuitarPrograma')).toBeHidden();
});
