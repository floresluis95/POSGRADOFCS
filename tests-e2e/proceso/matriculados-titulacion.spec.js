// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, ajax, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Matriculados: aviso y filtro de certificados para la titulación. Solo aparecen cuando el
// programa ya concluyó (FechaInicio + DuracionMeses antes de hoy). Solo avisa, no bloquea.

const grado = () => String(valor(`SELECT REPLACE(UPPER(GradoAcademico), 'Í', 'I') FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
let fechaInicioOriginal;

test.beforeAll(() => {
  fechaInicioOriginal = String(valor(`SELECT FechaInicio FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`));
  sql('DELETE FROM certificadotitulacion');
  sql(`INSERT INTO certificadotitulacion (Clave, GradoAcademico) VALUES ('MEDIO_MAGNETICO', '${grado()}')`);
});

test.afterAll(() => {
  // Las demás pruebas esperan el programa en curso y sin certificados exigidos
  sql(`UPDATE programa SET FechaInicio = '${fechaInicioOriginal}' WHERE ProgramaID = ${PROGRAMA_ID}`);
  sql('DELETE FROM certificadotitulacion');
});

/** Filas de Matriculados (HTML) del programa de pruebas con el filtro de titulación dado. */
const tabla = (page, titulacionFiltro = '') => page.request.post('ajax/inscripcionmodulo.ajax.php', {
  form: { accion: 'cargarTablaMatriculados', programaIDFiltro: String(PROGRAMA_ID), estadoFiltro: 'TODOS', titulacionFiltro },
}).then((r) => r.text());

test('solo con el programa concluido: etiqueta "Titulación" y filtro Listos / Les faltan', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  // Programa en curso: no se muestra nada de titulación y el filtro no trae a nadie
  sql(`UPDATE programa SET FechaInicio = CURDATE() WHERE ProgramaID = ${PROGRAMA_ID}`);
  let html = await tabla(page);
  expect(html).toContain(ci);
  expect(html).not.toContain('mt-titulacion');
  expect(await tabla(page, 'FALTAN')).not.toContain(ci);

  // Programa concluido: 0/1, en "Les faltan" y no en "Listos"
  sql(`UPDATE programa SET FechaInicio = DATE_SUB(CURDATE(), INTERVAL DuracionMeses + 1 MONTH) WHERE ProgramaID = ${PROGRAMA_ID}`);
  html = await tabla(page);
  expect(html).toMatch(/Titulación 0\/1/);
  expect(html).toContain('Falta para la titulación: Medio Magnético');
  expect(await tabla(page, 'FALTAN')).toContain(ci);
  expect(await tabla(page, 'LISTOS')).not.toContain(ci);

  // Con el Medio Magnético generado pasa a "Listos"
  await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: 'PRUEBA TITULACION',
      curso: 'ESPECIALIDAD', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'T', vaucher: '1603' + String(unico()).slice(-4),
      monto: '42', fecha: '2026-10-10', coordinador: 'C', director: 'D',
    },
  });
  expect(await tabla(page, 'LISTOS')).toContain(ci);
  expect(await tabla(page, 'FALTAN')).not.toContain(ci);

  // En pantalla: el filtro "Listos" lo muestra y la etiqueta lleva a Registro de documentos
  await page.goto('matriculados');
  await page.locator('#selectTitulacionFiltro').selectOption('LISTOS', { force: true });
  const etiqueta = page.locator('#tablaMatriculadosBody tr', { hasText: ci }).locator('a.mt-titulacion');
  await expect(etiqueta).toContainText('Titulación 1/1', { timeout: 15_000 });
  await etiqueta.click();
  await expect(page).toHaveURL(/registrodocumentos\?ci=/);
  await expect(page.locator('#rd-titulacion')).toContainText('1 de 1', { timeout: 15_000 });
});
