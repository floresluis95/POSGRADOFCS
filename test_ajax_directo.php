<?php
// Test accediendo como lo haría el navegador
session_start();

// Simular sesión válida
$_SESSION['Usuario'] = '1234567'; // Cambia por un CI real de docente
$_SESSION['Validar'] = true;

echo "<!DOCTYPE html><html><body><pre>\n";
echo "=== TEST AJAX DIRECTO ===\n\n";

// Probar con curl local
$url = 'http://localhost/POSGRADOFCS/ajax/calificacion.ajax.php';
$data = array('accion' => 'obtenerDocente');

// Usar CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";
echo "Respuesta:\n";
echo $response;
echo "\n\n";

// Intentar decodificar
$json = json_decode($response, true);
if ($json === null) {
    echo "ERROR: No es JSON válido\n";
    echo "Error: " . json_last_error_msg() . "\n";
    echo "\nPrimeros 200 caracteres de la respuesta:\n";
    echo substr($response, 0, 200);
} else {
    echo "JSON VÁLIDO:\n";
    print_r($json);
}

echo "</pre></body></html>";
?>
