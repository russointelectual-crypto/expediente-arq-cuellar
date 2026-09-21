<?php
declare(strict_types=1);

abstract class Equipo
{
    public function __construct(
        public int $id,
        public string $marca,
        public string $modelo,
        public string $numeroSerie
    ) {
        if ($id <= 0 || trim($marca) === '' || trim($modelo) === '' || trim($numeroSerie) === '') {
            throw new InvalidArgumentException('El equipo necesita ID positivo, marca, modelo y serie.');
        }
    }

    public function descripcion(): string
    {
        return $this->marca . ' ' . $this->modelo . ' / serie ' . $this->numeroSerie;
    }

    abstract public function revisionDeIngreso(): string;
}
