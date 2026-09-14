<?php
declare(strict_types=1);

require_once __DIR__ . '/cargar.php';

abstract class RecepcionEquipo
{
    // Factory Method: las subclases deciden que producto concreto crear.
    abstract public function crearEquipo(): Equipo;

    public function registrarIngreso(
        string $recibo, Cliente $cliente, string $motivo, DateTimeImmutable $fecha
    ): OrdenDeTrabajo {
        $equipo = $this->crearEquipo();
        return new OrdenDeTrabajo($recibo, $cliente, $equipo, $motivo, $fecha);
    }
}
