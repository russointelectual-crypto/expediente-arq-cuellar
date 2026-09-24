<?php
declare(strict_types=1);

/**
 * DECORATOR — Decorador base: envuelve un ServicioCobrable y le suma un agregado
 * (licencia, antivirus, repuesto...). Cada agregado es, a su vez, un ServicioCobrable,
 * así que se pueden apilar en cualquier orden y cantidad.
 */
abstract class AgregadoDeServicio implements ServicioCobrable
{
    public function __construct(protected readonly ServicioCobrable $envuelto)
    {
    }

    abstract protected function concepto(): string;

    abstract protected function monto(): float;

    protected function esRepuesto(): bool
    {
        return false;
    }

    public function descripcion(): string
    {
        return $this->envuelto->descripcion() . ' + ' . $this->concepto();
    }

    public function precio(): float
    {
        return $this->envuelto->precio() + $this->monto();
    }

    public function lineas(): array
    {
        return [...$this->envuelto->lineas(), ['concepto' => $this->concepto(), 'monto' => $this->monto(), 'esRepuesto' => $this->esRepuesto()]];
    }
}
