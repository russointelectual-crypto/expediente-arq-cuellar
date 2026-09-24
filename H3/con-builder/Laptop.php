<?php
declare(strict_types=1);

/**
 * Laptop: la única que tiene regla de batería (RN3).
 * Interna → queda en el equipo y se registra. Externa → se devuelve al cliente.
 */
final class Laptop extends Equipo
{
    public const BATERIA_INTERNA = 'INTERNA';
    public const BATERIA_EXTERNA = 'EXTERNA';
    public const SIN_BATERIA     = 'SIN_BATERIA';

    public function __construct(
        string $marca,
        string $modelo,
        string $procesador,
        int $ramGB,
        int $hddGB,
        int $ssdGB,
        public readonly string $bateria,
    ) {
        parent::__construct($marca, $modelo, $procesador, $ramGB, $hddGB, $ssdGB);
        if (!in_array($bateria, [self::BATERIA_INTERNA, self::BATERIA_EXTERNA, self::SIN_BATERIA], true)) {
            throw new InvalidArgumentException("Tipo de batería inválido: {$bateria}.");
        }
    }

    public function tipo(): string
    {
        return 'Laptop';
    }

    public function notasDeRecepcion(): array
    {
        return match ($this->bateria) {
            self::BATERIA_INTERNA => ['Batería interna: queda dentro del equipo.'],
            self::BATERIA_EXTERNA => ['Batería externa: se DEVUELVE al cliente en el mostrador.'],
            self::SIN_BATERIA     => ['El equipo ingresa sin batería.'],
        };
    }
}
