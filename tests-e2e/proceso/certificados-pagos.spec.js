// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Historial de pagos de certificados: costo por tipo (Autoridades) que llega prellenado
// como "Monto pagado", monto guardado con cada emisión, filtros, total e impresión en
// Registro de documentos.

test.beforeEach(async ({ page }) => {
  await ingresar(page, 'adm');
});

/** Genera la carta de Medio Magnético (no exige calificaciones) con el voucher y monto dados. */
async function emitirMedioMagnetico(page, estudianteID, voucher, monto, fecha = '2026-10-10') {
  const nombre = valor(`SELECT CONCAT(Apaterno, ' ', Amaterno, ' ', Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  const pdf = await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: String(nombre),
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA', vaucher: voucher,
      monto: String(monto), fecha, coordinador: 'COORDINADOR DE PRUEBA', director: 'DIRECTOR DE PRUEBA',
    },
  });
  expect(pdf.headers()['content-type']).toContain('application/pdf');
}

test('los costos se guardan por grado académico y llegan al certificado según el programa', async ({ page }) => {
  const grado = String(valor(`SELECT REPLACE(UPPER(GradoAcademico), 'Í', 'I') FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  const otroGrado = grado === 'DOCTORADO' ? 'DIPLOMADO' : 'DOCTORADO';
  const guardar = await ajax(page, 'autoridad.ajax.php', {
    accion: 'guardarCostosCertificados',
    [`costos[MEDIO_MAGNETICO][${grado}]`]: '85', [`costos[MEDIO_MAGNETICO][${otroGrado}]`]: '300',
    [`costos[CALIFICACIONES][${grado}]`]: '120.5',
  });
  expect(guardar.success, guardar.mensaje).toBe(true);
  const costos = await ajax(page, 'autoridad.ajax.php', { accion: 'obtenerCostosCertificados' });
  expect(costos.precios.MEDIO_MAGNETICO[grado]).toBe(85);
  expect(costos.precios.MEDIO_MAGNETICO[otroGrado]).toBe(300);
  expect(costos.precios.CALIFICACIONES[grado]).toBe(120.5);

  // Un costo negativo no se guarda
  const malo = await ajax(page, 'autoridad.ajax.php', { accion: 'guardarCostosCertificados', [`costos[REVISION][${grado}]`]: '-5' });
  expect(malo.success).toBe(false);

  // Al elegir al estudiante, "Monto pagado" llega con el precio del grado de su programa
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await page.goto('certificadomediomagnetico');
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#cert-elegido')).toBeVisible({ timeout: 15_000 });
  await expect(page.locator('#input-monto')).toHaveValue('85.00');
  await expect(page.locator('#ayuda-monto')).toContainText('Bs. 85,00');

  // Pantalla Costos de certificados: la tabla muestra los costos y se pueden editar
  await page.goto('costoscertificados');
  const campo = page.locator(`.costo-certificado[data-tipo="MEDIO_MAGNETICO"][data-grado="${grado}"]`);
  await expect(campo).toHaveValue('85.00', { timeout: 15_000 });
  await campo.fill('90');
  await expect(page.locator('#estadoCambios')).toContainText('1 cambio sin guardar');
  await page.locator('#btnGuardarCostos').click();
  // Confirmación con los precios que cambian
  await expect(page.locator('.swal2-popup')).toContainText('Bs. 90,00');
  await page.locator('.swal2-confirm').click();
  await expect.poll(() => valor(`SELECT Valor FROM configuracion WHERE Clave = 'COSTO_CERT_MEDIO_MAGNETICO_${grado}'`), { timeout: 15_000 }).toBe('90.00');
});

test('el monto se guarda con la emisión y el historial filtra, suma e imprime', async ({ page }) => {
  const { estudianteID } = await estudianteInscrito(page);
  const voucher = '1470' + String(unico()).slice(-3) + '-021';
  await emitirMedioMagnetico(page, estudianteID, voucher, 85);
  expect(valor(`SELECT Monto FROM documentoemitido WHERE NumeroVaucher = '${voucher}'`)).toBe('85.00');

  // Listado con filtros: tipo y fechas
  const delTipo = await ajax(page, 'certificado.ajax.php', { accion: 'listarDocumentosEmitidos', tipo: 'MEDIO_MAGNETICO' });
  expect(delTipo.documentos.every((d) => d.TipoDocumento === 'MEDIO_MAGNETICO')).toBe(true);
  expect(delTipo.documentos.some((d) => d.NumeroVaucher === voucher && Number(d.Monto) === 85)).toBe(true);
  const otroTipo = await ajax(page, 'certificado.ajax.php', { accion: 'listarDocumentosEmitidos', tipo: 'CALIFICACIONES' });
  expect(otroTipo.documentos.some((d) => d.NumeroVaucher === voucher)).toBe(false);
  const futuro = await ajax(page, 'certificado.ajax.php', { accion: 'listarDocumentosEmitidos', desde: '2099-01-01' });
  expect(futuro.documentos).toEqual([]);

  // Pantalla: columna Monto, total e impresión del detalle
  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(String(ci));
  await page.locator('#btn-buscar-ci').click();
  const fila = page.locator('#tabla-log-body tr', { hasText: voucher });
  await expect(fila).toContainText('Bs. 85,00', { timeout: 15_000 });
  await expect(page.locator('#tabla-log-pie')).toContainText('Bs. 85,00');
  await expect(page.locator('#tabla-log-pie')).toContainText('1 documento(s)');

  await page.locator('#filtro-tipo').selectOption('CALIFICACIONES');
  await expect(page.locator('#tabla-log-body')).toContainText('Sin documentos emitidos con estos filtros', { timeout: 15_000 });
  await page.locator('#btn-limpiar-pagos').click();
  await expect(fila).toBeVisible({ timeout: 15_000 });

  const [ventana] = await Promise.all([page.waitForEvent('popup'), page.locator('#btn-imprimir-pagos').click()]);
  await ventana.waitForLoadState();
  const html = await ventana.content();
  expect(html).toContain('DETALLE DE PAGOS DE CERTIFICADOS');
  expect(html).toContain(voucher);
  expect(html).toContain('Bs. 85,00');
  await ventana.close();

  // Reimprimir corrigiendo el monto lo actualiza en el registro
  const item = page.locator('#contenedor-checklist .item-checklist', { hasText: 'Medio Magnético' });
  await item.locator('[data-reimprimir]').click();
  await expect(page.locator('#reimp-monto')).toHaveValue('85');
  await page.locator('#reimp-monto').fill('90');
  await Promise.all([page.waitForEvent('popup'), page.locator('#form-reimprimir button[type="submit"]').click()]);
  await expect.poll(() => valor(`SELECT Monto FROM documentoemitido WHERE NumeroVaucher = '${voucher}'`)).toBe('90.00');
});
