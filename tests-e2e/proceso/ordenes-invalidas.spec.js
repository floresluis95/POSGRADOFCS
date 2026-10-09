// @ts-check
import { test, expect } from '@playwright/test';
import {
  valor, unico, ingresar, ajax, registrarEstudiante, preregistrar, ordenPendiente,
  validarVoucherMatricula, estudianteInscrito, generarOrdenCuota, pagarCuota, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// No se deben poder generar órdenes de pago vacías, en cero, negativas o mayores a lo que
// se debe. Cada prueba revisa en la BASE que la orden no quedó registrada.

// Problemas encontrados el 2026-10-08 y aún sin corregir en el servidor: la prueba queda
// marcada como "falla conocida" (test.fail). Al corregir cada uno, Playwright avisará que
// la prueba ya pasa y hay que quitar su test.fail().
const PENDIENTE = "Falla conocida: el servidor todavía no lo valida (pendiente de corregir)";

const contarOrdenes = (estudianteID) => valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${estudianteID}`);

test.describe('Órdenes de pago de matrícula (Pre-Registro)', () => {
  /** @type {number} */
  let estudianteID;

  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
    estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
  });

  for (const [caso, cambios, fallaConocida] of /** @type {[string, Record<string, string>, string?][]} */ ([
    ['sin estudiante', { idcliente: '' }],
    ['sin programa', { programa: '' }],
    ['sin monto', { montoAPagar: '' }],
    ['sin fecha', { fechaInscripcion: '' }],
    ['con monto en cero (sin exonerar la matrícula)', { montoAPagar: '0.00' }],
    ['con monto negativo', { montoAPagar: '-2100' }],
    ['con monto en texto', { montoAPagar: 'abc' }],
    ['con un monto distinto a la matrícula', { montoAPagar: '1' }],
    ['de "programa completo" en cero', { pagoCompleto: '1', montoAPagar: '0.00' }],
  ])) {
    test(`no se genera una orden ${caso}`, async ({ page }) => {
      test.fail(!!fallaConocida, fallaConocida);
      await preregistrar(page, estudianteID, cambios);
      expect(contarOrdenes(estudianteID)).toBe('0');
    });
  }

  test('sí se genera con la matrícula exonerada en Bs. 0 (caso permitido)', async ({ page }) => {
    const pagarMatricula = undefined;
    await preregistrar(page, estudianteID, { montoAPagar: '0.00', pagarMatricula });
    expect(contarOrdenes(estudianteID)).toBe('1');
  });

  test('sí se genera con el monto exacto de la matrícula', async ({ page }) => {
    await preregistrar(page, estudianteID);
    expect(contarOrdenes(estudianteID)).toBe('1');
  });

  for (const [caso, monto] of [['a cero', '0'], ['a un monto negativo', '-500']]) {
    test(`editar el pre-registro no deja la orden ${caso}`, async ({ page }) => {
      await preregistrar(page, estudianteID);
      const idOrden = ordenPendiente(estudianteID);
      const montoAntes = valor(`SELECT MontoFinal FROM ordenpago WHERE IdOrdenPago = ${idOrden}`);

      await ajax(page, 'ordenpago.ajax.php', {
        accion: 'actualizarPreregistro', idOrdenPago: String(idOrden), montoPagado: monto,
        pagoCompleto: '0', porcentajeDescuento: '0', montoDescuento: '0', fechaInscripcion: new Date().toISOString().slice(0, 10),
      });
      expect(valor(`SELECT MontoFinal FROM ordenpago WHERE IdOrdenPago = ${idOrden}`),
        'El servidor aceptó el nuevo monto sin validarlo').toBe(montoAntes);
    });
  }

  test('no se valida el voucher sin número ni sin foto', async ({ page }) => {
    await preregistrar(page, estudianteID);
    const idOrden = ordenPendiente(estudianteID);

    const sinNumero = await validarVoucherMatricula(page, idOrden, '');
    expect(sinNumero.success).toBe(false);

    const sinFoto = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'validarVoucherMatricula', idOrdenPago: String(idOrden), numeroVoucher: 'MAT-' + unico(),
    });
    expect(sinFoto.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('0');
  });
});

test.describe('Órdenes de cuota y pagos (Matriculados > Gestión de Pagos)', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  for (const [caso, monto] of [['vacía', ''], ['en cero', '0'], ['negativa', '-100'], ['con texto', 'abc']]) {
    test(`no se genera una orden de cuota ${caso}`, async ({ page }) => {
      const inscrito = await estudianteInscrito(page);
      const r = await generarOrdenCuota(page, inscrito, monto);
      expect(r.success).toBe(false);
      expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PENDIENTE'`)).toBe('0');
    });
  }

  test('no se genera una orden de cuota mayor al saldo del programa', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const costo = Number(valor(`SELECT MontoTotalPagar FROM pagoprograma WHERE idInscripcion = ${inscrito.idInscripcion}`));
    const r = await generarOrdenCuota(page, inscrito, String(costo + 5000));
    expect(r.success, 'La orden de cuota supera el saldo y el servidor la aceptó').toBe(false);
  });

  test('no se genera una orden de cuota para una inscripción que no existe', async ({ page }) => {
    const r = await generarOrdenCuota(page, { estudianteID: 999999, idInscripcion: 999999 }, '1000');
    expect(r.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = 999999`)).toBe('0');
  });

  test('no se registra un pago de cuota en cero', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    await generarOrdenCuota(page, inscrito, '1000');
    const r = await pagarCuota(page, inscrito.idInscripcion, '0', 'CUO-' + unico());
    expect(r.estado).toBe('validacion');
    expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion}`)).toBe('0');
  });

  test('el pago de la cuota debe ser por el monto de su orden', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    await generarOrdenCuota(page, inscrito, '3000');
    await pagarCuota(page, inscrito.idInscripcion, '1', 'CUO-' + unico());
    const pagado = valor(`SELECT COALESCE(SUM(montocuota), 0) FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PAGADO'`);
    expect(['0', '0.00', '3000.00'], `Orden de Bs. 3000 confirmada con un voucher de Bs. ${pagado}`).toContain(pagado);
  });

  test('no se genera una orden de clínica en cero', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const r = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), montoTotal: '0',
    });
    expect(r.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CLINICA'`)).toBe('0');
  });

  test('la orden antigua por módulos no se genera vacía ni en cero', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const r = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'registrarOrdenPago', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), listaPagosModulo: '', montoTotal: '0',
    });
    expect(r.success, 'Se creó una orden CONFIRMADA de Bs. 0 sin módulos').toBe(false);
  });
});
