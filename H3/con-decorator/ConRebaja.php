<?php
declare(strict_types=1);

/**
 * DECORATOR concreto — rebaja porcentual sobre TODO lo que envuelve (solo la autoriza el gerente, RN13).
 * Es el único agregado donde el ORDEN de apilamiento cambia el resultado.
 */
final class ConRebaja extends AgregadoDeServicio
{
    public function __construct(ServicioCobrable $envuelto, private readonly float $porcentaje, private readonly string $autorizadaPor)
    {
        if ($porcentaje <= 0 || $porcentaje > 50) {
            throw new InvalidArgumentException('La rebaja debe estar entre 0 y 50 %.');
        }
        parent::__construct($envuelto);
    }

    protected function concepto(): string
    {
        return sprintf('Rebaja %.0f %% (autoriza %s)', $this->porcentaje, $this->autorizadaPor);
    }

    protected function monto(): float
    {
        return -round($this->envuelto->precio() * $this->porcentaje / 100, 2);
    }
}
