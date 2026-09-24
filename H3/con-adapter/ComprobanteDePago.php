<?php
declare(strict_types=1);

/** Lo que Caja guarda de cada cobro, sea en efectivo o por QR (RN12: queda a nombre de quien cobró). */
final class ComprobanteDePago
{
    public function __construct(
        public readonly string $metodo,
        public readonly float $montoBs,
        public readonly string $concepto,
        public readonly string $referencia,
        public readonly DateTimeImmutable $fecha = new DateTimeImmutable(),
    ) {
    }

    public function __toString(): string
    {
        return sprintf('[%s] Bs %.2f · %s · ref: %s', $this->metodo, $this->montoBs, $this->concepto, $this->referencia);
    }
}
