<?php
declare(strict_types=1);

require_once __DIR__ . '/IObservadorOrden.php';
require_once __DIR__ . '/ServicioCaja.php';

final class ProcesarCobroEnEntrega implements IObservadorOrden
{
    public function __construct(
        private ServicioCaja $caja,
        private DatosCobro $datosCobro
    ) {
    }

    public function actualizar(CambioEstado $cambio): void
    {
        if ($cambio->estadoActual !== 'entregada') {
            return;
        }

        echo '[CAJA] ' . $cambio->numeroRecibo . ': '
            . $this->caja->cobrar($this->datosCobro) . "\n";
    }
}
