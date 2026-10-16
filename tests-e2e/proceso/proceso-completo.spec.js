// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, IMAGEN_VOUCHER, PROGRAMA_ID } from './ayudantes-proceso.js';

// Recorrido completo por la PANTALLA, como lo haría la secretaría, de punta a punta:
// pre-registro de un estudiante nuevo → validación del voucher de matrícula (inscripción) →
// aparece en Matriculados → orden de cuota y su pago → nota de un módulo → informes.
// Cada paso usa lo que dejó el anterior y comprueba en la base que quedó bien registrado.

test.describe.configure({ mode: 'serial' });

const hoy = new Date().toISOString().slice(0, 10);
const ci = unico();
const estudiante = { nombre: 'ANA PRUEBA', apaterno: 'PROCESO', amaterno: 'COMPLETO' };
const voucherMatricula = ci + '-01';
const voucherCuota = ci + '-02';
const MONTO_CUOTA = 3000;

/** Datos que se van llenando a medida que avanza el proceso */
const datos = { estudianteID: '', idInscripcion: '', matricula: '' };

/** Cierra el aviso de SweetAlert (v1 o v2) que haya en pantalla */
async function aceptarAviso(page, texto) {
  const aviso = page.locator('.swal-modal, .swal2-popup').filter({ hasText: texto });
  await expect(aviso).toBeVisible({ timeout: 15_000 });
  const boton = aviso.locator('.swal-button--confirm, .swal-button, .swal2-confirm').first();
  if (await boton.isVisible()) await boton.click();
}

