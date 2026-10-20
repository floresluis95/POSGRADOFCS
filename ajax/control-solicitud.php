<?php
/**
 * Control de envíos repetidos por corte de internet (ver modelos/controlsolicitud.modelo.php
 * y vistas/recursos/assets/js/scripts/control-envio.js).
 * Va como PRIMERA línea de cada archivo de ajax/ que recibe POST, antes de session_start:
 * así un reenvío que espera al primero no queda bloqueado por la sesión.
 *
 *   require_once __DIR__ . '/control-solicitud.php';
 */
require_once __DIR__ . '/../modelos/controlsolicitud.modelo.php';

ControlSolicitudModelo::Iniciar();
