// @ts-check
import { test, expect } from '@playwright/test';
import {
  sql, valor, unico, ingresar, ajax, enviarFormulario, registrarEstudiante, preregistrar,
  ordenPendiente, validarVoucherMatricula, estudianteInscrito, generarOrdenCuota, pagarCuota, PROGRAMA_ID,
} from './ayudantes-proceso.js';

// Ningún formulario debe dejar datos duplicados. Cada prueba intenta registrar dos veces
// lo mismo y revisa en la BASE (no solo el mensaje) que quedó un único registro.

test.describe('Duplicados en formularios', () => {
  test.beforeEach(async ({ page }) => {
    await ingresar(page, 'adm');
  });

  // ---------------------------------------------------------------- personas

  test('estudiante: no se registra dos veces el mismo C.I.', async ({ page }) => {
    const { ci, respuesta } = await registrarEstudiante(page);
    expect(respuesta.success, respuesta.mensaje).toBe(true);

    // El registro rápido (Pre-Registro) no duplica: reutiliza al estudiante que ya existe
    const segundo = await registrarEstudiante(page, { Ci: ci, Correo: `otro${ci}@correo-prueba.com` });
    expect(segundo.respuesta.estudianteID).toBe(respuesta.estudianteID);
    expect(segundo.respuesta.mensaje).toContain('ya estaba registrado');
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('1');
  });

  test('estudiante: el C.I. con espacios alrededor cuenta como el mismo', async ({ page }) => {
    const { ci, respuesta } = await registrarEstudiante(page);
    const segundo = await registrarEstudiante(page, { Ci: `  ${ci} `, Correo: `esp${ci}@correo-prueba.com` });
    expect(segundo.respuesta.estudianteID).toBe(respuesta.estudianteID);
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE TRIM(Ci) = '${ci}'`)).toBe('1');
  });

  test('estudiante: no se repite el correo con otro C.I.', async ({ page }) => {
    const { ci } = await registrarEstudiante(page);
    const otro = await registrarEstudiante(page, { Correo: `prueba${ci}@correo-prueba.com` });
    expect(otro.respuesta.success).toBe(false);
    // El aviso dice de quién es el correo (antes era "Error al registrar el estudiante")
    expect(otro.respuesta.mensaje).toContain('ya está registrado');
    expect(otro.respuesta.mensaje).toContain(ci);
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Correo = 'prueba${ci}@correo-prueba.com'`)).toBe('1');
  });

  test('estudiante (página Estudiantes): no se registra dos veces el mismo C.I.', async ({ page }) => {
    const ci = unico();
    const campos = {
      Ci: ci, Complemento: '', Exp: 'OR', Nombre: 'FORM', Apaterno: 'PRUEBA', Amaterno: 'DUPLICADO',
      FechaNacimiento: '1990-01-01', Edad: '36', Lugarn: 'ORURO', Correo: `form${ci}@correo-prueba.com`,
      IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion'), Trabajo: '', Direccion: '', Telefono: '', Celular: '70000001',
      IdUniversidad: valor("SELECT MIN(IdUniversidad) FROM universidad") ?? '', FechaEgreso: '2015-12-15',
    };
    await enviarFormulario(page, 'estudiantes', campos);
    const html = await enviarFormulario(page, 'estudiantes', { ...campos, Correo: `form2${ci}@correo-prueba.com` });
    expect(html).toContain(`Ya existe un estudiante con el C.I. ${ci}`);
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('1');
  });

  test('estudiante (página Estudiantes): el correo de otro estudiante se avisa con el motivo', async ({ page }) => {
    const { ci: ciOtro } = await registrarEstudiante(page);
    const ci = unico();
    const html = await enviarFormulario(page, 'estudiantes', {
      Ci: ci, Complemento: '', Exp: 'OR', Nombre: 'FORM', Apaterno: 'PRUEBA', Amaterno: 'CORREO',
      FechaNacimiento: '1990-01-01', Edad: '36', Lugarn: 'ORURO', Correo: `prueba${ciOtro}@correo-prueba.com`,
      IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion') ?? '', Trabajo: '', Direccion: '', Telefono: '', Celular: '70000001',
      IdUniversidad: valor("SELECT MIN(IdUniversidad) FROM universidad") ?? '', FechaEgreso: '2015-12-15',
    });
    expect(html).toContain('ya está registrado para el estudiante');
    expect(html).toContain(ciOtro);
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('0');
  });

  test('estudiante (editar): no se puede poner el correo de otro estudiante', async ({ page }) => {
    const { ci: ciOtro } = await registrarEstudiante(page);
    const { respuesta } = await registrarEstudiante(page);
    const r = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'actualizarEstudiante', EstudianteID: String(respuesta.estudianteID), Exp: 'OR', Nombre: 'PRUEBA', Apaterno: 'EDITAR',
      FechaNacimiento: '1995-05-10', Edad: '31', Lugarn: 'ORURO', Correo: `prueba${ciOtro}@correo-prueba.com`,
      IdProfesion: valor('SELECT MIN(IdProfesion) FROM profesion') ?? '', Celular: '70000000',
      IdUniversidad: valor("SELECT MIN(IdUniversidad) FROM universidad") ?? '', FechaEgreso: '2018-12-15',
    });
    expect(r.success).toBe(false);
    expect(r.mensaje).toContain(ciOtro);
  });

  test('docente: no se registra dos veces el mismo C.I.', async ({ page }) => {
    const ci = unico();
    const campos = {
      Ci: ci, Complemento: '', Exp: 'OR', Nombre: 'DOCENTE', Apaterno: 'PRUEBA', Amaterno: 'DUPLICADO',
      FechaNacimiento: '1980-01-01', CedulaProfesional: '', MatriculaNacional: '', Especialidad: 'PRUEBA',
      Direccion: '', Correo: `doc${ci}@correo-prueba.com`, Tel: '', Cel: '70000002',
    };
    await enviarFormulario(page, 'docentes', campos);
    expect(valor(`SELECT COUNT(*) FROM docente WHERE Ci = '${ci}'`)).toBe('1');

    const html = await enviarFormulario(page, 'docentes', { ...campos, Correo: `doc2${ci}@correo-prueba.com` });
    expect(html).toContain('ya esta registrado');
    expect(valor(`SELECT COUNT(*) FROM docente WHERE Ci = '${ci}'`)).toBe('1');
  });

  test('personal: no se crean dos usuarios con el mismo nombre de usuario', async ({ page }) => {
    const n = unico();
    const campos = (ci) => ({
      ci, apaterno: 'PERSONAL', amaterno: 'PRUEBA', nombres: 'DUPLICADO', direccion: '', celular: '70000003', telefono: '',
      correo: `pers${ci}@correo-prueba.com`, usuario: `usuario_dup_${n}`, password: 'Clave1234', password_confirmar: 'Clave1234', UICargo: 'SEC',
    });
    await enviarFormulario(page, 'usuario', campos(`P${n}`));
    expect(valor(`SELECT COUNT(*) FROM usuario WHERE Usuario = 'usuario_dup_${n}'`)).toBe('1');

    await enviarFormulario(page, 'usuario', campos(`Q${n}`));
    expect(valor(`SELECT COUNT(*) FROM usuario WHERE Usuario = 'usuario_dup_${n}'`),
      'Con dos usuarios iguales, el inicio de sesión solo encuentra al primero').toBe('1');
  });

  test('personal: no se registra dos veces el mismo C.I.', async ({ page }) => {
    const ci = `C${unico()}`;
    const campos = (usuario) => ({
      ci, apaterno: 'PERSONAL', amaterno: 'PRUEBA', nombres: 'MISMO CI', direccion: '', celular: '70000004', telefono: '',
      correo: `${usuario}@correo-prueba.com`, usuario, password: 'Clave1234', password_confirmar: 'Clave1234', UICargo: 'AUX',
    });
    await enviarFormulario(page, 'usuario', campos(`ci_a_${unico()}`));
    await enviarFormulario(page, 'usuario', campos(`ci_b_${unico()}`));
    expect(valor(`SELECT COUNT(*) FROM personal WHERE CedulaIdentidad = '${ci}'`)).toBe('1');
  });

  test('personal: el nombre de usuario no se repite aunque cambien mayúsculas o espacios', async ({ page }) => {
    const ci = `M${unico()}`;
    await enviarFormulario(page, 'usuario', {
      ci, apaterno: 'PERSONAL', amaterno: 'PRUEBA', nombres: 'MAYUSCULAS', direccion: '', celular: '70000005', telefono: '',
      correo: `may${ci}@correo-prueba.com`, usuario: '  PRUEBA_ADM ', password: 'Clave1234', password_confirmar: 'Clave1234', UICargo: 'SEC',
    });
    expect(valor(`SELECT COUNT(*) FROM usuario WHERE LOWER(TRIM(Usuario)) = 'prueba_adm'`)).toBe('1');
    expect(valor(`SELECT COUNT(*) FROM personal WHERE CedulaIdentidad = '${ci}'`), 'no debe quedar un personal sin usuario').toBe('0');
  });

  test('personal: sí se puede registrar a alguien que ya es estudiante (mismo C.I., otra cuenta)', async ({ page }) => {
    const { ci } = await registrarEstudiante(page);
    const usuario = `pers_est_${unico()}`;
    await enviarFormulario(page, 'usuario', {
      ci, apaterno: 'PERSONAL', amaterno: 'Y ESTUDIANTE', nombres: 'MISMA PERSONA', direccion: '', celular: '70000006', telefono: '',
      correo: `${usuario}@correo-prueba.com`, usuario, password: 'Clave1234', password_confirmar: 'Clave1234', UICargo: 'AUX',
    });
    expect(valor(`SELECT COUNT(*) FROM personal WHERE CedulaIdentidad = '${ci}'`)).toBe('1');
    expect(valor(`SELECT COUNT(*) FROM estudiante WHERE Ci = '${ci}'`)).toBe('1');
  });

  test('estudiante: si su C.I. ya es el usuario de otra cuenta, recibe un usuario distinto', async ({ page }) => {
    // Personal que usa su C.I. como nombre de usuario...
    const { ci, respuesta } = await registrarEstudiante(page);
    await enviarFormulario(page, 'usuario', {
      ci, apaterno: 'PERSONAL', amaterno: 'CON CI', nombres: 'COMO USUARIO', direccion: '', celular: '70000007', telefono: '',
      correo: `ciusr${ci}@correo-prueba.com`, usuario: ci, password: 'Clave1234', password_confirmar: 'Clave1234', UICargo: 'SEC',
    });
    expect(valor(`SELECT Tipo FROM usuario WHERE Usuario = '${ci}'`)).toBe('SEC');

    // ...y después se le crea la cuenta de estudiante: no puede llamarse igual
    await enviarFormulario(page, 'estudiantes', {
      estudiante_id: String(respuesta.estudianteID), estudiante_ci: ci, estudiante_nombre: 'PRUEBA',
    });
    expect(valor(`SELECT Usuario FROM usuario WHERE EstudianteID = ${respuesta.estudianteID}`)).toBe(`${ci}E`);
    expect(valor(`SELECT COUNT(*) FROM usuario WHERE Usuario = '${ci}'`)).toBe('1');
  });

  test('personal: editar a uno no cambia a otro que comparte el mismo C.I.', async ({ page }) => {
    const ci = `E${unico()}`;
    // Dos registros del personal con el mismo C.I. (como ya existen en la base real)
    const base = Number(valor('SELECT COALESCE(MAX(IdPersonal), 0) FROM personal'));
    sql(`INSERT INTO personal (IdPersonal, CedulaIdentidad, ApellidoPaterno, ApellidoMaterno, Nombres, Direccion, Celular, Telefono)
         VALUES (${base + 1}, '${ci}', 'UNO', 'A', 'PRIMERO', '', '', ''), (${base + 2}, '${ci}', 'DOS', 'B', 'SEGUNDO', '', '', '')`);

    await enviarFormulario(page, 'usuario', {
      editaridpersonal: String(base + 2), editarci: ci, editarapaterno: 'DOS', editaramaterno: 'B', editarnombres: 'SEGUNDO EDITADO',
      editardireccion: '', editarcelular: '', editartelefono: '', editarcorreo: '',
    });
    expect(valor(`SELECT Nombres FROM personal WHERE IdPersonal = ${base + 2}`)).toBe('SEGUNDO EDITADO');
    expect(valor(`SELECT Nombres FROM personal WHERE IdPersonal = ${base + 1}`)).toBe('PRIMERO');
  });

  // ---------------------------------------------------------------- programa

  test('programa: no se registra dos veces el mismo nombre', async ({ page }) => {
    const nombre = `PROGRAMA DUPLICADO ${unico()}`;
    const campos = {
      NombrePrograma: nombre, GradoAcademico: 'DIPLOMADO', Sigla: 'PD', DuracionMeses: '6', Modulos: '4',
      FechaInicio: '2027-01-15', Sede: 'ORURO', Costo: '5000', CostoMatricula: '500', Detalle: 'PRUEBA', Version: 'V-1', NumeroTramite: '',
    };
    await enviarFormulario(page, 'programas', campos);
    expect(valor(`SELECT COUNT(*) FROM programa WHERE NombrePrograma = '${nombre}'`)).toBe('1');

    await enviarFormulario(page, 'programas', { ...campos, NombrePrograma: nombre.toLowerCase() + '  ' });
    expect(valor(`SELECT COUNT(*) FROM programa WHERE UPPER(TRIM(NombrePrograma)) = '${nombre}'`)).toBe('1');
  });

  // ---------------------------------------------------------------- pre-registro e inscripción

  test('pre-registro: un estudiante no tiene dos órdenes de matrícula pendientes del mismo programa', async ({ page }) => {
    const { respuesta } = await registrarEstudiante(page);
    await preregistrar(page, respuesta.estudianteID);
    const html = await preregistrar(page, respuesta.estudianteID);
    expect(html).toMatch(/pre-registro pendiente|ya tiene/i);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${respuesta.estudianteID} AND Estado = 'PENDIENTE'`)).toBe('1');
  });

  test('inscripción: un estudiante ya inscrito no puede pre-registrarse otra vez al mismo programa', async ({ page }) => {
    const { estudianteID } = await estudianteInscrito(page);
    await preregistrar(page, estudianteID);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE EstudianteID = ${estudianteID} AND Estado = 'PENDIENTE'`)).toBe('0');
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID} AND ProgramaID = ${PROGRAMA_ID}`)).toBe('1');
  });

  test('inscripción: el mismo voucher no se valida dos veces', async ({ page }) => {
    const { estudianteID } = await estudianteInscrito(page);
    const idOrden = valor(`SELECT IdOrdenPago FROM ordenpago WHERE EstudianteID = ${estudianteID} AND Estado = 'CONFIRMADO' LIMIT 1`);
    const otraVez = await validarVoucherMatricula(page, idOrden, unico() + '-01');
    expect(otraVez.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${estudianteID}`)).toBe('1');
  });

  test('inscripción: el N° de voucher de matrícula no se repite entre estudiantes', async ({ page }) => {
    const voucher = 'MAT-REP-' + unico();
    const a = await registrarEstudiante(page);
    const b = await registrarEstudiante(page);
    await preregistrar(page, a.respuesta.estudianteID);
    await preregistrar(page, b.respuesta.estudianteID);

    const primero = await validarVoucherMatricula(page, ordenPendiente(a.respuesta.estudianteID), voucher);
    expect(primero.success, primero.mensaje).toBe(true);
    const segundo = await validarVoucherMatricula(page, ordenPendiente(b.respuesta.estudianteID), voucher);
    expect(segundo.success).toBe(false);
    expect(segundo.mensaje).toContain('ya fue registrado en la matrícula de');
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE nvauchermatricula = '${voucher}'`),
      'Dos inscripciones quedaron validadas con el mismo voucher de matrícula').toBe('1');
    expect(valor(`SELECT COUNT(*) FROM estudianteprograma WHERE EstudianteID = ${b.respuesta.estudianteID}`), 'el segundo no quedó inscrito').toBe('0');
  });

  test('voucher: no distingue mayúsculas ni espacios', async ({ page }) => {
    const voucher = 'MAT-MAY-' + unico();
    const a = await registrarEstudiante(page);
    const b = await registrarEstudiante(page);
    await preregistrar(page, a.respuesta.estudianteID);
    await preregistrar(page, b.respuesta.estudianteID);
    await validarVoucherMatricula(page, ordenPendiente(a.respuesta.estudianteID), voucher);
    const segundo = await validarVoucherMatricula(page, ordenPendiente(b.respuesta.estudianteID), `  ${voucher.toLowerCase()} `);
    expect(segundo.success).toBe(false);
  });

  test('voucher: el N° usado en una cuota no se vuelve a usar en una matrícula', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    await generarOrdenCuota(page, inscrito, '1000');
    const voucher = 'CUO-MAT-' + unico();
    expect((await pagarCuota(page, inscrito.idInscripcion, '1000', voucher)).estado).toBe('exitoso');

    const otro = await registrarEstudiante(page);
    await preregistrar(page, otro.respuesta.estudianteID);
    const r = await validarVoucherMatricula(page, ordenPendiente(otro.respuesta.estudianteID), voucher);
    expect(r.success).toBe(false);
    expect(r.mensaje).toContain('una cuota de');
  });

  // ---------------------------------------------------------------- plan y cuotas

  test('plan de pago: una inscripción no tiene dos planes', async ({ page }) => {
    const { estudianteID, idInscripcion } = await estudianteInscrito(page);
    const r = await ajax(page, 'ordenpago.ajax.php', {
      accion: 'registrarPlanPagoPrograma', idInscripcion: String(idInscripcion), estudianteID: String(estudianteID),
      programaID: String(PROGRAMA_ID), costoTotalPrograma: '30000', tipoPlan: 'DESCUENTO', porcentajeDescuento: '10',
    });
    expect(r.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM pagoprograma WHERE idInscripcion = ${idInscripcion}`)).toBe('1');
  });

  test('orden de cuota: no se generan dos órdenes pendientes para la misma inscripción', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    const primera = await generarOrdenCuota(page, inscrito);
    expect(primera.success, primera.mensaje).toBe(true);
    const segunda = await generarOrdenCuota(page, inscrito);
    expect(segunda.success).toBe(false);
    expect(valor(`SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = ${inscrito.idInscripcion} AND Estado = 'PENDIENTE'`)).toBe('1');
  });

  test('voucher de cuota: el mismo N° no se registra dos veces', async ({ page }) => {
    const a = await estudianteInscrito(page);
    const b = await estudianteInscrito(page);
    await generarOrdenCuota(page, a, '1000');
    await generarOrdenCuota(page, b, '1000');
    const voucher = unico() + '-02';

    const primero = await pagarCuota(page, a.idInscripcion, '1000', voucher);
    expect(primero.estado, primero.mensaje).toBe('exitoso');
    const segundo = await pagarCuota(page, b.idInscripcion, '1000', voucher);
    expect(segundo.estado).toBe('validacion');
    expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE nvaucher = '${voucher}' AND Estado <> 'ANULADO'`)).toBe('1');
  });

  test('voucher: el N° usado en la matrícula no se vuelve a usar en una cuota', async ({ page }) => {
    const inscrito = await estudianteInscrito(page);
    await generarOrdenCuota(page, inscrito, '1000');
    const voucherMatricula = valor(`SELECT nvauchermatricula FROM estudianteprograma WHERE idInscripcion = ${inscrito.idInscripcion}`);
    await pagarCuota(page, inscrito.idInscripcion, '1000', voucherMatricula);
    expect(valor(`SELECT COUNT(*) FROM pagomodulo WHERE nvaucher = '${voucherMatricula}' AND Estado <> 'ANULADO'`),
      `El voucher de matrícula ${voucherMatricula} se aceptó también como pago de cuota`).toBe('0');
  });
});
