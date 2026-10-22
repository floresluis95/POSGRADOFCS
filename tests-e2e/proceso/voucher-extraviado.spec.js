// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, estudianteInscrito, IMAGEN_VOUCHER, PROGRAMA_ID } from './ayudantes-proceso.js';

// Orden con "voucher por validar" (estado EXTRAVIADO; cuotas y clínicas): el estudiante pagó pero perdió el
// voucher. La orden pasa a EXTRAVIADO con motivo; se puede generar la siguiente; no cuenta
// como cobrado hasta registrar su voucher, que se hace después en esa misma orden.

const MOTIVO = 'Perdió el voucher; se pidió el código a Tesoro';
const hoy = () => new Date().toLocaleDateString('sv-SE');

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

/** Orden de cuota por el monto dado; devuelve su IdOrdenPago. */
async function ordenCuota(page, inscrito, monto) {
  const r = await ajax(page, 'ordenpago.ajax.php', {
    accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
    idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: String(monto),
  });
  expect(r.success, r.mensaje).toBe(true);
  return Number(valor(`SELECT MAX(IdOrdenPago) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`));
}

const marcar = (page, idOrdenPago, motivo = MOTIVO) =>
  ajax(page, 'ordenpago.ajax.php', { accion: 'marcarVoucherExtraviado', idOrdenPago: String(idOrdenPago), motivo });

const pagarCuota = (page, inscrito, monto, voucher, idOrdenPago) =>
  ajax(page, 'pagomodulo.ajax.php', {
    accion: 'registrarPagoModulo', idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID),
    fechaPago: hoy(), montoCuota: String(monto), numeroVaucher: voucher,
    ...(idOrdenPago ? { idOrdenPago: String(idOrdenPago) } : {}),
  }, { fmodulo: IMAGEN_VOUCHER });

const resumen = async (page, inscrito) =>
  (await ajax(page, 'pagomodulo.ajax.php', { accion: 'obtenerResumenCuotas', idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID) }));

