// @ts-check
import { test, expect } from '@playwright/test';
import {
  valor, unico, ingresar, ajax, registrarEstudiante, preregistrar, ordenPendiente,
  validarVoucherMatricula, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// Plan Grupal (decidido el 2026-10-10): el grupo se arma en Pre-Registro, con estudiantes
// del mismo programa. Cada integrante tiene su propia orden y sus propios descuentos, y
// todas las órdenes del grupo comparten el código, que pasa al plan al validar el voucher.
// Matriculados ya no arma grupos: solo los muestra.

const GRUPAL = { tipoPagoCodigo: 'GRUPAL', tipoPago: 'PLAN GRUPAL' };

const codigoDeOrden = (idOrden) => valor(`SELECT PlanPropuestoCodigoGrupo FROM ordenpago WHERE IdOrdenPago = ${idOrden}`);

test.describe('Plan Grupal armado en Pre-Registro', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('el selector de grupo solo aparece con el plan Grupal', async ({ page }) => {
    await page.goto('preregistro');
    const grupo = page.locator('#grupoCodigoGrupoPreregistro');
    await page.locator('#tipoPagoCodigo').selectOption('GRUPAL', { force: true });
    await expect(page.locator('#codigoGrupo option').first()).toHaveText('NUEVO GRUPO');
    expect(await grupo.evaluate((el) => el.style.display)).not.toBe('none');
    await page.locator('#tipoPagoCodigo').selectOption('REGULAR', { force: true });
    expect(await grupo.evaluate((el) => el.style.display)).toBe('none');
  });

  test('dos estudiantes en el mismo grupo, cada uno con sus descuentos', async ({ page }) => {
    const costoPrograma = Number(valor(`SELECT Costo FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));

    // Primer integrante: grupo nuevo
    const primero = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, primero, { ...GRUPAL, porcentajeDescuentoPrograma: '10' });
    const ordenPrimero = ordenPendiente(primero);
    const codigo = codigoDeOrden(ordenPrimero);
    expect(codigo).toMatch(new RegExp(`^GRUPO-${PROGRAMA_ID}-\\d+$`));

    // El grupo ya aparece para elegirlo
    const lista = await ajax(page, 'ordenpago.ajax.php', { accion: 'listarGruposPrograma', programaID: String(PROGRAMA_ID) });
    expect(lista.grupos.map((g) => g.CodigoGrupo)).toContain(codigo);

    // Segundo integrante: se suma al grupo, con otro % del programa
    const segundo = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, segundo, { ...GRUPAL, codigoGrupo: codigo, porcentajeDescuentoPrograma: '25' });
    const ordenSegundo = ordenPendiente(segundo);
    expect(codigoDeOrden(ordenSegundo)).toBe(codigo);

    for (const idOrden of [ordenPrimero, ordenSegundo]) {
      const r = await validarVoucherMatricula(page, idOrden, 'MAT-' + unico());
      expect(r.success, r.mensaje).toBe(true);
    }

    const plan = (estudianteID, campo) => valor(
      `SELECT pp.${campo} FROM pagoprograma pp INNER JOIN estudianteprograma ep ON ep.idInscripcion = pp.idInscripcion
       WHERE ep.EstudianteID = ${estudianteID} AND ep.ProgramaID = ${PROGRAMA_ID}`);
    expect(plan(primero, 'CodigoGrupo')).toBe(codigo);
    expect(plan(segundo, 'CodigoGrupo')).toBe(codigo);
    expect(plan(primero, 'CantidadInscritosGrupo')).toBe('2');
    expect(plan(segundo, 'CantidadInscritosGrupo')).toBe('2');
    expect(plan(primero, 'MontoTotalPagar')).toBe((Math.round(costoPrograma * 90) / 100).toFixed(2));
    expect(plan(segundo, 'MontoTotalPagar')).toBe((Math.round(costoPrograma * 75) / 100).toFixed(2));

    // Matriculados muestra el grupo y ya no tiene el botón para armarlo
    await page.goto('matriculados');
    await expect(page.locator('#btnAbrirPlanGrupal')).toHaveCount(0);
    await expect(page.locator('#tablaMatriculados').getByText(codigo).first()).toBeVisible();
  });

  test('un grupo que no existe en el programa no genera la orden', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID, { ...GRUPAL, codigoGrupo: 'GRUPO-999999-1' });
    expect(ordenPendiente(estudianteID)).toBeNull();
  });

  test('en otro plan el código de grupo se ignora', async ({ page }) => {
    const estudianteID = (await registrarEstudiante(page)).respuesta.estudianteID;
    await preregistrar(page, estudianteID, { codigoGrupo: 'GRUPO-999999-1' });
    const idOrden = ordenPendiente(estudianteID);
    expect(idOrden).not.toBeNull();
    expect(codigoDeOrden(idOrden)).toBe('NULL');
  });
});
