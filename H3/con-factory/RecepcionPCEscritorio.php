<?php
declare(strict_types=1);

/** FACTORY METHOD — Creador concreto: sabe crear una PC de escritorio. */
final class RecepcionPcEscritorio extends RecepcionDeEquipo
{
    public function tipoQueRecibe(): string
    {
        return 'PC de escritorio';
    }

    protected function crearEquipo(array $datos): Equipo
    {
        return new PcEscritorio(
            $this->requerido($datos, 'marca'),
            $this->requerido($datos, 'modelo'),
            $this->requerido($datos, 'procesador'),
            (int) $this->requerido($datos, 'ram'),
            (int) ($datos['hdd'] ?? 0),
            (int) ($datos['ssd'] ?? 0),
        );
    }
}
