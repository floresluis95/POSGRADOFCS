// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, registrarEstudiante } from './ayudantes-proceso.js';

// Botones "+ Nueva" de Profesión y Universidad (Pre-Registro y Estudiantes): el mismo
// nombre no se registra dos veces aunque se escriba con otras mayúsculas, sin tildes o
// con espacios de más; en ese caso se devuelve el que ya existe.

const nuevaProfesion = (page, nombre) =>
  ajax(page, 'ordenpago.ajax.php', { accion: 'registrarProfesionRapida', nombreProfesion: nombre });
const nuevaUniversidad = (page, nombre, tipo = 'PRIVADA') =>
  ajax(page, 'ordenpago.ajax.php', { accion: 'registrarUniversidadRapida', nombreUniversidad: nombre, tipoUniversidad: tipo });

test.describe('Profesión y universidad sin repetidos', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('profesión: el mismo nombre escrito distinto no se registra dos veces', async ({ page }) => {
    const base = `PRUEBA ODONTÓLOGO ${unico()}`;
    const primera = await nuevaProfesion(page, base);
    expect(primera.success, primera.mensaje).toBe(true);
    expect(primera.duplicado).toBe(false);

    for (const variante of [base.toLowerCase(), `  ${base.replace(/ /g, '   ')}  `, base.replace('Ó', 'O')]) {
      const r = await nuevaProfesion(page, variante);
      expect(r.duplicado, `"${variante}" debió reconocerse como repetida`).toBe(true);
      expect(r.profesion.IdProfesion).toBe(primera.profesion.IdProfesion);
    }
    expect(valor(`SELECT COUNT(*) FROM profesion WHERE NombreProfesion = '${base}'`)).toBe('1');
  });

  test('profesión: un nombre largo se guarda completo (antes se cortaba a 25 letras y se repetía)', async ({ page }) => {
    const largo = `LICENCIADO EN ODONTOLOGIA PEDIATRICA ${unico()}`;
    const r = await nuevaProfesion(page, largo);
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT NombreProfesion FROM profesion WHERE IdProfesion = ${r.profesion.IdProfesion}`)).toBe(largo);
  });

  test('profesión: más de 100 caracteres se rechaza', async ({ page }) => {
    const r = await nuevaProfesion(page, 'X'.repeat(101));
    expect(r.success).toBe(false);
  });

  test('universidad: el mismo nombre escrito distinto no se registra dos veces', async ({ page }) => {
    const base = `UNIVERSIDAD DE PRUEBA ${unico()}`;
    const primera = await nuevaUniversidad(page, base);
    expect(primera.success, primera.mensaje).toBe(true);
    const otra = await nuevaUniversidad(page, `  ${base.toLowerCase().replace(/ /g, '  ')} `, 'PUBLICA');
    expect(otra.duplicado).toBe(true);
    expect(otra.universidad.IdUniversidad).toBe(primera.universidad.IdUniversidad);
    expect(valor(`SELECT COUNT(*) FROM universidad WHERE NombreUniversidad = '${base}'`)).toBe('1');
  });

  test('universidad: con o sin la sigla entre paréntesis es la misma', async ({ page }) => {
    const n = unico();
    const conSigla = await nuevaUniversidad(page, `UNIVERSIDAD DE PRUEBA SIGLA ${n} (UPS${n})`);
    expect(conSigla.success, conSigla.mensaje).toBe(true);
    const id = conSigla.universidad.IdUniversidad;

    for (const variante of [`Universidad de Prueba Sigla ${n}`, `UPS${n}`, `Univ. de Prueba, Sigla ${n} (ups${n})`]) {
      const r = await nuevaUniversidad(page, variante);
      expect(r.duplicado, `"${variante}" debió reconocerse como la misma`).toBe(true);
      expect(r.universidad.IdUniversidad).toBe(id);
    }
    expect(valor(`SELECT COUNT(*) FROM universidad WHERE NombreUniversidad LIKE 'UNIVERSIDAD DE PRUEBA SIGLA ${n}%'`)).toBe('1');
  });

  test('pantalla: escribir la universidad sin sigla selecciona la que ya existe', async ({ page }) => {
    const existente = valor("SELECT NombreUniversidad FROM universidad WHERE NombreUniversidad LIKE '%(%' ORDER BY IdUniversidad LIMIT 1") ?? '';
    const idExistente = valor(`SELECT IdUniversidad FROM universidad WHERE NombreUniversidad = '${existente}'`);
    const total = valor('SELECT COUNT(*) FROM universidad');

    await page.goto('preregistro');
    await page.locator('button[data-target="#modalPasoEstudiante"]').first().click();
    await page.locator('#buscarPorCi').fill(unico());
    await page.locator('#btnBuscarPorCi').click();
    await expect(page.locator('#btnNuevaUniversidad')).toBeEnabled();
    await page.locator('#btnNuevaUniversidad').click();
    await page.locator('#inputNuevaUniversidad').fill(existente.replace(/\s*\([^)]*\)/, '').toLowerCase());
    await page.locator('#selectNuevaUniversidadTipo').selectOption('PRIVADA');
    await page.locator('#formNuevaUniversidad button[type="submit"]').click();

    await expect(page.locator('.swal-modal, .swal2-popup').first()).toContainText('ya existe');
    await expect(page.locator('#IdUniversidad')).toHaveValue(idExistente ?? '');
    expect(valor('SELECT COUNT(*) FROM universidad')).toBe(total);
  });

  test('datos del estudiante en MAYÚSCULAS también con tildes (el correo no)', async ({ page }) => {
    const { ci, respuesta } = await registrarEstudiante(page, {
      Nombre: 'josé ángel', Apaterno: 'núñez', Lugarn: 'Potosí', Correo: `Jose${unico()}@Correo-Prueba.com`,
    });
    expect(respuesta.success, respuesta.mensaje).toBe(true);
    const [nombre, apaterno, lugar, correo] = valor(`SELECT CONCAT_WS('|', Nombre, Apaterno, Lugarn, Correo) FROM estudiante WHERE Ci = '${ci}'`)?.split('|') ?? [];
    expect([nombre, apaterno, lugar]).toEqual(['JOSÉ ÁNGEL', 'NÚÑEZ', 'POTOSÍ']);
    expect(correo).toBe(correo.toLowerCase());
  });
});
