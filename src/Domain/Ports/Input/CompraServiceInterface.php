<?php 
namespace App\Domain\Ports\Input;

use App\Domain\Model\Cuenta;
use App\Domain\Model\Producto;
use App\Domain\Model\Compra;

interface CompraServiceInterface
{
    function procesarCompra(Cuenta $cuenta, Producto $producto): Compra;
}