test.describe('Proceso completo: del registro a los informes', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('1. Pre-registro: estudiante nuevo y orden de pago de matrícula', async ({ page }) => {
    datos.matricula = valor(`SELECT CostoMatricula FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`) ?? '';
    await page.goto('preregistro');
    await page.locator('button[data-target="#modalPasoEstudiante"]').first().click();

    // PASO 1: datos del estudiante
    const paso1 = page.locator('#modalPasoEstudiante');
    await expect(paso1).toBeVisible();
    // Primero se busca el C.I.: como no existe, se habilitan los campos para registrarlo
    await paso1.locator('#buscarPorCi').fill(ci);
    await paso1.locator('#btnBuscarPorCi').click();
    await expect(paso1.locator('#estadoBusquedaEstudiante')).toContainText('No existe');
    await expect(paso1.locator('#inputCi')).toHaveValue(ci);
    await paso1.locator('#selectExpedido').selectOption('OR');
    await paso1.locator('#fechaNacimiento').fill('1994-03-15');
    await paso1.locator('#fechaNacimiento').dispatchEvent('change');
    await paso1.locator('#inputNombres').fill(estudiante.nombre);
    await paso1.locator('#apaterno').fill(estudiante.apaterno);
    await paso1.locator('#amaterno').fill(estudiante.amaterno);
    if (!(await paso1.locator('#Edad').inputValue())) await paso1.locator('#Edad').fill('32');
    await paso1.locator('#Lugarn').selectOption({ index: 1 });
    await paso1.locator('#emailInput').fill(`ana${ci}@correo-prueba.com`);
    await paso1.locator('#inputCelular').fill('71234567');
    await paso1.locator('#IdProfesion').selectOption({ index: 1 });
    // Universidad (pública: la matrícula no es editable) y fecha de egreso, obligatorias
    await paso1.locator('#IdUniversidad').selectOption(valor("SELECT MIN(IdUniversidad) FROM universidad WHERE TipoUniversidad = 'PUBLICA'") ?? '');
    await paso1.locator('#FechaEgreso').fill('2019-11-30');
    await page.locator('#btnWizardPaso1Siguiente').click();

    // PASO 2: programa
    const paso2 = page.locator('#modalPasoPrograma');
    await expect(paso2).toBeVisible();
    const grado = valor(`SELECT GradoAcademico FROM programa WHERE ProgramaID = ${PROGRAMA_ID}`);
    await paso2.locator('#gradoAcademico').selectOption(grado ?? '');
    await expect(paso2.locator(`#programa option[value="${PROGRAMA_ID}"]`)).toHaveCount(1);
    await paso2.locator('#programa').selectOption(String(PROGRAMA_ID));
    await page.locator('#btnWizardPaso2Siguiente').click();

    // PASO 3: plan de pago y cuenta
    const paso3 = page.locator('#modalPasoPago');
    await expect(paso3).toBeVisible();
    await paso3.locator('#tipoPagoCodigo').selectOption('REGULAR');
    await expect(paso3.locator('#cuentaPago option[value]:not([value=""])').first()).toBeAttached();
    await paso3.locator('#cuentaPago').selectOption({ index: 1 });
    await expect(paso3.locator('#montoSoloMatricula')).toHaveValue(Number(datos.matricula).toFixed(2));
    await page.locator('#btnWizardPaso3Revisar').click();

    // PASO 4: confirmar
    await expect(page.locator('#modalConfirmarPreregistro')).toBeVisible();
    await expect(page.locator('#resumenEstudianteCi')).toHaveText(ci);
    await page.locator('#modalConfirmarPreregistro button[name="registrarOrdenPago"]').click();
    await aceptarAviso(page, 'Pre-registro exitoso');

    datos.estudianteID = valor(`SELECT EstudianteID FROM estudiante WHERE Ci = '${ci}'`) ?? '';
    expect(datos.estudianteID, 'el estudiante quedó registrado').not.toBe('');
    expect(valor(`SELECT MontoFinal FROM ordenpago WHERE EstudianteID = ${datos.estudianteID} AND Estado = 'PENDIENTE'`))
      .toBe(Number(datos.matricula).toFixed(2));
  });

  test('2. Inscripción: se valida el voucher de matrícula', async ({ page }) => {
    test.skip(!datos.estudianteID, 'Depende del paso 1');
    await page.goto('inscripcion');
    await page.locator('#buscarTermino').fill(ci);
    await page.locator('#btnBuscarPreregistro').click();
    await page.locator('.btn-seleccionar-preregistro').first().click();

    const modal = page.locator('#modalValidarVoucher');
    await expect(modal).toBeVisible();
    await expect(modal.locator('#detalleCi')).toContainText(ci);
    await modal.locator('#voucherNumero_largo').fill(voucherMatricula);
    await modal.locator('#voucherFecha').fill(hoy);
    await modal.locator('#voucherFoto').setInputFiles(IMAGEN_VOUCHER);
    // Al elegir la foto se abre la ventana de recorte (captura-foto.js)
    await page.locator('#capturaRecorteSinCambios').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
    const marcarTodos = modal.locator('#btnMarcarTodosRequisitos');
    if (await marcarTodos.isVisible()) await marcarTodos.click();
    await modal.locator('button[type="submit"]').click();

    // Antes de guardar se muestra el detalle; "Revisar" vuelve al formulario sin inscribir
    const resumen = page.locator('.swal-overlay--show-modal .in-resumen');
    await expect(resumen).toBeVisible();
    await expect(resumen).toContainText(ci);
    await expect(resumen).toContainText(voucherMatricula);
    await page.locator('.swal-modal .swal-button--cancel').click();
    await expect(resumen).toBeHidden();
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${datos.estudianteID}`)).toBe('0');

    await modal.locator('button[type="submit"]').click();
    await page.locator('.swal-modal .swal-button--confirm').click();

    // Inscrito: pasa a Matriculados filtrado por su C.I.
    await expect(page).toHaveURL(/\/matriculados$/, { timeout: 15_000 });
    await expect(page.locator('#tablaMatriculados_filter input')).toHaveValue(ci);
    await expect(page.locator('#tablaMatriculados tbody tr').filter({ hasText: ci })).toHaveCount(1);

    await expect.poll(() => valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${datos.estudianteID}`), { timeout: 15_000 })
      .not.toBeNull();
    datos.idInscripcion = valor(`SELECT idInscripcion FROM estudianteprograma WHERE EstudianteID = ${datos.estudianteID}`) ?? '';
    expect(valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${datos.idInscripcion}`)).toBe(voucherMatricula);
    expect(valor(`SELECT TipoPlan FROM pagoprograma WHERE idInscripcion = ${datos.idInscripcion}`), 'el plan regular se crea al validar').toBe('REGULAR');
    expect(valor(`SELECT Estado FROM ordenpago WHERE EstudianteID = ${datos.estudianteID} ORDER BY IdOrdenPago LIMIT 1`)).toBe('CONFIRMADO');
  });

  test('3. Matriculados: el estudiante aparece en la lista', async ({ page }) => {
    test.skip(!datos.idInscripcion, 'Depende del paso 2');
    await page.goto('matriculados');
    await page.locator('#tablaMatriculados_filter input').fill(ci);
    await expect(page.locator('#tablaMatriculados tbody tr').filter({ hasText: ci })).toHaveCount(1);
  });

  test('4. Gestión de pagos: orden de cuota y registro de su voucher', async ({ page }) => {
    test.skip(!datos.idInscripcion, 'Depende del paso 2');
    // La orden se imprime en una pestaña nueva: no hace falta revisarla aquí
    page.context().on('page', (nueva) => nueva.close().catch(() => {}));

    await page.goto('matriculados');
    await page.evaluate((id) => jQuery(`.btn-gestionar-pagos[data-idinscripcion="${id}"]`).first().trigger('click'), datos.idInscripcion);
    const modal = page.locator('#modalInscribirModulo');
    await expect(modal).toBeVisible();

    // Orden de pago de la cuota
    await expect(modal.locator('#opMontoCuota')).toBeVisible({ timeout: 15_000 });
    await modal.locator('#opMontoCuota').fill(String(MONTO_CUOTA));
    await modal.locator('#opCuentaPago').selectOption({ index: 1 }).catch(() => {});
    await modal.locator('#opFirma').selectOption({ index: 1 }, { timeout: 3_000 }).catch(() => {});
    await modal.locator('#opBtnGenerarOrden').click();
    // Sin responsable de la firma (catálogo vacío): "Generar de todas formas"
    if (await page.locator('.swal2-popup', { hasText: 'responsable de la firma' }).isVisible()) {
      await page.locator('.swal2-cancel').click();
    }
    // Resumen para verificar monto, cuota, programa y cuenta antes de generar
    await page.locator('.swal2-confirm').click();
    await expect.poll(() => valor(`SELECT MontoTotal FROM ordenpago WHERE idinscripcion = ${datos.idInscripcion} AND Estado = 'PENDIENTE'`), { timeout: 15_000 })
      .toBe(MONTO_CUOTA.toFixed(2));

    // Pago de la cuota con su voucher
    // El aviso "Orden generada" se cierra solo
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    await modal.locator('#tab-pago-modulos-link').click();
    await expect(modal.locator('#btnGuardarPagoCuota')).toBeEnabled({ timeout: 15_000 });
    await modal.locator('#numeroVaucher_largo').fill(voucherCuota);
    await modal.locator('#fmodulo').setInputFiles(IMAGEN_VOUCHER);
    // Al elegir la foto se abre la ventana de recorte (captura-foto.js)
    await page.locator('#capturaRecorteSinCambios').click();
    await expect(page.locator('#modalCapturaRecorte')).toBeHidden();
    await modal.locator('#btnGuardarPagoCuota').click();
    // Resumen antes de guardar: monto, N° de voucher y aviso de revisar la fecha (es la de hoy)
    const resumen = page.locator('.swal2-popup.vc-confirmar');
    await expect(resumen).toContainText(voucherCuota);
    await expect(resumen).toContainText('Bs. 3,000.00');
    await expect(resumen.locator('.vc-confirmar-fecha.is-hoy')).toBeVisible();
    // La fecha del depósito se elige en el mismo resumen: al cambiarla se quita el aviso
    const ayer = new Date(Date.now() - 86_400_000).toLocaleDateString('sv-SE');
    await resumen.locator('#vcConfirmarFecha').fill(ayer);
    await expect(resumen.locator('.vc-confirmar-fecha.is-hoy')).toHaveCount(0);
    await resumen.locator('.swal2-confirm').click();

    await expect.poll(() => valor(`SELECT montocuota FROM pagomodulo WHERE idinscripcion = ${datos.idInscripcion} AND Estado = 'PAGADO'`), { timeout: 15_000 })
      .toBe(MONTO_CUOTA.toFixed(2));
    expect(valor(`SELECT DATE(fechapago) FROM pagomodulo WHERE idinscripcion = ${datos.idInscripcion} AND Estado = 'PAGADO'`)).toBe(ayer);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${datos.idInscripcion} AND Estado = 'PENDIENTE'`)).toBe('0');

    // La pestaña "Orden de pago" se actualiza sola: ya no muestra la orden como pendiente
    await expect(page.locator('.swal2-popup')).toHaveCount(0, { timeout: 10_000 });
    await modal.locator('#tab-orden-pago-link').click();
    await expect(modal.locator('#btnReimprimirOrdenCuota')).toHaveCount(0);
    await expect(modal.locator('#opMontoCuota')).toBeVisible({ timeout: 15_000 });
  });

  test('5. Notas: se registra la calificación del primer módulo', async ({ page }) => {
    test.skip(!datos.idInscripcion, 'Depende del paso 2');
    const [idModulo, docenteID] = (valor(`SELECT CONCAT(Idmodulo, ',', DocenteID) FROM modulos WHERE ProgramaId = ${PROGRAMA_ID} AND estadomodulo = 'ACTIVO' AND DocenteID IS NOT NULL ORDER BY Idmodulo LIMIT 1`) ?? ',').split(',');
    test.skip(!idModulo, 'El programa de prueba no tiene módulos activos con docente');

    await page.goto('rnotasestudiante');
    await expect(page.locator(`#select-docente option[value="${docenteID}"]`)).toHaveCount(1, { timeout: 15_000 });
    await page.locator('#select-docente').selectOption(docenteID);
    await page.locator(`.btn-evaluar[data-modulo-id="${idModulo}"]`).first().click();

    const nota = page.locator(`.input-nota[data-estudiante-id="${datos.estudianteID}"]`);
    await expect(nota).toBeVisible({ timeout: 15_000 });
    // El sistema exige la nota de TODOS los estudiantes del módulo para guardar:
    // a los demás (que están en la copia de la base) se les pone una nota cualquiera.
    for (const otra of await page.locator('.input-nota').all()) {
      if (!(await otra.inputValue())) await otra.fill('80');
    }
    await nota.fill('85');
    await page.locator('#btn-guardar-notas').click();
    const confirmar = page.locator('.swal2-confirm');
    if (await confirmar.isVisible({ timeout: 3000 }).catch(() => false)) await confirmar.click();

    await expect.poll(() => Number(valor(`SELECT Nota FROM calificacion WHERE EstudianteID = ${datos.estudianteID} AND Idmodulo = ${idModulo}`)), { timeout: 15_000 })
      .toBe(85);
  });

  test('6. Informes: recibo, ficha, compromiso y reportes PDF', async ({ page }) => {
    test.skip(!datos.idInscripcion, 'Depende del paso 2');

    // Recibo de pago (HTML): el estudiante, su matrícula y la cuota pagada
    await page.goto(`vistas/componentes/recibo-pago-modulos.php?idinscripcion=${datos.idInscripcion}`);
    await expect(page.locator('body')).toContainText(ci);
    await expect(page.locator('body')).toContainText('Bs. 3,000.00');
    await expect(page.locator('body')).toContainText(voucherCuota);

    // Documentos PDF del estudiante
    for (const pdf of ['pdfestudiante', 'pdfcompromiso']) {
      const r = await page.request.get(`extensiones/tcpdf/pdf/${pdf}.php?idinscripcion=${datos.idInscripcion}`);
      expect(r.status(), pdf).toBe(200);
      expect((await r.body()).subarray(0, 4).toString(), `${pdf} debe ser un PDF`).toBe('%PDF');
    }

    // Reportes PDF (pantalla Reportes), filtrados por el programa del estudiante
    for (const tipo of ['estudiantes', 'por_programa', 'modulos', 'ejecutivo', 'recaudacion']) {
      const r = await page.request.post('ajax/reportes.ajax.php', {
        form: { tipo_reporte: tipo, sede: '', programa_id: String(PROGRAMA_ID), fecha_desde: '', fecha_hasta: '' },
      });
      expect(r.status(), tipo).toBe(200);
      const cuerpo = await r.body();
      expect(cuerpo.subarray(0, 4).toString(), `el reporte "${tipo}" debe ser un PDF (respondió: ${cuerpo.subarray(0, 200).toString()})`).toBe('%PDF');
    }
  });
});
