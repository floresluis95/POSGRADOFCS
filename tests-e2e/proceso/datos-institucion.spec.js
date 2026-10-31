// @ts-check
import { test, expect } from '@playwright/test';
import { inflateSync } from 'node:zlib';
import { sql, valor, ingresar, ajax, IMAGEN_VOUCHER, PROGRAMA_ID } from './ayudantes-proceso.js';

// Configuración > Datos de la institución y Logos: el encabezado de los reportes PDF
// (universidad, facultad, coordinación, dirección, logos), el cargo bajo la firma y los
// textos y logo de las pantallas salen de aquí (una instalación por área de posgrado).

test.describe.configure({ mode: 'serial' });

const limpiar = () => sql("DELETE FROM configuracion WHERE Clave LIKE 'INST\\_%'; DELETE FROM imagenfirma WHERE Clave LIKE 'LOGO\\_%';");
test.beforeAll(limpiar);
test.afterAll(limpiar);

/** Texto de un PDF de TCPDF: se descomprimen sus streams */
const textoPdf = (pdf) => {
  const crudo = pdf.toString('latin1');
  let texto = crudo;
  const re = /stream\r?\n/g;
  let m;
  while ((m = re.exec(crudo))) {
    const fin = crudo.indexOf('endstream', m.index);
    try {
      texto += inflateSync(pdf.subarray(m.index + m[0].length, fin)).toString('latin1');
    } catch { /* no comprimido o imagen */ }
  }
  return texto;
};

const conNotas = () => valor(`SELECT c.EstudianteID FROM calificacion c WHERE c.ProgramaId = ${PROGRAMA_ID} AND c.Nota IS NOT NULL LIMIT 1`);

test('sin configurar: valores de Posgrado Odontología y logos originales', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('autoridades');
  await expect(page.locator('#INST_UNIVERSIDAD')).toHaveValue('UNIVERSIDAD TÉCNICA DE ORURO');
  await expect(page.locator('#INST_AREA')).toHaveValue('Odontología');
  await expect(page.locator('#vistaUnidad')).toHaveText('COORDINACIÓN DE POSGRADO - ODONTOLOGÍA');
  await expect(page.locator('#vista_LOGO_IZQUIERDO img')).toHaveAttribute('src', 'extensiones/imagenespdf/logouto.png');
  await expect(page.locator('.btn-quitar-firma[data-clave="LOGO_IZQUIERDO"]')).toBeHidden();
  await expect(page.locator('#kt_header_mobile img')).toHaveAttribute('src', 'vistas/recursos/assets/media/logos/logo.png');
});

test('el ADM cambia los datos desde la pantalla y salen en el PDF y en las pantallas', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('autoridades');
  await expect(page.locator('#INST_AREA')).toHaveValue('Odontología');
  await page.locator('#INST_AREA').fill('Medicina');
  await page.locator('#INST_UNIDAD').fill('COORDINACIÓN DE POSGRADO - MEDICINA');
  await page.locator('#INST_CARGO_FIRMA').fill('COORDINADOR DE POSGRADO MEDICINA');
  await page.locator('#INST_DIRECCION').fill('Av. Prueba 123');
  await page.locator('#INST_TELEFONOS').fill(''); // vacío = valor original
  await expect(page.locator('#vistaUnidad')).toHaveText('COORDINACIÓN DE POSGRADO - MEDICINA'); // vista previa al escribir
  await page.locator('#btnGuardarInstitucion').click();
  await expect(page.locator('.swal2-title')).toHaveText('Datos de la institución actualizados');

  expect(valor("SELECT Valor FROM configuracion WHERE Clave = 'INST_AREA'")).toBe('Medicina');
  await page.reload();
  await expect(page.locator('#INST_TELEFONOS')).toHaveValue('Telf: 5237317 - Fax: 5247110');
  await expect(page.locator('#actualizadoInstitucion')).toContainText(/^Actualizado: .+ por .+/);

  // PDF de situación académica: coordinación, dirección y cargo bajo la firma
  const r = await page.request.get(`vistas/componentes/situacion-academica-pdf.php?estudianteID=${conNotas()}&programaID=${PROGRAMA_ID}`);
  expect(r.status()).toBe(200);
  const texto = textoPdf(await r.body());
  expect(texto).toContain('MEDICINA');
  expect(texto).toContain('Av. Prueba 123');
  expect(texto).toContain('COORDINADOR DE POSGRADO MEDICINA');

  // Pantallas: ingreso (público) con el área nueva
  await page.context().clearCookies();
  await page.goto('ingreso');
  await expect(page.locator('.lg-intro-texto')).toContainText('Coordinación de Posgrado en Medicina');
  await expect(page.locator('.lg-direccion')).toContainText('Av. Prueba 123');
});

