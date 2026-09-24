<?php
declare(strict_types=1);

/** FACTORY METHOD — Creador concreto: sabe crear una Laptop (y exige el tipo de batería, RN3). */
final class RecepcionLaptop extends RecepcionDeEquipo
{
    public function tipoQueRecibe(): string
    {
        return 'Laptop';
    }

    protected function crearEquipo(array $datos): Equipo
    {
        return new Laptop(
            $this->requerido($datos, 'marca'),
            $this->requerido($datos, 'modelo'),
            $this->requerido($datos, 'procesador'),
            (int) $this->requerido($datos, 'ram'),
            (int) ($datos['hdd'] ?? 0),
            (int) ($datos['ssd'] ?? 0),
            $this->requerido($datos, 'bateria'),
        );
    }
}
