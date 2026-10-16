// @ts-check
import { test, expect } from '@playwright/test';
import { valor, sql, unico, ingresar, ajax, estudianteInscrito, pagarCuota, IMAGEN_VOUCHER } from './ayudantes-proceso.js';

// El estudiante genera la orden de su siguiente cuota (Mis órdenes de pago). La orden
// queda marcada como suya y al imprimirla lleva la firma del coordinador y el sello
// (Configuración); las del personal no.

/** Usuario EST (contraseña Prueba123) para un estudiante recién inscrito. */
function crearUsuarioEstudiante(estudianteID) {
  const usuario = 'cuota_' + unico().slice(-8);
  sql(`INSERT INTO usuario (EstudianteID, Usuario, Password, Estado, Tipo)
       SELECT ${estudianteID}, '${usuario}', Password, '1', 'EST' FROM usuario WHERE Usuario = 'prueba_est'`);
  return usuario;
}

async function ingresarComo(page, usuario) {
  await page.goto('ingreso');
  await page.getByLabel('Usuario').fill(usuario);
  await page.getByLabel('Contraseña', { exact: true }).fill('Prueba123');
  await page.locator('#btnLogin').click();
  await expect(page).toHaveURL(/panel$/);
}

/** Cuántas imágenes distintas trae el PDF (logos, firma, sello). */
async function imagenesDelPdf(page, idOrden) {
  const pdf = (await (await page.request.get(`vistas/componentes/orden-pago-pdf.php?idordenpago=${idOrden}`)).body()).toString('latin1');
  return (pdf.match(/\/Subtype \/Image/g) || []).length;
}

test('el estudiante genera su cuota y la orden sale con firma y sello', async ({ page, browser }) => {
  test.setTimeout(90_000);
  await ingresar(page, 'adm');

  // Configuración: firma y sello (imágenes de prueba)
  await page.goto('autoridades');
  await page.locator('.input-imagen-firma[data-clave="FIRMA"]').setInputFiles(IMAGEN_VOUCHER);
  await expect(page.locator('#vista_FIRMA img')).toBeVisible({ timeout: 15_000 });
  await page.locator('.input-imagen-firma[data-clave="SELLO"]').setInputFiles(IMAGEN_VOUCHER);
  await expect(page.locator('#vista_SELLO img')).toBeVisible({ timeout: 15_000 });

  const inscrito = await estudianteInscrito(page); // Plan Regular de 10 cuotas
  const usuario = crearUsuarioEstudiante(inscrito.estudianteID);
  const cuotaPlan = Number(valor(`SELECT MontoCuota FROM pagoprograma WHERE idInscripcion = ${inscrito.idInscripcion}`));

  const est = await browser.newPage();
  await ingresarComo(est, usuario);
  await est.goto('misordenespago');
  const recuadro = est.locator('#lista_generar_cuota .mop-pp');
  await expect(recuadro).toContainText('Cuota 1 de 10', { timeout: 15_000 });
  await recuadro.locator('.btn-generar-cuota').click();
  await expect(est.locator('.swal2-popup')).toContainText('Tesoro Universitario');
  await est.locator('.swal2-confirm').click();
  await expect(est.locator('.swal2-popup')).toContainText('Orden generada', { timeout: 15_000 });
  await est.locator('.swal2-cancel').click();

  const [idOrden, monto, generada, firma] = String(valor(
    `SELECT CONCAT_WS('|', IdOrdenPago, MontoTotal, GeneradaPorEstudiante, Firma) FROM ordenpago
     WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`,
  )).split('|');
  expect(Number(monto)).toBe(cuotaPlan);
  expect(generada).toBe('1');
  const coordinador = valor("SELECT UPPER(Nombre) FROM autoridad WHERE Cargo = 'COORDINADOR'");
  if (coordinador) expect(firma).toBe(coordinador);

  // Ya tiene una por pagar: no se ofrece otra y el servidor la rechaza
  await expect(est.locator('#lista_por_pagar .mop-pp')).toHaveCount(1, { timeout: 15_000 });
  await expect(est.locator('#generar_cuota')).toBeHidden();
  const otra = await ajax(est, 'ordenpago.ajax.php', { accion: 'generarMiCuota', idinscripcion: String(inscrito.idInscripcion) });
  expect(otra.success).toBe(false);

  // La orden del estudiante trae la firma y el sello; la misma orden marcada como del personal, no
  const conFirma = await imagenesDelPdf(est, idOrden);
  sql(`UPDATE ordenpago SET GeneradaPorEstudiante = 0 WHERE IdOrdenPago = ${idOrden}`);
  const sinFirma = await imagenesDelPdf(est, idOrden);
  // Firma y sello en el original y en la copia (cada PNG transparente lleva además su máscara)
  expect(conFirma - sinFirma).toBe(8);
  await est.close();
});

