// @ts-check
import { test, expect } from '@playwright/test';
import {
  valor, unico, ingresar, ajax, registrarEstudiante, preregistrar, ordenPendiente,
  estudianteInscrito, IMAGEN_VOUCHER, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// Fecha de la orden (hoy por defecto, "Modificar fecha" para cambiarla, nunca futura) en
// las órdenes de matrícula, cuota y clínica; y la pestaña Clínicas de Gestión de Pagos:
// firma obligatoria, resumen antes de guardar y foto del voucher en el historial.

const dia = (desplazamiento) => new Date(Date.now() + desplazamiento * 86_400_000).toLocaleDateString('sv-SE');
const hace3 = dia(-3);
const manana = dia(1);

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

test.describe('Fecha de la orden', () => {
  test('matrícula: se guarda la fecha elegida y no se acepta una futura', async ({ page }) => {
    const id1 = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, id1, { fechaInscripcion: hace3 });
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE EstudianteID = ${id1} AND Estado = 'PENDIENTE'`)).toBe(hace3);

    const id2 = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, id2, { fechaInscripcion: manana });
    expect(ordenPendiente(id2)).toBeNull();
  });

  test('cuota: se guarda la fecha elegida y no se acepta una futura', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const datos = {
      accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
      idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: '1000',
    };
    const futura = await ajax(page, 'ordenpago.ajax.php', { ...datos, fechaGeneracion: manana });
    expect(futura.success).toBe(false);

    const pasada = await ajax(page, 'ordenpago.ajax.php', { ...datos, fechaGeneracion: hace3 });
    expect(pasada.success, pasada.mensaje).toBe(true);
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PENDIENTE'`)).toBe(hace3);
  });
});

test.describe('Fecha de la orden junto al voucher', () => {
  test('historial de pagos, recibo y PDF de pago muestran la fecha de la orden', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const fechaMatricula = valor(`SELECT DATE(MIN(FechaGeneracion)) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion}`);

    // Cuota con una orden de hace 3 días, pagada hoy
    const orden = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
      idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: '1000', fechaGeneracion: hace3,
    });
    expect(orden.success, orden.mensaje).toBe(true);
    const pago = await ajax(page, 'pagomodulo.ajax.php', {
      accion: 'registrarPagoModulo', idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID),
      fechaPago: dia(0), montoCuota: '1000', numeroVaucher: unico() + '-03',
    }, { fmodulo: IMAGEN_VOUCHER });
    expect(pago.estado, pago.mensaje).toBe('exitoso');

    // Historial de Pagos (personal)
    const historial = await ajax(page, 'historialpagos.ajax.php', { accion: 'buscarPorEstudianteId', estudianteId: String(inscrito.estudianteID) });
    const programa = historial.programas.find((p) => Number(p.idInscripcion) === inscrito.idInscripcion);
    expect(String(programa.FechaOrdenMatricula).slice(0, 10)).toBe(fechaMatricula);
    expect(String(programa.Cuotas[0].FechaOrden).slice(0, 10)).toBe(hace3);

    // Recibo de pago: columnas F. orden / F. pago
    const recibo = await (await page.request.get(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${inscrito.idInscripcion}`)).text();
    expect(recibo).toContain('F. orden');
    expect(recibo).toContain(hace3.split('-').reverse().join('/'));
    expect(recibo).toContain(fechaMatricula.split('-').reverse().join('/'));
    // Sin clínicas pagadas no aparece la sección
    expect(recibo).not.toContain('Clínicas pagadas');

    // Con una clínica pagada, el recibo la muestra aparte con su total
    const clinica = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), montoTotal: '375', firma: 'FIRMANTE DE PRUEBA',
    });
    expect(clinica.success, clinica.mensaje).toBe(true);
    const voucherClinica = unico() + '-04';
    const pagoClinica = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarPago', idinscripcion: String(inscrito.idInscripcion), fechaPago: dia(0), numeroVoucher: voucherClinica,
    }, { fotoVoucher: IMAGEN_VOUCHER });
    expect(pagoClinica.success, pagoClinica.mensaje).toBe(true);
    const reciboConClinica = await (await page.request.get(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${inscrito.idInscripcion}`)).text();
    expect(reciboConClinica).toContain('Clínicas pagadas (1)');
    expect(reciboConClinica).toContain(voucherClinica);
    expect(reciboConClinica).toContain('Bs. 375.00');

    // PDF de pago completo: este estudiante no tiene pago completo (el PDF solo es para
    // ese caso), pero la consulta con la fecha de la orden debe correr sin error SQL
    const pdf = await (await page.request.get(`extensiones/tcpdf/pdf/pdfpagocompletoprograma.php?idinscripcion=${inscrito.idInscripcion}`)).text();
    expect(pdf).toContain('no tiene pago completo');
  });
});

