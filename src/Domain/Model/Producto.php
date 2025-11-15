<?php 
namespace App\Domain\Model;

class PrecioPositivoException extends \Exception {}

class Producto
{
    private ?int $id_producto;
    private string $nombre;
    private string $precio;

    public function __construct(
        ?int $id_producto,
        string $nombre,
        string $precio
    ) {
        if (bccomp($precio, '0') !== 1) {
            throw new PrecioPositivoException("Error de precio: El precio de un producto siempre deber ser positivo.");
        }

        $this->id_producto = $id_producto;
        $this->nombre = $nombre;
        $this->precio = $precio;
    }

    public function getProductoId()
    {
        return $this->id_producto;
    }

    public function getProductoNombre()
    {
        return $this->nombre;
    }

    public function getProductoPrecio()
    {
        return $this->precio;
    }
}
