// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, PROGRAMA_ID } from './ayudantes-proceso.js';

// Buscar por Estudiante: cada estudiante y programa tiene el botón "Situación académica",
// que abre un PDF en hoja carta con todos los módulos del programa.

const conNotas = () => valor(`SELECT c.EstudianteID FROM calificacion c WHERE c.ProgramaId = ${PROGRAMA_ID} AND c.Nota IS NOT NULL LIMIT 1`);

test('el botón abre el PDF de situación académica (hoja carta)', async ({ page }) => {
  const estudianteID = conNotas();
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await ingresar(page, 'sec');
  await page.goto('bnotaestudiante');
  await page.locator('#input_filtro_valor').fill(ci);
  await page.locator('#btn_buscar_calificaciones').click();

  const boton = page.locator(`.btn-situacion-academica[href*="estudianteID=${estudianteID}&"]`).first();
  await expect(boton).toBeVisible({ timeout: 15_000 });
  const r = await page.request.get(String(await boton.getAttribute('href')));
  expect(r.status()).toBe(200);
  const pdf = await r.body();
  expect(pdf.subarray(0, 4).toString()).toBe('%PDF');
  // Hoja carta: 612 × 792 puntos
  expect(pdf.toString('latin1')).toMatch(/\/MediaBox \[0\.000+ 0\.000+ 612\.000+ 792\.000+\]/);
});

test('sin inscripción responde 404 y el docente no tiene acceso', async ({ page }) => {
  await ingresar(page, 'adm');
  const r = await page.request.get(`vistas/componentes/situacion-academica-pdf.php?estudianteID=0&programaID=${PROGRAMA_ID}`);
  expect(r.status()).toBe(404);

  await page.context().clearCookies();
  await ingresar(page, 'doc');
  const negado = await page.request.get(`vistas/componentes/situacion-academica-pdf.php?estudianteID=${conNotas()}&programaID=${PROGRAMA_ID}`);
  expect(negado.status()).toBe(403);
});

test('Certificados > Situación Académica: busca al estudiante y muestra el PDF', async ({ page }) => {
  const estudianteID = conNotas();
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  await ingresar(page, 'adm');
  await page.goto('panel');
  await expect(page.locator('.kt-menu__nav a[href="situacionacademica"]')).toHaveCount(1); // en el menú de Certificados
  await page.goto('situacionacademica');
  await expect(page.getByRole('heading', { name: 'SITUACIÓN ACADÉMICA' })).toBeVisible();
  await page.locator('#cert-termino').fill(ci);
  await page.locator('#cert-btn-buscar').click();
  // Con un solo resultado se elige solo; con varios hay que elegir el primero
  await expect(page.locator('#cert-elegido:visible, .cert-resultado:visible').first()).toBeVisible({ timeout: 15_000 });
  if (await page.locator('.cert-resultado:visible').count()) await page.locator('.cert-resultado').first().click();

  const abrir = page.locator('#btn-abrir-situacion');
  await expect(abrir).toBeVisible();
  const href = String(await abrir.getAttribute('href'));
  expect(href).toContain(`estudianteID=${estudianteID}&`);
  expect(await page.locator('#situacion-vista').getAttribute('src')).toBe(href);
  const r = await page.request.get(href);
  expect((await r.body()).subarray(0, 4).toString()).toBe('%PDF');

  // Las pantallas de cada certificado no tienen el botón
  await page.goto('certificadocalificaciones');
  await expect(page.locator('#btn-abrir-situacion, .cert-situacion')).toHaveCount(0);
});

test('la Secretaría no tiene la página (no tiene Certificados)', async ({ page }) => {
  await ingresar(page, 'sec');
  await page.goto('situacionacademica');
  await expect(page.locator('section.pn-bienvenida')).toBeVisible();
  await expect(page.locator('.kt-menu__nav a[href="situacionacademica"]')).toHaveCount(0);
});
