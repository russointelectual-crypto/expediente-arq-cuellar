<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

final class CobroEfectivo implements IPoliticaCobro
{
    public function nombre(): string
    {
        return 'efectivo';
    }

    public function preparar(DatosCobro $datos): string
    {
        return 'Cobro en efectivo preparado por ' . $datos->importeFormateado();
    }

    public function cobrar(DatosCobro $datos): string
    {
        $recibido = $datos->montoRecibidoCentavos;
        if ($recibido === null || $recibido < $datos->importeCentavos) {
            throw new DomainException('El efectivo recibido no cubre el importe.');
        }
        $cambio = $recibido - $datos->importeCentavos;
        return 'Cobro en efectivo confirmado; cambio Bs '
            . number_format($cambio / 100, 2, '.', '');
    }
}