test.describe('Editar la fecha de la orden en el Historial de Pagos', () => {
  /** Inscrito con una cuota pagada; devuelve también el id de la cuota y su orden. */
  async function conCuotaPagada(page) {
    const inscrito = await estudianteInscrito(page);
    await ajax(page, 'ordenpago.ajax.php', {
      accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
      idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: '1000',
    });
    const voucher = unico() + '-05';
    await ajax(page, 'pagomodulo.ajax.php', {
      accion: 'registrarPagoModulo', idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID),
      fechaPago: dia(0), montoCuota: '1000', numeroVaucher: voucher,
    }, { fmodulo: IMAGEN_VOUCHER });
    const idPagoModulo = valor(`SELECT Idpagomodulo FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PAGADO'`);
    const idOrden = valor(`SELECT IdOrdenPago FROM pagomodulo WHERE Idpagomodulo = ${idPagoModulo}`);
    return { ...inscrito, idPagoModulo, idOrden, voucher };
  }

  test('servidor: corrige la fecha de la orden de la cuota y de la matrícula (con su hora), nunca futura', async ({ page }) => {
    const d = await conCuotaPagada(page);
    const horaAntes = valor(`SELECT TIME(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`);
    const comunes = { numeroVoucher: d.voucher, fechaPago: dia(0), monto: '1000' };

    const futura = await ajax(page, 'historialpagos.ajax.php', { accion: 'actualizarCuota', idPagoModulo: String(d.idPagoModulo), ...comunes, fechaOrden: manana });
    expect(futura.success).toBe(false);

    const ok = await ajax(page, 'historialpagos.ajax.php', { accion: 'actualizarCuota', idPagoModulo: String(d.idPagoModulo), ...comunes, fechaOrden: hace3 });
    expect(ok.success, ok.mensaje).toBe(true);
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`)).toBe(hace3);
    expect(valor(`SELECT TIME(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`)).toBe(horaAntes);

    // Matrícula: su orden (la primera confirmada que no es cuota ni clínica)
    const idOrdenMatricula = valor(`SELECT MIN(IdOrdenPago) FROM ordenpago WHERE idinscripcion = ${d.idInscripcion} AND Estado = 'CONFIRMADO' AND COALESCE(Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')`);
    const matricula = await ajax(page, 'historialpagos.ajax.php', {
      accion: 'actualizarMatricula', idInscripcion: String(d.idInscripcion),
      numeroVoucher: valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${d.idInscripcion}`),
      fechaPago: dia(0), monto: valor(`SELECT costomatricula FROM estudianteprograma WHERE idInscripcion = ${d.idInscripcion}`),
      fechaOrden: dia(-5),
    });
    expect(matricula.success, matricula.mensaje).toBe(true);
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${idOrdenMatricula}`)).toBe(dia(-5));
    // La cuota no se tocó
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`)).toBe(hace3);
  });

  test('pantalla: el modal Editar pago cambia la F. orden de la cuota', async ({ page }) => {
    const d = await conCuotaPagada(page);
    const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${d.estudianteID}`);

    await page.goto('historialpagos');
    const buscar = page.locator('input[type="text"]:visible').first();
    await buscar.fill(ci);
    await buscar.press('Enter');
    const boton = page.locator(`.btnEditarPagoHistorial[data-tipo="cuota"][data-id="${d.idPagoModulo}"]`);
    await expect(boton).toBeVisible({ timeout: 15_000 });
    await boton.click();

    await expect(page.locator('#editPagoFechaOrden')).toBeVisible();
    await expect(page.locator('#editPagoFechaOrden')).toHaveValue(dia(0));
    // Primero la fecha de la orden y después la del pago
    const izquierda = async (sel) => (await page.locator(sel).boundingBox())?.x ?? 0;
    expect(await izquierda('#editPagoFechaOrden')).toBeLessThan(await izquierda('#editPagoFecha'));
    await page.locator('#editPagoFechaOrden').fill(hace3);
    await page.locator('#formEditarPagoHistorial button[type="submit"]').click();

    // Resumen para revisar: la fecha de la orden aparece como "antes → ahora"; aún no se guardó
    const resumen = page.locator('.swal2-popup');
    await expect(resumen).toContainText('Revise los cambios');
    await expect(resumen).toContainText(dia(0).split('-').reverse().join('/'));
    await expect(resumen).toContainText(hace3.split('-').reverse().join('/'));
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`)).toBe(dia(0));
    await resumen.locator('.swal2-confirm').click();

    await expect.poll(() => valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${d.idOrden}`), { timeout: 15_000 }).toBe(hace3);
    // La tabla se refresca con la fecha nueva
    const fila = page.locator('tr').filter({ hasText: d.voucher });
    await expect(fila).toContainText(hace3.split('-').reverse().join('/'), { timeout: 15_000 });
  });
});

