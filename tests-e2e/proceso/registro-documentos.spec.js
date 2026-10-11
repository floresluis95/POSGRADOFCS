// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Registro de documentos: cada certificado guarda los datos con que se generó, se
// puede reimprimir (y corregir) desde ahí, y se marca cuando vuelve aprobado por el
// decano (solo control). Si se corrige un documento aprobado, vuelve a "por aprobar".

test('un documento se aprueba, se reimprime corregido y vuelve a quedar por aprobar', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  const nombre = valor(`SELECT CONCAT(Apaterno, ' ', Amaterno, ' ', Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`);

  // Generar la carta de Medio Magnético (lo que hace su pantalla al imprimir; es el
  // único documento que no exige calificaciones registradas)
  const pdf = await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: String(nombre),
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA', vaucher: 'CERT-001',
      fecha: '2026-10-10', coordinador: 'COORDINADOR DE PRUEBA', director: 'DIRECTOR DE PRUEBA',
    },
  });
  expect(pdf.headers()['content-type']).toContain('application/pdf');
  const condicion = `TipoDocumento = 'MEDIO_MAGNETICO' AND EstudianteID = ${estudianteID}`;
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE ${condicion}`);
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('PENDIENTE');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.vaucher')) FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('CERT-001');

  // En Registro de documentos aparece "Por aprobar" y se marca aprobado por el decano
  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(String(ci));
  await page.locator('#btn-buscar-ci').click();
  const item = page.locator('#contenedor-checklist .item-checklist', { hasText: 'Medio Magnético' });
  await expect(item).toContainText('Por aprobar');
  await item.locator('[data-aprobar]').click();
  await page.locator('.swal2-confirm').click();
  await expect(item).toContainText('Aprobado');
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('APROBADO');

  // Reimprimir corrigiendo el voucher: no crea otro registro y vuelve a "por aprobar"
  await item.locator('[data-reimprimir]').click();
  await expect(page.locator('#ventana-reimprimir')).toBeVisible();
  await expect(page.locator('#reimp-vaucher')).toHaveValue('CERT-001');
  await page.locator('#reimp-vaucher').fill('CERT-002');
  await Promise.all([page.waitForEvent('popup'), page.locator('#form-reimprimir button[type="submit"]').click()]);
  await expect.poll(() => valor(`SELECT NumeroVaucher FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('CERT-002');
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('PENDIENTE');
  await expect(item).toContainText('Por aprobar');

  // Reimprimir sin cambios no cambia nada
  await item.locator('[data-reimprimir]').click();
  await expect(page.locator('#reimp-vaucher')).toHaveValue('CERT-002');
  await Promise.all([page.waitForEvent('popup'), page.locator('#form-reimprimir button[type="submit"]').click()]);
  await page.waitForTimeout(1500);
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
});

test('el Certificado de Revisión se genera desde su pantalla y queda en Registro de documentos', async ({ page }) => {
  await ingresar(page, 'sec');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const nombreCertificado = String(valor(`SELECT UPPER(CONCAT(Nombre, ' ', Apaterno, ' ', Amaterno)) FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  await page.goto('certificadorevision');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  await expect(page.locator('#dato-nombre')).toHaveText(nombreCertificado);
  await expect(page.locator('#dato-curso')).toContainText(' EN ');
  await page.locator('#input-tratamiento').selectOption('el señor');
  await page.locator('#input-fecha').fill('2026-09-15');
  await Promise.all([page.waitForEvent('popup'), page.locator('#btn-generar-certificado').click()]);

  const condicion = `TipoDocumento = 'REVISION' AND EstudianteID = ${estudianteID}`;
  await expect.poll(() => valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.tratamiento')) FROM documentoemitido WHERE ${condicion}`)).toBe('el señor');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.nombre')) FROM documentoemitido WHERE ${condicion}`)).toBe(nombreCertificado);

  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  await expect(page.locator('#contenedor-checklist .item-checklist', { hasText: 'Certificado de Revisión' })).toContainText('Por aprobar');
});
