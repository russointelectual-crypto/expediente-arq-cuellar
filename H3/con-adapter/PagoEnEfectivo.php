<?php
declare(strict_types=1);

/** Cobro en efectivo: ya habla el idioma del taller, no necesita adaptarse. Calcula el vuelto. */
final class PagoEnEfectivo implements MetodoDePago
{
    public function __construct(private readonly float $montoEntregadoBs)
    {
    }

    public function nombre(): string
    {
        return 'EFECTIVO';
    }

    public function cobrar(float $montoBs, string $concepto): ComprobanteDePago
    {
        if ($this->montoEntregadoBs < $montoBs) {
            throw new DomainException(sprintf('El cliente entregó Bs %.2f y debe Bs %.2f.', $this->montoEntregadoBs, $montoBs));
        }
        $vuelto = $this->montoEntregadoBs - $montoBs;

        return new ComprobanteDePago($this->nombre(), $montoBs, $concepto, sprintf('recibido Bs %.2f, vuelto Bs %.2f', $this->montoEntregadoBs, $vuelto));
    }
}
