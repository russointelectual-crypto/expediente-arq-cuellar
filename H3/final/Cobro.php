<?php
declare(strict_types=1);

/** Resultado de una ReglaDeCobro: el detalle que va al recibo de entrega y el total que cobra Caja. */
final class Cobro
{
    /**
     * @param array<int, array{concepto: string, monto: float}> $lineas
     */
    public function __construct(
        public readonly string $regla,
        public readonly array $lineas,
        public readonly string $nota = '',
    ) {
    }

    public function total(): float
    {
        return array_sum(array_column($this->lineas, 'monto'));
    }

    public function detalle(): string
    {
        if ($this->lineas === []) {
            return $this->nota !== '' ? $this->nota : 'sin cargo';
        }
        $partes = array_map(fn (array $l) => sprintf('%s Bs %.2f', $l['concepto'], $l['monto']), $this->lineas);
        return implode(' + ', $partes) . ($this->nota !== '' ? " — {$this->nota}" : '');
    }
}
