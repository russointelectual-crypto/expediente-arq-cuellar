<?php
declare(strict_types=1);

/** PC de escritorio: mismos datos que cualquier equipo, sin regla de batería. */
final class PcEscritorio extends Equipo
{
    public function tipo(): string
    {
        return 'PC de escritorio';
    }
}
