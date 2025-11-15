<?php
// -------------------------------------------------------------------------
// Cliente de Prueba para Consumir la API de Compra (index.php)
// Ejecutar vía CLI: php test.php
// -------------------------------------------------------------------------

// --- CONFIGURACIÓN ---
// **IMPORTANTE:** Reemplaza esta URL con la dirección real de tu archivo index.php
$api_url = 'http://localhost/babull-enterprise/tarjeta_cuentas/public/index.php'; 

// Datos de la compra a enviar en formato JSON
$purchase_data = [
    'id_cuenta' => 1111,
    'nombre' => 'Laptop de Prueba',
    'precio' => '5000.7591' // BCMath requiere el precio como string
];

$json_payload = json_encode($purchase_data);

echo "--- Iniciando Test de Consumo de API ---\n";
echo "Endpoint: " . $api_url . "\n";
echo "Payload enviado:\n";
print_r($purchase_data);
echo "-----------------------------------------\n";

// 1. Inicializar cURL
$ch = curl_init($api_url);

// 2. Configurar opciones de cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devolver la respuesta como string
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Establecer método POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload); // Cargar el JSON payload

// 3. Establecer las cabeceras para indicar que enviamos JSON
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_payload)
]);

// 4. Ejecutar la petición y obtener la respuesta
$response = curl_exec($ch);

// 5. Verificar si hubo un error de cURL
if (curl_errno($ch)) {
    echo "❌ ERROR de cURL: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

// 6. Obtener el código de estado HTTP
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 7. Cerrar la sesión cURL
curl_close($ch);

// 8. Procesar la respuesta
$decoded_response = json_decode($response, true);

echo "Código de Respuesta HTTP: " . $http_code . "\n";
echo "Respuesta del Servidor (Decodificada):\n";

if ($decoded_response === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ ERROR al decodificar la respuesta JSON.\n";
    echo "Respuesta cruda: " . $response . "\n";
} else {
    // 9. Mostrar el resultado de forma legible
    if (isset($decoded_response['status']) && $decoded_response['status'] === 'success') {
        echo "✅ Transacción Exitosa!\n";
    } else {
        echo "⚠️ Transacción Fallida (o error de validación).\n";
    }
    
    // Imprimir toda la respuesta estructurada
    print_r($decoded_response);
}

echo "--- Test Finalizado ---\n";
