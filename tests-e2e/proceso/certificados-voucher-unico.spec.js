// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// El N° de voucher de un certificado sigue la regla de todo el sistema: no puede estar
// ya usado en otro certificado ni en una matrícula, cuota o clínica. Al reimprimir un
// documento su propio voucher no cuenta como repetido.

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

/** Envía la carta de Medio Magnético (no exige calificaciones) y devuelve la respuesta. */
async function medioMagnetico(page, estudianteID, voucher, extra = {}) {
  const nombre = valor(`SELECT CONCAT(Apaterno, ' ', Amaterno, ' ', Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  return page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: String(nombre),
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA', vaucher: voucher,
      monto: '85', fecha: '2026-10-21', coordinador: 'COORDINADOR DE PRUEBA', director: 'DIRECTOR DE PRUEBA', ...extra,
    },
  });
}

test('un voucher ya usado en otro certificado no se acepta; la reimpresión del mismo sí', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const voucher = '1471' + String(unico()).slice(-4) + '-031';

  const primero = await medioMagnetico(page, estudianteID, voucher);
  expect(primero.headers()['content-type']).toContain('application/pdf');
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE NumeroVaucher = '${voucher}'`);

  // Otro certificado con el mismo voucher (también en minúsculas o con espacios): rechazado
  const repetido = await medioMagnetico(page, estudianteID, ' ' + voucher + ' ');
  expect(await repetido.text()).toContain('ya fue registrado en un certificado de');
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE NumeroVaucher = '${voucher}'`)).toBe('1');

  // La vista previa también avisa
  const previa = await medioMagnetico(page, estudianteID, voucher, { vistaPrevia: '1' });
  expect(await previa.text()).toContain('ya fue registrado');

  // Reimprimir el mismo documento con su voucher: se acepta y no crea otro registro
  const reimpresion = await medioMagnetico(page, estudianteID, voucher, { idDocumento: String(idDocumento), titulo: 'TÍTULO CORREGIDO' });
  expect(reimpresion.headers()['content-type']).toContain('application/pdf');
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE NumeroVaucher = '${voucher}'`)).toBe('1');

  // La consulta que usa la pantalla antes de la vista previa
  const consulta = await ajax(page, 'certificado.ajax.php', { accion: 'verificarVoucher', vaucher: voucher.toLowerCase() });
  expect(consulta.repetido).toBe(true);
  expect(consulta.mensaje).toContain('un certificado de');
  const propia = await ajax(page, 'certificado.ajax.php', { accion: 'verificarVoucher', vaucher: voucher, idDocumento: String(idDocumento) });
  expect(propia.repetido).toBe(false);
  const libre = await ajax(page, 'certificado.ajax.php', { accion: 'verificarVoucher', vaucher: voucher + '9' });
  expect(libre.repetido).toBe(false);
});

test('el voucher de una matrícula no sirve para un certificado', async ({ page }) => {
  const { estudianteID, idInscripcion } = await estudianteInscrito(page);
  const voucherMatricula = String(valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${idInscripcion}`));

  const certificado = await medioMagnetico(page, estudianteID, voucherMatricula);
  expect(await certificado.text()).toContain('ya fue registrado en la matrícula de');
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('0');
});

test('en pantalla: al generar con un voucher ya usado aparece el aviso y no se abre la vista previa', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const largo = '1473' + String(unico()).slice(-4);
  await medioMagnetico(page, estudianteID, largo + '-033');

  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await page.goto('certificadomediomagnetico');
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#card-carta')).toBeVisible({ timeout: 15_000 });
  await page.locator('#input-titulo').fill('título de prueba');
  await page.locator('#input-vaucher_largo').fill(largo);
  await page.locator('#input-vaucher_corto').fill('033');
  await page.locator('#input-monto').fill('85');
  await page.locator('#btn-generar-certificado').click();

  await expect(page.locator('.swal2-popup')).toContainText('Voucher ya registrado');
  await expect(page.locator('#modalConfirmarCertificado')).not.toBeVisible();
});
