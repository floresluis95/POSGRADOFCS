// @ts-check
import { test, expect } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { sql, valor, ingresar, ajax, unico } from './ayudantes-proceso.js';

// Carga masiva desde Excel: se descarga la plantilla de un programa con módulos, se llena
// (en el navegador, con JSZip) y se sube. Primero se revisa sin guardar; luego se registran
// solo las filas correctas. Una segunda carga del mismo Excel no duplica nada.

const SQL_PROGRAMA = `SELECT p.ProgramaID FROM programa p WHERE (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID) > 0
  ORDER BY (SELECT COUNT(*) FROM estudianteprograma ep WHERE ep.ProgramaID = p.ProgramaID), p.ProgramaID LIMIT 1`;

/** Llena la hoja Estudiantes de la plantilla (en el navegador) y devuelve el .xlsx */
async function llenarPlantilla(page, rutaPlantilla, filas) {
  const base64 = readFileSync(rutaPlantilla).toString('base64');
  const resultado = await page.evaluate(async ({ base64, filas }) => {
    const zip = await JSZip.loadAsync(base64, { base64: true });
    let hoja = await zip.file('xl/worksheets/sheet1.xml').async('string');
    const sistema = await zip.file('xl/worksheets/sheet4.xml').async('string');
    // Encabezado → clave, y clave → columna (según la fila 2 de la hoja)
    const claves = {};
    for (const m of sistema.matchAll(/<row r="\d+"><c r="A\d+"[^>]*><is><t[^>]*>([^<]*)<\/t><\/is><\/c><c r="B\d+"[^>]*><is><t[^>]*>([^<]*)<\/t><\/is><\/c><\/row>/g)) claves[m[1]] = m[2];
    const columnas = {};
    for (const m of hoja.matchAll(/<c r="([A-Z]+)2"[^>]*><is><t[^>]*>([^<]*)<\/t>/g)) columnas[claves[m[2].replace(/&amp;/g, '&')]] = m[1];
    const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;');
    let xmlFilas = '';
    filas.forEach((fila, i) => {
      const r = i + 3;
      const celdas = Object.entries(fila).map(([clave, v]) => {
        const col = columnas[clave];
        if (!col) throw new Error('columna desconocida ' + clave);
        return typeof v === 'number'
          ? `<c r="${col}${r}"><v>${v}</v></c>`
          : `<c r="${col}${r}" t="inlineStr"><is><t>${esc(v)}</t></is></c>`;
      });
      // Las celdas de una fila deben ir en orden de columna
      celdas.sort((a, b) => {
        const ca = /r="([A-Z]+)/.exec(a)[1], cb = /r="([A-Z]+)/.exec(b)[1];
        return ca.length - cb.length || ca.localeCompare(cb);
      });
      xmlFilas += `<row r="${r}">${celdas.join('')}</row>`;
    });
    hoja = hoja.replace('</sheetData>', xmlFilas + '</sheetData>');
    zip.file('xl/worksheets/sheet1.xml', hoja);
    return { xlsx: await zip.generateAsync({ type: 'base64' }), columnas };
  }, { base64, filas });
  return { buffer: Buffer.from(resultado.xlsx, 'base64'), columnas: resultado.columnas };
}

