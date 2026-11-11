// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, ingresar, ajax } from './ayudantes-proceso.js';

// Préstamo del ejemplar físico de una tesis: el estudiante solicita, el ADM reserva, entrega y
// recibe la devolución. Un ejemplar por tesis: mientras está prestada no se entrega a otro.

const SQL_TESIS = `SELECT IdTrabajo FROM trabajogrado WHERE FechaAprobacion IS NOT NULL AND TRIM(TituloTrabajo) <> '' ORDER BY IdTrabajo LIMIT 1`;

test('solicitar, reservar, entregar y devolver una tesis', async ({ browser }) => {
  test.setTimeout(120_000);
  const idTrabajo = String(valor(SQL_TESIS));
  expect(idTrabajo, 'la base de pruebas debe tener una tesis aprobada').not.toBe('null');
  sql(`DELETE FROM prestamotesis WHERE IdTrabajo = ${idTrabajo}`);

  // Estudiante: solicita desde el catálogo
  const est = await (await browser.newContext()).newPage();
  await ingresar(est, 'est');
  await est.goto('bibliotecatesis');
  const ficha = est.locator(`.bt-tesis[data-id="${idTrabajo}"]`);
  await ficha.locator('.bt-solicitar').click();
  await est.getByRole('button', { name: 'Sí, solicitar' }).click();
  await expect(ficha).toContainText('Solicitud enviada', { timeout: 15_000 });

  // No puede pedir la misma dos veces
  const repetida = await ajax(est, 'bibliotecatesis.ajax.php', { accion: 'solicitar', idTrabajo });
  expect(repetida.success).toBe(false);

  // El estudiante no puede entregarse la tesis a sí mismo
  const idPrestamo = String(valor(`SELECT IdPrestamo FROM prestamotesis WHERE IdTrabajo = ${idTrabajo} AND Estado = 'SOLICITADO'`));
  const r = await est.request.post('ajax/bibliotecatesis.ajax.php', { form: { accion: 'entregar', idPrestamo } });
  expect(r.status()).toBe(403);

  // ADM: la solicitud aparece; reserva
  const adm = await (await browser.newContext()).newPage();
  await ingresar(adm, 'adm');
  await adm.goto('index.php?action=bibliotecatesis&vista=prestamos');
  await adm.locator(`[data-accion="reservar"][data-id="${idPrestamo}"]`).click();
  await adm.getByRole('button', { name: 'Reservar', exact: true }).click();
  await expect(adm.locator('.swal2-title')).toContainText('reservado', { timeout: 15_000 });
  expect(valor(`SELECT Estado FROM prestamotesis WHERE IdPrestamo = ${idPrestamo}`)).toBe('APROBADO');

  // El estudiante lo ve en Mis préstamos
  await est.goto('index.php?action=bibliotecatesis&vista=misprestamos');
  await expect(est.locator('.bt-prestamo').first()).toContainText('RESERVADA');

  // ADM entrega: fecha límite = hoy + plazo
  await adm.goto('index.php?action=bibliotecatesis&vista=prestamos');
  await adm.locator(`[data-accion="entregar"][data-id="${idPrestamo}"]`).click();
  await adm.getByRole('button', { name: 'Sí, entregar' }).click();
  await expect(adm.locator('.swal2-title')).toContainText('Préstamo registrado', { timeout: 15_000 });
  const plazo = valor(`SELECT COALESCE((SELECT Valor FROM configuracion WHERE Clave = 'PLAZO_PRESTAMO_TESIS'), 7)`);
  expect(valor(`SELECT DATEDIFF(FechaLimite, CURDATE()) FROM prestamotesis WHERE IdPrestamo = ${idPrestamo}`)).toBe(String(plazo));

  // Ocupada: otra solicitud (del docente) queda en espera y no se puede entregar
  const doc = await (await browser.newContext()).newPage();
  await ingresar(doc, 'doc');
  await doc.goto('bibliotecatesis');
  await expect(doc.locator(`.bt-tesis[data-id="${idTrabajo}"] .bt-disp`)).toContainText('PRESTADA');
  const delDocente = await ajax(doc, 'bibliotecatesis.ajax.php', { accion: 'solicitar', idTrabajo });
  expect(delDocente.success).toBe(true);
  const idDocente = String(valor(`SELECT IdPrestamo FROM prestamotesis WHERE IdTrabajo = ${idTrabajo} AND TipoSolicitante = 'DOC' AND Estado = 'SOLICITADO'`));
  const entregaDoble = await ajax(adm, 'bibliotecatesis.ajax.php', { accion: 'entregar', idPrestamo: idDocente });
  expect(entregaDoble.success).toBe(false);
  expect(entregaDoble.mensaje).toContain('otra persona');

  // ADM registra la devolución: vuelve a estar disponible
  await adm.goto('index.php?action=bibliotecatesis&vista=prestamos&lista=prestadas');
  await adm.locator(`[data-accion="devolver"][data-id="${idPrestamo}"]`).click();
  await adm.locator('.swal2-input').fill('Ejemplar en buen estado');
  await adm.getByRole('button', { name: 'Registrar devolución' }).click();
  await expect(adm.locator('.swal2-title')).toContainText('Devolución registrada', { timeout: 15_000 });
  expect(valor(`SELECT CONCAT(Estado, '|', Motivo) FROM prestamotesis WHERE IdPrestamo = ${idPrestamo}`)).toBe('DEVUELTO|Ejemplar en buen estado');

  // El docente cancela su solicitud en espera
  await doc.goto('index.php?action=bibliotecatesis&vista=misprestamos');
  await doc.locator(`.bt-cancelar[data-id="${idDocente}"]`).click();
  await doc.getByRole('button', { name: 'Sí, cancelar' }).click();
  await expect(doc.locator('.swal2-title')).toContainText('cancelada', { timeout: 15_000 });
  expect(valor(`SELECT Estado FROM prestamotesis WHERE IdPrestamo = ${idDocente}`)).toBe('CANCELADO');

  sql(`DELETE FROM prestamotesis WHERE IdTrabajo = ${idTrabajo}`);
});
