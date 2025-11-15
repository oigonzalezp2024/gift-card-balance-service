<?php 
namespace App\Domain\Model;

class Compra
{
    private ?int $id_compra;
    private string $cuenta_numero;
    private string $compra_monto;
    private string $saldo_anterior;
    private string $saldo_actual;

    public function __construct(
        ?int $id_compra,
        string $cuenta_numero,
        string $compra_monto,
        string $saldo_anterior,
        string $saldo_actual
    ) {
        $this->id_compra = $id_compra;
        $this->cuenta_numero = $cuenta_numero;
        $this->compra_monto = $compra_monto;
        $this->saldo_anterior = $saldo_anterior;
        $this->saldo_actual = $saldo_actual;
    }

    public function getCompraId()
    {
        return $this->id_compra;
    }

    public function getCuentaNumero()
    {
        return $this->cuenta_numero;
    }

    public function getCompraMonto()
    {
        return $this->compra_monto;
    }

    public function getSaldoAnterior()
    {
        return $this->saldo_anterior;
    }

    public function getSaldoActual()
    {
        return $this->saldo_actual;
    }
}
