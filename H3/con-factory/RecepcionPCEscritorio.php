<?php
declare(strict_types=1);

require_once __DIR__ . '/RecepcionEquipo.php';

class RecepcionPCEscritorio extends RecepcionEquipo
{
    public function __construct(
        private int $id, private string $marca, private string $modelo,
        private string $serie, private string $tipoGabinete,
        private bool $monitorIncluido = false
    ) {}

    public function crearEquipo(): Equipo
    {
        return new PCEscritorio($this->id, $this->marca, $this->modelo, $this->serie,
            $this->tipoGabinete, $this->monitorIncluido);
    }
}
