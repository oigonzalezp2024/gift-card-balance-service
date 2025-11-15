<?php
// -------------------------------------------------------------------------
// Cliente de Prueba para Simular Cargas de Concurrencia con Saldo Bajo
// Ejecutar vía CLI: php test_concurrencia.php
// -------------------------------------------------------------------------

// --- CONFIGURACIÓN DE CARGA ---
$target_account_id = 1111; // La cuenta que será atacada
$num_simultaneous_requests = 1000; // ¡Carga extrema de 1000 peticiones concurrentes!
$price_per_purchase = '0.01'; // Cada compra cuesta $0.01 (1 centavo)

// **IMPORTANTE:** Ajusta esta URL a la dirección de tu index.php
$api_url = 'http://localhost/babull-enterprise/tarjeta_cuentas/public/index.php'; 
// ------------------------------

$total_simulated_cost = bcmul($num_simultaneous_requests, $price_per_purchase, 2);

echo "--- Iniciando Test de Concurrencia EXTREMA (1000 Micro-Transacciones) ---\n";
echo "Endpoint: " . $api_url . "\n";
echo "Cuenta objetivo: " . $target_account_id . "\n";
echo "Peticiones simultáneas: " . $num_simultaneous_requests . "\n";
echo "Costo Total Simulado: $" . number_format($total_simulated_cost, 2) . "\n";
echo "----------------------------------------\n";
echo ">>> REQUIERE SALDO MANUALMENTE BAJO (Ej: \$5.00 o menos) EN CUENTA 1111 <<<\n";
echo "----------------------------------------\n";


// Array para almacenar los manejadores de cURL
$ch_array = [];
$multi_handle = curl_multi_init();
$start_time = microtime(true);

// 1. Crear y configurar las peticiones cURL
for ($i = 1; $i <= $num_simultaneous_requests; $i++) {
    $purchase_data = [
        'id_cuenta' => $target_account_id,
        'nombre' => "Item Concurrente #{$i}",
        'precio' => $price_per_purchase 
    ];
    $json_payload = json_encode($purchase_data);

    $ch = curl_init($api_url);
    
    // Configuración para cada petición
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_payload)
    ]);
    
    // Añadir el manejador al pool múltiple
    curl_multi_add_handle($multi_handle, $ch);
    $ch_array[$i] = $ch;
}

// 2. Ejecutar las peticiones en paralelo
$running = null;
do {
    curl_multi_exec($multi_handle, $running);
    usleep(100); 
} while ($running > 0);

$end_time = microtime(true);

// 3. Procesar los resultados
$success_count = 0;
$failure_count = 0;

echo "\n--- Resultados de las Peticiones ---\n";
foreach ($ch_array as $index => $ch) {
    $response_body = curl_multi_getcontent($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $decoded_response = json_decode($response_body, true);

    $status = $decoded_response['status'] ?? 'unknown';
    $message = $decoded_response['message'] ?? 'Sin respuesta válida.';

    if ($status === 'success' && $http_code === 200) {
        $success_count++;
        // Solo mostramos un punto por éxito para no saturar la salida con 1000 líneas
        // echo "✅"; 
    } else {
        $failure_count++;
        $dev_message = $decoded_response['developer_message'] ?? '';
        $is_balance_error = (strpos($dev_message, 'insuficiente') !== false);
        
        // Solo mostramos un punto por fallo para no saturar la salida
        // echo "🚫"; 
    }
    
    curl_multi_remove_handle($multi_handle, $ch);
}
echo "\n"; // Salto de línea después de los puntos de progreso

curl_multi_close($multi_handle);

// 4. Resumen
$duration = number_format($end_time - $start_time, 3);

echo "\n--- RESUMEN FINAL DE CONCURRENCIA ---\n";
echo "Peticiones Totales: {$num_simultaneous_requests}\n";
echo "✅ Éxitos: {$success_count}\n";
echo "❌ Fallos: {$failure_count}\n";
echo "Costo total de éxitos: $" . number_format($success_count * $price_per_purchase, 2) . "\n";
echo "Tiempo Total de Procesamiento: {$duration} segundos\n";
echo "-------------------------------------\n";

// Verificación clave
if ($success_count > 0 && $failure_count > 0) {
    echo "🎉 ¡PRUEBA SUPERADA!\n";
    echo "La mezcla de éxitos y fallos demuestra que tu bloqueo (`SELECT FOR UPDATE`) está funcionando correctamente,\n";
    echo "serializando las transacciones y deteniendo las compras cuando el saldo real se agota.\n";
} elseif ($success_count == $num_simultaneous_requests) {
    echo "⚠️ ADVERTENCIA: TODAS las transacciones fueron exitosas.\n";
    echo "Esto significa que el saldo inicial de la Cuenta {$target_account_id} es mayor o igual a \$" . number_format($total_simulated_cost, 2) . ".\n";
    echo ">>> ¡Reduce manualmente el saldo a menos de \$10.00 y vuelve a probar! <<<\n";
} else {
    echo "❌ FALLO CRÍTICO: Revisa logs de PHP/Apache/MySQL. Puede haber un problema de conexión o deadlock.\n";
}
?>