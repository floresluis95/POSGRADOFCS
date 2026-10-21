// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Certificados sin documento (Pro Biblioteca y los que agregue el ADM): no se genera nada,
// solo se registra el pago (nombre, voucher, fecha de pago y fecha de registro) en una
// inscripción. Salen en Registro de documentos, en el recibo de certificados de ESE programa
// y en la cuenta del estudiante, separados por programa.

const idProBiblioteca = () => valor("SELECT IdCertificadoSD FROM certificadosindocumento WHERE Nombre = 'PRO BIBLIOTECA'");
const otroPrograma = () => valor(`SELECT ProgramaID FROM programa WHERE ProgramaID <> ${PROGRAMA_ID} LIMIT 1`);

test('se registra desde Registro de documentos y queda en el programa abierto', async ({ page }) => {
  await ingresar(page, 'adm');
  const precio = await ajax(page, 'certificado.ajax.php', { accion: 'guardarSinDocumento', id: String(idProBiblioteca()), nombre: 'pro biblioteca', monto: '25', activo: '1' });
  expect(precio.success, precio.mensaje).toBe(true);

  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const largo = '1501' + String(unico()).slice(-4);

  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  await expect(page.locator('#rd-estudiante')).toBeVisible({ timeout: 15_000 });
  await page.locator('#btn-registrar-sd').click();
  await expect(page.locator('#ventana-sd')).toHaveClass(/abierta/);
  await page.locator('#sd-certificado').selectOption(String(idProBiblioteca()));
  await expect(page.locator('#sd-monto')).toHaveValue('25.00');
  await page.locator('#sd-voucher_largo').fill(largo);
  await page.locator('#sd-fecha-pago').fill('2026-10-01');
  await page.locator('#btn-guardar-sd').click();
  await expect(page.locator('.swal2-popup')).toContainText('Certificado registrado');

  const fila = page.locator('#tabla-estudiante-body tr', { hasText: largo });
  await expect(fila).toContainText('PRO BIBLIOTECA', { timeout: 15_000 });
  await expect(fila).toContainText('Pagó: 01/10/2026');
  await expect(fila).toContainText('Registrado');
  await expect(fila.locator('[data-imprimir], [data-aprobar]')).toHaveCount(0);
  expect(valor(`SELECT CONCAT(TipoDocumento, '|', NombreCertificado, '|', ProgramaID, '|', FechaPago, '|', Monto) FROM documentoemitido WHERE NumeroVaucher = '${largo}'`))
    .toBe(`SIN_DOCUMENTO|PRO BIBLIOTECA|${PROGRAMA_ID}|2026-10-01|25.00`);

  // Editar: corrige la fecha de pago; sin "Guardar e imprimir" (no hay documento)
  await fila.locator('[data-editar]').click();
  await expect(page.locator('#btn-guardar-imprimir')).toBeHidden();
  await page.locator('#reimp-fechaPago').fill('2026-10-02');
  await page.locator('#btn-guardar-documento').click();
  await expect(page.locator('.swal2-popup')).toContainText('Cambios guardados');
  await expect.poll(() => valor(`SELECT FechaPago FROM documentoemitido WHERE NumeroVaucher = '${largo}'`)).toBe('2026-10-02');

  // El voucher entra en la regla de voucher único
  const otraVez = await ajax(page, 'certificado.ajax.php', {
    accion: 'registrarSinDocumento', estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID),
    idCertificadoSD: String(idProBiblioteca()), vaucher: largo, monto: '25', fechaPago: '2026-10-01',
  });
  expect(otraVez.success).toBe(false);
  expect(otraVez.mensaje).toContain('ya fue registrado en un certificado de');
});