test.describe('Clínicas en los historiales', () => {
  test('el pago de clínica aparece en el Historial de Pagos y su foto solo la ve su dueño', async ({ page, browser }) => {
    const inscrito = await estudianteInscrito(page);
    const orden = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
      programaID: String(PROGRAMA_ID), montoTotal: '350', firma: 'FIRMANTE DE PRUEBA', fechaGeneracion: hace3,
    });
    expect(orden.success, orden.mensaje).toBe(true);
    const voucher = unico() + '-06';
    const pago = await ajax(page, 'pagoclinica.ajax.php', {
      accion: 'registrarPago', idinscripcion: String(inscrito.idInscripcion), fechaPago: dia(0), numeroVoucher: voucher,
    }, { fotoVoucher: IMAGEN_VOUCHER });
    expect(pago.success, pago.mensaje).toBe(true);

    // Historial de Pagos (personal): sección de clínicas con fecha de orden y voucher
    const historial = await ajax(page, 'historialpagos.ajax.php', { accion: 'buscarPorEstudianteId', estudianteId: String(inscrito.estudianteID) });
    const programa = historial.programas.find((p) => Number(p.idInscripcion) === inscrito.idInscripcion);
    expect(programa.Clinicas).toHaveLength(1);
    expect(programa.Clinicas[0].NumeroVoucher).toBe(voucher);
    expect(String(programa.Clinicas[0].FechaGeneracion).slice(0, 10)).toBe(hace3);

    // Foto: el personal la ve
    const idPagoClinica = programa.Clinicas[0].IdPagoClinica;
    const foto = await page.request.get(`ajax/obtener-voucher-clinica.php?id=${idPagoClinica}`);
    expect(foto.status()).toBe(200);
    expect(foto.headers()['content-type']).toMatch(/^image\//);

    // Sin sesión u otro estudiante (prueba_est): acceso denegado
    const sinSesion = await (await browser.newContext()).request.get(`ajax/obtener-voucher-clinica.php?id=${idPagoClinica}`);
    expect(sinSesion.status()).toBe(403);
    const otro = await browser.newPage();
    await ingresar(otro, 'est');
    expect((await otro.request.get(`ajax/obtener-voucher-clinica.php?id=${idPagoClinica}`)).status()).toBe(403);
    await otro.close();
  });
});

