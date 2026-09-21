<?php
declare(strict_types=1);

require_once __DIR__ . '/IObservadorOrden.php';

final class AvisoEquipoListo implements IObservadorOrden
{
    public function actualizar(CambioEstado $cambio): void
    {
        if ($cambio->estadoActual === 'lista') {
            echo '[AVISO] ' . $cambio->cliente . ' (' . $cambio->celular . '): '
                . 'la orden ' . $cambio->numeroRecibo . " esta lista para retirar.\n";
        }

        if ($cambio->estadoActual === 'devuelta sin solucion') {
            echo '[AVISO] ' . $cambio->cliente . ': la orden ' . $cambio->numeroRecibo
                . " fue devuelta sin solucion.\n";
        }
    }
}
