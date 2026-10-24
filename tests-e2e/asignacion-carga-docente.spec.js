// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Asignación Docente > pestaña Carga docente: resumen por docente o lista de módulos (una
// tabla a la vez), filtros (docente, año, grado, programa, estado) y botones Excel | PDF.
// Usa los módulos con docente que ya tenga la base de pruebas (proyecto_pruebas).

/** Elige un valor en un combo con Select2 (el <select> real queda oculto) */
async function elegir(page, selector, valor) {
  await page.evaluate(([sel, val]) => {
    // @ts-ignore jQuery de la página
    window.jQuery(sel).val(val).trigger('change');
  }, [selector, valor]);
}

for (const rol of ['adm', 'sec']) {
  test(`${rol.toUpperCase()}: la carga docente cuenta los módulos de cada docente y filtra`, async ({ page }) => {
    await ingresar(page, rol);
    await page.goto('asignaciondocente');

    // Al entrar se ve la pestaña de asignar; la carga está en la otra pestaña
    const seccion = page.locator('#carga-docente');
    await expect(page.locator('#tabla-modulos')).toBeVisible();
    await expect(seccion).toBeHidden();
    await page.locator('.ad-tab[data-panel="carga"]').click();
    await expect(seccion).toBeVisible();
    await expect(page.locator('#panel-asignar')).toBeHidden();

    const filas = page.locator('#tabla-carga-detalle tr.cd-fila');
    const total = await filas.count();
    test.skip(total === 0, 'La base de pruebas no tiene módulos con docente');

    // Vista por docente: el resumen suma todos los módulos; la lista está oculta
    const sumaResumen = async () => {
      const cantidades = await page.locator('#cd-resumen-cuerpo .cd-cantidad').allInnerTexts();
      return cantidades.reduce((s, t) => s + Number(t), 0);
    };
    expect(await sumaResumen()).toBe(total);
    await expect(page.locator('#cd-total-modulos')).toHaveText(String(total));
    await expect(page.locator('#tabla-carga-resumen')).toBeVisible();
    await expect(page.locator('#tabla-carga-detalle')).toBeHidden();
    await expect(page.locator('#cd-limpiar')).toBeHidden();

    // Solo se ven los botones de exportar de la tabla que está a la vista
    await expect(seccion.locator('.exportar-pdf:visible')).toHaveCount(1);
    await expect(seccion.locator('.exportar-excel:visible')).toHaveCount(1);

    // Clic en el primer docente: pasa a la lista con solo sus módulos
    const primero = page.locator('#cd-resumen-cuerpo tr.cd-docente-fila').first();
    const docenteID = await primero.getAttribute('data-docente');
    const suyos = Number(await primero.locator('.cd-cantidad').innerText());
    await primero.click();
    await expect(page.locator('#tabla-carga-detalle')).toBeVisible();
    await expect(page.locator('#tabla-carga-resumen')).toBeHidden();
    await expect(page.locator('#tabla-carga-detalle tr.cd-fila:visible')).toHaveCount(suyos);
    await expect(page.locator('#tabla-carga-detalle tr.cd-grupo:visible')).toHaveCount(1);
    await expect(page.locator(`#tabla-carga-detalle tr.cd-grupo[data-docente="${docenteID}"] .cd-grupo-num`)).toHaveText(String(suyos));
    await expect(page.locator('#cd-docente')).toHaveValue(String(docenteID));
    await expect(page.locator('#cd-detalle-filtro')).toContainText('Filtrado por');
    await expect(page.locator('#cd-limpiar')).toBeVisible();
    // El título del Excel/PDF lleva el filtro
    await expect(page.locator('#tabla-carga-detalle')).toHaveAttribute('data-exportar', /Carga docente - Detalle - .+/);
    await expect(page.locator('#tabla-carga-resumen')).toHaveAttribute('data-exportar', /Carga docente - Resumen - .+/);

    // Quitar filtros: vuelven todos los módulos
    await page.locator('#cd-limpiar').click();
    await expect(page.locator('#tabla-carga-detalle tr.cd-fila:visible')).toHaveCount(total);

    // Filtro por grado: todas las filas visibles son de ese grado
    const grado = await filas.first().getAttribute('data-grado');
    await elegir(page, '#cd-grado', grado);
    const grados = await page.locator('#tabla-carga-detalle tr.cd-fila:visible').evaluateAll(
      (trs) => [...new Set(trs.map((tr) => tr.getAttribute('data-grado')))],
    );
    expect(grados).toEqual([grado]);
    // El combo de programas solo ofrece los de ese grado
    const gradosProgramas = await page.locator('#cd-programa option:not([value=""])').evaluateAll(
      (ops) => [...new Set(ops.map((o) => o.getAttribute('data-grado')))],
    );
    expect(gradosProgramas).toEqual([grado]);

    // Volver a la vista por docente con el botón
    await page.locator('.cd-vista[data-vista="resumen"]').click();
    await expect(page.locator('#tabla-carga-resumen')).toBeVisible();

    // La dirección recuerda la pestaña: al recargar sigue en Carga docente
    await page.reload();
    await expect(seccion).toBeVisible();
  });
}
