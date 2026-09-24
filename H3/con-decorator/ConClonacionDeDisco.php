<?php
declare(strict_types=1);

/** DECORATOR concreto — clonación de HDD a SSD sin reinstalar (precio de ejemplo). */
final class ConClonacionDeDisco extends AgregadoDeServicio
{
    protected function concepto(): string { return 'Clonación de HDD a SSD'; }
    protected function monto(): float { return 60.0; }
}
