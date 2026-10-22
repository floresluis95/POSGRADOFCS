// @ts-check
import { test, expect } from '@playwright/test';
import { valor, ingresar, estudianteInscrito, PROGRAMA_ID } from './ayudantes-proceso.js';

// Registro de documentos: cada certificado guarda los datos con que se generó, se
// puede reimprimir (y corregir) desde ahí, y se marca cuando vuelve aprobado por el
// decano (solo control). Si se corrige un documento aprobado, vuelve a "por aprobar".

test('un documento se aprueba, se reimprime corregido y vuelve a quedar por aprobar', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  const nombre = valor(`SELECT CONCAT(Apaterno, ' ', Amaterno, ' ', Nombre) FROM estudiante WHERE EstudianteID = ${estudianteID}`);

  // Generar la carta de Medio Magnético (lo que hace su pantalla al imprimir; es el
  // único documento que no exige calificaciones registradas)
  const pdf = await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: String(nombre),
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA', vaucher: 'CERT-001',
      fecha: '2026-10-10', coordinador: 'COORDINADOR DE PRUEBA', director: 'DIRECTOR DE PRUEBA',
    },
  });
  expect(pdf.headers()['content-type']).toContain('application/pdf');
  const condicion = `TipoDocumento = 'MEDIO_MAGNETICO' AND EstudianteID = ${estudianteID}`;
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE ${condicion}`);
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('PENDIENTE');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.vaucher')) FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('CERT-001');

  // En Registro de documentos aparece "Por aprobar" y se marca aprobado por el decano
  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(String(ci));
  await page.locator('#btn-buscar-ci').click();
  const item = page.locator('#tabla-estudiante-body tr', { hasText: 'Medio Magnético' });
  await expect(item).toContainText('Por aprobar');
  await item.locator('[data-aprobar]').click();
  await page.locator('.swal2-confirm').click();
  await expect(item).toContainText('Aprobado');
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('APROBADO');

  // Reimprimir corrigiendo el voucher: no crea otro registro y vuelve a "por aprobar"
  await item.locator('[data-editar]').click();
  await expect(page.locator('#ventana-reimprimir')).toBeVisible();
  await expect(page.locator('#reimp-vaucher')).toHaveValue('CERT-001');
  await page.locator('#reimp-vaucher').fill('CERT-002');
  await Promise.all([page.waitForEvent('popup'), page.locator('#btn-guardar-imprimir').click()]);
  await expect.poll(() => valor(`SELECT NumeroVaucher FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('CERT-002');
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
  expect(valor(`SELECT Estado FROM documentoemitido WHERE IdDocumento = ${idDocumento}`)).toBe('PENDIENTE');
  await expect(item).toContainText('Por aprobar');

  // Reimprimir sin cambios no cambia nada
  await item.locator('[data-editar]').click();
  await expect(page.locator('#reimp-vaucher')).toHaveValue('CERT-002');
  await Promise.all([page.waitForEvent('popup'), page.locator('#btn-guardar-imprimir').click()]);
  await page.waitForTimeout(1500);
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
});

