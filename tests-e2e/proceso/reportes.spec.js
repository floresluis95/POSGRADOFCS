// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, estudianteInscrito, generarOrdenCuota, pagarCuota, PROGRAMA_ID } from './ayudantes-proceso.js';

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

test('lista general con filtros y reporte personalizado generan PDF (con abandonos)', async ({ page }) => {
  await ingresar(page, 'adm');
  const inscrito = await estudianteInscrito(page);
  sql(`UPDATE estudianteprograma SET Estado = 'ABANDONO', MotivoAbandono = 'PRUEBA' WHERE idInscripcion = ${inscrito.idInscripcion}`);
  const gestion = String(valor(`SELECT YEAR(FechaInicio) FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  const programa = String(PROGRAMA_ID);

  await esPdf(page, { tipo_reporte: 'estudiantes' });
  await esPdf(page, { tipo_reporte: 'estudiantes', programa_id: programa, gestion, situacion: 'ABANDONO', orden: 'programa' });
  await esPdf(page, { tipo_reporte: 'estudiantes', situacion: 'SIN_INSCRIPCION' });
  await esPdf(page, { tipo_reporte: 'estudiantes', gestion: '1990' }); // gestión inexistente: se ignora

  const columnas = ['ci', 'nombre', 'edad', 'correo', 'profesion', 'universidad', 'programa', 'gestion', 'situacion',
    'abandono_modulo', 'abandono_motivo', 'aprobados', 'promedio', 'plan', 'cuotas', 'costo', 'pagado', 'saldo'];
  const r = await page.request.post('ajax/reportes.ajax.php', {
    multipart: { tipo_reporte: 'personalizado', titulo: 'Prueba personalizada', agrupar: 'programa', orden_personalizado: 'saldo',
      marcar_abandonos: '1', ...Object.fromEntries(columnas.map((c, i) => [`columnas[${i}]`, c])) },
  });
  expect((await r.body()).subarray(0, 4).toString()).toBe('%PDF');
  // Columna desconocida y sin columnas válidas: usa las de siempre, no falla
  await esPdf(page, { tipo_reporte: 'personalizado', 'columnas[]': 'no_existe', solo_con_saldo: '1', orientacion: 'P' });
  sql(`UPDATE estudianteprograma SET Estado = 'ACTIVO', MotivoAbandono = NULL WHERE idInscripcion = ${inscrito.idInscripcion}`);
});

test('pestaña Personalizado: plantillas y columnas', async ({ page }) => {
  await ingresar(page, 'sec');
  await page.goto('reportes');
  await page.locator('.rp-tab[data-panel="rp-personalizado"]').click();
  await expect(page.locator('#rp-personalizado')).toBeVisible();
  await page.locator('[data-plantilla="cobranza"]').click();
  await expect(page.locator('#rpPersonalizado input[value="saldo"]')).toBeChecked();
  await expect(page.locator('#orden_pz')).toHaveValue('saldo');
  await expect(page.locator('#rpPzContador')).toHaveText('(9 elegidas)');
});

test('el pie de página no queda fijo encima del contenido al cambiar de pestaña', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.setViewportSize({ width: 1366, height: 650 });
  await page.goto('reportes');
  await page.evaluate(() => { try { localStorage.setItem('reportes.categoria', 'rp-estudiantes'); } catch (e) {} });
  await page.reload();
  await page.locator('.rp-tab[data-panel="rp-personalizado"]').click(); // la más larga
  await expect.poll(() => page.evaluate(() => document.getElementById('kt_footer').style.position)).toBe('');
  // El pie queda al final del contenido y se puede llegar a él con scroll
  await page.mouse.wheel(0, 5000);
  await expect.poll(() => page.evaluate(() => window.scrollY)).toBeGreaterThan(0);
});

test('filtros de la lista general: contador, cascada de programas, sin programa y limpiar', async ({ page }) => {
  await ingresar(page, 'adm');
  // El contador usa el mismo filtrado que el PDF
  const total = Number(valor(`SELECT COUNT(*) FROM estudianteprograma ep JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
    JOIN programa p ON p.ProgramaID = ep.ProgramaID WHERE e.Estado = 1 AND p.Estado = 1 AND ep.Estado IN ('ACTIVO','CONFIRMADO','ABANDONO')`));
  const r = await page.request.get('ajax/reportes.ajax.php?tipo_reporte=contar_estudiantes');
  expect((await r.json()).registros).toBe(total);

  await page.goto('reportes');
  const tarjeta = page.locator('form.rp-contable').filter({ has: page.locator('input[value="estudiantes"]') });
  await expect(tarjeta.locator('.rp-contador')).toContainText('Saldrán');

  // Programa acotado por la gestión elegida
  const gestion = String(valor(`SELECT YEAR(FechaInicio) FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  const enGestion = Number(valor(`SELECT COUNT(*) FROM programa WHERE Estado = 1 AND YEAR(FechaInicio) = ${gestion}`));
  await tarjeta.locator('select[name="gestion"]').selectOption(gestion);
  await expect(tarjeta.locator('select[name="programa_id"] option:not([value=""])')).toHaveCount(enGestion);

  // "Sin ningún programa" apaga los filtros del programa
  await tarjeta.locator('select[name="situacion"]').selectOption('SIN_INSCRIPCION');
  await expect(tarjeta.locator('select[name="gestion"]')).toBeDisabled();
  await expect(tarjeta.locator('.rp-aviso-sin')).toBeVisible();

  // Limpiar deja todo como al inicio
  await tarjeta.locator('.rp-limpiar').click();
  await expect(tarjeta.locator('select[name="situacion"]')).toHaveValue('');
  await expect(tarjeta.locator('select[name="gestion"]')).toBeEnabled();
  await expect(tarjeta.locator('select[name="gestion"]')).toHaveValue('');
  await expect(tarjeta.locator('select[name="programa_id"] option:not([value=""])')).toHaveCount(
    Number(valor(`SELECT COUNT(*) FROM programa WHERE Estado = 1`)));
});
