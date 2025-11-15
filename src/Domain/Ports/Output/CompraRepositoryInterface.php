<?php 
namespace App\Domain\Ports\Output;

use App\Domain\Model\Compra;

interface CompraRepositoryInterface
{
    function guardar(Compra $compra): Compra;
}
