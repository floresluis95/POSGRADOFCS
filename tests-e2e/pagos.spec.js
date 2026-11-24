// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Pagos recibidos (vistas/componentes/pagos.php): todo lo cobrado en una pantalla.
// Solo lee: no crea datos. ADM y SEC la tienen en el menú; el Auxiliar no.
test.describe('Pagos recibidos', () => {
  test('lista, totales por concepto, filtros y búsqueda', async ({ page }) => {
    await ingresar(page, 'sec');
    // Errores de JS desde aquí (la página de ingreso tiene uno conocido e inofensivo: header.on)
    const errores = [];
    page.on('pageerror', (e) => errores.push(e.message));
    // El enlace está en su menú (el menú de Metronic tiene scroll propio: se entra por la URL)
    await expect(page.locator('#kt_aside a[href="pagos"]')).toHaveCount(1);
    await page.goto('pagos');
    await expect(page.getByRole('heading', { name: 'PAGOS RECIBIDOS' })).toBeVisible();

    // Cada cambio de filtro vuelve a consultar: se espera la respuesta antes de leer la tabla
    const respuesta = () => page.waitForResponse((r) => r.url().includes('ajax/pagos.ajax.php'));

    // "Todo": la copia de pruebas tiene pagos de todos los conceptos
    await Promise.all([respuesta(), page.locator('[data-periodo="todo"]').click()]);
    await expect(page.locator('#pgTitulo')).toHaveText('Todos los pagos');
    await expect(page.locator('#pgCuerpo tr').first()).not.toHaveClass(/pg-vacio/);
    const filas = await page.locator('#pgCuerpo tr').count();
    expect(filas).toBeGreaterThan(0);
    await expect(page.locator('#pgTotalN')).toHaveText(`${filas} ${filas === 1 ? 'pago' : 'pagos'}`);

    // El total grande es la suma de las 4 tarjetas
    const monto = (t) => parseFloat(t.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    const total = monto(await page.locator('#pgTotal').innerText());
    let suma = 0;
    for (const t of await page.locator('.pg-total[data-concepto] [data-total]').allInnerTexts()) suma += monto(t);
    expect(Math.round(suma * 100)).toBe(Math.round(total * 100));

    // Pulsar la tarjeta de Cuotas deja solo cuotas; pulsarla otra vez vuelve a todos
    await Promise.all([respuesta(), page.locator('.pg-total[data-concepto="CUOTAS"]').click()]);
    await expect(page.locator('#pgTitulo')).toHaveText('Todos los pagos · Cuotas');
    const conceptos = await page.locator('#pgCuerpo .pg-concepto').allInnerTexts();
    expect(conceptos.length).toBeGreaterThan(0);
    expect(new Set(conceptos)).toEqual(new Set(['Cuota']));
    await Promise.all([respuesta(), page.locator('.pg-total[data-concepto="CUOTAS"]').click()]);
    await expect(page.locator('#pgTitulo')).toHaveText('Todos los pagos');

    // Búsqueda por C.I.: solo pagos de ese estudiante
    const ci = (await page.locator('#pgCuerpo tr').first().locator('.pg-sub').first().innerText()).replace(/^C\.I\.\s*/, '').split(/[-\s]/)[0];
    await Promise.all([respuesta(), page.locator('#pgEstudiante').fill(ci)]);
    await expect(page.locator('#pgResumen')).toContainText(`búsqueda: "${ci}"`);
    for (const texto of await page.locator('#pgCuerpo tr td:nth-child(2)').allInnerTexts()) {
      expect(texto).toContain(`C.I. ${ci}`);
    }

    // Un nombre que no existe: aviso claro y botón para ver todos los periodos
    await Promise.all([respuesta(), page.locator('#pgEstudiante').fill('zzqqxx inexistente')]);
    await expect(page.locator('#pgCuerpo')).toContainText('No hay pagos de "zzqqxx inexistente"');

    // Botones Excel | PDF del exportador común
    await expect(page.locator('#pgExportar button')).toHaveCount(2);

    expect(errores).toEqual([]);
  });

  test('el Auxiliar no la ve en el menú ni puede abrirla', async ({ page }) => {
    await ingresar(page, 'aux');
    await expect(page.locator('#kt_aside a[href="pagos"]')).toHaveCount(0);
    await page.goto('pagos');
    await expect(page.getByRole('heading', { name: 'PAGOS RECIBIDOS' })).toHaveCount(0);
  });
});
