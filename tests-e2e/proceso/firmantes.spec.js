// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax } from './ayudantes-proceso.js';

// Responsables de la firma: se guardan en MAYÚSCULAS (con tildes y Ñ), como el resto de
// los nombres del sistema, y el mismo nombre escrito distinto no se duplica.
test.describe('Responsables de la firma en mayúsculas', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('se guarda en mayúsculas, sin espacios de más y sin duplicarse', async ({ page }) => {
    const sufijo = unico();
    const r = await ajax(page, 'ordenpago.ajax.php', { accion: 'registrarFirmante', nombre: `  maría   peña ${sufijo} ` });
    expect(r.success, r.mensaje).toBe(true);
    expect(r.nombre).toBe(`MARÍA PEÑA ${sufijo}`);
    expect(valor(`SELECT Nombre FROM firmante WHERE IdFirmante = ${r.idFirmante}`)).toBe(`MARÍA PEÑA ${sufijo}`);

    // Escrito de otra forma es el mismo firmante
    const otraVez = await ajax(page, 'ordenpago.ajax.php', { accion: 'registrarFirmante', nombre: `María Peña ${sufijo}` });
    expect(String(otraVez.idFirmante)).toBe(String(r.idFirmante));
    expect(valor(`SELECT COUNT(*) FROM firmante WHERE Nombre = 'MARÍA PEÑA ${sufijo}'`)).toBe('1');
  });
});
