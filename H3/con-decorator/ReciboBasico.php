<?php
declare(strict_types=1);

require_once __DIR__ . '/IRecibo.php';
require_once __DIR__ . '/../base/OrdenDeTrabajo.php';

class ReciboBasico implements IRecibo
{
    public function __construct(private OrdenDeTrabajo $orden)
    {
    }

    public function generar(): string
    {
        return 'Recibo: ' . $this->orden->numeroRecibo . "\n"
            . 'Cliente: ' . $this->orden->cliente->nombreCompleto . "\n"
            . 'Motivo: ' . $this->orden->motivoIngreso . "\n"
            . 'Estado: ' . $this->orden->estadoActual() . "\n";
    }
}