test('el Certificado de Revisión se genera desde su pantalla y queda en Registro de documentos', async ({ page }) => {
  await ingresar(page, 'adm'); // los certificados son solo del Administrador (2026-10-22)
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const nombreCertificado = String(valor(`SELECT UPPER(CONCAT(Nombre, ' ', Apaterno, ' ', Amaterno)) FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  await page.goto('certificadorevision');
  await page.locator('#cert-termino').fill(ci);
  await expect(page.locator('#cert-elegido')).toContainText(ci, { timeout: 15_000 });
  await expect(page.locator('#select-inscripcion option:checked')).toHaveAttribute('data-nombre', nombreCertificado);
  await expect(page.locator('#dato-curso')).toContainText(' EN ');
  await page.locator('#input-tratamiento').selectOption('el señor');
  await page.locator('#input-fecha').fill('2026-09-15');
  // "Revisar y generar" muestra los datos y la vista previa; se emite con "Generar e imprimir"
  await page.locator('#btn-generar-certificado').click();
  await expect(page.locator('#certModalDatos')).toContainText(nombreCertificado);
  await Promise.all([page.waitForEvent('popup'), page.locator('#certModalGenerar').click()]);

  const condicion = `TipoDocumento = 'REVISION' AND EstudianteID = ${estudianteID}`;
  await expect.poll(() => valor(`SELECT COUNT(*) FROM documentoemitido WHERE ${condicion}`)).toBe('1');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.tratamiento')) FROM documentoemitido WHERE ${condicion}`)).toBe('el señor');
  expect(valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.nombre')) FROM documentoemitido WHERE ${condicion}`)).toBe(nombreCertificado);

  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  await expect(page.locator('#tabla-estudiante-body tr', { hasText: 'Certificado de Revisión' })).toContainText('Por aprobar');
});

test('un documento por aprobar se puede borrar; uno aprobado no', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`);
  const pdf = await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: 'PRUEBA BORRAR',
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA', vaucher: 'BORRA-' + estudianteID,
      fecha: '2026-10-10', coordinador: 'COORDINADOR DE PRUEBA', director: 'DIRECTOR DE PRUEBA',
    },
  });
  expect(pdf.headers()['content-type']).toContain('application/pdf');
  const idDocumento = valor(`SELECT IdDocumento FROM documentoemitido WHERE EstudianteID = ${estudianteID}`);
  const existe = () => valor(`SELECT COUNT(*) FROM documentoemitido WHERE IdDocumento = ${idDocumento}`);

  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(String(ci));
  await page.locator('#btn-buscar-ci').click();
  const item = page.locator('#tabla-estudiante-body tr', { hasText: 'Medio Magnético' });

  // Aprobado: no aparece el botón y el ajax tampoco lo borra
  await item.locator('[data-aprobar]').click();
  await page.locator('.swal2-confirm').click();
  await expect(item).toContainText('Aprobado');
  await expect(item.locator('[data-borrar-documento]')).toHaveCount(0);
  const rechazo = await page.request.post('ajax/certificado.ajax.php', { form: { accion: 'eliminarDocumento', idDocumento: String(idDocumento) } });
  expect((await rechazo.json()).success).toBe(false);
  expect(existe()).toBe('1');

  // Por aprobar: se borra después de confirmar
  await item.locator('[data-quitar-aprobacion]').click();
  await page.locator('.swal2-confirm').click();
  await expect(item).toContainText('Por aprobar');
  await item.locator('[data-borrar-documento]').click();
  await expect(page.locator('.swal2-popup')).toContainText('BORRA-' + estudianteID);
  await page.locator('.swal2-confirm').click();
  await expect.poll(existe).toBe('0');
  await expect(item.locator('[data-borrar-documento]')).toHaveCount(0);
});

test('al entrar no se lista nada; al buscar se ven TODAS las emisiones del estudiante', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));

  // El mismo certificado emitido 3 veces (cada vez con su propio voucher)
  for (let i = 1; i <= 3; i++) {
    const pdf = await page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
      form: {
        estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: 'PRUEBA VARIAS',
        curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO DE PRUEBA',
        vaucher: `VARIAS${estudianteID}-${i}`, monto: '50', fecha: '2026-10-10', coordinador: 'COORDINADOR', director: 'DIRECTOR',
      },
    });
    expect(pdf.headers()['content-type']).toContain('application/pdf');
  }

  // Al entrar: ni la ficha ni filas; el reporte espera a "Consultar"
  await page.goto('registrodocumentos');
  await expect(page.locator('#rd-vacio-estudiante')).toBeVisible();
  await expect(page.locator('#rd-estudiante')).toBeHidden();
  await page.locator('#tab-reporte').click();
  await expect(page.locator('#tabla-log-body')).toContainText('pulse Consultar');
  await expect(page.locator('#tabla-log-body tr')).toHaveCount(1);
  await page.locator('#tab-estudiante').click();

  // Al buscar: en "Últimos emitidos" solo el más reciente; los otros 2 en "Anteriores"
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  const ultimo = page.locator('#tabla-estudiante-body tr', { hasText: 'Medio Magnético' });
  await expect(ultimo).toHaveCount(1, { timeout: 15_000 });
  await expect(ultimo).toHaveClass(/rd-ultimo/);
  await expect(ultimo).toContainText(`VARIAS${estudianteID}-3`);
  await expect(page.locator('#tabla-estudiante-pie')).toContainText('1 documento(s)');
  await expect(page.locator('#n-ultimos')).toHaveText('1');
  await expect(page.locator('#n-anteriores')).toHaveText('2');
  await expect(page.locator('#tabla-anteriores')).toBeHidden();
  await page.locator('#subtab-anteriores').click();
  await expect(page.locator('#tabla-estudiante')).toBeHidden();
  for (const i of [1, 2]) await expect(page.locator('#tabla-anteriores-body')).toContainText(`VARIAS${estudianteID}-${i}`);
  await expect(page.locator('#tabla-anteriores-pie')).toContainText('2 documento(s)');
  await expect(page.locator('#tabla-anteriores-pie')).toContainText('Bs. 100,00');

  // Recibo: vienen marcados los pagos del último día (los 3); con solo uno marcado sale solo ese
  await expect(page.locator('#contador-recibo')).toHaveText('(3)');
  for (const i of [1, 2]) {
    await page.locator('#tabla-anteriores-body tr', { hasText: `VARIAS${estudianteID}-${i}` }).locator('.rd-check-recibo').uncheck();
  }
  await expect(page.locator('#contador-recibo')).toHaveText('(1)');
  const [recibo] = await Promise.all([page.waitForEvent('popup'), page.locator('#btn-imprimir-estudiante').click()]);
  await recibo.waitForLoadState();
  const html = await recibo.content();
  expect(html).toContain(`VARIAS${estudianteID}-3`);
  expect(html).not.toContain(`VARIAS${estudianteID}-1`);
  expect(html).toContain('Bs. 50,00');
  await recibo.close();

  // La casilla del encabezado marca solo su tabla; sin ninguno marcado, avisa
  await page.locator('#check-recibo-anteriores').check();
  await expect(page.locator('#contador-recibo')).toHaveText('(3)');
  await page.locator('#check-recibo-anteriores').uncheck();
  await expect(page.locator('#contador-recibo')).toHaveText('(1)');
  await page.locator('#subtab-ultimos').click();
  await page.locator('#check-recibo-todos').uncheck();
  await expect(page.locator('#contador-recibo')).toHaveText('');
  await page.locator('#btn-imprimir-estudiante').click();
  await expect(page.locator('.swal2-popup')).toContainText('Marque al menos un pago');
});

test('Editar guarda los cambios sin imprimir; un voucher de otro certificado no se acepta', async ({ page }) => {
  await ingresar(page, 'adm');
  const { estudianteID } = await estudianteInscrito(page);
  const ci = String(valor(`SELECT Ci FROM estudiante WHERE EstudianteID = ${estudianteID}`));
  const emitir = (voucher) => page.request.post('tcpdf/pdf/certificado-medio-magnetico-pdf.php', {
    form: {
      estudianteID: String(estudianteID), programaID: String(PROGRAMA_ID), tratamiento: 'El Sr.', nombre: 'PRUEBA EDITAR',
      curso: 'ESPECIALIDAD DE PRUEBA', tipotrabajo: 'TRABAJO DE GRADO', titulo: 'TÍTULO ORIGINAL', vaucher: voucher,
      monto: '50', fecha: '2026-10-10', coordinador: 'COORDINADOR', director: 'DIRECTOR',
    },
  });
  await emitir(`EDITA${estudianteID}-1`);
  await emitir(`EDITA${estudianteID}-2`);
  const id1 = valor(`SELECT IdDocumento FROM documentoemitido WHERE NumeroVaucher = 'EDITA${estudianteID}-1'`);
  const dato = (campo) => valor(`SELECT JSON_UNQUOTE(JSON_EXTRACT(Datos, '$.${campo}')) FROM documentoemitido WHERE IdDocumento = ${id1}`);

  await page.goto('registrodocumentos');
  await page.locator('#input-buscar-ci').fill(ci);
  await page.locator('#btn-buscar-ci').click();
  // El primero ya no es el último emitido: está en "Anteriores"
  await page.locator('#subtab-anteriores').click();
  const fila = page.locator(`#tabla-anteriores-body tr[data-id="${id1}"]`);

  // Guardar cambios: se guarda sin abrir ninguna pestaña
  let pestañas = 0;
  page.on('popup', () => pestañas++);
  await fila.locator('[data-editar]').click();
  await expect(page.locator('#reimp-titulo')).toHaveValue('TÍTULO ORIGINAL');
  await page.locator('#reimp-titulo').fill('TÍTULO CORREGIDO');
  await page.locator('#reimp-monto').fill('75');
  await page.locator('#btn-guardar-documento').click();
  await expect(page.locator('.swal2-popup')).toContainText('Cambios guardados');
  await expect.poll(() => dato('titulo')).toBe('TÍTULO CORREGIDO');
  expect(valor(`SELECT Monto FROM documentoemitido WHERE IdDocumento = ${id1}`)).toBe('75.00');
  await expect(fila).toContainText('Bs. 75,00');
  expect(pestañas).toBe(0);

  // El voucher del otro certificado: no se guarda y avisa
  await fila.locator('[data-editar]').click();
  await page.locator('#reimp-vaucher').fill(`EDITA${estudianteID}-2`);
  await page.locator('#btn-guardar-documento').click();
  await expect(page.locator('.swal2-popup')).toContainText('ya fue registrado');
  expect(String(valor(`SELECT NumeroVaucher FROM documentoemitido WHERE IdDocumento = ${id1}`))).toBe(`EDITA${estudianteID}-1`);

  // Imprimir: el PDF con lo guardado, sin abrir la ventana de edición
  await page.locator('.swal2-confirm').click();
  await page.locator('[data-cerrar-ventana]').first().click();
  const [pdf] = await Promise.all([page.waitForEvent('popup'), fila.locator('[data-imprimir]').click()]);
  await pdf.waitForLoadState();
  await expect(page.locator('#ventana-reimprimir')).not.toBeVisible();
  expect(valor(`SELECT COUNT(*) FROM documentoemitido WHERE EstudianteID = ${estudianteID}`)).toBe('2');
});
