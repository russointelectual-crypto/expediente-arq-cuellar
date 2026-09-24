<?php
declare(strict_types=1);

/** DECORATOR concreto — agrega la licencia de Office (precio de ejemplo). */
final class ConLicenciaOffice extends AgregadoDeServicio
{
    protected function concepto(): string { return 'Licencia Office'; }
    protected function monto(): float { return 120.0; }
}
