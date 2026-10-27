// @ts-check
import { test, expect } from '@playwright/test';
import { inflateSync } from 'node:zlib';
import { sql, valor, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Abandono marcado en Matriculados desde un módulo: el estudiante sigue en las planillas
// de los módulos anteriores (y donde ya tiene nota) y sale de las siguientes. En la
// Situación Académica esos módulos salen como ABANDONO. Reporte de abandonos con montos.

/** Texto de un PDF de TCPDF (descomprime sus streams). */
function textoPdf(buffer) {
  const crudo = buffer.toString('latin1');
  let texto = crudo;
  const re = /stream\r?\n/g;
  let m;
  while ((m = re.exec(crudo))) {
    const fin = crudo.indexOf('endstream', m.index);
    try {
      texto += inflateSync(buffer.subarray(m.index + m[0].length, fin)).toString('latin1');
    } catch { /* no comprimido o no es texto */ }
  }
  return texto;
}

test('abandono desde un módulo: planillas, situación académica, buscador y reporte', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID, idInscripcion } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const modulos = sql(`SELECT Idmodulo FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} AND estadomodulo != 'INACTIVO' ORDER BY Idmodulo LIMIT 4`).map((f) => Number(f[0]));
  expect(modulos.length, 'el programa de prueba necesita al menos 4 módulos').toBe(4);
  const [m1, m2, m3, m4] = modulos;
  // Ya tiene nota en el primer módulo
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro) VALUES (${estudianteID}, ${PROGRAMA_ID}, ${m1}, 99, 'REGISTRADO', CURDATE())`);

  const urlPdf = `vistas/componentes/situacion-academica-pdf.php?estudianteID=${estudianteID}&programaID=${PROGRAMA_ID}`;
  const enPlanilla = async (moduloID) => {
    const r = await ajax(page, 'calificacion.ajax.php', { accion: 'obtenerEstudiantes', moduloID: String(moduloID), programaID: String(PROGRAMA_ID) });
    return r.data.some((e) => Number(e.EstudianteID) === estudianteID);
  };
  const marcar = (campos) => ajax(page, 'inscripcionmodulo.ajax.php', { accion: 'actualizarEstadoInscripcion', idinscripcion: String(idInscripcion), ...campos });

  // Módulos para elegir: con la nota del primero
  const lista = await ajax(page, 'inscripcionmodulo.ajax.php', { accion: 'modulosAbandono', idinscripcion: String(idInscripcion) });
  expect(lista.success).toBe(true);
  expect(Number(lista.modulos.find((m) => Number(m.Idmodulo) === m1).Nota)).toBe(99);

  // El módulo y el motivo son obligatorios; un módulo de otro programa no vale
  const otroModulo = valor(`SELECT Idmodulo FROM modulos WHERE ProgramaId != ${PROGRAMA_ID} LIMIT 1`);
  expect((await marcar({ nuevoEstado: 'ABANDONO', motivoAbandono: 'X' })).success).toBe(false);
  if (otroModulo) expect((await marcar({ nuevoEstado: 'ABANDONO', idModuloAbandono: String(otroModulo), motivoAbandono: 'X' })).success).toBe(false);
  expect((await marcar({ nuevoEstado: 'ABANDONO', idModuloAbandono: String(m3), motivoAbandono: '  ' })).success).toBe(false);

  const marcado = await marcar({ nuevoEstado: 'ABANDONO', idModuloAbandono: String(m3), motivoAbandono: 'no canceló cuotas' });
  expect(marcado.success, marcado.mensaje).toBe(true);
  const guardado = sql(`SELECT Estado, IdModuloAbandono, MotivoAbandono, FechaAbandono IS NOT NULL FROM estudianteprograma WHERE idInscripcion = ${idInscripcion}`)[0];
  expect(guardado).toEqual(['ABANDONO', String(m3), 'NO CANCELÓ CUOTAS', '1']);

  // Planillas: antes del módulo de abandono sí; desde él, no
  expect(await enPlanilla(m1)).toBe(true);
  expect(await enPlanilla(m2)).toBe(true);
  expect(await enPlanilla(m3)).toBe(false);
  expect(await enPlanilla(m4)).toBe(false);
  // Con nota en un módulo posterior (registrada antes del abandono) sigue en esa planilla
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro) VALUES (${estudianteID}, ${PROGRAMA_ID}, ${m4}, 80, 'REGISTRADO', CURDATE())`);
  expect(await enPlanilla(m4)).toBe(true);

  // Situación académica: aprobado, pendiente (m2) y abandono (desde m3)
  const pdf = textoPdf(await (await page.request.get(urlPdf)).body());
  expect(pdf).toContain('APROBADO');
  expect(pdf).toContain('PENDIENTE');
  expect(pdf).toContain('ABANDONO');

  // La página Situación Académica lo encuentra; los certificados no
  const buscar = (incluirAbandono) => ajax(page, 'certificado.ajax.php', { accion: 'buscarEstudiantes', termino: ci, incluirAbandono });
  expect((await buscar('0')).resultados).toHaveLength(0);
  expect((await buscar('1')).resultados[0].EstadoInscripcion).toBe('ABANDONO');

  // Reporte de abandonos (filtrado por C.I.): resumen y detalle
  const reporte = await page.request.post('ajax/reportes.ajax.php', { form: { tipo_reporte: 'abandonos', ci } });
  const buf = await reporte.body();
  expect(buf.subarray(0, 4).toString()).toBe('%PDF');
  const texto = textoPdf(buf);
  expect(texto).toContain('REPORTE DE ABANDONOS');
  expect(texto).toContain(ci);
  expect(texto).toContain('TOTAL GENERAL');

  // Matriculados muestra desde qué módulo
  const filas = await page.request.post('ajax/inscripcionmodulo.ajax.php', { form: { accion: 'cargarTablaMatriculados', estadoFiltro: 'ABANDONO', programaIDFiltro: String(PROGRAMA_ID) } });
  const codigoM3 = String(valor(`SELECT codigomodulo FROM modulos WHERE Idmodulo = ${m3}`));
  expect(await filas.text()).toContain(`ABANDONO desde ${codigoM3}`);

  // Reactivar borra los datos del abandono y vuelve a todas las planillas
  expect((await marcar({ nuevoEstado: 'ACTIVO' })).success).toBe(true);
  expect(sql(`SELECT Estado, IFNULL(IdModuloAbandono, '-'), IFNULL(MotivoAbandono, '-') FROM estudianteprograma WHERE idInscripcion = ${idInscripcion}`)[0])
    .toEqual(['ACTIVO', '-', '-']);
  expect(await enPlanilla(m3)).toBe(true);
});