test('estudiante en dos programas: cada certificado en su programa, su recibo y su cuenta', async ({ page, browser }) => {
  const estudianteID = Number(valor("SELECT EstudianteID FROM usuario WHERE Usuario = 'prueba_est'"));
  const programa2 = Number(otroPrograma());
  test.skip(!estudianteID || !programa2, 'hace falta prueba_est y un segundo programa');
  // prueba_est inscrito también en el segundo programa (solo para esta prueba: al final se
  // borra, para no cambiar lo que ven las demás pruebas del estudiante)
  const yaInscrito = valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${programa2}`) !== '0';
  sql(`INSERT INTO estudianteprograma (EstudianteID, ProgramaID, costomatricula, nvauchermatricula, FechaInscripcion, Estado)
       SELECT ${estudianteID}, ${programa2}, 0, 'SIN MATRÍCULA', CURDATE(), 'ACTIVO'
       FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${programa2})`);

  await ingresar(page, 'adm');
  const registrar = (programaID, voucher, fecha) => ajax(page, 'certificado.ajax.php', {
    accion: 'registrarSinDocumento', estudianteID: String(estudianteID), programaID: String(programaID),
    idCertificadoSD: String(idProBiblioteca()), vaucher: voucher, monto: '25', fechaPago: fecha,
  });
  const v1 = '1502' + String(unico()).slice(-4);
  const v2 = '1503' + String(unico()).slice(-4);
  expect((await registrar(PROGRAMA_ID, v1, '2026-10-03')).success).toBe(true);
  expect((await registrar(programa2, v2, '2026-10-04')).success).toBe(true);
  // Un programa en el que no está inscrito: no se acepta
  const ajeno = await ajax(page, 'certificado.ajax.php', {
    accion: 'registrarSinDocumento', estudianteID: String(estudianteID), programaID: '999999',
    idCertificadoSD: String(idProBiblioteca()), vaucher: v2 + '9', monto: '25', fechaPago: '2026-10-04',
  });
  expect(ajeno.success).toBe(false);

  // Recibo de cada programa: solo su certificado, con fecha de pago y de registro
  const recibo1 = await (await page.request.get(`vistas/componentes/recibo-pago-certificados.php?estudianteID=${estudianteID}&programaID=${PROGRAMA_ID}`)).text();
  expect(recibo1).toContain(v1);
  expect(recibo1).not.toContain(v2);
  expect(recibo1).toContain('PRO BIBLIOTECA');
  expect(recibo1).toContain('03/10/2026');
  expect(recibo1).toContain('F. registro');
  const recibo2 = await (await page.request.get(`vistas/componentes/recibo-pago-certificados.php?estudianteID=${estudianteID}&programaID=${programa2}`)).text();
  expect(recibo2).toContain(v2);
  expect(recibo2).not.toContain(v1);

  // Cuenta del estudiante: un bloque por programa
  const est = await browser.newPage();
  await ingresar(est, 'est');
  await est.goto('miscertificados');
  await expect(est.locator(`.mc-programa-grupo[data-programa="${PROGRAMA_ID}"]`)).toContainText(v1);
  await expect(est.locator(`.mc-programa-grupo[data-programa="${PROGRAMA_ID}"]`)).not.toContainText(v2);
  await expect(est.locator(`.mc-programa-grupo[data-programa="${programa2}"]`)).toContainText(v2);
  await expect(est.locator(`.mc-programa-grupo[data-programa="${programa2}"]`)).toContainText('04/10/2026');
  await est.close();

  sql(`DELETE FROM documentoemitido WHERE NumeroVaucher IN ('${v1}', '${v2}')`);
  if (!yaInscrito) sql(`DELETE FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${programa2}`);
});

test('la lista de otros certificados se mantiene en Costos de certificados (solo ADM)', async ({ page, browser }) => {
  await ingresar(page, 'adm');
  const nombre = 'CERTIFICADO PRUEBA ' + unico();

  // Agregar desde la pantalla, con precio
  await page.goto('costoscertificados');
  await page.locator('#btnModoEdicion').click(); // al entrar está bloqueado
  await expect(page.locator('#tabla-sd-catalogo')).toContainText('PRO BIBLIOTECA', { timeout: 15_000 });
  await page.locator('#sdc-nombre').fill(nombre.toLowerCase());
  await page.locator('#sdc-monto').fill('10');
  await page.locator('#sdc-guardar').click();
  await expect(page.locator('.swal2-popup')).toContainText('Certificado agregado');
  const fila = page.locator('#tabla-sd-catalogo tr', { hasText: nombre });
  await expect(fila).toContainText('Bs. 10,00');

  // Editar: cambiar el precio y desactivarlo
  await fila.locator('[data-editar-sd]').click();
  await page.locator('#sdc-monto').fill('12.5');
  await page.locator('#sdc-activo').uncheck();
  await page.locator('#sdc-guardar').click();
  await expect(fila).toContainText('Inactivo', { timeout: 15_000 });
  expect(valor(`SELECT CONCAT(Monto, '|', Activo) FROM certificadosindocumento WHERE Nombre = '${nombre}'`)).toBe('12.50|0');
  // Repetido (aunque sea en minúsculas): no
  expect((await ajax(page, 'certificado.ajax.php', { accion: 'guardarSinDocumento', nombre: nombre.toLowerCase(), monto: '1' })).success).toBe(false);

  // Secretaría: no abre Costos de certificados ni puede cambiar la lista
  const sec = await browser.newPage();
  await ingresar(sec, 'sec');
  await sec.goto('costoscertificados');
  await expect(sec.locator('#cardOtrosCertificados')).toHaveCount(0);
  expect((await ajax(sec, 'certificado.ajax.php', { accion: 'guardarSinDocumento', nombre: 'NO DEBE', monto: '1' })).success).toBe(false);
  await sec.close();
});
