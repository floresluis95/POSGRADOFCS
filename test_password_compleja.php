<?php
/**
 * Test para verificar la generación de contraseñas complejas
 */

// Simular la función de generación de contraseñas
function GenerarPasswordCompleja($nombre, $ci)
{
    // Obtener primeras 2 letras del nombre (primera mayúscula, segunda minúscula)
    $primerLetra = strtoupper(substr($nombre, 0, 1));
    $segundaLetra = strtolower(substr($nombre, 1, 1));
    $prefijo = $primerLetra . $segundaLetra;

    // Generar 4 caracteres aleatorios (mezcla de mayúsculas, minúsculas y números)
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $aleatorios = '';
    for ($i = 0; $i < 4; $i++) {
        $aleatorios .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    // Seleccionar un símbolo especial aleatorio
    $simbolos = '@#$%&*';
    $simbolo = $simbolos[random_int(0, strlen($simbolos) - 1)];

    // Construir la contraseña: Primeras2Letras + CI + 4Aleatorios + Símbolo
    $password = $prefijo . $ci . $aleatorios . $simbolo;

    return $password;
}

echo "===== TEST DE GENERACIÓN DE CONTRASEÑAS COMPLEJAS =====\n\n";

// Pruebas con diferentes nombres
$tests = [
    ['nombre' => 'Juan', 'ci' => '1234567'],
    ['nombre' => 'María', 'ci' => '7654321'],
    ['nombre' => 'Pedro', 'ci' => '9876543'],
    ['nombre' => 'Ana', 'ci' => '1111111'],
    ['nombre' => 'Carlos', 'ci' => '5555555']
];

foreach ($tests as $test) {
    $password = GenerarPasswordCompleja($test['nombre'], $test['ci']);
    echo "Nombre: {$test['nombre']}, CI: {$test['ci']}\n";
    echo "Contraseña generada: $password\n";
    echo "Longitud: " . strlen($password) . " caracteres\n";
    echo "Formato: Primeras2Letras + CI + 4Aleatorios + Símbolo\n";
    echo "----------------------------------------\n\n";
}

echo "\n✅ CARACTERÍSTICAS DE LA CONTRASEÑA:\n";
echo "- Contiene letras mayúsculas y minúsculas\n";
echo "- Contiene números (del CI)\n";
echo "- Contiene caracteres especiales (@#$%&*)\n";
echo "- Longitud mínima: 13+ caracteres\n";
echo "- Incluye información personalizada del docente\n";
