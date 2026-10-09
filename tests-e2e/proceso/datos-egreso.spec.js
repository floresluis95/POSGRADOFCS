// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, ingresar, registrarEstudiante, preregistrar } from './ayudantes-proceso.js';

// Pre-Registro: la universidad y la fecha de egreso del estudiante son obligatorias,
// y la fecha de egreso no puede ser posterior a hoy.

/** Fecha local (YYYY-MM-DD) desplazada `dias` desde hoy. */
const fechaDesdeHoy = (dias) => {
  const d = new Date();
  d.setDate(d.getDate() + dias);
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

test.describe('Universidad y fecha de egreso (Pre-Registro)', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('en la pantalla, los dos campos son obligatorios y la fecha tiene como máximo hoy', async ({ page }) => {
    await page.goto('preregistro');
    await expect(page.locator('#IdUniversidad')).toHaveAttribute('required', '');
    await expect(page.locator('#FechaEgreso')).toHaveAttribute('required', '');
    await expect(page.locator('#FechaEgreso')).toHaveAttribute('max', valor('SELECT CURDATE()') ?? '');
  });

  for (const [caso, cambios] of /** @type {[string, Record<string, string>][]} */ ([
    ['sin universidad', { IdUniversidad: '' }],
    ['con una universidad que no existe', { IdUniversidad: '999999' }],
    ['sin fecha de egreso', { FechaEgreso: '' }],
    ['con una fecha de egreso inválida', { FechaEgreso: '2020-02-31' }],
    ['con la fecha de egreso de mañana', { FechaEgreso: fechaDesdeHoy(1) }],
  ])) {
    test(`no se registra al estudiante ${caso}`, async ({ page }) => {
      const { ci, respuesta } = await registrarEstudiante(page, cambios);
      expect(respuesta.success).toBe(false);
      expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('0');
    });
  }

  test('sí se registra con la fecha de egreso de hoy', async ({ page }) => {
    const { respuesta } = await registrarEstudiante(page, { FechaEgreso: fechaDesdeHoy(0) });
    expect(respuesta.success, respuesta.mensaje).toBe(true);
  });

  test('no se genera el pre-registro de un estudiante existente sin datos de egreso', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    sql(`UPDATE estudiante SET FechaEgreso = NULL WHERE EstudianteID = ${estudianteID}`);
    await preregistrar(page, estudianteID);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${estudianteID}`)).toBe('0');
  });
});

test.describe('Egreso a los 15 años de edad como mínimo (Pre-Registro)', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('no se registra si egresó antes de cumplir 15 años', async ({ page }) => {
    const { ci, respuesta } = await registrarEstudiante(page, { FechaNacimiento: '1995-05-10', FechaEgreso: '2010-05-09' });
    expect(respuesta.success).toBe(false);
    expect(respuesta.mensaje).toContain('15 años');
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('0');
  });

  test('no se registra si egresó el mismo año en que nació', async ({ page }) => {
    const { respuesta } = await registrarEstudiante(page, { FechaNacimiento: '1995-05-10', FechaEgreso: '1995-12-20' });
    expect(respuesta.success).toBe(false);
  });

  test('sí se registra si egresó justo al cumplir 15 años', async ({ page }) => {
    const { respuesta } = await registrarEstudiante(page, { FechaNacimiento: '1995-05-10', FechaEgreso: '2010-05-10' });
    expect(respuesta.success, respuesta.mensaje).toBe(true);
  });
});
