<?php
declare(strict_types=1);

require_once __DIR__ . '/IObservadorOrden.php';

class AvisoEquipoListo implements IObservadorOrden
{
    public function actualizar(CambioEstado $cambio): void
    {
        if ($cambio->estadoActual !== 'lista') {
            return;
        }
        echo '[AVISO SIMULADO] Para ' . $cambio->cliente . ' (' . $cambio->celular
            . '): su equipo de la orden ' . $cambio->numeroRecibo . " esta listo.\n";
    }
}
