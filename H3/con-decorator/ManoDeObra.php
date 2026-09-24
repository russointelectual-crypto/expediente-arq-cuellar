<?php
declare(strict_types=1);

/** DECORATOR — Componente concreto: el trabajo del técnico, lo que siempre está en el centro. */
final class ManoDeObra implements ServicioCobrable
{
    public function __construct(private readonly string $trabajo, private readonly float $monto)
    {
    }

    public function descripcion(): string
    {
        return $this->trabajo;
    }

    public function precio(): float
    {
        return $this->monto;
    }

    public function lineas(): array
    {
        return [['concepto' => "Mano de obra: {$this->trabajo}", 'monto' => $this->monto, 'esRepuesto' => false]];
    }
}
