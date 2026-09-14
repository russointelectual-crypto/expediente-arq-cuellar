<?php
declare(strict_types=1);

require_once __DIR__ . '/RecepcionEquipo.php';

class RecepcionLaptop extends RecepcionEquipo
{
    public function __construct(
        private int $id, private string $marca, private string $modelo,
        private string $serie, private string $tipoBateria,
        private bool $bateriaEntregada = false
    ) {}

    public function crearEquipo(): Equipo
    {
        return new Laptop($this->id, $this->marca, $this->modelo, $this->serie,
            $this->tipoBateria, $this->bateriaEntregada);
    }
}
