<?php 
namespace App\Application\Service;

use App\Domain\Ports\Input\CompraServiceInterface;
use App\Domain\Ports\Output\CuentaRepositoryInterface;
use App\Domain\Ports\Output\CompraRepositoryInterface;
use App\Domain\Model\Cuenta;
use App\Domain\Model\Producto;
use App\Domain\Model\Compra;

class CompraService implements CompraServiceInterface
{
    private CuentaRepositoryInterface $cuentaRepository;
    private CompraRepositoryInterface $compraRepository;

    public function __construct(
        CuentaRepositoryInterface $cuentaRepository,
        CompraRepositoryInterface $compraRepository
    ) {
        $this->cuentaRepository = $cuentaRepository;
        $this->compraRepository = $compraRepository;
    }

    public function procesarCompra(Cuenta $cuenta, Producto $producto): Compra
    {
        $compra_monto_str = $producto->getProductoPrecio();

        if (bccomp($compra_monto_str, '0') !== 1) {
            throw new \Exception("Error de Monto: El monto a debitar debe ser positivo.");
        }

        $cuenta->puedeDebitar($compra_monto_str);

        $saldo_anterior_str = $cuenta->debitar($compra_monto_str);
        $saldo_actual_str = $cuenta->getCuentaSaldo();

        $compra = new Compra(
            null,
            $cuenta->getCuentaNumero(),
            $compra_monto_str,
            $saldo_anterior_str,
            $saldo_actual_str
        );

        $compra_res = $this->compraRepository->guardar($compra);
        $this->cuentaRepository->actualizarSaldo($cuenta);

        return $compra_res;
    }
}
