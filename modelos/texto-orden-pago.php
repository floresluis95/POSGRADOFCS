<?php
/**
 * Texto del programa en la orden de pago de matrícula (o del programa completo):
 * "PROGRAMA DE POSGRADO EN LA ESPECIALIDAD EN ARMONIZACIÓN OROFACIAL MATRÍCULA".
 * El nombre del programa a veces ya empieza con el grado ("ESPECIALIDAD DE ..."):
 * se quita para no repetirlo, y el grado va con tilde (la BD guarda MAESTRIA).
 * La palabra final (MATRÍCULA / PROGRAMA COMPLETO) la usa la orden impresa para
 * llenar "Tipo de pago" (ver orden-pago-pdf-render.php).
 * Lo usan orden-pago-pdf.php y ajax/ordenpago.ajax.php (obtenerPreregistro).
 */
function textoProgramaOrdenPago($grado, $nombrePrograma, $version, $programaCompleto)
{
    $grado = mb_strtoupper(trim((string)$grado), 'UTF-8');
    $grado = strtr($grado, ['MAESTRIA' => 'MAESTRÍA']);
    $nombre = mb_strtoupper(trim((string)$nombrePrograma), 'UTF-8');
    $nombre = preg_replace('/^(DIPLOMADO|ESPECIALIDAD|MAESTR[IÍ]A|DOCTORADO)\s+(DE|EN)\s+/u', '', $nombre);

    // No se imprime la versión "V-1": es la base y no aporta nada al nombre
    $mostrarVersion = !empty($version) && mb_strtoupper($version, 'UTF-8') !== 'V-1';

    return 'PROGRAMA DE POSGRADO EN LA ' . $grado . ' EN ' . $nombre .
        ($mostrarVersion ? ' "' . mb_strtoupper($version, 'UTF-8') . '"' : '') .
        ' ' . ($programaCompleto ? 'PROGRAMA COMPLETO' : 'MATRÍCULA');
}
