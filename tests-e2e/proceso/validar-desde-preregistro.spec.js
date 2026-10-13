// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar, registrarEstudiante, preregistrar, ordenPendiente } from './ayudantes-proceso.js';

// Botón "Validar" en la tabla de Pre-Registro: lleva a Inscripción con la validación
// del voucher de esa solicitud ya abierta (inscripcion?orden=ID), sin buscarla.
test.describe('Validar voucher desde Pre-Registro', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('el botón Validar abre la validación de esa solicitud en Inscripción', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID);
    const idOrden = ordenPendiente(estudianteID);

    await page.goto('preregistro');
    await page.locator(`a.btn-validar-voucher[href="inscripcion?orden=${idOrden}"]`).click();

    await expect(page).toHaveURL(/inscripcion$/);
    await expect(page.locator('#modalValidarVoucher')).toBeVisible();
    await expect(page.locator('#detalleIdOrdenPago')).toHaveValue(String(idOrden));
  });
});