test.describe('Servidor', () => {
  test('cuota: con motivo pasa a EXTRAVIADO, permite la siguiente y luego se registra su voucher con su N° de cuota', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const ordenA = await ordenCuota(page, inscrito, 1000);

    // Motivo obligatorio
    expect((await marcar(page, ordenA, '')).success).toBe(false);
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenA}`)).toBe('PENDIENTE');

    const ok = await marcar(page, ordenA);
    expect(ok.success, ok.mensaje).toBe(true);
    expect(valor(`SELECT CONCAT(Estado, '|', MotivoExtraviado, '|', ResponsableExtraviado IS NOT NULL, '|', DATE(FechaExtraviado)) FROM ordenpago WHERE IdOrdenPago = ${ordenA}`))
      .toBe(`EXTRAVIADO|${MOTIVO}|1|${hoy()}`);

    // No cuenta como cobrado, pero se descuenta de lo que se puede pedir
    const r1 = (await resumen(page, inscrito)).resumen;
    expect(r1.totalPagado).toBe(0);
    expect(r1.montoEnEspera).toBe(1000);
    expect(r1.disponibleOrden).toBeCloseTo(r1.saldoPendiente - 1000, 2);
    expect((await resumen(page, inscrito)).extraviadas.map((o) => Number(o.IdOrdenPago))).toEqual([ordenA]);

    // Una orden mayor a lo disponible no se genera; una normal sí (la siguiente)
    const demasiado = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
      idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: String(r1.saldoPendiente),
    });
    expect(demasiado.success).toBe(false);
    const ordenB = await ordenCuota(page, inscrito, 1000);
    expect(ordenB).toBeGreaterThan(ordenA);

    // Se paga primero la B (pendiente): es la cuota 2
    const pagoB = await pagarCuota(page, inscrito, 1000, unico() + '-08');
    expect(pagoB.estado, pagoB.mensaje).toBe('exitoso');
    expect(valor(`SELECT NumeroCuota FROM pagomodulo WHERE IdOrdenPago = ${ordenB}`)).toBe('2');

    // Tesoro dio el código: se registra el voucher de A, que conserva el N° 1
    const pagoA = await pagarCuota(page, inscrito, 1000, unico() + '-09', ordenA);
    expect(pagoA.estado, pagoA.mensaje).toBe('exitoso');
    expect(valor(`SELECT NumeroCuota FROM pagomodulo WHERE IdOrdenPago = ${ordenA}`)).toBe('1');
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenA}`)).toBe('CONFIRMADO');

    const r2 = (await resumen(page, inscrito)).resumen;
    expect(r2.totalPagado).toBe(2000);
    expect(r2.montoEnEspera).toBe(0);
  });

  test('no se marcan órdenes de matrícula ni ya pagadas; el estudiante no puede marcar', async ({ page, browser }) => {
    const inscrito = await estudianteInscrito(page);
    const matricula = valor(`SELECT MIN(IdOrdenPago) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion}`);
    expect((await marcar(page, matricula)).success).toBe(false);

    const orden = await ordenCuota(page, inscrito, 1000);
    await pagarCuota(page, inscrito, 1000, unico() + '-10');
    expect((await marcar(page, orden)).success).toBe(false);
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${orden}`)).toBe('CONFIRMADO');

    const otra = await ordenCuota(page, inscrito, 1000);
    const est = await browser.newPage();
    await ingresar(est, 'est');
    const r = await est.request.post('ajax/ordenpago.ajax.php', { form: { accion: 'marcarVoucherExtraviado', idOrdenPago: String(otra), motivo: MOTIVO } });
    expect(r.status()).toBe(403);
    await est.close();
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${otra}`)).toBe('PENDIENTE');
  });

  test('clínica: se marca, se genera otra y luego se registra el voucher de la extraviada', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const clinica = (cambios = {}) => ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), montoTotal: '400', firma: 'FIRMANTE DE PRUEBA', ...cambios,
    });
    const c1 = await clinica();
    expect(c1.success, c1.mensaje).toBe(true);
    // Con una pendiente no se puede generar otra
    expect((await clinica()).success).toBe(false);

    expect((await marcar(page, c1.idOrdenPago)).success).toBe(true);
    const c2 = await clinica({ montoTotal: '250' });
    expect(c2.success, c2.mensaje).toBe(true);

    const pago = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarPago', idinscripcion: String(inscrito.idInscripcion), fechaPago: hoy(),
      numeroVoucher: unico() + '-11', idOrdenPago: String(c1.idOrdenPago),
    }, { fotoVoucher: IMAGEN_VOUCHER });
    expect(pago.success, pago.mensaje).toBe(true);
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${c1.idOrdenPago}`)).toBe('CONFIRMADO');
    expect(valor(`SELECT Monto FROM pagoclinica WHERE IdOrdenPago = ${c1.idOrdenPago}`)).toBe('400.00');
    // La segunda sigue pendiente
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${c2.idOrdenPago}`)).toBe('PENDIENTE');
  });
});

test.describe('Historiales: cuotas sin voucher validado', () => {
  test('la cuota con orden pendiente se ve como Pendiente en Historial de Pagos y en Gestión de Pagos', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const orden = await ordenCuota(page, inscrito, 1000);

    // Datos: Historial de Pagos trae la orden como cuota por pagar, con su N°
    const historial = await ajax(page, 'historialpagos.ajax.php', { accion: 'buscarPorEstudianteId', estudianteId: String(inscrito.estudianteID) });
    const programa = historial.programas.find((p) => Number(p.idInscripcion) === inscrito.idInscripcion);
    expect(programa.CuotasPorPagar.map((o) => [Number(o.IdOrdenPago), o.Estado, Number(o.NumeroCuota)])).toEqual([[orden, 'PENDIENTE', 1]]);

    // Pantalla Historial de Pagos
    const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);
    await page.goto('historialpagos');
    const buscar = page.locator('input[type="text"]:visible').first();
    await buscar.fill(ci);
    await buscar.press('Enter');
    const fila = page.locator('tr.hp-fila-pendiente');
    await expect(fila).toHaveCount(1, { timeout: 15_000 });
    await expect(fila).toContainText('PENDIENTE');
    await expect(fila).toContainText('Bs. 1.000,00');

    // Gestión de Pagos > Cuotas del programa
    await page.goto('matriculados');
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');
    await modal.locator('#tab-pago-modulos-link').click();
    await expect(modal.locator('#tablaCuotasBody tr.gp-fila-pendiente')).toContainText('Pendiente', { timeout: 15_000 });

    // Al validar el voucher deja de estar pendiente y queda como pagada
    const pago = await pagarCuota(page, inscrito, 1000, unico() + '-12');
    expect(pago.estado, pago.mensaje).toBe('exitoso');
    const despues = await ajax(page, 'historialpagos.ajax.php', { accion: 'buscarPorEstudianteId', estudianteId: String(inscrito.estudianteID) });
    const p2 = despues.programas.find((p) => Number(p.idInscripcion) === inscrito.idInscripcion);
    expect(p2.CuotasPorPagar).toEqual([]);
    expect(p2.Cuotas.map((c) => c.Estado)).toEqual(['PAGADO']);
  });
});

