<?php
declare(strict_types=1);

/** DECORATOR concreto — agrega la licencia de Windows (precio de ejemplo). */
final class ConLicenciaWindows extends AgregadoDeServicio
{
    protected function concepto(): string { return 'Licencia Windows 11 Pro'; }
    protected function monto(): float { return 150.0; }
}
