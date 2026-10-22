// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, estudianteInscrito, generarOrdenCuota, pagarCuota, PROGRAMA_ID } from './ayudantes-proceso.js';

// Pantalla Reportes: categorías en pestañas y los reportes nuevos (estado de cuenta, saldos,
// consolidado de notas, movimientos e ingresos por mes) responden con un PDF.

const esPdf = async (page, form) => {
  const r = await page.request.post('ajax/reportes.ajax.php', { form });
  expect(r.status(), form.tipo_reporte).toBe(200);
  const cuerpo = await r.body();
  expect(cuerpo.subarray(0, 4).toString(), `"${form.tipo_reporte}" debe ser un PDF (respondió: ${cuerpo.subarray(0, 200).toString()})`).toBe('%PDF');
};

test('las categorías se cambian con las pestañas', async ({ page }) => {
  await ingresar(page, 'sec');
  await page.goto('reportes');
  await expect(page.locator('#rp-estudiantes')).toBeVisible();
  await expect(page.locator('#rp-cuentas')).toBeHidden();
  await page.locator('.rp-tab[data-panel="rp-cuentas"]').click();
  await expect(page.locator('#rp-cuentas')).toBeVisible();
  await expect(page.locator('#rp-cuentas .rp-card')).toHaveCount(4);
  await expect(page.locator('#rp-estudiantes')).toBeHidden();
});

test('los reportes nuevos generan PDF con datos y sin datos', async ({ page }) => {
  await ingresar(page, 'adm');
  const inscrito = await estudianteInscrito(page);
  expect((await generarOrdenCuota(page, inscrito, '1000')).success).toBe(true);
  await pagarCuota(page, inscrito.idInscripcion, '1000', unico() + '-02');
  expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PAGADO'`)).toBe('1');
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`));
  const cuenta = String(valor(`SELECT NumeroCuenta FROM programacuenta WHERE ProgramaID = ${PROGRAMA_ID} AND Estado = 1 LIMIT 1`) ?? '');
  const hoy = new Date().toISOString().slice(0, 10);
  const programa = String(PROGRAMA_ID);

  await esPdf(page, { tipo_reporte: 'estado_cuenta', ci });
  await esPdf(page, { tipo_reporte: 'estado_cuenta', ci: '1' }); // no existe
  await esPdf(page, { tipo_reporte: 'saldos', programa_id: programa });
  await esPdf(page, { tipo_reporte: 'saldos', solo_con_saldo: '1' });
  await esPdf(page, { tipo_reporte: 'notas_programa', programa_id: programa });
  await esPdf(page, { tipo_reporte: 'notas_programa', programa_id: '' }); // falta elegir
  await esPdf(page, { tipo_reporte: 'movimientos', numero_cuenta: cuenta, fecha_desde: '2020-01-01', fecha_hasta: hoy });
  await esPdf(page, { tipo_reporte: 'movimientos', concepto: 'CLINICA', fecha_desde: '1990-01-01', fecha_hasta: '1990-01-02' }); // vacío
  await esPdf(page, { tipo_reporte: 'ingresos_mes', programa_id: programa });
  await esPdf(page, { tipo_reporte: 'cuentas_programa', programa_id: programa });
  await esPdf(page, { tipo_reporte: 'cuentas_programa', fecha_desde: '1990-01-01', fecha_hasta: '1990-01-02' }); // sin pagos: igual muestra las cuentas
});

test('la Secretaría también genera los reportes nuevos', async ({ page }) => {
  await ingresar(page, 'sec');
  await esPdf(page, { tipo_reporte: 'ingresos_mes' });
  await esPdf(page, { tipo_reporte: 'saldos', programa_id: String(PROGRAMA_ID) });
});
