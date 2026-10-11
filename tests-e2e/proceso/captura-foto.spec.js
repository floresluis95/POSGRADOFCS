// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes-proceso.js';

// Foto del voucher (captura-foto.js): botón "Cámara" junto a cada <input data-captura-foto>
// y ventana para recortar/girar al elegir o tomar una imagen. El servidor de pruebas es
// localhost (contexto seguro), así que la cámara del navegador está permitida; Chromium
// usa una cámara simulada.
test.use({
  launchOptions: { args: ['--use-fake-device-for-media-stream', '--use-fake-ui-for-media-stream'] },
  permissions: ['camera'],
});

/** Imagen PNG de 400x300 generada en el navegador (la de los vouchers es de 1x1). */
async function imagenDePrueba(page) {
  const dataUrl = await page.evaluate(() => {
    const lienzo = document.createElement('canvas');
    lienzo.width = 400;
    lienzo.height = 300;
    const ctx = /** @type {CanvasRenderingContext2D} */ (lienzo.getContext('2d'));
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, 400, 300);
    ctx.fillStyle = '#1e3c72';
    ctx.fillRect(50, 50, 300, 200);
    return lienzo.toDataURL('image/png');
  });
  return { name: 'voucher-grande.png', mimeType: 'image/png', buffer: Buffer.from(dataUrl.split(',')[1], 'base64') };
}

/** Datos del archivo que quedó en el input. */
const archivoDelInput = (page, selector) => page.locator(selector).evaluate((el) => {
  const archivo = /** @type {HTMLInputElement} */ (el).files?.[0];
  return archivo ? { nombre: archivo.name, tipo: archivo.type, tamanio: archivo.size } : null;
});

test.describe('Foto del voucher: cámara y recorte', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
    await page.goto('inscripcion');
    // Un campo agregado después de cargar la página también recibe el botón "Cámara"
    await page.evaluate(() => document.body.insertAdjacentHTML('beforeend',
      '<div id="zonaPrueba" style="position:relative;z-index:1;background:#fff;padding:8px;">' +
      '<input type="file" id="fotoPrueba" accept="image/*,.pdf" data-captura-foto></div>'));
  });

  test('los campos de foto de voucher tienen el botón Cámara', async ({ page }) => {
    await expect(page.locator('#voucherFoto')).toHaveAttribute('data-captura-foto', '');
    await expect(page.locator('#zonaPrueba .captura-foto-boton')).toHaveCount(1);
  });

  test('al elegir una imagen se abre el recorte y queda un JPEG recortado', async ({ page }) => {
    await page.evaluate(() => {
      // @ts-ignore
      window.__cambios = 0;
      // @ts-ignore
      document.getElementById('fotoPrueba').addEventListener('change', () => window.__cambios++);
    });
    await page.locator('#fotoPrueba').setInputFiles(await imagenDePrueba(page));
    await expect(page.locator('#modalCapturaRecorte')).toBeVisible();
    await page.locator('[data-captura-accion="girar-der"]').click();
    await page.locator('#capturaRecorteUsar').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();

    const archivo = await archivoDelInput(page, '#fotoPrueba');
    expect(archivo?.tipo).toBe('image/jpeg');
    expect(archivo?.nombre).toBe('voucher-grande.jpg');
    // La pantalla recibe un "change" con la foto final (para su vista previa), sin
    // volver a abrir el recorte
    // @ts-ignore
    expect(await page.evaluate(() => window.__cambios)).toBe(2);
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
  });

  test('un PDF se deja tal cual, sin recorte', async ({ page }) => {
    await page.locator('#fotoPrueba').setInputFiles({ name: 'voucher.pdf', mimeType: 'application/pdf', buffer: Buffer.from('%PDF-1.4\n%%EOF') });
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
    expect((await archivoDelInput(page, '#fotoPrueba'))?.tipo).toBe('application/pdf');
  });

  test('con la cámara se toma la foto, se recorta y queda en el campo', async ({ page }) => {
    await page.locator('#zonaPrueba .captura-foto-boton').click();
    await expect(page.locator('#modalCapturaCamara')).toBeVisible();
    await expect.poll(() => page.locator('#capturaCamaraVideo').evaluate((v) => /** @type {HTMLVideoElement} */ (v).videoWidth)).toBeGreaterThan(0);
    await page.locator('#capturaCamaraTomar').click();

    await expect(page.locator('#modalCapturaRecorte')).toBeVisible();
    await page.locator('#capturaRecorteSinCambios').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();

    const archivo = await archivoDelInput(page, '#fotoPrueba');
    expect(archivo?.tipo).toBe('image/jpeg');
    expect(archivo?.tamanio).toBeGreaterThan(0);
  });
});
