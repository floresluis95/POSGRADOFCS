// @ts-check
import { test, expect } from '@playwright/test';
import { valor, sql, ingresar } from './ayudantes-proceso.js';

// Usuarios > Editar: el ADM cambia el rol de una cuenta del personal. Quien tenía la sesión
// abierta la pierde en su siguiente clic y al volver a ingresar entra con el rol nuevo.
// El propio rol no se puede cambiar.

const idUsuario = (usuario) => String(valor(`SELECT ID FROM usuario WHERE Usuario = '${usuario}'`));
const rol = (usuario) => valor(`SELECT Tipo FROM usuario WHERE Usuario = '${usuario}'`);

test('el ADM cambia el rol de un auxiliar a secretaría y su sesión se renueva', async ({ browser }) => {
  sql(`UPDATE usuario SET Tipo = 'AUX', Estado = 1 WHERE Usuario = 'prueba_aux'`);
  try {
    // El auxiliar está conectado antes del cambio
    const aux = await (await browser.newContext()).newPage();
    await ingresar(aux, 'aux');
    await aux.goto('programas');
    await expect(aux.locator('section.pn-bienvenida')).toBeVisible(); // el AUX no tiene Programas

    // El ADM le cambia el rol desde Editar
    const adm = await (await browser.newContext()).newPage();
    await ingresar(adm, 'adm');
    await adm.goto('usuario');
    await adm.locator(`.btnEditarUsuario[IdUsuario="${idUsuario('prueba_aux')}"]`).click();
    await expect(adm.locator('#UECargo')).toHaveValue('AUX');
    await expect(adm.locator('#UECargo')).toBeEnabled();
    await adm.locator('#UECargo').selectOption('SEC');
    await adm.locator('#formEditarUsuario button[type="submit"]').click();
    await expect(adm.locator('.swal-modal')).toContainText('Su rol ahora es Secretaría', { timeout: 10_000 });
    expect(rol('prueba_aux')).toBe('SEC');

    // Su sesión abierta ya no sirve: al siguiente clic va al login
    await aux.goto('panel');
    await expect(aux).toHaveURL(/ingreso$/);
    // Al volver a ingresar tiene los permisos de Secretaría
    await ingresar(aux, 'aux');
    await aux.goto('programas');
    await expect(aux.locator('section.pn-bienvenida')).toHaveCount(0);

    // El ADM no puede cambiar su propio rol: el campo está deshabilitado y el servidor lo rechaza
    await adm.goto('usuario');
    await adm.locator(`.btnEditarUsuario[IdUsuario="${idUsuario('prueba_adm')}"]`).click();
    await expect(adm.locator('#UECargo')).toBeDisabled();
    await adm.request.post('usuario', { form: {
      editaridusuario: idUsuario('prueba_adm'), editaridpersonal: '0', editarci: '0', editarapaterno: 'PRUEBA', editaramaterno: '', editarnombres: 'ADM',
      editardireccion: '', editarcelular: '', editartelefono: '', editarcorreo: '', editarcargo: 'SEC',
    } });
    expect(rol('prueba_adm')).toBe('ADM');
  } finally {
    sql(`UPDATE usuario SET Tipo = 'AUX' WHERE Usuario = 'prueba_aux'`);
  }
});

test('una cuenta dada de baja pierde la sesión abierta', async ({ page }) => {
  await ingresar(page, 'sec');
  sql(`UPDATE usuario SET Estado = 0 WHERE Usuario = 'prueba_sec'`);
  try {
    await page.goto('matriculados');
    await expect(page).toHaveURL(/ingreso$/);
  } finally {
    sql(`UPDATE usuario SET Estado = 1 WHERE Usuario = 'prueba_sec'`);
  }
});