test.describe('Historiales: orden correlativo', () => {
  test('pagadas y pendientes van juntas por N° de cuota (1 extraviada, 2 pagada, 3 pendiente)', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const orden1 = await ordenCuota(page, inscrito, 1000);
    expect((await marcar(page, orden1)).success).toBe(true);
    await ordenCuota(page, inscrito, 1000);
    const voucherCuota2 = unico() + '-13';
    expect((await pagarCuota(page, inscrito, 1000, voucherCuota2)).estado).toBe('exitoso');
    await ordenCuota(page, inscrito, 1000);

    const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);
    const numeros = (filas) => filas.evaluateAll((trs) => trs.map((tr) => tr.querySelector('td strong').textContent.trim()));

    // Historial de Pagos
    await page.goto('historialpagos');
    const buscar = page.locator('input[type="text"]:visible').first();
    await buscar.fill(ci);
    await buscar.press('Enter');
    const tabla = page.locator('tr.hp-fila-pendiente').first().locator('xpath=ancestor::tbody[1]');
    await expect(tabla.locator('tr')).toHaveCount(3, { timeout: 15_000 });
    expect(await numeros(tabla.locator('tr'))).toEqual(['1', '2', '3']);
    await expect(tabla.locator('tr').nth(0)).toContainText('VOUCHER POR VALIDAR');
    await expect(tabla.locator('tr').nth(1)).toContainText('PAGADO');
    await expect(tabla.locator('tr').nth(2)).toContainText('PENDIENTE');

    // Gestión de Pagos > Cuotas del programa
    await page.goto('matriculados');
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');
    await modal.locator('#tab-pago-modulos-link').click();
    await expect(modal.locator('#tablaCuotasBody tr')).toHaveCount(3, { timeout: 15_000 });
    expect((await numeros(modal.locator('#tablaCuotasBody tr'))).map((t) => t.split(' ')[0])).toEqual(['1', '2', '3']);

    // Recibo de pago: las tres en orden, las no pagadas marcadas; el pagado y el saldo no cambian
    const recibo = await page.request.get(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${inscrito.idInscripcion}`);
    const html = await recibo.text();
    expect(html).toContain('Cuotas del programa (1 pagada · 2 pendientes de pago)');
    const i1 = html.indexOf('VOUCHER POR VALIDAR');
    const i2 = html.indexOf(voucherCuota2);
    const i3 = html.indexOf('PENDIENTE DE PAGO');
    expect(i1).toBeGreaterThan(-1);
    expect(i2).toBeGreaterThan(i1);
    expect(i3).toBeGreaterThan(i2);
    const pagado = (await resumen(page, inscrito)).resumen.totalPagado;
    expect(pagado).toBe(1000);
    expect(html).toContain('Bs. 1,000.00</strong></div>'); // "Pagado del programa": solo la cuota pagada
  });
});

test.describe('Pantalla', () => {
  test('Gestión de Pagos: marcar la cuota, generar la siguiente y registrar el voucher recuperado', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const ordenA = await ordenCuota(page, inscrito, 1000);
    page.context().on('page', (nueva) => nueva.close().catch(() => {}));

    await page.goto('matriculados');
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');

    // "Voucher extraviado" y "Anular orden" no quedan abiertos a la vez
    await modal.locator('.btn-abrir-extraviado').click();
    await expect(modal.locator('#cajaExtraviadoCuota')).toBeVisible();
    await modal.locator('#btnAnularOrdenCuota').click();
    await expect(modal.locator('#cajaExtraviadoCuota')).toBeHidden();
    await expect(modal.locator('#cajaAnularCuota')).toBeVisible();
    await modal.locator('.btn-abrir-extraviado').click();
    await expect(modal.locator('#cajaAnularCuota')).toBeHidden();
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenA}`)).toBe('PENDIENTE');

    // Orden pendiente → Voucher extraviado (motivo obligatorio)
    await expect(modal.locator('#cajaExtraviadoCuota')).toBeVisible();
    await modal.locator('#cajaExtraviadoCuota .btn-confirmar-extraviado').click();
    await expect(modal.locator('#cajaExtraviadoCuota .vx-aviso')).toBeVisible();
    await modal.locator('#cajaExtraviadoCuotaMotivo').fill(MOTIVO);
    await modal.locator('#cajaExtraviadoCuota .btn-confirmar-extraviado').click();
    await expect.poll(() => valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenA}`), { timeout: 15_000 }).toBe('EXTRAVIADO');

    // Ya se puede generar la siguiente: es la cuota 2 y se avisa lo que está en espera
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    await expect(modal.locator('#opMontoCuota')).toBeVisible({ timeout: 15_000 });
    await expect(modal.locator('#ordenPagoContenido .op-cab-titulo')).toContainText('Cuota 2');
    await expect(modal.locator('#ordenPagoContenido')).toContainText('voucher por validar');

    // Cuotas del programa: la orden aparece en la lista y se le registra el voucher
    await modal.locator('#tab-pago-modulos-link').click();
    const lista = modal.locator('#cuotasExtraviadas');
    await expect(lista).toContainText(MOTIVO, { timeout: 15_000 });
    await lista.locator('.btn-registrar-extraviada').click();
    await expect(modal.locator('#cuotaPendienteInfo')).toContainText('Cuota 1');
    await expect(modal.locator('#cuotaPendienteInfo')).toContainText('Voucher por validar');

    const voucher = String(Date.now()).slice(-7);
    await modal.locator('#numeroVaucher_largo').fill(voucher);
    await modal.locator('#fmodulo').setInputFiles(IMAGEN_VOUCHER);
    await page.locator('#capturaRecorteSinCambios').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
    await modal.locator('#btnGuardarPagoCuota').click();
    const confirmar = page.locator('.swal2-popup.vc-confirmar');
    await expect(confirmar).toContainText('voucher por validar');
    await confirmar.locator('.swal2-confirm').click();

    await expect.poll(() => valor(`SELECT CONCAT(nvaucher, '|', NumeroCuota) FROM pagomodulo WHERE IdOrdenPago = ${ordenA}`), { timeout: 15_000 })
      .toBe(`${voucher}|1`);
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenA}`)).toBe('CONFIRMADO');
    await expect(lista.locator('.vx-item')).toHaveCount(0, { timeout: 15_000 });
  });

  test('Órdenes de Pago: botón Extraviado con motivo y luego Registrar voucher', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const orden = await ordenCuota(page, inscrito, 1000);
    const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);

    await page.goto('ordenespago?ci=' + ci);
    const marcarBtn = page.locator(`.op-btn-extraviado[data-id="${orden}"]`);
    await expect(marcarBtn).toBeVisible({ timeout: 15_000 });
    await marcarBtn.click();
    await page.locator('.swal2-textarea').fill(MOTIVO);
    await page.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${orden}`), { timeout: 15_000 }).toBe('EXTRAVIADO');

    // La fila muestra el estado y el botón para registrar el voucher recuperado
    await expect(page.locator('.op-estado-extraviado').first()).toBeVisible({ timeout: 15_000 });
    await expect(page.locator('#op_extraviadas')).toHaveText('1');
    await page.locator(`.op-btn-validar[data-id="${orden}"]`).click();
    await page.locator('#opv_numero_largo').fill(unico() + '-14');
    await page.locator('#opv_archivo').setInputFiles(IMAGEN_VOUCHER);
    await page.locator('#capturaRecorteSinCambios').click();
    await page.locator('#opv_guardar').click();
    await expect(page.locator('.swal2-popup')).toContainText('Revise el pago');
    await page.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${orden}`), { timeout: 15_000 }).toBe('CONFIRMADO');
  });
});
