// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax } from './ayudantes-proceso.js';

// Programas > Administrar cuentas: corregir y borrar una cuenta registrada por error.
// Solo se borra si aún no tiene órdenes de pago; con órdenes, el tipo queda fijo.

test.describe('Cuentas del programa: editar y borrar', () => {
  test('se corrige y se borra una cuenta sin órdenes desde la ventana', async ({ page }) => {
    const programaID = String(valor(`SELECT ProgramaID FROM programa ORDER BY ProgramaID LIMIT 1`));
    const u = unico();
    const tipo = `PRUEBA${u}`;

    await ingresar(page, 'adm');
    await page.goto('programas');
    await page.evaluate((id) => jQuery(`.btnAbrirCuentasPrograma[data-idprograma="${id}"]`).first().trigger('click'), programaID);
    const ventana = page.locator('#gestionarCuentas');
    await expect(ventana).toBeVisible();

    // Agregar una cuenta (con el número mal escrito)
    await page.locator('#inputTipoCuenta').fill(tipo);
    await page.locator('#inputNumeroCuenta').fill(`111-${u}`);
    await page.locator('#btnGuardarCuentaPrograma').click();
    await page.locator('.swal2-confirm').click();
    const fila = ventana.locator('#tablaCuentasBody tr', { hasText: tipo });
    await expect(fila).toContainText(`111-${u}`);

    // Corregirla: los datos suben al formulario y se guarda la corrección
    await fila.locator('.btnEditarCuentaPrograma').click();
    await expect(page.locator('#btnGuardarCuentaPrograma')).toContainText('Guardar corrección');
    await expect(page.locator('#inputNumeroCuenta')).toHaveValue(`111-${u}`);
    await page.locator('#inputNumeroCuenta').fill(`222-${u}`);
    await page.locator('#inputBancoCuenta').fill('banco union');
    await page.locator('#btnGuardarCuentaPrograma').click();
    await expect(page.locator('.swal2-popup')).toContainText('se corrigió');
    await page.locator('.swal2-confirm').click();
    await expect(fila).toContainText(`222-${u}`);
    await expect(fila).toContainText('BANCO UNION');
    await expect(page.locator('#btnGuardarCuentaPrograma')).toContainText('Agregar cuenta');
    expect(valor(`SELECT COUNT(*) FROM programacuenta WHERE TipoCuenta = '${tipo}' AND NumeroCuenta = '222-${u}'`)).toBe('1');

    // Borrarla (pide confirmación)
    await fila.locator('.btnEliminarCuentaPrograma').click();
    await expect(page.locator('.swal2-popup')).toContainText('¿Borrar la cuenta?');
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('.swal2-popup')).toContainText('se borró');
    await page.locator('.swal2-confirm').click();
    await expect(fila).toHaveCount(0);
    expect(valor(`SELECT COUNT(*) FROM programacuenta WHERE TipoCuenta = '${tipo}'`)).toBe('0');
  });

  test('una cuenta con órdenes de pago no se borra ni cambia de tipo', async ({ page }) => {
    const cuentaUsada = valor(`SELECT ProgramaCuentaID FROM ordenpago WHERE ProgramaCuentaID IS NOT NULL LIMIT 1`);
    test.skip(!cuentaUsada, 'No hay cuentas con órdenes en la base de pruebas');
    const [programaID, tipo, numero, banco] = /** @type {string[]} */ ([
      valor(`SELECT ProgramaID FROM programacuenta WHERE ProgramaCuentaID = ${cuentaUsada}`),
      valor(`SELECT TipoCuenta FROM programacuenta WHERE ProgramaCuentaID = ${cuentaUsada}`),
      valor(`SELECT NumeroCuenta FROM programacuenta WHERE ProgramaCuentaID = ${cuentaUsada}`),
      valor(`SELECT IFNULL(Banco, '') FROM programacuenta WHERE ProgramaCuentaID = ${cuentaUsada}`),
    ]);

    await ingresar(page, 'adm');
    await page.goto('programas');
    await page.evaluate((id) => jQuery(`.btnAbrirCuentasPrograma[data-idprograma="${id}"]`).first().trigger('click'), programaID);
    // En la ventana: sin botón de borrar y, al editar, el tipo queda fijo
    const editar = page.locator(`.btnEditarCuentaPrograma[data-idcuenta="${cuentaUsada}"]`);
    await expect(editar).toBeVisible();
    await expect(page.locator(`.btnEliminarCuentaPrograma[data-idcuenta="${cuentaUsada}"]`)).toHaveCount(0);
    await editar.click();
    await expect(page.locator('#inputTipoCuenta')).toHaveJSProperty('readOnly', true);
    await expect(page.locator('#avisoTipoFijo')).toBeVisible();

    // El servidor también lo impide aunque se envíe a mano
    const borrar = await ajax(page, 'programacuenta.ajax.php', { accion: 'eliminar', ProgramaCuentaID: String(cuentaUsada) });
    expect(borrar.estado).toBe('en_uso');
    const otroTipo = await ajax(page, 'programacuenta.ajax.php', {
      accion: 'editar', ProgramaCuentaID: String(cuentaUsada), TipoCuenta: 'OTRO TIPO', NumeroCuenta: numero, Banco: banco,
    });
    expect(otroTipo.estado).toBe('en_uso');
    expect(valor(`SELECT TipoCuenta FROM programacuenta WHERE ProgramaCuentaID = ${cuentaUsada}`)).toBe(tipo);

    // Sí se puede corregir el banco (se deja como estaba)
    const corregir = await ajax(page, 'programacuenta.ajax.php', {
      accion: 'editar', ProgramaCuentaID: String(cuentaUsada), TipoCuenta: tipo, NumeroCuenta: numero, Banco: banco,
    });
    expect(corregir.estado).toBe('exitoso');
  });

  test('al borrar la Principal, otra cuenta del mismo tipo pasa a serlo', async ({ page }) => {
    const programaID = String(valor(`SELECT ProgramaID FROM programa ORDER BY ProgramaID LIMIT 1`));
    const tipo = `PRINC${unico()}`;
    await ingresar(page, 'adm');
    for (const n of ['A', 'B']) {
      const r = await ajax(page, 'programacuenta.ajax.php', { accion: 'registrar', ProgramaID: programaID, TipoCuenta: tipo, NumeroCuenta: `${n}-1`, Banco: '' });
      expect(r.estado).toBe('exitoso');
    }
    const primera = valor(`SELECT ProgramaCuentaID FROM programacuenta WHERE TipoCuenta = '${tipo}' AND Principal = 1`);
    const r = await ajax(page, 'programacuenta.ajax.php', { accion: 'eliminar', ProgramaCuentaID: String(primera) });
    expect(r.estado).toBe('exitoso');
    expect(valor(`SELECT NumeroCuenta FROM programacuenta WHERE TipoCuenta = '${tipo}' AND Principal = 1`)).toBe('B-1');
    await ajax(page, 'programacuenta.ajax.php', {
      accion: 'eliminar', ProgramaCuentaID: String(valor(`SELECT ProgramaCuentaID FROM programacuenta WHERE TipoCuenta = '${tipo}'`)),
    });
  });

  test('el Auxiliar no puede editar ni borrar cuentas', async ({ page }) => {
    await ingresar(page, 'aux');
    const id = String(valor(`SELECT ProgramaCuentaID FROM programacuenta LIMIT 1`));
    for (const accion of ['editar', 'eliminar']) {
      const r = await ajax(page, 'programacuenta.ajax.php', { accion, ProgramaCuentaID: id, TipoCuenta: 'X', NumeroCuenta: 'X', Banco: '' });
      expect(r.estado).not.toBe('exitoso');
    }
  });
});