test('Matriculados: la ventana de abandono pide el módulo (sugiere el primero sin nota) y el motivo', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID, idInscripcion } = await estudianteInscrito(page);
  const [m1, m2] = sql(`SELECT Idmodulo FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} AND estadomodulo != 'INACTIVO' ORDER BY Idmodulo LIMIT 2`).map((f) => f[0]);
  sql(`INSERT INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro) VALUES (${estudianteID}, ${PROGRAMA_ID}, ${m1}, 90, 'REGISTRADO', CURDATE())`);

  await page.goto('matriculados');
  const boton = page.locator(`.btn-marcar-abandono[data-idinscripcion="${idInscripcion}"]`);
  await expect(boton).toHaveCount(1, { timeout: 15_000 });
  await boton.evaluate((el) => /** @type {HTMLElement} */ (el).click()); // está dentro del menú desplegable

  await expect(page.locator('#abandonoModulo')).toBeVisible();
  await expect(page.locator('#abandonoModulo')).toHaveValue(m2); // m1 ya tiene nota
  await page.locator('#abandonoMotivo').selectOption('OTRO');
  await page.locator('.swal2-confirm').click();
  await expect(page.locator('.swal2-validation-message')).toContainText('detalle');
  await page.locator('#abandonoDetalle').fill('cambio de ciudad');
  await page.locator('.swal2-confirm').click();
  await expect(page.locator('.swal2-title')).toHaveText('Listo');

  expect(sql(`SELECT Estado, IdModuloAbandono, MotivoAbandono FROM estudianteprograma WHERE idInscripcion = ${idInscripcion}`)[0])
    .toEqual(['ABANDONO', m2, 'CAMBIO DE CIUDAD']);
});

test('Reportes: la tarjeta Abandonos está en Por programa', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('reportes');
  await page.locator('.rp-tab[data-panel="rp-programas"]').click();
  const tarjeta = page.locator('#rp-programas .rp-card', { has: page.locator('input[name="tipo_reporte"][value="abandonos"]') });
  await expect(tarjeta).toBeVisible();
  await expect(tarjeta.locator('select[name="grado"]')).toBeVisible();
  await expect(page.locator('.rp-tab[data-panel="rp-programas"] .rp-n')).toHaveText(String(await page.locator('#rp-programas .rp-card').count()));
});
