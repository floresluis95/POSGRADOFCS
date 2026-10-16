// @ts-check
import { test, expect } from '@playwright/test';
import {
  valor, unico, ingresar, ajax, registrarEstudiante, preregistrar, ordenPendiente, validarVoucherMatricula,
  estudianteInscrito, generarOrdenCuota, IMAGEN_VOUCHER, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// Plan al Contado sin matrícula (una sola orden por el programa completo), edición del
// pago de clínicas en Historial de Pagos, aviso si la orden de cuota no tiene firmante y
// botones Excel/PDF junto a las tablas.

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test.describe('Plan al Contado sin matrícula', () => {
  test('genera una sola orden por el programa completo y al validar queda pagado', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    const costo = Number(valor(`SELECT Costo FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
    const total = Math.round(costo * 0.9 * 100) / 100;
    await preregistrar(page, estudianteID, {
      tipoPagoCodigo: 'CONTADO', tipoPago: 'PLAN AL CONTADO', pagarMatricula: undefined, montoAPagar: '0.00',
      porcentajeDescuentoPrograma: '10', numeroCuotas: '3',
    });
    const idOrden = ordenPendiente(estudianteID);
    expect(idOrden, 'se debió crear la orden').not.toBeNull();
    const [pagoCompleto, montoFinal, cuotas, costoMatricula] = String(valor(
      `SELECT CONCAT_WS('|', PagoCompleto, MontoFinal, PlanPropuestoNumeroCuotas, CostoMatricula) FROM ordenpago WHERE IdOrdenPago = ${idOrden}`,
    )).split('|');
    expect(pagoCompleto).toBe('1');
    expect(Number(montoFinal)).toBe(total);
    expect(cuotas).toBe('1');
    expect(Number(costoMatricula)).toBe(0);

    // Sin voucher no se inscribe (se paga el programa)
    const sinVoucher = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'validarVoucherMatricula', idOrdenPago: String(idOrden), numeroVoucher: '', fechaInscripcion: new Date().toISOString().slice(0, 10),
    });
    expect(sinVoucher.success).toBe(false);

    const voucher = unico() + '-31';
    const validado = await validarVoucherMatricula(page, idOrden, voucher);
    expect(validado.success, validado.mensaje).toBe(true);
    const idInscripcion = valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`);
    expect(valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${idInscripcion}`)).toBe('SIN MATRÍCULA');
    expect(valor(`SELECT CONCAT_WS('|', NumeroCuota, montocuota, nvaucher, Estado) FROM pagomodulo WHERE idinscripcion = ${idInscripcion}`))
      .toBe(`1|${total.toFixed(2)}|${voucher}|PAGADO`);
    expect(valor(`SELECT CONCAT_WS('|', TipoPlan, NumeroCuotas) FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`)).toBe('DESCUENTO|1');

    // Saldo del programa en 0: ya no se puede generar otra cuota
    const otra = await generarOrdenCuota(page, { estudianteID, idInscripcion: Number(idInscripcion) }, '100');
    expect(otra.success).toBe(false);
  });

  test('en pantalla: al quitar "Cobrar matrícula" en Contado avisa y fija una cuota', async ({ page }) => {
    await page.goto('preregistro');
    await page.evaluate(() => {
      jQuery('#costoTotalPrograma').val('10000.00');
      jQuery('#tipoPagoCodigo').val('CONTADO').trigger('change');
      jQuery('#pagarMatricula').prop('checked', false).trigger('change');
    });
    await expect(page.locator('#avisoProgramaCompleto')).toContainText('programa completo');
    await expect(page.locator('#avisoProgramaCompleto')).toContainText('Bs. 10.000,00');
    await expect(page.locator('#numeroCuotas')).toHaveValue('1');
    await expect(page.locator('#montoAPagar')).toHaveValue('10000.00');
    // Con matrícula vuelve a ser la orden de matrícula
    await page.evaluate(() => jQuery('#pagarMatricula').prop('checked', true).trigger('change'));
    await expect(page.locator('#avisoProgramaCompleto')).toBeHidden();
  });
});

test.describe('Historial de Pagos: editar clínicas', () => {
  test('corrige voucher, monto y fecha del pago de clínica (voucher único)', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const orden = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), montoTotal: '350', firma: 'FIRMANTE DE PRUEBA',
    });
    expect(orden.success, orden.mensaje).toBe(true);
    const voucher = unico() + '-07';
    const pago = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarPago', idinscripcion: String(inscrito.idInscripcion), fechaPago: new Date().toLocaleDateString('sv-SE'), numeroVoucher: voucher,
    }, { fotoVoucher: IMAGEN_VOUCHER });
    expect(pago.success, pago.mensaje).toBe(true);
    const idPagoClinica = valor(`SELECT IdPagoClinica FROM pagoclinica WHERE NumeroVoucher = '${voucher}'`);

    // Un voucher ya usado en otro pago no se acepta
    const usado = String(valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${inscrito.idInscripcion}`));
    const repetido = await ajax(page, 'historialpagos.ajax.php', {
      accion: 'actualizarClinica', idPagoClinica: String(idPagoClinica), numeroVoucher: usado, fechaPago: '2026-10-01', monto: '400',
    });
    expect(repetido.success).toBe(false);
    expect(repetido.mensaje).toContain('ya fue registrado');

    // En pantalla: botón editar de la fila de clínica
    const nuevo = unico() + '-08';
    await page.goto('historialpagos');
    await page.locator('#valorBusquedaHistorial').fill(String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`)));
    await page.locator('#btnBuscarHistorial').click();
    const fila = page.locator('tr', { hasText: voucher });
    await fila.locator('.btnEditarPagoHistorial').click();
    const modal = page.locator('#modalEditarPagoHistorial');
    await expect(modal).toBeVisible();
    await modal.locator('#editPagoVoucher_largo').fill(nuevo.split('-')[0]);
    await modal.locator('#editPagoVoucher_corto').fill(nuevo.split('-')[1]);
    await modal.locator('#editPagoMonto').fill('400');
    await modal.locator('button[type="submit"]').click();
    await page.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT CONCAT_WS('|', NumeroVoucher, Monto) FROM pagoclinica WHERE IdPagoClinica = ${idPagoClinica}`), { timeout: 15_000 })
      .toBe(`${nuevo}|400.00`);
    expect(valor(`SELECT MontoTotal FROM ordenpago op JOIN pagoclinica pc ON pc.IdOrdenPago = op.IdOrdenPago WHERE pc.IdPagoClinica = ${idPagoClinica}`)).toBe('400.00');
  });
});

