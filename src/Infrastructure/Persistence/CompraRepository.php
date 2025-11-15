<?php 
namespace App\Infrastructure\Persistence;

use App\Domain\Model\Compra;
use App\Domain\Ports\Output\CompraRepositoryInterface;
use PDO;

class CompraRepository implements CompraRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    function guardar(Compra $compra): Compra
    {
        $pdo = $this->pdo;
        $sql = "INSERT INTO compras (
                        cuenta_numero,
                        monto,
                        saldo_anterior,
                        saldo_actual
                    ) VALUES (
                        :cuenta_numero,
                        :monto,
                        :saldo_anterior,
                        :saldo_actual
                    )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':cuenta_numero' => $compra->getCuentaNumero(),
            ':monto' => $compra->getCompraMonto(),
            ':saldo_anterior' => $compra->getSaldoAnterior(),
            ':saldo_actual' => $compra->getSaldoActual()
        ]);

        $id = (int) $pdo->lastInsertId();

        return new Compra(
            $id,
            $compra->getCuentaNumero(),
            $compra->getCompraMonto(),
            $compra->getSaldoAnterior(),
            $compra->getSaldoActual()
        );
    }
}
