// @ts-check
import { test, expect } from '@playwright/test';
import { ingresar } from './ayudantes.js';

// Permisos de páginas por rol (controladores/enlaces.controlador.php), probados de punta a punta:
// un usuario real inicia sesión y escribe la URL a mano.
// Requiere los usuarios de prueba: tests-e2e/datos/usuarios-prueba.sql

// Al negar una página la URL NO cambia; se reconoce por el contenido que se muestra.
const PANEL = (page) => page.locator('section.pn-bienvenida');
const PAGINA_404 = (page) => page.getByText('PAGINA NO ENCONTRADA');

// [rol, página, qué debe ver: título de la página | 'panel' | '404']
const CASOS = [
  ['adm', 'usuario', 'GESTIÓN DE USUARIOS'],
  ['adm', 'misordenespago', 'panel'],
  ['adm', 'reportecalificaciones', '404'],

  // Usuarios del personal: solo el ADM (Secretaría podía crear un Administrador)
  ['sec', 'usuario', 'panel'],
  ['sec', 'programas', 'PROGRAMAS'],
  // Secretaría: sin Certificados ni Registrar Notas; las notas solo las consulta e imprime
  ['sec', 'registrodocumentos', 'panel'],
  ['sec', 'certificadocalificaciones', 'panel'],
  ['sec', 'rnotasestudiante', 'panel'],
  ['sec', 'reportenotas', 'REPORTES DE CALIFICACIONES'],
  ['sec', 'reportecalificaciones', '404'],

  // Auxiliar: matrícula, cobros, estudiantes y notas
  ['aux', 'matriculados', 'MATRICULADOS'],
  ['aux', 'rnotasestudiante', 'REGISTRO DE CALIFICACIONES'],
  ['aux', 'usuario', 'panel'],
  ['aux', 'programas', 'panel'],
  ['aux', 'modulos', 'panel'],
  ['aux', 'docentes', 'panel'],
  ['aux', 'asignaciondocente', 'panel'],
  ['aux', 'registrodocumentos', 'panel'],

  ['doc', 'reportecalificaciones', 'REPORTE DE CALIFICACIONES'],
  ['doc', 'matriculados', 'panel'],

  ['est', 'misrequisitos', 'MIS REQUISITOS'],
  ['est', 'usuario', 'panel'],
  ['est', 'matriculados', 'panel'],
];

test.describe('Permisos de páginas por rol', () => {

  for (const [rol, pagina, esperado] of CASOS) {
    const descripcion = esperado === 'panel' ? 've el panel'
      : esperado === '404' ? 've la 404'
      : 'la puede abrir';

    test(`${rol.toUpperCase()} → ${pagina}: ${descripcion}`, async ({ page }) => {
      await ingresar(page, rol);
      await page.goto(pagina);

      if (esperado === 'panel') {
        await expect(PANEL(page)).toBeVisible();
      } else if (esperado === '404') {
        await expect(PAGINA_404(page)).toBeVisible();
      } else {
        await expect(page.getByRole('heading', { name: esperado, exact: true }).first()).toBeVisible();
        await expect(PANEL(page)).toHaveCount(0);
      }
    });
  }

});

// Asignar usuario y restablecer contraseñas: la Secretaría ve las listas pero sin esos botones
test.describe('Cuentas de estudiantes y docentes', () => {
  test('SEC no ve Asignar ni Restablecer en Estudiantes y Docentes', async ({ page }) => {
    await ingresar(page, 'sec');
    await page.goto('estudiantes');
    await expect(page.locator('#btnRestablecerPasswordEstudianteDetalle')).toHaveCount(0);
    await expect(page.locator('.btnAsignarUsuario')).toHaveCount(0);
    await page.goto('docentes');
    await expect(page.locator('#btnRestablecerPasswordDocenteDetalle')).toHaveCount(0);
    await expect(page.locator('.btnAsignarUsuarioDocente')).toHaveCount(0);
  });

  test('ADM sí tiene Restablecer en Estudiantes y Docentes', async ({ page }) => {
    await ingresar(page, 'adm');
    await page.goto('estudiantes');
    await expect(page.locator('#btnRestablecerPasswordEstudianteDetalle')).toHaveCount(1);
    await page.goto('docentes');
    await expect(page.locator('#btnRestablecerPasswordDocenteDetalle')).toHaveCount(1);
  });
});
