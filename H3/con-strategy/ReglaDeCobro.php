<?php
declare(strict_types=1);

/** STRATEGY — contrato de toda regla que decide cuánto se cobra por una orden cerrada. */
interface ReglaDeCobro
{
    public function nombre(): string;

    public function calcular(OrdenDeTrabajo $orden): Cobro;
}
