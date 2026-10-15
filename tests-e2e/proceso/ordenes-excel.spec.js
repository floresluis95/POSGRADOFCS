// @ts-check
import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import { valor, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Órdenes de Pago: N° correlativo, tipo diferenciado (matrícula / cuota N° x de y /
// clínica) y exportación a Excel (.xlsx real, con montos como números).

test('la tabla numera las órdenes, distingue el tipo y exporta a Excel', async ({ page }) => {
  await ingresar(page, 'adm');
  const inscrito = await estudianteInscrito(page);
  const cuota = await ajax(page, 'ordenpago.ajax.php', {
    accion: 'registrarOrdenPagoCuotaPrograma', estudianteID: String(inscrito.estudianteID),
    idinscripcion: String(inscrito.idInscripcion), programaID: String(PROGRAMA_ID), montoTotal: '1250.5',
  });
  expect(cuota.success, cuota.mensaje).toBe(true);
  const clinica = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: '300', firma: 'FIRMANTE DE PRUEBA',
  });
  expect(clinica.success, clinica.mensaje).toBe(true);

  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);
  await page.goto('ordenespago?ci=' + ci);
  const filas = page.locator('#op_cuerpo tr');
  await expect(filas).toHaveCount(3, { timeout: 15_000 }); // matrícula, cuota y clínica

  // N° correlativo 1, 2, 3
  expect(await page.locator('#op_cuerpo td.op-correlativo').allTextContents()).toEqual(['1', '2', '3']);
  // Cada tipo con su insignia; la cuota con su N° del plan
  await expect(page.locator('#op_cuerpo .op-tipo-matricula')).toHaveCount(1);
  await expect(page.locator('#op_cuerpo .op-tipo-clinica')).toHaveCount(1);
  const filaCuota = page.locator('#op_cuerpo tr', { has: page.locator('.op-tipo-cuota') });
  await expect(filaCuota).toContainText('N° 1 de 10');

  // Excel: se descarga un .xlsx con una fila por orden, montos numéricos y total
  const [descarga] = await Promise.all([page.waitForEvent('download'), page.locator('#op_exportar').click()]);
  expect(descarga.suggestedFilename()).toMatch(/^ordenes-de-pago-\d{8}\.xlsx$/);
  const ruta = await descarga.path();
  const base64 = fs.readFileSync(String(ruta)).toString('base64');
  const hoja = await page.evaluate(async (b64) => {
    const zip = await window.JSZip.loadAsync(b64, { base64: true });
    return zip.file('xl/worksheets/sheet1.xml').async('string');
  }, base64);
  expect(hoja).toContain('Cuota 1 de 10');
  expect(hoja).toContain('Clínica');
  expect(hoja).toContain(cuota.numeroOrden);
  expect(hoja).toContain('<v>1250.5</v>'); // monto como número, no texto
  expect(hoja).toContain('Total (sin anuladas)');
});

test('sin escribir un estudiante, "Buscar" lista con los filtros (tipo, estado, programa)', async ({ page }) => {
  await ingresar(page, 'adm');
  const inscrito = await estudianteInscrito(page);
  const clinica = await ajax(page, 'pagoclinica.ajax.php', {
    accion: 'registrarOrden', estudianteID: String(inscrito.estudianteID), idinscripcion: String(inscrito.idInscripcion),
    programaID: String(PROGRAMA_ID), montoTotal: '300', firma: 'FIRMANTE DE PRUEBA',
  });
  expect(clinica.success, clinica.mensaje).toBe(true);

  await page.goto('ordenespago');
  // Al entrar no lista nada: pide buscar un estudiante o elegir filtros
  await expect(page.locator('#op_cuerpo .op-vacio')).toContainText('pulse Buscar', { timeout: 15_000 });

  // "Buscar" sin filtros: todas las órdenes del periodo
  await page.locator('#op_btn_buscar').click();
  const total = Number(valor('SELECT COUNT(*) FROM ordenpago'));
  await expect(page.locator('#op_mostrando')).toContainText(`de ${total} órdenes`);

  // Solo clínicas: cada fila es de clínica y está la generada
  await page.locator('#op_tipo').selectOption('CLINICA');
  const clinicas = Number(valor(`SELECT COUNT(*) FROM ordenpago WHERE Observaciones = 'CLINICA'`));
  await expect(page.locator('#op_mostrando')).toContainText(`de ${clinicas} órdenes`);
  await expect(page.locator('#op_cuerpo tr', { hasText: clinica.numeroOrden })).toHaveCount(1);
  expect(await page.locator('#op_cuerpo .op-tipo').allTextContents()).toEqual(Array(Math.min(clinicas, 50)).fill('Clínica'));

  // Matrículas de un programa, solo pagadas
  await page.locator('#op_tipo').selectOption('MATRICULA');
  await page.locator('#op_programa').selectOption(String(PROGRAMA_ID));
  await page.locator('#op_estado').selectOption('PAGADA');
  const matriculasPagadas = Number(valor(`SELECT COUNT(*) FROM ordenpago WHERE ProgramaID = ${PROGRAMA_ID} AND Estado = 'CONFIRMADO' AND ListaModulos IS NULL AND COALESCE(Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')`));
  await expect(page.locator('#op_mostrando')).toContainText(`de ${matriculasPagadas} órdenes`);

  // Limpiar vuelve al estado inicial (no lista nada)
  await page.locator('#op_limpiar').click();
  await expect(page.locator('#op_cuerpo .op-vacio')).toContainText('pulse Buscar');
});