test.describe('Orden de cuota sin responsable de la firma', () => {
  test('avisa y permite elegir el responsable o generar de todas formas', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    page.context().on('page', (nueva) => nueva.close().catch(() => {}));
    await page.goto('matriculados');
    await page.evaluate(() => localStorage.removeItem('opUltimaFirma'));
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');
    await expect(modal.locator('#opMontoCuota')).toBeVisible({ timeout: 15_000 });
    await modal.locator('#opMontoCuota').fill('500');
    await expect(modal.locator('#opFirma option').nth(1)).toBeAttached({ timeout: 15_000 });
    await modal.locator('#opFirma').selectOption('');

    // Sin firmante: aviso con las dos opciones
    await modal.locator('#opBtnGenerarOrden').click();
    const aviso = page.locator('.swal2-popup');
    await expect(aviso).toContainText('Seleccione el responsable de la firma');
    await aviso.locator('.swal2-confirm').click();
    await expect(modal.locator('#opFirma')).toHaveClass(/is-invalid/);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`)).toBe('0');

    // "Generar de todas formas": pasa al resumen (Firma: sin responsable) y se genera
    await modal.locator('#opBtnGenerarOrden').click();
    await page.locator('.swal2-popup .swal2-cancel').click();
    await expect(page.locator('.swal2-popup')).toContainText('Sin responsable de la firma');
    await page.locator('.swal2-popup .swal2-confirm').click();
    await expect.poll(() => valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`), { timeout: 15_000 })
      .toBe('1');
  });
});

test.describe('Exportar tablas', () => {
  test('Matriculados y Órdenes de pago exportan a Excel y PDF', async ({ page }) => {
    await estudianteInscrito(page);
    await page.goto('matriculados');
    const barra = page.locator('.exportar-barra').first();
    await expect(barra.locator('.exportar-excel')).toBeVisible({ timeout: 15_000 });
    const [excel] = await Promise.all([page.waitForEvent('download'), barra.locator('.exportar-excel').click()]);
    expect(excel.suggestedFilename()).toMatch(/^matriculados-\d{8}\.xlsx$/);
    const [pdf] = await Promise.all([page.waitForEvent('download'), barra.locator('.exportar-pdf').click()]);
    expect(pdf.suggestedFilename()).toMatch(/^matriculados-\d{8}\.pdf$/);

    await page.goto('ordenespago');
    await page.locator('#op_btn_buscar').click();
    const [pdfOrdenes] = await Promise.all([page.waitForEvent('download'), page.locator('#op_exportar_pdf').click()]);
    expect(pdfOrdenes.suggestedFilename()).toMatch(/^ordenes-de-pago-\d{8}\.pdf$/);
  });
});
