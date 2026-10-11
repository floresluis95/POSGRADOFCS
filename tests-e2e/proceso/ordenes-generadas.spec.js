// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, ajax, estudianteInscrito, generarOrdenCuota } from './ayudantes-proceso.js';

// Pantalla "Órdenes de pago" del personal: todas las órdenes ya generadas
// (matrícula, cuota, clínica), con filtros y botón para volver a imprimirlas.
// Cada orden guarda quién la generó (ResponsableGeneracion).

test.describe('Órdenes de pago generadas', () => {
  test('lista las órdenes del estudiante, las filtra y permite reimprimirlas', async ({ page }) => {
    await ingresar(page, 'adm');
    const inscrito = await estudianteInscrito(page);
    const cuota = await generarOrdenCuota(page, inscrito, '2500');
    expect(cuota.success, cuota.mensaje).toBe(true);

    // Las dos órdenes guardan el nombre de quien las generó
    const sinResponsable = valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${inscrito.estudianteID} AND COALESCE(ResponsableGeneracion, '') = ''`);
    expect(sinResponsable).toBe('0');

    await ingresar(page, 'sec');
    await page.goto('ordenespago');
    const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${inscrito.estudianteID}`);
    await page.locator('#op_buscar').fill(String(ci));
    await expect(page.locator('#op_total')).toHaveText('2');
    await expect(page.locator('#op_pagadas')).toHaveText('1');
    await expect(page.locator('#op_pendientes')).toHaveText('1');

    await page.locator('#op_tipo').selectOption('CUOTA');
    await expect(page.locator('#op_cuerpo tr')).toHaveCount(1);
    const fila = page.locator('#op_cuerpo tr').first();
    await expect(fila).toContainText('Bs. 2.500,00');
    await expect(fila).toContainText('Pendiente');
    await expect(fila).toContainText('por ');

    const idOrdenCuota = valor(`SELECT IdOrdenPago FROM ordenpago WHERE EstudianteID = ${inscrito.estudianteID} AND Observaciones = 'CUOTA PROGRAMA'`);
    const enlace = fila.locator('a.op-imprimir');
    await expect(enlace).toHaveAttribute('href', `vistas/componentes/orden-pago-pdf.php?idordenpago=${idOrdenCuota}`);
    const pdf = await page.request.get(/** @type {string} */ (await enlace.getAttribute('href')));
    expect(pdf.headers()['content-type']).toContain('application/pdf');
  });

  test('el estudiante no puede abrir la pantalla ni pedir la lista', async ({ page }) => {
    await ingresar(page, 'est');
    await page.goto('ordenespago');
    await expect(page.locator('#op_tabla')).toHaveCount(0);
    const respuesta = await ajax(page, 'ordenpago.ajax.php', { accion: 'listarOrdenesGeneradas' });
    expect(respuesta.success).toBe(false);
  });
});
