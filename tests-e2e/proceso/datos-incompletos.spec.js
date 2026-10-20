// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, PROGRAMA_ID, registrarEstudiante, preregistrar, ordenPendiente, validarVoucherMatricula } from './ayudantes-proceso.js';
import { envioCrudo, cookieDeSesion, cuerpoFormulario, cuerpoMultipart } from '../envio-crudo.js';

// Datos que NO llegan completos o guardado que falla a la mitad: nunca debe quedar
// un registro a medias. (El caso "se guardó pero no llegó la respuesta" está en
// corte-internet.spec.js.)

const SERVIDOR = 'http://localhost:8099/';

// Los campos obligatorios primero y la descripción al final: el corte cae en medio de
// la descripción, así que si el servidor ejecutara con lo que alcanzó a llegar,
// guardaría un evento con la descripción cortada y la prueba lo detectaría.
const camposEvento = (titulo) => ({
  accion: 'registrarEvento',
  tipoEvento: 'ACTIVIDAD',
  titulo,
  programaID: String(PROGRAMA_ID),
  fechaInicio: '2026-12-02 08:00:00',
  fechaFin: '2026-12-02 10:00:00',
  descripcion: 'DESCRIPCION LARGA DE PRUEBA '.repeat(20),
});

const contarEventos = (titulo) => valor(`SELECT COUNT(*) FROM calendario_evento WHERE Titulo = '${titulo}'`);
const esperar = (ms) => new Promise((r) => setTimeout(r, ms));

test.describe('Datos que no llegan completos', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  test('control: el mismo envío completo SÍ se registra (el método de prueba funciona)', async ({ page }) => {
    const titulo = `COMPLETO ${unico()}`;
    const r = await envioCrudo({
      url: SERVIDOR + 'ajax/calendarioevento.ajax.php',
      cookie: await cookieDeSesion(page),
      tipo: 'application/x-www-form-urlencoded',
      cuerpo: cuerpoFormulario(camposEvento(titulo)),
    });
    expect(r.texto).toContain('"success":true');
    expect(contarEventos(titulo)).toBe('1');
  });

  test('formulario cortado a mitad del envío: no se registra nada', async ({ page }) => {
    const titulo = `CORTADO ${unico()}`;
    const cuerpo = cuerpoFormulario(camposEvento(titulo));
    const r = await envioCrudo({
      url: SERVIDOR + 'ajax/calendarioevento.ajax.php',
      cookie: await cookieDeSesion(page),
      tipo: 'application/x-www-form-urlencoded',
      cuerpo,
      enviar: cuerpo.length - 200, // falta el final de la descripción
    });
    expect(r.respondio).toBe(false);
    await esperar(2000);
    expect(contarEventos(titulo)).toBe('0');
  });

  test('envío con archivos (multipart) cortado: no se registra nada', async ({ page }) => {
    const titulo = `CORTADO MP ${unico()}`;
    const { tipo, cuerpo } = cuerpoMultipart(camposEvento(titulo));
    const r = await envioCrudo({
      url: SERVIDOR + 'ajax/calendarioevento.ajax.php',
      cookie: await cookieDeSesion(page),
      tipo,
      cuerpo,
      enviar: cuerpo.length - 200,
    });
    expect(r.respondio).toBe(false);
    await esperar(2000);
    expect(contarEventos(titulo)).toBe('0');
  });
});

test.describe('Falla a mitad del guardado (transacción)', () => {
  // Trigger temporal que rompe el TERCER paso de la validación del voucher de matrícula
  // (el plan de pago en pagoprograma), cuando ya se escribieron los dos primeros:
  // la inscripción (estudianteprograma) y la orden confirmada (ordenpago).
  const TRIGGER = 'tr_prueba_falla_pagoprograma';

  test.afterEach(() => {
    sql(`DROP TRIGGER IF EXISTS ${TRIGGER};`);
  });

  test('validar voucher de matrícula: si falla un paso no queda nada a medias', async ({ page }) => {
    await ingresar(page, 'adm');
    const { respuesta } = await registrarEstudiante(page);
    expect(respuesta.success, respuesta.mensaje).toBe(true);
    const estudianteID = respuesta.estudianteID;
    await preregistrar(page, estudianteID);
    const idOrden = ordenPendiente(estudianteID);
    expect(idOrden, 'se debió crear la orden de matrícula').not.toBeNull();
    const voucher = unico() + '-01';

    sql(`CREATE TRIGGER ${TRIGGER} BEFORE INSERT ON pagoprograma FOR EACH ROW
         SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Falla simulada a mitad del guardado';`);

    const fallida = await validarVoucherMatricula(page, idOrden, voucher);
    expect(fallida.success).toBe(false);

    // Nada de los pasos anteriores quedó guardado
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('PENDIENTE');
    expect(valor(`SELECT idInscripcion FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('NULL');
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('0');
    expect(valor(`SELECT COUNT(*) FROM pagoprograma pp JOIN estudianteprograma ep ON ep.idInscripcion = pp.idInscripcion WHERE ep.EstudianteID = ${estudianteID}`)).toBe('0');

    // Sin la falla, la misma validación (mismo voucher) funciona: el voucher no quedó "usado"
    sql(`DROP TRIGGER ${TRIGGER};`);
    const correcta = await validarVoucherMatricula(page, idOrden, voucher);
    expect(correcta.success, correcta.mensaje).toBe(true);
    expect(valor(`SELECT Estado FROM ordenpago WHERE IdOrdenPago = ${idOrden}`)).toBe('CONFIRMADO');
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('1');
  });
});
