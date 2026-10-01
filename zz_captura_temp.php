<?php
// TEMPORAL (capturas de pantalla de Claude): se borra al terminar. Solo localhost.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit; }
session_start();
$_SESSION = ['Validar' => true, 'IdUsuario' => 1, 'Usuario' => 'luis123', 'Tipo' => 'ADM', 'Estado' => '1',
    'IdPersonal' => 1, 'Nombres' => 'LUIS', 'ApellidoPaterno' => 'PRUEBA', 'ApellidoMaterno' => '', 'CedulaIdentidad' => '0'];
header('Location: ' . preg_replace('/[^a-z]/', '', $_GET['ir'] ?? 'panel'));
