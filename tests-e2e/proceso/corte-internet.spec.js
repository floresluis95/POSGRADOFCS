// @ts-check
import { test, expect } from '@playwright/test';
import { valor, unico, ingresar, PROGRAMA_ID } from './ayudantes-proceso.js';

// Corte de internet: el servidor guardó, pero la respuesta no llegó al navegador.
// Al reenviar los mismos datos no se debe duplicar el registro (control-envio.js +
// ajax/control-solicitud.php). Se usa el registro de eventos del calendario porque,
// sin el control, cada envío crea una fila nueva.

const datosEvento = (titulo) => ({
  accion: 'registrarEvento',
  tipoEvento: 'ACTIVIDAD',
  titulo,
  programaID: String(PROGRAMA_ID),
  fechaInicio: '2026-12-01 08:00:00',
  fechaFin: '2026-12-01 10:00:00',
  descripcion: 'Prueba de corte de internet',
});

// $.ajax desde la página (pasa por control-envio.js); devuelve estado, cabecera y JSON
function enviarDesdePagina(page, datos) {
  return page.evaluate((d) => new Promise((resolver) => {
    window.jQuery.ajax({ url: 'ajax/calendarioevento.ajax.php', type: 'POST', data: d, dataType: 'json' })
      .done((json, _e, xhr) => resolver({ status: xhr.status, repetida: xhr.getResponseHeader('X-Solicitud-Repetida'), json }))
      .fail((xhr) => resolver({ status: xhr.status, repetida: null, json: null }));
  }), datos);
}

const contarEventos = (titulo) => valor(`SELECT COUNT(*) FROM calendario_evento WHERE Titulo = '${titulo}'`);

test.describe('Corte de internet al guardar', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
    await page.goto('calendario'); // pantalla que registra eventos con $.ajax
  });

  test('se guarda una vez, avisa del corte y el reenvío no duplica', async ({ page }) => {
    const titulo = `CORTE ${unico()}`;

    // 1) El servidor procesa, pero el navegador no recibe la respuesta
    let cortar = true;
    await page.route('**/ajax/calendarioevento.ajax.php', async (ruta) => {
      if (!cortar) return ruta.continue();
      cortar = false;
      await ruta.fetch();                       // llega al servidor y se guarda
      await ruta.abort('internetdisconnected'); // la respuesta se pierde
    });

    const primero = await enviarDesdePagina(page, datosEvento(titulo));
    expect(primero.status).toBe(0);
    expect(contarEventos(titulo)).toBe('1');
    await expect(page.getByText('No se sabe si los datos alcanzaron a guardarse', { exact: false })).toBeVisible();
    await page.keyboard.press('Escape');

    // 2) Vuelve la conexión: mismo botón, mismos datos → respuesta del primer envío, sin fila nueva
    const reenvio = await enviarDesdePagina(page, datosEvento(titulo));
    expect(reenvio.status).toBe(200);
    expect(reenvio.repetida).toBe('1');
    expect(reenvio.json.success, reenvio.json.mensaje).toBe(true);
    expect(contarEventos(titulo)).toBe('1');

    // 3) Con la respuesta recibida se olvida el identificador: un envío nuevo sí se registra
    const nuevo = await enviarDesdePagina(page, datosEvento(titulo));
    expect(nuevo.repetida).toBeNull();
    expect(contarEventos(titulo)).toBe('2');
  });

  test('doble clic: dos envíos iguales a la vez registran uno solo', async ({ page }) => {
    const titulo = `DOBLE ${unico()}`;
    const [a, b] = await Promise.all([
      enviarDesdePagina(page, datosEvento(titulo)),
      enviarDesdePagina(page, datosEvento(titulo)),
    ]);
    expect(a.json.success).toBe(true);
    expect(b.json.success).toBe(true);
    expect([a.repetida, b.repetida].filter((r) => r === '1')).toHaveLength(1);
    expect(contarEventos(titulo)).toBe('1');
  });

  test('datos distintos son envíos distintos', async ({ page }) => {
    const titulo = `DISTINTO ${unico()}`;
    await enviarDesdePagina(page, datosEvento(titulo));
    await enviarDesdePagina(page, { ...datosEvento(titulo), descripcion: 'Otra descripción' });
    expect(contarEventos(titulo)).toBe('2');
  });

  test('sin internet aparece la franja roja', async ({ page, context }) => {
    await context.setOffline(true);
    await expect(page.locator('#franjaSinConexion')).toBeVisible();
    await context.setOffline(false);
    await expect(page.locator('#franjaSinConexion')).toBeHidden();
  });
});
