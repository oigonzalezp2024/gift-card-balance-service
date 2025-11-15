<?php
// src/Infrastructure/Http/CompraController.php

namespace App\Infrastructure\Http;

use App\Application\Service\CompraService;
use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Model\Producto;
use App\Domain\Ports\Output\CuentaRepositoryInterface;
use App\Domain\Ports\Output\TransactionManagerInterface;
use App\Domain\Model\Compra; // Necesario para tipado y extracción de datos

/**
 * Adaptador de Entrada (Input Adapter): Traduce la petición externa (JSON) a 
 * una llamada al Caso de Uso y DEVUELVE los datos para que el index.php los formatee.
 * * NOTA DE CORRECCIÓN: Los 'echo' se han eliminado y el tipo de retorno se ha cambiado a array.
 */
class CompraController
{
    private CompraService $compraService;
    private CuentaRepositoryInterface $cuentaRepository;
    private TransactionManagerInterface $transactionManager;

    // Las dependencias se inyectan, NO se crean dentro del Controller.
    public function __construct(
        CompraService $compraService,
        CuentaRepositoryInterface $cuentaRepository,
        TransactionManagerInterface $transactionManager
    ) {
        $this->compraService = $compraService;
        $this->cuentaRepository = $cuentaRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * Procesa una compra. 
     * @return array Retorna un array con los detalles de la compra para la respuesta JSON.
     * @throws \Exception|InsufficientBalanceException Si la compra falla o hay errores de DB.
     */
    public function process(string $cuentaNumero, string $nombre, string $precio): array // <--- ¡Ajuste CRÍTICO: Debe devolver un array!
    {
        try {
            // 1. Obtener Entidad inicial (lectura simple)
            $cuentaInicial = $this->cuentaRepository->buscarCuentaNumero($cuentaNumero);
            if ($cuentaInicial === null) {
                // Lanzamos una excepción que será capturada por el index.php
                throw new \Exception("Cuenta no encontrada en la base de datos.", 404);
            }
            
            // 2. Crear Entidad Producto desde los datos de entrada
            $producto = new Producto(null, $nombre, $precio);

            // 3. Ejecutar el Caso de Uso envuelto en una transacción
            // El resultado de esta transacción será un objeto Compra.
            $compra = $this->transactionManager->executeInTransaction(
                function() use ($cuentaInicial, $producto): Compra {
                    // 3a. Re-obtener la cuenta bajo bloqueo (FOR UPDATE)
                    $cuentaBloqueada = $this->cuentaRepository->obtenerParaActualizar($cuentaInicial);

                    // 3b. Llamar al Caso de Uso (Lógica del Servicio de Aplicación)
                    return $this->compraService->procesarCompra($cuentaBloqueada, $producto);
                }
            );

            // 4. DEVOLVER EL ARRAY DE RESULTADOS (EN LUGAR DE USAR 'ECHO')
            return [
                'saldo_anterior' => $compra->getSaldoAnterior(),
                'monto_debitado' => $compra->getCompraMonto(),
                'saldo_actual' => $compra->getSaldoActual(),
                'id_compra' => $compra->getCompraId()
            ];

        } catch (InsufficientBalanceException $e) {
            // Relanzamos la excepción para que el index.php pueda manejar el error 500 con un mensaje específico.
            throw new \Exception(
                "El saldo actual es insuficiente para realizar la compra. Deberá hacer un aporte en efectivo.", 
                403, // Usamos un código HTTP 403 o similar para indicar la razón
                $e
            );
        }
    }
}
