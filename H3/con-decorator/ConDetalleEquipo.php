<?php
declare(strict_types=1);

require_once __DIR__ . '/ReciboDecorador.php';
require_once __DIR__ . '/../base/Equipo.php';

class ConDetalleEquipo extends ReciboDecorador
{
    public function __construct(IRecibo $recibo, private Equipo $equipo)
    {
        parent::__construct($recibo);
    }

    public function generar(): string
    {
        return parent::generar()
            . 'Equipo: ' . $this->equipo->descripcion() . "\n"
            . 'Revision de ingreso: ' . $this->equipo->revisionDeIngreso() . "\n";
    }
}
