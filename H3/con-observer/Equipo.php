<?php
declare(strict_types=1);

/**
 * Equipo que ingresa al taller (RN3). Es abstracta porque en el H2 el switch(tipo)
 * se reemplazó por subclases: cada tipo sabe sus propias reglas (OCP).
 */
abstract class Equipo
{
    public function __construct(
        public readonly string $marca,
        public readonly string $modelo,
        public readonly string $procesador,
        public readonly int $ramGB,
        public readonly int $hddGB = 0,
        public readonly int $ssdGB = 0,
    ) {
        if (trim($marca) === '' || trim($modelo) === '') {
            throw new InvalidArgumentException('Marca y modelo del equipo son obligatorios.');
        }
        if ($ramGB < 0 || $hddGB < 0 || $ssdGB < 0) {
            throw new InvalidArgumentException('Las capacidades de RAM y disco no pueden ser negativas.');
        }
    }

    abstract public function tipo(): string;

    /** Notas que se imprimen en el recibo de ingreso según el tipo de equipo. */
    public function notasDeRecepcion(): array
    {
        return [];
    }

    public function resumen(): string
    {
        $discos = [];
        if ($this->ssdGB > 0) {
            $discos[] = "SSD {$this->ssdGB} GB";
        }
        if ($this->hddGB > 0) {
            $discos[] = "HDD {$this->hddGB} GB";
        }
        $disco = $discos === [] ? 'sin disco' : implode(' + ', $discos);

        return sprintf('%s %s %s · %s · %d GB RAM · %s',
            $this->tipo(), $this->marca, $this->modelo, $this->procesador, $this->ramGB, $disco);
    }
}
