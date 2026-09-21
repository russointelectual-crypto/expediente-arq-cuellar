<?php
declare(strict_types=1);

final class DatosCobro
{
    public function __construct(
        public string $numeroRecibo,
        public int $importeCentavos,
        public ?string $referenciaQr = null,
        public ?int $montoRecibidoCentavos = null
    ) {
        if (trim($numeroRecibo) === '' || $importeCentavos <= 0) {
            throw new InvalidArgumentException('El recibo y el importe positivo son obligatorios.');
        }
        if ($montoRecibidoCentavos !== null && $montoRecibidoCentavos < 0) {
            throw new InvalidArgumentException('El monto recibido no puede ser negativo.');
        }
    }

    public function importeFormateado(): string
    {
        return 'Bs ' . number_format($this->importeCentavos / 100, 2, '.', '');
    }
}
