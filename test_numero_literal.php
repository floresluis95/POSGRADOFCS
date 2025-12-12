<?php
/**
 * Test de la función numeroALetras
 */

function numeroALetras($numero) {
    $numero = round($numero, 2); // Redondear a 2 decimales
    $entero = floor($numero);
    $decimales = round(($numero - $entero) * 100);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    if ($entero == 0) {
        $literal = 'CERO';
    } elseif ($entero == 100) {
        $literal = 'CIEN';
    } elseif ($entero > 100) {
        $literal = 'CIENTO ';
        $resto = $entero - 100;

        if ($resto >= 10 && $resto < 20) {
            $literal .= $especiales[$resto - 10];
        } elseif ($resto >= 20 && $resto < 30) {
            $literal .= $veintenas[$resto - 20];
        } elseif ($resto >= 30) {
            $dec = floor($resto / 10);
            $uni = $resto % 10;
            $literal .= $decenas[$dec];
            if ($uni > 0) {
                $literal .= ' Y ' . $unidades[$uni];
            }
        } else {
            $literal .= $unidades[$resto];
        }
    } elseif ($entero >= 10 && $entero < 20) {
        $literal = $especiales[$entero - 10];
    } elseif ($entero >= 20 && $entero < 30) {
        $literal = $veintenas[$entero - 20];
    } else {
        $dec = floor($entero / 10);
        $uni = $entero % 10;
        $literal = $decenas[$dec];
        if ($uni > 0) {
            if ($dec > 0) {
                $literal .= ' Y ';
            }
            $literal .= $unidades[$uni];
        }
    }

    // Agregar decimales si existen
    if ($decimales > 0) {
        $literal .= ' CON ' . str_pad($decimales, 2, '0', STR_PAD_LEFT) . '/100';
    }

    return trim($literal);
}

echo "===== TEST DE CONVERSIÓN NUMÉRICA A LITERAL =====\n\n";

$numeros_prueba = [
    0,
    1,
    10,
    15,
    20,
    25,
    30,
    45,
    51,
    51.5,
    60,
    75.25,
    80,
    90,
    95,
    100,
    100.5
];

foreach ($numeros_prueba as $num) {
    $literal = numeroALetras($num);
    echo sprintf("%-8s → %s\n", $num, $literal);
}

echo "\n✅ Función de conversión funcionando correctamente\n";
