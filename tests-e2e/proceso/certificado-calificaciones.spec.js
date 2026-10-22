// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Certificado de Calificaciones: sale con los módulos que ya tienen nota (no hace falta
// tenerlos todos) y "Monto pagado" llega con el precio del grado, editable.

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

const grado = () => String(valor(`SELECT GradoAcademico FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
const clavePrecio = () => `COSTO_CERT_CALIFICACIONES_${grado()}`;

async function elegirEstudiante(page, estudianteID) {
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await page.goto('certificadocalificaciones');
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#card-modulos')).toBeVisible({ timeout: 15_000 });
}

test('sin notas avisa; con algunas notas genera y el precio se puede cambiar', async ({ page }) => {
  test.setTimeout(60_000);
  sql(`REPLACE INTO configuracion (Clave, Valor) VALUES ('${clavePrecio()}', '150.00')`);
  const { estudianteID } = await estudianteInscrito(page);
  const total = Number(valor(`SELECT COUNT(*) FROM modulos WHERE ProgramaId = ${PROGRAMA_ID}`));

  // Sin ninguna nota: aviso claro y no se puede generar; el precio igual se muestra
  await elegirEstudiante(page, estudianteID);
  await expect(page.locator('#alerta-sin-modulos')).toContainText(`0 de ${total} módulos`);
  await expect(page.locator('#alerta-sin-modulos')).toContainText('No hace falta que tenga todos los módulos');
  await expect(page.locator('#btn-generar-certificado')).toBeDisabled();
  await expect(page.locator('#input-monto')).toHaveValue('150.00');

  // Con solo 2 notas: se puede generar con esos 2 módulos
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro)
       SELECT ${estudianteID}, ${PROGRAMA_ID}, Idmodulo, 85, 'APROBADO', CURDATE()
       FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} ORDER BY Idmodulo LIMIT 2`);
  await elegirEstudiante(page, estudianteID);
  await expect(page.locator('#resumen-modulos')).toContainText(`2 de ${total} módulos con nota`);
  await expect(page.locator('#tabla-modulos-certificado tbody tr')).toHaveCount(2);
  await expect(page.locator('#btn-generar-certificado')).toBeEnabled();

  // El monto llega con el precio pero se puede cambiar
  await expect(page.locator('#input-monto')).toHaveValue('150.00');
  await page.locator('#input-monto').fill('120');
  await page.locator('#input-vaucher_largo').fill('77' + Date.now() % 100000);

  const previa = page.waitForResponse((r) => r.url().includes('certificado-calificaciones-pdf.php'));
  await page.locator('#btn-generar-certificado').click();
  const modal = page.locator('#modalConfirmarCertificado');
  await expect(modal.locator('#certModalDatos')).toContainText('Bs. 120,00');
  await expect(modal.locator('#certModalDatos')).toContainText('2 módulos');
  expect((await previa).headers()['content-type']).toContain('application/pdf');

  // Al generar: el PDF sale en otra pestaña, queda registrado y la pantalla pasa a Registro
  // de documentos con el estudiante abierto y el documento nuevo resaltado
  const [ventana] = await Promise.all([page.waitForEvent('popup'), modal.locator('#certModalGenerar').click()]);
  await expect.poll(() => valor(`SELECT COUNT(*) FROM documentoemitido WHERE TipoDocumento = 'CALIFICACIONES' AND EstudianteID = ${estudianteID}`), { timeout: 15_000 }).toBe('1');
  await ventana.close();
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE TipoDocumento = 'CALIFICACIONES' AND EstudianteID = ${estudianteID}`);
  const fila = page.locator(`#tabla-estudiante-body tr[data-id="${idDocumento}"]`);
  await expect(fila).toHaveClass(/rd-ultimo/, { timeout: 15_000 });
  await expect(fila).toContainText('Certificado de Calificaciones');
  await expect(page).toHaveURL(/registrodocumentos\?ci=.+&inscripcion=/);

  // Al recargar sigue abierto el estudiante y el documento sigue marcado
  await page.reload();
  await expect(fila).toHaveClass(/rd-ultimo/, { timeout: 15_000 });
  await expect(page.locator('#tabla-estudiante-body tr.rd-ultimo')).toHaveCount(1);
});

test('sin precio para el grado avisa dónde definirlo', async ({ page }) => {
  sql(`DELETE FROM configuracion WHERE Clave = '${clavePrecio()}'`);
  const { estudianteID } = await estudianteInscrito(page);
  await elegirEstudiante(page, estudianteID);
  await expect(page.locator('#input-monto')).toHaveValue('');
  await expect(page.locator('#ayuda-monto')).toHaveClass(/cert-ayuda-aviso/);
  await expect(page.locator('#ayuda-monto')).toContainText('Costos de Certificados');
});

test('el documento completo de demostración sale en PDF y no se registra', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro)
       SELECT ${estudianteID}, ${PROGRAMA_ID}, Idmodulo, 90, 'APROBADO', CURDATE()
       FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} ORDER BY Idmodulo LIMIT 3`);
  await elegirEstudiante(page, estudianteID);
  await expect(page.locator('#btn-documento-completo')).toBeEnabled();

  // Sin voucher ni monto: es solo para mostrar
  const pdfPantalla = page.context().waitForEvent('response', (r) => r.url().includes('certificado-calificaciones-pdf.php'));
  const [ventana] = await Promise.all([page.waitForEvent('popup'), page.locator('#btn-documento-completo').click()]);
  expect((await pdfPantalla).headers()['content-type']).toContain('application/pdf');
  await ventana.close();

  // El PDF se genera con el formato completo y no deja registro
  const directo = await page.request.post('tcpdf/pdf/certificado-calificaciones-pdf.php', {
    form: { estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), nombre: 'PRUEBA DEMO', programa: 'PROGRAMA DE PRUEBA',
      nivel: 'MAESTRIA', anio: '2025', vaucher: '', fecha: '2026-10-20', completo: '1' },
  });
  expect(directo.headers()['content-type']).toContain('application/pdf');
  expect((await directo.body()).length).toBeGreaterThan(50_000); // lleva el escudo y las fuentes
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('0');
  // Y el formulario no quedó con el campo de demostración
  expect(await page.locator('#form-generar-certificado input[name="completo"]').count()).toBe(0);
});
