// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, estudianteInscrito } from './ayudantes-proceso.js';

// Mis órdenes de pago (estudiante): pago en Tesoro Universitario, conceptos que tiene,
// comprobante de la orden pagada (también la matrícula), recibo de pagos propio y
// vista en celular sin textos cortados.

const estudianteDePrueba = () => valor("SELECT EstudianteID FROM usuario WHERE Usuario = 'prueba_est'");

test('muestra sus órdenes, el comprobante de la matrícula y el recibo de pagos', async ({ page }) => {
  const estudianteID = estudianteDePrueba();
  const idInscripcion = valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND foto IS NOT NULL LIMIT 1`);
  test.skip(!idInscripcion, 'prueba_est no tiene una matrícula con foto');

  await ingresar(page, 'est');

  // Mis órdenes de pago: dónde se paga, conceptos que tiene y enlace a sus pagos
  await page.goto('misordenespago');
  await expect(page.locator('.mop-aviso')).toContainText('Tesoro Universitario');
  await expect(page.locator('#tabla_ordenes_pago tbody tr').first()).toBeVisible({ timeout: 15_000 });
  await expect(page.locator('#filtro_tipo option', { hasText: 'Módulos' })).toHaveCount(0);
  const porPagar = Number(valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${estudianteID} AND Estado IN ('PENDIENTE', 'VENCIDO', 'EXTRAVIADO')`));
  await expect(page.locator('#lista_por_pagar .mop-pp')).toHaveCount(porPagar);
  // Orden ya pagada: avisa antes de imprimir y deja imprimirla igual
  await page.locator('.btn-imprimir-orden[data-pagada]').first().click();
  const aviso = page.locator('.swal2-popup');
  await expect(aviso).toContainText('Esta orden ya fue pagada');
  const [pedido] = await Promise.all([
    page.context().waitForEvent('request', (r) => r.url().includes('orden-pago-pdf.php?idordenpago=')),
    aviso.locator('.swal2-confirm').click(),
  ]);
  expect(pedido.url()).toContain('orden-pago-pdf.php');
  for (const p of page.context().pages()) if (p !== page) await p.close();

  await page.locator('#btn_historial_pagos').click();

  // Historial de pagos: comprobante de la matrícula (la imagen carga) y recibo de pagos
  await expect(page).toHaveURL(/historialmodulosestudiante/);
  const fila = page.locator('#tbody_pagos tr', { hasText: 'Matrícula' });
  await fila.locator('.btn-comprobante').click({ timeout: 15_000 });
  const imagen = page.locator('#imagen_recibo_grande');
  await expect(imagen).toBeVisible();
  await expect.poll(() => imagen.evaluate((img) => /** @type {HTMLImageElement} */ (img).naturalWidth), { timeout: 15_000 }).toBeGreaterThan(0);
  await page.locator('#modalFotoRecibo .close').click();
  await expect(page.locator('#btn_recibo_pagos')).toBeVisible();
  const recibo = await page.request.get(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${idInscripcion}`);
  expect(await recibo.text()).not.toContain('Acceso denegado');
});

test('un estudiante no ve el comprobante ni el recibo de otro', async ({ page, browser }) => {
  await ingresar(page, 'adm');
  const otro = await estudianteInscrito(page);
  const ajeno = await browser.newPage();
  await ingresar(ajeno, 'est');
  expect((await ajeno.request.get(`ajax/obtener-foto-matricula.php?idInscripcion=${otro.idInscripcion}`)).status()).toBe(403);
  expect(await (await ajeno.request.get(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${otro.idInscripcion}`)).text()).toContain('Acceso denegado');
  await ajeno.close();
});

test('en celular el estado "Voucher por validar" no se sale de la tarjeta', async ({ page }) => {
  await ingresar(page, 'est');
  await page.setViewportSize({ width: 360, height: 760 });
  await page.goto('misordenespago');
  await expect(page.locator('#tabla_ordenes_pago tbody tr').first()).toBeVisible({ timeout: 15_000 });
  // Se fuerza el estado más largo en la primera fila
  await page.evaluate(() => {
    const celda = document.querySelector('#tabla_ordenes_pago tbody tr td[data-label="Estado"]');
    if (celda) celda.innerHTML = '<span class="badge-orden badge-extraviado">Voucher por validar</span>';
  });
  const desborda = await page.evaluate(() => {
    const badge = document.querySelector('#tabla_ordenes_pago .badge-extraviado');
    const tarjeta = badge.closest('tr').getBoundingClientRect();
    const b = badge.getBoundingClientRect();
    return b.right > tarjeta.right + 1 || document.documentElement.scrollWidth > window.innerWidth + 1;
  });
  expect(desborda).toBe(false);
});