test('logos: subir, se usan en pantallas y reportes, y volver al original', async ({ page }) => {
  await ingresar(page, 'adm');
  for (const clave of ['LOGO_IZQUIERDO', 'LOGO_SISTEMA']) {
    const subida = await ajax(page, 'autoridad.ajax.php', { accion: 'subirImagenFirma', clave }, { imagen: IMAGEN_VOUCHER });
    expect(subida.success, JSON.stringify(subida)).toBe(true);
  }

  await page.goto('autoridades');
  await expect(page.locator('#vista_LOGO_IZQUIERDO img')).toHaveAttribute('src', /^data:image\/png;base64,/);
  await expect(page.locator('#vistaLogoIzq')).toHaveAttribute('src', /^data:image\/png;base64,/);
  await expect(page.locator('#vista_LOGO_DERECHO img')).toHaveAttribute('src', 'extensiones/imagenespdf/logofcs.png');

  // Logo del sistema en las pantallas: servido por ajax/imagen-institucion.php (público)
  const src = String(await page.locator('#kt_header_mobile img').getAttribute('src'));
  expect(src).toMatch(/^ajax\/imagen-institucion\.php\?clave=LOGO_SISTEMA&v=\d+$/);
  await page.context().clearCookies();
  const logo = await page.request.get(src);
  expect(logo.status()).toBe(200);
  expect(logo.headers()['content-type']).toBe('image/png');
  // La firma y el sello nunca salen por ahí
  expect((await page.request.get('ajax/imagen-institucion.php?clave=FIRMA')).status()).toBe(404);

  // El reporte PDF se genera con el logo subido
  await ingresar(page, 'adm');
  const r = await page.request.get(`vistas/componentes/situacion-academica-pdf.php?estudianteID=${conNotas()}&programaID=${PROGRAMA_ID}`);
  expect((await r.body()).subarray(0, 4).toString()).toBe('%PDF');

  // Volver al original
  await page.goto('autoridades');
  await page.locator('.btn-quitar-firma[data-clave="LOGO_SISTEMA"]').click();
  await page.locator('.swal2-confirm').click();
  await expect(page.locator('.btn-quitar-firma[data-clave="LOGO_SISTEMA"]')).toBeHidden();
  expect(valor("SELECT COUNT(*) FROM imagenfirma WHERE Clave = 'LOGO_SISTEMA'")).toBe('0');
  await page.reload();
  await expect(page.locator('#kt_header_mobile img')).toHaveAttribute('src', 'vistas/recursos/assets/media/logos/logo.png');
});

test('solo el ADM cambia los datos de la institución', async ({ page }) => {
  await ingresar(page, 'sec');
  const r = await page.request.post('ajax/autoridad.ajax.php', { form: { accion: 'guardarInstitucion', 'institucion[INST_AREA]': 'X' } });
  expect(r.status()).not.toBe(200);
  expect(valor("SELECT COUNT(*) FROM configuracion WHERE Clave = 'INST_AREA' AND Valor = 'X'")).toBe('0');
});