test('paga dos cuotas por adelantado: una orden por cuota, con su N° y numeradas', async ({ page, browser }) => {
  test.setTimeout(90_000);
  await ingresar(page, 'adm');
  const inscrito = await estudianteInscrito(page); // Plan Regular de 10 cuotas
  const usuario = crearUsuarioEstudiante(inscrito.estudianteID);

  const est = await browser.newPage();
  await ingresarComo(est, usuario);
  await est.goto('misordenespago');
  await est.locator('#lista_generar_cuota .btn-generar-cuota').click({ timeout: 15_000 });
  // Antes de confirmar se ve cada cuota y el total; cambia con la cantidad elegida
  const detalle = est.locator('#detalle_cuotas');
  await expect(detalle.locator('tr')).toHaveCount(2); // 1 cuota + total
  await expect(detalle).toContainText('Cuota 1 de 10');
  await est.locator('#cantidad_cuotas').selectOption('2');
  await expect(detalle.locator('tr')).toHaveCount(3);
  await expect(detalle).toContainText('Cuota 2 de 10');
  await expect(detalle.locator('.mop-detalle-total')).toContainText('2 órdenes');
  const cuotaPlan = Number(valor(`SELECT MontoCuota FROM pagoprograma WHERE idInscripcion = ${inscrito.idInscripcion}`));
  const totalEsperado = (cuotaPlan * 2).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  await expect(detalle.locator('.mop-detalle-total')).toContainText(totalEsperado);
  await est.locator('.swal2-confirm').click();
  await expect(est.locator('.swal2-popup')).toContainText('2 órdenes generadas', { timeout: 15_000 });
  await expect(est.locator('.swal2-popup a')).toHaveCount(2);
  await est.locator('.swal2-cancel').click();

  // Dos órdenes de cuota, cada una con su módulo
  expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA' AND GeneradaPorEstudiante = 1`)).toBe('2');
  expect(valor(`SELECT COUNT(DISTINCT opm.IdModulo) FROM ordenpagomodulo opm INNER JOIN ordenpago op ON op.IdOrdenPago = opm.IdOrdenPago
                WHERE op.idinscripcion = ${inscrito.idInscripcion}`)).toBe('2');

  // En la tabla: "Cuota N de 10"; la última generada arriba con el N° correlativo más alto
  const filas = est.locator('#tabla_ordenes_pago tbody tr');
  await expect(filas.first().locator('td[data-label="Concepto"]')).toHaveText('Cuota 2 de 10', { timeout: 15_000 });
  await expect(filas.nth(1).locator('td[data-label="Concepto"]')).toHaveText('Cuota 1 de 10');
  const total = await filas.count();
  await expect(filas.first().locator('td[data-label="N°"]')).toHaveText(String(total));
  await expect(filas.nth(1).locator('td[data-label="N°"]')).toHaveText(String(total - 1));
  await expect(filas.last().locator('td[data-label="N°"]')).toHaveText('1');
  // La matrícula va en su propia sección, fuera de la tabla numerada
  await expect(est.locator('#tabla_matricula tbody tr')).toHaveCount(1);
  await expect(est.locator('#tabla_matricula tbody td[data-label="Concepto"]')).toHaveText('Matrícula');
  await expect(est.locator('#tabla_matricula td[data-label="N°"]')).toHaveCount(0);
  await expect(est.locator('#tabla_ordenes_pago tbody', { hasText: 'Matrícula' })).toHaveCount(0);
  await expect(est.locator('#filtro_tipo option', { hasText: 'Matrícula' })).toHaveCount(0);
  await expect(est.locator('#lista_por_pagar .mop-pp')).toHaveCount(2);

  // Historial de pagos: las dos órdenes aún no validadas (la última arriba), sin
  // correlativo: se distingue el N° de cuota
  await est.goto('historialmodulosestudiante');
  const sinValidar = est.locator('#tbody_sin_validar tr');
  await expect(sinValidar).toHaveCount(2, { timeout: 15_000 });
  await expect(est.locator('#tabla_sin_validar td[data-label="N°"]')).toHaveCount(0);
  await expect(sinValidar.first().locator('td[data-label="Concepto"] .hp-cuota')).toHaveText('Cuota 2 de 10');
  await expect(sinValidar.first().locator('td[data-label="Estado"]')).toHaveText('Pendiente de pago');
  await expect(sinValidar.nth(1).locator('td[data-label="Concepto"]')).toHaveText('Cuota 1 de 10');
  await est.close();

  // Personal (validar voucher): primero la cuota 1 con su módulo; la 2 queda para después
  const ordenesCuota = String(valor(`SELECT GROUP_CONCAT(IdOrdenPago ORDER BY IdOrdenPago) FROM ordenpago
                                     WHERE idinscripcion = ${inscrito.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`)).split(',');
  const moduloDe = (id) => valor(`SELECT m.codigomodulo FROM ordenpagomodulo opm INNER JOIN modulos m ON m.Idmodulo = opm.IdModulo WHERE opm.IdOrdenPago = ${id}`);
  const pendiente = await ajax(page, 'ordenpago.ajax.php', { accion: 'obtenerOrdenPendienteCuotaPrograma', idinscripcion: String(inscrito.idInscripcion) });
  expect(String(pendiente.orden.IdOrdenPago)).toBe(ordenesCuota[0]);
  expect(pendiente.orden.NumeroCuota).toBe(1);
  expect(pendiente.siguientes.map((s) => s.NumeroCuota)).toEqual([2]);

  await page.goto('matriculados');
  await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), inscrito.idInscripcion);
  const modal = page.locator('#modalInscribirModulo');
  await modal.locator('#tab-pago-modulos-link').click();
  await expect(modal.locator('.vc-orden-titulo')).toContainText('Cuota 1 de 10', { timeout: 15_000 });
  await expect(modal.locator('.vc-orden-meta')).toContainText(String(moduloDe(ordenesCuota[0])));
  await expect(modal.locator('.vc-orden-despues')).toContainText('Cuota 2');

  // El voucher queda en la cuota 1 (su orden), no en la 2
  const pago = await pagarCuota(page, inscrito.idInscripcion, valor(`SELECT MontoTotal FROM ordenpago WHERE IdOrdenPago = ${ordenesCuota[0]}`), unico() + '-07');
  expect(pago.estado, pago.mensaje).toBe('exitoso');
  expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenesCuota[0]}`)).toBe('CONFIRMADO');
  expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${ordenesCuota[1]}`)).toBe('PENDIENTE');
});

test('un estudiante no puede generar cuotas de otra inscripción', async ({ page }) => {
  await ingresar(page, 'adm');
  const ajeno = await estudianteInscrito(page);
  await ingresar(page, 'est');
  const r = await ajax(page, 'ordenpago.ajax.php', { accion: 'calcularMiCuota', idinscripcion: String(ajeno.idInscripcion) });
  expect(r.success).toBe(false);
  // Y el personal no usa esta acción (solo la ve el estudiante en su pantalla)
  const g = await ajax(page, 'ordenpago.ajax.php', { accion: 'generarMiCuota', idinscripcion: String(ajeno.idInscripcion) });
  expect(g.success).toBe(false);
  expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${ajeno.idInscripcion} AND Observaciones = 'CUOTA PROGRAMA'`)).toBe('0');
});
