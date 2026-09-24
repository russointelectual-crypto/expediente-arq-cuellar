<?php
declare(strict_types=1);

/** STRATEGY concreta — el taller no logró dar solución: se devuelve el equipo sin cobrar. */
final class SinCargo implements ReglaDeCobro
{
    public function nombre(): string
    {
        return 'Sin cargo';
    }

    public function calcular(OrdenDeTrabajo $orden): Cobro
    {
        return new Cobro($this->nombre(), [], 'el taller no logró reparar');
    }
}
