<?php 
namespace App\Domain\Ports\Output;

use App\Domain\Model\Cuenta;

interface CuentaRepositoryInterface
{
    public function buscarCuenta(int $id_cuenta): ?Cuenta;
    public function buscarCuentaNumero(string $cuentaNumero): ?Cuenta;
    public function actualizarSaldo(Cuenta $cuenta): Cuenta;
    public function obtenerParaActualizar(Cuenta $cuenta): Cuenta;
}
