// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, ingresar, ajax } from './ayudantes-proceso.js';

// Registrar Notas: el ADM ve los módulos terminados con notas pendientes (marcados) y avisa
// a sus docentes; el docente ve el aviso en su panel y lo confirma con "Entendido".

// Módulo que ya terminó, no está cerrado y tiene estudiantes activos sin nota
const SQL_PENDIENTE = `SELECT m.Idmodulo FROM modulos m INNER JOIN programa p ON p.ProgramaID = m.ProgramaId
  WHERE m.estadomodulo NOT IN ('VALIDADO','CERRADO','INACTIVO') AND p.Estado = 1 AND m.DocenteID IS NOT NULL
    AND m.FechaFinal < CURDATE()
    AND (SELECT COUNT(DISTINCT ep.EstudianteID) FROM estudianteprograma ep WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO')
      > (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL)`;

test('el ADM ve los pendientes marcados y el docente recibe el aviso', async ({ browser }) => {
  const esperados = sql(SQL_PENDIENTE).length;
  expect(esperados, 'la base de pruebas debe tener algún módulo pendiente').toBeGreaterThan(0);

  // El aviso se prueba con el docente de prueba: se le asigna un módulo pendiente y luego se devuelve
  const idModulo = String(valor(SQL_PENDIENTE + ' ORDER BY m.Idmodulo LIMIT 1'));
  const docenteOriginal = String(valor(`SELECT DocenteID FROM modulos WHERE Idmodulo = ${idModulo}`));
  const docentePrueba = String(valor(`SELECT DocenteID FROM usuario WHERE Usuario = 'prueba_doc'`));
  sql(`UPDATE modulos SET DocenteID = ${docentePrueba} WHERE Idmodulo = ${idModulo}`);
  sql(`DELETE FROM avisocalificacion WHERE Idmodulo = ${idModulo}`);

  try {
    // ADM: la lista cuadra con la base, todos marcados
    const adm = await (await browser.newContext()).newPage();
    await ingresar(adm, 'adm');
    await adm.goto('rnotasestudiante');
    const filas = adm.locator('#rnPendLista tbody tr');
    await expect(filas).toHaveCount(esperados, { timeout: 15_000 });
    await expect(adm.locator('#rnPendN')).toHaveText(String(esperados));
    await expect(adm.locator('#rnPendLista tr.is-marcada')).toHaveCount(esperados);

    // Al elegir al docente, su módulo pendiente aparece marcado en la tabla del paso 2
    await adm.evaluate((id) => jQuery('#select-docente').val(id).trigger('change'), docentePrueba);
    await expect(adm.locator('tr.rn-fila-pendiente').first()).toBeVisible({ timeout: 15_000 });
    await expect(adm.locator('tr.rn-fila-pendiente').first()).toContainText('Falta calificar');
    await adm.goto('rnotasestudiante');
    await expect(filas).toHaveCount(esperados, { timeout: 15_000 });

    // Solo el módulo de prueba marcado → enviar aviso
    await adm.locator('#rnPendTodos').uncheck();
    await adm.locator(`.rn-pend-check[value="${idModulo}"]`).check();
    await expect(adm.locator('#rnPendNotificar')).toContainText('(1 módulo)');
    await adm.locator('#rnPendNotificar').click();
    await adm.locator('#rnPendEnviar').click();
    await expect.poll(() => valor(`SELECT COUNT(*) FROM avisocalificacion WHERE Idmodulo = ${idModulo} AND DocenteID = ${docentePrueba}`)).toBe('1');
    await expect(adm.locator(`.rn-pend-check[value="${idModulo}"]`).locator('xpath=ancestor::tr')).toContainText('Aún no lo vio', { timeout: 10_000 });

    // Docente: ve el aviso en su panel, con el módulo; "Entendido" lo marca como visto
    const doc = await (await browser.newContext()).newPage();
    await ingresar(doc, 'doc');
    const aviso = doc.locator('#avisoCalificaciones');
    await expect(aviso).toBeVisible();
    await expect(aviso).toContainText('Debe registrar calificaciones');
    await expect(aviso).toContainText('La Coordinación de Posgrado le recuerda');
    await doc.screenshot({ path: test.info().outputPath('aviso-docente.png') });
    await doc.locator('#avisoCalificacionesOk').click();
    await expect(aviso).toHaveCount(0);
    await expect.poll(() => valor(`SELECT FechaLeido IS NOT NULL FROM avisocalificacion WHERE Idmodulo = ${idModulo}`)).toBe('1');
    await doc.reload();
    await expect(doc.locator('#avisoCalificaciones')).toHaveCount(0);

    // El ADM ve que ya lo vio
    await adm.reload();
    await expect(adm.locator(`.rn-pend-check[value="${idModulo}"]`).locator('xpath=ancestor::tr')).toContainText('Visto el', { timeout: 15_000 });
    await adm.screenshot({ path: test.info().outputPath('pendientes-adm.png'), fullPage: true });

    // Permisos: el docente no puede listar ni notificar; el ADM no puede "entender" por otro
    expect((await ajax(doc, 'avisocalificacion.ajax.php', { accion: 'pendientes' })).success).not.toBe(true);
  } finally {
    sql(`UPDATE modulos SET DocenteID = ${docenteOriginal} WHERE Idmodulo = ${idModulo}`);
  }
});
