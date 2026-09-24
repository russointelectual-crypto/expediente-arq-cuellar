<?php
declare(strict_types=1);

/** DECORATOR concreto — agrega un antivirus por un año (precio de ejemplo). */
final class ConAntivirus extends AgregadoDeServicio
{
    protected function concepto(): string { return 'Antivirus 1 año'; }
    protected function monto(): float { return 80.0; }
}
