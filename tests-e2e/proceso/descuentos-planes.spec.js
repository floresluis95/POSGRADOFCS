// @ts-check
import { test, expect } from '@playwright/test';
import {
  valor, unico, ingresar, ajax, registrarEstudiante, preregistrar, ordenPendiente,
  validarVoucherMatricula, generarOrdenCuota, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// Descuentos por separado (decidido el 2026-10-10): en los tres planes el Pre-Registro
// define un % para la matrícula (esta orden) y otro % para el programa (plan de cuotas).
// El plan del programa queda definitivo al validar el voucher, también en Grupal, y
// ningún plan obliga a pagar el programa en una sola cuota.

const PLANES = /** @type {const} */ ([
  ['REGULAR', 'PLAN REGULAR', 'REGULAR'],
  ['CONTADO', 'PLAN AL CONTADO', 'DESCUENTO'],
  ['GRUPAL', 'PLAN GRUPAL', 'GRUPAL'],
]);

const redondear = (monto) => Math.round(monto * 100) / 100;

test.describe('Descuentos de matrícula y programa por separado', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('en la pantalla hay un % para la matrícula y otro para el programa', async ({ page }) => {
    await page.goto('preregistro');
    await expect(page.locator('#porcentajeDescuento')).toHaveCount(1);
    await expect(page.locator('#porcentajeDescuentoPrograma')).toHaveCount(1);
  });

  for (const [codigo, nombre, tipoPlan] of PLANES) {
    test(`${nombre}: cada descuento se guarda por separado y el plan queda al validar el voucher`, async ({ page }) => {
      const matricula = Number(valor(`SELECT CostoMatricula FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
      const costoPrograma = Number(valor(`SELECT Costo FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
      const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;

      await preregistrar(page, estudianteID, {
        tipoPagoCodigo: codigo, tipoPago: nombre,
        porcentajeDescuento: '10', porcentajeDescuentoPrograma: '20',
        montoAPagar: redondear(matricula * 0.9).toFixed(2),
      });
      const idOrden = ordenPendiente(estudianteID);
      expect(idOrden, 'se debió crear la orden de matrícula').not.toBeNull();
      expect(valor(`SELECT PorcentajeDescuento FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('10.00');
      expect(valor(`SELECT MontoFinal FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe(redondear(matricula * 0.9).toFixed(2));
      expect(Number(valor(`SELECT PlanPropuestoPorcentaje FROM ordenpago WHERE IdOrdenPago = ${idOrden}`))).toBe(20);

      const validacion = await validarVoucherMatricula(page, idOrden, unico() + '-01');
      expect(validacion.success, validacion.mensaje).toBe(true);
      const idInscripcion = valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${PROGRAMA_ID}`);

      expect(valor(`SELECT TipoPlan FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`)).toBe(tipoPlan);
      expect(Number(valor(`SELECT PorcentajeDescuento FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`))).toBe(20);
      expect(valor(`SELECT MontoTotalPagar FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`)).toBe(redondear(costoPrograma * 0.8).toFixed(2));
    });

    test(`${nombre}: el programa se puede pagar en varias cuotas`, async ({ page }) => {
      const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
      await preregistrar(page, estudianteID, { tipoPagoCodigo: codigo, tipoPago: nombre, porcentajeDescuentoPrograma: '15' });
      const validacion = await validarVoucherMatricula(page, ordenPendiente(estudianteID), unico() + '-01');
      expect(validacion.success, validacion.mensaje).toBe(true);
      const idInscripcion = Number(valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${PROGRAMA_ID}`));

      // Una primera cuota por menos del saldo (antes Contado y Grupal exigían el total)
      const saldo = Number(valor(`SELECT MontoTotalPagar FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`));
      const r = await generarOrdenCuota(page, { estudianteID, idInscripcion }, redondear(saldo / 2).toFixed(2));
      expect(r.success, r.mensaje).toBe(true);
    });
  }

  test('el % del programa fuera de rango se ajusta entre 0 y 100', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID, { tipoPagoCodigo: 'CONTADO', tipoPago: 'PLAN AL CONTADO', porcentajeDescuentoPrograma: '150' });
    expect(Number(valor(`SELECT PlanPropuestoPorcentaje FROM ordenpago WHERE IdOrdenPago = ${ordenPendiente(estudianteID)}`))).toBe(100);
  });
});

// Inscripción ya no tiene la casilla "Matricular sin pago de matrícula": si se cobra o
// no la matrícula lo define el Pre-Registro y el servidor lo toma de la orden.
test.describe('Validar voucher según lo definido en el Pre-Registro', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  /** Valida sin N° de voucher ni foto, como haría la pantalla con la matrícula exonerada. */
  const validarSinVoucher = (page, idOrden) => ajax(page, 'ordenpago.ajax.php', {
    accion: 'validarVoucherMatricula', idOrdenPago: String(idOrden), numeroVoucher: '',
    fechaInscripcion: new Date().toISOString().slice(0, 10),
  });

  test('en la pantalla de Inscripción ya no está la casilla', async ({ page }) => {
    await page.goto('inscripcion');
    await expect(page.locator('#sinPagoMatricula')).toHaveCount(0);
  });

  test('matrícula exonerada: se inscribe sin voucher', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID, { montoAPagar: '0.00', pagarMatricula: undefined });
    const r = await validarSinVoucher(page, ordenPendiente(estudianteID));
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('SIN MATRÍCULA');
    expect(valor(`SELECT montoPagado FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('0.00');
  });

  test('matrícula cobrada: sin voucher no se inscribe (ya no se puede saltar)', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID);
    const idOrden = ordenPendiente(estudianteID);

    const sinNada = await validarSinVoucher(page, idOrden);
    expect(sinNada.success).toBe(false);
    // Lo que antes enviaba la casilla ya no tiene efecto
    const conCasilla = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'validarVoucherMatricula', idOrdenPago: String(idOrden), numeroVoucher: 'SIN MATRÍCULA', sinMatricula: '1',
      fechaInscripcion: new Date().toISOString().slice(0, 10),
    });
    expect(conCasilla.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('0');
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('PENDIENTE');
  });

  test('matrícula cobrada: con voucher y foto se inscribe con el monto pagado', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID);
    const idOrden = ordenPendiente(estudianteID);
    const r = await validarVoucherMatricula(page, idOrden, unico() + '-01');
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT montoPagado FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`))
      .toBe(valor(`SELECT MontoFinal FROM ordenpago WHERE IdOrdenPago = ${idOrden}`));
  });
});
