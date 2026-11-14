// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar } from './ayudantes-proceso.js';

// Pre-Registro: si el programa aún no tiene cuenta de matrícula, se registra ahí mismo
// (debajo de "Cuenta de depósito") sin salir de la pantalla ni perder lo llenado.

const COLUMNAS = 'NombrePrograma,GradoAcademico,Codigo,Sigla,DuracionMeses,Modulos,FechaInicio,Sede,Costo,CostoMatricula,Detalle,Estado,Version,NumeroTramite';

/** Copia de un programa existente, sin cuentas. */
function programaSinCuentas(u) {
  sql(`INSERT INTO programa (${COLUMNAS})
       SELECT CONCAT('PRUEBA CUENTA RAPIDA ', '${u}'), GradoAcademico, Codigo, Sigla, DuracionMeses, Modulos, FechaInicio, Sede, Costo, CostoMatricula, Detalle, Estado, Version, NumeroTramite
       FROM programa ORDER BY ProgramaID LIMIT 1`);
  return String(valor(`SELECT MAX(ProgramaID) FROM programa`));
}

/** Elige el programa en el combo de Pre-Registro (dispara la carga de sus cuentas). */
async function elegirPrograma(page, programaID) {
  // Directo al Paso 3 (orden de pago), donde está "Cuenta de depósito"
  await page.evaluate(() => jQuery('#modalPasoPago').modal('show'));
  await expect(page.locator('#modalPasoPago')).toBeVisible();
  await page.evaluate((id) => {
    const $p = jQuery('#programa');
    if (!$p.find(`option[value="${id}"]`).length) $p.append(new Option('PRUEBA', id));
    $p.val(id).trigger('change');
  }, programaID);
}

test.describe('Registrar la cuenta del programa desde Pre-Registro', () => {
  let programaID = '';
  test.afterEach(() => {
    if (programaID) {
      sql(`DELETE FROM programacuenta WHERE ProgramaID = ${programaID}; DELETE FROM programa WHERE ProgramaID = ${programaID};`);
    }
  });

  test('ADM: sin cuenta, la registra ahí mismo y queda elegida', async ({ page }) => {
    const u = unico();
    programaID = programaSinCuentas(u);

    await ingresar(page, 'adm');
    await page.goto('preregistro');
    await elegirPrograma(page, programaID);

    const caja = page.locator('#cuentaPago + .cr-caja');
    await expect(caja).toContainText('aún no tiene cuenta de matrícula');
    await caja.getByRole('button', { name: 'Registrarla aquí' }).click();

    // Sin número no guarda; Enter no envía el formulario del Pre-Registro
    await caja.locator('.cr-guardar').click();
    await expect(caja.locator('.cr-mensaje')).toContainText('Escriba el número de cuenta');

    await caja.locator('.cr-numero').fill(`10000${u}`);
    await caja.locator('.cr-banco').fill('Banco Union');
    await caja.locator('.cr-numero').press('Enter');

    await expect(caja).toContainText(`Cuenta 10000${u} registrada y elegida`);
    await expect(page).toHaveURL(/preregistro$/);
    const elegida = await page.locator('#cuentaPago').inputValue();
    expect(elegida).not.toBe('');
    expect(valor(`SELECT CONCAT(TipoCuenta, '|', NumeroCuenta, '|', Banco, '|', Principal) FROM programacuenta WHERE ProgramaCuentaID = ${elegida}`))
      .toBe(`MATRICULA|10000${u}|BANCO UNION|1`);

    // Ya con cuenta, el enlace ofrece registrar otra; repetir el número avisa
    await caja.getByRole('button', { name: /Registrar otra/ }).click();
    await caja.locator('.cr-numero').fill(`10000${u}`);
    await caja.locator('.cr-guardar').click();
    await expect(caja.locator('.cr-mensaje.cr-error')).toContainText('ya está registrada');
    expect(valor(`SELECT COUNT(*) FROM programacuenta WHERE ProgramaID = ${programaID}`)).toBe('1');
  });

  test('Auxiliar: no puede registrarla, ve a quién pedírsela', async ({ page }) => {
    programaID = programaSinCuentas(unico());

    await ingresar(page, 'aux');
    await page.goto('preregistro');
    await elegirPrograma(page, programaID);

    const caja = page.locator('#cuentaPago + .cr-caja');
    await expect(caja).toContainText('Pida a Secretaría o Administración');
    await expect(caja.locator('.cr-abrir')).toHaveCount(0);
  });
});
