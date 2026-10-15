// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, estudianteInscrito, IMAGEN_VOUCHER, PROGRAMA_ID } from './ayudantes-proceso.js';

// N° de voucher en dos partes (voucher-partes.js): largo - corto. El guion lo pone el
// sistema; la parte corta es opcional; en la base se guarda en un solo campo.

const corto = () => String(Math.floor(Math.random() * 900) + 100);

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

/** Abre Gestión de Pagos > Cuotas con una orden de cuota pendiente. */
async function abrirCuotaPendiente(page) {
  const inscrito = await estudianteInscrito(page);
  const orden = await ajax(page, 'ordenpago.ajax.php', {
    accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
    idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: '1000',
  });
  expect(orden.success, orden.mensaje).toBe(true);
  await page.goto('matriculados');
  await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
  const modal = page.locator('#modalInscribirModulo');
  await modal.locator('#tab-pago-modulos-link').click();
  await expect(modal.locator('#btnGuardarPagoCuota')).toBeEnabled({ timeout: 15_000 });
  return { inscrito, modal };
}

test('el guion se pone solo, la parte corta es opcional y se puede pegar el número completo', async ({ page }) => {
  const { modal } = await abrirCuotaPendiente(page);
  const original = modal.locator('#numeroVaucher');
  const largo = modal.locator('#numeroVaucher_largo');
  const parteCorta = modal.locator('#numeroVaucher_corto');

  // El original queda oculto; se ven las dos partes
  await expect(original).toHaveAttribute('type', 'hidden');
  await expect(largo).toBeVisible();
  await expect(parteCorta).toBeVisible();

  // Solo la parte larga: sin guion
  await largo.fill('1469337');
  await expect(original).toHaveValue('1469337');

  // Con la corta: el sistema pone el guion
  await parteCorta.fill('002');
  await expect(original).toHaveValue('1469337-002');
  await expect(modal.locator('.vp-se-guarda')).toContainText('1469337-002');

  // Escribir el guion en la parte larga salta a la corta
  await largo.fill('');
  await parteCorta.fill('');
  await largo.pressSequentially('1469338-');
  await expect(parteCorta).toBeFocused();
  await page.keyboard.type('005');
  await expect(original).toHaveValue('1469338-005');

  // Pegar el número completo en la parte larga lo reparte
  await largo.fill('1470000-010');
  await expect(largo).toHaveValue('1470000');
  await expect(parteCorta).toHaveValue('010');
  await expect(original).toHaveValue('1470000-010');
});

test('el N° de voucher solo acepta números (y el guion): pantalla y servidor', async ({ page }) => {
  const { inscrito, modal } = await abrirCuotaPendiente(page);

  // Pantalla: las letras y otros caracteres se quitan y se avisa
  await modal.locator('#numeroVaucher_largo').fill('14A69.337');
  await expect(modal.locator('#numeroVaucher_largo')).toHaveValue('1469337');
  await expect(modal.locator('.vp-aviso')).toBeVisible();
  await modal.locator('#numeroVaucher_corto').fill('0x2');
  await expect(modal.locator('#numeroVaucher')).toHaveValue('1469337-02');

  // Servidor: cuota, clínica y matrícula rechazan un voucher con letras
  const cuota = await ajax(page, 'pagomodulo.ajax.php', {
    accion: 'registrarPagoModulo', idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID),
    fechaPago: new Date().toLocaleDateString('sv-SE'), montoCuota: '1000', numeroVaucher: 'ABC-123',
  }, { fmodulo: IMAGEN_VOUCHER });
  expect(cuota.estado).toBe('validacion');
  expect(cuota.mensaje).toContain('solo puede tener números');
  expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion}`)).toBe('0');

  const otro = await estudianteInscrito(page);
  const clinica = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarOrden', estudianteID: String(otro.estudianteID), idinscripcion: String(otro.idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: '200', firma: 'FIRMANTE DE PRUEBA',
  });
  expect(clinica.success, clinica.mensaje).toBe(true);
  const pagoClinica = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarPago', idinscripcion: String(otro.idInscripcion), fechaPago: new Date().toLocaleDateString('sv-SE'), numeroVoucher: '12 34',
  }, { fotoVoucher: IMAGEN_VOUCHER });
  expect(pagoClinica.success).toBe(false);
  expect(pagoClinica.mensaje).toContain('solo puede tener números');

  // Formatos válidos: solo la parte larga, o larga-corta
  const valido = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarPago', idinscripcion: String(otro.idInscripcion), fechaPago: new Date().toLocaleDateString('sv-SE'), numeroVoucher: unico(),
  }, { fotoVoucher: IMAGEN_VOUCHER });
  expect(valido.success, valido.mensaje).toBe(true);
});

test('el resumen muestra el voucher unido y se guarda en un solo campo', async ({ page }) => {
  const { inscrito, modal } = await abrirCuotaPendiente(page);
  const largo = String(Date.now()).slice(-7);
  const c = corto();

  await modal.locator('#numeroVaucher_largo').fill(largo);
  await modal.locator('#numeroVaucher_corto').fill(c);
  await modal.locator('#fmodulo').setInputFiles(IMAGEN_VOUCHER);
  await page.locator('#capturaRecorteSinCambios').click();
  await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
  await modal.locator('#btnGuardarPagoCuota').click();

  const resumen = page.locator('.swal2-popup.vc-confirmar');
  await expect(resumen).toContainText(`${largo}-${c}`);
  await resumen.locator('.swal2-confirm').click();
  await expect.poll(() => valor(`SELECT nvaucher FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PAGADO'`), { timeout: 15_000 })
    .toBe(`${largo}-${c}`);

  // Tras guardar, los dos campos quedan vacíos para la siguiente cuota
  await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
  await expect(modal.locator('#numeroVaucher_largo')).toHaveValue('');
  await expect(modal.locator('#numeroVaucher_corto')).toHaveValue('');
});

test('al editar en el Historial de Pagos el voucher guardado se reparte en las dos partes', async ({ page }) => {
  const inscrito = await estudianteInscrito(page);
  const voucher = valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${inscrito.idInscripcion}`);
  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);

  await page.goto('historialpagos');
  const buscar = page.locator('input[type="text"]:visible').first();
  await buscar.fill(ci);
  await buscar.press('Enter');
  const boton = page.locator(`.btnEditarPagoHistorial[data-tipo="matricula"][data-id="${inscrito.idInscripcion}"]`);
  await expect(boton).toBeVisible({ timeout: 15_000 });
  await boton.click();

  // "MAT-123": largo MAT, corto 123
  const [parteLarga, parteCorta] = String(voucher).split('-');
  await expect(page.locator('#editPagoVoucher_largo')).toHaveValue(parteLarga);
  await expect(page.locator('#editPagoVoucher_corto')).toHaveValue(parteCorta || '');
});
