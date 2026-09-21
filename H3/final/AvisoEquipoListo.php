<?php
declare(strict_types=1);

require_once __DIR__ . '/IObservadorOrden.php';
require_once __DIR__ . '/ServicioCaja.php';

final class AvisoEquipoListo implements IObservadorOrden
{
    public function __construct(
        private ServicioCaja $caja,
        private DatosCobro $datosCobro
    ) {
    }

    public function actualizar(CambioEstado $cambio): void
    {
        if ($cambio->estadoActual !== 'lista') {
            return;
        }

        echo '[AVISO] ' . $cambio->cliente . ': la orden ' . $cambio->numeroRecibo
            . ' esta lista. Medio seleccionado: ' . $this->caja->metodoActual() . '. '
            . $this->caja->preparar($this->datosCobro) . "\n";
    }
}
