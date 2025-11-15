<?php

bcscale(6);

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Service\CompraService;
use App\Domain\Ports\Output\TransactionManagerInterface;
use App\Infrastructure\Database\DbConnection;
use App\Infrastructure\Persistence\CompraRepository;
use App\Infrastructure\Persistence\CuentaRepository;
use App\Infrastructure\Tool\PDOTransactionManager;
use App\Infrastructure\Http\CompraController;
try {
    $pdo = DbConnection::createPDO();
} catch (\PDOException $e) {
    sendJsonResponse(503, ['status' => 'error', 'message' => 'Database connection failed.']);
}

$compraRepository = new CompraRepository($pdo);
$cuentaRepository = new CuentaRepository($pdo);
$transactionManager = new PDOTransactionManager($pdo);
$compraService = new CompraService($cuentaRepository, $compraRepository); 

$controller = new CompraController(
    $compraService,
    $cuentaRepository,
    $transactionManager
);

function sendJsonResponse(int $statusCode, array $data): void
{
    if (ob_get_length() > 0) {
        ob_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(405, [
        'status' => 'error',
        'message' => 'Method Not Allowed. Only POST requests are accepted.'
    ]);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => 'Invalid JSON. The request body must be a valid JSON object.'
    ]);
}

$requiredFields = ['cuenta_numero', 'nombre', 'precio'];
$missingFields = array_diff($requiredFields, array_keys($data));

if (!empty($missingFields)) {
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => 'Required fields missing: ' . implode(', ', $missingFields),
        'required' => $requiredFields
    ]);
}

$cuenta_numero = (string) ($data['cuenta_numero'] ?? 0);
$nombre = (string) ($data['nombre'] ?? '');
$precio = (string) ($data['precio'] ?? '0');

if (empty($cuenta_numero) || empty($nombre) || bccomp($precio, '0', 6) <= 0) {
    sendJsonResponse(400, [
        'status' => 'error',
        'message' => 'Invalid input data. Check cuenta_numero (must be positive), nombre (must not be empty), and precio (must be greater than zero).'
    ]);
}

try {
    $resultData = $controller->process($cuenta_numero, $nombre, $precio);
    
    sendJsonResponse(200, [
        'status' => 'success',
        'message' => '¡Compra Exitosa!',
        'metadata' => [
            'cuenta_numero' => $cuenta_numero,
            'nombre_producto' => $nombre,
        ],
        'data' => $resultData
    ]);

} catch (\Exception $e) {

    error_log("Error during purchase for account $cuenta_numero: " . $e->getMessage());

    sendJsonResponse(500, [
        'status' => 'error',
        'message' => 'Error en el procesamiento de la compra. Revise el log del servidor.', 
        'developer_message' => $e->getMessage(), 
        'code' => $e->getCode()
    ]);
}