test('plantilla, revisión y registro de estudiantes, notas y trabajo de grado', async ({ page }) => {
  test.setTimeout(120_000);
  const programaID = String(valor(SQL_PROGRAMA));
  const modulos = sql(`SELECT Idmodulo FROM modulos WHERE ProgramaId = ${programaID} ORDER BY Idmodulo`).map((f) => f[0]);
  const profesion = String(valor('SELECT NombreProfesion FROM profesion ORDER BY IdProfesion LIMIT 1'));
  const ci = unico();
  const existente = sql(`SELECT e.Ci FROM estudiante e WHERE e.Apaterno <> '' LIMIT 1`)[0][0];

  await ingresar(page, 'adm');
  await page.goto('cargamasiva');
  await page.locator('#cm-programa').selectOption(programaID);
  await expect(page.locator('#cm-info-programa')).toContainText(`${modulos.length} módulos`);
  const [descarga] = await Promise.all([page.waitForEvent('download'), page.locator('#cm-descargar').click()]);
  expect(descarga.suggestedFilename()).toMatch(/^carga-masiva-.*\.xlsx$/);
  const rutaPlantilla = await descarga.path();

  // La plantilla se puede leer de vuelta y trae una columna de nota por módulo
  const notas = Object.fromEntries(modulos.map((id, i) => [`nota:${id}`, i === 0 ? 100 : 80 + (i % 15)]));
  const correcta = {
    ci, exp: 'OR', nombre: 'Prueba Carga', apaterno: 'Masiva', amaterno: 'Excel', profesion: profesion.toLowerCase(),
    fechaNacimiento: 32874 /* 01/01/1990 como fecha de Excel */, fechaInscripcion: '15/03/2024', celular: '71234567',
    ...notas,
    titulo: 'evaluación de la carga masiva', fechaAprobacion: '20/11/2025', calificacionTesis: '95,5',
    presidente: 'Dr. Uno', tribunal1: 'Dr. Dos',
  };
  const filas = [
    correcta,
    { ci: existente, exp: 'LP', nombre: 'OTRA', apaterno: 'PERSONA DISTINTA', profesion, fechaInscripcion: '01/01/2024' },
    { ci: unico(), exp: 'LP', nombre: 'FECHA', apaterno: 'MALA', profesion, fechaInscripcion: '31/02/2024' },
    { ci, exp: 'OR', nombre: 'REPETIDO', apaterno: 'MASIVA', profesion, fechaInscripcion: '15/03/2024' },
  ];
  const { buffer, columnas } = await llenarPlantilla(page, rutaPlantilla, filas);
  for (const id of modulos) expect(columnas[`nota:${id}`], `columna del módulo ${id}`).toBeTruthy();

  // Revisar: no guarda nada
  await page.locator('#cm-archivo').setInputFiles({ name: 'carga.xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', buffer });
  await page.locator('#cm-revisar').click();
  await expect(page.locator('#cm-resultado .cm-tabla tbody tr')).toHaveCount(4, { timeout: 20_000 });
  await expect(page.locator('#cm-resultado .cm-cifra.ok b')).toHaveText('1');
  await expect(page.locator('#cm-resultado .cm-cifra.error b')).toHaveText('3');
  const tabla = page.locator('#cm-resultado .cm-tabla');
  await expect(tabla).toContainText('ya está registrado a nombre de');
  await expect(tabla).toContainText('no es una fecha válida');
  await expect(tabla).toContainText('repetido en el Excel (fila 3)');
  expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = ${ci}`)).toBe('0');

  // Registrar
  await page.locator('#cm-registrar').click();
  await page.getByRole('button', { name: 'Sí, registrar' }).click();
  await expect(page.locator('#cm-resultado .cm-badge.ok')).toHaveCount(1, { timeout: 30_000 });

  const estudianteID = String(valor(`SELECT EstudianteID FROM estudiante WHERE Ci = ${ci}`));
  expect(valor(`SELECT CONCAT_WS('|', Nombre, Apaterno, Amaterno, Exp, FechaNacimiento, Celular) FROM estudiante WHERE EstudianteID = ${estudianteID}`))
    .toBe('PRUEBA CARGA|MASIVA|EXCEL|OR|1990-01-01|71234567');
  expect(valor(`SELECT CONCAT_WS('|', Estado, nvauchermatricula, pagoCompleto, FechaInscripcion) FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${programaID}`))
    .toBe('ACTIVO|CARGA HISTÓRICA|1|2024-03-15');
  expect(valor(`SELECT COUNT(*) FROM calificacion WHERE EstudianteID = ${estudianteID} AND ProgramaId = ${programaID}`)).toBe(String(modulos.length));
  expect(valor(`SELECT Nota FROM calificacion WHERE EstudianteID = ${estudianteID} AND Idmodulo = ${modulos[0]}`)).toBe('100.00');
  expect(valor(`SELECT CONCAT_WS('|', TituloTrabajo, CalificacionNumeral, FechaAprobacion, Tribunal) FROM trabajogrado WHERE EstudianteID = ${estudianteID}`))
    .toBe('EVALUACIÓN DE LA CARGA MASIVA|95.50|2025-11-20|PRESIDENTE: Dr. Uno|TRIBUNAL: Dr. Dos');

  // Ya aparece en el buscador de los certificados
  const busqueda = await ajax(page, 'certificado.ajax.php', { accion: 'buscarPorCI', ci });
  expect(busqueda.resultados.length).toBe(1);

  // El mismo Excel otra vez: no duplica; avisa que ya estaba
  await page.locator('#cm-archivo').setInputFiles({ name: 'carga.xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', buffer });
  await page.locator('#cm-revisar').click();
  const primera = page.locator('#cm-resultado .cm-tabla tbody tr').first();
  await expect(primera).toContainText('Ya estaba inscrito', { timeout: 20_000 });
  await expect(primera).toContainText('se conserva la del sistema');
  await expect(primera).toContainText('No hay nada nuevo');

  // Matriculados del programa abre sin errores con la inscripción histórica
  await page.goto('matriculados');
  await expect(page.locator('body')).not.toContainText(/Fatal error|Warning:|Notice:|Uncaught/);

  // Limpieza
  sql(`DELETE FROM calificacion WHERE EstudianteID = ${estudianteID};
       DELETE FROM trabajogrado WHERE EstudianteID = ${estudianteID};
       DELETE FROM estudianteprograma WHERE EstudianteID = ${estudianteID};
       DELETE FROM estudiante WHERE EstudianteID = ${estudianteID};`);
});

test('pagos del Excel: plan, matrícula y cuotas para que el saldo y los reportes salgan bien', async ({ page }) => {
  test.setTimeout(120_000);
  const programaID = String(valor(SQL_PROGRAMA));
  const costo = Number(valor(`SELECT Costo FROM programa WHERE ProgramaID = ${programaID}`));
  const profesion = String(valor('SELECT NombreProfesion FROM profesion ORDER BY IdProfesion LIMIT 1'));
  const ciPagos = unico();
  const ciSinPagos = unico();
  const voucherCuota = unico();
  const voucherUsado = String(valor(`SELECT nvaucher FROM pagomodulo WHERE nvaucher REGEXP '^[0-9]+(-[0-9]+)?$' LIMIT 1`) || '');
  const total = Math.round(costo * 0.9 * 100) / 100; // con 10 % de descuento
  const cuota1 = Math.round(total / 2 * 100) / 100;

  await ingresar(page, 'adm');
  await page.goto('cargamasiva');
  await page.locator('#cm-programa').selectOption(programaID);
  const [descarga] = await Promise.all([page.waitForEvent('download'), page.locator('#cm-descargar').click()]);
  const rutaPlantilla = await descarga.path();

  const base = { exp: 'LP', apaterno: 'PAGOS', profesion, fechaInscripcion: '01/02/2024' };
  const filas = [
    { ...base, ci: ciPagos, nombre: 'CON CUOTAS', 'pago:total': total, 'pago:matricula': 500,
      'pago:cuota1monto': cuota1, 'pago:cuota1fecha': '10/02/2024', 'pago:cuota1voucher': voucherCuota,
      'pago:cuota2monto': '1.000,50' },
    { ...base, ci: ciSinPagos, nombre: 'SIN PAGOS' },
    { ...base, ci: unico(), nombre: 'SE PASA', 'pago:total': 100, 'pago:cuota1monto': 150 },
    { ...base, ci: unico(), nombre: 'VOUCHER REPETIDO', 'pago:cuota1monto': 10, 'pago:cuota1voucher': voucherCuota },
  ];
  if (voucherUsado) filas.push({ ...base, ci: unico(), nombre: 'VOUCHER USADO', 'pago:cuota1monto': 10, 'pago:cuota1voucher': voucherUsado });
  const { buffer, columnas } = await llenarPlantilla(page, rutaPlantilla, filas);
  expect(columnas['pago:cuota1monto']).toBeTruthy();

  const subir = async () => {
    await page.locator('#cm-archivo').setInputFiles({ name: 'pagos.xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', buffer });
    await page.locator('#cm-revisar').click();
    await expect(page.locator('#cm-resultado .cm-tabla tbody tr')).toHaveCount(filas.length, { timeout: 20_000 });
  };
  await subir();
  const tabla = page.locator('#cm-resultado .cm-tabla');
  await expect(tabla).toContainText('pasan el total a pagar');
  await expect(tabla).toContainText('repetido en el Excel (fila 3)');
  if (voucherUsado) await expect(tabla).toContainText('ya fue registrado en');
  await expect(tabla).toContainText('Sin pagos: en los reportes figurará con saldo');
  await expect(page.locator('#cm-resultado .cm-cifra.ok b')).toHaveText('2');

  await page.locator('#cm-registrar').click();
  await page.getByRole('button', { name: 'Sí, registrar' }).click();
  await expect(page.locator('#cm-resultado .cm-badge.ok')).toHaveCount(2, { timeout: 30_000 });

  const insc = String(valor(`SELECT ep.idInscripcion FROM estudianteprograma ep JOIN estudiante e ON e.EstudianteID = ep.EstudianteID WHERE e.Ci = ${ciPagos}`));
  expect(valor(`SELECT CONCAT_WS('|', CostoTotalPrograma, MontoDescuento, MontoTotalPagar, NumeroCuotas, TipoPlan) FROM pagoprograma WHERE idInscripcion = ${insc}`))
    .toBe([costo.toFixed(2), (costo - total).toFixed(2), total.toFixed(2), 2, 'REGULAR'].join('|'));
  expect(valor(`SELECT GROUP_CONCAT(CONCAT_WS(':', NumeroCuota, montocuota, fechapago, nvaucher, Estado) ORDER BY NumeroCuota SEPARATOR ';') FROM pagomodulo WHERE idinscripcion = ${insc}`))
    .toBe(`1:${cuota1.toFixed(2)}:2024-02-10:${voucherCuota}:PAGADO;2:1000.50:2024-02-01:CARGA HISTÓRICA:PAGADO`);
  expect(valor(`SELECT CONCAT_WS('|', costomatricula, montoPagado, nvauchermatricula) FROM estudianteprograma WHERE idInscripcion = ${insc}`))
    .toBe('500|500.00|CARGA HISTÓRICA');

  // Saldo como lo calcula Gestión de Pagos y los reportes (plan pactado - cuotas pagadas)
  expect(Number(valor(`SELECT pp.MontoTotalPagar - (SELECT SUM(montocuota) FROM pagomodulo WHERE idinscripcion = ${insc} AND Estado = 'PAGADO') FROM pagoprograma pp WHERE pp.idInscripcion = ${insc}`)))
    .toBeCloseTo(total - cuota1 - 1000.5, 2);

  // El inscrito sin pagos: subir otra vez con pagos los agrega (inscripción histórica sin pagos)
  const inscSin = String(valor(`SELECT ep.idInscripcion FROM estudianteprograma ep JOIN estudiante e ON e.EstudianteID = ep.EstudianteID WHERE e.Ci = ${ciSinPagos}`));
  expect(valor(`SELECT COUNT(*) FROM pagoprograma WHERE idInscripcion = ${inscSin}`)).toBe('0');
  const segunda = await llenarPlantilla(page, rutaPlantilla, [
    { ...base, ci: ciSinPagos, nombre: 'SIN PAGOS', 'pago:cuota1monto': costo },
    { ...base, ci: ciPagos, nombre: 'CON CUOTAS', 'pago:cuota1monto': 5 },
  ]);
  await page.locator('#cm-archivo').setInputFiles({ name: 'pagos2.xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', buffer: segunda.buffer });
  await page.locator('#cm-revisar').click();
  await expect(page.locator('#cm-resultado .cm-tabla tbody tr')).toHaveCount(2, { timeout: 20_000 });
  await expect(page.locator('#cm-resultado .cm-tabla tbody tr').nth(1)).toContainText('Ya tenía pagos registrados');
  await page.locator('#cm-registrar').click();
  await page.getByRole('button', { name: 'Sí, registrar' }).click();
  await expect(page.locator('#cm-resultado .cm-badge.ok').first()).toBeVisible({ timeout: 30_000 });
  expect(valor(`SELECT MontoTotalPagar FROM pagoprograma WHERE idInscripcion = ${inscSin}`)).toBe(costo.toFixed(2));
  expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE idinscripcion = ${insc}`)).toBe('2'); // no se duplicó

  // El comprobante de pago completo se genera (PDF) con las cuotas cargadas
  const pdf = await page.request.get(`extensiones/tcpdf/pdf/pdfpagocompletoprograma.php?idinscripcion=${insc}`);
  expect(pdf.headers()['content-type']).toContain('application/pdf');

  // Limpieza
  for (const ci of [ciPagos, ciSinPagos]) {
    const id = String(valor(`SELECT EstudianteID FROM estudiante WHERE Ci = ${ci}`));
    sql(`DELETE pm FROM pagomodulo pm JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion WHERE ep.EstudianteID = ${id};
         DELETE FROM pagoprograma WHERE EstudianteID = ${id};
         DELETE FROM estudianteprograma WHERE EstudianteID = ${id};
         DELETE FROM estudiante WHERE EstudianteID = ${id};`);
  }
});

test('solo el ADM usa la carga masiva', async ({ page }) => {
  await ingresar(page, 'sec');
  const r = await page.request.post('ajax/cargamasiva.ajax.php', { form: { accion: 'plantilla', programaID: '1' } });
  expect(r.status()).toBe(403);
  await page.goto('cargamasiva');
  await expect(page.locator('section.pn-bienvenida')).toBeVisible();
});
