<?php 
namespace App\Domain\Ports\Output;

interface TransactionManagerInterface
{
    /**
     * Ejecuta una operación de negocio dentro de una transacción.
     * @param callable $operation La función que contiene la lógica de negocio.
     * @return mixed El resultado de la operación.
     * @throws \Exception
     */
    public function executeInTransaction(callable $operation);
}
