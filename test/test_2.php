<?php
// -------------------------------------------------------------------------
// Cliente de Prueba diseñado para CAPTURAR los errores de la API.
// Ejecutar vía CLI: php test_problematico.php
// -------------------------------------------------------------------------

// **IMPORTANTE:** Ajusta esta URL a la dirección de tu index.php
$api_url = 'http://localhost/babull-enterprise/tarjeta_cuentas/public/index.php'; 

$purchase_data = [
    'id_cuenta' => 1111,
    'nombre' => 'Producto Fallido',
    'precio' => '100.00'
];
$json_payload = json_encode($purchase_data);

echo "--- Iniciando Test de Falla de la API ---\n";
echo "1. Enviando payload: " . $json_payload . "\n";
echo "----------------------------------------\n";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
curl_setopt($ch, CURLOPT_HEADER, true); // Solicitamos las cabeceras para ver la contaminación
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_payload)
]);

$response_with_headers = curl_exec($ch);

if (curl_errno($ch)) {
    echo "❌ ERROR de cURL: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

$headers = substr($response_with_headers, 0, $header_size);
$body_raw = substr($response_with_headers, $header_size);

curl_close($ch);

// --- ANÁLISIS DE LA RESPUESTA ---

echo "2. Código HTTP de Respuesta: " . $http_code . "\n";
echo "3. Cabeceras Recibidas:\n";
echo "----------------------------------------\n";
echo $headers;
echo "----------------------------------------\n";

// PROBLEMA 1: Contaminación de la Respuesta y JSON Inválido
$decoded_response = json_decode($body_raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ **PROBLEMA 1 EVIDENCIADO: JSON INVÁLIDO O CONTAMINADO**\n";
    echo "   Razón: `json_decode` falló. El cuerpo de la respuesta contiene texto extra o no es JSON válido.\n";
    echo "   Cuerpo de Respuesta Cruda:\n";
    echo "----------------------------------------\n";
    echo $body_raw . "\n"; // Esto mostrará el texto contaminante (ej. "✅ **¡Compra Exitosa!**")
    echo "----------------------------------------\n";
} else {
    echo "✅ El JSON es válido. Esto significa que el Problema 1 está corregido.\n";

    // PROBLEMA 2: Falta de datos (solo se puede verificar si el JSON es válido)
    if (!isset($decoded_response['data']) || empty($decoded_response['data'])) {
        echo "❌ **PROBLEMA 2 EVIDENCIADO: BLOQUE 'data' VACÍO**\n";
        echo "   Razón: El `CompraController::process` no está devolviendo el array de datos.\n";
        echo "   Estructura de la Respuesta:\n";
        print_r($decoded_response);
    } else {
        echo "✅ El bloque 'data' contiene información. Esto significa que el Problema 2 está corregido.\n";
    }
}

echo "--- Test de Falla Finalizado ---\n";