test.describe('Clínicas (Gestión de Pagos)', () => {
  const ordenClinica = (inscrito, cambios = {}) => ({
    accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: '500', firma: 'FIRMANTE DE PRUEBA', ...cambios,
  });
  const contarClinicas = (idInscripcion) => valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${idInscripcion} AND Observaciones = 'CLINICA'`);

  test('servidor: sin firma o con fecha futura no se genera; con fecha pasada se guarda', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);

    const sinFirma = await ajax(page, 'pagoclinica.ajax.php', ordenClinica(inscrito, { firma: '' }));
    expect(sinFirma.success).toBe(false);
    const futura = await ajax(page, 'pagoclinica.ajax.php', ordenClinica(inscrito, { fechaGeneracion: manana }));
    expect(futura.success).toBe(false);
    expect(contarClinicas(inscrito.idInscripcion)).toBe('0');

    const ok = await ajax(page, 'pagoclinica.ajax.php', ordenClinica(inscrito, { fechaGeneracion: hace3 }));
    expect(ok.success, ok.mensaje).toBe(true);
    expect(valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE IdOrdenPago = ${ok.idOrdenPago}`)).toBe(hace3);
  });

  test('pantalla: firma obligatoria, resumen antes de guardar y foto en el historial', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    // La orden se imprime en una pestaña nueva: no hace falta revisarla aquí
    page.context().on('page', (nueva) => nueva.close().catch(() => {}));
    // Al menos un nombre en el catálogo de firmantes
    await ajax(page, 'ordenpago.ajax.php', { accion: 'registrarFirmante', nombre: 'FIRMANTE CLINICA PRUEBA' });

    await page.goto('matriculados');
    await page.evaluate(() => { try { localStorage.removeItem('opUltimaFirma'); } catch (e) {} });
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');
    await modal.locator('#tab-clinica-link').click();
    await expect(modal.locator('#clMonto')).toBeVisible({ timeout: 15_000 });

    // Fecha de la orden: hoy y bloqueada hasta marcar la casilla
    await expect(modal.locator('#clFechaOrden')).toHaveAttribute('readonly', '');
    await expect(modal.locator('#clFechaOrden')).toHaveValue(dia(0));

    // Sin firma no deja seguir
    await modal.locator('#clMonto').fill('450');
    await expect(modal.locator('#clFirma option')).not.toHaveCount(1, { timeout: 10_000 });
    await modal.locator('#clBtnGenerarOrden').click();
    await expect(page.locator('.swal2-popup')).toContainText('responsable de la firma');
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    expect(contarClinicas(inscrito.idInscripcion)).toBe('0');

    // Con firma y fecha cambiada: resumen y recién al confirmar se guarda
    await modal.locator('#clFirma').selectOption({ index: 1 });
    await modal.locator('label[for="clChkModificarFecha"]').click();
    await expect(modal.locator('#clFechaOrden')).not.toHaveAttribute('readonly', '');
    await modal.locator('#clFechaOrden').fill(hace3);
    await modal.locator('#clBtnGenerarOrden').click();
    const resumen = page.locator('.swal2-popup.vc-confirmar');
    await expect(resumen).toContainText('Bs. 450.00');
    await expect(resumen).toContainText(hace3.split('-').reverse().join('/'));
    await resumen.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT DATE(FechaGeneracion) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CLINICA' AND Estado = 'PENDIENTE'`), { timeout: 15_000 })
      .toBe(hace3);

    // Voucher: miniatura, resumen con la fecha del depósito y guardado
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    await expect(modal.locator('#clBtnGuardarPago')).toBeVisible({ timeout: 15_000 });
    const voucher = unico() + '-07';
    await modal.locator('#clNumeroVoucher_largo').fill(voucher);
    await modal.locator('#clFotoVoucher').setInputFiles(IMAGEN_VOUCHER);
    await page.locator('#capturaRecorteSinCambios').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
    await expect(modal.locator('#clPreviewFoto img')).toHaveCount(1);
    await modal.locator('#clBtnGuardarPago').click();
    const confirmar = page.locator('.swal2-popup.vc-confirmar');
    await expect(confirmar).toContainText(voucher);
    await confirmar.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT NumeroVoucher FROM pagoclinica WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PAGADO'`), { timeout: 15_000 })
      .toBe(voucher);

    // Historial: la foto se ve en grande con su N°, igual que en cuotas
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    const verFoto = modal.locator('#tablaClinicasBody .cl-ver-foto');
    await expect(verFoto).toHaveCount(1, { timeout: 15_000 });
    await verFoto.click();
    await expect(page.locator('#modalFotoVoucher')).toBeVisible();
    await expect(page.locator('#numeroVoucherGrande')).toContainText(voucher);
  });
});
