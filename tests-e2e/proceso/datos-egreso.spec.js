// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, ajax, enviarFormulario, registrarEstudiante, preregistrar } from './ayudantes-proceso.js';

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

test.describe('Universidad y fecha de egreso (módulo Estudiantes)', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  const universidad = () => valor('SELECT MIN(IdUniversidad) FROM universidad') ?? '';

  /** Alta por el formulario "Nuevo estudiante" de la página Estudiantes. */
  const altaPorFormulario = async (page, cambios = {}) => {
    const ci = unico();
    const html = await enviarFormulario(page, 'estudiantes', {
      Ci: ci, Complemento: '', Exp: 'OR', Nombre: 'FORM', Apaterno: 'PRUEBA', Amaterno: 'EGRESO',
      FechaNacimiento: '1995-05-10', Edad: '31', Lugarn: 'ORURO', Correo: `prueba${ci}@correo-prueba.com`,
      IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion') ?? '', Trabajo: '', Direccion: '', Telefono: '', Celular: '70000001',
      IdUniversidad: universidad(), FechaEgreso: '2018-12-15',
      ...cambios,
    });
    return { ci, html };
  };

  /** Edición por el botón "Editar" de la página Estudiantes. */
  const editar = (page, estudianteID, cambios = {}) => ajax(page, 'ordenpago.ajax.php', {
    accion: 'actualizarEstudiante', EstudianteID: String(estudianteID), Exp: 'OR', Nombre: 'PRUEBA', Apaterno: 'EDITAR',
    FechaNacimiento: '1995-05-10', Edad: '31', Lugarn: 'ORURO', Correo: `editar${estudianteID}@correo-prueba.com`,
    IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion') ?? '', Celular: '70000000',
    IdUniversidad: universidad(), FechaEgreso: '2018-12-15',
    ...cambios,
  });

  test('en la pantalla, los campos son obligatorios en el alta y en la edición', async ({ page }) => {
    await page.goto('estudiantes');
    for (const id of ['#IdUniversidad', '#FechaEgreso', '#IdUniversidadEditar', '#FechaEgresoEditar']) {
      await expect(page.locator(id)).toHaveAttribute('required', '');
    }
    await expect(page.locator('#FechaEgreso')).toHaveAttribute('max', valor('SELECT CURDATE()') ?? '');
  });

  test('en la pantalla, la fecha de egreso anterior a los 15 años queda inválida', async ({ page }) => {
    await page.goto('estudiantes');
    await page.locator('[data-target="#ModalInsertarEstudiante"]').click();
    await expect(page.locator('#fechaNacimiento')).toBeVisible();
    await page.locator('#fechaNacimiento').fill('1995-05-10');
    await page.locator('#fechaNacimiento').dispatchEvent('change');
    await page.locator('#FechaEgreso').fill('2010-05-09');
    await page.locator('#FechaEgreso').dispatchEvent('change');
    await expect(page.locator('#FechaEgreso')).toHaveAttribute('min', '2010-05-10');
    expect(await page.locator('#FechaEgreso').evaluate((el) => /** @type {HTMLInputElement} */ (el).validationMessage)).toContain('15 años');
  });

  for (const [caso, cambios] of /** @type {[string, Record<string, string>][]} */ ([
    ['sin universidad', { IdUniversidad: '' }],
    ['sin fecha de egreso', { FechaEgreso: '' }],
    ['con la fecha de egreso de mañana', { FechaEgreso: fechaDesdeHoy(1) }],
    ['si egresó antes de cumplir 15 años', { FechaEgreso: '2010-05-09' }],
  ])) {
    test(`alta: no se registra ${caso}`, async ({ page }) => {
      const { ci } = await altaPorFormulario(page, cambios);
      expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('0');
    });

    test(`edición: no se guarda ${caso}`, async ({ page }) => {
      const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
      const r = await editar(page, estudianteID, cambios);
      expect(r.success).toBe(false);
      expect(valor(`SELECT FechaEgreso FROM estudiante WHERE EstudianteID = ${estudianteID}`)).toBe('2018-12-15');
    });
  }

  test('alta: sí se registra con datos de egreso válidos', async ({ page }) => {
    const { ci } = await altaPorFormulario(page);
    expect(valor(`SELECT FechaEgreso FROM estudiante WHERE Ci = '${ci}'`)).toBe('2018-12-15');
  });

  test('edición: sí se guarda y completa el egreso de un estudiante que no lo tenía', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    sql(`UPDATE estudiante SET IdUniversidad = NULL, FechaEgreso = NULL WHERE EstudianteID = ${estudianteID}`);
    const r = await editar(page, estudianteID, { FechaEgreso: '2017-06-30' });
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT FechaEgreso FROM estudiante WHERE EstudianteID = ${estudianteID}`)).toBe('2017-06-30');
  });
});
