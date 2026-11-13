// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, ajax, registrarEstudiante } from './ayudantes-proceso.js';

// Correcciones del ADM (2026-11-13): página "Universidades y Profesiones" (corregir, unir
// duplicados, eliminar sin uso) y botón "Corregir C.I." de estudiante/docente/personal.
// Secretaría no puede hacer nada de esto.

const catalogo = (page, campos) => ajax(page, 'catalogos.ajax.php', campos);
const nuevaProfesion = async (page, nombre) =>
  (await ajax(page, 'ordenpago.ajax.php', { accion: 'registrarProfesionRapida', nombreProfesion: nombre })).profesion.IdProfesion;

test.describe('Correcciones del ADM', () => {
  test('profesión mal escrita: no se renombra a una existente, se une y se elimina sin uso', async ({ page }) => {
    await ingresar(page, 'adm');
    const n = unico();
    const mal = await nuevaProfesion(page, `PRUEBA BIOQIMICO ${n}`);
    const bien = await nuevaProfesion(page, `PRUEBA BIOQUIMICO ${n}`);
    const sinUso = await nuevaProfesion(page, `PRUEBA SIN USO ${n}`);
    const { respuesta } = await registrarEstudiante(page, { IdProfesion: mal });
    expect(respuesta.success, respuesta.mensaje).toBe(true);
    const estudianteID = valor(`SELECT EstudianteID FROM estudiante WHERE IdProfesion = ${mal}`);

    // La página lista la profesión con su estudiante
    await page.goto('catalogos');
    await page.locator('#tabProfesiones').click();
    await page.locator('#buscarCatalogo').fill(`BIOQIMICO ${n}`);
    await expect(page.locator('#tablaCatalogo tr')).toHaveCount(1);
    await expect(page.locator('#tablaCatalogo tr td').nth(1)).toHaveText('1');

    // Renombrar a la que ya existe: se pide unir
    let r = await catalogo(page, { accion: 'editar', catalogo: 'profesion', id: mal, nombre: `prueba  bioquimico ${n}` });
    expect(r.success).toBe(false);
    expect(r.mensaje).toContain('Unir con');

    // Con estudiantes no se elimina
    r = await catalogo(page, { accion: 'eliminar', catalogo: 'profesion', id: mal });
    expect(r.success).toBe(false);

    // Unir: el estudiante pasa a la correcta y la mal escrita desaparece
    r = await catalogo(page, { accion: 'unir', catalogo: 'profesion', id: mal, destinoId: bien });
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT IdProfesion FROM estudiante WHERE EstudianteID = ${estudianteID}`)).toBe(String(bien));
    expect(valor(`SELECT COUNT(*) FROM profesion WHERE IdProfesion = ${mal}`)).toBe('0');

    // Corregir el nombre y eliminar la que no se usa
    r = await catalogo(page, { accion: 'editar', catalogo: 'profesion', id: bien, nombre: `prueba bioquímico farmacéutico ${n}` });
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT NombreProfesion FROM profesion WHERE IdProfesion = ${bien}`)).toBe(`PRUEBA BIOQUÍMICO FARMACÉUTICO ${n}`);
    r = await catalogo(page, { accion: 'eliminar', catalogo: 'profesion', id: sinUso });
    expect(r.success, r.mensaje).toBe(true);
  });

  test('universidad: corregir nombre, tipo y estado; la misma escrita de otra forma pide unir', async ({ page }) => {
    await ingresar(page, 'adm');
    const n = unico();
    const crear = async (nombre) => (await ajax(page, 'ordenpago.ajax.php',
      { accion: 'registrarUniversidadRapida', nombreUniversidad: nombre, tipoUniversidad: 'PRIVADA' })).universidad.IdUniversidad;
    const id = await crear(`UNIVERCIDAD PRUEBA ${n}`);
    const otra = await crear(`UNIVERSIDAD OTRA PRUEBA ${n} (UOP${n})`);

    let r = await catalogo(page, { accion: 'editar', catalogo: 'universidad', id, nombre: `Universidad Prueba ${n}`, tipo: 'PUBLICA', estado: 0 });
    expect(r.success, r.mensaje).toBe(true);
    expect(valor(`SELECT CONCAT(NombreUniversidad, '|', TipoUniversidad, '|', Estado) FROM universidad WHERE IdUniversidad = ${id}`))
      .toBe(`UNIVERSIDAD PRUEBA ${n}|PUBLICA|0`);

    // Solo la sigla de otra universidad = la misma: pide unir
    r = await catalogo(page, { accion: 'editar', catalogo: 'universidad', id, nombre: `UOP${n}`, tipo: 'PUBLICA', estado: 1 });
    expect(r.success).toBe(false);
    expect(r.repetidoId).toBe(Number(otra));
  });

  test('corregir el C.I. de un estudiante desde Estudiantes: cambia también su usuario', async ({ page }) => {
    await ingresar(page, 'adm');
    const { ci, respuesta } = await registrarEstudiante(page);
    expect(respuesta.success, respuesta.mensaje).toBe(true);
    const estudianteID = valor(`SELECT EstudianteID FROM estudiante WHERE Ci = ${ci}`);
    // Cuenta con el C.I. como usuario (como se crean)
    valor(`INSERT INTO usuario (EstudianteID, Usuario, Password, Tipo) VALUES (${estudianteID}, '${ci}', 'x', 'EST'); SELECT 1`);
    const nuevo = unico();

    await page.goto('estudiantes');
    await page.locator('input[type="search"]').first().fill(ci);
    await page.locator(`.btnEditarEstudiante[data-estudiante-id="${estudianteID}"]`).click();
    await expect(page.locator('#inputCiEditar')).toHaveValue(ci);
    await page.locator('#ModalEditarEstudiante .btnCorregirCi').click();
    await page.locator('#corregirCiNuevo').fill(nuevo);
    await page.locator('#btnGuardarCorregirCi').click();
    await expect(page.locator('#corregirCiMensaje')).toContainText(`${ci} → ${nuevo}`);

    expect(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`)).toBe(nuevo);
    expect(valor(`SELECT Usuario FROM usuario WHERE EstudianteID = ${estudianteID}`)).toBe(nuevo);

    // C.I. de otro estudiante: no
    const r = await ajax(page, 'correccionci.ajax.php', { accion: 'corregir', tipo: 'estudiante', id: estudianteID,
      ci: valor(`SELECT Ci FROM estudiante WHERE EstudianteID <> ${estudianteID} LIMIT 1`) });
    expect(r.success).toBe(false);
    expect(r.mensaje).toContain('ya pertenece a otro estudiante');
  });

  test('corregir el C.I. de un docente desde Docentes (página con SweetAlert v1)', async ({ page }) => {
    await ingresar(page, 'adm');
    const docenteID = valor('SELECT MIN(DocenteID) FROM docente WHERE EsExtranjero = 0');
    const ci = valor(`SELECT Ci FROM docente WHERE DocenteID = ${docenteID}`);
    const nuevo = unico();
    await page.goto('docentes');
    await page.locator(`.btnEditarDocente[data-docente-id="${docenteID}"]`).first().click();
    await expect(page.locator('#editCi')).toHaveValue(ci);
    await page.locator('#ModalEditarDocente .btnCorregirCi').click();
    await page.locator('#corregirCiNuevo').fill(nuevo);
    await page.locator('#btnGuardarCorregirCi').click();
    await expect(page.locator('#corregirCiMensaje')).toContainText(`${ci} → ${nuevo}`);
    expect(valor(`SELECT Ci FROM docente WHERE DocenteID = ${docenteID}`)).toBe(nuevo);
    // Se deja como estaba (otras pruebas usan a este docente)
    const r = await ajax(page, 'correccionci.ajax.php', { accion: 'corregir', tipo: 'docente', id: docenteID, ci });
    expect(r.success, r.mensaje).toBe(true);
  });

  test('Secretaría no puede corregir catálogos ni C.I.', async ({ page }) => {
    await ingresar(page, 'sec');
    await page.goto('catalogos');
    await expect(page.locator('#tablaCatalogo')).toHaveCount(0); // lo manda al panel (permisos.spec.js)
    expect((await catalogo(page, { accion: 'listar', catalogo: 'profesion' })).success).toBe(false);
    const estudianteID = valor('SELECT MIN(EstudianteID) FROM estudiante');
    expect((await ajax(page, 'correccionci.ajax.php', { accion: 'corregir', tipo: 'estudiante', id: estudianteID, ci: '99999999' })).success).toBe(false);

    // Tampoco ve el botón en Estudiantes
    await page.goto('estudiantes');
    await expect(page.locator('.btnCorregirCi')).toHaveCount(0);
  });
});
