<?php 
namespace App\Infrastructure\Persistence;

class CuentaNoExistsException extends \Exception {}

use App\Domain\Model\Cuenta;
use App\Domain\Ports\Output\CuentaRepositoryInterface;
use PDO;

class CuentaRepository implements CuentaRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarCuenta(int $id_cuenta): ?Cuenta
    {
        $sql = "SELECT id_cuenta, numero, saldo FROM cuentas WHERE id_cuenta = :id_cuenta";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_cuenta' => $id_cuenta]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return new Cuenta(
            (int) $data['id_cuenta'],
            $data['numero'],
            $data['saldo']
        );
    }

    public function buscarCuentaNumero(string $cuentaNumero): ?Cuenta
    {
        $sql = "SELECT id_cuenta, numero, saldo FROM cuentas WHERE numero = :numero";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':numero' => $cuentaNumero]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return new Cuenta(
            (int) $data['id_cuenta'],
            $data['numero'],
            $data['saldo']
        );
    }

    function actualizarSaldo(Cuenta $cuenta): Cuenta
    {
        $pdo = $this->pdo;
        $sql = "UPDATE cuentas SET saldo = :saldo WHERE id_cuenta = :id_cuenta";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':saldo' => $cuenta->getCuentaSaldo(),
            ':id_cuenta' => $cuenta->getCuentaId()
        ]);
        return $cuenta;
    }

    public function obtenerParaActualizar(Cuenta $cuenta): Cuenta
    {
        $pdo = $this->pdo;
        $id_cuenta = $cuenta->getCuentaId();
        
        $sql = "SELECT id_cuenta, numero, saldo 
            FROM cuentas 
            WHERE id_cuenta = :id_cuenta FOR UPDATE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_cuenta' => $id_cuenta]);
        $data = $stmt->fetch();

        if (!$data) {
            throw new CuentaNoExistsException("Error fatal: La cuenta (ID: $id_cuenta) no existe o fue eliminada.");
        }

        return new Cuenta(
            (int) $data['id_cuenta'],
            $data['numero'],
            $data['saldo']
        );
    }
}
