<?php
declare(strict_types=1);

require_once __DIR__ . '/Equipo.php';

class Laptop extends Equipo
{
    public function __construct(
        int $id, string $marca, string $modelo, string $numeroSerie,
        public string $tipoBateria,
        public bool $bateriaEntregadaAlCliente = false
    ) {
        parent::__construct($id, $marca, $modelo, $numeroSerie);
        if (trim($tipoBateria) === '') {
            throw new InvalidArgumentException('Indique el tipo de bateria.');
        }
    }

    public function revisionDeIngreso(): string
    {
        return 'Laptop: revisar pantalla, teclado y cargador. Bateria: ' . $this->tipoBateria
            . ($this->bateriaEntregadaAlCliente ? ' (entregada al cliente).' : ' (queda en el taller).');
    }
}
