// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Certificados exigidos para la titulación: el ADM los marca por grado en Costos de
// certificados; el sistema cuenta, por estudiante y programa, cuáles ya tiene (generados o
// registrados sin documento). Se ve en Registro de documentos y en "Mis requisitos".

const grado = () => String(valor(`SELECT REPLACE(UPPER(GradoAcademico), 'Í', 'I') FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
// Un certificado sin documento propio de la prueba (no depende de cómo se llame en la base real)
const NOMBRE_SD = 'BIBLIOTECA PRUEBA ' + Date.now();
const idProBiblioteca = () => valor(`SELECT IdCertificadoSD FROM certificadosindocumento WHERE Nombre = '${NOMBRE_SD}'`);

test.afterAll(() => {
  // Las demás pruebas no esperan certificados exigidos
  sql('DELETE FROM certificadotitulacion');
});

test('el ADM marca los exigidos y el avance se ve en Registro de documentos y en Mis requisitos', async ({ page, browser }) => {
  sql('DELETE FROM certificadotitulacion');
  await ingresar(page, 'adm');
  expect((await ajax(page, 'certificado.ajax.php', { accion: 'guardarSinDocumento', nombre: NOMBRE_SD, monto: '25' })).success).toBe(true);

  // Costos de certificados: Medio Magnético se marca con la casilla; Pro Biblioteca también
  await page.goto('costoscertificados');
  await page.locator('#btnModoEdicion').click(); // al entrar está bloqueado
  const casillaMM = page.locator(`.req-titulacion[data-clave="MEDIO_MAGNETICO"][data-grado="${grado()}"]`);
  await expect(casillaMM).toBeVisible({ timeout: 15_000 });
  await casillaMM.check();
  await expect.poll(() => valor(`SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'MEDIO_MAGNETICO' AND GradoAcademico = '${grado()}'`)).toBe('1');
  const casillaPB = page.locator(`.req-titulacion[data-clave="SD:${idProBiblioteca()}"][data-grado="${grado()}"]`);
  await casillaPB.check();
  await expect.poll(() => valor(`SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'SD:${idProBiblioteca()}'`)).toBe('1');
  // Al recargar siguen marcadas
  await page.reload();
  await expect(casillaMM).toBeChecked({ timeout: 15_000 });
  await expect(casillaPB).toBeChecked();

  // Un estudiante nuevo: 0 de 2, faltan los dos
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  const tit = page.locator('#rd-titulacion');
  await expect(tit).toContainText('0 de 2', { timeout: 15_000 });
  await expect(tit).toContainText('Medio Magnético');
  await expect(tit).toContainText(NOMBRE_SD);

  // Registra Pro Biblioteca (sin documento): 1 de 2, falta solo Medio Magnético
  const r = await ajax(page, 'certificado.ajax.php', {
    accion: 'registrarSinDocumento', estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID),
    idCertificadoSD: String(idProBiblioteca()), vaucher: '1601' + String(unico()).slice(-4), monto: '25', fechaPago: '2026-10-05',
  });
  expect(r.success, r.mensaje).toBe(true);
  await page.locator('#btn-buscar-ci').click();
  await expect(tit).toContainText('1 de 2', { timeout: 15_000 });
  await expect(tit.locator('.rd-tit-falta')).toHaveCount(1);
  await expect(tit.locator('.rd-tit-falta')).toContainText('Medio Magnético');

  // Con el Medio Magnético generado: completo
  await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: 'PRUEBA TITULACION',
      curso: 'ESPECIALIDAD', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'T', vaucher: '1602' + String(unico()).slice(-4),
      monto: '42', fecha: '2026-10-10', coordinador: 'C', director: 'D',
    },
  });
  await page.locator('#btn-buscar-ci').click();
  await expect(tit).toContainText('2 de 2', { timeout: 15_000 });
  await expect(tit).toContainText('Tiene todos los certificados exigidos');

  // El estudiante lo ve en Mis requisitos (solo consulta, con lo que le falta)
  const est = await browser.newPage();
  await ingresar(est, 'est');
  await est.goto('misrequisitos');
  const seccion = est.locator('.mr-titulacion').first();
  await expect(seccion).toContainText('Certificados para la titulación', { timeout: 15_000 });
  await expect(seccion).toContainText('Medio Magnético');
  await expect(seccion).toContainText(NOMBRE_SD);
  await expect(seccion.locator('button, a')).toHaveCount(0);
  await est.close();
});

test('solo el ADM define los exigidos', async ({ page }) => {
  await ingresar(page, 'sec');
  const r = await ajax(page, 'certificado.ajax.php', { accion: 'guardarRequisitoTitulacion', clave: 'MEDIO_MAGNETICO', grado: 'MAESTRIA', exigir: '1' });
  expect(r.success).toBe(false);
  await ingresar(page, 'adm');
  const malo = await ajax(page, 'certificado.ajax.php', { accion: 'guardarRequisitoTitulacion', clave: 'NO_EXISTE', grado: 'MAESTRIA', exigir: '1' });
  expect(malo.success).toBe(false);
});

test('"Titulación en todos los grados" marca y desmarca toda la fila', async ({ page }) => {
  sql("DELETE FROM certificadotitulacion WHERE Clave = 'HISTORIAL_ACADEMICO'");
  const totalGrados = 4; // Diplomado, Especialidad, Maestría y Doctorado
  await ingresar(page, 'adm');
  await page.goto('costoscertificados');
  await page.locator('#btnModoEdicion').click(); // al entrar está bloqueado
  const boton = page.locator('.cc-tit-fila[data-clave="HISTORIAL_ACADEMICO"]');
  await expect(boton).toContainText('Titulación en todos los grados', { timeout: 15_000 });

  await boton.click();
  await expect.poll(() => valor("SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'HISTORIAL_ACADEMICO'")).toBe(String(totalGrados));
  await expect(page.locator('.req-titulacion[data-clave="HISTORIAL_ACADEMICO"]:checked')).toHaveCount(totalGrados);
  await expect(boton).toContainText('Quitar titulación en todos');

  // Con todos marcados, el mismo botón los quita
  await boton.click();
  await expect.poll(() => valor("SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'HISTORIAL_ACADEMICO'")).toBe('0');
  await expect(page.locator('.req-titulacion[data-clave="HISTORIAL_ACADEMICO"]:checked')).toHaveCount(0);

  // Con uno solo marcado, el botón completa la fila (no la vacía)
  await page.locator('.req-titulacion[data-clave="HISTORIAL_ACADEMICO"]').first().check();
  await expect.poll(() => valor("SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'HISTORIAL_ACADEMICO'")).toBe('1');
  await boton.click();
  await expect.poll(() => valor("SELECT COUNT(*) FROM certificadotitulacion WHERE Clave = 'HISTORIAL_ACADEMICO'")).toBe(String(totalGrados));
});

test('Costos de certificados abre bloqueado; se activa y se vuelve a bloquear', async ({ page }) => {
  await ingresar(page, 'adm');
  await page.goto('costoscertificados');
  const precio = page.locator('.costo-certificado').first();
  await expect(precio).toBeVisible({ timeout: 15_000 });

  // Bloqueado: precios y casillas sin poder tocarse, sin botones de edición
  await expect(precio).toBeDisabled();
  await expect(page.locator('.req-titulacion').first()).toBeDisabled();
  await expect(page.locator('#btnGuardarCostos')).toBeHidden();
  await expect(page.locator('.cc-otros-form')).toBeHidden();
  await expect(page.locator('#textoModo')).toContainText('Edición bloqueada');

  // Activar
  await page.locator('#btnModoEdicion').click();
  await expect(precio).toBeEnabled();
  await expect(page.locator('#btnGuardarCostos')).toBeVisible();
  await expect(page.locator('.cc-otros-form')).toBeVisible();
  await expect(page.locator('#btnModoEdicion')).toContainText('Bloquear edición');

  // Bloquear con un precio cambiado sin guardar: avisa; al confirmar lo descarta
  const original = await precio.inputValue();
  await precio.fill('999');
  await page.locator('#btnModoEdicion').click();
  await expect(page.locator('.swal2-popup')).toContainText('Hay cambios sin guardar');
  await page.locator('.swal2-confirm').click();
  await expect(precio).toBeDisabled();
  await expect(precio).toHaveValue(original);
});
