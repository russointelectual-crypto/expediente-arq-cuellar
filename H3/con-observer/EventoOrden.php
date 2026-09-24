<?php
declare(strict_types=1);

/** OBSERVER — lo que la orden publica: qué pasó, a qué orden, quién lo hizo y cuándo. */
final class EventoOrden
{
    public const CAMBIO_ESTADO    = 'CAMBIO_ESTADO';
    public const PLAZO_POR_VENCER = 'PLAZO_POR_VENCER';
    public const PLAZO_VENCIDO    = 'PLAZO_VENCIDO';

    public function __construct(
        public readonly string $tipo,
        public readonly OrdenDeTrabajo $orden,
        public readonly string $usuario,
        public readonly DateTimeImmutable $fecha,
        public readonly string $estadoAnterior,
        public readonly string $estadoNuevo,
        public readonly string $nota = '',
    ) {
    }

    public function esCierre(): bool
    {
        return $this->tipo === self::CAMBIO_ESTADO
            && in_array($this->estadoNuevo, [OrdenDeTrabajo::SOLUCIONADA, OrdenDeTrabajo::SIN_SOLUCION], true);
    }

    public function esDePlazo(): bool
    {
        return in_array($this->tipo, [self::PLAZO_POR_VENCER, self::PLAZO_VENCIDO], true);
    }
}
