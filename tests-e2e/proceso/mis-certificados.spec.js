// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, PROGRAMA_ID } from './ayudantes-proceso.js';

// "Mis certificados" (cuenta del estudiante): ve sus pagos de certificados (tipo, voucher,
// monto y el último pagado) pero no puede ver ni imprimir el certificado ni el recibo.

const estudianteDePrueba = () => Number(valor("SELECT EstudianteID FROM usuario WHERE Usuario = 'prueba_est'"));

test('el estudiante ve sus pagos de certificados y el último pagado, sin imprimir nada', async ({ page, browser }) => {
  const estudianteID = estudianteDePrueba();
  test.skip(!estudianteID, 'prueba_est no tiene estudiante');

  // Secretaría registra dos certificados del estudiante de prueba
  await ingresar(page, 'adm');
  const voucher1 = '1481' + String(unico()).slice(-4) + '-041';
  const voucher2 = '1482' + String(unico()).slice(-4) + '-042';
  const emitir = (ruta, voucher, extra) => page.request.post(ruta, {
    form: { estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), nombre: 'PRUEBA ESTUDIANTE', vaucher: voucher, fecha: '2026-10-10', ...extra },
  });
  await emitir('tcpdf/pdf/certificado-medio-magnetico-pdf.php', voucher1, {
    tratamiento: 'El Sr.', curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO', monto: '42', coordinador: 'C', director: 'D',
  });
  await emitir('tcpdf/pdf/certificado-revision-pdf.php', voucher2, { tratamiento: 'el señor', curso: 'ESPECIALIDAD EN PRUEBA', monto: '30' });
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE NumeroVaucher = '${voucher2}'`);

  const est = await browser.newPage();
  await ingresar(est, 'est');
  await est.goto('miscertificados');

  // El último pagado arriba, con su voucher; en la tabla, los dos pagos con su monto
  await expect(est.locator('#mc-ultimo')).toContainText('Certificado de Revisión');
  await expect(est.locator('#mc-ultimo')).toContainText(voucher2);
  const tabla = est.locator(`#mis-certificados .mc-programa-grupo[data-programa="${PROGRAMA_ID}"]`);
  await expect(tabla).toContainText(voucher1);
  await expect(tabla).toContainText('Bs. 42,00');
  await expect(tabla).toContainText(voucher2);
  await expect(tabla.locator('tbody tr').first()).toContainText('Último');

  // Solo consulta: ningún botón ni enlace para imprimir, ver el certificado o el recibo
  const mc = est.locator('#mis-certificados');
  await expect(mc.locator('button, a')).toHaveCount(0);
  await expect(mc).not.toContainText('Imprimir');

  // Tampoco por dirección: recibo, datos del documento y PDF están cerrados para el estudiante
  const recibo = await est.request.get(`vistas/componentes/recibo-pago-certificados.php?estudianteID=${estudianteID}&programaID=${PROGRAMA_ID}`);
  expect(await recibo.text()).toContain('Acceso denegado');
  const datos = await est.request.post('ajax/certificado.ajax.php', { form: { accion: 'obtenerDocumento', idDocumento: String(idDocumento) } });
  expect(await datos.text()).not.toContain(voucher2);
  const pdf = await est.request.post('tcpdf/pdf/certificado-revision-pdf.php', {
    form: { estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), nombre: 'X', curso: 'X', fecha: '2026-10-10', idDocumento: String(idDocumento), vistaPrevia: '1' },
  });
  expect(pdf.headers()['content-type'] || '').not.toContain('application/pdf');
  await est.close();
});

test('Mis certificados es solo del estudiante', async ({ page }) => {
  await ingresar(page, 'sec');
  await page.goto('miscertificados');
  await expect(page.locator('#mis-certificados')).toHaveCount(0);
});
