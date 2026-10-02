<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../controladores/enlaces.controlador.php';

/**
 * Lista blanca de páginas por rol (EnlacesControladores::PuedeVerPagina).
 * No usa base de datos ni sesión: solo recibe el tipo de usuario y la página.
 */
class EnlacesControladoresTest extends TestCase
{
    public function testLasPaginasComunesLasVeCualquierRol()
    {
        foreach (['ADM', 'SEC', 'AUX', 'DOC', 'EST'] as $tipo) {
            $this->assertTrue(EnlacesControladores::PuedeVerPagina($tipo, 'panel'), "$tipo debería ver panel");
            $this->assertTrue(EnlacesControladores::PuedeVerPagina($tipo, 'calendario'), "$tipo debería ver calendario");
        }
    }

    public function testElRegistroDeNotasDelDocenteEsSoloDelDocente()
    {
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('DOC', 'reportecalificaciones'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('ADM', 'reportecalificaciones'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('SEC', 'reportecalificaciones'));
    }

    public function testElAdministradorVeLasPaginasDelPersonalPeroNoLasDelEstudiante()
    {
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('ADM', 'usuario'));
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('ADM', 'requisitos'));
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('ADM', 'autoridades'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('ADM', 'misordenespago'));
        // Excepción: el historial de notas sí le sirve al ADM
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('ADM', 'historialnotaestudiante'));
    }

    public function testElAuxiliarNoVeUsuariosCertificadosNiReportes()
    {
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('AUX', 'matriculados'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('AUX', 'usuario'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('AUX', 'certificadocalificaciones'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('AUX', 'reportes'));
        // Secretaría sí los ve
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('SEC', 'usuario'));
    }

    public function testElEstudianteSoloVeSusPaginas()
    {
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('EST', 'misrequisitos'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('EST', 'matriculados'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('EST', 'usuario'));
    }

    public function testUnRolDesconocidoSoloVeLasComunes()
    {
        $this->assertTrue(EnlacesControladores::PuedeVerPagina('XYZ', 'panel'));
        $this->assertFalse(EnlacesControladores::PuedeVerPagina('XYZ', 'programas'));
    }
}
