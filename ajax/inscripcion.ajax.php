<?php
/**
 * AJAX Handler para Inscripciones
 * Maneja todas las peticiones AJAX relacionadas con inscripciones
 * Compatible con PHP 8
 */

require_once file_exists(__DIR__ . '/../modelos/inscripcion.modelo.php') ? __DIR__ . '/../modelos/inscripcion.modelo.php' : __DIR__ . '/../core/inscripcion.modelo.php';
require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

// Incluir controlador de inscripción
require_once '../controladores/inscripcion.controlador.php';

// El controlador ya maneja las acciones AJAX directamente
// Este archivo sirve como punto de entrada alternativo si se necesita
