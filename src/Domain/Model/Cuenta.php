<?php 
namespace App\Domain\Model;

class InsufficientBalanceException extends \Exception {}

class Cuenta
{
    private ?int $id_cuenta;
    private string $numero;
    private string $saldo;

    public function __construct(
        ?int $id_cuenta,
        string $numero,
        string $saldo
    ) {
        $this->id_cuenta = $id_cuenta;
        $this->numero = $numero;
        $this->saldo = $saldo;
    }

    public function getCuentaId()
    {
        return $this->id_cuenta;
    }

    public function getCuentaNumero()
    {
        return $this->numero;
    }

    public function getCuentaSaldo()
    {
        return $this->saldo;
    }

    public function puedeDebitar(string $monto): void
    {
        if (bccomp($this->saldo, $monto) === -1) {
            throw new InsufficientBalanceException("El saldo es insuficiente.");
        }
    }

    public function debitar(string $monto): string
    {
        $saldo_anterior = $this->saldo;
        $this->saldo = bcsub($saldo_anterior, $monto);
        return $saldo_anterior;
    }
}
