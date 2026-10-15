// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, estudianteInscrito } from './ayudantes-proceso.js';

// Historial de Pagos: botón "Ver en Matriculados" que abre Matriculados filtrado por el C.I.

test('desde el Historial de Pagos se abre Matriculados con el estudiante', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  await page.goto('historialpagos');
  await page.locator('#valorBusquedaHistorial').fill(ci);
  await page.locator('#btnBuscarHistorial').click();
  const boton = page.locator('#historialIrMatriculados');
  await expect(boton).toBeVisible({ timeout: 15_000 });
  await expect(boton).toHaveAttribute('href', 'matriculados?buscar=' + ci);

  await boton.click();
  await expect(page).toHaveURL(new RegExp('matriculados\\?buscar=' + ci + '$'));
  await expect(page.locator('#tablaMatriculados tbody tr').filter({ hasText: ci })).toHaveCount(1, { timeout: 15_000 });
});

test('en Matriculados el botón "Órdenes" (fuera del menú) abre las órdenes del estudiante', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  await page.goto('matriculados?buscar=' + ci);
  const fila = page.locator('#tablaMatriculados tbody tr').filter({ hasText: ci });
  await expect(fila).toHaveCount(1, { timeout: 15_000 });
  // Visible directamente en la fila, no dentro del menú del engranaje
  const boton = fila.locator('a.mt-btn-ordenes');
  await expect(boton).toBeVisible();
  await expect(fila.locator('.mt-acciones .dropdown-item', { hasText: 'Órdenes de pago' })).toHaveCount(0);

  await boton.click();
  await expect(page).toHaveURL(/ordenespago\?ci=/);
  await expect(page.locator('#op_cuerpo tr .op-tipo-matricula')).toHaveCount(1, { timeout: 15_000 });
});
