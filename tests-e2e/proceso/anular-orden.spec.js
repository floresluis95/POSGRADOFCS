// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, ajax, estudianteInscrito, generarOrdenCuota, PROGRAMA_ID } from './ayudantes-proceso.js';

// Anular una orden pendiente desde Matriculados (Gestión de Pagos): se pide el motivo, que
// queda guardado con quién y cuándo, y se ve en Órdenes de pago. Pre-Registro sigue
// anulando sin motivo.

const MOTIVO = 'Monto equivocado; se generará una orden nueva';
const ultimaCuota = (idInscripcion) =>
  valor(`SELECT MAX(IdOrdenPago) FROM ordenpago WHERE idinscripcion = ${idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`);

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test('cuota: la caja pide el motivo, anula y el motivo se ve en Órdenes de pago', async ({ page }) => {
  const inscrito = await estudianteInscrito(page);
  expect((await generarOrdenCuota(page, inscrito, '1000')).success).toBe(true);
  const idOrden = ultimaCuota(inscrito.idInscripcion);
  page.context().on('page', (nueva) => nueva.close().catch(() => {}));

  await page.goto('matriculados');
  await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
  const modal = page.locator('#modalInscribirModulo');
  const boton = modal.locator('#btnAnularOrdenCuota');
  await expect(boton).toBeVisible({ timeout: 15_000 });

  // Al quitar el puntero después del clic no queda pintado (antes el tema lo dejaba lila)
  await boton.click();
  await page.mouse.move(0, 0);
  await expect(boton).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');

  // Sin motivo no anula
  const caja = modal.locator('#cajaAnularCuota');
  await expect(caja).toBeVisible();
  await caja.locator('.btn-confirmar-anular').click();
  await expect(caja.locator('.vx-aviso')).toContainText('Escriba el motivo');
  expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('PENDIENTE');

  // Cancelar cierra la caja sin tocar la orden
  await caja.locator('.btn-cerrar-anular').click();
  await expect(caja).toBeHidden();

  // Con motivo: anulada, con quién y cuándo
  await boton.click();
  await caja.locator('.an-motivo').fill(MOTIVO);
  await caja.locator('.btn-confirmar-anular').click();
  await expect.poll(() => valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`), { timeout: 15_000 }).toBe('ANULADO');
  expect(valor(`SELECT MotivoAnulacion FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe(MOTIVO);
  expect(valor(`SELECT ResponsableAnulacion IS NOT NULL AND FechaAnulacion IS NOT NULL FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('1');

  // Órdenes de pago: la anulada muestra el motivo
  const numero = String(valor(`SELECT NumeroOrden FROM ordenpago WHERE IdOrdenPago = ${idOrden}`));
  await page.goto('ordenespago');
  await page.locator('#op_btn_buscar').click();
  const fila = page.locator('#op_tabla tbody tr', { hasText: numero });
  await expect(fila.locator('.op-motivo-anulacion')).toContainText(MOTIVO, { timeout: 15_000 });
});

test('servidor: con motivo vacío o corto no anula; sin el campo (Pre-Registro) anula como antes', async ({ page }) => {
  const inscrito = await estudianteInscrito(page);
  expect((await generarOrdenCuota(page, inscrito, '1000')).success).toBe(true);
  const idOrden = ultimaCuota(inscrito.idInscripcion);

  const corto = await ajax(page, 'ordenpago.ajax.php', { accion: 'anularPreregistro', idOrdenPago: String(idOrden), motivo: '  no ' });
  expect(corto.success).toBe(false);
  expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('PENDIENTE');

  const sinCampo = await ajax(page, 'ordenpago.ajax.php', { accion: 'anularPreregistro', idOrdenPago: String(idOrden) });
  expect(sinCampo.success, sinCampo.mensaje).toBe(true);
  expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('ANULADO');
  expect(valor(`SELECT MotivoAnulacion IS NULL FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('1');
});

test('clínica: la misma caja con motivo', async ({ page }) => {
  const inscrito = await estudianteInscrito(page);
  const orden = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: '350', firma: 'FIRMANTE DE PRUEBA',
  });
  expect(orden.success, orden.mensaje).toBe(true);
  const idOrden = valor(`SELECT MAX(IdOrdenPago) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PENDIENTE' AND Observaciones <> 'CUOTA PROGRAMA'`);

  await page.goto('matriculados');
  await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
  const modal = page.locator('#modalInscribirModulo');
  await modal.locator('#tab-clinica-link').click();
  await modal.locator('#clBtnAnular').click();
  const caja = modal.locator('#clCajaAnular');
  await caja.locator('.an-motivo').fill(MOTIVO);
  await caja.locator('.btn-confirmar-anular').click();
  await expect.poll(() => valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`), { timeout: 15_000 }).toBe('ANULADO');
  expect(valor(`SELECT MotivoAnulacion FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe(MOTIVO);
  // La pestaña se recarga: ya no hay orden pendiente que anular
  await expect(modal.locator('#clBtnAnular')).toHaveCount(0, { timeout: 15_000 });
});
