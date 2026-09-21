<?php
declare(strict_types=1);

require_once __DIR__ . '/IPoliticaCobro.php';

final class CobroQR implements IPoliticaCobro
{
    public function nombre(): string
    {
        return 'QR';
    }

    public function preparar(DatosCobro $datos): string
    {
        $referencia = $datos->referenciaQr ?? ('QR-' . $datos->numeroRecibo);
        return 'QR preparado [' . $referencia . '] por ' . $datos->importeFormateado();
    }

    public function cobrar(DatosCobro $datos): string
    {
        $referencia = $datos->referenciaQr ?? ('QR-' . $datos->numeroRecibo);
        return 'Cobro QR confirmado [' . $referencia . '] por ' . $datos->importeFormateado();
    }
}
