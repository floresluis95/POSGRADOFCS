// @ts-check
import { test, expect } from '@playwright/test';
import { sql, valor, unico, ingresar, enviarFormulario } from './ayudantes-proceso.js';

// Código del programa SEDE-GRADO-N-AÑO: cada sede y grado lleva su propia numeración
// (ORU-ESP-1, ORU-ESP-2…, ORU-MAE-1…). Antes el número era el total de programas del
// sistema y especialidades, maestrías y diplomados compartían la cuenta.
// Se usa una sede propia de la prueba (POTOSÍ → POT) para no depender de los demás datos.

const SEDE = 'POTOSÍ';

function limpiar() {
  sql(`DELETE FROM programa WHERE Sede = '${SEDE}'`);
}

async function crear(page, grado, fecha) {
  const nombre = `PRUEBA CODIGO ${grado} ${unico()}`;
  await enviarFormulario(page, 'programas', {
    NombrePrograma: nombre, GradoAcademico: grado, Sigla: 'PC', DuracionMeses: '12', Modulos: '6',
    FechaInicio: fecha, Sede: SEDE, Costo: '9000', CostoMatricula: '500', Detalle: 'PRUEBA', Version: 'V-1', NumeroTramite: '',
  });
  const fila = sql(`SELECT ProgramaID, Codigo FROM programa WHERE NombrePrograma = '${nombre}'`)[0];
  return { id: fila[0], codigo: fila[1], nombre };
}

async function editar(page, { id, nombre }, grado, fecha, sede = SEDE) {
  await enviarFormulario(page, 'programas', {
    ProgramaIDEditar: id, NombreProgramaEditar: nombre, GradoAcademicoEditar: grado, SiglaEditar: 'PC',
    DuracionMesesEditar: '12', ModulosEditar: '6', FechaInicioEditar: fecha, SedeEditar: sede,
    CostoEditar: '9000', CostoMatriculaEditar: '500', DetalleEditar: 'PRUEBA', VersionEditar: 'V-1',
  });
  return valor(`SELECT Codigo FROM programa WHERE ProgramaID = ${id}`);
}

test.describe('Código del programa por sede y grado', () => {
  test.beforeEach(limpiar);
  test.afterEach(limpiar);

  test('cada grado lleva su propia numeración y el editar la respeta', async ({ page }) => {
    const oruroAntes = valor(`SELECT GROUP_CONCAT(Codigo ORDER BY ProgramaID) FROM programa WHERE Sede = 'ORURO'`);
    await ingresar(page, 'adm');

    const esp1 = await crear(page, 'ESPECIALIDAD', '2024-03-01');
    const mae1 = await crear(page, 'MAESTRIA', '2024-05-01');
    const esp2 = await crear(page, 'ESPECIALIDAD', '2024-08-01');
    const dip1 = await crear(page, 'DIPLOMADO', '2025-02-01');
    const esp3 = await crear(page, 'ESPECIALIDAD', '2025-04-01');
    const doc1 = await crear(page, 'DOCTORADO', '2025-06-01');

    expect(esp1.codigo).toBe('POT-ESP-1-2024');
    expect(mae1.codigo).toBe('POT-MAE-1-2024');
    expect(esp2.codigo).toBe('POT-ESP-2-2024');
    expect(dip1.codigo).toBe('POT-DIP-1-2025');
    expect(esp3.codigo).toBe('POT-ESP-3-2025'); // no vuelve a 1 con el año nuevo
    expect(doc1.codigo).toBe('POT-DOC-1-2025'); // antes salía OTR

    // Otra sede no se mezcla
    expect(valor(`SELECT GROUP_CONCAT(Codigo ORDER BY ProgramaID) FROM programa WHERE Sede = 'ORURO'`)).toBe(oruroAntes);

    // Editar: misma sede y grado conserva el número y actualiza el año
    expect(await editar(page, esp2, 'ESPECIALIDAD', '2026-01-10')).toBe('POT-ESP-2-2026');
    // Cambia de grado: toma el siguiente número de su nuevo grado
    expect(await editar(page, esp2, 'MAESTRIA', '2026-01-10')).toBe('POT-MAE-2-2026');
    // Un número que quedó libre no se reutiliza: la siguiente especialidad es la 4
    expect((await crear(page, 'ESPECIALIDAD', '2026-03-01')).codigo).toBe('POT-ESP-4-2026');
  });
});
