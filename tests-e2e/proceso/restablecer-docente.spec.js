// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar } from './ayudantes-proceso.js';

// Docentes > Ver detalle > Restablecer contraseña. La página usa SweetAlert v1 (swal); el
// botón usaba Swal.fire (SweetAlert2, que aquí no se carga) y no hacía nada.

test('el ADM restablece la contraseña de un docente desde su detalle', async ({ page }) => {
  // Un docente con usuario que NO sea el del usuario de prueba prueba_doc (se le cambiaría la clave)
  const docenteDePrueba = String(valor(`SELECT DocenteID FROM usuario WHERE Usuario = 'prueba_doc'`));
  const docenteID = String(valor(`SELECT DocenteID FROM usuario WHERE DocenteID IS NOT NULL AND DocenteID <> ${docenteDePrueba} ORDER BY DocenteID LIMIT 1`));
  const antes = valor(`SELECT Password FROM usuario WHERE DocenteID = ${docenteID}`);

  const errores = [];
  page.on('pageerror', (e) => { if (/Swal|swal/.test(e.message)) errores.push(e.message); });
  await ingresar(page, 'adm');
  await page.goto('docentes');
  await page.locator(`.btnVerDetalleDocente[data-docente-id="${docenteID}"]`).click();
  const boton = page.locator('#btnRestablecerPasswordDocenteDetalle');
  await expect(boton).toBeVisible();
  await boton.click();

  // Confirmación y luego la contraseña nueva, que queda visible en el detalle
  await expect(page.locator('.swal-modal')).toContainText('¿Restablecer contraseña?');
  await page.locator('.swal-button--confirm').click();
  await expect(page.locator('.swal-modal')).toContainText('Nueva contraseña:', { timeout: 10_000 });
  const nueva = (await page.locator('#detalleDocentePassword').textContent() || '').trim();
  expect(nueva.length).toBeGreaterThanOrEqual(8);
  expect(valor(`SELECT Password FROM usuario WHERE DocenteID = ${docenteID}`)).not.toBe(antes);
  expect(errores).toEqual([]);
});
