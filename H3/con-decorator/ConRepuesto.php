<?php
declare(strict_types=1);

/** DECORATOR concreto — agrega un repuesto sacado del inventario, con su precio de venta. */
final class ConRepuesto extends AgregadoDeServicio
{
    public function __construct(ServicioCobrable $envuelto, private readonly string $nombre, private readonly float $precioVenta)
    {
        parent::__construct($envuelto);
    }

    protected function concepto(): string { return $this->nombre; }
    protected function monto(): float { return $this->precioVenta; }
    protected function esRepuesto(): bool { return true; }
}
