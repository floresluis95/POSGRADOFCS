// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, ajax, PROGRAMA_ID } from './ayudantes-proceso.js';

// Pestaña Gráficos del Reporte General: filtros por sede, grado y programa.

const sumaMeses = (datos) => (datos.meses || []).reduce((t, m) => t + m.matricula + m.cuotas + m.clinicas, 0);

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('servidor: los gráficos y la cartera respetan sede, grado y programa', async ({ page }) => {
  const sede = valor(`SELECT Sede FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);
  const grado = valor(`SELECT GradoAcademico FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);

  // Un solo programa: la cartera trae solo ese
  const cartera = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerReportePagosPrograma', programaID: String(PROGRAMA_ID) });
  expect(cartera.map((p) => Number(p.ProgramaID))).toEqual([PROGRAMA_ID]);

  // Sede y grado del programa: aparece; una sede inexistente: nada
  const conSede = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerReportePagosPrograma', sede, grado });
  expect(conSede.some((p) => Number(p.ProgramaID) === PROGRAMA_ID)).toBe(true);
  expect(conSede.every((p) => p.Sede === sede && p.GradoAcademico === grado)).toBe(true);
  const sinSede = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerReportePagosPrograma', sede: 'SEDE QUE NO EXISTE' });
  expect(sinSede).toEqual([]);

  // Cobrado por mes: con el programa = todo lo de ese programa; con sede inexistente = 0
  const todo = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerDatosGraficos', meses: '24' });
  const delPrograma = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerDatosGraficos', meses: '24', programaID: String(PROGRAMA_ID) });
  const ninguno = await ajax(page, 'reportemodulos.ajax.php', { accion: 'obtenerDatosGraficos', meses: '24', sede: 'SEDE QUE NO EXISTE' });
  expect(sumaMeses(delPrograma)).toBeLessThanOrEqual(sumaMeses(todo));
  expect(sumaMeses(ninguno)).toBe(0);
  expect(Object.values(ninguno.ordenes).every((g) => g.cantidad === 0)).toBe(true);
});

test('pantalla: la lista de programas se acota con la sede y el programa filtra la cartera', async ({ page }) => {
  const sede = valor(`SELECT Sede FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);
  const enSede = Number(valor(`SELECT COUNT(*) FROM programa WHERE Estado = 1 AND Sede = '${sede}'`));

  await page.goto('reportemodulos#graficos');
  await expect(page.locator('#rgCartera .rg-vacio')).toHaveCount(0, { timeout: 15_000 });
  await expect(page.locator('#rgSede option')).not.toHaveCount(1);

  // Sede: solo quedan sus programas (+ "Todos los programas")
  await page.evaluate((s) => window.jQuery('#rgSede').val(s).trigger('change'), sede);
  await expect(page.locator('#rgPrograma option')).toHaveCount(enSede + 1);

  // Programa: la cartera muestra solo ese programa
  const nombre = valor(`SELECT NombrePrograma FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);
  const respuesta = page.waitForResponse((r) => r.url().includes('reportemodulos.ajax.php') && (r.request().postData() || '').includes('obtenerReportePagosPrograma'));
  await page.evaluate((id) => window.jQuery('#rgPrograma').val(String(id)).trigger('change'), PROGRAMA_ID);
  await respuesta;
  await expect(page.locator('#rgCartera')).toContainText(nombre, { timeout: 15_000 });
  await expect(page.locator('#rgCartera .rg-barra-fila')).toHaveCount(1);
});
