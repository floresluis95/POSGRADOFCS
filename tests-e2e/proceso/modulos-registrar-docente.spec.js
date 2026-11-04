// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar } from './ayudantes-proceso.js';

// Módulos > Editar módulo > "¿No está el docente? Registrar nuevo docente": lleva a Docentes
// con el formulario abierto y, al guardar, vuelve a Módulos con el mismo programa y el
// docente nuevo ya disponible para asignar.

test('desde Módulos se registra un docente nuevo y se vuelve con el programa', async ({ page }) => {
  const programaID = String(valor(`SELECT ProgramaId FROM modulos GROUP BY ProgramaId ORDER BY ProgramaId LIMIT 1`));
  const especialidad = String(valor(`SELECT Nombre FROM especialidad WHERE Estado = 1 ORDER BY EspecialidadID LIMIT 1`));
  const ci = unico();

  await ingresar(page, 'adm');
  await page.goto('modulos');
  await page.evaluate((id) => jQuery('#selectPrograma').val(id).trigger('change').trigger({ type: 'select2:select', params: { data: { id } } }), programaID);
  await page.locator('.btn-editar-modulo').first().click();
  await expect(page.locator('#modalEditarModulo')).toBeVisible();

  // Botón -> confirmación -> Docentes con el formulario abierto y el aviso
  await page.locator('#modalEditarModulo .btn-ir-registrar-docente').click();
  await expect(page.locator('.swal2-popup')).toContainText('Registrar nuevo docente');
  await page.locator('.swal2-confirm').click();
  await expect(page).toHaveURL(/docentes$/);
  await expect(page.locator('#ModalInsertarDocente')).toBeVisible();
  await expect(page.locator('#avisoVolverModulos')).toBeVisible();
  await expect(page.locator('#btnVolverModulos')).toBeVisible();

  await page.locator('#inputCi').fill(ci);
  await page.locator('#selectExpedido').selectOption('OR');
  await page.locator('#inputNombres').fill('DOCENTE');
  await page.locator('#apaterno').fill('DESDE MODULOS');
  await page.locator('#fechaNacimiento').fill('1980-01-01');
  await page.locator('#matriculaNacional').fill(`MN-${ci}`);
  await page.locator('#especialidad').selectOption({ label: especialidad });
  await page.locator('#emailInput').fill(`mod${ci}@correo-prueba.com`);
  await page.locator('#inputCelular').fill('70000003');
  await page.locator('#formNuevoDocente button[type="submit"]').click();

  await expect(page.locator('.swal-modal')).toContainText('Ahora puede asignarlo', { timeout: 10_000 });
  await page.locator('.swal-button--confirm').click();

  // De vuelta en Módulos con el mismo programa y el docente nuevo en la lista
  await expect(page).toHaveURL(/modulos$/);
  await expect(page.locator('#selectPrograma')).toHaveValue(programaID);
  await expect(page.locator('.btn-editar-modulo').first()).toBeVisible();
  const docenteID = String(valor(`SELECT DocenteID FROM docente WHERE Ci = '${ci}'`));
  await expect(page.locator(`#editDocenteID option[value="${docenteID}"]`)).toHaveCount(1);

  // Al entrar después a Docentes por el menú ya no se abre el formulario solo
  await page.goto('docentes');
  await expect(page.locator('#avisoVolverModulos')).toBeHidden();
  await expect(page.locator('#ModalInsertarDocente')).toBeHidden();
});
