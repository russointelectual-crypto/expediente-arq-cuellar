<?php
declare(strict_types=1);

require_once __DIR__ . '/Equipo.php';

class PCEscritorio extends Equipo
{
    public function __construct(
        int $id, string $marca, string $modelo, string $numeroSerie,
        public string $tipoGabinete,
        public bool $monitorIncluido = false
    ) {
        parent::__construct($id, $marca, $modelo, $numeroSerie);
        if (trim($tipoGabinete) === '') {
            throw new InvalidArgumentException('Indique el tipo de gabinete.');
        }
    }

    public function revisionDeIngreso(): string
    {
        return 'PC: revisar gabinete ' . $this->tipoGabinete . ', fuente y puertos.'
            . ($this->monitorIncluido ? ' Incluye monitor.' : ' Sin monitor.');
    }
}
